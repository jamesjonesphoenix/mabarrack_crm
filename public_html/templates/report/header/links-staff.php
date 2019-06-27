<div class="header_buttons">
    <a href="w_enterjob.php">
        <div class="btn btn-default">◀ &nbsp; Back</div>
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
</div>

