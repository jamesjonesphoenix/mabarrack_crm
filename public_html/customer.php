<?php

namespace Phoenix;

include '../src/crm_init.php';
getDetailPageHeader( 'page.php?id=5', 'Customers', 'Customer' );

?>
    <form id='customer_form' class='detail-form'>
    <table>
    <?php

/*
if ( isset( $_GET[ 'add' ] ) ) //add a new customer
    $modifier = 'add';
else {
    $modifier = 'update';
}
    <form id='customer_form' class='detail-form'>
        <table>
            <tr>
                <td><b>Name: </b><input type='text' class='form-control w300' name='name' autocomplete='off' value=''/>
                </td>
            </tr>
        </table>
        <input type='submit' value='<?php echo ucfirst( $modifier ); ?>' class='btn btn-default'
               id='<?php echo $modifier; ?>btn'>
    </form>
<?php
*/

if ( isset( $_GET['add'] ) ) { //add a new customer
    //Add Customer Form
    ?>
    <tr>
        <td><b>Name: </b><input type='text' class='form-control w300' name='name' autocomplete='off' value=''/></td>
    </tr>
    </table><input type='submit' value='Add' class='btn btn-default' id='add-button'>
    </form>
    <?php

} else { //view existing customer

    $customerID = ph_validate_number( $_GET['id'] );
    if ( $customerID ) {

        $factory = new CustomerFactory( PDOWrap::instance(), Messages::instance() );
        $customer = $factory->getCustomer( $customerID );

        //$customerRow = PDOWrap::instance()->getRow( 'customers', array('ID' => $customerID) );

        if ( $customer->exists ) {
            //Customer details
            ?>
            <tr>
                <td><b>ID: </b><input type='text' class='form-control viewinputp w300' name='ID' autocomplete='off'
                                      value='<?php echo $customer->id; ?>'/></td>
            </tr>
            <tr>
                <td><b>Name: </b><input type='text' class='form-control viewinput w300' name='name' autocomplete='off'
                                        value='<?php echo $customer->name; ?>'/></td>
            </tr>
            </table><input type='submit' value='Update' class='btn btn-default' id='update-button'>
            </form>
            <h3>Jobs</h3>
            <?php
            //List of Jobs for customer

            $columns = array(
                'ID',
                'date_started',
                'date_finished',
                'status',
                'priority',
                'furniture',
                'description'
            );

            $jobTableData = [];
            foreach ( $customer->jobs as $job ) {
                $jobTableData[] = [
                    'ID' => $job->id, //needed for View shift button
                    'date_started' => $job->dateStarted,
                    'date_finished' => $job->dateFinished,
                    'status' => $job->status,
                    'priority' => $job->priority,
                    'furniture' => $job->furniture,
                    'description' => $job->description
                ];
            }
            echo generateTable( $columns, $jobTableData, 'jobs' );
        } else {
            echo 'no result';
        }
    }
}
?>
</div>
<?php
getDetailPageFooter( '#customer_form', 'customers', 'page.php?id=1' );