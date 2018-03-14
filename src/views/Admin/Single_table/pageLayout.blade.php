@extends('autocrud::layouts.admin')

@section('content')
    <div class="container-fluid">
    <div class="row">
        <div class="clearfix"></div>
        <div class="col-md-12">
            <form action="{{url('createLayout')}}" method="get" role="form">

                <div class="alert alert-dismissible alert-warning m-top col-sm-offset-2 col-sm-8">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <strong>PELASE NOTE! </strong> If you already create view for table <strong>{{ $table }}</strong> then it will overwrite layout.
                </div>
                <center><h3 class="b-bottom heading">SELECT FORM LAYOUT</h3></center>
                <div class="col-sm-2 col-sm-offset-2">
                    <div class="panel panel-default panel-hover">
                        <div class="panel-heading">Left Align</div>
                        <div class="panel-body">
                            <label>
                                <img src="{{ asset('autocrud/img/left.png') }}" alt="left" class="img-thumbnail img-responsive img-check">
                                <input type="radio" name="layout" id="left" value="left" class="hidden" autocomplete="off" required>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="col-sm-2 ">
                    <div class="panel panel-default panel-hover">
                        <div class="panel-heading right-text">Right Align</div>
                        <div class="panel-body">
                            <label>
                                <img src="{{ asset('autocrud/img/right.png') }}" alt="right" class="img-thumbnail img-responsive img-check">
                                <input type="radio" name="layout" id="right" value="right" class="hidden" autocomplete="off">
                            </label>
                        </div>
                    </div>
                </div>
                <div class="col-sm-2 ">
                    <div class="panel panel-default panel-hover">
                        <div class="panel-heading">Center</div>
                        <div class="panel-body">
                            <label>
                                <img src="{{ asset('autocrud/img/center.png') }}" alt="center" class="img-thumbnail img-responsive img-check">
                                <input type="radio" name="layout" id="center" value="center" class="hidden" autocomplete="off">
                            </label>
                        </div>
                    </div>
                </div>
                <div class="col-sm-2 ">
                    <div class="panel panel-default panel-hover">
                        <div class="panel-heading">Devide in Column</div>
                        <div class="panel-body">
                            <label>
                                <img src="{{ asset('autocrud/img/cols.png') }}" alt="cols" class="img-thumbnail img-responsive img-check">
                                <input type="radio" name="layout" id="cols" value="cols" class="hidden" autocomplete="off">
                            </label>
                        </div>
                    </div>
                </div>
                <div class="clearfix"></div>
               <center><button type="submit" class="btn btn-success btn-lg">Next</button></center>
                <input type="hidden" name="table" value="{{ $table }}">
            </form>
        </div>
    </div>
@endsection

@section('custom-js')
    <script type="text/javascript">
        $(document).ready(function() {
            $(".js-example-basic-single").select2({
                placeholder: "Select a Table"
            });

            $('#table').val('{{ empty($table)?"":$table }}');
        });
    </script>
@endsection