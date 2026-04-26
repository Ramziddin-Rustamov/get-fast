<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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
                return redirect()->back()->with('error', 'Already processed');
            }

            $user = $withdraw->user;
            $balance = $user->balance;

            if ($balance->balance < $withdraw->amount) {
                return redirect()->back()->with('error', 'Insufficient balance');
            }

            $balance->balance -= $withdraw->amount;
            $balance->save();

            $withdraw->status = 'approved';
            $withdraw->save();

            DB::commit();

            return redirect()->back()->with('success', 'Approved successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', $e->getMessage());
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