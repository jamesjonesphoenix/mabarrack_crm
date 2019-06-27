<?php include 'include/crm_init.php';
$redirecturl = getdetailpageheader( "page.php?id=7", "Furniture", "Furniture" );
if ( isset( $_GET[ 'add' ] ) ) { //add a new customer
    //Add Customer Form
    echo "<form id='furniture_form' class='detailform'><table>";
    echo "<tr><td><b>Name: </b><input type='text' class='form-control w300' name='name' autocomplete='off' value=''/></td></tr>\n";
    echo "</table><input type='submit' value='Add' class='btn btn-default' id='addbtn'>";
    echo "</form>";
} else { //view existing customer
    $d_id = ph_validate_number( $_GET[ 'id' ] );
    $d_rows = get_rows( "furniture", "WHERE ID = " . $d_id );
    $d_row = [];
    if ( $d_rows !== FALSE ) {
        $d_row = $d_rows[ 0 ];

        //Customer details
        echo "<form id='furniture_form' class='detailform'><table>";
        echo "<tr><td><b>ID: </b><input type='text' class='form-control viewinputp w300' name='ID' autocomplete='off' value='" . $d_row[ 'ID' ] . "'/></td></tr>\n";
        echo "<tr><td><b>Name: </b><input type='text' class='form-control viewinput w300' name='name' autocomplete='off' value='" . $d_row[ 'name' ] . "'/></td></tr>\n";
        echo "</table><input type='submit' value='Update' class='btn btn-default' id='updatebtn'>";
        echo "</form>";

    } else {
        echo "no result";
    }
}
getdetailpagefooter( "#furniture_form", "furniture", 'page.php?id=1' );
?>