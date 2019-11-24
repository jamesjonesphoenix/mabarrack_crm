<?php

namespace Phoenix;

include '../src/crm_init.php';

echo getTable( 'depots');
?>
    <h4>Add Depot:</h4>
    <form action="add_depot.php" method="post" class="form-inline">
        ID: <input name="ID" type="number" class="form-control" data-validation="number">
        Name:<input name="name" type="text" class="form-control" data-validation="text">
        <input type="submit" value="Submit" class="btn btn-default">
    </form>
<?php
echo getTable( 'trucks');

?>
    <h4>Add Truck:</h4>
    <form action="add_truck.php" method="post" class="form-inline">
        Truck ID: <input name="ID" type="number" class="form-control" data-validation="number">
        Depot:<select name="depot" class="depots_dd form-control">
        </select>
        <input type="submit" value="Submit" class="btn btn-default">
    </form>
<?php
echo getTable(  'workers');
?>
    <h4>Add Worker:</h4>
    <form action="add_worker.php" method="post" class="form-inline">
        ID: <input name="ID" type="number" class="form-control" data-validation="number">
        Name:<input name="name" type="text" class="form-control" data-validation="text">
        <input type="submit" value="Submit" class="btn btn-default">
    </form>
<?php
echo getTable(  'jobs');
?>
    <h4>Add Job:</h4>
    <form action="add_job.php" method="post" class="form-inline">
        Start Date: <input name="date_started" type="date" class="form-control"/>
        Truck: <select name="truck" class="trucks_dd form-control"></select>
        Depot:<select name="depot" class="depots_dd form-control"></select>
        Description: <input name="description" type="text" class="form-control"/>
        <input type="submit" value="Submit" class="btn btn-default">
    </form>
<?php
echo getTable(  'shifts');
?>
    <h4>Add Shift:</h4>
    <form action="add_shift.php" method="post" class="form-inline">
        Job:<select name="job" class="jobs_dd form-control">
        </select>
        Worker:<select name="worker" class="workers_dd form-control">
        </select>
        Date: <input name="date" type="date" class="form-control"/>
        Start Time: <select name="time_started" class="time_dd form-control"></select>
        Finish Time: <select name="time_finished" class="time_dd form-control"></select>
        Description: <input name="description" type="text" class="form-control"/>
        <input type="submit" value="Submit" class="btn btn-default">
    </form>
    <br><br><br><br><br><br><br><br>

    <script>
        load_dropdown_options( ".depots_dd", "depots", "ID", "name" );
        load_dropdown_options( ".trucks_dd", "trucks", "ID", "ID" );
        load_dropdown_options( ".jobs_dd", "jobs", "ID", "description" );
        load_dropdown_options( ".workers_dd", "workers", "ID", "name" );
        load_time_dropdown();
        $( ":date" ).dateinput( {
            format: 'dd-mm-yyyy'
        } );
        $( ".table" ).tablesorter();
        $( ".idh" ).trigger( "click" );
        $.validate( {
            lang: 'en'
        } );
    </script>

<?php
ph_get_template_part('footer');
?>