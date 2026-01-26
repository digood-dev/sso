<?php

namespace Digood\Sso\Http\Controllers;

use Digood\Sso\Services\SsoService;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Logto\Sdk\LogtoException;


class SsoController
{
    public function __construct(protected SsoService $ssoService)
    {

    }

    /**
     * @return RedirectResponse
     * @throws \Exception
     */
    private function redirectToHome(string $name = 'home')
    {
        if (!Route::has($name)) throw new \Exception('未找到系统首页路由，请检查路由配置！');
        return response()->redirectToRoute($name);// 默认跳转到系统首页
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
     * 登录（不透明Token方式）
     * @param Request $request
     * @return mixed
     */
    public function sign_in_by_token(Request $request)
    {
        $redirect_to = base64_decode($request->get('redirect_to'));// 需要跳转的地址(base64)

        $token = $request->route('token');
        if (empty($token)) return response('<h1>缺少用户TOKEN值，请关闭页面重试！</h1>', 500);

        try {
            $userInfo = $this->ssoService->getUserInfoByAccessToken($token);// 直接使用父程序传递的token来获取用户信息
            if (empty($userInfo)) return response('<h1>账户流转失败，请关闭页面重试！</h1>', 500);

            sso_user_setup($userInfo);// 手动设置用户信息,注入Session

            return response()->redirectTo($redirect_to);

        } catch (ClientException $e) {
            $res = $e->getResponse()->getBody()->getContents();
            $res = json_decode($res, true);

            $messages = [
                '<hr>',
                '<p>Code:' . ($res['code'] ?? '') . '</p>',
                '<p>Message:' . ($res['message'] ?? '') . '</p>',
                '<p>Description:' . ($res['description'] ?? '') . '</p>'
            ];
        }

        return response('<h1>账户信息校验失败，请关闭页面重试！</h1>' . implode('', $messages), 500);
    }

    /**
     * 通过临时Key进行登录
     * @param Request $request
     * @return ResponseFactory|RedirectResponse|\Illuminate\Http\Response
     * @throws \Exception
     */
    public function sign_in_by_key(Request $request)
    {
        $redirect_to = base64_decode($request->get('redirect_to'));

        // 临时登录key
        $temporary = sso_api_read_temporary_sign_in($request->route('key'));
        if (!$temporary) return response('临时登录标识不存在或已失效，请重试', 500);

        // 取得PAT值
        $pat = Arr::get($temporary, 'sso_user_token');// 用户的PAT
        if (empty($pat)) return response('SSO 用户PAT身份标识不存在或已失效，请重试', 500);

        // 读取用户信息
        try {
            $accessToken = sso_api_user_access_token($pat);// PAT换取accessToken
            if (empty($accessToken)) return response('SSO 用户PAT身份标识验证失败，请重试', 500);

            $userInfo = (new SsoService())->getUserInfoByAccessToken($accessToken);
            if (empty($userInfo)) return response('SSO 用户信息读取失败，请重试', 500);

        } catch (\Exception $e) {
            return response('SSO 用户信息失败，请重试', 500);
        }

        sso_user_setup($userInfo);// 植入用户信息到当前会话并删除缓存

        return empty($redirect_to) ? self::redirectToHome() : response()->redirectTo($redirect_to);//跳转到指定页或首页
    }

    /**
     * @throws \Exception
     */
    public function sign_in_callback(Request $request)
    {
        try {
            $this->ssoService->handleSignIn(); //处理登录回调

            $redirect_to = Session::get('redirect_to');// 是否设置的登录后跳转页面
            if (!empty($redirect_to)) {// 登录跳转到登录前访问的页面
                Session::remove('redirect_to');// 移除登录后跳转页面的配置值
                return response()->redirectTo($redirect_to);
            }

        } catch (\Exception $e) {
            Log::error('登录失败', [$e->getMessage()]);
            throw new \Exception('登录失败，请重试');
        }

        return self::redirectToHome();
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
        if ($request->session()->get('sso_login', false)) {
            $request->session()->remove('sso_login');
            $request->session()->remove('sso_userinfo');
            return Response::redirectTo($urlIndex);
        }

        try {
            $urlRedirect = $this->ssoService->getSignOutUrl($urlIndex);

        } catch (LogtoException $e) {
            return Response::make($e->getMessage() . '<hr><a href="' . $urlSignIn . '">重试</a>', 500);
        }

        return Response::redirectTo($urlRedirect);
    }
}