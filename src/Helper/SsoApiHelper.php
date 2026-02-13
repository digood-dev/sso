<?php

use Digood\Sso\Services\SsoPatService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

if (!function_exists('sso_api_user_pat')) {
    /**
     * @param string $name
     * @return array|string|null
     */
    function sso_api_user_pat(string $name = 'sso-user-token'): array|string|null
    {
        $bearerToken = request()->bearerToken();// 标准的token值
        $headerToken = request()->header($name);// 自定义特有的token值
        return $bearerToken ?? $headerToken ?? null;
    }
}

if (!function_exists('sso_api_user_access_token')) {
    /**
     * @param string|null $pat
     * @return mixed
     * @throws Exception
     */
    function sso_api_user_access_token(string|null $pat = null): mixed
    {
        $pat = $pat ?: sso_api_user_pat();
        return (new SsoPatService())->getAccessToken($pat);
    }
}

if (!function_exists('sso_api_set_temporary_sign_in')) {
    /**
     * @param array $data
     * @return bool|string
     */
    function sso_api_set_temporary_sign_in(array $data): bool|string
    {
        $key = 'sso_api_temporary_sign_in_' . Str::orderedUuid()->toString();// 临时登录信息key
        return Cache::put(md5($key), $data, now()->addHour()) ? $key : false;
    }
}

if (!function_exists('sso_api_read_temporary_sign_in')) {
    /**
     * @param string|null $key
     * @return mixed
     */
    function sso_api_read_temporary_sign_in(string|null $key): mixed
    {
        return Cache::pull(md5($key));
    }

}