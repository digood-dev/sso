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

        $tokenValue = $request->header($tokenKey);// PAT Token
        if (empty($tokenValue)) return response_error('SSO Token值为空');

        $cacheKey = md5($tokenValue);
        if (Cache::has($cacheKey)) return $next($request);// 此PAT token已校验通过

        try {
            // 以PAT Token获取Access Token
            $accessToken = (new SsoPatService())->getAccessToken($tokenValue);
            if (empty($accessToken)) return response_error('SSO 用户校验失败，请重试！');// 验证失败

            Cache::put($cacheKey, true, now()->addMinutes(30));// 缓存Token的验证状态

        } catch (\Exception $e) {
            return response_error('SSO用户校验失败，请重试！', $e->getMessage());
        }

        return $next($request);
    }

}