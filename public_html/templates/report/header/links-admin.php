<div class="header_buttons">
    <a href="index.php">
        <div class="btn btn-default">◀ &nbsp; Main Menu</div>
    </a>
    <a href="report.php">
        <div class="btn btn-default">New Report</div>
    </a>
    <?php if ( !empty( $date_previous ) && $worker_id ) { ?>
        <a href="report.php?worker_id=<?php echo $worker_id; ?>&date_start=<?php echo $date_previous; ?>">
            <div class="btn btn-default">Previous Week</div>
        </a>
    <?php }
    if ( !empty( $date_next ) && $worker_id ) {
        if ( !( strtotime( $date_next ) > time() ) ) { ?>
            <a href="report.php?worker_id=<?php echo $worker_id; ?>&date_start=<?php echo $date_next; ?>">
                <div class="btn btn-default">Next Week</div>
            </a>
        <?php }
    } ?>
    <a class="btn btn-default" id="printbtn">Print</a>
</div>
