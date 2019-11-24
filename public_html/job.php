<?php

namespace Phoenix;

include '../src/crm_init.php';
$redirecturl = getDetailPageHeader( 'page.php?id=3', 'Jobs', 'Job' );
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
                                echo '<option value="' . $pt . '" selected="selected">' . $pt . "</option>\n";
                            } else {
                                echo '<option value="' . $pt . '">' . $pt . "</option>\n";
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
                        $customerRows = PDOWrap::instance()->getRows( 'customers' );

                        foreach ( $customerRows as $customerRow ) {
                            echo '<option value="' . $customerRow['ID'] . '">' . $customerRow['name'] . "</option>\n";
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
                        $furnitures = PDOWrap::instance()->getRows( 'furniture' );
                        foreach ( $furnitures as $furniture ) {
                            echo '<option value="' . $furniture['ID'] . '">' . ucfirst( $furniture['name'] ) . "</option>\n";
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
    $jobRow = PDOWrap::instance()->getRow( 'jobs', array('ID' => $jobID) );
    if ( $jobRow !== false ) {
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
                                                      value='<?php echo $jobRow['ID']; ?>'/></td>
                                <td><b>Priority: </b><select class='form-control viewinput' name='priority'
                                                             autocomplete='off'>
                                        <?php
                                        $pts = array(1, 2, 3, 4);
                                        foreach ( $pts as $pt ) {
                                            if ( $jobRow['priority'] == $pt ) {
                                                echo '<option value="' . $pt . '" selected="selected">' . $pt . "</option>\n";
                                            } else {
                                                echo '<option value="' . $pt . '">' . $pt . "</option>\n";
                                            }
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
                                if ( $jobRow['status'] == $jobStatus['name'] ) {
                                    echo '<option value="' . $jobStatus['name'] . '" selected="selected">' . ucwords( str_replace( '_', ' ', $jobStatus['value'] ) ) . "</option>\n";
                                } else {
                                    echo '<option value="' . $jobStatus['name'] . '">' . ucwords( str_replace( '_', ' ', $jobStatus['value'] ) ) . "</option>\n";
                                }
                            }
                            ?>
                <tr>
                    <td width=310><b>Started: </b><input type='date' class='form-control viewinput w300'
                                                         name='date_started'
                                                         value='<?php echo DateTime::validate_date( $jobRow['date_started'] ); ?>'
                                                         autocomplete='off'/></td>
                    <td><b>Finished: </b><input type='date' class='form-control viewinput w300' name='date_finished'
                                                value='<?php echo DateTime::validate_date( $jobRow['date_finished'] ); ?>'
                                                autocomplete='off'/></td>
                    <?php
                    $customerRows = PDOWrap::instance()->getRows( 'customers' );

                    echo "<tr><td><b>Customer: </b><select class='form-control viewinput' name='customer' autocomplete='off'>\n";
                    foreach ( $customerRows as $customerRow ) {
                        if ( $customerRow['ID'] == $jobRow['customer'] ) {
                            echo '<option value="' . $customerRow['ID'] . '" selected="selected">' . $customerRow['name'] . "</option>\n";
                        } else {
                            echo '<option value="' . $customerRow['ID'] . '">' . $customerRow['name'] . "</option>\n";
                        }
                    }
                    ?>
                    </select></td></tr>

                <tr>
                    <td colspan=2><b>Description: </b><textarea class='form-control viewinput' name='description'
                                                                autocomplete='off'><?php echo $jobRow['description']; ?></textarea>
                    </td>
                </tr>

                <tr>
                    <td><b>Sale Price: </b><input type='number' step='0.01' min='0' class='form-control viewinput w200'
                                                  name='sale_price' autocomplete='off'
                                                  value='<?php echo $jobRow['sale_price']; ?>'/><span
                                class='currencyinput'></span></td>
                </tr>

                <tr>
                    <td><b>Material Cost: </b><input type='number' step='0.01' min='0'
                                                     class='form-control viewinput w200'
                                                     name='material_cost' autocomplete='off'
                                                     value='<?php echo $jobRow['material_cost']; ?>'/><span
                                class='currencyinput'></span></td>

                    <td><b>Contractor Cost: </b><input type='number' step='0.01' min='0'
                                                       class='form-control viewinput w200'
                                                       name='contractor_cost' autocomplete='off'
                                                       value='<?php echo $jobRow['contractor_cost']; ?>'/><span
                                class='currencyinput'></span></td>

                    <td><b>Spare Cost: </b><input type='number' step='0.01' min='0' class='form-control viewinput w200'
                                                  name='spare_cost' autocomplete='off'
                                                  value='<?php echo $jobRow['spare_cost']; ?>'/><span
                                class='currencyinput'></span></td>
                </tr>

                <tr>
                    <td><b>Furniture</b></td>
                </tr>
                <?php
                $furnitureJSON = json_decode( $jobRow['furniture'], true );
                $furnitures = PDOWrap::instance()->getRows( 'furniture' );
                foreach ( $furnitureJSON as $key => $ff ) {
                    echo "<tr class='furrow'><td><select class='form-control viewinput w300 fur-name' autocomplete='off'>";
                    foreach ( $furnitures as $furniture ) {
                        $ffid = current( array_keys( $ff ) );
                        $ffq = reset( $ff );
                        if ( $furniture['ID'] == $ffid ) {
                            echo '<option value="' . $furniture['ID'] . '" selected="selected">' . ucfirst( $furniture['name'] ) . '</option>';
                        } else {
                            echo '<option value="' . $furniture['ID'] . '">' . ucfirst( $furniture['name'] ) . "</option>\n";
                        }
                    }
                    echo '</select></td>';

                    echo "<td><div class='w200'><input type='number' min='0' value='" . $ffq . "' class='form-control viewinput w100 fur-qty'>";
                    if ( $key !== 0 ) {
                        echo "<input class='btn btn-default viewinput removefur redbtn' value='&minus;' type='button'></div></td></tr>";
                    } else {
                        echo '</div></td></tr>';
                    }

                }
                ?>

                <tr>
                    <td><input id='addfurbtn' class='btn btn-default viewinput' value='&plus;' type='button'></td>
                </tr>

            </table>
            <input type='submit' value='Update' class='btn btn-default' id='updatebtn'>
        </form>
        <h3>Shifts</h3>
        <?php
        $query = 'SELECT shifts.ID, shifts.job, shifts.worker, shifts.date, shifts.time_started, shifts.time_finished, shifts.minutes, shifts.activity, users.name as worker FROM shifts INNER JOIN users ON shifts.worker=users.ID WHERE job = ?';
        $shiftRows = PDOWrap::instance()->run( $query, [$jobID] )->fetchAll();
        if ( $shiftRows !== false ) {
            $activities = new Activities( PDOWrap::instance() );
            foreach ( $shiftRows as $shiftKey => $shiftRow ) {
                $shiftRows[$shiftKey]['activity'] = $activities->getDisplayName( $shiftRow['activity'] ) ?? '';
            }
        }
        echo generateTable( array('worker', 'date', 'time_started', 'time_finished', 'minutes', 'activity'), $shiftRows, 'shifts' );

    } else {
        echo 'no result';
    }
}
getDetailPageFooter( '#job_form', 'jobs', 'page.php?id=1' );