
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no" />
    <title>Messenger Application</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="auth_id" content="{{ auth()->user()->id }}">
    <meta name="id" content="" />
    <link rel="icon" type="image/png" href="{{ asset('frontend/images/favicon.png') }}">
    <link rel="stylesheet" href="{{ asset('frontend/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('frontend/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('frontend/css/slick.css') }}">
    <link rel="stylesheet" href="{{ asset('frontend/css/venobox.min.css') }}">
    <link rel="stylesheet" href="{{ asset('frontend/css/emojionearea.min.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.css">
    <link rel="stylesheet" href="{{ asset('frontend/css/spacing.css') }}">
    <link rel="stylesheet" href="{{ asset('frontend/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('frontend/css/responsive.css') }}">
    <link rel="stylesheet" href="{{ asset('https://unpkg.com/nprogress@0.2.0/nprogress.css') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/messenger.js'])
</head>

<body>

    <!--==================================
        Chatting Application Start
    ===================================-->
    @yield('messenger_content')
    <!--==================================
        Chatting Application End
    ===================================-->


    <!--jquery library js-->
    <script src="{{ asset('frontend/js/jquery-3.7.1.min.js') }}"></script>
    <!--bootstrap js-->
    <script src="{{ asset('frontend/js/bootstrap.bundle.min.js') }}"></script>
    <!--font-awesome js-->
    <script src="{{ asset('frontend/js/Font-Awesome.js') }}"></script>
    <script src="{{ asset('frontend/js/slick.min.js') }}"></script>
    <script src="{{ asset('frontend/js/venobox.min.js') }}"></script>
    <script src="{{ asset('frontend/js/emojionearea.min.js') }}"></script>
    <script src="https://unpkg.com/nprogress@0.2.0/nprogress.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!--main/custom js-->
    <script src="{{ asset('frontend/js/main.js') }}"></script>
    <script>
        var notyf = new Notyf({
            duration: 5000
        });
    </script>
    @stack('scripts')
</body>

</html>
