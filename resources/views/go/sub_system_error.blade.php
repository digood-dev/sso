<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>多谷SSO系统提示</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center p-4">
<div class="max-w-md w-full bg-white rounded-xl shadow-lg overflow-hidden border border-gray-200">
    <div class="p-6 text-center">
        <!-- 警告/信息图标 -->
        <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-yellow-100 mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-yellow-600" fill="none"
                 viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4.5c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"/>
            </svg>
        </div>

        <!-- 标题 -->
        <h2 class="text-xl font-semibold text-gray-900 mb-2">操作失败</h2>

        <!-- 说明文案 -->
        <p class="text-gray-600 mb-4">
            {{$title}}
        </p>

        <!-- 系统信息（可选，增强上下文） -->
        <div class="text-left bg-gray-50 rounded-lg p-3 mb-5 text-sm text-gray-700">
            <p class="font-bold">可能的原因：</p>
            @foreach($reasons as $reason)
                <p>- <span class="font-medium">{{$reason}}</p>
            @endforeach
        </div>

        <div class="text-left bg-gray-50 rounded-lg p-3 mb-5 text-sm text-gray-700">
            <p class="font-bold">尝试解决：</p>
            @foreach($solves as $solve)
                <p>* <span class="font-medium">{{$solve}}</p>
            @endforeach
        </div>

        <!-- 操作按钮 -->
        {{--        <div class="mt-4">--}}
        {{--            <button--}}
        {{--                type="button"--}}
        {{--                class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-md transition"--}}
        {{--                onclick="window.location.href='/'"--}}
        {{--            >--}}
        {{--                返回控制台--}}
        {{--            </button>--}}
        {{--        </div>--}}

        <!-- 附加提示（可选） -->
        <p class="mt-4 text-xs text-green-600">
            你正在申请访问其它子系统
        </p>
    </div>
</div>
</body>
</html>


