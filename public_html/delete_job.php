<?php

namespace Phoenix;

include '../src/crm_init.php';

if (!isset($_GET['id'])) {
    ph_redirect('index');
}

$id = ph_validate_number($_GET['id']);
?>
    <div class='panel panel-default'>
        <div style="text-align: center;">
            <h1>Are you sure you want to delete job <?php echo $id; ?> ?</h1>
            <br><a href='remove_job.php?id=<?php echo $id; ?>' class='btn btn-default redbtn'><h3>Delete</h3>
            </a><br><br>
            <a href='job.php?id=<?php echo $id; ?>' class='btn btn-default'><h3>Cancel</h3></a>
        </div>
        <br>
    </div>
    <?php


?>

    <script>
        pagefunctions();
    </script>

    <?php ph_get_template_part('footer'); ?>