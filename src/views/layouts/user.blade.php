<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>AutoCRUD - @yield('title')</title>
    <link rel="stylesheet" href="{{ asset('autocrud/css/bootstrap/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('autocrud/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('autocrud/css/jquery.dataTables.min.css') }}">
    <link rel="stylesheet" href="{{ asset('autocrud/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('autocrud/css/bootstrap-tagsinput.css') }}">
    @yield('custom-css')
</head>
<body>



<nav class="navbar navbar-default">
    <div class="container-fluid">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            {{--your app name goes here--}}
            <a class="navbar-brand text-danger" href="#">AutoCRUD</a>
        </div>

        <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
            <ul class="nav navbar-nav">
                <li class="active"><a href="#">Link <span class="sr-only">(current)</span></a></li>
                <li><a href="#">Link</a></li>
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">Dropdown <span class="caret"></span></a>
                    <ul class="dropdown-menu" role="menu">
                        <li><a href="#">Action</a></li>
                        <li><a href="#">Another action</a></li>
                        <li><a href="#">Something else here</a></li>
                        <li class="divider"></li>
                        <li><a href="#">Separated link</a></li>
                        <li class="divider"></li>
                        <li><a href="#">One more separated link</a></li>
                    </ul>
                </li>
            </ul>
            <form class="navbar-form navbar-left" role="search">
                <div class="form-group">
                    <input type="text" class="form-control" placeholder="Search">
                </div>
                <button type="submit" class="btn btn-default">Submit</button>
            </form>
            <ul class="nav navbar-nav navbar-right">
                <li><a href="#">Link</a></li>
            </ul>
        </div>
    </div>
</nav>
@yield('content')
</body>

<script src="{{ asset('autocrud/js/jquery-3.2.1.min.js') }}"></script>
<script src="{{ asset('autocrud/js/bootstrap/bootstrap.min.js') }}"></script>
<script src="{{ asset('autocrud/js/select2.min.js') }}"></script>
<script src="{{ asset('autocrud/js/bootstrap-tagsinput.js') }}"></script>
<script src="{{ asset('autocrud/js/custom.js') }}"></script>
<script src="{{ asset('autocrud/js/jquery.dataTables.min.js') }}"></script>
@yield('custom-js')
</html>
