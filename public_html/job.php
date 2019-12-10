<?php

namespace Phoenix;

include '../src/crm_init.php';
$redirecturl = getDetailPageHeader( 'page.php?id=3', 'Jobs', 'Job' );

$customerFactory = new CustomerFactory(PDOWrap::instance(),Messages::instance());
$customers = $customerFactory->getAll();

$furnitureFactory = new FurnitureFactory(PDOWrap::instance(),Messages::instance());
$allFurniture = $furnitureFactory->getAll();

if ( isset( $_GET['add'] ) ) { //add a new job
    //add job form
    ?>
    <form id='job_form' class='detailform'>
        <table>
            <tr>
                <td><b>ID: </b><input type='text' class='form-control w100' name='ID' value=''/></td>
            <tr>
                <td><b>Priority: </b><select class='form-control w100' name='priority' autocomplete='off'>
                        <?php
                        $pts = array(1, 2, 3, 4);
                        foreach ( $pts as $pt ) {
                            if ( $pt == 4 ) {
                                echo '<option value="' . $pt . '" selected="selected">' . $pt . '</option>';
                            } else {
                                echo '<option value="' . $pt . '">' . $pt . '</option>';
                            }
                        }
                        ?>
                    </select></td>
            </tr>
            <tr>
                <td width=310><b>Started: </b><input type='date' class='form-control w300' name='date_started'
                                                     value='<?php echo date( 'd/m/Y' ); ?>' autocomplete='off'/></td>

                <td><b>Finished: </b><input type='date' class='form-control w300' name='date_finished' value=''
                                            autocomplete='off'/></td>
            <tr>
                <td><b>Customer: </b><select class='form-control' name='customer' autocomplete='off'>
                        <?php
                        foreach ( $customers as $customer ) {
                            echo '<option value="' . $customer->id . '">' . $customer->name . '</option>';
                        }
                        ?>
                    </select></td>
            </tr>

            <tr>
                <td colspan=2><b>Description: </b><textarea class='form-control' name='description'
                                                            autocomplete='off'></textarea><br></td>
            </tr>

            <tr>
                <td><b>Sale Price: </b><input type='number' step='0.01' min='0' class='form-control w200'
                                              name='sale_price'
                                              autocomplete='off' value='0'/><span class='currencyinput'></span></td>
            </tr>

            <tr>
                <td><b>Material Cost: </b><input type='number' step='0.01' min='0' class='form-control w200'
                                                 name='material_cost' autocomplete='off' value='0'/><span
                            class='currencyinput'></span></td>

                <td><b>Contractor Cost: </b><input type='number' step='0.01' min='0' class='form-control w200'
                                                   name='contractor_cost' autocomplete='off' value='0'/><span
                            class='currencyinput'></span></td>

                <td><b>Spare Cost: </b><input type='number' step='0.01' min='0' class='form-control w200'
                                              name='spare_cost'
                                              autocomplete='off' value='0'/><span class='currencyinput'></span></td>
            </tr>

            <tr>
                <td><b>Furniture</b></td>
            </tr>
            <tr class='furrow'>
                <td><select class='form-control w300 fur-name' autocomplete='off'>
                        <?php
                        foreach ( $allFurniture as $furniture ) {
                            echo '<option value="' . $furniture->id . '">' . ucfirst( $furniture->name ) . '</option>';
                        }
                        ?>
                    </select></td>

                <td>
                    <div class='w200'><input type='number' value='1' min='0' class='form-control w100 fur-qty'>
                    </div>
                </td>
            </tr>
            <tr>
                <td><input id='addfurbtn' class='btn btn-default' value='&plus;' type='button'></td>
            </tr>
        </table>
        <input type='submit' value='Add' class='btn btn-default' id='addbtn'>
    </form>
    <?php
} else { //view existing job

    $jobID = ph_validate_number( $_GET['id'] );
    $jobFactory = new JobFactory( PDOWrap::instance(), Messages::instance() );
    //$job = EntityFactory::instance()->getJob( $jobID );

    $job = $jobFactory->getJob( $jobID );

    if ( $job->exists ) {

        //job details form
        ?>
        <a href='delete_job.php?id=<?php echo $jobID; ?>' id='deletebtn' class='btn btn-default redbtn'>Delete</a>
        <form id='job_form' class='detailform'>
            <table>
                <tr>
                    <td>
                        <table>
                            <tr>
                                <td><b>ID: </b><input type='text' class='form-control viewinputp w100' name='ID'
                                                      value='<?php echo $job->id; ?>'/></td>
                                <td><b>Priority: </b><select class='form-control viewinput' name='priority'
                                                             autocomplete='off'>
                                        <?php
                                        $pts = array(1, 2, 3, 4);
                                        foreach ( $pts as $pt ) {
                                            $selected = $job->priority === $pt ? ' selected="selected"' : '';
                                            echo '<option value="' . $pt . '"' . $selected . '>' . $pt . '</option>';
                                        }
                                        ?>
                                    </select></td>
                            </tr>
                        </table>
                    </td>

                    <td><b>Status: </b><select class='form-control viewinput' id='jstatus' name='status'
                                               autocomplete='off'>
                            <?php
                            $jobStatuses = PDOWrap::instance()->getRows( 'settings', array('name' => array(
                                'value' => 'jobstat',
                                'operator' => 'LIKE')
                            ) );

                            foreach ( $jobStatuses as $jobStatus ) {
                                $selected = $job->status === $jobStatus['name'] ? ' selected="selected"' : '';
                                echo '<option value="' . $jobStatus['name'] . '"' . $selected . '>' . ucwords( str_replace( '_', ' ', $jobStatus['value'] ) ) . '</option>';
                            }
                            ?>
                <tr>
                    <td width=310><b>Started: </b><input type='date' class='form-control viewinput w300'
                                                         name='date_started'
                                                         value='<?php echo DateTime::validateDate( $job->dateStarted ); ?>'
                                                         autocomplete='off'/></td>
                    <td><b>Finished: </b><input type='date' class='form-control viewinput w300' name='date_finished'
                                                value='<?php echo DateTime::validateDate( $job->dateFinished ); ?>'
                                                autocomplete='off'/></td>


                <tr>
                    <td><b>Customer: </b><select class='form-control viewinput' name='customer' autocomplete='off'>
                            <?php
                            foreach ( $customers as $customer ) {
                                $selected = $customer->id === $job->customer ? ' selected="selected"' : '';
                                echo '<option value="' . $customer->id . '"' . $selected . '>' . $customer->name . '</option>';
                            }
                            ?>
                        </select></td>
                </tr>

                <tr>
                    <td colspan=2><b>Description: </b><textarea class='form-control viewinput' name='description'
                                                                autocomplete='off'><?php echo $job->description; ?></textarea>
                    </td>
                </tr>

                <tr>
                    <td><b>Sale Price: </b><input type='number' step='0.01' min='0' class='form-control viewinput w200'
                                                  name='sale_price' autocomplete='off'
                                                  value='<?php echo $job->salePrice; ?>'/><span
                                class='currencyinput'></span></td>
                </tr>

                <tr>
                    <td><b>Material Cost: </b><input type='number' step='0.01' min='0'
                                                     class='form-control viewinput w200'
                                                     name='material_cost' autocomplete='off'
                                                     value='<?php echo $job->materialCost; ?>'/><span
                                class='currencyinput'></span></td>

                    <td><b>Contractor Cost: </b><input type='number' step='0.01' min='0'
                                                       class='form-control viewinput w200'
                                                       name='contractor_cost' autocomplete='off'
                                                       value='<?php echo $job->contractorCost; ?>'/><span
                                class='currencyinput'></span></td>

                    <td><b>Spare Cost: </b><input type='number' step='0.01' min='0' class='form-control viewinput w200'
                                                  name='spare_cost' autocomplete='off'
                                                  value='<?php echo $job->spareCost; ?>'/><span
                                class='currencyinput'></span></td>
                </tr>

                <tr>
                    <td><b>Furniture</b></td>
                </tr>
                <?php
                foreach ( $job->furniture as $jobFurniture ) {
                    ?>
                    <tr class='furrow'>
                        <td><select class='form-control viewinput w300 fur-name' autocomplete='off'>
                                <?php
                                foreach ( $allFurniture as $furniture ) {
                                    $selected = $furniture->id === $jobFurniture->id ? ' selected="selected"' : '';
                                    echo '<option value="' . $furniture->id . '"' . $selected . '>' . ucfirst( $furniture->name ) . '</option>';
                                }
                                ?>
                            </select>
                        </td>
                        <td>
                            <div class='w200'><input type='number' min='0'
                                                     value='<?php echo $jobFurniture->quantity; ?>'
                                                     class='form-control viewinput w100 fur-qty'>
                                <input class='btn btn-default viewinput removefur redbtn' value='&minus;' type='button'>
                            </div>
                        </td>
                    </tr>
                    <?php
                } ?>
                <tr>
                    <td><input id='addfurbtn' class='btn btn-default viewinput' value='&plus;' type='button'>
                    </td>
                </tr>

            </table>
            <input type='submit' value='Update' class='btn btn-default' id='updatebtn'>
        </form>
        <h3>Shifts</h3>
        <?php

        $shifts = $job->shifts;
        $shiftTableData = [];
        foreach ( $shifts as $shift ) {
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

    } else {
        echo 'no result';
    }

}
getDetailPageFooter( '#job_form', 'jobs', 'page.php?id=1' );
