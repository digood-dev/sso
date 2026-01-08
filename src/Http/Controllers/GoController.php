<?php

namespace Digood\Sso\Http\Controllers;

use Digood\Sso\Services\SsoService;
use Illuminate\Http\Request;

class GoController
{
    /**
     * @param Request $request
     * @return mixed
     * @throws \Exception
     */
    function sub_system(Request $request)
    {
        if ($request->session()->get('sso_login', false)) throw new \Exception('当前登录态来自其它系统流转，不支持继续流转至其它子系统');

        $token = (new SsoService())->client()->getAccessToken();
        if (empty($token)) throw new \Exception('无法提取的你TOKEN，可能未登录或者从其它系统流转的登录态');

        $redirect_to = base64_decode($request->input('redirect_to'));
        $host = parse_url($redirect_to, PHP_URL_HOST);

        $url = sprintf(
            'https://%s/sso/sign-in/by_token/%s?redirect_to=%s',
            $host, $token, base64_encode($redirect_to)
        );

        return response()->redirectTo($url);
    }
}