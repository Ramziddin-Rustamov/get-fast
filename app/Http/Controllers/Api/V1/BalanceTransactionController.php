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

        return response()->json(
            [ 
                'message' => 'No balance transactions found',
                'status' => 'error'
            ],
            404
        );
    }

    public function downloadPdfTransactions(Request $request)
    {
        $transactions = BalanceTransaction::where('user_id', $request->user()->id)->get();

        $pdf = Pdf::loadView('pdf.transactions', compact('transactions'));

        return response($pdf->output(), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="balance-transactions.pdf"');
    }

    public function downloadOnePdfTransactions($id)
    {
        $transaction = BalanceTransaction::where('user_id', auth()->user()->id)->find($id);
        if(!$transaction) {
            return response()->json(
                [
                    'message' => 'No balance transactions found',
                    'status' => 'error'
                ],
                404
            );
        }
        $pdf = Pdf::loadView('pdf.transaction', compact('transaction'));

        return response($pdf->output(), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="balance-transaction.pdf"');
    }
}
