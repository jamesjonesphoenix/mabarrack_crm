<?php

namespace Phoenix;

include '../src/crm_init.php'; ?>
    <div class="row">
        <?php
        $urgentJobCutoff = PDOWrap::instance()->getRow( 'settings', array('name' => 'joburg_th') )['value'];

        $menuItems = array(
            'In Progress' => array(
                'name' => 'In Progress',
                'url' => 'page.php?id=1',
                'image' => 'inprogress.svg',
                'number' => PDOWrap::instance()->run( 'SELECT COUNT(*) as num FROM jobs WHERE jobs.status = "jobstat_red" AND jobs.ID != 0' )->fetch()['num']
            ),
            'Urgent' => array(
                'name' => 'Urgent',
                'url' => 'page.php?id=8',
                'image' => 'urgent.svg',
                'number' => PDOWrap::instance()->run( 'SELECT COUNT(*) as num FROM jobs WHERE status = "jobstat_red" AND priority < (' . $urgentJobCutoff . '+1) AND jobs.ID != 0' )->fetch()['num']
            ),
            'All Jobs' => array(
                'name' => 'All Jobs',
                'url' => 'page.php?id=3',
                'image' => 'jobs.svg'
            ),
            'Shifts' => array(
                'name' => 'Shifts',
                'url' => 'page.php?id=4&g=job',
                'image' => 'shifts.svg'
            ),
            'Customers' => array(
                'name' => 'Customers',
                'url' => 'page.php?id=5',
                'image' => 'customer.svg'
            ),
            'Workers' => array(
                'name' => 'Workers',
                'url' => 'page.php?id=6',
                'image' => 'worker.svg'
            ),
            'Reports' => array(
                'name' => 'Reports',
                'url' => 'reports.php',
                'image' => 'reports.svg'
            ),
            'Furniture' => array(
                'name' => 'Furniture',
                'url' => 'page.php?id=7',
                'image' => 'furniture.svg'
            )
        );

        if ( $menuItems !== false ) {
            foreach ( $menuItems as $menuItem ) { ?>
                <div class="col-md-3 col-sm-4 col-xs-6">
                    <a href="<?php echo $menuItem['url']; ?>">
                        <div class="btn main-btn">
                            <img src="img/admin/<?php echo $menuItem['image']; ?>"/>
                            <h2><?php echo $menuItem['name']; ?></h2>
                            <?php
                            if ( !empty( $menuItem['number'] ) ) { //has notification query
                                echo "<div class='notifs'>" . $menuItem['number'] . '</div>';
                            }
                            ?>
                        </div>
                    </a>
                </div>
                <?php
            }
        }
        ?>
    </div>
    <?php
ph_get_template_part( 'footer' );