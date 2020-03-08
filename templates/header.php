<?php

namespace Phoenix;

$scriptFilename = ph_script_filename( '.php' ) ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title><?php echo ucfirst( $scriptFilename ) . ' - ' . SYSTEM_TITLE; ?></title>
        <link rel="stylesheet" type="text/css" href="css/styles.<?php echo ''; //'min.';
        ?>css">
        <link rel="stylesheet" type="text/css" href="css/datepicker.min.css">
        <link rel="stylesheet" type="text/css" href="css/fonts.css">
        <script type="text/javascript" src="js/jquery.min.js"></script>
        <script type="text/javascript" src="js/bootstrap.min.js"></script>
        <script type="text/javascript" src="js/jquery.tablesorter.js"></script>
        <script type="text/javascript" src="js/mousetrap.min.js"></script>
        <script type="text/javascript" src="js/mousetrap-global-bind.min.js"></script>
        <script type="text/javascript" src="js/functions.js"></script>
    </head>
<body class="<?php echo $scriptFilename; ?>">
    <?php
if ( $scriptFilename !== 'login' ) { ?>
    <header>
        <div class="container">
            <div class="row">
                <div class="col-md-9 col-sm-8 col-xs-11 logo_title py-3">
                    <img src="img/logo.png"/>
                    <h1 class='crm-title mb-0'><?php echo SYSTEM_TITLE; ?></h1>

                </div>
                <div class="col-md-3 col-sm-4 col-xs-1 py-3">
                    <div class="d-flex flex-row justify-content-end mb-2">
                        <div class="ml-2">
                            <a href='login.php?logout=true' class="btn btn-default logout">Log Out</a>
                        </div>

                        <?php
                        if ( CurrentUser::instance()->role === 'admin' ) {
                            ?>
                            <div class="ml-2"><?php
                            echo "<a href='settings.php' id='setbtn' class='btn btn-default'><img src='img/admin/settings.svg'></a>";
                            ?></div><?php
                        }
                        ?>
                    </div>
                    <div class="d-flex flex-row justify-content-end">
                        <span>Welcome <b>
                                <?php echo CurrentUser::instance()->name; ?>
                            </b></span>
                    </div>
                </div>
            </div>
        </div>
    </header>
    <?php
    ph_messages()->display();
} else { ?>
    <div class="container"><?php
}