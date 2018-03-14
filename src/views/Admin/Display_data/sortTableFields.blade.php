@extends('autocrud::layouts.admin')

@section('content')
    <div class="container">
        <legend>ARRANGE TABLE ROWS IN ORDER TO DISPLAY IN TABLE</legend>
        <form action="{{url("createMultiTableView")}}" method="post" role="form" id="frmGenerateOrder">
            <div class="row" style="margin-bottom: 20px;">
                <div class="col-md-10 col-md-offset-1">
                    <div class="error-msg alert alert-dismissible alert-danger hidden">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        <strong>Opps!</strong> <span class="msg"></span>
                    </div>
                </div>
                <div class="col-md-6 col-md-offset-3 shadow">
                    <div class="row">
                        <div class="col-md-3">
                            <table class="table table-hover sequence">
                                <thead>
                                <tr>
                                    <th>Number</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php $i = 0 ?>
                                @foreach($fields as $field)
                                    <tr>
                                        <td>{{ ++$i }}</td>
                                    </tr>
                                @endforeach
                                @if($data)
                                    @foreach($data as $field)
                                        <tr>
                                            <td>{{ ++$i }}</td>
                                        </tr>
                                    @endforeach
                                @endif
                                </tbody>
                            </table>
                        </div>
                        <div class="col-md-9" >
                            <table class="table table-hover">
                                <thead>
                                <tr>
                                    <th>Field Name</th>
                                    <th>Table Name</th>
                                    <th>Remove</th>
                                </tr>
                                </thead>
                                <tbody class="sortable">
                                @foreach($fields as $field)
                                    <tr>
                                        <td>{{ $field->field_name }}</td>
                                        <td class="tblname">{{ $field->table_name }}</td>
                                        <td class="remove"><a href="#"><span class="glyphicon glyphicon-remove text-danger"></span></a></td>
                                        <input type="hidden" name="order[]" value="{{ $field->field_name ."~~".$field->table_name }}">
                                    </tr>
                                @endforeach
                                @if($data)
                                    @foreach($data as $field)
                                        <tr>
                                            <?php $t = explode("*-*",$field) ?>
                                            <td>{{ $t[0]  }}</td>
                                            <td>Extra Added</td>
                                            <input type="hidden" name="order[]" value="{{ $t[0] }}">
                                        </tr>
                                    @endforeach
                                @endif
                                </tbody>
                            </table>
                            <div class="row p-bottom">
                                <div class="col-md-4">
                                    <a class="btn btn-success" data-toggle="modal" href="#filter" id="btn-filter">filter</a>
                                </div>
                                <div class="col-md-4">
                                    <a class="btn btn-primary" data-toggle="modal" id="fadeup-modal" href="#modal-id">Next</a>
                                </div>
                            </div>

                            <input type="submit" class="hidden" id="generateOrderSubmit">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            <input type="hidden" name="refno" value="{{$refno}}">
                            <input type="hidden" name="no_of_filter" id="no_of_filter" value="">
                            {{--filter modal--}}
                            <div class="modal fade" id="filter">
                                <div class="modal-dialog custom-modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <button type="button" class="close" data-dismiss="modal"
                                                    aria-hidden="true">&times;</button>
                                            <h4 class="modal-title">Add filter</h4>
                                            <p class="text-warning">* Use Laravel query builder for filter <br> * Avoid equle to (=)</p>
                                        </div>
                                        <div class="modal-body" id="filter-body">
                                            <div id="div-filter-body">
                                                <div class="row main-row">
                                                    <div class="col-md-3">
                                                        <label for="">Filter Type</label>
                                                        <select name="filterType" id="0" class="form-control filterType">
                                                            <option value=""> -- Select One -- </option>
                                                            <option value="where">where</option>
                                                            <option value="orWhere">orWhere</option>
                                                            <option value="whereColumn">whereColumn</option>
                                                            <option value="whereIn">whereIn</option>
                                                            <option value="whereNotIn">whereNotIn</option>
                                                            <option value="whereNull">whereNull</option>
                                                            <option value="whereNotNull">whereNotNull</option>
                                                            <option value="whereBetween">whereBetween</option>
                                                            <option value="whereNotBetween">whereNotBetween</option>
                                                            <option value="groupBy">groupBy</option>
                                                            <option value="orderBy">orderBy</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-9">
                                                        <div class="row" id="filter-fields0">

                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="clearfix"></div>
                                            </div>
                                            <div class="form-group m-top">
                                                <button type="button" id="more-filter" class="btn btn-info">more filter</button>
                                            </div>
                                        </div>
                                        <div class="clearfix"></div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-default" data-dismiss="modal">Close
                                            </button>
                                            <button type="button" id="filter-save-and-next" class="btn btn-primary">Save and next</button>
                                        </div>
                                    </div><!-- /.modal-content -->
                                </div><!-- /.modal-dialog -->
                            </div><!-- /.modal -->

                            {{--get the view name from user--}}
                            <div class="modal fade" id="modal-id">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                                            <h4 class="modal-title">Name of view and controller</h4>
                                        </div>
                                        <div class="modal-body">
                                            <input type="text" name="name_of_view" class="form-control" required="required">
                                        </div>

                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                            <button type="submit" class="btn btn-primary">Save changes</button>
                                        </div>
                                    </div><!-- /.modal-content -->
                                </div><!-- /.modal-dialog -->
                            </div><!-- /.modal -->
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection

