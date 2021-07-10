<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0" />

    <meta name="base-url" content="{{ url('/') }}" />
    {{-- minutes to microseconds --}}
    <meta name="session-lifetime-seconds"
        content="{{ Config::get('session.lifetime') * 60000 }}" />

    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="manifest" href="/site.webmanifest">
    <link rel="mask-icon" href="/safari-pinned-tab.svg" color="#5bbad5">
    <meta name="msapplication-TileColor" content="#da532c">
    <meta name="theme-color" content="#ffffff">

    <link href="https://fonts.googleapis.com/css2?family=Krub:ital,wght@0,300;0,400;0,600;1,300;1,600&display=swap"
        rel="stylesheet">

    <link href="{{ mix('/css/app.css') }}" rel="stylesheet" />
    <script src="{{ mix('/js/app.js') }}" defer></script>
    @routes

    <style>
        .bg-test {
            background-image: url("{{ asset('image/test-bg.svg') }}");
            background-repeat: repeat;
        }

    </style>
</head>

<body
    class="m-0 font-krub font-light text-gray-600 {{ config('app.env') === 'dev' ? 'bg-test' : 'bg-soft-theme-light' }}">
    <div id="page-loading-indicator"
        style="height: 100vh; display: flex; align-items: center; justify-content: center;">
        <img
            style="width: 150px; height: 150px;"
            class="floating-x text-bitter-theme-light"
            src="{{ asset('/image/inhaler'.collect([1,2,3])->random().'.png') }}" />
    </div>
    @inertia
</body>

</html>
