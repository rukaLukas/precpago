<?php

namespace App\Services;

use Exception;
use Illuminate\Http\Request;
use App\Exceptions\OlderTimestampException;
use App\Http\Requests\StoreTransactionRequest;

class TransactionValidationService
{
    protected $createRequest;

    public function __construct()
    {
        $this->createRequest = StoreTransactionRequest::class;
    }
    
    public function validateRequest(Request $request)
    {
        $requestBody = $request->getContent();
        throw_if(json_decode($requestBody) === null, new Exception('Invalid JSON', 400));
        
        $allowedFields = ['amount', 'timestamp'];
        $requestData = json_decode($requestBody, true);
        $extraFields = array_diff(array_keys($requestData), $allowedFields);
        throw_if(!empty($extraFields), new Exception('Invalid fields: ' . implode(', ', $extraFields), 422));
        
        $timestamp = strtotime($request->input('timestamp'));
        throw_if($timestamp < strtotime('-60 seconds'), new OlderTimestampException());
        
        $createRequest = app($this->createRequest);
        $request->validate($createRequest->rules());   
    }
}