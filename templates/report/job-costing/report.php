<?php namespace Phoenix; ?>

<h2>Records Of Times For Job Costing</h2>
<div class='panel panel-default' style='position: relative'>
    <?php if ( !empty( $job ) ) : ?>
        <div class='row'>
            <div class='col-md-12'><h3 style='float:left;'><?php echo $job[ 'customer' ]; ?> &nbsp;&nbsp;&nbsp;Job
                    No. <?php echo $job[ 'ID' ]; ?></h3>
                <h3 style='float:right; text-transform: none'><?php echo $job[ 'date_finished' ]; ?></h3>
            </div>
        </div>
    <?php endif;
    if ( !empty( $shifts ) ) : ?>
        <div class='row jcr'>
            <div class='col-md-12 col-sm-12'>
                <?php //output table of shifts
                echo generate_table( array( 'shift_ID', 'worker', 'W/ending', 'hours', 'activity', 'rate', 'line_item_cost' ), $shifts, "shifts" ); ?>
            </div>
        </div>
    <?php endif; ?>
    <?php if ( !empty( $activities_summary ) ) : ?>
        <h3>Activity Summary</h3>
        <?php echo generate_table( array( 'activity_ID', 'activity', 'activity_hours', '%_of_total_hours', 'activity_cost', '%_of_total_employee_cost' ), $activities_summary );
    endif;
    if ( !empty( $totals ) ) :
        ph_get_template_part( 'report/job-costing/totals-summary', array(
            'totals' => $totals,
        ) );
    endif;
    ?>


</div>