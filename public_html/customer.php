<?php include 'include/crm_init.php';
getdetailpageheader( "page.php?id=5", "Customers", "Customer" );
?>
    <form id='customer_form' class='detailform'><table>
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
if ( isset( $_GET[ 'add' ] ) ) { //add a new customer
    //Add Customer Form
    echo "<tr><td><b>Name: </b><input type='text' class='form-control w300' name='name' autocomplete='off' value=''/></td></tr>\n";
    echo "</table><input type='submit' value='Add' class='btn btn-default' id='addbtn'>";
    echo "</form>";
} else { //view existing customer
    $d_id = ph_validate_number( $_GET[ 'id' ] );
    $d_rows = get_rows( "customers", "WHERE ID = " . $d_id );
    $d_row = [];
    if ( $d_rows !== FALSE ) {
        $d_row = $d_rows[ 0 ];

        //Customer details
        echo "<tr><td><b>ID: </b><input type='text' class='form-control viewinputp w300' name='ID' autocomplete='off' value='" . $d_row[ 'ID' ] . "'/></td></tr>\n";
        echo "<tr><td><b>Name: </b><input type='text' class='form-control viewinput w300' name='name' autocomplete='off' value='" . $d_row[ 'name' ] . "'/></td></tr>\n";
        echo "</table><input type='submit' value='Update' class='btn btn-default' id='updatebtn'>";
        echo "</form>";

        //List of Jobs for customer
        echo "<h3>Jobs</h3>";
        $j_rows = get_rows_qry( "jc", [ $d_id ] );
        $cols = array_diff( get_columns_qry( "jc", [ $d_id ] ), array( 'customer' ) );
        echo generate_table( $cols, $j_rows, "jobs" );

    } else {
        echo "no result";
    }
}

getdetailpagefooter( "#customer_form", "customers", 'page.php?id=1' );