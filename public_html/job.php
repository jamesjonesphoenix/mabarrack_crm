<?php

namespace Phoenix;

include '../src/crm_init.php';
//$redirectURL = getDetailPageHeader( 'page.php?id=3', 'Jobs', 'Job' );


$customerFactory = new CustomerFactory( PDOWrap::instance(), Messages::instance() );
$customers = $customerFactory->getAll();
$furnitureFactory = new FurnitureFactory( PDOWrap::instance(), Messages::instance() );
$allFurniture = $furnitureFactory->getAll();
$jobFactory = new JobFactory( PDOWrap::instance(), Messages::instance() );

if ( !empty( $_GET['id'] ) ) { //existing job
    $jobID = ph_validate_number( $_GET['id'] );

    $job = $jobFactory->getJob( $jobID );

    $title = 'Job Details';
    $formArgs = [
        'submit' => [
            'value' => 'Update',
            'id' => 'update-button'
        ]
    ];
    $disabled = true; //Safety feature to prevent accidentally editing existing job

    // d( $job->furniture );
    // if ( count( $job->furniture ) === 0 ) {}

} else { //new job
    $job = $jobFactory->getNewJob();

    $title = 'New Job';
    $formArgs = [
        'submit' => [
            'value' => 'Add',
            'id' => 'add-button'
        ]
    ];
}

$form = new Form();

$jobStatuses = PDOWrap::instance()->getRows( 'settings', array('name' => array(
    'value' => 'jobstat',
    'operator' => 'LIKE')
) );
$jobStatusOptions = array_column( $jobStatuses, 'value', 'name' );
$jobStatusOptionsDropdown = $form->optionDropdown(
    $jobStatusOptions, [
    'selected' => $job->status,
    'html' => ['id' => 'job-status', 'class' => $job->status, 'name' => 'status']
] );
$jobPriorityOptionsDropdown = $form->optionDropdown(
    array(1 => 1, 2 => 2, 3 => 3, 4 => 4), [
    'selected' => $job->priority,
    'html' => ['name' => 'priority']
] );
$customerOptions = array_column( $customers, 'name', 'id' );
$customerOptionsDropdown = $form->optionDropdown(
    $customerOptions, [
    'selected' => $job->customer->id,
    'html' => ['name' => 'customer']
] );
$furnitureOptions = array_column( $allFurniture, 'name', 'id' );
$furnitureOptionsDropdowns = [];
foreach ( $job->furniture as $jobFurniture ) {
    $furnitureOptionsDropdowns[$jobFurniture->id] = $form->optionDropdown(
        $furnitureOptions,
        [
            'selected' => $jobFurniture->id,
            'html' => [
                'class' => 'w300 furniture-name',
                'id' => empty( $firstFurnitureDropdownLooped ) ? 'inputFurniture' : '',
            ],
            'placeholder' => 'Select Furniture'
        ] );
    $firstFurnitureDropdownLooped = true;
}


?>
<div class="container">
    <div class="row pt-3 pb-2">
        <div class="col">
            <h2><?php echo $title; ?></h2>
        </div>
    </div>
