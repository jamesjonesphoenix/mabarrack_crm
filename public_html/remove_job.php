<?php

namespace Phoenix;

include '../src/crm_init.php';


if (!isset($_GET['id'])) {
    ph_redirect('index');
}

$id = ph_validate_number($_GET['id']);

$sql = 'DELETE FROM jobs WHERE ID = ' . $id;
$connection = init_crmdb();
$qry = mysqli_query($connection, $sql);
if ($qry) { ?>

    <div class='panel panel-default'>
        <div style="text-align: center;">
            <h1>Job <?php echo $_GET['id']; ?> deleted</h1>
            <br>
        </div>
    </div>
    <?php
} else {
    $errorString = '<b>Error: ' . $sql . '<br>' . mysqli_error($connection) . '</b>';
    close_crmdb($connection);
    return $errorString;
}


?>
    <script>
        pagefunctions();
        setTimeout(function () {
            location.href = 'page.php?id=3';
        }, 600);
    </script>

    <?php ph_get_template_part('footer'); ?>