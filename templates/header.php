<?php

namespace Phoenix;

use Phoenix\Entity\CurrentUser;

$scriptFilename = getScriptFilename( '.php' ) ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title><?php echo ucfirst( $scriptFilename ) . ' - ' . SYSTEM_TITLE; ?></title>
        <link rel="stylesheet" type="text/css" href="css/styles.css">
        <link rel="stylesheet" type="text/css" href="css/datepicker.min.css">
        <link rel="stylesheet" type="text/css" href="css/fonts.css">
        <script type="text/javascript" src="js/jquery.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
        <script type="text/javascript" src="js/bootstrap.min.js"></script>
        <script type="text/javascript" src="js/jquery.tablesorter.js"></script>
        <script type="text/javascript" src="js/mousetrap.min.js"></script>
        <script type="text/javascript" src="js/mousetrap-global-bind.min.js"></script>
        <script type="text/javascript" src="js/jquery.matchHeight.js"></script>
        <script type="text/javascript" src="js/functions.js"></script>
    </head>
<body class="<?php echo $scriptFilename; ?>">

    <header class="mb-3">
        <div class="container">
            <?php if ( $scriptFilename !== 'login' ) { ?>
                <div class="row">
                    <div class="col-md-9 col-sm-8 col-xs-11 logo_title py-3">
                        <a href="index.php">
                            <img src="img/logo.png"/>
                            <h1 class='crm-title mb-0 text-decoration-none text-white'><?php echo SYSTEM_TITLE; ?></h1>
                        </a>
                    </div>
                    <div class="col-md-3 col-sm-4 col-xs-1 py-3">
                        <div class="d-flex flex-row justify-content-end mb-2">
                            <div class="ml-2">
                                <a href='login.php?logout=true' class="btn logout">Log Out</a>
                            </div>

                            <?php
                            if ( CurrentUser::instance()->role === 'admin' ) {
                                ?>
                                <div class="ml-2"><?php
                                echo "<a href='settings.php' id='settings-button' class='btn'><img src='img/admin/settings.svg'></a>";
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
                <?php
            } else { ?>
                <div class="row" style="text-align: center;">
                    <div class="col-md-12 logo_title py-3">
                        <img src="img/logo.png"/>
                        <h1 class='crm-title mb-0' style="text-align: center"><?php echo SYSTEM_TITLE; ?></h1>
                    </div>
                </div>
                <?php
            } ?>
        </div>
    </header>
    <?php if ( Messages::instance()->isMessage() ) { ?>
<?php } ?>