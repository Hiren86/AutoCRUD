@extends('autocrud::layouts.admin')

@section('content')
    <div class="container-fluid">
        <div class="col-md-12">
            <legend>select reference id between all the tables</legend>
            <form action="{{url('mformFields')}}" method="get" role="form">
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
                                            <input type="hidden" name="order[]" value="{{ $field->field_name ."~".$field->table_name }}">
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                                <div class="row p-bottom">
                                    <div class="col-md-4">
                                        <button type="submit" class="btn btn-primary btn-lg" id="submit-simple">Next</button>
                                    </div>
                                </div>

                                <input type="submit" class="hidden" id="generateOrderSubmit">
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <input type="hidden" name="refno" value="{{$refno}}">
                                <input type="hidden" name="no_of_filter" id="no_of_filter" value="">
                                <input type="hidden" name="name_of_view" id="name_of_view">

                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('custom-js')
    <script src="{{ asset('autocrud/js/jquery-ui.js') }}"></script>
    <script type="text/javascript">
        $(document).ready(function(){
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

            $('#name_of_view').val(tempName());

        });

        $('.remove').click(function(){
            $(this).closest('tr').remove();
            $('.sequence').find('tr:last').remove();
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