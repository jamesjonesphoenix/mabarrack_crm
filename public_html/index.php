<?php

namespace Phoenix;

include '../src/crm_init.php'; ?>
    <div class="row">
        <?php
        $menuitems = get_rows('main_menu', '');

        if ($menuitems !== false) {
            foreach ($menuitems as $menuitem) { ?>
                <div class="col-md-3 col-sm-4 col-xs-6">
                    <a href="<?php echo $menuitem['url']; ?>">
                        <div class="btn main-btn">
                            <img src="img/admin/<?php echo $menuitem['image']; ?>"/>
                            <h2><?php echo $menuitem['name']; ?></h2>
                            <?php

                            if ($menuitem['notif_qry'] != '') { //has notification query
                                $notif = get_notify_qry($menuitem['notif_qry']);

                                echo "<div class='notifs'>" . $notif . '</div>';

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
ph_get_template_part('footer');