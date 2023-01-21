<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Stocks Trade</title>

    <link rel="shortcut icon" href="acfev.ico" type="image/x-icon">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css"
    integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N" crossorigin="anonymous">
    <script src="https://use.fontawesome.com/releases/v6.1.0/js/all.js" crossorigin="anonymous"></script>
    <link
    href="https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,200;0,400;0,500;0,600;0,700;1,200;1,300;1,400;1,600&display=swap"
    rel="stylesheet">


    <!-- Custom styles for this template -->
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">
    @yield('css')
</head>

<body class="bg-light">
    <div id="app">
    <div class="overlay"></div>
    @include('layouts.nav')

    <main role="main" class="bg-lighterblue">
        {{-- @include('common.user-notify') --}}
        @yield('content')
    </main>
    <footer class="py-5 bg-blue text-white">
    </footer>
    </div>
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js"
    integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj"
    crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-Fy6S3B9q64WdZWQUiU+q4/2Lc9npb8tCaSX9FK7E8HnRr0Jz8D6OP9dO5Vg3Q9ct"
    crossorigin="anonymous"></script>
    <script src="{{ asset('js/app.js') }}"></script>
    @stack('scripts')

    @yield('js')

</body>

</html>
