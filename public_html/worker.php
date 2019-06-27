<?php include 'include/crm_init.php';
$redirecturl = getdetailpageheader( "page.php?id=6", "Workers", "Worker" );
if ( isset( $_GET[ 'add' ] ) ) { //add a new worker
    //Add Worker Form
    ?>
    <form id='worker_form' class='detailform'>
        <table>
            <input type='hidden' name='type' value='staff'/>
            <tr>
                <td><b>Name: </b><input type='text' class='form-control w300' name='name' autocomplete='off' value=''/>
                </td>
            </tr>
            <tr>
                <td><b>PIN: </b><input type='text' maxlength='4' class='form-control w300' name='pin' autocomplete='off'
                                       value=''/></td>
            </tr>
            <tr>
                <td><b>Password: </b><br>Workers do not require a password, however it's recommended you enter one as it will be used to increase session encryption.<input type='text' class='form-control w300' name='password' autocomplete='off' value=''/></td>
            </tr>
            <tr>
                <td><b>Rate: </b><input type='number' step='0.01' min='0' class='form-control w100' name='rate'
                                        autocomplete='off' value='0'/><span class='currencyinput'></span></td>
            </tr>
        </table>
        <input type='submit' value='Add' class='btn btn-default' id='addbtn'>
    </form>
    <?php
} else { //view existing worker
    $w_id = ph_validate_number( $_GET[ 'id' ] );
    $w_rows = get_rows( "users", "WHERE ID = " . $w_id );
    $w_row = [];
    if ( $w_rows !== FALSE ) {
        $w_row = $w_rows[ 0 ];

        //Worker Details Form
        echo "<form id='worker_form' class='detailform'><table>";
        echo "<tr><td><b>ID: </b><input type='text' class='form-control viewinputp w300' name='ID' autocomplete='off' value='" . $w_row[ 'ID' ] . "'/></td></tr>\n";
        echo "<tr><td><b>Name: </b><input type='text' class='form-control viewinput w300' name='name' autocomplete='off' value='" . $w_row[ 'name' ] . "'/></td></tr>\n";
        echo "<tr><td><b>Pin: </b><input type='text' maxlength='4' class='form-control viewinput w300' name='pin' autocomplete='off' value='" . $w_row[ 'pin' ] . "'/></td></tr>\n";
        echo "<tr><td><b>Rate: </b><input type='number' step='0.01' min='0' class='form-control viewinput w100' name='rate' autocomplete='off' value='" . $w_row[ 'rate' ] . "'/><span class='currencyinput'></span></td></tr>\n";
        echo "</table><input type='submit' value='Update' class='btn btn-default' id='updatebtn'>";
        echo "</form>";

        //List of Worker's Shifts
        echo "<h3>Shifts</h3>";
        $s_rows = get_rows_qry( "sq", [ 'worker', $w_id ] );
        if ( $s_rows !== FALSE ) {
            foreach ( $s_rows as $skey => $s_row ) {
                $a_rows = get_rows( "activities", "WHERE ID in (" . $s_row[ 'activity' ] . ")" );
                $a_str = "";
                foreach ( $a_rows as $a_row ) {
                    $a_str .= $a_row[ 'name' ];
                }
                $s_rows[ $skey ][ 'activity' ] = $a_str;
            }
        }

        $cols = array_diff( get_columns( "shifts", false ), array( 'worker', 'activity_values', 'activity_comments', 'furniture' ) );
        echo generate_table( $cols, $s_rows, "shifts" );
    } else {
        echo "no result";
    }
}
getdetailpagefooter( "#worker_form", "users", 'page.php?id=1' );