<?php

namespace Digood\Sso\Http\Middleware;

use Closure;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class SsoApiMiddleware
{
    public function handle($request, Closure $next)
    {
        $sso_user_token = $request->header('sso_user_token');// PAT Token
        if (empty($sso_user_token)) return response_error('缺少SSO Token，请重试');

        $cacheKey = md5($sso_user_token);
        if (Cache::has($cacheKey)) return $next($request);// 此PATtoken已校验通过

        try {
            $params = [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:token-exchange',
                'client_id' => config('sso.digood.appId'),
                'client_secret' => config('sso.digood.appSecret'),
                'subject_token' => $sso_user_token,
                'subject_token_type' => 'urn:logto:token-type:personal_access_token',
                'scope' => implode(' ', config('sso.digood.scopes'))
            ];

            $result = Http::withoutVerifying()
                ->connectTimeout(10)
                ->timeout(30)
                ->asForm()
                ->baseUrl(config('sso.digood.endpoint'))
                ->post('/oidc/token', $params);

            if ($result->failed()) return response_error('SSO用户Token交换失败，请重试', $result->json());

            Cache::put($cacheKey, $result->json(), $result->json('expires_in') - 30);

        } catch (ConnectionException $e) {
            return response_error('SSO用户校验失败，请重试');
        }

        return $next($request);
    }

}