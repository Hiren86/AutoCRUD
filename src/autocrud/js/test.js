


// kai baju form rakhavu che e select kare ane form par click kara pachi form ne highlight kare enu che
$(".img-check").click(function(){
    $('.img-check').removeClass('check');
    $(this).addClass("check");
    $('.panel-default').removeClass('shadow');
    $(this).parents('.panel-default').addClass('shadow');
});

// horizotal form - left - right - ni select box change kare eni js
$(document).ready(function() {
    $(".js-example-basic-single").select2({
        placeholder: "Select a Table"
    });
    // set all the select boxes default value
    $('#offset').val('1');
    $('#form_cols').val('6');
    $('#extra_cols').val('4');
    if($('#form_layout').val() == 'cols')
    {
        $('#form_cols').parents('.form-group').addClass('hidden');
        $('#form_type').parents('.form-group').attr('class','form-group col-sm-2 col-sm-offset-3');
        $('#extra_cols').parents('.form-group').addClass('hidden');
        $('#no_of_cols').parents('.form-group').removeClass('hidden');
        $('#cols_width').parents('.form-group').removeClass('hidden');
        $('#cols_width').val(6);
    }
    if($('#form_layout').val() == 'center')
    {
        $('#extra_cols').parents('.form-group').addClass('hidden');
        $('#form_cols').parents('.form-group').addClass('hidden');
        $('#offset').val(3);
        $('#form_type').parents('.form-group').attr('class','form-group col-sm-2 col-sm-offset-4');
    }
});

$('#form_type').change(function(){
    var off = $('#offset').val();
    var new_name = $('#array_name').val();
    var type = $('#form_type').val() + "_" + $('#form_layout').val();
    $('.preview').html(window[new_name][type]);

    //========================================================== js for left form type ================================================
    // set default value of FORM COLUMN WIDTH after changed it's value
    $('#form_cols').val('6');

    //change value of SIDE DIV WIDTH according to size of OFFSET and FORM COLUMN WIDTH
    var occupied_space = ((parseInt($('#offset').val())) * 2 ) + parseInt($('#form_cols').val());
    $('#extra_cols').empty();
    for (i = 0; i <= 12 - occupied_space; i++)
    {
        $('#extra_cols').append($('<option>',
            {
                value: i,
                text : i
            }
        ));
    }

    //apply max width to SIDE DIV WIDTH
    var max = 12 - occupied_space;
    $('#extra_cols').val(max);

    //===================================================== js for left form type ================================================
    //change class FORM COLUMN WIDTH as per user changes
    $('#form-div').attr('class', "shadow col-sm-offset-"+off + " " + "col-sm-" + $('#form_cols').val());

    //change class SIDE DIV WIDTH according to user changes
    $('#extra-div').attr('class', "equal col-sm-" + max);


    //===================================================== js for right form type ===============================================
    //change class FORM COLUMN WIDTH as per user changes
    $('#form-div-right').attr('class', "shadow col-sm-" + $('#form_cols').val());

    //change class SIDE DIV WIDTH according to user changes
    $('#extra-div-right').attr('class', "equal col-sm-" + max + " col-sm-offset-"+off);


    //================================================== js for cols form type ===================================================
    //change class FORM COLUMN WIDTH as per user changes
    $('#form-div-right').attr('class', "shadow col-sm-" + $('#form_cols').val());
    if($('#form_layout').val() == 'cols')
    {
        $('#offset').val(1);
        $('#no_of_cols').val(2);
    }
});


