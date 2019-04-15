<?php include 'include/crm_init.php'; ?>
    <div class="row panel panel-default actsbtns">
        <div class="col-md-12 col-sm-12 col-xs-12">
            <h1>Choose Activity</h1></div>
        <?php

        $act_rows = get_rows( "activities" );

        foreach ( $act_rows as $act_row ) {
            $jid = ph_validate_number( $_GET[ 'jid' ] );
            if ( $jid != 0 ) {
                if ( ( $act_row[ 'ID' ] > 0 ) and ( $act_row[ 'ID' ] < 11 ) ) {
                    echo '<div class="col-md-3 col-sm-4 col-xs-6"><div class="btn main-btn"><a href="nextshift.php?jid=' . $jid . '&fid=' . $jid . '&aid=' . $act_row[ 'ID' ] . '">';
                    echo '<img src="' . $act_row[ 'image' ] . '"/>';
                    echo '<h2>' . $act_row[ 'name' ] . '</h2></a>';
                    echo '</div></div>';
                }
            } else {
                if ( $act_row[ 'ID' ] >= 11 ) {

                    if ( $act_row[ 'ID' ] >= 14 ) {
                        echo '<div class="col-md-3 col-sm-4 col-xs-6"><a href="othercomment.php?jid=' . $jid . '&aid=' . $act_row[ 'ID' ] . '"><div class="btn main-btn">';
                        echo '<img src="' . $act_row[ 'image' ] . '"/>';
                        echo '<h2>' . $act_row[ 'name' ] . '</h2>';
                        echo '</div></a></div>';
                    } else {
                        echo '<div class="col-md-3 col-sm-4 col-xs-6"><a href="nextshift.php?jid=' . $jid . '&aid=' . $act_row[ 'ID' ] . '"><div class="btn main-btn">';
                        echo '<img src="' . $act_row[ 'image' ] . '"/>';
                        echo '<h2>' . $act_row[ 'name' ] . '</h2>';
                        echo '</div></a></div>';
                    }
                }
            }
        }
        ?>
    </div>
<?php include 'include/footer.php' ?>