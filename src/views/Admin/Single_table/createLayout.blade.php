@extends('autocrud::layouts.admin')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <center><h3 class="b-bottom heading">DESIGN FORM LAYOUT({{ $layout  }})</h3></center>
                <form action="{{ url('generateView') }}" method="post" role="form" class="m-top">
                    <div class="form-group col-sm-2 col-sm-offset-2">
                        <label for="select" class="control-label">Form Type</label>
                        <select class="form-control" id="form_type" name="form_type">
                            <option value="horizontal">Horizontal</option>
                            <option value="vertical">Vertical</option>
                        </select>
                    </div>
                    <div class="form-group col-sm-2">
                        <label for="select" class="control-label">Offset</label>
                        <select class="form-control" id="offset" name="offset">
                            <option value="1">1</option>
                            <option value="2">2</option>
                            <option value="3">3</option>
                        </select>
                    </div>
                    <div class="form-group col-sm-2">
                        <label for="select" class="control-label">Form Column Width</label>
                        <select class="form-control" id="form_cols" name="form_cols">
                            <option value="3">3</option>
                            <option value="4">4</option>
                            <option value="5">5</option>
                            <option value="6">6</option>
                            <option value="7">7</option>
                            <option value="8">8</option>
                            <option value="9">9</option>
                            <option value="10">10</option>
                        </select>
                    </div>
                    <div class="form-group col-sm-2 hidden">
                        <label for="select" class="control-label">No of colums</label>
                        <select class="form-control" id="no_of_cols" name="no_of_cols">
                            <option value="2">2</option>
                            <option value="3">3</option>
                            <option value="4">4</option>
                        </select>
                    </div>
                    <div class="form-group col-sm-2">
                        <label for="select" class="control-label">Side Div Width</label>
                        <select class="form-control" id="extra_cols" name="extra_cols">
                            <option value="1">1</option>
                            <option value="2">2</option>
                            <option value="3">3</option>
                            <option value="4">4</option>
                        </select>
                    </div>
                    <div class="clearfix"></div>
                    <center>
                        <input type="button" value="reset" id="reset" class="btn btn-primary" >
                        <a class="btn btn-success" data-toggle="modal" href="#get_view_name" id="add_view_data">next</a>
                    </center>
                    {{--modal start--}}
                    <div class="modal fade" id="get_view_name">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <button type="button" class="close" data-dismiss="modal"
                                            aria-hidden="true">&times;</button>
                                    <h4 class="modal-title">Add name of view</h4>
                                </div>
                                <div class="modal-body">
                                    <div class="form-group">
                                        <label for="">View Name</label>
                                        <input type="text" class="form-control" name="view_name" id="">
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-default" data-dismiss="modal">Close
                                    </button>
                                    <button type="submit" class="btn btn-primary">Save changes</button>
                                </div>
                            </div><!-- /.modal-content -->
                        </div><!-- /.modal-dialog -->
                    </div><!-- /.modal -->
                    <input type="hidden" name="form_layout" id="form_layout" value="{{ $layout }}">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <input type="hidden" name="array_name" id="array_name" value="{{ $table }}">
                    <input type="hidden" name="view_data" value="" id="view_data">
                    <input type="hidden" name="edit_view_data" id="view_edit_data">
                </form>
            </div>
        </div>
    </div>
    <div class="clearfix"></div>
    <div class="container-fluid preview m-top-title">

    </div>
@endsection

@section('custom-js')
    <script type="text/javascript">
        $(document).ready(function(){
            let new_name = $('#array_name').val();
            var type = $('#form_type').val() + "_" + $('#form_layout').val();
            $('.preview').html(window[new_name][type]);

            $('#add_view_data').click(function(){
                localStorage.setItem('refreshStatus','0');
            });
            if(localStorage.getItem('refreshStatus') == null)
            {
                localStorage.setItem('refreshStatus','1');
                location.reload();
            }
        });
    </script>
@endsection