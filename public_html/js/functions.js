$j = jQuery.noConflict();

$j(document).ready(function () {

    entityPageFunctions();
    matchTablesWidths(); //Must match table widths before table sorter as it adds extra classes

    let waitForFinalEvent = (function () {
        let timers = {};
        return function (callback, ms, uniqueId) {
            if (!uniqueId) {
                uniqueId = "Don't call this twice without a uniqueId";
            }
            if (timers[uniqueId]) {
                clearTimeout (timers[uniqueId]);
            }
            timers[uniqueId] = setTimeout(callback, ms);
        };
    })();

    $j(window).resize(function () {
        waitForFinalEvent(function(){ //limit how often resize fires so browser doesn't kill itself
            matchTablesWidths();
        }, 800, "some unique string");
    });

    $j("#print-button").click(function () {
        window.print();
    });

    $j('.alert').on('closed.bs.alert', function () {
        let container = $j('div.container.messages');
        let siblings = container.find('.alert');
        if (siblings.length === 0) {
            container.collapse('hide');
        }
    });

    $j('#collapse-messages').on('shown.bs.collapse', function () { //Destroy additional messages expander after expanding
        $j('.collapse-messages-column .alert').alert('close');
    });

    /**
     *
     */
    function matchTablesWidths() {
        let tables = ['table.table.archive', 'table.table.choose-job', 'table.table.home-shift-table'];
        for (let i = 0; i <= tables.length; i++) {
            matchTableWidths(tables[i]);
        }
    }

    /**
     *
     */
    $j("table.table-sorter").each(function () {
        if (this.rows.length < 7) {
            return;
        }

        $j(this).tablesorter({
            dateFormat: 'uk',
            showProcessing: true,
            headers: {
                '.view, .furniture-select.select': {
                    sorter: false,
                    filter: false
                },
                '.errors': {
                    filter: false
                }
            },
            delayInit: true, //doesn't actually save any time if filters enabled
            textExtraction: function (node) {
                return node.textContent || $j(node).text() || '';
            },
            headerTemplate: '',
            widgets: ['filter', 'zebra'], // , 'zebra' too slow for large tables
            widgetOptions: {
                //filter_cellFilter: ['asdads', 'pipipipip'],
                filter_cssFilter: 'form-control',
                filter_placeholder: {search: 'Filter', select: 'Filter'},
                filter_searchDelay: 300,
                filter_hideEmpty: true,
            }
        });

    });


    let columnToggles = $j('input.column-toggle');
    columnToggles.each(function () {
        toggleTableColumn($j(this));
    });

    /**
     *
     */
    columnToggles.click(function () {
        toggleTableColumn($j(this));
    });

    /**
     *
     * @param input
     */
    function toggleTableColumn(input) {
        let columnIndex = input.attr('data-column'),
            columnClass = input.val(),
            checked = input.prop('checked'),
            table = 'table.table.archive ';

        $j('input.column-toggle[data-column="' + columnIndex + '"][value="' + columnClass + '"]').prop(
            'checked',
            checked
        );

        table = $j(
            table + ' thead td[data-column="' + columnIndex + '"], '
            + table + ' tbody td.' + columnClass + ', '
            + table + ' thead th.' + columnClass
        );
        if (checked) {
            table.removeClass('d-none');
        } else {
            table.addClass('d-none');
        }
        matchTablesWidths();
    }

    /**
     *
     * @param tableSelector
     */
    function matchTableWidths(tableSelector) {
        let tables = $j(tableSelector);
        if (tables.length < 2) {
            return;
        }
        let numberOfColumns = tables.find('tr')[0].cells.length,
            itemsToMatch = [];
        for (let i = 1; i <= numberOfColumns; i++) {
            let cssClass = $j(tableSelector + ' thead th:nth-child(' + i + ')').attr('class');
            if (!cssClass.includes('d-none')) {
                itemsToMatch[i] = tableSelector + ' thead th[class^="' + cssClass + '"]';
            }
        }
        //console.log(itemsToMatch);
        matchHeight(itemsToMatch,
            {
                byRow: false,
                property: 'width',
                target: null,
                remove: false,
                axis: 'horizontal'
            },
            0
        );
    }

    /**
     *
     */
    /*
    matchHeight(
        ['.top-worker-page-row .grey-bg'], {
            byRow: false,
            property: 'height',
            target: null,
            remove: false,
        },
        0
    );

     */

    /*
    matchHeight(
        ['div.archive-table-column div.alert.alert-info, div.archive-table-column div.table-responsive'], {
            byRow: false,
            property: 'width',
            target: null,
            remove: false,
            axis: 'horizontal'
        },
        0
    );
    */


    /**
     *
     * @param itemsToMatch
     * @param options
     * @param breakPoint
     */
    function matchHeight(itemsToMatch, options, breakPoint) {
        let i,
            doMatchHeight,
            itemsToMatchLength = itemsToMatch.length;
        if ($j(window).width() >= breakPoint) {
            doMatchHeight = true;
        }
        for (i = 0; i < itemsToMatchLength; i++) {
            if (doMatchHeight) {
                $j(itemsToMatch[i]).matchHeight(options);
            } else {
                $j(itemsToMatch[i]).matchHeight({remove: true}).height('100%');
                $j(itemsToMatch[i]).css("min-height", "none");
            }
        }
    }
});