//============================================================= left layout =================================================================
$('#offset').change(function()
{
    if($('#form_layout').val() == 'center')
    {
        var of = parseInt($('#offset').val());
        var col = 12 - of * 2;
        $('#form-div-center').attr('class','col-sm-'+ col +' col-sm-offset-' + of +' shadow');
    }

    if($('#form_layout').val() == 'cols')
    {
        var of = parseInt($('#offset').val());
        var width = 12 - of * 2;
        $('#form-div-center').attr('class','col-sm-'+ width +' col-sm-offset-' + of +' shadow');
    }
    //change the value of select box FORM COLUMN WIDTH according to offset value
    var off = $('#offset').val();
    $('#form_cols').empty();
    for (i = off; i <= 12 - off*2; i++)
    {
        $('#form_cols').append($('<option>',
            {
                value: i,
                text : i
            }));
    }

    // set default value of FORM COLUMN WIDTH after changed it's value
    $('#form_cols').val('6');

    //change value of SIDE DIV WIDTH according to size of OFFSET and FORM COLUMN WIDTH
    var occupied_space = ((parseInt($('#offset').val())) * 2 ) + parseInt($('#form_cols').val());
    $('#extra_cols').empty();
    for (i = 0; i <= 12 - occupied_space; i++)
    {
        $('#extra_cols').append($('<option>',
            {
                value: i,
                text : i
            }
        ));
    }

    //apply max width to SIDE DIV WIDTH
    var max = 12 - occupied_space;
    $('#extra_cols').val(max);

    //change class FORM COLUMN WIDTH as per user changes
    $('#form-div').attr('class', "shadow col-sm-offset-"+off + " " + "col-sm-" + $('#form_cols').val());

    //change class SIDE DIV WIDTH according to user changes
    $('#extra-div').attr('class', "equal col-sm-" + max);
});

$('#form_cols').change(function(){
    if($('#form_layout').val() == "center")
    {
        var cols = $('#form_cols').val();
        var ofset =(12 - parseInt($('#form_cols').val())) / 2;
        $('#form-div-center').attr('class','col-sm-'+cols + ' shadow col-sm-offset-'+ofset);
    }
    else
    {
        var off = $('#offset').val();

        //change value of SIDE DIV WIDTH according to size of OFFSET and FORM COLUMN WIDTH
        var occupied_space = ((parseInt($('#offset').val())) * 2 ) + parseInt($('#form_cols').val());
        $('#extra_cols').empty();
        for (i = 0; i <= 12 - occupied_space; i++)
        {
            $('#extra_cols').append($('<option>',
                {
                    value: i,
                    text : i
                }
            ));
        }
        //apply max width to SIDE DIV WIDTH
        var max = 12 - occupied_space;
        $('#extra_cols').val(max);

        //change class FORM COLUMN WIDTH as per user changes
        $('#form-div').attr('class', "shadow col-sm-offset-"+off + " " + "col-sm-" + $('#form_cols').val());

        //change class SIDE DIV WIDTH according to user changes
        $('#extra-div').attr('class', "equal col-sm-" + max);
    }
});

$('#extra_cols').change(function(){
    var col = $('#extra_cols').val();
    //change class SIDE DIV WIDTH according to user changes
    $('#extra-div').attr('class', "equal col-sm-" + col);
});



//============================================================= right layout =================================================================
$('#offset').change(function()
{

    var off = $('#offset').val();

    var type = $('#form_type').val()+"_{{ $layout }}";
    $('.preview').html(form_array[type]);

    // set default value of FORM COLUMN WIDTH after changed it's value
    $('#form_cols').val('6');

    //change value of SIDE DIV WIDTH according to size of OFFSET and FORM COLUMN WIDTH
    var occupied_space = ((parseInt($('#offset').val())) * 2 ) + parseInt($('#form_cols').val());
    $('#extra_cols').empty();
    for (i = 0; i <= 12 - occupied_space; i++)
    {
        $('#extra_cols').append($('<option>',
            {
                value: i,
                text : i
            }
        ));
    }

    //apply max width to SIDE DIV WIDTH
    var max = 12 - occupied_space;
    $('#extra_cols').val(max);

    //=================================================== js for left form type ================================================
    //change class FORM COLUMN WIDTH as per user changes
    $('#form-div').attr('class', "shadow col-sm-offset-"+off + " " + "col-sm-" + $('#form_cols').val());

    //change class SIDE DIV WIDTH according to user changes
    $('#extra-div').attr('class', "equal col-sm-" + max);


    //================================================== js for right form type ================================================
    //change class FORM COLUMN WIDTH as per user changes
    $('#form-div-right').attr('class', "shadow col-sm-" + $('#form_cols').val());

    //change class SIDE DIV WIDTH according to user changes
    $('#extra-div-right').attr('class', "equal col-sm-" + max + " col-sm-offset-"+off);
});

