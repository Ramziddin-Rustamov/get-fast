<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\V1\PaymentService;
use App\Http\Requests\V1\PaymentStoreRequest;
use App\Http\Requests\V1\PaymentUpdateRequest;
use App\Http\Resources\V1\PaymentResource;

class PaymentController extends Controller
{
    protected $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    public function index()
    {
        $payments = $this->paymentService->getAllPayments();
        return PaymentResource::collection($payments);
    }

    public function show($id)
    {
        $payment = $this->paymentService->getPaymentById($id);
        return new PaymentResource($payment);
    }

    public function store(PaymentStoreRequest $request)
    {
        $payment = $this->paymentService->createPayment($request->validated());
        return new PaymentResource($payment);
    }

    public function update(PaymentUpdateRequest $request, $id)
    {
        $payment = $this->paymentService->updatePayment($id, $request->validated());
        return new PaymentResource($payment);
    }

    public function destroy($id)
    {
        $this->paymentService->deletePayment($id);
        return response()->json(['message' => 'Payment deleted successfully'], 200);
    }

}
