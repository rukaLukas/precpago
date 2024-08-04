<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\RabbitMQService;
use Illuminate\Support\Facades\Cache;
use App\Services\TransactionValidationService;

class TransactionController extends Controller
{
    protected $rabbitMQService;
    protected $validationService;

    public function __construct(
        RabbitMQService $rabbitMQService,
        TransactionValidationService $transactionValidationService)
    {
        $this->rabbitMQService = $rabbitMQService;
        $this->validationService = $transactionValidationService;
    }

    public function store(Request $request)
    { 
       $this->validationService->validateRequest($request);
        
       $transaction = [
            'amount' => $request->input('amount'),
            'timestamp' => $request->input('timestamp'),
        ];        

        $this->rabbitMQService->publish(json_encode($transaction));
        Cache::forever('statistics_polling_flag', true);
       
        return response('', 201);
    }

    public function destroy()
    {
        $this->rabbitMQService->deleteAllMessages();
        return response('', 204);
    }    
}