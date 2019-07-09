<?php include 'include/crm_init.php';
$redirecturl = getdetailpageheader("page.php?id=3", "Jobs", "Job");
if (isset($_GET['add'])) { //add a new job
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
                        foreach ($pts as $pt) {
                            if ($pt == 4) {
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
                                                     value='<?php echo date("d/m/Y"); ?>' autocomplete='off'/></td>

                <td><b>Finished: </b><input type='date' class='form-control w300' name='date_finished' value=''
                                            autocomplete='off'/></td>
            <tr>
                <td><b>Customer: </b><select class='form-control' name='customer' autocomplete='off'>
                        <?php
                        $trows = get_rows('customers', "");//

                        foreach ($trows as $trow) {
                            echo '<option value="' . $trow['ID'] . '">' . $trow['name'] . "</option>\n";
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
                        $furs = get_rows("furniture", "");
                        foreach ($furs as $furn) {
                            echo '<option value="' . $furn['ID'] . '">' . ucfirst($furn['name']) . "</option>\n";
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
    $j_id = ph_validate_number($_GET['id']);
    $j_rows = get_rows("jobs", "WHERE ID = " . $j_id);
    $j_row = [];
    if ($j_rows !== FALSE) {
        $j_row = $j_rows[0];

        echo "<a href='delete_job.php?id=" . $j_id . "' id='deletebtn' class='btn btn-default redbtn'>Delete</a>";

        //job details form
        echo "<form id='job_form' class='detailform'><table>";
        echo "<tr><td><table><tr><td><b>ID: </b><input type='text' class='form-control viewinputp w100' name='ID' value='" . $j_row['ID'] . "'/></td>";
        echo "<td><b>Priority: </b><select class='form-control viewinput' name='priority' autocomplete='off'>";
        $pts = array(1, 2, 3, 4);
        foreach ($pts as $pt) {
            if ($j_row['priority'] == $pt) {
                echo '<option value="' . $pt . '" selected="selected">' . $pt . "</option>\n";
            } else {
                echo '<option value="' . $pt . '">' . $pt . "</option>\n";
            }
        }
        echo "</select></td></tr></table></td>\n";


        echo "<td><b>Status: </b><select class='form-control viewinput' id='jstatus' name='status' autocomplete='off'>\n";
        $sts = get_rows_qry("jstats", []);
        foreach ($sts as $st) {
            if ($j_row['status'] == $st['name']) {
                echo '<option value="' . $st['name'] . '" selected="selected">' . ucwords(str_replace("_", " ", $st['value'])) . "</option>\n";
            } else {
                echo '<option value="' . $st['name'] . '">' . ucwords(str_replace("_", " ", $st['value'])) . "</option>\n";
            }
        }

        echo "<tr><td width=310><b>Started: </b><input type='date' class='form-control viewinput w300' name='date_started' value='" . ph_DateTime::validate_date($j_row['date_started']) . "' autocomplete='off'/></td>\n";
        echo "<td><b>Finished: </b><input type='date' class='form-control viewinput w300' name='date_finished' value='" . ph_DateTime::validate_date($j_row['date_finished']) . "' autocomplete='off'/></td>\n";

        $trows = get_rows('customers', "");//
        echo "<tr><td><b>Customer: </b><select class='form-control viewinput' name='customer' autocomplete='off'>\n";
        foreach ($trows as $trow) {
            if ($trow['ID'] == $j_row['customer']) {
                echo '<option value="' . $trow['ID'] . '" selected="selected">' . $trow['name'] . "</option>\n";
            } else {
                echo '<option value="' . $trow['ID'] . '">' . $trow['name'] . "</option>\n";
            }
        }
        echo "</select></td></tr>";

        echo "<tr><td colspan=2><b>Description: </b><textarea class='form-control viewinput' name='description' autocomplete='off'>" . $j_row['description'] . "</textarea></td></tr>\n";

        echo "<tr><td><b>Sale Price: </b><input type='number' step='0.01' min='0' class='form-control viewinput w200' name='sale_price' autocomplete='off' value='" . $j_row['sale_price'] . "'/><span class='currencyinput'></span></td></tr>\n";

        echo "<tr><td><b>Material Cost: </b><input type='number' step='0.01' min='0' class='form-control viewinput w200' name='material_cost' autocomplete='off' value='" . $j_row['material_cost'] . "'/><span class='currencyinput'></span></td>\n";

        echo "<td><b>Contractor Cost: </b><input type='number' step='0.01' min='0' class='form-control viewinput w200' name='contractor_cost' autocomplete='off' value='" . $j_row['contractor_cost'] . "'/><span class='currencyinput'></span></td>\n";

        echo "<td><b>Spare Cost: </b><input type='number' step='0.01' min='0' class='form-control viewinput w200' name='spare_cost' autocomplete='off' value='" . $j_row['spare_cost'] . "'/><span class='currencyinput'></span></td></tr>\n";

        echo "<tr><td><b>Furniture</b></td></tr>";

        $fjson = json_decode($j_row['furniture'], true);
        $furs = get_rows("furniture", "");
        foreach ($fjson as $key => $ff) {
            echo "<tr class='furrow'><td><select class='form-control viewinput w300 fur-name' autocomplete='off'>\n";
            foreach ($furs as $furn) {
                $ffid = current(array_keys($ff));
                $ffq = reset($ff);
                if ($furn['ID'] == $ffid) {
                    echo '<option value="' . $furn['ID'] . '" selected="selected">' . ucfirst($furn['name']) . "</option>\n";
                } else {
                    echo '<option value="' . $furn['ID'] . '">' . ucfirst($furn['name']) . "</option>\n";
                }
            }
            echo "</select></td>";

            echo "<td><div class='w200'><input type='number' min='0' value='" . $ffq . "' class='form-control viewinput w100 fur-qty'>";
            if ($key != 0) {
                echo "<input class='btn btn-default viewinput removefur redbtn' value='&minus;' type='button'></div></td></tr>";
            } else {
                echo "</div></td></tr>";
            }

        }
        ?>

        <tr>
            <td><input id='addfurbtn' class='btn btn-default viewinput' value='&plus;' type='button'></td>
        </tr>

        </table><input type='submit' value='Update' class='btn btn-default' id='updatebtn'>
        </form>
        <h3>Shifts</h3>
        <?php
        $s_rows = get_rows_qry("sq", ['job', $j_id]);
        if ($s_rows !== FALSE) {
            foreach ($s_rows as $skey => $s_row) {
                $a_rows = get_rows("activities", "WHERE ID in (" . $s_row['activity'] . ")");
                $a_str = "";
                foreach ($a_rows as $a_row) {
                    $a_str .= $a_row['type'] . ' ' . $a_row['name'];
                }
                $s_rows[$skey]['activity'] = $a_str;
            }
        }

        $cols = array_diff(get_columns("shifts", false), array('ID', 'job', 'activity_values', 'activity_comments', 'furniture'));
        echo generate_table($cols, $s_rows, "shifts");

    } else {
        echo "no result";
    }
}
getdetailpagefooter("#job_form", "jobs", 'page.php?id=1');