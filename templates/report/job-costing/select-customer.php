<?php namespace Phoenix; ?>
<h2>Records Of Times For Job Costing</h2>
<div class='panel panel-default' style='position: relative'>
    <?php if ( !empty( $customers ) ) { ?>
        <h3>Choose Customer: </h3>
        <form id='jcr_form' action='report.php' method='get' class='detail-form'>
            <input type="hidden" name="report" value="jcr">
            <select class='form-control w300' name='customer_id' autocomplete='off'>
                <?php foreach ( $customers as $customer ) {
                    echo '<option value="' . $customer[ 'ID' ] . '">' . $customer[ 'name' ] . "</option>";
                } ?>
            </select>
            <br>
            <input type='submit' value='Next' class='btn btn-default'>
        </form>
        <?php getTemplatePart( 'report/job-costing/enter-job' ); ?>
    <?php } ?>
</div>