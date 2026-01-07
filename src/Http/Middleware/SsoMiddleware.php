<?php

namespace Digood\Sso\Http\Middleware;

use Closure;
use Digood\Sso\Services\SsoService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SsoMiddleware
{
    public function __construct(protected SsoService $ssoService)
    {

    }

    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @param mixed ...$roles
     * @return Response
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $urlSignIn = $this->ssoService->getSignInUrl();// 登录跳转地址

        // 校验登录状态
        if (!$this->ssoService->isSignIn()) return response()->redirectTo($urlSignIn);

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
                '<a href="' . $urlSignIn . '">刷新</a>',
            ]);

            if (!in_array(true, $roleConditions)) return response($msg, 500);// 权限不足
        }

        return $next($request);
    }

}