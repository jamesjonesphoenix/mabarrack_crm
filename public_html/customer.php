<?php

namespace Phoenix;

include '../src/crm_init.php';
getDetailPageHeader( 'page.php?id=5', 'Customers', 'Customer' );
?>
    <form id='customer_form' class='detailform'>
    <table>
    <?php

/*
if ( isset( $_GET[ 'add' ] ) ) //add a new customer
    $modifier = 'add';
else {
    $modifier = 'update';
}
    <form id='customer_form' class='detailform'>
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
    </table><input type='submit' value='Add' class='btn btn-default' id='addbtn'>
    </form>
    <?php

} else { //view existing customer

    $customerID = ph_validate_number( $_GET['id'] );
    if ( $customerID ) {
        $customerRow = PDOWrap::instance()->getRow( 'customers', array('ID' => $customerID) );

        if ( $customerRow !== false ) {
            //Customer details
            ?>
            <tr>
                <td><b>ID: </b><input type='text' class='form-control viewinputp w300' name='ID' autocomplete='off'
                                      value='<?php echo $customerRow['ID']; ?>'/></td>
            </tr>
            <tr>
                <td><b>Name: </b><input type='text' class='form-control viewinput w300' name='name' autocomplete='off'
                                        value='<?php echo $customerRow['name']; ?>'/></td>
            </tr>
            </table><input type='submit' value='Update' class='btn btn-default' id='updatebtn'>
            </form>
            <h3>Jobs</h3>
            <?php
            //List of Jobs for customer

            $query = 'SELECT jobs.ID, jobs.date_started, jobs.date_finished, jobs.status, jobs.priority, jobs.customer, jobs.furniture, jobs.description, customers.name
             as customer FROM jobs INNER JOIN customers ON jobs.customer=customers.ID WHERE jobs.ID != 0 AND jobs.customer = ?';

            $jobRows = PDOWrap::instance()->run( $query, [$customerID] )->fetchAll();
            $columns = array(
                'ID',
                'date_started',
                'date_finished',
                'status',
                'priority',
                'furniture',
                'description'
            );

            echo generateTable( $columns, $jobRows, 'jobs' );

        } else {
            echo 'no result';
        }
    }
}

getDetailPageFooter( '#customer_form', 'customers', 'page.php?id=1' );