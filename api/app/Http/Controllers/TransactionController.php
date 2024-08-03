<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use App\Jobs\ProcessTransaction;
use App\Services\RabbitMQService;
use Illuminate\Http\JsonResponse;
use App\Exceptions\OlderTimestampException;
use App\Http\Requests\StoreTransactionRequest;
use App\Services\TransactionValidationService;
use Illuminate\Validation\ValidationException;

class TransactionController extends Controller
{
    // protected $createRequest = StoreTransactionRequest::class;
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
       
        return response('', 201);
    }

    public function destroy()
    {
        $this->rabbitMQService->deleteAllMessages();
        return response('', 204);
    }

    // private function validateRequest(Request $request)
    // {   
    //     $requestBody = $request->getContent();
    //     throw_if(json_decode($requestBody) === null, new Exception('JSON inválido'));
        
    //     $allowedFields = ['amount', 'timestamp'];
    //     $requestData = json_decode($requestBody, true);
    //     $extraFields = array_diff(array_keys($requestData), $allowedFields);
    //     throw_if(!empty($extraFields), new Exception('Invalid fields: ' . implode(', ', $extraFields)));

        
    //     $timestamp = strtotime($request->input('timestamp'));
    //     throw_if($timestamp < strtotime('-60 seconds'), new OlderTimestampException());
        
    //     $createRequest = app($this->createRequest);
    //     $request->validate($createRequest->rules());        
    // }  
    
}