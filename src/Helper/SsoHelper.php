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

if(!function_exists('sso_signin_sub_system_url')){
    /**
     * 生成进入子系统的URL
     * @param string $subSystemUrl
     * @return mixed
     */
    function sso_signin_sub_system_url(string $subSystemUrl): mixed
    {
        return route('sso.sign-in.by_token', ['redirect_to' => base64_encode($subSystemUrl)]);
    }

}
