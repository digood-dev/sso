<?php

namespace Digood\Sso\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SsoApiMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $pat = sso_api_user_pat();
        $cacheKey = md5($pat);
        if (Cache::has($cacheKey)) return $next($request);// 此PAT token已校验通过

        try {
            $accessToken = sso_api_user_access_token($pat);// 以PAT Token获取Access Token
            if (empty($accessToken)) return response_error('SSO 用户PAT校验失败，请重试！');// 验证失败

            Cache::put($cacheKey, true, now()->addMinutes(30));// 缓存Token的验证状态

        } catch (\Exception $e) {
            return response_error('SSO用户校验失败，请重试！', $e->getMessage());
        }

        return $next($request);
    }

}