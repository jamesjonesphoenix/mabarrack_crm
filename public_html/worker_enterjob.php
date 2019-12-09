<?php

namespace Phoenix;

include '../src/crm_init.php';


$newsText = PDOWrap::instance()->getRow( 'settings', array('name' => 'news_text') )['value'];
$newsText = nl2br( $newsText );

$userID = CurrentUser::instance()->id
?>
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default whomepanel topnews">
                <div><?php echo $newsText; ?></div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <div class="panel panel-default whomepanel">
                <?php


                $shiftRows = PDOWrap::instance()->run( 'SELECT shifts.*, activities.name as activity FROM shifts INNER JOIN activities ON shifts.activity=activities.ID WHERE shifts.worker=? AND date = CURRENT_DATE', [$userID] )->fetchAll();
                if ( empty( $shiftRows ) ) { //no shifts found for today, show start day
                    ?>
                    <a class="btn btn-default whbtn" style="margin: 50px;" href="choosejob.php?sd">
                        <h2>Start Day</h2>
                    </a>

                <?php } else { ?>
                    <a class="btn btn-default whbtn" style="margin: 70px 50px 50px 50px;" href="choosejob.php">
                        <h2>Next Shift</h2>
                    </a>
                    <?php

                    $hadLunch = false;

                    foreach ( $shiftRows as $shift ) {
                        if ( $shift['activity'] === 'Lunch' ) {
                            $hadLunch = true;
                        }
                    } ?>
                    <a class="btn btn-default redbtn whbtn" style="margin: 70px 50px 50px 50px;"
                    <?php
                    if ( $hadLunch ) { ?>
                        href="finish_day.php">
                        <h2>Finish Day</h2></a>
                    <?php } else { ?>
                        href="nextshift.php">
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
                        $todayMinutes = 0;
                        if ( !empty( $shiftRows ) ) {
                            foreach ( $shiftRows as $shiftRow ) {
                                $todayMinutes += $shiftRow['minutes'];
                            }
                        }
                        $todayHours = ph_format_hours_minutes( $todayMinutes );
                        echo '<h3 class="well">' . $todayHours . '</h3>';
                        ?>
                    </div>
                    <div class="col-md-6 col-sm-6">
                        <h3>Total Hours<br>This Week</h3>
                        <?php

                        //get week dates
                        $date_s = '';
                        $date_f = '';

                        if ( date( 'w' ) === '5' /*Friday*/ ) {
                            $date_s = date( 'd-m-Y' );
                        } else {
                            $date_s = date( 'd-m-Y', strtotime( 'previous friday' ) );
                        }

                        if ( date( 'w' ) === '4' /*Thursday*/ ) {
                            $date_f = date( 'd-m-Y' );
                        } else {
                            $date_f = date( 'd-m-Y', strtotime( 'next thursday' ) );
                        }

                        //get shifts

                        $query = 'SELECT shifts.*, activities.chargable, customers.name as customer FROM shifts 
    INNER JOIN jobs ON shifts.job=jobs.ID INNER JOIN customers ON jobs.customer=customers.ID INNER JOIN activities ON shifts.activity=activities.ID WHERE shifts.worker=:worker AND shifts.date >=:startDate AND shifts.date <=:finishDate';
                        $shiftRows = PDOWrap::instance()->run(
                            $query,
                            [
                                'worker' => $userID,
                                'startDate' => date( 'Y-m-d', strtotime( $date_s ) ),
                                'finishDate' => date( 'Y-m-d', strtotime( $date_f ) )
                            ]
                        )->fetchAll();

                        $weekMinutes = 0;
                        if ( !empty( $shiftRows ) ) {
                            foreach ( $shiftRows as $shiftRow ) {
                                $weekMinutes += $shiftRow['minutes'];
                            }
                        }

                        ?>
                        <h3 class='well'><?php echo ph_format_hours_minutes( $weekMinutes ); ?></h3></div>
                </div>

                <h3>Current Job</h3>
                <?php
                if ( !empty( $shiftRows ) ) {
                    $lastShift['ID'] = 0;

                    foreach ( $shiftRows as $shiftRow ) {
                        if ( ($shiftRow['ID'] > $lastShift['ID']) && ($shiftRow['activity'] !== 'Lunch') ) {
                            $lastShift = $shiftRow;
                        }
                    }
                    $lastJob = PDOWrap::instance()->run( 'SELECT jobs.*, customers.name as customer FROM jobs INNER JOIN customers ON jobs.customer=customers.ID WHERE jobs.ID=:jobID', ['jobID' => $lastShift['job']] )->fetchAll()[0];


                    echo '<h3 class="well" style="margin-bottom: 0;">' . $lastJob['customer'] . '</h3>';

                    $activities = new Activities( PDOWrap::instance() );
                    $activity = $activities->getName( $lastShift['activity'] );
                    echo !empty( $activity ) ? '<br><h3 class="well">' . $activity . '</h3>' : '';
                    echo !empty( $lastJob['description'] ) && $lastShift['job'] !== 0 ? '<br><h3 class="well">' . $lastJob['description'] . '</h3>' : '';


                } else {
                    ?><h3 class='well' style='margin-bottom: 0'>none</h3><?php
                }
                ?>
                <div class='row justify-content-center'>
                    <a class="btn btn-default whbtn" style="margin: 10px;"
                       href="report.php?worker_id=<?php echo $userID; ?>"><h2 style="font-size: 24px">Clock
                            Record</h2></a>
                </div>
            </div>
        </div>
    </div>
    <?php


ph_get_template_part( 'footer' ) ?>