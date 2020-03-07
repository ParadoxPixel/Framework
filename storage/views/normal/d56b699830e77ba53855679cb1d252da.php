<html lang="en">
<head>
    <!-- ==============================================
		            Title and Meta Tags
	=============================================== -->
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo /*csrf_token()*/ 'Data'; ?>">
    <title><?php echo env('NAME', 'CMS'); ?></title>

    <!-- ==============================================
                         CSS Files
    =============================================== -->
    <link href="<?php echo asset('assets/css/bootstrap.min.css'); ?>" rel="stylesheet">
    <link href="<?php echo asset('assets/css/panel.min.css'); ?>" rel="stylesheet">
</head>
<body>

<div class="page">
    <div class="form">
        <form method="post" action="<?php echo route('login'); ?>">
            <input type="text" name="X-CSRF" style="display: none" value="<?php echo session()->get('X-CSRF'); ?>">

            <img src="<?php echo asset('assets/img/logo-lg.png'); ?>" alt="IOByte">
            <?php if(isset($error)) { ?>

            <div class="alert alert-danger" role="alert">
                <?php echo 'Invalid Email or Password'; ?>
                <a class="close" data-dismiss="alert" aria-label="close">&times;</a>
            </div>

            <?php } ?>
            <div class="input-group">
                <span class="input-group-addon"><i class="glyphicon glyphicon-envelope"></i></span>
                <input id="email" type="email" class="form-control" name="email" placeholder="Email" required autocomplete="email" autofocus>
            </div>
            <div class="input-group">
                <span class="input-group-addon"><i class="glyphicon glyphicon-lock"></i></span>
                <input id="password" type="password" class="form-control" name="password" placeholder="Password" required autocomplete="current-password">
            </div>

            <button class="btn btn-block btn-primary"><?php echo 'Login'; ?></button>
        </form>
    </div>
</div>

<footer class="footer">
    <div class="container">
        <span>Copyright &copy; 2019 <a href="https://www.iobyte.nl/"><img src="<?php echo asset('assets/img/logo-lg.png'); ?>" alt="IOByte" style="height: 20px"></a>. All Rights Reserved.</span>
    </div>
</footer>

<!-- ==============================================
                      JS Files
=============================================== -->
<script src="<?php echo asset('assets/js/jquery.min.js'); ?>"></script>
<script src="<?php echo asset('assets/js/bootstrap.min.js'); ?>"></script>
</body>
</html>