<?php

namespace Phoenix;

include '../src/crm_init.php'; ?>
    <a href="index.php" class="page-header-breadcrumb">
        <div class="btn btn-default">◀ &nbsp; Main Menu</div>
    </a>
    <h2>Settings</h2>
    <div class='panel panel-default' style='position: relative'>
        <h3>Job Settings</h3>
        <form id='settings_form' class='detailform'>
            <h4>Job Status Text</h4>
            <?php

            $jobStatuses = PDOWrap::instance()->getRows( 'settings', array('name' => array(
                'value' => 'jobstat',
                'operator' => 'LIKE')
            ) );
            foreach ( $jobStatuses as $jobStatus ) {
                $jobStatus_name = ucfirst( str_replace( 'jobstat_', '', $jobStatus['name'] ) );
                echo '<b>' . $jobStatus_name . '</b>';
                echo "<input type='text' class='form-control w300' name='" . $jobStatus['ID'] . "' value='" . $jobStatus['value'] . "'/>";
            }
            ?>
            <br><h4>Urgency Threshold</h4>
            <select class='form-control w100' name='4' autocomplete='off'>
                <?php
                $pts = array(1, 2, 3, 4);
                $joburg_th = PDOWrap::instance()->getRow( 'settings', array('name' => 'joburg_th') )['value'];
                foreach ( $pts as $pt ) {
                    $selected = $pt === $joburg_th ? ' selected="selected"' : '';
                    echo '<option value="' . $pt . '"' . $selected .'>' . $pt . "</option>";
                }

                $newsText = PDOWrap::instance()->getRow( 'settings', array('name' => 'news_text') )['value'];
                $newsText = urldecode( $newsText );

                ?>
            </select>
            <br>
            <h3>News</h3>
            <div style='max-width:500px'><textarea id='newsbox' name='5' class='form-control'
                                                   autocomplete='off'><?php echo $newsText; ?></textarea></div>

            <br>
            <input type='submit' value='Apply' class='btn btn-default' id='applysetbtn'>
        </form>

    </div>
    <script>
        pagefunctions();

        success_count = 0;

        //  Updating Settings  //
        $("#settings_form").submit(function (e) {

            e.preventDefault();

            ajax_url = "add_entry.php?update";


            console.log($("#settings_form").serialize());


            $("input[type=text]").each(function (index) {
                id = $(this).attr('name');

                data_str = "ID=" + id + "&value=" + encodeURI($(this).val()) + "&table=settings";
                console.log(data_str);

                $.ajax({
                    url: ajax_url,
                    type: 'POST',
                    data: data_str,
                    success: function (data) {
                        console.log(data);
                        if (data == "success") {
                            success_count++;
                        }
                        if (success_count == 5) {
                            location.assign("settings.php");
                        }
                    },
                    error: function (xhr, desc, err) {
                        console.log(xhr);
                        console.log("Details: " + desc + "\nError:" + err);
                        return false;
                    }
                });


            });

            $("select").each(function (index) {
                id = $(this).attr('name');

                data_str = "ID=" + id + "&value=" + encodeURI($(this).val()) + "&table=settings";
                console.log(data_str);

                $.ajax({
                    url: ajax_url,
                    type: 'POST',
                    data: data_str,
                    success: function (data) {
                        console.log(data);
                        if (data == "success") {
                            success_count++;
                        }
                        if (success_count == 5) {
                            location.assign("settings.php");
                        }
                    },
                    error: function (xhr, desc, err) {
                        console.log(xhr);
                        console.log("Details: " + desc + "\nError:" + err);
                        return false;
                    }
                });


            });


            id = $("#newsbox").attr('name');

            data_str = "ID=" + id + "&value=" + encodeURI($('#newsbox').val()) + "&table=settings";
            console.log(data_str);

            $.ajax({
                url: ajax_url,
                type: 'POST',
                data: data_str,
                success: function (data) {
                    console.log(data);
                    if (data == "success") {
                        success_count++;
                    }
                    if (success_count == 5) {
                        location.assign("settings.php");
                    }
                },
                error: function (xhr, desc, err) {
                    console.log(xhr);
                    console.log("Details: " + desc + "\nError:" + err);
                    return false;
                }
            });


        });

    </script>

    <?php ph_get_template_part( 'footer' ); ?>