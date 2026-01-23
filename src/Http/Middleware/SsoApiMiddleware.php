<?php

namespace Digood\Sso\Http\Middleware;

use Closure;
use Digood\Sso\Services\SsoPatService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SsoApiMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $tokenKey = 'sso-user-token';
        if (!$request->headers->has($tokenKey)) return response_error('缺少SSO Token，请重试');

        $tokenValue = $request->header($tokenKey);// PAT Token
        if (empty($tokenValue)) return response_error('SSO Token值为空');

        $cacheKey = md5($tokenValue);
        if (Cache::has($cacheKey)) return $next($request);// 此PAT token已校验通过

        try {
            $access_token = (new SsoPatService())->getAccessToken($tokenValue);
            if (!$access_token) return response_error('SSO用户校验无法完成，请重试！');// 验证失败

            // todo 加上读取用户详情，防止api的控制器里需要使用用户详情

            Cache::put($cacheKey, true, now()->addMinutes(30));

        } catch (\Exception $e) {
            return response_error('SSO用户校验失败，请重试！', $e->getMessage());
        }

        return $next($request);
    }

}