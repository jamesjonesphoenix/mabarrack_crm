<div class='row'>
    <div class='col-md-12 col-sm-12'>
        <h2>Job Summary</h2>
        <table class='report_summary_table nocolor'>
            <tbody>
            <tr>
                <td><h4>Total Time Taken (Hours):</h4></td>
                <td><h4><?php echo $totals[ 'total_recorded_time' ]; ?></h4></td>
            </tr>
            </tbody>
        </table>
        <table class='report_summary_table nocolor'>
            <thead>
            <tr>
                <th><h3>Item</h3></th>
                <th><h3>Value</h3></th>
                <th><h3>Percent of Sum Cost</h3></th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td><h4>Employee Cost:</h4></td>
                <td><h4><?php echo $totals[ 'employee_cost' ]; ?></h4></td>
                <td><h4><?php echo $totals[ 'percent_employee_cost' ]; ?></h4></td>
            </tr>
            <tr>
                <td><h4>Material Cost:</h4></td>
                <td><h4><?php echo $totals[ 'material_cost' ]; ?></h4></td>
                <td><h4><?php echo $totals[ 'percent_material_cost' ]; ?></h4></td>
            </tr>
            <tr>
                <td><h4>Contractor Cost:</h4></td>
                <td><h4><?php echo $totals[ 'contractor_cost' ]; ?></h4></td>
                <td><h4><?php echo $totals[ 'percent_contractor_cost' ]; ?></h4></td>
            </tr>
            <tr>
                <td><h4>Spare Cost:</h4></td>
                <td><h4><?php echo $totals[ 'spare_cost' ]; ?></h4></td>
                <td><h4><?php echo $totals[ 'percent_spare_cost' ]; ?></h4></td>
            </tr>
            <tr class="jcr-total_costs">
                <td><h4>Sum Costs:</h4></td>
                <td><h4><?php echo $totals[ 'total_cost' ]; ?></h4></td>
                <td><h4>100.0%</h4></td>
            </tr>
            <tr>
                <td><h4>Sale Price:</h4></td>
                <td><h4><?php echo $totals[ 'sale_price' ]; ?></h4></td>
            </tr>
            <tr>
                <td><h4>Total Profit:</h4></td>
                <td><h4><?php echo $totals[ 'total_profit' ]; ?></h4></td>
            </tr>
            <tr>
                <td><h4>Gross Margin:</h4></td>
                <td><h4><?php echo $totals[ 'gross_margin' ]; ?></h4></td>
            </tr>
            <tr>
                <td><h4>Markup:</h4></td>
                <td><h4><?php echo $totals[ 'markup' ]; ?></h4></td>
            </tr>
            </tbody>
        </table>
    </div>
</div>