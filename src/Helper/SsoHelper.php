<?php

use Digood\Sso\Services\SsoService;

if (!function_exists('sso_user_is_signIn')) {
    /**
     * 用户是否登录
     * @return bool
     */
    function sso_user_is_signIn(): bool
    {
        return (new SsoService())->isSignIn();
    }

}

if (!function_exists('sso_user_info')) {
    /**
     * 用户信息
     * @return array|bool
     */
    function sso_user_info(): array|bool
    {
        return (new SsoService())->getUserInfo();
    }
}

if (!function_exists('sso_user_display')) {
    function sso_user_display()
    {
        $userInfo = sso_user_info();
        return $userInfo['nickname'] ?? $userInfo['username'] ?? $userInfo['phone'] ?? $userInfo['email'] ?? $userInfo['id'] ?? false;

    }
}

if (!function_exists('sso_user_id')) {
    /**
     * 用户标识
     * @return string|false
     */
    function sso_user_id(): string|false
    {
        return (new SsoService())->getUserId();
    }
}

if (!function_exists('sso_user_roles')) {
    /**
     * 用户角色清单
     * @return array
     */
    function sso_user_roles(): array
    {
        return (new SsoService())->getUserRoles();
    }
}

if (!function_exists('sso_user_is_digood')) {
    /**
     * 用户是否为多谷内部员工
     * @return bool
     */
    function sso_user_is_digood(): bool
    {
        return (new SsoService())->isRoleDigood();
    }
}

if (!function_exists('sso_signin_sub_system_url')) {
    /**
     * 生成进入子系统的URL
     * @param string $subSystemUrl
     * @return string
     * @throws Exception
     */
    function sso_signin_sub_system_url(string $subSystemUrl): string
    {
        if (request()->session()->get('sso_login', false)) throw new \Exception('当前登录态来自其它系统流转，不支持继续流转至其它子系统');

        $token = (new SsoService())->client()->getAccessToken();
        if (empty($token)) throw new \Exception('无法提取的你TOKEN，可能未登录或者从其它系统流转的登录态');

        $host = parse_url($subSystemUrl, PHP_URL_HOST);

        return sprintf(
            'https://%s/sso/sign-in/by_token/%s?redirect_to=%s',
            $host, $token, base64_encode($subSystemUrl)
        );
    }

}
