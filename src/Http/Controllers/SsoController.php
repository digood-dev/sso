<?php

namespace Digood\Sso\Http\Controllers;

use Digood\Sso\Services\SsoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Logto\Sdk\LogtoException;


class SsoController
{
    public function __construct(protected SsoService $logtoService)
    {

    }

    /**
     * 登录
     * @param Request $request
     * @return RedirectResponse
     */
    public function sign_in(Request $request): RedirectResponse
    {
        $urlCallback = route('sso.sign-in.callback');
        $urlRedirect = $this->logtoService->getSignInUrl($urlCallback);
        return Response::redirectTo($urlRedirect);
    }

    /**
     * @throws \Exception
     */
    public function sign_in_callback(Request $request)
    {
        try {
            $this->logtoService->handleSignIn(); //处理登录回调

            // 检查登录前的访问页面
            $beforeSignIn = $request->session()->get('redirect_to');// 是否设置的登录后跳转页面
            if (!empty($beforeSignIn)) {// 登录跳转到登录前访问的页面
                $request->session()->remove('redirect_to');// 移除登录后跳转页面的配置值
                return $beforeSignIn;
            }

        } catch (\Exception $e) {
            Log::error('登录失败', [$e->getMessage()]);
            throw new \Exception('登录失败，请重试');
        }

        return route('home');
    }

    /**
     * 登出
     * @return RedirectResponse|\Illuminate\Http\Response
     */
    public function sign_out(): \Illuminate\Http\Response|RedirectResponse
    {
        $urlHome = route('index');
        $urlSignIn = route('sso.sign-in');

        try {
            $urlRedirect = $this->logtoService->getSignOutUrl($urlHome);

        } catch (LogtoException $e) {
            return Response::make($e->getMessage() . '<hr><a href="' . $urlSignIn . '">重试</a>', 500);
        }

        return Response::redirectTo($urlRedirect);
    }

}