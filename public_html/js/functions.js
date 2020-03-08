/**
 * add leading zeros to numbers
 *
 * @param str
 * @param max
 * @returns {*}
 */
function pad(str, max) {
    str = str.toString();
    return str.length < max ? pad("0" + str, max) : str;
}

/**
 * return all the rows from a table in JSON format
 *
 * @param tbl
 * @param qry
 * @param callback
 */
function getRows(tbl, qry, callback) {
    $.ajax({
        url: 'getRows.php',
        type: 'POST',
        dataType: 'json',
        data: {'table': tbl, 'query': qry},
        success: function (data) {
            //console.log(data);
            callback(data);
        },
        error: function (xhr, desc, err) {
            console.log(xhr);
            console.log("Details: " + desc + "\nError:" + err);
            return false;
        }
    });
}

/**
 * insert options into the given <select> element from a table
 *
 * @param dd_id
 * @param tbl
 * @param val_key
 * @param txt_key
 */
function loadDropdownOptions(dd_id, tbl, val_key, txt_key) {
    getRows(tbl, "", function (options) {
        var dd = $(dd_id);
        $.each(options, function (i, option) {
            var op_str = '<option value="' + option[val_key] + '">' + option[txt_key] + ' (' + option[val_key] + ')</option>';
            dd.append(op_str);
        });
    });
}

/**
 * search form show input field for selected column
 */
function searchFormInit() {
    var ci = "." + $('#searchcolumn').val() + "_input";
    $('.sci').prop('disabled', true);
    $(ci).show();
    $(ci).prop('disabled', false);

    $('#searchcolumn').on('change', function () {
        $('.sci').hide();
        $('.sci').prop('disabled', true);
        var ci = "." + this.value + "_input";
        $(ci).show();
        $(ci).prop('disabled', false);
    })
}

/**
 * setup date input fields
 */
function dateInputInit() {
    $('input[type=date]').datepicker({
        format: 'dd-mm-yyyy'
    });

}

/**
 * javascript for page.php
 */
function pagefunctions() {
    //var table_sorter_options = ;
    //table_sorter_options.push();
    var options_array = {
        dateFormat: 'uk'
    }
    if (typeof table_sorter_options !== 'undefined')
        options_array['sortList'] = table_sorter_options;
    $(document).ready(function () {
        $(".tablesorter").tablesorter(options_array);
    });
    $("th:first-child").trigger("click");
    $("th:first-child").trigger("click");
    //dateInputInit();
    searchFormInit();


    $("#printbtn").click(function () {
        window.print();
    });
}

/**
 *
 */
function addFurniture() {

}

/**
 *
 */
function initRemoveFurniture() {
    $(".remove-furniture").click(function () {
        $(this).parent().remove();
    });
}

/**
 *
 * @returns {string}
 */
function furnitureJSON() {
    var jsonString = '[';
    $(".furniture-name").each(function (index) {
        var furnitureID = $(this).val();
        if(furnitureID > 0) {
            if (index != 0) {
                jsonString += ',';
            }
            jsonString += '{"' + furnitureID + '":' + $(this).siblings('.furniture-quantity').val() + '}';
        }
    });

    if (jsonString == '[') { //no furniture on this page
        return "";
    } else {
        jsonString += ']';
        return "&furniture=" + jsonString;
    }
}

/**
 * javascript for detail pages
 *
 * @param detailform
 * @param detailtable
 */
