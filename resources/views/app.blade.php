<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="robots" content="noindex,nofollow">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'SteamTop')</title>

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}" defer></script>

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">

</head>

<body class="font-sans text-gray-900 bg-gray-300">
    <div id="app" class="p-8">

        <nav>
            <h1 class="text-4xl"><span class="font-black">Steam</span><span class="font-light">Top</span></h1>
        </nav>

        @yield('content')

    </div>

@yield('script')

</body>
</html>
