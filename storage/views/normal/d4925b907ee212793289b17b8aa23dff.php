<html lang="en">
<head>
    <!-- ==============================================
              Title and Meta Tags
    =============================================== -->
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="shortcut icon" href="<?php echo asset('favicon.ico'); ?>">
    <title>CMS</title>

    <!-- ==============================================
                       CSS Files
    =============================================== -->
    <link href="<?php echo asset('assets/css/bootstrap.min.css'); ?>" rel="stylesheet">
    <link href="<?php echo asset('assets/css/font-awesome.min.css'); ?>" rel="stylesheet">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.2/css/all.css">
    <link href="<?php echo asset('assets/css/AdminLTE.min.css'); ?>" rel="stylesheet">
    
    <link href="<?php echo asset('assets/css/skin.css'); ?>" rel="stylesheet">

    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>
<body class="hold-transition sidebar-mini">

<div class="wrapper">
    <header class="main-header">
        <a class="logo">
            <span class="logo-mini"><img src="<?php echo asset('assets/img/logo-sm.png'); ?>" alt="IOB"></span>
            <span class="logo-lg"><img src="<?php echo asset('assets/img/logo-lg.png'); ?>" alt="IOByte"></span>
        </a>

        <nav class="navbar navbar-static-top">
            <a class="sidebar-toggle" data-toggle="push-menu" role="button">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </a>

            <div class="navbar-custom-menu">
                <ul class="nav navbar-nav">
                    <li class="dropdown user user-menu">
                        <a class="dropdown-toggle" data-toggle="dropdown">
                            <img src="<?php echo asset('assets/img/no-profile.png'); ?>" class="user-image" alt="User Image">
                            <span class="hidden-xs"><?php echo \Fontibus\Facades\Auth::user()->first_name; ?></span>
                        </a>
                        <ul class="dropdown-menu">
                            <li class="user-header">
                                <img src="<?php echo asset('assets/img/no-profile.png'); ?>" alt="User Image">
                                <p>
                                    <?php echo  \Fontibus\Facades\Auth::user()->fullname(); ?><small><?php echo  \Fontibus\Facades\Auth::user()->email; ?></small>
                                </p>
                            </li>

                            <li class="user-footer">
                                <div class="col-xs-6 col-xs-offset-6">
                                    <a href="<?php echo route('logout'); ?>" class="btn btn-primary btn-block">Sign out</a>
                                </div>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </nav>
    </header>

    <aside class="main-sidebar">
        <section class="sidebar">
            <div class="user-panel">
                <div class="pull-left image">
                    <img src="<?php echo asset('assets/img/no-profile.png'); ?>" alt="User Image">
                </div>
                <div class="pull-left info">
                    <p><?php echo  \Fontibus\Facades\Auth::user()->first_name; ?></p>
                </div>
            </div>

            <ul class="sidebar-menu" data-widget="tree">
                <li class="header">NAVIGATION</li>
                <li class="active">
                    <a><i class="fas fa-tachometer-alt"></i> <span>Dashboard</span></a>
                </li>
            </ul>
        </section>
    </aside>

    <div class="content-wrapper">
        <section class="content-header">
            <h1><?php echo 'Dashboard'; ?></h1>
            <ol class="breadcrumb">
                <li><a><i class="fas fa-tachometer-alt"></i> Panel</a></li>
                <li class="active"><?php echo 'Dashboard'; ?></li>
            </ol>
        </section>

        <section class="content">
            <p>Content</p>
        </section>
    </div>

    <footer class="main-footer">
        <div class="pull-right hidden-xs">
            <b>Version</b> <?php echo env('APP_VERSION', '1.0'); ?>
        </div>
        <span>Copyright &copy; 2020 <a href="https://www.iobyte.nl/"><img src="<?php echo asset('assets/img/logo-lg.png'); ?>" alt="IOByte"></a>. All rights reserved.</span>
    </footer>

    <div class="control-sidebar-bg"></div>
</div>

<!-- ==============================================
                      JS Files
=============================================== -->
<script src="<?php echo asset('assets/js/jquery.min.js'); ?>"></script>
<script src="<?php echo asset('assets/js/bootstrap.min.js'); ?>"></script>
<script src="<?php echo asset('assets/js/adminlte.min.js'); ?>"></script>

</body>
</html>