function detailPageFunctions(detailform, detailtable) {
    $("#jstatus").addClass($("#jstatus").val());
    $("#jstatus").change(function () {
        this.className = 'form-control';
        $(this).addClass($(this).val());
    });


    $("#add-furniture-button").click(function () {
        console.log('howdy');
        //addFurniture();
        var jobFurnitureWrapper = $('.job-furniture-row');
        var jobFurnitureHTML = $('.job-furniture-row .furniture-group').last();
        var jobFurnitureNew = jobFurnitureHTML.clone().insertAfter(jobFurnitureHTML);
        jobFurnitureNew.children('select').val('');
        jobFurnitureNew.children('input.furniture-quantity').val(1);

        initRemoveFurniture();
    });

    initRemoveFurniture();

    $(".viewinput").prop('disabled', true); //disabled all the inputs
    $(".viewinputp").prop('disabled', true); //disabled all the inputs

    $("#edit-button").click(function () {
        $(".viewinput").prop('disabled', false); //enable all the inputs
        $(".viewinput").removeClass('viewinput');
        $("#update-button").show();
        $("#update-button").prop('disabled', false);
        $("#cancel-button").show();
        $("#edit-button").hide();


        $(".remove-furniture, #add-furniture-button").removeClass('disabled');
        $("#add-furniture-button").prop('disabled', false);

    });

    $("#cancel-button").click(function () {
        location.reload();
        $(detailform + " input").addClass('viewinput');
        $(detailform + " select").addClass('viewinput');
        $(detailform + " textarea").addClass('viewinput');
        $(".viewinput").prop('disabled', true); //enable all the inputs
        $("#update-button").hide();
        $("#update-button").prop('disabled', true);
        $("#cancel-button").hide();
        $("#edit-button").show();
    });

    //  ADD / UPDATE ENTRY  //
    $(detailform).submit(function (e) {
        console.log('submitted');
        e.preventDefault();
        $(".alert").alert('close');

        //if start time set to be after finish time -> throws error
        var time_started = $(detailform + " input[name='time_started']").val();
        var time_finished = $(detailform + " input[name='time_finished']").val();
        //alert(time_started + ' - ' + time_finished);
        //alert(compareTime( time_started, time_finished ));
        if (compareTime(time_started, time_finished) == 1) {
            errorAlert("<strong>Error!</strong> The start time should be before the finish time. Please change times and resumbit.", detailform);
            return false;
        }

        //if start date set to be after finish date -> throws error
        var date_started = Date.parse($(detailform + " input[name='date_started']").val());
        var date_finished = Date.parse($(detailform + " input[name='date_finished']").val());
        if ((date_finished > 0) && (date_started > date_finished)) {
            errorAlert("<strong>Error!</strong> The start date should be before the finish date. Please change dates and resubmit", detailform);
            return false;
        }

        ajax_url = "add_entry.php";
        if ($('#update-button').length > 0) {
            ajax_url += "?update";
        }
        $("#addbtn").prop('disabled', true);
        $("#update-button").prop('disabled', true);
        $(".viewinputp").prop('disabled', false); //temp enable permanent disabled inputs so their data can still be sent

        /*
        var detailform_array = $( detailform ).serializeArray();
        switch(detailform){
            case '#customer_form'
                redirecturl .=
                break;
        }
        */
        //console.log( detailform_array );
        console.log($(detailform).serialize());

        console.log($(detailform).serialize() + furnitureJSON() + "&table=" + detailtable);

        $.ajax({
            url: ajax_url,
            type: 'POST',
            data: $(detailform).serialize() + furnitureJSON() + "&table=" + detailtable,
            success: function (data) {
                console.log(data);
                if (data != "success") {
                    $("#addbtn").prop('disabled', false);
                    $("#update-button").prop('disabled', false);
                    errorAlert(data);
                } else {
                    console.log("success");
                    location.assign(redirecturl);
                }
            },
            error: function (xhr, desc, err) {
                console.log(xhr);
                console.log("Details: " + desc + "\nError:" + err);
                return false;
            }
        });

        $(".viewinputp").prop('disabled', true);


    });
}

/**
 *
 * @param message
 * @param location
 */
function errorAlert(message = '', location = '.detailform') {
    var error_html = "<div class=\"alert alert-danger alert-dismissible fade show\" role=\"alert\">" + message + "<button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"Close\"> <span aria-hidden=\"true\">&times;</span> </button> </div>";
    $(location).prepend(error_html);
}

/**
 *
 * @param time1
 * @param time2
 * @returns {number}
 */
function compareTime(time1 = '', time2 = '') {
    var t1 = new Date();
    var parts = time1.split(":");
    t1.setHours(parts[0], parts[1], 0, 0);
    var t2 = new Date();
    parts = time2.split(":");
    t2.setHours(parts[0], parts[1], 0, 0);

    // returns 1 if greater, -1 if less and 0 if the same
    if (t1.getTime() > t2.getTime()) return 1;
    if (t1.getTime() < t2.getTime()) return -1;
    return 0;
}
