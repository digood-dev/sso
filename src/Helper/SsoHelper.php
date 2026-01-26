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

if (!function_exists('sso_user_info_makeup')) {
    /**
     * @param array $info
     * @return array|null[]
     */
    function sso_user_info_makeup(array $info): array
    {
        return [
            'id' => $info['sub'] ?? null,
            'email' => $info['email'] ?? null,
            'phone' => $info['phone_number'] ?? null,
            'name' => $info['name'] ?? null,
            'username' => $info['username'] ?? null,
            'picture' => $info['picture'] ?? null,
            'extra' => $info['extra'] ?? null,
            'roles' => $info['roles'] ?? null,//角色
        ];
    }
}

if (!function_exists('sso_user_setup')) {
    /**
     * 手动设置用户信息
     * @param array $info
     * @return void
     */
    function sso_user_setup(array $info): void
    {
        request()->session()->put('sso_login', true);
        request()->session()->put('sso_userinfo', sso_user_info_makeup($info));
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
        return $userInfo['name'] ?? $userInfo['nickname'] ?? $userInfo['username'] ?? $userInfo['phone'] ?? $userInfo['email'] ?? $userInfo['id'] ?? false;

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

if (!function_exists('sso_go_sub_system_url')) {
    /**
     * 生成进入子系统的URL
     * @param string $redirect_to
     * @param string $name
     * @return string
     */
    function sso_go_sub_system_url(string $redirect_to, string $name = '子系统'): string
    {
        return route('sso.go.sub-system', ['redirect_to' => base64_encode($redirect_to), 'name' => $name]);
    }
}