$('#form_cols').change(function(){
    var off = $('#offset').val();

    //change value of SIDE DIV WIDTH according to size of OFFSET and FORM COLUMN WIDTH
    var occupied_space = ((parseInt($('#offset').val())) * 2 ) + parseInt($('#form_cols').val());
    $('#extra_cols').empty();
    for (i = 0; i <= 12 - occupied_space; i++)
    {
        $('#extra_cols').append($('<option>',
            {
                value: i,
                text : i
            }
        ));
    }
    //apply max width to SIDE DIV WIDTH
    var max = 12 - occupied_space;
    $('#extra_cols').val(max);

    //change class FORM COLUMN WIDTH as per user changes
    $('#form-div-right').attr('class', "shadow col-sm-" + $('#form_cols').val());

    //change class SIDE DIV WIDTH according to user changes
    $('#extra-div-right').attr('class', "equal col-sm-" + max + " col-sm-offset-"+off);
});

$('#extra_cols').change(function(){
    var off = $('#offset').val();
    var col = $('#extra_cols').val();
    //change class SIDE DIV WIDTH according to user changes
    $('#extra-div-right').attr('class', "equal col-sm-" + col + " col-sm-offset-"+off);
});


//======================================================= js for form type = cols ================================================
$('#no_of_cols').change(function(){
    var no_of_cols = $('#no_of_cols').val();
    var col = 12/parseInt($('#no_of_cols').val());
    $('.cols-form .form-group').attr('class','form-group col-sm-'+col);

    $("#form-div-center .clearfix").each(function(index) {
        $(this).remove();
    });

    $("#form-div-center .form-group").each(function(index) {
        if ((index+1) % no_of_cols == 0)
        {
            $(this).after('<div class="clearfix"></div>');
        }
    });
});

$('#reset').click(function(){
    window.location.reload();
});


//=============================================================================================================================
//
//                                              createFormField
//
//=============================================================================================================================

