<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Digood SSO 正在进入{{$name}}</title>
    <!-- 引入 Tailwind CSS（CDN 方式，仅用于快速演示） -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* 可选：微调加载动画 */
        .spinner {
            border: 3px solid rgba(0, 0, 0, 0.1);
            border-top: 3px solid #3b82f6;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }
            100% {
                transform: rotate(360deg);
            }
        }
    </style>
</head>
<body class="bg-gray-50 flex items-center justify-center min-h-screen">
<!-- 主提示区域 -->
<div class="text-center p-6 bg-white rounded-lg shadow-md max-w-md w-full">
    <div class="flex justify-center mb-4">
        <div class="inline-block h-6 w-6 animate-spin rounded-full border-2 border-solid border-blue-500 border-t-transparent"></div>
    </div>
    <h1 class="text-xl font-semibold text-gray-800">正在进入{{$name}}...</h1>
    <p class="text-gray-500 mt-2">请稍候，页面即将跳转。</p>
    <p class="text-gray-500 mt-2">你的登录状态将会无缝流转</p>
</div>

<!-- 底部说明文字-->
<p class="mt-6 text-xs text-gray-400">
    该服务由 <span class="font-mono">{{$host}}</span> 子系统提供
</p>

<script>
    setTimeout(() => {
        window.location.href = '{{$url}}';
    }, 2000);// 2秒后跳转
</script>
</body>
</html>