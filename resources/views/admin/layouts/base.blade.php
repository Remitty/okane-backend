<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>Algorithmic.Cash - Admin</title>
    <link rel="apple-touch-icon" href="#">
    <link rel="shortcut icon" href="#">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css" integrity="sha384-TX8t27EcRE3e/ihU7zmQxVncDAy5uIKz4rEkgIXeMed4M0jlfIDPvg6uqKI2xXr2" crossorigin="anonymous">

    <link rel="stylesheet" href="{{ asset('main/vendor/DataTables/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('main/vendor/DataTables/Responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.1/css/jquery.dataTables.min.css">

    <link href="{{ asset('admin/css/styles.css') }}" rel="stylesheet" />
    <link href="{{ asset('admin/css/custom.css') }}" rel="stylesheet" />
    @yield('css')
    <script src="https://use.fontawesome.com/releases/v6.1.0/js/all.js" crossorigin="anonymous"></script>
    <link
        href="https://fonts.googleapis.com/css2?family=Lato:ital,wght@0,100;0,300;0,400;0,700;0,900;1,100;1,300;1,400;1,700;1,900&display=swap"
        rel="stylesheet">
</head>

<body class="sb-nav-fixed">
    <div id="admin">
        <nav class="sb-topnav navbar navbar-expand bg-white py-3 boxshadow">
            <!-- Navbar Brand-->
            <a class="navbar-brand ps-3" href="{{ route('admin.dashboard') }}">
                <strong>{{ Auth::guard('admin')->user()->roleLabel() }} </strong>
            </a>
            <!-- Sidebar Toggle-->
            <button class="btn btn-link topbar btn-sm order-1 order-lg-0 me-4 me-lg-0" id="sidebarToggle"
                href="#!"><i class="fas fa-bars"></i></button>
            <!-- Navbar Search-->
            <ul class="navbar-nav logout ms-auto me-0 me-md-3 my-2 my-md-0">
                <li class="nav-item dropdown"> <a class="nav-link dropdown-toggle topbar" id="navbarDropdown"
                        href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><svg
                            class="svg-inline--fa fa-user fa-fw" aria-hidden="true" focusable="false" data-prefix="fas"
                            data-icon="user" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"
                            data-fa-i2svg="">
                            <path fill="currentColor"
                                d="M224 256c70.7 0 128-57.31 128-128s-57.3-128-128-128C153.3 0 96 57.31 96 128S153.3 256 224 256zM274.7 304H173.3C77.61 304 0 381.6 0 477.3c0 19.14 15.52 34.67 34.66 34.67h378.7C432.5 512 448 496.5 448 477.3C448 381.6 370.4 304 274.7 304z">
                            </path>
                        </svg><!-- <i class="fas fa-user fa-fw"></i> Font Awesome fontawesome.com --></a>
                    <ul class="dropdown-menu dropdown-menu-right boxshadow" aria-labelledby="navbarDropdown">
                        <li><a class="dropdown-item" href="{{ route('admin.logout') }}">Logout</a></li>
                    </ul>
                </li>
            </ul>
        </nav>
        <div id="layoutSidenav">
            @include('admin.layouts.nav')

            <div id="layoutSidenav_content">
                <main>
                    @include('common.modal-notify')
                    @yield('content')
                </main>
                <footer class="py-4 bg-light mt-auto">
                    <div class="container-fluid px-4">
                        <div class="row align-items-center justify-content-between small text-center">
                            <div class="text-muted">© {{ Auth::guard('admin')->user()->roleLabel() }}'s Panel</div>
                        </div>
                    </div>
                </footer>
            </div>
        </div>
    </div>
    <script src="{{ asset('js/app.js') }}"></script>
    <script src="{{ asset('js/jquery.3.5.0.min.js') }}"></script>
    <script>
        window.jQuery || document.write('<script src="../../assets/js/vendor/jquery-slim.min.js"><\/script>')
    </script>
    <script src="{{ asset('js/bootstrap.min.js') }}"></script>

    <script type="text/javascript" src="{{ asset('main/vendor/DataTables/js/jquery.dataTables.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('main/vendor/DataTables/js/dataTables.bootstrap4.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('main/vendor/DataTables/Responsive/js/dataTables.responsive.min.js') }}">
    </script>
    <script src="{{ asset('admin/js/scripts.js') }}"></script>
    <script>
        $('#notifymodal').modal('show') ;
    </script>
    @yield('js')
</body>

</html>
