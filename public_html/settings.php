<?php include 'include/crm_init.php'; ?>
    <a href="index.php" class="page-header-breadcrumb">
        <div class="btn btn-default">◀ &nbsp; Main Menu</div>
    </a>
    <h2>Settings</h2>
    <div class='panel panel-default' style='position: relative'>
        <h3>Job Settings</h3>
        <form id='settings_form' class='detailform'>
            <h4>Job Status Text</h4>
            <?php

            $jstats = get_rows_qry( "jstats", [] );

            foreach ( $jstats as $jstat ) {
                $jstat_name = ucfirst( str_replace( "jobstat_", "", $jstat[ 'name' ] ) );
                echo "<b>" . $jstat_name . "</b>";
                echo "<input type='text' class='form-control w300' name='" . $jstat[ 'ID' ] . "' value='" . $jstat[ 'value' ] . "'/>";
            }

            echo "<br><h4>Urgency Threshold</h4>";
            echo "<select class='form-control w100' name='4' autocomplete='off'>";
            $pts = array( 1, 2, 3, 4 );
            $joburg_th = get_rows( "settings", "WHERE name = 'joburg_th'" )[ 0 ][ 'value' ];
            foreach ( $pts as $pt ) {
                if ( $pt == $joburg_th ) {
                    echo '<option value="' . $pt . '" selected="selected">' . $pt . "</option>\n";
                } else {
                    echo '<option value="' . $pt . '">' . $pt . "</option>\n";
                }
            }
            echo "</select>\n<br>";
            echo "<h3>News</h3>\n";
            echo "<div style='max-width:500px'><textarea id='newsbox' name='5' class='form-control' autocomplete='off'>" . urldecode( get_rows( "settings", "WHERE name = 'news_text'" )[ 0 ][ 'value' ] ) . "</textarea></div>";

            ?>
            <br>
            <input type='submit' value='Apply' class='btn btn-default' id='applysetbtn'>
        </form>

    </div>
    <script>
        pagefunctions();

        success_count = 0;

        //  Updating Settings  //
        $( "#settings_form" ).submit( function ( e ) {

            e.preventDefault();

            ajax_url = "add_entry.php?update";


            console.log( $( "#settings_form" ).serialize() );


            $( "input[type=text]" ).each( function ( index ) {
                id = $( this ).attr( 'name' );

                data_str = "ID=" + id + "&value=" + encodeURI( $( this ).val() ) + "&table=settings";
                console.log( data_str );

                $.ajax( {
                    url: ajax_url,
                    type: 'POST',
                    data: data_str,
                    success: function ( data ) {
                        console.log( data );
                        if ( data == "success" ) {
                            success_count++;
                        }
                        if ( success_count == 5 ) {
                            location.assign( "settings.php" );
                        }
                    },
                    error: function ( xhr, desc, err ) {
                        console.log( xhr );
                        console.log( "Details: " + desc + "\nError:" + err );
                        return false;
                    }
                } );


            } );

            $( "select" ).each( function ( index ) {
                id = $( this ).attr( 'name' );

                data_str = "ID=" + id + "&value=" + encodeURI( $( this ).val() ) + "&table=settings";
                console.log( data_str );

                $.ajax( {
                    url: ajax_url,
                    type: 'POST',
                    data: data_str,
                    success: function ( data ) {
                        console.log( data );
                        if ( data == "success" ) {
                            success_count++;
                        }
                        if ( success_count == 5 ) {
                            location.assign( "settings.php" );
                        }
                    },
                    error: function ( xhr, desc, err ) {
                        console.log( xhr );
                        console.log( "Details: " + desc + "\nError:" + err );
                        return false;
                    }
                } );


            } );


            id = $( "#newsbox" ).attr( 'name' );

            data_str = "ID=" + id + "&value=" + encodeURI( $( '#newsbox' ).val() ) + "&table=settings";
            console.log( data_str );

            $.ajax( {
                url: ajax_url,
                type: 'POST',
                data: data_str,
                success: function ( data ) {
                    console.log( data );
                    if ( data == "success" ) {
                        success_count++;
                    }
                    if ( success_count == 5 ) {
                        location.assign( "settings.php" );
                    }
                },
                error: function ( xhr, desc, err ) {
                    console.log( xhr );
                    console.log( "Details: " + desc + "\nError:" + err );
                    return false;
                }
            } );


        } );

    </script>

<?php include 'include/footer.php'; ?>