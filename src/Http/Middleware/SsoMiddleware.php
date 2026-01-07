<?php

namespace Digood\Sso\Http\Middleware;

use Closure;
use Digood\Sso\Services\SsoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

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
     * @return RedirectResponse|mixed
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
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