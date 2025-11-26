<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\BalanceTransaction;
use Illuminate\Http\Request;
use App\Http\Resources\V1\BalanceTransactionResource;
use Barryvdh\DomPDF\Facade\Pdf;

class BalanceTransactionController extends Controller
{

    
    public function getAllUserBalanceTransactions(Request $request)
    {
        $userTransaction = BalanceTransaction::where('user_id', $request->user()->id)->paginate(20);
        if ($userTransaction) {
            return BalanceTransactionResource::collection($userTransaction);
        }

        $messages = [
            'uz' => "Balans tranzaksiyalari topilmadi.",
            'ru' => "Транзакции баланса не найдены.",
            'en' => "No balance transactions found.",
        ];

        $message = $messages[auth()->user()->authLanguage->language ?? 'uz'];

        return response()->json([
            'status' => 'error',
            'message' => $message
        ], 404);
    }

    public function downloadPdfTransactions(Request $request)
    {

        $transactions = BalanceTransaction::where('user_id', $request->user()->id)->get();

        // Tilga mos sarlavha va ustun nomlarini tayyorlash
        $titles = [
            'uz' => [
                'title' => 'Balans tranzaksiyalari',
                'id' => 'ID',
                'type' => 'Turi',
                'amount' => 'Summasi',
                'before' => 'Oldingi balans',
                'after' => 'Keyingi balans',
                'status' => 'Holati',
                'reason' => 'Sababi',
                'created_at' => 'Yaratilgan vaqti',
            ],
            'ru' => [
                'title' => 'Транзакции баланса',
                'id' => 'ID',
                'type' => 'Тип',
                'amount' => 'Сумма',
                'before' => 'Баланс до',
                'after' => 'Баланс после',
                'status' => 'Статус',
                'reason' => 'Причина',
                'created_at' => 'Дата создания',
            ],
            'en' => [
                'title' => 'Balance Transactions',
                'id' => 'ID',
                'type' => 'Type',
                'amount' => 'Amount',
                'before' => 'Balance Before',
                'after' => 'Balance After',
                'status' => 'Status',
                'reason' => 'Reason',
                'created_at' => 'Created At',
            ],
        ];

        $t = $titles[$request->user()->authLanguage->language ?? 'uz'];

        $pdf = Pdf::loadView('pdf.transactions', compact('transactions', 't'));

        return response($pdf->output(), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="balance-transactions.pdf"');
    }

    public function downloadOnePdfTransaction($id)
    {

        $transaction = BalanceTransaction::where('user_id', auth()->user()->id)->find($id);
        if (!$transaction) {
            $messages = [
                'uz' => "Balans tranzaksiyalari topilmadi.",
                'ru' => "Транзакции баланса не найдены.",
                'en' => "No balance transactions found.",
            ];

            $message = $messages[auth()->user()->authLanguage->language ?? 'uz'];

            return response()->json([
                'status' => 'error',
                'message' => $message
            ], 404);
        }

        // Tilga mos sarlavha va ustun nomlari
        $titles = [
            'uz' => [
                'title' => 'Balans tranzaksiyasi',
                'id' => 'ID',
                'type' => 'Turi',
                'amount' => 'Summasi',
                'before' => 'Oldingi balans',
                'after' => 'Keyingi balans',
                'status' => 'Holati',
                'reason' => 'Sababi',
                'created_at' => 'Yaratilgan vaqti',
            ],
            'ru' => [
                'title' => 'Транзакция баланса',
                'id' => 'ID',
                'type' => 'Тип',
                'amount' => 'Сумма',
                'before' => 'Баланс до',
                'after' => 'Баланс после',
                'status' => 'Статус',
                'reason' => 'Причина',
                'created_at' => 'Дата создания',
            ],
            'en' => [
                'title' => 'Balance Transaction',
                'id' => 'ID',
                'type' => 'Type',
                'amount' => 'Amount',
                'before' => 'Balance Before',
                'after' => 'Balance After',
                'status' => 'Status',
                'reason' => 'Reason',
                'created_at' => 'Created At',
            ],
        ];

        $t = $titles[auth()->user()->authLanguage->language ?? 'uz'];

        $pdf = Pdf::loadView('pdf.transaction', compact('transaction', 't'));

        return response($pdf->output(), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="balance-transaction.pdf"');
    }
}
