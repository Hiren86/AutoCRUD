@extends('autocrud::layouts.admin')

@section('content')
    <div class="container-fluid">
        <legend>ADD EXTRA FIELDS FOR TABLE</legend>
        <div class="col-md-12">
            <form action="{{url('createExtraFields')}}" method="post" role="form">
                <div class="extra-fields">
                    <div class="form-group">
                        <h5 class="text-success">
                            <input type="text" name="field_lbl[]" id="" value="Column Heading" class="custom-lbl" onblur="changeLableValue(this)">
                        </h5>
                        <div class="col-sm-2">
                            <label for="">Field Type</label>
                            <select name="" class="field-type form-control">
                                <option value=""> -- Select One --</option>
                                <option value="calculate">calculate</option>
                                <option value="condition">conditional</option>
                            </select>
                        </div>
                    </div>
                    <div class="clearfix"></div>
                </div>
                <input type="button" value="Add more" class="m-top btn btn-success" id="add-fields">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <input type="hidden" name="refid" id="refid" value="{{ $refid }}">
                <input type="hidden" name="total_fields" id="total_fields">
                <input type="hidden" name="type" id="type">
                <input type="hidden" name="allUID" id="allUID">
                <div class="form-group">
                    <div class="col-sm-11">
                        <button type="button" class="btn btn-primary btn-lg pull-right" id="generateIds">next</button>
                        <input type="submit" value="" id="extrasubmit" class="hidden">
                    </div>
                </div>
            </form>
        </div>

        <div class="modal fade" id="modal-if">
            <div class="custom-modal-dialog modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title">Write your condition here.</h4>
                        <p class="text-warning">*if you match string in condition then, write it inside quotation mark(" ").</p>
                    </div>
                    <div class="modal-body condition-modal-body">

                    </div>
                    <div class="clearfix"></div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary add-condition">Save changes</button>
                    </div>
                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div><!-- /.modal -->



        <div class="modal fade" id="modal-action">
            <div class="custom-modal-dialog modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <p class="text-warning">*if you print string in action then, write it inside quotation mark(" ").</p>
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title">Action if condition true.</h4>
                    </div>
                    <div class="modal-body if-then-modal-body">

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary add-if-action">Save changes</button>
                    </div>
                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div><!-- /.modal -->



        <div class="modal fade" id="modal-else-action">
            <div class="custom-modal-dialog modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title">Action if condition false.</h4>
                        <p class="text-warning">*if you print string in else action then, write it inside quotation mark(" ").</p>
                    </div>
                    <div class="modal-body else-modal-body">

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary add-else-action">Save changes</button>
                    </div>
                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div><!-- /.modal -->

        <div class="clearfix"></div>

    </div>
@endsection

@section('custom-js')
    {{--script added to custom.js file--}}

    <script type="text/javascript">
        //js for extraFields.blade.php
        if($('#refid').val())
        {
            $.ajax({
                type:'get',
                url: localStorage.getItem("baseURL") + "/getJoinFields",
                data:{'refid':$('#refid').val()},
                success:function(data)
                {
                    var len = data.length;
                    var calcselect = '<div class="form-group col-sm-2"><label for="">Select Field</label><select name="name" id="inputID" class="form-control"' +
                            ' for="calcselect"><option value=""> -- Select One --</option>';
                    for(var i=0; i<len; i++)
                    {
                        calcselect = calcselect + '<option value="'+ data[i]["table_name"]+ '~~' + data[i]["field_name"] +'" >' + data[i]["table_name"]+ ' :- ' + data[i]["field_name"] + '</option>';
                    }
                    calcselect = calcselect + '</select></div>';

                    var condifselect = '<div class="form-group col-sm-2"><label for="">Select Field</label><select name="name" id="inputID" class="form-control" for="condifselect"><option value=""> -- Select One --</option>';
                    for(var i=0; i<len; i++)
                    {
                        condifselect = condifselect + '<option value="'+ data[i]["table_name"]+ '~~' + data[i]["field_name"] +'" >' + data[i]["table_name"]+ ' :- ' + data[i]["field_name"] + '</option>';
                    }
                    condifselect = condifselect + '</select></div>';

                    var filterselect = '<div class="form-group col-sm-3"><label for="">Select Field</label><select name="filterselect" class="form-control"><option value=""> -- Select One --</option>';
                    for(var i=0; i<len; i++)
                    {
                        filterselect = filterselect + '<option value="'+ data[i]["table_name"]+ '~~' + data[i]["field_name"] +'" >' + data[i]["table_name"]+ ' :- ' + data[i]["field_name"] + '</option>';
                    }
                    filterselect = filterselect + '</select></div>';

                    var condifthenselect = '<div class="form-group col-sm-2"><label for="">Select Field</label><select name="name" id="inputID" class="form-control" for="condifthenselect"><option value=""> -- Select One --</option>';
                    for(var i=0; i<len; i++)
                    {
                        condifthenselect = condifthenselect + '<option value="'+ data[i]["table_name"]+ '~~' + data[i]["field_name"] +'" >' + data[i]["table_name"]+ ' :- ' + data[i]["field_name"] + '</option>';
                    }
                    condifthenselect = condifthenselect + '</select></div>';

                    var condelsethenselect = '<div class="form-group col-sm-2"><label for="">Select Field</label><select name="name" id="inputID" class="form-control" for="condelsethenselect"><option value=""> -- Select One --</option>';
                    for(var i=0; i<len; i++)
                    {
                        condelsethenselect = condelsethenselect + '<option value="'+ data[i]["table_name"]+ '~~' + data[i]["field_name"] +'" >' + data[i]["table_name"]+ ' :- ' + data[i]["field_name"] + '</option>';
                    }
                    condelsethenselect = condelsethenselect + '</select></div>';

                    localStorage.setItem('calcselect',calcselect);
                    localStorage.setItem('condifselect',condifselect);
                    localStorage.setItem('filterselect',filterselect);
                    localStorage.setItem('condifthenselect',condifthenselect);
                    localStorage.setItem('condelsethenselect',condelsethenselect);
                    localStorage.setItem('calcopr','<div class="form-group col-sm-1"><label>Oprator,()</label><input type="text" class="form-control" id="" name="" for="calcopr"></div>');
                    localStorage.setItem('condifopr','<div class="form-group col-sm-2"><label>Oprator,()</label><input type="text" class="form-control" id="" name="" for="condifopr"></div>');
                    localStorage.setItem('condifthenopr','<div class="form-group col-sm-2"><label>Oprator,()</label><input type="text" class="form-control" id="" name="" for="condifthenopr"></div>');
                    localStorage.setItem('condelsethenopr','<div class="form-group col-sm-2"><label>Oprator,()</label><input type="text" class="form-control" id="" name="" for="condelsethenopr"></div>');
                }
            });
            var t = $.parseHTML(localStorage.getItem('select'));
        }

    </script>
@endsection