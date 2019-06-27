<?php include 'include/crm_init.php'; ?>
    <div class="row main-btns">
        <?php
        $menuitems = get_rows( "main_menu", "" );

        if ( $menuitems !== FALSE ) {
            foreach ( $menuitems as $menuitem ) {
                echo '<div class="col-md-3 col-sm-4 col-xs-6"><a href="' . $menuitem[ 'url' ] . '"><div class="btn main-btn">';
                echo '<img src="' . $menuitem[ 'image' ] . '"/>';
                echo '<h2>' . $menuitem[ 'name' ] . '</h2>';

                if ( $menuitem[ 'notif_qry' ] != '' ) { //has notification query
                    $notif = get_notify_qry( $menuitem[ 'notif_qry' ] );

                    echo "<div class='notifs'>" . $notif . "</div>";

                }

                echo "</div></a></div>\n";
            }
        }

        ?>
    </div>
<?php
include 'include/footer.php';