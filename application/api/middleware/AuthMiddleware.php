<?php
namespace app\api\middleware;

use think\Exception;
use think\exception\HttpResponseException;
use think\Response;
use think\Request;

class AuthMiddleware
{
    public static function checkApiKey(Request $request)
    {
        // 获取API key
        $apiKey = $request->header('apiKey');

        if (!$apiKey || $apiKey !== config('api_key')) {
            return false;
        }
        return true;
    }
}

