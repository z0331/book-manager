toggleVisibility = function() {
    return this.css('visibility', function(i, visibility) {
        return (visibility == 'visibile') ? 'collapse' : 'visible';
    });
}

$(document).ready(function() {

    //Toggle season tab visibility on index.php
    $('.season').click(function(e) {
        e.preventDefault();
        if ($(this).parent().next('tbody').css('visibility') == 'collapse') {
            $(this).parent().next('tbody').css('visibility', 'visible');
        } else {
            $(this).parent().next('tbody').css('visibility', 'collapse');
        }
        
    });

    /*
    * Add and delete new contributors from basic info 
    */
    $('#new_contributor').click(function(e) {
        e.preventDefault();
        $('#contributors').append('<div id="contributor">'
                + '<label for="contributor">Contributor: </label>'
                + '<input type="text" id="contributor" name="contributor[]" />'
                + '</div>');
    });

    $('#delete_contributor').click(function(e) {
        e.preventDefault();
        $('#contributors #contributor:last').remove();
    });

    /*
    * Add and delete new custom fields
    */
    $('#new_field').click(function(e) {
        e.preventDefault();
        
        $('#custom-fields').append('<div id="custom-field">Field:<br>'
                + '<input type="text" id="field_name" name="field[name][]" value="Field Name" />'
                + '<input type="text" id="field" name="field[value][]" />'
                + '</div>');
    });

    $('#delete_field').click(function(e) {
        e.preventDefault();
        $('#custom-fields #custom-field:last').remove();
    });

    /*
    * Sets default scheduling for to press, etc. based on given pub date.
    */
    $('#default_pub_schedule').click(function(e) {
        e.preventDefault();
        var pub_date = moment($('#pub_date').val());
        var arc_prod = moment(pub_date.subtract(26, 'w'));  //To production for ARC
        var arc_press = moment(pub_date.add(2, 'w'));       //To press for ARC
        var to_prod = moment(pub_date.add(8, 'w'));         //To production no ARC
        var to_press = moment(pub_date.add(5, 'w'));        //To press final

        if($('#arc').is(':checked')) {  //Check if ARC box is checked to fill in those dates
            $('#arc_prod_date').val(arc_prod.format('YYYY-MM-DD'));
            $('#arc_press_date').val(arc_press.format('YYYY-MM-DD'));
        }
        else {
            $('#arc_prod_date').val('No ARC');
            $('#arc_press_date').val('No ARC');
        }

        $('#prod_date').val(to_prod.format('YYYY-MM-DD'));
        $('#press_date').val(to_press.format('YYYY-MM-DD'));
    });

    /*
    * Sets default editorial scheduling based on given manuscript delivery.
    */
    $('#default_edit_schedule').click(function(e) {
        e.preventDefault();
        var manuscript_date = moment($('#manuscript_date').val());
        var edits_to_author = moment(manuscript_date.add(2, 'w'));      //Edits to author
        var revisions_in = moment(manuscript_date.add(4, 'w'));         //Revisions back from author
        var to_copyedit = moment(manuscript_date.add(1, 'w'));          //MS to copyediting
        var manuscript_finalized = moment(manuscript_date.add(2, 'w')); //Finalize MS

        $('#edits_to_author').val(edits_to_author.format('YYYY-MM-DD'));
        $('#revisions_in').val(revisions_in.format('YYYY-MM-DD'));
        $('#to_copyedit').val(to_copyedit.format('YYYY-MM-DD'));
        $('#manuscript_finalized').val(manuscript_finalized.format('YYYY-MM-DD'));
    });

    /*
    * On Book View, checks status of checkbox when changed
    * If checked, marks current date as completed date
    * If unchecked, marks Not Completed
    */
    $('.complete-check').change(function() {
        if(this.checked) {
            var today = moment();
            $(this).next().text(today.format('YYYY-MM-DD'));
        }
        else if(!this.checked) {
            $(this).next().text('Not Complete');
        }
    })
});

