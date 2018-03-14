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
                    <form action="{{ url('mDesignForm') }}" method="post" role="form">
                        <fieldset>
                            <div class="clearfix"></div>
                            <?php $i=0; ?>
                            @foreach($columns as $item)
                                <h5 class="text-success"><input type="text" name="field-lbl{{$i}}" id="" value="{{ str_replace('_', " ",substr($item, strpos($item, "~") + 1)) }}" class="custom-lbl"></h5>
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
                                    <input type="hidden" class="field_name" name="field{{$i++}}" value="{{$item}}">
                                </div>
                                <div class="clearfix"></div>
                            @endforeach
                            <div class="col-sm-11">
                                <input type="button" value="submit" class="btn btn-success btn-lg pull-right" id="btnFormField">
                            </div>
                        </fieldset>
                        <input type="hidden" name="no_of_fields" value="{{ sizeof($columns) }}">
                        <input type="hidden" name="table" value="{{ $table }}">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    </form>
                </div>
                <div class="clearfix m-top"></div>
            </div>
        </div>
    </div>
@endsection


@section('custom-js')
@endsection