<?php include 'include/crm_init.php'; ?>
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default container">
                <h1>Description of Work</h1>
                <center>
                    <form action="nextshift.php" class="detailform" method="get">
                        <input type="hidden" name="jid" value="<?php echo ph_validate_number( $_GET[ 'job_id' ] ); ?>">
                        <input type="hidden" name="aid" value="<?php echo ph_validate_number( $_GET[ 'activity_id' ] ); ?>">
                        <input type="text" class='form-control' name="comment" value="" autocomplete="off" autofocus>
                        <br>
                        <input type="submit" class='btn btn-default' value="Done">
                    </form>
                </center>
            </div>
        </div>
    </div>
<?php include 'include/footer.php' ?>