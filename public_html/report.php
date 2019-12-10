<?php

namespace Phoenix;
include '../src/crm_init.php';


if ( $ph_user->role === 'staff' ) {
    $workerID = $ph_user->id;
} elseif ( $ph_user->role === 'admin' ) {
    if ( !empty( $_GET['worker_id'] ) ) {
        $workerID = ph_validate_number( $_GET['worker_id'] );
    }
    if ( !empty( $_GET['job_id'] ) ) {
        $jobID = ph_validate_number( $_GET['job_id'] );
    }
}

if ( empty( $workerID ) && empty( $jobID ) && $ph_user->role === 'admin' ) {
    if ( !empty( $_GET['report'] ) ) {

        ph_get_template_part( 'report/header/links-admin' );
        ph_messages()->display();
        if ( $_GET['report'] === 'jcr' ) {
            if ( isset( $_GET['customer_id'] ) ) {
                $customerID = ph_validate_number( $_GET['customer_id'] );
                //kill $jobs = getRowsQuery( 'jc', [ $customerID ] );
                $jobs = PDOWrap::instance()->run( 'SELECT jobs.ID, jobs.date_started, jobs.date_finished, jobs.status, jobs.priority, jobs.customer, jobs.furniture, jobs.description, customers.name as customer
                FROM jobs
                INNER JOIN customers ON jobs.customer=customers.ID WHERE jobs.ID != 0 AND jobs.customer = ?', [$customerID] )->fetchAll();
                $template_args = array('jobs' => $jobs);
                ph_get_template_part( 'report/job-costing/select-job', array('jobs' => $jobs) );
            } else {
                $customers = PDOWrap::instance()->getRows(
                    'customers',
                    array('ID' => array('value' => 0, 'operator' => '!=')) ); //"WHERE ID != 0"
                ph_get_template_part( 'report/job-costing/select-customer', array('customers' => $customers) );
            }
        } elseif ( $_GET['report'] === 'wtr' ) {
            $workers = PDOWrap::instance()->getRows( 'users', array('type' => 'staff'), array('ID', 'name') );
            ph_get_template_part( 'report/worker-week/select-worker', array('workers' => $workers,) );
        }
    } else {
        ph_get_template_part( 'report/select-report' );
    }
} else {
    $total_mins = 0;
    $totalEmployeeCost = 0;
    $activityFactory = new ActivityFactory( PDOWrap::instance(), Messages::instance() );
    $activities = $activityFactory->getActivities( [], true );


    if ( !empty( $jobID ) ) {
        /*
         * job costing report
         */
        $report = new Report\JobCosting( PDOWrap::instance(), Messages::instance(), $activities );
        $report->activities = $activities;
        $report->init( $jobID );
    } elseif ( !empty( $workerID ) ) {
        /*
         * worker weekly time record
         */
        $dateStart = !empty( $_GET['date_start'] ) ? $_GET['date_start'] : '';
        $report = new Report\WorkerWeek( PDOWrap::instance(), Messages::instance(), $activities );
        $report->activities = $activities;
        $report->init( $workerID, $dateStart );
    }
    ph_messages()->display();
    $report->outputReport();
}

?>
    <script>
        pagefunctions();
        $(document).ready(function () {
            pagefunctions();
            $("th:first-child").trigger("click");
        });
    </script>
    <?php ph_get_template_part( 'footer' ); ?>