@section('custom-js')
    <script src="{{ asset('js/jquery-ui.js') }}"></script>
    <script type="text/javascript">
        $(document).ready(function() {
            $(".js-example-basic-single").select2({
                placeholder: "Select a Table",
                allowClear: true
            });

            $('.sortable').sortable();

            $('#generateOrder').click(function(){
                $('#fadeup-modal').trigger('click');
            });
            $('#submit-simple').removeClass('hidden');
            $('#btn-filter').trigger('click');


            var tblname = "";
            var tbls = [];
            $('.tblname').each(function(){
                if($(this).text() != tblname)
                {
                    tbls.push($(this).text());
                    tblname = $(this).text();
                }
            });
            localStorage.setItem('tbls',tbls);
            $('.sortable').sortable();
            $('#submit-simple').removeClass('hidden');
        });


        $('.remove').click(function(){
            $(this).closest('tr').remove();
            $('.sequence').find('tr:last').remove();
        });

        $(document).on('change','.filterType',function(){
            var num = $(this).attr("id");
            if($(this).val() == "orderBy" || $(this).val() == "groupBy" || $(this).val() == "whereNull" || $(this).val() == "whereNotNull")
            {
                $('#filter-fields' + num).empty().html(localStorage.getItem('filterselect'));
            }
            else if($(this).val() == "whereBetween" || $(this).val() == "whereNotBetween")
            {
                $('#filter-fields' + num).empty().html(localStorage.getItem('filterselect') + '<div class="form-group col-sm-3"> <label>From</label><input class="form-control" name="from" type="text"></div><div class="form-group col-sm-3"> <label>To</label><input class="form-control" type="text" class="to" name="to"></div>');
            }
            else if($(this).val() == "whereColumn" ){
                $('#filter-fields' + num).empty().html(localStorage.getItem('filterselect') + '<div class="form-group col-sm-3"> <label>Relation</label><input class="form-control" type="text" name="relation"></div>' + localStorage.getItem('filterselect'));
            }
            else if($(this).val() == "where" || $(this).val() == "orWhere")
            {
                $('#filter-fields' + num).empty().html(localStorage.getItem('filterselect') + '<div class="form-group col-sm-3"> <label>Relation</label><input class="form-control" type="text" name="relation"></div>' + '<div class="form-group col-sm-3"> <label>Value</label><input class="form-control" type="text" name="value"></div>');
            }
            else if($(this).val() == "")
            {
                $('#filter-fields' + num).empty();
            }
            else
            {
                $('#filter-fields' + num).empty().html(localStorage.getItem('filterselect') + '<div class="form-group col-sm-3"> <label>Relation</label><input class="form-control" type="text" name="relation"></div>' + '<div class="form-group col-sm-6"> <label>Array</label><input class="form-control array_place" type="text" name="array" ></div>');
                $('.array_place').attr('placeholder','1,2,3 or "a","b","c"');
            }
        });

        $('#more-filter').click(function(){
            var num = $('.filterType').last().attr("id");
            $('#div-filter-body').append('<div class="row main-row"> <div class="col-md-3"> <label for="">Filter Type</label> <select name="filterType" id="'+ (parseInt(num)+1) +'" class="form-control filterType"> <option value=""> -- Select One -- </option><option value="where">where</option><option value="orWhere">orWhere</option><option value="whereColumn">whereColumn</option><option value="whereIn">whereIn</option><option value="whereNotIn">whereNotIn</option><option value="whereNull">whereNull</option><option value="whereNotNull">whereNotNull</option><option value="whereBetween">whereBetween</option><option value="whereNotBetween">whereNotBetween</option><option value="groupBy">groupBy</option><option value="orderBy">orderBy</option></select> </div><div class="col-sm-9"><div class="row" id="filter-fields'+ (parseInt(num)+1) +'"></div></div></div><div class="clearfix"></div>');
        });

        $('#filter-save-and-next').click(function(){
            var i=0;
            $('#div-filter-body').find('.main-row').each(function(){
                var main_row = $(this);
                if($(this).find('.form-control').first().val() == "where" || $(this).find('.form-control').first().val() == "orWhere")
                {
                    $(main_row).find('.form-control').each(function(){
                        $(this).attr("name",$(this).attr("name") + i);
                    });
                }
                else if($(this).find('.form-control').first().val() == "whereColumn" )
                {
                    var flg = 0;
                    $(main_row).find('.form-control').each(function(){
                        if($(this).attr("name") == "filterselect")
                        {
                            if(flg == 0)
                            {
                                $(this).attr("name","1" + $(this).attr("name") + i);
                                flg = 1;
                            }
                            else
                            {
                                $(this).attr("name","2" + $(this).attr("name") + i);
                            }
                        }
                        else
                        {
                            $(this).attr("name",$(this).attr("name") + i);
                        }
                    });
                }
                else if($(this).find('.form-control').first().val() == "whereIn" || $(this).find('.form-control').first().val() == "whereNotIn" )
                {
                    $(main_row).find('.form-control').each(function(){
                        $(this).attr("name",$(this).attr("name") + i);
                    });
                }
                else if($(this).find('.form-control').first().val() == "whereBetween" || $(this).find('.form-control').first().val() == "whereNotBetween")
                {
                    $(main_row).find('.form-control').each(function(){
                        $(this).attr("name",$(this).attr("name") + i);
                    });
                }
                else if($(this).find('.form-control').first().val() == "" )
                {
                    $(main_row).find('.form-control').each(function(){
                        $(this).attr("name",$(this).attr("name") + i);
                    });
                }
                else
                {
                    $(main_row).find('.form-control').each(function(){
                        $(this).attr("name",$(this).attr("name") + i);
                    });
                }
                i++;
            });
            $('#no_of_filter').val(i);
            $('.close').trigger('click');
            $('#fadeup-modal').trigger('click');
        });

        $('#fadeup-modal').click(function(){
            var ntblname = "";
            var ntbls = [];
            $('.tblname').each(function(){
                if($(this).text() != ntblname)
                {
                    ntbls.push($(this).text());
                    ntblname = $(this).text();
                }
            });
            var tbls = localStorage.getItem('tbls');
            var tbls = tbls.split(',');
            var diff = $(tbls).not(ntbls).get();

            if(parseInt(diff.length) > 0)
            {
                $('.msg').text('you deleted all the fields of Table : '+ diff );
                $('.error-msg').removeClass('hidden');
                return false;
            }
        });
    </script>
@endsection