<?php

namespace Phoenix;

include '../src/crm_init.php';

$redirecturl = getDetailPageHeader( 'page.php?id=7', 'Furniture', 'Furniture' );

if ( isset( $_GET['add'] ) ) { //add a new customer
    //Add Customer Form
    ?>
    <form id='furniture_form' class='detailform'>
        <table>
            <tr>
                <td><b>Name: </b><input type='text' class='form-control w300' name='name' autocomplete='off' value=''/>
                </td>
            </tr>
        </table>
        <input type='submit' value='Add' class='btn btn-default' id='addbtn'>
    </form>
    <?php
} else {


    //view existing customer
    $furnitureID = ph_validate_number( $_GET['id'] );
    $furnitureRow = PDOWrap::instance()->getRow( 'furniture', array('ID' => $furnitureID) );


    if ( $furnitureRow !== false ) {

        //Customer details
        ?>
        <form id='furniture_form' class='detailform'>
            <table>
                <tr>
                    <td><b>ID: </b><input type='text' class='form-control viewinputp w300' name='ID' autocomplete='off'
                                          value='<?php echo $furnitureRow[' ID']; ?>'/>
                    </td>
                </tr>
                <tr>
                    <td><b>Name: </b><input type='text' class='form-control viewinput w300' name='name'
                                            autocomplete='off' value='<?php echo $furnitureRow[' name']; ?>'/>
                    </td>
                </tr>
            </table>
            <input type='submit' value='Update' class='btn btn-default' id='updatebtn'>
        </form>';
        <?php
    } else {
        echo 'no result';
    }
}
getDetailPageFooter( '#furniture_form', 'furniture', 'page.php?id=1' );
