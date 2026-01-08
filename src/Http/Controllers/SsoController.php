<?php

namespace Digood\Sso\Http\Controllers;

use Digood\Sso\Services\SsoService;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;
use Logto\Sdk\LogtoException;


class SsoController
{
    public function __construct(protected SsoService $ssoService)
    {

    }

    /**
     * 登录
     * @param Request $request
     * @return RedirectResponse
     */
    public function sign_in(Request $request): RedirectResponse
    {
        $redirect_to = $request->get('redirect_to');
        if ($redirect_to) $request->session()->put('redirect_to', $redirect_to);

        $urlCallback = route('sso.sign-in.callback');
        $urlRedirect = $this->ssoService->getSignInUrl($urlCallback);
        return Response::redirectTo($urlRedirect);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function sign_in_by_token(Request $request)
    {
        $token = $request->route('token');
        $redirect_to = $request->get('redirect_to');// 登录成功后需要跳转的地址(base64)

        try {
            $userInfo = $this->ssoService->getUserInfo($token);// 直接使用父程序传递的token来获取用户信息
            if (empty($userInfo)) return response('<h1>账户信息流转失败，请关闭页面重试！</h1>', 500);

            $request->session()->put('oss_login', true);
            $request->session()->put('oss_userinfo', $userInfo);

            return response()->redirectTo(base64_decode($redirect_to));

        } catch (ClientException $e) {
            $res = $e->getResponse()->getBody()->getContents();
            $res = json_decode($res, true);

            $messages = [
                '<h1>账户信息流转校验失败，请关闭页面重试！' . '</h1>',
                '<hr>',
                '<p>Code:' . ($res['code'] ?? '') . '</p>',
                '<p>Message:' . ($res['message'] ?? '') . '</p>',
                '<p>Description:' . ($res['description'] ?? '') . '</p>'
            ];

            return response(implode('', $messages), 500);
        }

        return response('<h1>账户操作失败</h1>', 500);
    }

    /**
     * @throws \Exception
     */
    public function sign_in_callback(Request $request)
    {
        try {
            $this->ssoService->handleSignIn(); //处理登录回调

            // 检查登录前的访问页面
            $redirect_to = Session::get('redirect_to');// 是否设置的登录后跳转页面
            if (!empty($redirect_to)) {// 登录跳转到登录前访问的页面
                Session::remove('redirect_to');// 移除登录后跳转页面的配置值
                return Response::redirectTo($redirect_to);
            }

        } catch (\Exception $e) {
            Log::error('登录失败', [$e->getMessage()]);
            throw new \Exception('登录失败，请重试');
        }

        return Response::redirectToRoute('home');
    }

    /**
     * 登出
     * @param Request $request
     * @return RedirectResponse|\Illuminate\Http\Response
     */
    public function sign_out(Request $request): \Illuminate\Http\Response|RedirectResponse
    {
        $urlIndex = route('index');
        $urlSignIn = route('sso.sign-in');

        if (!$this->ssoService->isSignIn()) return Response::redirectTo($urlIndex);// 未登录

        // 远程SSO退出
        if ($request->session()->get('oss_login', false)) {
            $request->session()->remove('oss_login');
            $request->session()->remove('oss_userinfo');
            return Response::redirectTo($urlIndex);
        }

        try {
            $urlRedirect = $this->ssoService->getSignOutUrl($urlIndex);

        } catch (LogtoException $e) {
            return Response::make($e->getMessage() . '<hr><a href="' . $urlSignIn . '">重试</a>', 500);
        }

        return Response::redirectTo($urlRedirect);
    }

    public function goSubSystem(string $subSystemUrl)
    {

    }

}