/**
 * javascript for detail pages
 *
 */
function entityPageFunctions() {
    let formClass = '.detail-form',
        detailForm = $j(formClass);
    if (detailForm.length === 0) {
        return;
    }

    if ($j('#job_form' + formClass).length === 1) {
        jobDetailPageFunctions();
    }

    let cancelButton = $j('#cancel-button'),
        editButton = $j('#edit-button'),
        submitButton = $j('#submit-button'),
        passwordToggleButton = $j('#change-password-button'),
        deleteDryRunButton = $j('#delete-dry-run-button'),

        passwordField1ID = '#inputPassword',
        passwordField2ID = passwordField1ID + '-2',
        passwordField1 = $j(passwordField1ID),
        passwordField2 = $j(passwordField2ID),
        passwordFields = $j(passwordField1ID + ',' + passwordField2ID);

    editButton.click(function () {
        enableForm();
    });
    cancelButton.click(function () {
        disableForm();
    });
    passwordToggleButton.click(function () {
        if (passwordField1.prop('disabled')) {
            enableFormPassword();
        } else {
            disableFormPassword()
        }
    });

    /**
     * @returns {*|jQuery.fn.init|jQuery|HTMLElement}
     */
    function getFormControls() {
        return $j('form.detail-form .form-control:not(#inputFakeID,#inputID,' + passwordField1ID + ',' + passwordField2ID + '), input#add-furniture-button, a.remove-furniture');
    }

    /**
     * Enable most form fields but not password fields
     */
    function enableForm() {
        getFormControls().prop('disabled', false).removeClass('disabled');
        cancelButton.prop('disabled', false).show();
        submitButton.prop('disabled', false).show();
        passwordToggleButton.prop('disabled', false);
        editButton.hide().prop('disabled', true);
    }

    /**
     * Disable all form fields including password fields
     */
    function disableForm() {
        getFormControls().prop('disabled', true).addClass('disabled');
        cancelButton.hide().prop('disabled', true);
        submitButton.prop('disabled', true);
        editButton.prop('disabled', false).show();
        disableFormPassword();
        passwordToggleButton.prop("disabled", true);
        //location.reload(); //the loser's choice
    }

    /**
     *
     */
    function enableFormPassword() {
        passwordToggleButton.html('Cancel Change');
        passwordFields.prop("disabled", false);
    }

    /**
     *
     */
    function disableFormPassword() {
        passwordToggleButton.html('Change Password')
        passwordFields.prop("disabled", true).val('');
    }

    /**
     *
     * @returns {boolean}
     */
    function validateFormInput() {
        let timeStartedField = $j(formClass + " input[name='time_started']"),
            timeFinishedField = $j(formClass + " input[name='time_finished']"),
            dateStartedField = $j(formClass + " input[name='date_started']"),
            dateFinishedField = $j(formClass + " input[name='date_finished']");

        if (timeStartedField.length > 0 && timeFinishedField.length > 0) { //Validate finish/start times
            let timeStarted = timeStartedField.val(),
                timeFinished = timeFinishedField.val();
            console.log(timeStarted);
            console.log(timeFinished);
            if (timeFinished !== '') {
                if (compareTime(timeStarted, timeFinished) === 1) { //If start time set to be after finish time
                    return addError('The <strong>start time</strong> must be before the <strong>finish time</strong>. Please change times.');
                }
                if (compareTime(timeStarted, timeFinished) === 0) { //If start time set to be after finish time
                    return addError("The <strong>start time</strong> and <strong>finish time</strong> cannot be exactly the same. Please change times.");
                }
            }
        }
        if (dateStartedField.length > 0 && dateFinishedField.length > 0) { //Validate finish/start dates
            let dateStarted = Date.parse(dateStartedField.val()),
                dateFinished = Date.parse(dateFinishedField.val());
            if ((dateFinished > 0) && (dateStarted > dateFinished)) { //If start date set to be after finish date
                return addError('The <strong>start date</strong> must be before the <strong>finish date</strong>. Please change dates.');
            }
            if ((dateFinished > 0) && dateStarted <= 0) { //If finish date set without a start date
                return addError('The <strong>finish date</strong> should not be set without a valid <strong>start date</strong>. Please set a <strong>start date</strong>.');
            }
        }
        if (passwordField1.length > 0 && !passwordField1.prop('disabled')) {
            let passwordString = passwordField1.val();

            if (passwordString === '') {
                return addError('Cannot save with empty <strong>password</strong>. Please enter a <strong>password</strong>.');
            }
            if (passwordString !== passwordField2.val()) {
                return addError("<strong>Confirm password</strong> field and <strong>password</strong> don't match. Please re-enter <strong>passwords</strong>.");
            }
            if (passwordString.length < 8) {
                return addError('<strong>Password</strong> must be at least 8 characters long.');
            }
        }
        return true;
    }

    /**
     *
     */
    deleteDryRunButton.click(function () {
        doEntityAction('delete-dry-run');
    });

    /**
     *
     */
    detailForm.submit(function (e) { //EntityForm submitted
        console.log('submitted');
        e.preventDefault();
        const formAction = $j('input[name="submit_action"]').val();
        doEntityAction(formAction);
    });


    /**
     *
     * @param formAction
     * @returns {boolean}
     */
    function doEntityAction(formAction = '') {
        console.log('submitted');
        $j(".alert").alert('close');

        let ajaxURL = "add_entry.php",
            ajaxData = $j(".detail-form").serialize() + getFurnitureFromInputs() + '&db_action=' + formAction,
            formEntity = $j('input[name="entity"]').val(),
            defaultFailMessage = 'Failed to ' + formAction + ' ' + formEntity + '.',
            formValidated = validateFormInput();

        if (!formValidated) {
            return false;
        }

        console.log(ajaxData);
        submitButton.prop('disabled', true);
        $j.ajax({
            url: ajaxURL,
            type: 'POST',
            data: ajaxData,
            success: function (data) {
                console.log(data);
                data = JSON.parse(data);
                console.log(data);
                if (typeof data['message'] !== 'undefined') {
                    $j('.detail-form .messages').prepend(data['message']);
                }
                if (typeof data['result'] !== 'undefined' && data['result'] === 'success') {
                    console.log("success");
                    if (typeof data['redirect'] !== 'undefined' && data['redirect'] === true && typeof data['redirectURL'] !== 'undefined') {
                        location.assign(data['redirectURL']);
                    }
                    disableForm();
                    if (formAction === 'delete-dry-run') {
                        $j('#delete-for-real').click(function () {
                            doEntityAction('delete-for-real');
                        });
                    }
                    return true;
                } else {
                    console.log("fail");
                    if (typeof data['message'] === 'undefined') {
                        displayMessage(defaultFailMessage);
                    }
                    submitButton.prop('disabled', false);

                }
            },
            error: function (xhr, desc, err) {
                console.log(xhr);
                let ajaxError = 'Details: ' + desc + '\nError: ' + err;
                console.log(ajaxError);
                submitButton.prop('disabled', false);
                return addError(defaultFailMessage + '\n' + ajaxError);
            }
        });
    }

    /**
     * Get data from job form furniture rows and return as JSON
     *
     * @returns {string}
     */
    function getFurnitureFromInputs() {

        let furnitureArray = [];
        $j('div.furniture-group').each(function () {
            let ID = $j(this).find('select.furniture-name').val();
            if (ID > 0) {
                let quantity = $j(this).find('input.furniture-quantity').val();
                if (quantity > 0) {
                    let furniture = {};
                    furniture[ID] = Number(quantity);
                    furnitureArray.push(furniture);
                }
            }
        });
        if (furnitureArray.length === 0) {
            return '';
        }

        return '&furniture=' + JSON.stringify(furnitureArray);
    }

    /**
     * Function for single job page.
     */
    function jobDetailPageFunctions() {

        initRemoveFurnitureButton();

        $j("#job-status").change(function () {
            this.className = 'form-control';
            $j(this).addClass($j(this).val());
        });

        $j("#add-furniture-button").click(function () {
            let jobFurnitureLast = $j('.job-furniture-row .furniture-group').last();
            let jobFurnitureNew = jobFurnitureLast.clone();
            jobFurnitureNew.find('select').removeAttr('id').val('');
            jobFurnitureNew.find('select option').prop("selected", false).removeAttr("selected");

            jobFurnitureNew.find('input.furniture-quantity').val(1); //Set Quantity to 1
            jobFurnitureNew.find('a.remove-furniture').removeClass('disabled').prop('disabled', false); //Enable remove furniture button.
            jobFurnitureNew.insertAfter(jobFurnitureLast);

            initRemoveFurnitureButton();

        });

        /**
         *
         */
        function initRemoveFurnitureButton() {
            $j(".remove-furniture").click(function () {
                $j(this).parent().remove();
            });
        }
    }
}

