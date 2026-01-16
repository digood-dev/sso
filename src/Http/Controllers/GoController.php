<?php

namespace Digood\Sso\Http\Controllers;

use Digood\Sso\Services\SsoService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;

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

        return response()->view('digood.sso::go.sub_system', ['url' => $url, 'name' => $name, 'host' => $host]);
    }

    /**
     * @param Request $request
     * @return Response
     * @throws ConnectionException
     */
    function sub_system_by_key(Request $request)
    {
        if (!sso_user_is_signIn()) return self::viewError('此功能需要SSO联合账户权限', ['未使用多谷SSO联合账户进行登录当前系统', '从其它系统流转的登录态不支持二次流转'], ['重新使用多谷SSO方式登录']);

        $name = $request->get('name', '子系统');
        $endpoint = $request->get('endpoint');// 子系统地址
        $host = parse_url($endpoint, PHP_URL_HOST);
        $redirect_to = $request->get('redirect_to');// 跳转地址

        $sso_user_token = sso_user_info()['sso_user_token'] ?? null;
        if (empty($sso_user_token)) return response('当前用户密钥未找到，请重新登录再尝试！');

        $headers = [
            'sso_user_token' => $sso_user_token,
        ];

        $params = [
            'redirect_to' => base64_decode($redirect_to)
        ];

        // 拉取子系统的免密地址
        $result = Http::connectTimeout(10)
            ->timeout(30)
            ->withoutVerifying()
            ->asJson()
            ->acceptJson()
            ->withHeaders($headers)
            ->baseUrl(base64_decode($endpoint))->post('sso/api/sign-in/key', ['redirect_to' => $params]);

        if ($result->failed()) return self::viewError('与子系统的通信失败，请重试！（' . $result->status() . '）', ['联系客服处理']);

        $url = $result->json('data.url');
        if (empty($url)) return self::viewError('子系统的响应结果不符合要求，请重试！', ['联系客服处理']);

        $data = [
            'url' => $url,
            'name' => $name,
            'host' => $host];

        return response()->view('digood.sso::go.sub_system', $data);
    }
}