@extends('autocrud::layouts.admin')

@section('content')
    <div class="container-fluid">
        <div class="col-md-12">
            <legend>select reference id between all the tables</legend>
            <form action="{{url('mStoreCommon')}}" method="get" role="form">
                @php
                    $n = sizeof($data);
                @endphp
                @for($i=1; $i<$n; $i++)
                    <div class="row m-top">
                        @foreach($data as $item)
                            <div class="form-group col-sm-{{ $n<5?$n<3?3:12/$n:2 }}">
                                <label class="control-label">Common Field from {{ $item[0]->table_name }}</label>
                                <select name="cmn[]" class="form-control form-control-rq">
                                    <option value=""> -- Select One --</option>
                                    @foreach($item as $subitem)
                                        <option value="{{ $subitem->table_name . "~" .$subitem->field_name }}"> {{ $subitem->field_name }} </option>
                                    @endforeach
                                </select>
                            </div>
                        @endforeach
                        <div class="clearfix"></div>
                        <div class="error-div text-danger"></div>
                    </div>
                    <div class="clearfix"></div>
                @endfor
                <input type="hidden" name="no_of_table" value="{{ $n }}">
                <button type="button" class="btn btn-primary pull-right btn-lg" id="validate-common">next</button>
            </form>
        </div>
    </div>
@endsection

@section('custom-js')
    <script type="text/javascript">
        $('#validate-common').click(function(e){
            var set = $('.row');
            var length = set.length;
            var err = 0;
            $('.row').each(function(index){
                var row = $(this);
                var i = 0;
                row.find('.form-control-rq').each(function(){
                    if($(this).val() != "")
                        i++;
                });
                if(i < 2)
                {
                    row.find('.error-div').text("Select two fields");
                    row.find('.form-control-rq').each(function(){
                        if($(this).val() == "")
                        {
                            $(this).parents('.form-group').addClass('has-error');
                            err = 1;
                        }
                    });
                    $('#validate-common').attr('type','button');
                }
                else if(i > 2)
                {
                    row.find('.error-div').text('You can select two fields only');
                    row.find('.form-control-rq').each(function(){
                        if($(this).val() != "")
                        {
                            err = 1;
                            $(this).parents('.form-group').addClass('has-error');
                        }
                    });
                    $('#validate-common').attr('type','button');
                }
                if(length == index + 1)
                {
                    if(err == 1)
                        return false;
                    else
                        $('form').submit();
                }
            });
        });
    </script>
@endsection