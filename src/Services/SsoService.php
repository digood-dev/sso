<?php

namespace Digood\Sso\Services;

use Illuminate\Support\Facades\Cache;
use Logto\Sdk\Constants\DirectSignInMethod;
use Logto\Sdk\LogtoClient;
use Logto\Sdk\LogtoConfig;
use Logto\Sdk\LogtoException;
use Logto\Sdk\Models\DirectSignInOptions;
use Logto\Sdk\Oidc\OidcCore;

class SsoService
{
    protected string $appId, $appSecret, $endpoint;

    /**
     * @throws \Exception
     */
    public function __construct()
    {
        $this->appId = config('sso.digood.appId');
        $this->appSecret = config('sso.digood.appSecret');
        $this->endpoint = config('sso.digood.endpoint');
        $this->scopes = config('sso.digood.scopes', ['profile', 'email', 'phone', 'username', 'picture', 'roles']);

        if (empty($this->appId) || empty($this->appSecret)) throw new \Exception('Digood SSO 参数缺失，请检查！');
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
                scopes: $this->scopes,
            ),
        );
    }

    /**
     * @return bool
     */
    public function isSignIn(): bool
    {
        $isAPIRoute = in_array('api', request()->route()->computedMiddleware);

        if (request()->hasSession() && request()->session()->has('sso_userinfo') && !$isAPIRoute) {// 模拟登录，使用session
            return request()->session()->get('sso_login', false);

        } else if (request()->wantsJson() && $isAPIRoute) {// API请求，使用令牌
            $cacheKey = md5(sso_api_user_pat());
            if (Cache::has($cacheKey)) return true;// 此PAT token已校验通过

        }

        return self::client()->isAuthenticated();
    }

    /**
     * @return OidcCore
     */
    public function getOidcCore(): OidcCore
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
     * 登录连接（使用企业微信）
     * @param string|null $callbackUrl
     * @return string
     */
    public function getSignInByWeComUrl(string|null $callbackUrl = null): string
    {
        if (empty($callbackUrl)) $callbackUrl = route('sso.sign-in.callback');

        return self::client()->signIn(
            $callbackUrl,
            directSignIn: new DirectSignInOptions(
                method: DirectSignInMethod::social,
                target: 'wecom'
            )
        );
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
     * @return array|bool
     */
    public function getUserInfo(): array|bool
    {
        $isAPIRoute = in_array('api', request()->route()->computedMiddleware);

        if (request()->hasSession() && request()->session()->has('sso_userinfo') && !$isAPIRoute) {// 模拟登录，使用session
            return request()->session()->get('sso_userinfo');

        } else if (request()->wantsJson() && $isAPIRoute) {// API请求，使用令牌
            try {
                $tmpPAT = request()->header('sso-user-token');
                $tmpAccessToken = (new SsoPatService())->getAccessToken($tmpPAT);
                $info = self::getUserInfoByAccessToken($tmpAccessToken);// 拉取SSO用户信息
            } catch (\Exception $e) {
                return false;
            }

        } else if (self::client()->isAuthenticated()) {// 原生登录
            $info = self::getUserInfoByClaim();// 令牌声明

        } else {
            return false;
        }

        return sso_user_info_makeup($info);
    }

    /**
     * @return array
     */
    public function getUserInfoByClaim(): array
    {
        return self::client()->getIdTokenClaims()->jsonSerialize();// 本地令牌声明
    }

    /**
     * @param string $token
     * @return array
     */
    public function getUserInfoByAccessToken(string $token): array
    {
        return self::getOidcCore()->fetchUserInfo($token)->jsonSerialize();// 实时从端点获取用户信息
    }

    /**
     * 获取用户ID
     * @return string|bool
     */
    public function getUserId(): string|bool
    {
        return self::getUserInfo()['id'] ?? self::getUserInfo()['sub'] ?? false;
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