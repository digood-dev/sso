<?php

namespace Digood\Sso\Services;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class SsoPatService
{
    /**
     * 获取AccessToken元数据
     * @param string $pat_token
     * @return false|mixed|void
     * @throws \Exception
     */
    public function getAccessTokenRaw(string $pat_token)
    {
        $cacheKey = 'pat_get_accesstoken_' . md5($pat_token);
        if (Cache::has($cacheKey)) return Cache::get($cacheKey);// 此PAT token已校验通过

        $params = [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:token-exchange',
            'client_id' => config('sso.digood.appId'),
            'client_secret' => config('sso.digood.appSecret'),
            'subject_token' => $pat_token,
            'subject_token_type' => 'urn:logto:token-type:personal_access_token',
            'scope' => implode(' ', config('sso.digood.scopes'))
        ];

        $result = Http::withoutVerifying()
            ->connectTimeout(10)
            ->timeout(30)
            ->asForm()
            ->baseUrl(config('sso.digood.endpoint'))
            ->post('/oidc/token', $params);

        if ($result->failed()) throw new \Exception($result->json('message', '用户Token交互失败，请检查配置！'));

        $data = $result->json();

        Cache::put($cacheKey, $result->json(), $result->json('expires_in') - 30);

        return $data;
    }

    /**
     * 获取AccessToken
     * @param string $pat_token
     * @return array|\ArrayAccess|mixed
     * @throws \Exception
     */
    public function getAccessToken(string $pat_token)
    {
        $raw = self::getAccessTokenRaw($pat_token);
        return Arr::get($raw, 'access_token');
    }

}