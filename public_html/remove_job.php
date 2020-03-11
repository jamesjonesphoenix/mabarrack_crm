<?php

namespace Phoenix;

include '../src/crm_init.php';


if ( !isset( $_GET['id'] ) ) {
    ph_redirect( 'index' );
}

$id = ph_validate_number( $_GET['id'] );

$result = PDOWrap::instance()->delete( 'jobs', array('ID' => $id) );

if ( $result ) { ?>

    <div class='panel panel-default'>
        <div style="text-align: center;">
            <h1>Job <?php echo $id; ?> deleted</h1>
            <br>
        </div>
    </div>
    <?php
} else {
    return 'Failed to remove job';
}


?>
    <script>
        pageFunctions();
        setTimeout(function () {
            location.href = 'page.php?id=3';
        }, 600);
    </script>

    <?php getTemplatePart( 'footer' ); ?>