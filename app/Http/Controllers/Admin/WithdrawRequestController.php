<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\V1\CompanyBalance;
use App\Models\V1\CompanyBalanceTransaction;
use App\Models\WithdrawRequest;
use Illuminate\Support\Facades\DB;

class WithdrawRequestController extends Controller
{
    // 📄 LIST PAGE
    public function indexForAdmin()
    {
        $withdraws = WithdrawRequest::with('user')->latest()->paginate(30);

        return view('admin-views.withdraw.index', compact('withdraws'));
    }

    // ✅ APPROVE
    public function approve($id)
    {
        try {
            DB::beginTransaction();

            $withdraw = WithdrawRequest::lockForUpdate()->findOrFail($id);

            if ($withdraw->status !== 'pending') {
                return back()->with('error', 'Already processed');
            }

            $user = $withdraw->user;

            $userBalance = \App\Models\UserBalance::where('user_id', $user->id)
                ->lockForUpdate()
                ->first();

            if (!$userBalance) {
                throw new \Exception('User balance not found');
            }

            if ($userBalance->balance < $withdraw->amount) {
                return back()->with('error', 'Insufficient balance');
            }

            // 💰 USER BALANCE UPDATE
            $beforeUserBalance = $userBalance->balance;
            $afterUserBalance  = $beforeUserBalance - $withdraw->amount;

            $userBalance->update([
                'balance' => $afterUserBalance
            ]);

            $lang = $user->authLanguage->language ?? 'uz';

            $cardInfo = trim(
                ($withdraw->card_holder ?? '') . ' ' .
                    ($withdraw->card?->number ?? '')
            );

            $message = [
                'uz' => "So‘rovingiz bo‘yicha pul hisobingizdan yechildi va yuborgan kartangizga tashlandi. $cardInfo",

                'ru' => "По вашему запросу средства были списаны с вашего счета и переведены на указанную карту. $cardInfo",

                'en' => "According to your request, funds have been withdrawn from your account and transferred to your provided card. $cardInfo",
            ];

            // 🧾 USER TRANSACTION
            \App\Models\BalanceTransaction::create([
                'user_id'        => $user->id,
                'type'           => 'debit', // debit - chiqim, credit - kirim
                'amount'         => $withdraw->amount,
                'balance_before' => $beforeUserBalance,
                'balance_after'  => $afterUserBalance,
                'status'         => 'success',
                'reason'         => $message[$lang],
            ]);

            // 🏢 COMPANY BALANCE
            $companyBalance = CompanyBalance::lockForUpdate()
                ->firstOrCreate([], [
                    'balance' => 0,
                    'total_income' => 0
                ]);

            $beforeCompanyBalance = $companyBalance->balance;
            $afterCompanyBalance  = $beforeCompanyBalance + $withdraw->amount;

            $companyBalance->increment('balance', $withdraw->amount);
            $companyBalance->increment('total_income', $withdraw->amount);

            // 🧾 COMPANY TRANSACTION
            CompanyBalanceTransaction::create([
                'company_balance_id' => $companyBalance->id,
                'amount'             => $withdraw->amount,
                'balance_before'     => $beforeCompanyBalance,
                'balance_after'      => $afterCompanyBalance,
                'type'               => 'outgoing',
                'reason'             => 'Withdraw from user: paid by admin and company account ' . $user->first_name . ' ' . $user->last_name,
                'currency'           => 'UZS',
            ]);

            // ✅ WITHDRAW STATUS
            $withdraw->status = 'approved';
            $withdraw->save();

            DB::commit();

            return back()->with('success', 'Approved successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }

    // ❌ REJECT
    public function reject($id)
    {
        try {
            DB::beginTransaction();

            $withdraw = WithdrawRequest::findOrFail($id);

            if ($withdraw->status !== 'pending') {
                return redirect()->back()->with('error', 'Already processed');
            }

            $withdraw->status = 'rejected';
            $withdraw->save();

            DB::commit();

            return redirect()->back()->with('success', 'Rejected successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
