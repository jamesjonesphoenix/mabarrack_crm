<?php include 'include/crm_init.php';


if ( $ph_user->get_role() == 'staff' )
    $worker_id = $ph_user->get_id();
elseif ( $ph_user->get_role() == 'admin' ) {
    if ( !empty( $_GET[ 'worker_id' ] ) )
        $worker_id = ph_validate_number( $_GET[ 'worker_id' ] );
    if ( !empty( $_GET[ 'job_id' ] ) )
        $job_id = ph_validate_number( $_GET[ 'job_id' ] );
}


if ( empty( $worker_id ) && empty( $job_id ) && $ph_user->get_role() == 'admin' ) {
    if ( !empty( $_GET[ 'report' ] ) ) {

        ph_get_template_part( 'report/header/links-admin' );
        ph_messages()->display();
        if ( $_GET[ 'report' ] == 'jcr' ) {
            if ( isset( $_GET[ 'customer_id' ] ) ) {
                $customer_id = ph_validate_number( $_GET[ 'customer_id' ] );
                //kill $jobs = get_rows_qry( 'jc', [ $customer_id ] );
                $jobs = ph_pdo()->run( 'SELECT jobs.ID, jobs.date_started, jobs.date_finished, jobs.status, jobs.priority, jobs.customer, jobs.furniture, jobs.description, customers.name as customer
                FROM jobs
                INNER JOIN customers ON jobs.customer=customers.ID WHERE jobs.ID != 0 AND jobs.customer = ?', [ $customer_id ] )->fetchAll();
                $template_args = array( 'jobs' => $jobs );
                ph_get_template_part( 'report/job-costing/select-job', array( 'jobs' => $jobs ) );
            } else {
                $customers = ph_pdo()->get_rows(
                    'customers', 'all',
                    array( 'ID' => array( 'value' => 0, 'operator' => '!=' ) ) ); //"WHERE ID != 0"
                ph_get_template_part( 'report/job-costing/select-customer', array( 'customers' => $customers ) );
            }
        } elseif ( $_GET[ 'report' ] == 'wtr' ) {
            $workers = ph_pdo()->get_rows( 'users', array( 'ID', 'name' ), array( 'type' => 'staff' ) );
            ph_get_template_part( 'report/worker-week/select-worker', array( 'workers' => $workers, ) );
        }
    } else {
        ph_get_template_part( 'report/select-report' );
    }
} else {
    $total_mins = 0;
    $total_employee_cost = 0;
    $activities = new ph_Activities();
    if ( !empty( $job_id ) ) {
        /*
         * job costing report
         */
        $report = new ph_Report_Job_Costing( $job_id );
    } elseif ( !empty( $worker_id ) ) {
        /*
         * worker weekly time record
         */
        $date_start = !empty( $_GET[ 'date_start' ] ) ? $_GET[ 'date_start' ] : '';
        $report = new ph_Report_Worker_Week( $worker_id, $date_start );
    }
    ph_messages()->display();
    $report->output_report();
}

?>
    <script>
        pagefunctions();
        $( document ).ready( function () {
            pagefunctions();
            $( "th:first-child" ).trigger( "click" );
        } );
    </script>
<?php include 'include/footer.php'; ?>