<?php

namespace Digood\Sso\Services;

use Illuminate\Support\Facades\Log;
use Logto\Sdk\LogtoClient;
use Logto\Sdk\LogtoConfig;
use Logto\Sdk\LogtoException;
use Logto\Sdk\Oidc\OidcCore;

class SsoService
{
    protected string $appId, $appSecret, $endpoint;

    public function __construct()
    {
        $this->appId = config('oss.digood.appId');
        $this->appSecret = config('oss.digood.appSecret');
        $this->endpoint = config('oss.digood.endpoint');
    }

    /**
     * @return LogtoClient
     */
    public function client(): LogtoClient
    {
        return new LogtoClient(
            new LogtoConfig(
                endpoint: $this->endpoint,
                appId: $this->appId,
                appSecret: $this->appSecret,
                scopes: config('oss.digood.scopes')
            ),
        );
    }

    /**
     * @return bool
     */
    public function isSignIn(): bool
    {
        if (request()->hasSession() && request()->session()->get('oss_login', false)) return true;
        return self::client()->isAuthenticated();
    }

    public function getOidcCore()
    {
        return OidcCore::create(rtrim($this->endpoint, "/"));
    }

    /**
     * 登录链接
     * @param string|null $callbackUrl
     * @return string
     */
    public function getSignInUrl(string|null $callbackUrl = null): string
    {
        if (empty($callbackUrl)) $callbackUrl = route('sso.sign-in.callback');

        return self::client()->signIn($callbackUrl);
    }

    /**
     * 退出连接
     * @param string $returnUrl
     * @return string
     * @throws LogtoException
     */
    public function getSignOutUrl(string $returnUrl): string
    {
        return self::client()->signOut($returnUrl);
    }

    /**
     * 登录回调处理
     * @return void
     * @throws LogtoException
     */
    public function handleSignIn(): void
    {
        self::client()->handleSignInCallback();
    }

    /**
     * 获取用户信息
     * @param string|null $accessToken | 留空则读取默认
     * @return array|false
     */
    public function getUserInfo(string|null $accessToken = null): false|array
    {
        if (request()->hasSession() && request()->session()->has('oss_userinfo', false)) return request()->session()->get('oss_userinfo');

        try {
            if (empty($accessToken)) {// 本地用户资料
                $info = self::client()->getIdTokenClaims();// 本地令牌声明

            } else {// 远程用户资料
                $info = self::getOidcCore()->fetchUserInfo($accessToken);// 实时从端点获取用户信息
            }

            $data = [
                'id' => $info->sub,
                'email' => $info->email ?? null,
                'phone' => $info->phone_number ?? null,
                'username' => $info->username ?? null,
                'picture' => $info->picture ?? null,
                'extra' => $info->extra ?? null,
                'roles' => $info->roles ?? null,//角色
            ];

        } catch (LogtoException $e) {
            Log::error($e->getMessage());
        }

        return $data ?? false;
    }

    /**
     * 获取用户ID
     * @return string|bool
     */
    public function getUserId(): string|bool
    {
        return self::getUserInfo()['id'] ?? false;
    }

    /**
     * 获取用户角色
     * @return array
     */
    public function getUserRoles(): array
    {
        return self::getUserInfo()['roles'] ?? [];
    }

    /**
     * 判断用户角色
     * @param string $role
     * @return bool
     */
    public function isRole(string $role): bool
    {
        return in_array($role, self::getUserRoles());
    }

    /**
     * 判断用户是否是多谷员工
     * @return bool
     */
    public function isRoleDigood(): bool
    {
        $userCondition = [
            self::isRole('DigoodStaff'),
            self::isRole('多谷内部员工')
        ];

        return in_array(true, $userCondition);
    }
}