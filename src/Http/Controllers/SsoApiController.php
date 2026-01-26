<?php

namespace Digood\Sso\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SsoApiController
{
    /**
     * 获取临时登录URL
     * @return JsonResponse
     */
    public function sign_in_key(Request $request)
    {
        $redirect_to = $request->input('redirect_to');

        $data= [
            'sso_user_token' => sso_api_user_pat(),
            'redirect_to' => $redirect_to
        ];

        $params = [
            'key' => sso_api_set_temporary_sign_in($data),
            'redirect_to' => base64_encode($redirect_to)
        ];

        $data = [
            'url' => route('sso.sign-in.by_key',$params),// 构造前台登录地址
        ];

        return response_success('success', $data);
    }

}