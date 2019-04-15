<?php include 'include/crm_init.php';?>
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default whomepanel topnews">
                <div><?php echo nl2br( get_rows( "settings", "WHERE name = 'news_text'" )[ 0 ][ 'value' ] ); ?></div>
            </div>
        </div>
    </div>
    <div class="row">
    <div class="col-md-6">
        <div class="panel panel-default whomepanel">
            <?php

            $user_id = ph_validate_number($_SESSION[ 'user_id' ]);

            $shftrows = get_rows_qry( "wcs", [ $user_id ] );
            if ( $shftrows === FALSE ) { //no shifts found for today, show start day
                ?>
                <a class="btn btn-default whbtn" style="margin: 50px;" href="choosejob.php?sd"><h2>Start Day</h2></a>
            <?php } else { ?>
                <a class="btn btn-default whbtn" style="margin: 70px 50px 50px 50px;" href="choosejob.php"><h2>Next
                        Shift</h2></a>
                <?php

                $hadlunch = false;

                foreach ( $shftrows as $shft ) {
                    if ( $shft[ 'activity' ] == 'Lunch' ) {
                        $hadlunch = true;
                    }
                }

                if ( $hadlunch ) { ?>
                    <a class="btn btn-default redbtn whbtn" style="margin: 70px 50px 50px 50px;" href="finishday.php">
                        <h2>Finish Day</h2></a>
                <?php } else { ?>
                    <a class="btn btn-default redbtn whbtn" style="margin: 70px 50px 50px 50px;" href="startlunch.php">
                        <h2>Start Lunch</h2></a>
                <?php }
            }
            ?>
        </div>
    </div>
    <div class="col-md-6">
    <div class="panel panel-default whomepanel">
        <div class="row">
            <div class="col-md-6 col-sm-6">
                <h3>Total Hours<br>Today</h3>
                <?php
                $todaymins = 0;
                if ( $shftrows !== FALSE ) {
                    foreach ( $shftrows as $sr ) {
                        $todaymins += $sr[ 'minutes' ];
                    }
                }
                $todayhrs = ph_format_hours_minutes( $todaymins );
                echo "<h3 class='well'>" . $todayhrs . "</h3>";
                ?>
            </div>
            <div class="col-md-6 col-sm-6">
                <h3>Total Hours<br>This Week</h3>
                <?php

                //get week dates
                $date_s = "";
                $date_f = "";

                if ( date( "w" ) == 5 ) {
                    $date_s = date( "d-m-Y" );
                } else {
                    $date_s = date( "d-m-Y", strtotime( "previous friday" ) );
                }

                if ( date( "w" ) == 4 ) {
                    $date_f = date( "d-m-Y" );
                } else {
                    $date_f = date( "d-m-Y", strtotime( "next thursday" ) );
                }

                //get shifts
                $s_rows = get_rows_qry( "wtr", [ $user_id, date( "'Y-m-d'", strtotime( $date_s ) ), date( "'Y-m-d'", strtotime( $date_f ) ) ] );
                $weekmins = 0;
                if ( $s_rows !== FALSE ) {
                    foreach ( $s_rows as $srow ) {
                        $weekmins += $srow[ 'minutes' ];
                    }
                }

                $weekhrs = ph_format_hours_minutes( $weekmins );
                echo "<h3 class='well'>" . $weekhrs . "</h3></div></div>";

                echo "<h3>Current Job</h3>";

                if ( $shftrows !== FALSE ) {
                    $lastshift[ 'ID' ] = 0;

                    foreach ( $shftrows as $sr ) {
                        if ( ( $sr[ 'ID' ] > $lastshift[ 'ID' ] ) and ( $sr[ 'activity' ] != 'Lunch' ) ) {
                            $lastshift = $sr;
                        }
                    }

                    $lastjob = get_rows_qry( "wcj", [ $lastshift[ 'job' ] ] )[ 0 ];

                    if ( $lastjob[ 'ID' ] == 0 ) {
                        echo "<h3 class='well' style='margin-bottom: 0;'>" . $lastjob[ 'customer' ] . "</h3><br><h3 class='well'>" . $lastshift[ 'activity' ] . "</h3>";
                    } else {
                        echo "<h3 class='well' style='margin-bottom: 0;'>" . $lastjob[ 'customer' ] . "</h3><br><h3 class='well'>" . $lastjob[ 'description' ] . "</h3>";
                    }
                } else {
                    echo "<h3 class='well' style='margin-bottom: 0'>none</h3>";
                }
                ?>
                <div class='row justify-content-center'>
                    <a class="btn btn-default whbtn" style="margin: 10px;"
                       href="report.php?worker_id=<?php echo $user_id; ?>"><h2 style="font-size: 24px">Clock
                            Record</h2></a>
                </div>
            </div>
        </div>
    </div>
<?php include 'include/footer.php' ?>