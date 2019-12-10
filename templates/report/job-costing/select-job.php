<h2>Records Of Times For Job Costing</h2>
<div class='panel panel-default' style='position: relative'>
    <?php if ( !empty( $jobs ) ) { ?>
        <h3>Choose Job: </h3>
        <form id='jcr_form' action='report.php' method='get' class='detailform'>
            <input type="hidden" name="report" value="jcr">
            <select class='form-control w300' name='job_id' autocomplete='off'>
                <?php foreach ( $jobs as $job ) {
                    echo '<option value="' . $job['ID'] . '">' . $job['description'] . "</option>";
                } ?>
            </select>
            <br>
            <input type='submit' value='Run Report' class='btn btn-default'>
        </form>
        <?php Phoenix\ph_get_template_part( 'report/job-costing/enter-job' );
    } else { ?>
        <h3>No jobs found for this customer</h3>
        <a href="report.php?report=jcr" class="btn btn-default">Select another customer</a>
    <?php } ?>
</div>