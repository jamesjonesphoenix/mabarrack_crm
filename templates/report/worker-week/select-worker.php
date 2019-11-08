<h2>Weekly Worker Time Record</h2>
<div class='panel panel-default' style='position: relative'>
    <form id='wtr_form' method='get' class='detailform'>
        <h3>Choose Worker: </h3>
        <select class='form-control w300' name='worker_id' autocomplete='off'>
            <?php foreach ( $workers as $worker ) {
                echo '<option value="' . $worker[ 'ID' ] . '">' . $worker[ 'name' ] . "</option>";
            } ?>
        </select><br>
        <input type='submit' value='Run Report' class='btn btn-default'>
    </form>
</div>