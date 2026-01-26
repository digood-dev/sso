<?php

use Digood\Sso\Services\SsoPatService;

if (!function_exists('sso_api_user_pat')) {
    /**
     * @param string $name
     * @return array|string|null
     */
    function sso_api_user_pat(string $name = 'sso-user-token'): array|string|null
    {
        return request()->header($name);
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