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
    @inertia

        <div id="page-loading-indicator"
            style="height: 100vh; display: flex; align-items: center; justify-content: center;">
            <svg style="width: 150px; height: 150px;" class="floating-x text-bitter-theme-light" viewBox="0 0 512 512">
                <path fill="currentColor"
                    d="M484.112 27.889C455.989-.233 416.108-8.057 387.059 8.865 347.604 31.848 223.504-41.111 91.196 91.197-41.277 223.672 31.923 347.472 8.866 387.058c-16.922 29.051-9.1 68.932 19.022 97.054 28.135 28.135 68.011 35.938 97.057 19.021 39.423-22.97 163.557 49.969 295.858-82.329 132.474-132.477 59.273-256.277 82.331-295.861 16.922-29.05 9.1-68.931-19.022-97.054zm-22.405 72.894c-38.8 66.609 45.6 165.635-74.845 286.08-120.44 120.443-219.475 36.048-286.076 74.843-22.679 13.207-64.035-27.241-50.493-50.488 38.8-66.609-45.6-165.635 74.845-286.08C245.573 4.702 344.616 89.086 411.219 50.292c22.73-13.24 64.005 27.288 50.488 50.491zm-169.861 8.736c1.37 10.96-6.404 20.957-17.365 22.327-54.846 6.855-135.779 87.787-142.635 142.635-1.373 10.989-11.399 18.734-22.326 17.365-10.961-1.37-18.735-11.366-17.365-22.326 9.162-73.286 104.167-168.215 177.365-177.365 10.953-1.368 20.956 6.403 22.326 17.364z">
                </path>
            </svg>
        </div>
</body>

</html>
