<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>AutoCRUD - @yield('title')</title>
    <link rel="stylesheet" href="{{ asset('autocrud/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('autocrud/css/bootstrap-tagsinput.css') }}">
    <link rel="stylesheet" href="{{ asset('autocrud/css/jquery-ui.css') }}">
    <link rel="stylesheet" href="{{ asset('autocrud/css/jquery.dataTables.min.css') }}">
    <link rel="stylesheet" href="{{ asset('autocrud/css/bootstrap/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('autocrud/css/style.css') }}">
</head>
<body>



<nav class="navbar navbar-default">
    <div class="container-fluid nav-container">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand text-danger" href="#" style="color: #FB503B;">
                AutoCRUD
                {{--<img src="img/logo.png" class="img-responsive" alt="AutoCRUD">--}}
            </a>
        </div>

        <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
            <ul class="nav navbar-nav">

            </ul>
            <ul class="nav navbar-nav navbar-right">
                <li class="active"><a href="{{url('selectTable')}}">SINGLE TABLE <span class="sr-only">(current)</span></a></li>
                <li><a href="{{url('multiTableCRUD')}}">MULTIPLE TABLE</a></li>
                <li><a href="{{ url('multiTables') }}">DISPLAY DATA</a></li>
                {{--<li><a href="#">Link</a></li>--}}
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
<script src="{{ asset('autocrud/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('autocrud/js/custom.js') }}"></script>
<script type="text/javascript">
    localStorage.setItem("baseURL", "{{ URL::to('/') }}");
</script>
@yield('custom-js')
</html>