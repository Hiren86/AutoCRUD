@extends('autocrud::layouts.admin')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <form action="{{url('createFormField')}}" method="get" role="form">
                    <div class="col-md-5 col-sm-offset-4">
                        <center>
                            <h3 class="b-bottom heading">CREATE VIEW FOR TABLE</h3>
                            <div class="col-sm-10 col-sm-offset-1">
                                <select class="form-control js-example-basic-single" id="table" required name="table">
                                    <option value=""></option>
                                    @foreach($table_list as $item)
                                        <option value="{{$item}}">{{$item}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="clearfix"></div>
                            <button type="submit" class="m-top btn btn-success">NEXT</button>
                        </center>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('custom-js')
    <script type="text/javascript">
        $(document).ready(function() {
            $(".js-example-basic-single").select2({
                placeholder: "Select a Table",
                allowClear: true
            });

            $('#table').val('{{ empty($table)?"":$table }}');
        });
    </script>
@endsection