</div>
<div class="container" style="position: relative">
    <div class="row grey-bg pt-3">
        <div class="col mb-3">
            <a type="button" class="btn btn-danger btn-lg mr-2" href="delete_job.php?id=<?php echo $jobID; ?>"
               id="deletebtn">Delete
            </a>
            <button type="button" id="edit-button" class="btn btn-primary btn-lg mr-2">Edit</button>
            <button type="button" id="cancel-button" class="btn btn-primary btn-lg btn-secondary mr-2">Cancel
            </button>
        </div>
    </div>
    <div class="row grey-bg">
        <div class="col mb-3">
            <form id='job_form' class='detail-form'>
                <fieldset<?php echo !empty( $disabled ) ? ' disabled' : ''; ?>>
                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label for="inputID">ID:</label>
                            <input type="text" class="form-control viewinput" id="inputID" placeholder="ID"
                                   name='ID'
                                   value="<?php echo $job->id; ?>">
                        </div>
                        <div class="form-group col-md-4">
                            <label for="inputPriority">Priority:</label>
                            <?php echo $jobPriorityOptionsDropdown; ?>
                        </div>
                        <div class="form-group col-md-4">
                            <label for="inputStatus">Status:</label>
                            <?php echo $jobStatusOptionsDropdown; ?>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="inputDateStarted">Date Started:</label>
                            <input type='date' class='form-control viewinput' id="inputDateStarted"
                                   name="date_started"
                                   value="<?php echo $job->dateStarted; ?>" autocomplete='off'/>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="inputDateFinished">Date Finished:</label>
                            <input type='date' class='form-control viewinput' id="inputDateFinished"
                                   name="date_finished"
                                   value="<?php echo $job->dateFinished; ?>"
                                   autocomplete='off'/>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="inputCustomer">Customer:</label>
                            <?php echo $customerOptionsDropdown; ?>
                            <div class="mt-3">
                                <label for="inputSalePrice">Sale Price:</label>
                                <input class='form-control viewinput' id="inputSalePrice" type='number' step='0.01'
                                       min='0'
                                       name='sale_price' autocomplete='off' value='<?php echo $job->salePrice; ?>'/>
                            </div>
                        </div>
                        <div class="form-group col-md-6 job-description">
                            <label for="inputDescription">Description:</label>
                            <textarea class="form-control viewinput" id="inputDescription" name="description"
                                      autocomplete="off"><?php echo $job->description; ?></textarea>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label for="inputMaterialCost">Material Cost:</label>
                            <input class='form-control viewinput' id="inputMaterialCost" type='number' step='0.01'
                                   min='0'
                                   name='material_cost' autocomplete='off'
                                   value='<?php echo $job->materialCost; ?>'/>
                        </div>
                        <div class="form-group col-md-4">
                            <label for="inputContractorCost">Contractor Cost:</label>
                            <input class='form-control viewinput' id="inputContractorCost" type='number' step='0.01'
                                   min='0'
                                   name='material_cost' autocomplete='off'
                                   value='<?php echo $job->contractorCost; ?>'/>
                        </div>
                        <div class="form-group col-md-4">
                            <label for="inputSpareCost">Spare Cost:</label>
                            <input class='form-control viewinput' id="inputSpareCost" type='number' step='0.01'
                                   min='0'
                                   name='material_cost' autocomplete='off' value='<?php echo $job->spareCost; ?>'/>
                        </div>
                    </div>
                    <div class="form-row job-furniture-row">
                        <?php
                        foreach ( $job->furniture as $furnitureID => $jobFurniture ) { ?>
                            <div class="form-group furniture-group col-sm-12">
                                <?php
                                if ( empty( $firstFurnitureLooped ) ) {
                                    echo '<label style="display:block;" for="inputFurniture">Furniture:</label>';
                                }
                                echo $furnitureOptionsDropdowns[$jobFurniture->id];
                                ?>
                                <input type="number" min="0"
                                       value="<?php echo $jobFurniture->quantity; ?>"
                                       class="form-control viewinput w100 furniture-quantity">
                                <a class="btn btn-default viewinput remove-furniture<?php
                                if ( empty( $firstFurnitureLooped ) ) {
                                    echo ' disabled';
                                }
                                ?>" type="button">&minus;</a>
                            </div>
                            <?php
                            $firstFurnitureLooped = true;
                        } ?>
                        <div class="form-group col-sm-12">
                            <input id="add-furniture-button" class="btn btn-primary viewinput"
                                   value="&plus; Add Furniture"
                                   type="button">
                        </div>
                    </div>
                    <div class="form-row mt-4">
                        <input type="submit" value="<?php echo $formArgs['submit']['value']; ?>"
                               class="btn btn-primary btn-lg"
                               id="<?php echo $formArgs['submit']['id']; ?>">
                    </div>
                </fieldset>
            </form>
        </div>
    </div>
    <?php

    if ( $job->exists && count( $job->shifts ) > 0 ) { ?>
        <div class="row grey-bg">
            <div class="col mt-3 mb-3">
                <h3>Shifts</h3><?php
                $shiftTableData = [];
                foreach ( $job->shifts as $shift ) {
                    $shiftTableData[] = [
                        'ID' => $shift->id, //needed for View shift button
                        'worker' => $shift->worker->name,
                        'date' => $shift->date,
                        'time_started' => $shift->timeStarted,
                        'time_finished' => $shift->timeFinished,
                        'minutes' => $shift->getShiftLength(),
                        'activity' => $shift->activity->displayName
                    ];
                }
                echo generateTable( array('worker', 'date', 'time_started', 'time_finished', 'minutes', 'activity'), $shiftTableData, 'shifts' );
                ?>
            </div>
        </div>
    <?php } ?>
</div>
<?php
getDetailPageFooter( '#job_form', 'jobs', 'page.php?id=1' );
?>
<script>
    jobDetailPageFunctions();
</script>
