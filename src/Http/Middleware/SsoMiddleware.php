<?php

namespace Digood\Sso\Http\Middleware;

use Closure;
use Digood\Sso\Services\SsoService;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class SsoMiddleware
{
    public function __construct(protected SsoService $ssoService)
    {

    }

    private function doFatherTransLogin(string $access_token)
    {
        try {
            $userInfo = $this->ssoService->getUserInfo($access_token);// 直接使用父程序传递的token来获取用户信息

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
        }

        if (empty($userInfo)) $messages = ['<h1>账户信息流转失败，请关闭页面重试！</h1>'];

        return response(implode(PHP_EOL, $messages ?? ['<h1>账户操作失败</h1>']));
    }

    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @param mixed ...$roles
     * @return RedirectResponse|mixed
     */
    public function handle(Request $request, Closure $next, ...$roles): mixed
    {
        // 是否带有父程序token值(例如 Oauth嵌套进来)
        $father_access_token = $request->get('father_access_token');
        if (!empty($father_access_token)) return self::doFatherTransLogin($father_access_token);//使用父程序的token进行登录

        // 校验登录状态
        if (!$this->ssoService->isSignIn()) return Response::redirectToRoute('sso.sign-in');

        // 校验角色
        if (!empty($roles)) {
            $roleConditions = [];
            foreach ($roles as $role) {
                $roleConditions[] = $this->ssoService->isRole($role);
            }

            $msg = implode(PHP_EOL, [
                '<h1>抱歉，你的角色或权限不足！</h1>',
                '<p>开放角色：' . implode(',', $roles) . '</p>',
                '<hr>',
                '<p>当前角色：' . implode(',', $this->ssoService->getUserRoles()) . '</p>',
                '<p>用户标识：' . $this->ssoService->getUserId() . '</p>',
                '<hr>',
                '<a href="' . route('sso.sign-in') . '">刷新</a>',
            ]);

            if (!in_array(true, $roleConditions)) return Response::make($msg, 500);// 权限不足
        }

        return $next($request);
    }

}