<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Exceptions\Custom\ValidationException;
use Illuminate\Support\Facades\Validator;

class BaseController extends Controller
{
    /**
     * Validate request data
     */
    protected function validateRequest(Request $request, array $rules, array $messages = [])
    {
        $validator = Validator::make($request->all(), $rules, $messages);
        
        if ($validator->fails()) {
            throw new ValidationException($validator->errors());
        }
        
        return $validator->validated();
    }

    /**
     * Return success response
     */
    protected function successResponse($data, $message = 'Success', $code = 200)
    {
        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    /**
     * Return error response
     */
    protected function errorResponse($message, $code = 400)
    {
        return response()->json([
            'status' => 'error',
            'message' => $message,
        ], $code);
    }
}