<!DOCTYPE html>
<html>
    <head>
        <title>Page Not Found.</title>

        <link href="https://fonts.googleapis.com/css?family=Lato:100" rel="stylesheet" type="text/css">
        <link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet">
        <link href="{{ asset('css/app.css') }}" rel="stylesheet">
        <link href="{{ asset('css/style.css') }}" rel="stylesheet">
        <style>
            html, body {
                height: 100%;
            }

            body {
                margin: 0;
                padding: 0;
                width: 100%;
                color: #B0BEC5;
                display: table;
                font-weight: 100;
                font-family: 'Lato', sans-serif;
            }

            .container {
                text-align: center;
                display: table-cell;
                vertical-align: middle;
            }

            .content {
                text-align: center;
                display: inline-block;
            }

            .title {
                font-size: 72px;
                margin-bottom: 40px;
            }
        </style>
    </head>
    <body class="bg-light">
        <div id="app">
        <div class="container">
            <div class="content">
                <div class="title">Page Not Found.</div>
            </div>
        </div>
        <footer class="py-5 bg-blue text-white">
            <div class="container">
                <div class="row my-3">
                    <div class="col-12">
                        <p class="text-center"> <a class="px-2" href="#">About Us</a> <a class="px-2"
                                href="#">Payments</a> <a class="px-2" href="#">Refund
                                Policy</a> <a class="px-2" href="#">Terms and Conditions</a> <a
                                class="px-2" href="#">Privacy Policy</a> </p>
                        <p class="text-center"><img src="{{ asset('img/cards.png') }}" class="img-fluid" /></p>
                        <p class="text-center">@2022 RUPEEFARM</p>
                    </div>
                </div>
            </div>
            <!-- /.container -->
        </footer>
        </div>
    </body>
</html>
