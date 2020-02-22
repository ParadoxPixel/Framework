<html lang="en">
<head>
    <!-- ==============================================
		            Title and Meta Tags
	=============================================== -->
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ /*csrf_token()*/ 'Data' }}">
    <title>{{ env('NAME', 'CMS') }}</title>

    <!-- ==============================================
                         CSS Files
    =============================================== -->
    <link href="{{ asset('assets/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/panel.min.css') }}" rel="stylesheet">
</head>
<body>

<div class="page">
    <div class="form">
        <form method="post" action="{{ route('login') }}">
            @csrf()
            <img src="{{ asset('assets/img/logo-lg.png') }}" alt="IOByte">
            @if(isset($error))

            <div class="alert alert-danger" role="alert">
                {{ 'Invalid Email or Password' }}
                <a class="close" data-dismiss="alert" aria-label="close">&times;</a>
            </div>

            @endif
            <div class="input-group">
                <span class="input-group-addon"><i class="glyphicon glyphicon-envelope"></i></span>
                <input id="email" type="email" class="form-control" name="email" placeholder="Email" required autocomplete="email" autofocus>
            </div>
            <div class="input-group">
                <span class="input-group-addon"><i class="glyphicon glyphicon-lock"></i></span>
                <input id="password" type="password" class="form-control" name="password" placeholder="Password" required autocomplete="current-password">
            </div>

            <button class="btn btn-block btn-primary">{{ 'Login' }}</button>
        </form>
    </div>
</div>

<footer class="footer">
    <div class="container">
        <span>Copyright &copy; 2019 <a href="https://www.iobyte.nl/"><img src="{{ asset('assets/img/logo-lg.png') }}" alt="IOByte" style="height: 20px"></a>. All Rights Reserved.</span>
    </div>
</footer>

<!-- ==============================================
                      JS Files
=============================================== -->
<script src="{{ asset('assets/js/jquery.min.js') }}"></script>
<script src="{{ asset('assets/js/bootstrap.min.js') }}"></script>
</body>
</html>