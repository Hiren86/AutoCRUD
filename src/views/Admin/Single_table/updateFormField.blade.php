@extends('autocrud::layouts.admin')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="col-md-12">
                    <center><h3 class="b-bottom heading">CREATE FORM FIELDS</h3></center>
                </div>
                <div class="clearfix"></div>
                <div class="col-md-12" id="createFormFieldDiv">
                    <form action="{{ url('updateForm') }}" method="post" role="form">
                        <fieldset>
                            <div class="clearfix"></div>
                            <?php $i=0; ?>
                            @foreach($columns as $item)
                                <h5 class="text-success"><input type="text" name="field-lbl{{$i}}" id="" value="{{ str_replace('_', " ",$lable_names[$i]->name) }}" class="custom-lbl"></h5>
                                <div class="row">
                                    <div class="form-group col-md-2 form-input">
                                        <label class="control-label">Type</label>
                                        <select name="inputType{{ $i }}" id="inputType{{ $i }}" class="form-control select2 createFormInputType" required>
                                            <option value=""></option>
                                            <option value="none">none</option>
                                            <option value="text">Text</option>
                                            <option value="number">Number</option>
                                            <option value="email">Email</option>
                                            <option value="password">Password</option>
                                            <option value="checkbox">Checkbox</option>
                                            <option value="radio">Radio</option>
                                            <option value="select">Select</option>
                                            <option value="multiselect">Multiselect</option>
                                            <option value="url">URL</option>
                                            <option value="date">Date</option>
                                            <option value="time">Time</option>
                                            <option value="month">Month</option>
                                            <option value="week">Week</option>
                                            <option value="range">Range</option>
                                            <option value="textarea">Textarea</option>
                                        </select>
                                    </div>
                                    <input type="hidden" name="no_of_fields" value="{{ sizeof($columns) }}">
                                    <input type="hidden" class="field_name" name="field{{$i++}}" value="{{$item}}">
                                    <input type="hidden" id="table" name="table" value="{{ $table }}">
                                </div>
                                <div class="clearfix"></div>
                            @endforeach
                            <div class="col-sm-11">
                                <input type="button" value="submit" class="pull-right btn btn-lg btn-success" id="btnUpdateFormField">
                            </div>
                        </fieldset>
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    </form>
                </div>
                <div class="clearfix m-top"></div>
            </div>
        </div>
    </div>
@endsection


@section('custom-js')
    <script type="text/javascript">
        $(document).ready(function() {
            $(".js-example-basic-single").select2({
                placeholder: "Select a Table"
            });

            var table = $('#table').val();

            $.ajax({
                method:'get',
                url:'getFormFields',
                data:{table:table},
                success:function(data)
                {
                    var i = 0;
                    var len = data.length;
                    for(i=0; i<len; i++)
                    {
                        $('#inputType'+i).select2({}).val(data[i]['type']).change();
                        var main = $('#inputType0').parents().parents($('.row'));
                    }
                    var i = 0;
                    $('#createFormFieldDiv').find('.row').each(function(){
                        var type = $(this).find($('select')).val();
                        if(type == 'text')
                        {
                            $(this).find($('.ext')).each(function(){
                                if($(this).attr('name') == 'placeholder')
                                    $(this).val(data[i]['placeholder']);
                                else if($(this).attr('name') == 'maxlength')
                                    $(this).val(data[i]['maxlength']);
                                else if($(this).attr('name') == 'minlength')
                                    $(this).val(data[i]['minlength']);
                                else if($(this).attr('name') == 'pattern')
                                    $(this).val(data[i]['pattern']);
                                else
                                {
                                    if(data[i]['required'] == 'required')
                                        $(this).prop('checked',true);
                                }
                            });
                        }
                        else if(type == 'number')
                        {
                            $(this).find($('.ext')).each(function(){
                                if($(this).attr('name') == 'placeholder')
                                    $(this).val(data[i]['placeholder']);
                                else if($(this).attr('name') == 'max')
                                    $(this).val(data[i]['maxlength']);
                                else if($(this).attr('name') == 'min')
                                    $(this).val(data[i]['minlength']);
                                else if($(this).attr('name') == 'step')
                                    $(this).val(data[i]['step']);
                                else
                                {
                                    if(data[i]['required'] == 'required')
                                        $(this).prop('checked',true);
                                }
                            });
                        }
                        else if(type == 'radio' || type == 'checkbox' || type == 'select' || type == 'multiselect')
                        {
                            $(this).find($('.ext')).each(function(){
                                if($(this).attr('name') == 'select' || $(this).attr('name') == 'multiselect' || $(this).attr('name') == 'radio' || $(this).attr('name') == 'checkbox')
                                {
                                    var arr = data[i]['value'].split(',');
                                    for(j=0; j < arr.length; j++)
                                        $(this).tagsinput('add', arr[j]);
                                }
                                else
                                {
                                    console.log(data[i]['required']);
                                    if(data[i]['required'] != '')
                                        $(this).prop('checked',true);
                                }
                            });
                        }
                        else if(type == 'range')
                        {
                            $(this).find($('.ext')).each(function(){
                                if($(this).attr('name') == 'value')
                                    $(this).val(data[i]['value']);
                                else if($(this).attr('name') == 'max')
                                    $(this).val(data[i]['maxlength']);
                                else if($(this).attr('name') == 'min')
                                    $(this).val(data[i]['minlength']);
                                else
                                    $(this).val(data[i]['step']);
                            });
                        }
                        else if(type == 'textarea')
                        {
                            $(this).find($('.ext')).each(function(){
                                if($(this).attr('name') == 'placeholder')
                                    $(this).val(data[i]['placeholder']);
                                else if($(this).attr('name') == 'maxlength')
                                    $(this).val(data[i]['maxlength']);
                                else if($(this).attr('name') == 'minlength')
                                    $(this).val(data[i]['minlength']);
                                else if($(this).attr('name') == 'rows')
                                    $(this).val(data[i]['rows']);
                                else
                                {
                                    if(data[i]['required'] == 'required')
                                        $(this).prop('checked',true);
                                }
                            });
                        }
                        else if(type == 'email' || type == 'password' || type == 'url')
                        {
                            $(this).find($('.ext')).each(function(){
                                if($(this).attr('name') == 'placeholder')
                                    $(this).val(data[i]['placeholder']);
                                else
                                {
                                    console.log(data[i]['required']);
                                    if(data[i]['required'] == 'required')
                                        $(this).prop('checked',true);
                                }
                            });
                        }
                        else if(type == 'date' || type == 'time'|| type == 'month'|| type == 'week')
                        {
                            $(this).find($('.ext')).each(function() {
                                if (data[i]['required'] == 'required')
                                    $(this).prop('checked', true);
                            });
                        }
                        i++;
                    });
                }
            });
        });

        $('.createFormInputType').change(function(){
            $(this).parent().siblings('.form-group').remove();
            var createFormInputType = $(this).val();
            $(this).parent().after(formInput[createFormInputType]);
        });
    </script>
@endsection