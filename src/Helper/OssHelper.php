<?php

use Digood\Sso\Services\SsoService;
if(!function_exists('oss_user_is_signIn')){
    /**
     * 用户是否登录
     * @return bool
     */
    function oss_user_is_signIn(): bool
    {
        return (new SsoService())->isSignIn();
    }

}

if (!function_exists('oss_user_info')) {
    /**
     * 用户信息
     * @return array|bool
     */
    function oss_user_info(): array|bool
    {
        return (new SsoService())->getUserInfo();
    }
}

if (!function_exists('oss_user_id')) {
    /**
     * 用户标识
     * @return string|false
     */
    function oss_user_id(): string|false
    {
        return (new SsoService())->getUserId();
    }
}

if (!function_exists('oss_user_roles')) {
    /**
     * 用户角色清单
     * @return array
     */
    function oss_user_roles(): array
    {
        return (new SsoService())->getUserRoles();
    }
}

if (!function_exists('oss_user_is_digood')) {
    /**
     * 用户是否为多谷内部员工
     * @return bool
     */
    function oss_user_is_digood(): bool
    {
        return (new SsoService())->isRoleDigood();
    }
}
