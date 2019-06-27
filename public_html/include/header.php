<?php $script_filename = ph_script_filename( '.php' ) ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title><?php echo ucfirst( $script_filename ) . ' - ' . SYSTEM_TITLE; ?></title>
        <link rel="stylesheet" type="text/css" href="css/styles.min.css">
        <link rel="stylesheet" type="text/css" href="css/datepicker.min.css">
        <link rel="stylesheet" type="text/css" href="css/fonts.css">
        <script type="text/javascript" src="js/jquery.min.js"></script>
        <script type="text/javascript" src="js/bootstrap.min.js"></script>
        <script type="text/javascript" src="js/jquery.tablesorter.js"></script>
        <script type="text/javascript" src="js/mousetrap.min.js"></script>
        <script type="text/javascript" src="js/mousetrap-global-bind.min.js"></script>
        <script type="text/javascript" src="js/functions.js"></script>
    </head>
<body class="<?php echo $script_filename; ?>">
<?php
if ( $script_filename != "login" ) { ?>
    <div class="crmheadbg"></div>
    <div class="container">
    <div class="row crmhead">
    <div class="col-md-9 col-sm-8 col-xs-11 logo_title">
        <img src="img/logo.png"/>
        <h1 class='crmtitle'><?php echo SYSTEM_TITLE; ?></h1>
    </div>
    <div class="col-md-3 col-sm-4 col-xs-1">
        <a href='login.php?logout=true' class="btn btn-default logout">Log Out</a>
        <?php
        if ( $ph_user->get_role() == "admin" ) {
            echo "<a href='settings.php' id='setbtn' class='btn btn-default'><img src='img/settings.svg'></a>";
        }
        ?>
    </div>
    <?php echo "<div class='usrnm'><p>Welcome <b>" . $ph_user->get_name() . "</b></p></div></div>";
    ph_messages()->display();
} else { ?>
    <div class="container"><?php
}