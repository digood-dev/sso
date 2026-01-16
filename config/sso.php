<?php

return [
    'digood' => [
        'endpoint' => env('DIGOOD_SSO_ENDPOINT', 'https://sso.digood.cn'),
        'appId' => env('DIGOOD_SSO_APPID'),
        'appSecret' => env('DIGOOD_SSO_APPSECRET'),
        'scopes' => env('DIGOOD_SSO_SCOPES', ['openid','profile', 'email', 'phone', 'username', 'picture', 'roles']),
    ]
];