var formInput = {
    'text' : '<div class="form-group col-md-2 form-input"><label class="control-label">placeholder</label><input class="form-control ext" name="placeholder" type="text" placeholder="It\'s a placeholder"> </div> <div class="form-group col-md-2 form-input"> <label class="control-label">Minlength</label> <input class="form-control ext" name="minlength" pattern="[0-9]*" title="It should contains only number" type="text"> </div> <div class="form-group col-md-2 form-input"> <label class="control-label">Maxlength</label> <input class="form-control ext" name="maxlength" pattern="[0-9]*" title="It should contains  only number" type="text"> </div> <div class="form-group col-md-2 form-input"> <label class="control-label">Pattern</label> <input class="form-control ext" name="pattern" type="text" data-toggle="tooltip" data-placement="bottom" title="Alphanumeric(With Spaces) - [a-zA-Z0-9\s]+ </br>Alphanumeric (No Spaces) - [a-zA-Z0-9]+ </br>Credit/Bank Account - [0-9]{13,16} </br>ExtendedZip Code - (\d{5}([\-]\d{4})?) </br>Short Zip Code - (\d{5}?) </br>Numbers only - [0-9]*"> </div> <div class="form-group col-md-2 form-input"> <label class="control-label">Required</label> <div class="checkbox"> <label> <input type="checkbox" class="ext" name="required" value="required"> Should not be empty</label></div></div>',

    'number' : '<div class="form-group col-md-2 form-input"> <label class="control-label">placeholder</label> <input class="form-control ext" name="placeholder" type="text" placeholder="It\'s a placeholder"> </div> <div class="form-group col-md-2 form-input"> <label class="control-label">Min</label> <input class="form-control ext" name="min" pattern="[0-9]*" title="It should contains only number" type="text"> </div> <div class="form-group col-md-2 form-input"> <label class="control-label">Max</label> <input class="form-control ext" name="max" pattern="[0-9]*" title="It should contains only number" type="text"> </div> <div class="form-group col-md-2 form-input"> <label class="control-label">step</label> <input class="form-control ext" name="step" pattern="[0-9]*" title="It should contains only number" type="text"> </div> <div class="form-group col-md-2 form-input"> <label class="control-label">Required</label> <div class="checkbox"> <label> <input type="checkbox" class="ext" name="required" value="required"> Should not be empty</label> </div> </div>',

    'email' : '<div class="form-group col-md-2 form-input"> <label class="control-label">placeholder</label> <input class="form-control ext" name="placeholder" type="text" placeholder="It\'s a placeholder"> </div> <div class="form-group col-md-2 form-input"> <label class="control-label">Required</label> <div class="checkbox"> <label> <input type="checkbox" class="ext" name="required" value="required"> Should not be empty</label> </div> </div>',

    'password' : ' <div class="form-group col-md-2 form-input"> <label class="control-label">placeholder</label> <input class="form-control ext" name="placeholder" type="text" placeholder="It\'s a placeholder"> </div> <div class="form-group col-md-2 form-input"> <label class="control-label">Required</label> <div class="checkbox"> <label> <input type="checkbox" class="ext" name="required" value="required"> Should not be empty</label> </div> </div>',

    'url' : '<div class="form-group col-md-2 form-input"> <label class="control-label">placeholder</label> <input class="form-control ext" name="placeholder" type="text" placeholder="It\'s a placeholder"> </div> <div class="form-group col-md-2 form-input"> <label class="control-label">Required</label> <div class="checkbox"> <label> <input type="checkbox" class="ext" name="required" value="required"> Should not be empty</label> </div> </div>',

    'date' : '<div class="form-group col-md-2 form-input"> <label class="control-label">Required</label> <div class="checkbox"> <label> <input type="checkbox" class="ext" name="required" value="required"> Should not be empty</label> </div> </div>',

    'time' : '<div class="form-group col-md-2 form-input"> <label class="control-label">Required</label> <div class="checkbox"> <label> <input type="checkbox" class="ext" name="required" value="required"> Should not be empty</label> </div> </div>',

    'month' : ' <div class="form-group col-md-2 form-input"> <label class="control-label">Required</label> <div class="checkbox"> <label> <input type="checkbox" class="ext" name="required" value="required"> Should not be empty</label> </div> </div>',

    'week' : '<div class="form-group col-md-2 form-input"> <label class="control-label">Required</label> <div class="checkbox"> <label> <input type="checkbox" class="ext" name="required" value="required"> Should not be empty</label> </div> </div>',

    'textarea' : '<div class="form-group col-md-2 form-input"> <label class="control-label">placeholder</label> <input class="form-control ext" name="placeholder" type="text" placeholder="It\'s a placeholder"> </div> <div class="form-group col-md-2 form-input"> <label class="control-label">Rows</label> <input class="form-control ext" name="rows" pattern="[0-9]*"  title="It should contains only number" type="text"> </div> <div class="form-group col-md-2 form-input"> <label class="control-label">Minlength</label> <input class="form-control ext" name="minlength" pattern="[0-9]*" title="It should contains only number" type="text"> </div> <div class="form-group col-md-2 form-input"> <label class="control-label">Maxlength</label> <input class="form-control ext" name="maxlength" pattern="[0-9]*" title="It should contains only number" type="text"> </div> <div class="form-group col-md-2 form-input"> <label class="control-label">Required</label> <div class="checkbox"> <label> <input type="checkbox" class="ext" name="required" value="required"> Should not be empty</label> </div> </div>',

    'radio' : '<div class="col-md-8 form-group"> <label class="control-label">Add all options</label> <input type="text" class="form-control tags ext" data-role="tagsinput" placeholder="New option" name="radio" required> </div> <div class="col-md-2 form-group"><label class="control-label">Required</label> <div class="checkbox"> <label> <input type="checkbox" class="ext" name="required" value="required">  Should not be empty</label> </div>',

    'checkbox' : ' <div class="col-md-8 form-group"> <label class="control-label">Add all options</label><input type="text" class="form-control tags ext" data-role="tagsinput" placeholder="New option" name="checkbox" required> </div> <div class="col-md-2 form-group"><label class="control-label">Required</label> <div class="checkbox"> <label> <input type="checkbox" class="ext" name="required" value="required"> Should not be empty</label> </div> </div> ',

    'select' : '<div class="col-md-8 form-group"> <label class="control-label">Add all options</label> <input type="text" class="form-control tags ext" data-role="tagsinput" placeholder="New option" name="select" required> </div>  <div class="col-md-2 form-group"><label class="control-label">Required</label> <div class="checkbox"> <label> <input type="checkbox" class="ext" name="required" value="required"> Should not be empty</label> </div> </div> ',

    'multiselect' : '<div class="col-md-8 form-group"> <label class="control-label">Add all options</label> <input type="text" class="form-control tags ext" data-role="tagsinput" placeholder="New option" name="multiselect" required> </div> <div class="col-md-2 form-group"><label class="control-label">Required</label> <div class="checkbox"> <label> <input type="checkbox" class="ext" name="required" value="required"> Should not be empty</label> </div> </div> ',

    'range' : ' <div class="form-group col-md-2 form-input"> <label class="control-label">value</label> <input class="form-control ext" name="value" pattern="[0-9]*" title="It should contains only number" type="text"> </div> <div class="form-group col-md-2 form-input"> <label class="control-label">min</label> <input class="form-control ext" name="min" pattern="[0-9]*" title="It should contains only number" type="text"> </div> <div class="form-group col-md-2 form-input"> <label class="control-label">max</label> <input class="form-control ext" name="max" pattern="[0-9]*" title="It should contains only number" type="text"> </div> <div class="form-group col-md-2 form-input"> <label class="control-label">step</label> <input class="form-control ext" name="step" pattern="[0-9]*" title="It should contains only number" type="text"> </div>'
};


