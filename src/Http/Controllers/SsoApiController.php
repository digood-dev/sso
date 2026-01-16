<?php

namespace Digood\Sso\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class SsoApiController
{
    /**
     * 获取临时登录URL
     * @return JsonResponse
     */
    public function sign_in_key(Request $request)
    {
        $key = Str::orderedUuid()->toString();// 临时登录信息key
        $redirect_to = $request->input('redirect_to');
        $sso_user_token = $request->header('sso_user_token');// PAT Token

        Cache::put($key, ['sso_user_token' => $sso_user_token, 'redirect_to' => $redirect_to], now()->addHour());

        $data = [
            'url' => route('sso.sign-in.by_key', ['key' => $key]),// 构造前台登录地址
        ];

        return response_success(null, $data);
    }

}