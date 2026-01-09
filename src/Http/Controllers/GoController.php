<?php

namespace Digood\Sso\Http\Controllers;

use Digood\Sso\Services\SsoService;
use Illuminate\Http\Request;

class GoController
{
    /**
     * @param string $title
     * @param array $reasons
     * @param array $solves
     * @return mixed
     */
    private function viewError(string $title = '抱歉，操作失败！', array $reasons = [], array $solves = [])
    {
        return response()->view(
            'digood.sso::go.sub_system_error',
            [
                'title' => $title,
                'reasons' => $reasons,
                'solves' => $solves
            ],
            500);
    }

    /**
     * @param Request $request
     * @return mixed
     * @throws \Exception
     */
    function sub_system(Request $request)
    {
        if ($request->session()->get('sso_login', false)) return self::viewError('此功能需要SSO联合账户权限', ['未使用多谷SSO联合账户进行登录当前系统', '从其它系统流转的登录态不支持二次流转'], ['重新使用多谷SSO方式登录']);

        // 读取实时Token
        $token = (new SsoService())->client()->getAccessToken();
        if (empty($token)) return self::viewError('此功能需要SSO联合账户权限', ['无法读取你的联合账户Token密钥', '未使用多谷SSO联合账户进行登录当前系统', '从其它系统流转的登录态不支持二次流转'], ['重新使用多谷SSO方式登录']);

        // 整理跳转参数
        $redirect_to = base64_decode($request->input('redirect_to'));// 子系统页面（在登录成功后跳转）
        $host = parse_url($redirect_to, PHP_URL_HOST);// 子系统主机
        $name = $request->get('name', '子系统');

        $url = sprintf(
            'https://%s/sso/sign-in/by_token/%s?redirect_to=%s',
            $host, $token, base64_encode($redirect_to)
        );

        return response()->view('digood.sso::go.sub_system', ['url' => $url, 'name' => $name]);
    }
}