//change name of all element
$('#btnFormField').click(function(){
    $('#createFormFieldDiv .row').each(function () {
        var fieldname = $(this).find('.field_name').val();
        $(this).find('.ext').each(function(){
            var nm = $(this).attr('name');
            $(this).attr('name',fieldname + nm);
        });
    });
    $('#createFormFieldBtn').remove();
    $('#btnFormField').after('<input type="submit" value="" id="createFormFieldBtn" class="hidden">');
    $('#createFormFieldBtn').trigger('click');
});
//change name of all element for update page
$('#btnUpdateFormField').click(function(){
    $('#createFormFieldDiv .row').each(function () {
        var fieldname = $(this).find('.field_name').val();
        $(this).find('.ext').each(function(){
            var nm = $(this).attr('name');
            $(this).attr('name',fieldname + nm);
        });
    });
    $('#createFormFieldBtn').remove();
    $('#btnUpdateFormField').after('<input type="submit" value="" id="createFormFieldBtn" class="hidden">');
    $('#createFormFieldBtn').trigger('click');
});


$(document).change(function(){
    $('.tags').tagsinput({
    });
});

//add value to hidden field view_data for on page createLayout
$('#add_view_data').click(function(){
    $('#view_data').val($('.preview').html());
});

// asign route to form according to button
$('.edit_btn').click(function(){
    if($('.chkbx:checked').length == 0)
    {
        alert('You must have to select one row');
        return false;
    }
    else if($('.chkbx:checked').length > 1)
    {
        alert("You can't go ahead with multiple rows");
        return false;
    }
    else
    {
        var formid = $(this).parent().parent().parents('form').attr('id');
        var form = $('#'+formid);
        $(form).children('.token').remove();
        var baseURL = localStorage.getItem("baseURL");
        var rowId = $('.chkbx:checked').val();
        form.attr('action', baseURL + '/temp/' + rowId + '/edit');
        form.submit();
    }
});

// asign route to form according to button
$('.view_btn').click(function(){
    if($('.chkbx:checked').length == 0)
    {
        alert('You must have to select one row');
        return false;
    }
    else if($('.chkbx:checked').length > 1)
    {
        alert("You can't go ahead with multiple rows");
        return false;
    }
    else
    {
        var formid = $(this).parent().parent().parents('form').attr('id');
        var form = $('#'+formid);
        $(form).children('.token').remove();
        var baseURL = localStorage.getItem("baseURL");
        var rowId = $('.chkbx:checked').val();
        form.attr('action', baseURL + '/temp/' + rowId );
        form.submit();
    }
});

// asign route to form according to button
$('.delete_btn').click(function(){
    if($('.chkbx:checked').length == 0)
    {
        alert('You must have to select one row');
        return false;
    }
    else if($('.chkbx:checked').length > 1)
    {
        alert("You can't go ahead with multiple rows");
        return false;
    }
    else
    {
        var formid = $(this).parent().parent().parents('form').attr('id');
        var form = $('#'+formid);
        var baseURL = localStorage.getItem("baseURL");
        var rowId = $('.chkbx:checked').val();
        form.attr('method', 'post' );
        form.attr('action', baseURL + '/temp/' + rowId );
        var method_type = $(form).children('.method_type');
        method_type.html("");
        $(method_type).html('<input name="_method" type="hidden" value="DELETE">');
        form.submit();
    }
});