/**
 * Basically an alias of displayMessage, but returns false so we can have one less line of code in logic functions.
 *
 * @param message
 * @returns {boolean}
 */
function addError(message = '') {
    displayMessage(message);
    return false;
}

/**
 *
 * @param message
 * @param type
 */
function displayMessage(message = '', type = 'danger') {
    if (message === '') {
        return false;
    }

    let location = '.detail-form .messages',
        error_html = '<div class="row"><div class="col"><div class="alert alert-'
            + type
            + ' alert-dismissible fade show my-2" role="alert">'
            + message
            + '<button type="button" class="close" data-dismiss="alert" aria-label="Close"> <span aria-hidden="true">&times;</span> </button> </div></div></div>';
    $j(location).prepend(error_html);
}

/**
 * Returns 1 if greater, -1 if less and 0 if the same
 *
 * @param startTime
 * @param finishTime
 * @returns {number}
 */
function compareTime(startTime = '', finishTime = '') {
    let time1 = new Date(),
        time2 = new Date();

    startTime = startTime.split(":");
    finishTime = finishTime.split(":");

    time1.setHours(startTime[0], startTime[1], 0, 0);
    time2.setHours(finishTime[0], finishTime[1], 0, 0);

    if (time1.getTime() > time2.getTime()) { //Start time happening after finish time. Probably a bad thing
        return 1;
    }
    if (time1.getTime() < time2.getTime()) { //Start time happening before finish time. Probably a good thing
        return -1;
    }
    return 0; //Times are the same
}
