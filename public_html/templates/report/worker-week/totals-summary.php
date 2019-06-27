<div class='col-md-12 col-sm-12 col-xs-12'>
    <h2>Weekly Summary</h2>
    <table class='report_summary_table nocolor'>
        <thead>
        <tr>
            <th><h3>Item</h3></th>
            <th><h3>Hours</h3></th>
            <th><h3>Percent of Hours Paid</h3></th>
            <th><h3>Percent of Total Recorded</h3></th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td><h4>Total Value Adding:</h4></td>
            <td><h4><?php echo $totals[ 'time_value_adding' ]; ?></h4></td>
            <td><h4><?php echo $totals[ 'percent_paid_time_value_adding' ]; ?></h4></td>
            <td><h4><?php echo $totals[ 'percent_time_value_adding' ]; ?></h4></td>
        </tr>
        <tr>
            <td><h4>Total Non Chargeable:</h4></td>
            <td><h4><?php echo $totals[ 'time_non_chargeable' ]; ?></h4></td>
            <td><h4><?php echo $totals[ 'percent_paid_time_non_chargeable' ]; ?></h4></td>
            <td><h4><?php echo $totals[ 'percent_time_non_chargeable' ]; ?></h4></td>
        </tr>
        <tr class="jcr-total_costs">
            <td><h4>Total Recorded:</h4></td>
            <td><h4><?php echo $totals[ 'total_recorded_time' ]; ?></h4></td>
            <td><h4><?php echo $totals[ 'percent_paid_time_total_recorded' ]; ?></h4></td>
            <td><h4>100%</h4></td>
        </tr>
        <tr>
            <td><h4>Factory - Any Type:</h4></td>
            <td><h4><?php echo $totals[ 'time_factory' ]; ?></h4></td>
            <td><h4><?php echo $totals[ 'percent_paid_time_factory' ]; ?></h4></td>
            <td><h4><?php echo $totals[ 'percent_time_factory' ]; ?></h4></td>
        </tr>
        <tr>
            <td><h4>Factory - With Job Number:</h4></td>
            <td><h4><?php echo $totals[ 'time_factory_with_job_number' ]; ?></h4></td>
            <td><h4><?php echo $totals[ 'percent_paid_time_factory_with_job_number' ]; ?></h4></td>
            <td><h4><?php echo $totals[ 'percent_time_factory_with_job_number' ]; ?></h4></td>
        </tr>
        <tr>
            <td><h4>Factory - Without Job Number:</h4></td>
            <td><h4><?php echo $totals[ 'time_factory_without_job_number' ]; ?></h4></td>
            <td><h4><?php echo $totals[ 'percent_paid_time_factory_without_job_number' ]; ?></h4></td>
            <td><h4><?php echo $totals[ 'percent_time_factory_without_job_number' ]; ?></h4></td>
        </tr>
        <tr>
            <td><h4>Lunch:</h4></td>
            <td><h4><?php echo $totals[ 'time_lunch' ]; ?></h4></td>
            <td><h4><?php echo $totals[ 'percent_paid_time_lunch' ]; ?></h4></td>
            <td><h4><?php echo $totals[ 'percent_time_lunch' ]; ?></h4></td>
        </tr>
        <tr>
            <td><h4>Total To Be Paid:</h4></td>
            <td><h4><?php echo $totals[ 'time_paid' ]; ?></h4></td>
            <td><h4>100%</h4></td>
            <td><h4><?php echo $totals[ 'percent_time_paid' ]; ?></h4></td>
        </tr>
        </tbody>
    </table>
    <br>
</div>