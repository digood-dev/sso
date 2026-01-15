<?php

namespace Digood\Sso\Http\Middleware;

use Closure;
use Digood\Sso\Services\SsoPatService;
use Illuminate\Support\Facades\Cache;

class SsoApiMiddleware
{
    public function handle($request, Closure $next)
    {
        $sso_user_token = $request->header('sso_user_token');// PAT Token
        if (empty($sso_user_token)) return response_error('缺少SSO Token，请重试');

        $cacheKey = md5($sso_user_token);
        if (Cache::has($cacheKey)) return $next($request);// 此PAT token已校验通过

        try {
            $access_token = (SsoPatService::class)->getAccessToken($sso_user_token);
            if (!empty($access_token)) return $next($request);

        } catch (\Exception $e) {
            return response_error('SSO用户校验失败，请重试！', $e->getMessage());
        }

        return response_error('SSO用户校验无法完成，请重试！');
    }

}