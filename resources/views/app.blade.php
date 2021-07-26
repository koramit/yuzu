<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0" />

    <meta name="base-url" content="{{ url('/') }}" />
    {{-- minutes to microseconds --}}
    <meta name="session-lifetime-seconds"
        content="{{ Config::get('session.lifetime') * 60000 }}" />

    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>🍊</text></svg>">

    {{-- <link href="https://fonts.googleapis.com/css2?family=Krub:ital,wght@0,300;0,400;0,500;0,600;1,300;1,600&display=swap"
        rel="stylesheet"> --}}

    @if(config('app.font_workaround'))
        {!! str_replace('http:', 'https:', app(Spatie\GoogleFonts\GoogleFonts::class)->load()->toHtml()); !!}
    @else
        @googlefonts
    @endif

    <link href="{{ asset(mix('/css/app.css')) }}" rel="stylesheet" />
    <script src="{{ asset(mix('/js/app.js')) }}" defer></script>
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
