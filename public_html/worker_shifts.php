<?php

namespace Phoenix;

include '../src/crm_init.php';
$jobID = -1;

if ( !isset( $_GET['jobno'] ) ) {
    ph_redirect( 'worker_enterjob' );
}


$jobID = ph_validate_number( $_GET['jobno'] );
$jobRow = PDOWrap::instance()->getRow( 'jobs', array('ID' => $jobID) );
if ( empty( $jobRow ) ) {
    ph_redirect( 'worker_enterjob' );
}

?>
    <div id="actadder">
        <div class='panel panel-default'>
            <h2>Add Activity</h2>
            <?php echo addActivityPanel(); ?>
            <div class='btn btn-default add_act'>ADD</div>
        </div>
    </div>
    <div id="shiftadd_bg"></div>
    <div class="row">
        <div id="shiftadder" class="col-md-12">
            <div class='panel panel-default'>
                <h2>Add Shift</h2>
                <div class='btn btn-default show_acts'>(A)dd Activity</div>
                <div class='btn btn-default shft_cancel'>(C)ancel</div>
                <?php echo addShiftForm( $jobID, ph_validate_number( $_SESSION['user_id'] ) ); ?>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class='btn btn-default jobselect'>◀ &nbsp; Select Job</div>
            <div class="panel panel-default">
                <input type='button' id="asbtn" value='(A)dd Shift' class="btn btn-default" autofocus/>
                <h2 style="display: inline; margin-right: 50px;">Job
                    <div class="well"><?php echo $jobID; ?></div>
                </h2>
                <h2 style='display: inline'>Truck
                    <div class='well'><?php echo $jobRow['truck']; ?></div>
                </h2>
                <h3 style='margin-bottom: 0px;'>Description</h3>
                <div class='well' style='margin-top: 5px; width: 100%; text-align: left'>
                    <p><?php echo $jobRow['description']; ?></p></div>
                <h3>Shifts</h3>
                <?php
                //SELECT shifts.ID, shifts.job, shifts.worker, shifts.date, shifts.time_started, shifts.time_finished, shifts.minutes, shifts.activity, users.name as worker FROM shifts INNER JOIN users ON shifts.worker=users.ID WHERE arg0 = arg1

                $s_rows = getRowsQuery( 'sq', ['job', $jobID] );
                if ( $s_rows !== false ) {
                    foreach ( $s_rows as $skey => $s_row ) {
                        $activityRow = PDOWrap::instance()->getRow( 'activities', array('ID' => $s_row['activity']) );
                        $s_rows[$skey]['activity'] = $activityRow['name'] . ', ';
                    }
                }

                $columns = array('furniture','worker','date','time_started','time_finished','minutes','activity');
                echo generateTable( $columns, $s_rows );

                ?>
            </div>
        </div>
    </div>
    <script>
        pagefunctions();

        // go back to job enter page
        $(".jobselect").click(function () {
            window.location.href = "worker_enterjob.php";
        });

        ////  ACTIVITIES DATA  ////
        acts_list = []; //list of activities for the current shifts (cleared when shift added)
        $(".shift-finish").prop('disabled', true);

        // open the shift adder
        $("#asbtn").click(function () {
            $("#shiftadd_bg").show();
            $("#shiftadd_bg").height($(window).height() - 125);
            $("#shiftadder").show();
        });


        function close_sa() {
            $("#shiftadder").hide(); //close the shift adder
            $("#shiftadd_bg").hide(); //hide bg
            $(".shft_acts").html("no activities"); //remove html of added activities
            acts_list.length = 0; //remove all activites
        }

        Mousetrap.bindGlobal('c', close_sa);

        // close/cancel the shift adder
        $(".shft_cancel").click(function () {
            $("#shiftadder").hide(); //close the shift adder
            $("#shiftadd_bg").hide(); //hide bg
            $(".shft_acts").html("no activities"); //remove html of added activities
            acts_list.length = 0; //remove all activites
        });

        // open the activity adder
        $(".show_acts").click(function () {
            $("#actadder").show();
        });

        //  ADD ACTIVITY TO SHIFT ADDER  //
        $(".add_act").click(function () {
            var actdiv = $(".shft_acts");
            if (actdiv.html() == "no activities") {
                actdiv.html("");
            }
            //Get Activity Chosen
            var act_chosen = $(".act_chosen");
            //Get Activity Chosen Options
            var act_chosen_ops = $("#" + $(act_chosen).attr('id').replace("act", "actopt"));
            //Create Activity Object
            var act_obj = {'id': $(act_chosen).attr('name'), 'comment': $(".act_comment").val()};

            //Get Options
            var firstop = true;
            var act_op_str = ""; //string to store all the options and their values
            $('input', act_chosen_ops).each(function () { //for each input
                if (!firstop) {
                    act_op_str += "|";
                }
                firstop = false;
                if ($(this).prop('type') == "checkbox") { //for checkboxs
                    act_op_str += $(this).prop('checked');
                }
            });
            act_obj['options'] = act_op_str; //add options string to activity object

            //add activity object to activity list
            acts_list.push(act_obj);
            console.log(acts_list);

            //Create html to display activity in the shift adder
            var act_str = "<div class='activity'>";
            act_str += "<p class='name'>" + act_chosen.html() + "</p>";
            act_str += "<p>Options</p>";
            act_str += "<span class='options'>" + act_op_str + "</span>";
            act_str += "<p>Comment: </p><p class='comment'>" + $(".act_comment").val() + "</p>";
            act_str += "</div>";

            //Add activity html to shift adder
            actdiv.append(act_str);

            $("#actadder").hide();
            $(".act_comment").val(""); //clear the comment field
            $(".act_btn").removeClass("act_chosen"); //clear chosen activity
            $(".shift-finish").prop('disabled', false);
        });


        //  SELECT ACTIVITY  //
        $(".act_btn").click(function () {
            $(".act_btn").removeClass("act_chosen");
            $(this).addClass("act_chosen");
            $(".add_act").show();
            $(".act_op").hide();
            var actop_str = $(this).attr('id');
            actop_str = "#" + actop_str.replace("act", "actopt");
            console.log(actop_str);
            $(actop_str).show();
        });


        //  ADD SHIFT  //
        $("#shft_add_form").submit(function (e) {
            $(".shift-finish").prop('disabled', true);

            var acts = {'activity': '', 'activity_values': '', 'activity_comments': ''}
            var firsta = true;

            for (var i = 0; i < acts_list.length; i++) {
                if (!firsta) {
                    acts['activity'] += ",";
                    acts['activity_values'] += ",";
                    acts['activity_comments'] += ",";
                } else {
                    firsta = false;
                }
                var a_o = acts_list[i]; //activity object
                acts['activity'] += a_o['id']; //add id
                acts['activity_values'] += a_o['options']; //add option values
                acts['activity_comments'] += a_o['comment']; //add comment
            }

            console.log(acts);

            acts_str = "&activity=" + acts['activity'] + "&activity_values=" + acts['activity_values'] + "&activity_comments=" + acts['activity_comments'];

            //alert($('#shft_add_form').serialize() + acts_str);

            $.ajax({
                url: 'add_entry.php',
                type: 'POST',
                data: $('#shft_add_form').serialize() + acts_str,
                success: function (data) {
                    console.log(data);
                    if (data != "success") {
                        $(".shift-finish").prop('disabled', false);
                    } else {
                        console.log("shift added!");
                        $("#shiftadder").hide(); //close the shift adder
                        $(".shft_acts").html("no activities"); //remove html of added activities
                        acts_list.length = 0; //remove all activites
                        location.reload();
                    }
                },
                error: function (xhr, desc, err) {
                    console.log(xhr);
                    console.log("Details: " + desc + "\nError:" + err);
                    return false;
                }
            });

            e.preventDefault();

        });

    </script>
    <?php ph_get_template_part( 'footer' ) ?>