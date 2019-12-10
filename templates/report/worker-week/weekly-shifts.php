<?php
namespace Phoenix;
?>

<div class='panel panel-default'>
    <h1><?php echo $worker[ 'name' ]; ?></h1>
    <h3 style='float:right; text-transform: none'><?php echo $date_start; ?> to <?php echo $date_finish; ?></h3>
    <h2>Weekly Time Record</h2>
    <?php if ( CurrentUser::instance()->role === 'admin' ) : ?>
        <h3>Rate: $<?php echo $worker[ 'rate' ]; ?>/hour</h3>
    <?php
    endif;
    if ( !empty( $weekly_time_record ) ) : ?>
        <?php echo generateTable( array_keys( reset( $weekly_time_record ) ), $weekly_time_record, "shifts" );
    endif; ?>
    <?php if ( !empty( $time_clock_record ) ) : ?>
        <h2>Time Clock Record</h2>
        <?php
        echo generateTable( array( 'ID', 'day', 'date', 'start_time', 'finish_time', 'hours', 'total', 'lunch_start', 'lunch_finish' ), $time_clock_record );
    endif; ?>
    <br>
    <?php
    if ( !empty( $customer_hours ) ) : ?>
        <div class='row'>
            <div class='col-md-12 col-sm-12 col-xs-12'>
                <h4>Customer Hours</h4>
                <?php echo generateTable( array_keys( reset( $customer_hours ) ), $customer_hours, array(
                    array( 'table' => 'jobs', 'column' => 'job_ID' ),
                    array( 'table' => 'customers', 'column' => 'customer_ID' )
                ) ); ?>
            </div>
        </div>
    <?php endif; ?>
    <?php
    $activitiesColumns = array( 'activity_ID', 'activity', 'activity_hours', '%_of_hours_paid', '%_of_total_hours', 'activity_cost', '%_of_total_employee_cost' );
    //$activities_view_link = array( array( 'table' => 'activities', 'column' => 'activity_ID' ) );
    if ( !empty( $factory_hours_with_job_number ) ) : ?>
        <h4>Factory Hours (with job number)</h4>
        <?php
        echo generateTable( $activitiesColumns, $factory_hours_with_job_number );
    endif;
    if ( !empty( $factory_hours_no_job_number ) ) : ?>
        <h4>Factory Hours (without job number)</h4>
        <?php echo generateTable( $activitiesColumns, $factory_hours_no_job_number );
    endif;
    if ( !empty( $totals ) ) :
        ph_get_template_part( 'report/worker-week/totals-summary', array( 'totals' => $totals, ) );
    endif;
    ?>
</div>