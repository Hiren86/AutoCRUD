<?php

namespace Hiren\Autocrud\Controllers;

use App\PageLayout;
use App\FormDefination;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Mockery\CountValidator\Exception;
use Illuminate\Support\Facades\Schema;

class SingleTableController extends Controller
{
    
    //public function __construct()
    //{
       //$this->middleware('auth', ['except' => ['createView','selectTable','pageLayout','createLayout','createFormField','designForm','getFormFields','updateForm','generateView','generateController','getColsList','getJoinFields','sortTableFields']]);
    //}
    
    public function home()
    {
        return view('autocrud::Admin.Single_table.home');
    }

    public function createView(Request $request){
        try
        {
            if (DB::connection()->getDatabaseName()) {
                $dbname = "Tables_in_" . DB::connection()->getDatabaseName();
            }
            $tables = DB::select('SHOW TABLES');
            $table_list = array();

            foreach ($tables as $table)
                array_push($table_list, $table->$dbname);

            $table_list = array_diff($table_list, array('users', 'migrations', 'password_resets','form_definations','page_layouts','common_fields','join_tables'));
            if ($request->table) {
                $table = $request->table;
                $columns = Schema::getColumnListing($table);
                $columns = array_diff($columns, array('id', 'created_at', 'updated_at'));
                return view('autocrud::Admin.Single_table.pageLayout', compact('table_list', 'table', 'columns'));
            }
            return view('autocrud::Admin.Single_table.pageLayout', compact('table_list', 'columns'));
        }
        catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function selectTable(){
        try {
            if (DB::connection()->getDatabaseName()) {
                $dbname = "Tables_in_" . DB::connection()->getDatabaseName();
            }
            $tables = DB::select('SHOW TABLES');
            $table_list = array();
            foreach ($tables as $table) {
                array_push($table_list, $table->$dbname);
            }

            $table_list = array_diff($table_list, array('users', 'migrations', 'password_resets','form_definations','page_layouts','common_fields','join_tables'));
            return view('autocrud::Admin.Single_table.selectTable', compact('table_list'));
        } 
        catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function pageLayout(Request $request){
        try
        {
            $table = $request->table;
            $is_available = DB::table('page_layouts')
                ->where('table', $table)
                ->first();
            if (!$is_available) {
                $page_layouts = new PageLayout();
                $page_layouts->table = $table;
                $page_layouts->save();
                return view('autocrud::Admin.Single_table.pageLayout', compact('table'));
            }
            else
            {
                return view('autocrud::Admin.Single_table.pageLayout', compact('table','is_available'));
            }
        }
        catch (\Exception $e) {
            echo $e->getMessage();
        }
    }

    public function createLayout(Request $request){
        try
        {
            $table = $request->table;
            $layout = $request->layout;
            DB::table('page_layouts')
                ->where('table',$request->table)
                ->update(['layout'=>$layout]);

            return view('autocrud::Admin.Single_table.createLayout',compact('layout','table'));
        }
        catch(\Exception $e)
        {
            echo $e;
        }
    }

    public function createFormField(Request $request){
        try
        {
            if ($request->table) {
                $table = $request->table;
                $columns = Schema::getColumnListing($table);
                $columns = array_diff($columns, array('id', 'created_at', 'updated_at'));
                if(DB::table('form_definations')->where('table',$request->table)->exists())
                {
                    $lable_names = DB::table('form_definations')
                        ->where('table',$table)
                        ->get(['name']);
                    return view('autocrud::Admin.Single_table.updateFormField', compact('table', 'columns','lable_names'));
                }
                else
                {
                    return view('autocrud::Admin.Single_table.createFormField', compact('table', 'columns'));
                }
            }
        }
        catch(Exception $e)
        {
            echo $e;
        }
    }

    public function designForm(Request $request){

        $formsArray = 'var '. $request->table .' = {';
        $eformsArray = 'var edit_'. $request->table .' = {';

        $vertical_cols = '"vertical_cols" :' . "'";
        $evertical_cols = '"vertical_cols" :' . "'";
        $horizontal_left = '"horizontal_left" :' . "'";
        $ehorizontal_left = '"horizontal_left" :' . "'";
        $horizontal_right = '"horizontal_right" : ' . "'";
        $ehorizontal_right = '"horizontal_right" : ' . "'";
        $vertical_left = '"vertical_left" : ' . "'";
        $evertical_left = '"vertical_left" : ' . "'";
        $vertical_right = '"vertical_right" : ' . "'";
        $evertical_right = '"vertical_right" : ' . "'";
        $horizontal_center = '"horizontal_center" : ' . "'";
        $ehorizontal_center = '"horizontal_center" : ' . "'";
        $vertical_center = '"vertical_center" : ' . "'";
        $evertical_center = '"vertical_center" : ' . "'";
        $horizontal_cols = '"horizontal_cols" : ' . "'";
        $ehorizontal_cols = '"horizontal_cols" : ' . "'";

        $vertical_cols = $vertical_cols . '<div class=""><center><h3 class="b-bottom heading">Form Preview</h3></center></div><div class="row"><div class="col-sm-10 col-sm-offset-1 shadow" id="form-div-center" style="background-color: #fff;border-radius:5px; "><form class="cols-form"><legend>YOUR FORM TITLE</legend>';
        $evertical_cols = $evertical_cols . '<div class=""><center><h3 class="b-bottom heading">Form Preview</h3></center></div><div class="row"><div class="col-sm-10 col-sm-offset-1 shadow" id="form-div-center" style="background-color: #fff;border-radius:5px; "><form class="cols-form"><legend>YOUR FORM TITLE</legend>';
        $horizontal_left = $horizontal_left . '<div class=""><center><h3 class="b-bottom heading">Form Preview</h3></center></div><div class="row"><div class="col-md-6 col-sm-offset-1 shadow" id="form-div" style="background-color: #fff;border-radius:5px; "><form class="form-horizontal"><fieldset><legend>YOUR FORM TITLE</legend>';
        $ehorizontal_left = $ehorizontal_left . '<div class=""><center><h3 class="b-bottom heading">Form Preview</h3></center></div><div class="row"><div class="col-md-6 col-sm-offset-1 shadow" id="form-div" style="background-color: #fff;border-radius:5px; "><form class="form-horizontal"><fieldset><legend>YOUR FORM TITLE</legend>';
        $horizontal_right = $horizontal_right . '<div class=""><center><h3 class="b-bottom heading">Form Preview</h3></center></div><div class="row"><div class="col-sm-4 equal col-sm-offset-1" id="extra-div-right"><div class="row extra" style="height: 100%; width: 98%; margin-left: 1%;"></div></div><div class="col-md-6 shadow" id="form-div-right" style="background-color: #fff;border-radius:5px; "><form class="form-horizontal"><fieldset><legend>YOUR FORM TITLE</legend>';
        $ehorizontal_right = $ehorizontal_right . '<div class=""><center><h3 class="b-bottom heading">Form Preview</h3></center></div><div class="row"><div class="col-sm-4 equal col-sm-offset-1" id="extra-div-right"><div class="row extra" style="height: 100%; width: 98%; margin-left: 1%;"></div></div><div class="col-md-6 shadow" id="form-div-right" style="background-color: #fff;border-radius:5px; "><form class="form-horizontal"><fieldset><legend>YOUR FORM TITLE</legend>';
        $vertical_left = $vertical_left . '<div class=""><center><h3 class="b-bottom heading">Form Preview</h3></center></div><div class="row"><div class="col-md-6 col-sm-offset-1 shadow" id="form-div" style="background-color: #fff;border-radius:5px; "><form class="form"><fieldset><legend>YOUR FORM TITLE</legend>';
        $evertical_left = $evertical_left . '<div class=""><center><h3 class="b-bottom heading">Form Preview</h3></center></div><div class="row"><div class="col-md-6 col-sm-offset-1 shadow" id="form-div" style="background-color: #fff;border-radius:5px; "><form class="form"><fieldset><legend>YOUR FORM TITLE</legend>';
        $vertical_right = $vertical_right . '<div class=""><center><h3 class="b-bottom heading">Form Preview</h3></center></div><div class="row"><div class="col-sm-4 col-sm-offset-1 equal" id="extra-div-right"><div class="row extra" style="height: 100%; width: 98%; margin-left: 1%;"></div></div><div class="col-md-6 shadow" id="form-div-right" style="background-color: #fff;border-radius:5px; "><form class="form"><fieldset><legend>YOUR FORM TITLE</legend>';
        $evertical_right = $evertical_right . '<div class=""><center><h3 class="b-bottom heading">Form Preview</h3></center></div><div class="row"><div class="col-sm-4 col-sm-offset-1 equal" id="extra-div-right"><div class="row extra" style="height: 100%; width: 98%; margin-left: 1%;"></div></div><div class="col-md-6 shadow" id="form-div-right" style="background-color: #fff;border-radius:5px; "><form class="form"><fieldset><legend>YOUR FORM TITLE</legend>';
        $horizontal_center = $horizontal_center . '<div class=""><center><h3 class="b-bottom heading">Form Preview</h3></center></div><div class="row"><div class="col-sm-6 col-sm-offset-3 shadow" id="form-div-center" style="background-color: #fff;border-radius:5px; "><form class="form-horizontal"><fieldset><legend>YOUR FORM TITLE</legend>';
        $ehorizontal_center = $ehorizontal_center . '<div class=""><center><h3 class="b-bottom heading">Form Preview</h3></center></div><div class="row"><div class="col-sm-6 col-sm-offset-3 shadow" id="form-div-center" style="background-color: #fff;border-radius:5px; "><form class="form-horizontal"><fieldset><legend>YOUR FORM TITLE</legend>';
        $vertical_center = $vertical_center . '<div class=""><center><h3 class="b-bottom heading">Form Preview</h3></center></div><div class="row"><div class="col-sm-6 col-sm-offset-3 shadow" id="form-div-center" style="background-color: #fff;border-radius:5px; "><form class="form"><fieldset><legend>YOUR FORM TITLE</legend>';
        $evertical_center = $evertical_center . '<div class=""><center><h3 class="b-bottom heading">Form Preview</h3></center></div><div class="row"><div class="col-sm-6 col-sm-offset-3 shadow" id="form-div-center" style="background-color: #fff;border-radius:5px; "><form class="form"><fieldset><legend>YOUR FORM TITLE</legend>';
        $horizontal_cols = $horizontal_cols . '<div class=""><center><h3 class="b-bottom heading">Form Preview</h3></center></div><div class="row"><div class="col-sm-10 col-sm-offset-1 shadow" id="form-div-center" style="background-color: #fff;border-radius:5px; "><form class="form-horizontal cols-form"><fieldset><legend>YOUR FORM TITLE</legend>';
        $ehorizontal_cols = $ehorizontal_cols . '<div class=""><center><h3 class="b-bottom heading">Form Preview</h3></center></div><div class="row"><div class="col-sm-10 col-sm-offset-1 shadow" id="form-div-center" style="background-color: #fff;border-radius:5px; "><form class="form-horizontal cols-form"><fieldset><legend>YOUR FORM TITLE</legend>';

        $horizontal = "";
        $ehorizontal = "";
        $vertical = "";
        $evertical = "";

        //remove all entry from form_definations if requested table is exist.
        if (DB::table('form_definations')->where('table',$request->table)->exists())
        {
            DB::table('form_definations')
                ->where('table',$request->table)
                ->delete();
        }
        for($i=0; $i<$request->no_of_fields; $i++)
        {
            $field_name = $request->{'field'.$i};
            if($request->{'inputType'.$i} == 'text')
            {
                if(trim($request->{$field_name.'pattern'}," ") == "")
                {
                    $vertical_cols = $vertical_cols .'<div class="form-group col-sm-6"><label class="control-label capitalize capitalize">'.$request->{'field-lbl'.$i}.'</label><input type="text" class="form-control" name="'. $request->{'field'.$i} .'" id="" placeholder="'.$request->{$field_name.'placeholder'}.'" maxlength="'.$request->{$field_name.'maxlength'}.'" minlength="'.$request->{$field_name.'minlength'}.'" '.$request->{$field_name.'required'}.'></div>';
                    $evertical_cols = $evertical_cols .'<div class="form-group col-sm-6"><label class="control-label capitalize capitalize">'.$request->{'field-lbl'.$i}.'</label><input type="text" class="form-control" name="'. $request->{'field'.$i} .'" value = "{{ $data->'.$request->{'field'.$i}.' }}" id="" placeholder="'.$request->{$field_name.'placeholder'}.'" maxlength="'.$request->{$field_name.'maxlength'}.'" minlength="'.$request->{$field_name.'minlength'}.'" '.$request->{$field_name.'required'}.'></div>';
                    $horizontal_cols = $horizontal_cols . '<div class="form-group col-sm-6"> <label class="col-lg-2 control-label capitalize capitalize">'.$request->{'field-lbl'.$i}.'</label><div class="col-lg-10"> <input type="text" id="" name="'. $request->{'field'.$i} .'" class="form-control"  placeholder="'.$request->{$field_name.'placeholder'}.'" maxlength="'.$request->{$field_name.'maxlength'}.'" minlength="'.$request->{$field_name.'minlength'}.'" '.$request->{$field_name.'required'}.'></div></div>';
                    $ehorizontal_cols = $ehorizontal_cols . '<div class="form-group col-sm-6"> <label class="col-lg-2 control-label capitalize capitalize">'.$request->{'field-lbl'.$i}.'</label><div class="col-lg-10"> <input type="text" id="" name="'. $request->{'field'.$i} .'" class="form-control" value = "{{ $data->'.$request->{'field'.$i}.' }}" placeholder="'.$request->{$field_name.'placeholder'}.'" maxlength="'.$request->{$field_name.'maxlength'}.'" minlength="'.$request->{$field_name.'minlength'}.'" '.$request->{$field_name.'required'}.'></div></div>';
                    $horizontal = $horizontal . '<div class="form-group"> <label class="col-lg-2 control-label capitalize capitalize">'.$request->{'field-lbl'.$i}.'</label><div class="col-lg-10"> <input type="text" id="" class="form-control" name="'. $request->{'field'.$i} .'" placeholder="'.$request->{$field_name.'placeholder'}.'" maxlength="'.$request->{$field_name.'maxlength'}.'" minlength="'.$request->{$field_name.'minlength'}.'" '.$request->{$field_name.'required'}.'></div></div>';
                    $ehorizontal = $ehorizontal . '<div class="form-group"> <label class="col-lg-2 control-label capitalize capitalize">'.$request->{'field-lbl'.$i}.'</label><div class="col-lg-10"> <input type="text" id="" class="form-control" name="'. $request->{'field'.$i} .'" placeholder="'.$request->{$field_name.'placeholder'}.'" value = "{{ $data->'.$request->{'field'.$i}.' }}" maxlength="'.$request->{$field_name.'maxlength'}.'" minlength="'.$request->{$field_name.'minlength'}.'" '.$request->{$field_name.'required'}.'></div></div>';
                    $vertical = $vertical . '<div class="form-group"><label class="control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><input type="text" class="form-control" id="" name="'. $request->{'field'.$i} .'" placeholder="'.$request->{$field_name.'placeholder'}.'" maxlength="'.$request->{$field_name.'maxlength'}.'" minlength="'.$request->{$field_name.'minlength'}. '" '.$request->{$field_name.'required'}.'></div>';
                    $evertical = $evertical . '<div class="form-group"><label class="control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><input type="text" class="form-control" id="" value = "{{ $data->'.$request->{'field'.$i}.' }}" name="'. $request->{'field'.$i} .'" placeholder="'.$request->{$field_name.'placeholder'}.'" maxlength="'.$request->{$field_name.'maxlength'}.'" minlength="'.$request->{$field_name.'minlength'}. '" '.$request->{$field_name.'required'}.'></div>';
                }
                else
                {
                    $vertical_cols = $vertical_cols .'<div class="form-group col-sm-6"><label class="control-label capitalize capitalize">'.$request->{'field-lbl'.$i}.'</label><input type="text" class="form-control" name="'. $request->{'field'.$i} .'" id="" placeholder="'.$request->{$field_name.'placeholder'}.'" maxlength="'.$request->{$field_name.'maxlength'}.'" minlength="'.$request->{$field_name.'minlength'}.'" pattern="'.$request->{$field_name.'pattern'}.'" '.$request->{$field_name.'required'}.'></div>';
                    $evertical_cols = $evertical_cols .'<div class="form-group col-sm-6"><label class="control-label capitalize capitalize">'.$request->{'field-lbl'.$i}.'</label><input type="text" value = "{{ $data->'.$request->{'field'.$i}.' }}" class="form-control" name="'. $request->{'field'.$i} .'" id="" placeholder="'.$request->{$field_name.'placeholder'}.'" maxlength="'.$request->{$field_name.'maxlength'}.'" minlength="'.$request->{$field_name.'minlength'}.'" pattern="'.$request->{$field_name.'pattern'}.'" '.$request->{$field_name.'required'}.'></div>';
                    $horizontal_cols = $horizontal_cols . '<div class="form-group col-sm-6"> <label class="col-lg-2 control-label capitalize capitalize">'.$request->{'field-lbl'.$i}.'</label><div class="col-lg-10"> <input type="text" id="" name="'. $request->{'field'.$i} .'" class="form-control"  placeholder="'.$request->{$field_name.'placeholder'}.'" maxlength="'.$request->{$field_name.'maxlength'}.'" minlength="'.$request->{$field_name.'minlength'}.'" pattern="'.$request->{$field_name.'pattern'}.'" '.$request->{$field_name.'required'}.'></div></div>';
                    $ehorizontal_cols = $ehorizontal_cols . '<div class="form-group col-sm-6"> <label class="col-lg-2 control-label capitalize capitalize">'.$request->{'field-lbl'.$i}.'</label><div class="col-lg-10"> <input type="text" id="" name="'. $request->{'field'.$i} .'" value = "{{ $data->'.$request->{'field'.$i}.' }}" class="form-control"  placeholder="'.$request->{$field_name.'placeholder'}.'" maxlength="'.$request->{$field_name.'maxlength'}.'" minlength="'.$request->{$field_name.'minlength'}.'" pattern="'.$request->{$field_name.'pattern'}.'" '.$request->{$field_name.'required'}.'></div></div>';
                    $horizontal = $horizontal . '<div class="form-group"> <label class="col-lg-2 control-label capitalize capitalize">'.$request->{'field-lbl'.$i}.'</label><div class="col-lg-10"> <input type="text" id="" class="form-control" name="'. $request->{'field'.$i} .'" placeholder="'.$request->{$field_name.'placeholder'}.'" maxlength="'.$request->{$field_name.'maxlength'}.'" minlength="'.$request->{$field_name.'minlength'}.'" pattern="'.$request->{$field_name.'pattern'}.'" '.$request->{$field_name.'required'}.'></div></div>';
                    $ehorizontal = $ehorizontal . '<div class="form-group"> <label class="col-lg-2 control-label capitalize capitalize">'.$request->{'field-lbl'.$i}.'</label><div class="col-lg-10"> <input type="text" id="" class="form-control" name="'. $request->{'field'.$i} .'" value = "{{ $data->'.$request->{'field'.$i}.' }}" placeholder="'.$request->{$field_name.'placeholder'}.'" maxlength="'.$request->{$field_name.'maxlength'}.'" minlength="'.$request->{$field_name.'minlength'}.'" pattern="'.$request->{$field_name.'pattern'}.'" '.$request->{$field_name.'required'}.'></div></div>';
                    $vertical = $vertical . '<div class="form-group"><label class="control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><input type="text" class="form-control" id="" name="'. $request->{'field'.$i} .'" placeholder="'.$request->{$field_name.'placeholder'}.'" maxlength="'.$request->{$field_name.'maxlength'}.'" minlength="'.$request->{$field_name.'minlength'}.'" pattern="'.$request->{$field_name.'pattern'}.'" '.$request->{$field_name.'required'}.'></div>';
                    $evertical = $evertical . '<div class="form-group"><label class="control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><input type="text" value = "{{ $data->'.$request->{'field'.$i}.' }}" class="form-control" id="" name="'. $request->{'field'.$i} .'" placeholder="'.$request->{$field_name.'placeholder'}.'" maxlength="'.$request->{$field_name.'maxlength'}.'" minlength="'.$request->{$field_name.'minlength'}.'" pattern="'.$request->{$field_name.'pattern'}.'" '.$request->{$field_name.'required'}.'></div>';
                }

                if($i>0 && $i%2==1)
                {
                    $vertical_cols = $vertical_cols . '<div class="clearfix"></div>';
                    $evertical_cols = $evertical_cols . '<div class="clearfix"></div>';
                    $horizontal_cols = $horizontal_cols . '<div class="clearfix"></div>';
                    $ehorizontal_cols = $ehorizontal_cols . '<div class="clearfix"></div>';
                }

                try {
                    $formDefination = new FormDefination();
                    $formDefination->table = $request->table;
                    $formDefination->type = $request->{'inputType' . $i};
                    $formDefination->name = str_replace(" ","_",$request->{'field-lbl' . $i});
                    $formDefination->placeholder = $request->{$field_name . 'placeholder'};
                    $formDefination->max = $request->{$field_name . 'maxlength'};
                    $formDefination->min = $request->{$field_name . 'minlength'};
                    $formDefination->pattern = $request->{$field_name . 'pattern'};
                    $required = $request->{$field_name . 'required'} == "" ? " " : 'required';
                    $formDefination->required = $required;
                    $formDefination->save();
                }
                catch(\Exception $e)
                {
                    echo $e;
                }
            }
            elseif($request->{'inputType'.$i} == 'number')
            {
                $vertical_cols = $vertical_cols .'<div class="form-group col-sm-6"><label class="control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><input type="number" class="form-control" id="" name="'. $request->{'field'.$i} .'" placeholder="'.$request->{$field_name.'placeholder'}.'" max="'.$request->{$field_name.'maxlength'}.'" min="'.$request->{$field_name.'minlength'}.'" step="'.$request->{$field_name.'step'}.'" '.$request->{$field_name.'required'}.'></div>';
                $evertical_cols = $evertical_cols .'<div class="form-group col-sm-6"><label class="control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><input type="number" class="form-control" id="" name="'. $request->{'field'.$i} .'" value = "{{ $data->'.$request->{'field'.$i}.' }}" placeholder="'.$request->{$field_name.'placeholder'}.'" max="'.$request->{$field_name.'maxlength'}.'" min="'.$request->{$field_name.'minlength'}.'" step="'.$request->{$field_name.'step'}.'" '.$request->{$field_name.'required'}.'></div>';
                $horizontal_cols = $horizontal_cols.'<div class="form-group col-sm-6"> <label class="col-lg-2 control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><div class="col-lg-10"> <input type="number" name="'. $request->{'field'.$i} .'" id="" class="form-control"  placeholder="'.$request->{$field_name.'placeholder'}.'" max="'.$request->{$field_name.'maxlength'}.'" min="'.$request->{$field_name.'minlength'}.'" step="'.$request->{$field_name.'step'}.'" '.$request->{$field_name.'required'}.'></div></div>';
                $ehorizontal_cols = $ehorizontal_cols.'<div class="form-group col-sm-6"> <label class="col-lg-2 control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><div class="col-lg-10"> <input type="number" name="'. $request->{'field'.$i} .'" value = "{{ $data->'.$request->{'field'.$i}.' }}" id="" class="form-control"  placeholder="'.$request->{$field_name.'placeholder'}.'" max="'.$request->{$field_name.'maxlength'}.'" min="'.$request->{$field_name.'minlength'}.'" step="'.$request->{$field_name.'step'}.'" '.$request->{$field_name.'required'}.'></div></div>';
                $horizontal = $horizontal.'<div class="form-group"> <label class="col-lg-2 control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><div class="col-lg-10"> <input type="number" id="" class="form-control" name="'. $request->{'field'.$i} .'" placeholder="'.$request->{$field_name.'placeholder'}.'" max="'.$request->{$field_name.'maxlength'}.'" min="'.$request->{$field_name.'minlength'}.'" step="'.$request->{$field_name.'step'}.'" '.$request->{$field_name.'required'}.'></div></div>';
                $ehorizontal = $ehorizontal.'<div class="form-group"> <label class="col-lg-2 control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><div class="col-lg-10"> <input type="number" id="" class="form-control" value = "{{ $data->'.$request->{'field'.$i}.' }}" name="'. $request->{'field'.$i} .'" placeholder="'.$request->{$field_name.'placeholder'}.'" max="'.$request->{$field_name.'maxlength'}.'" min="'.$request->{$field_name.'minlength'}.'" step="'.$request->{$field_name.'step'}.'" '.$request->{$field_name.'required'}.'></div></div>';
                $vertical = $vertical .'<div class="form-group"><label class="control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><input type="number" class="form-control" id="" name="'. $request->{'field'.$i} .'" placeholder="'.$request->{$field_name.'placeholder'}.'" max="'.$request->{$field_name.'maxlength'}.'" min="'.$request->{$field_name.'minlength'}.'" step="'.$request->{$field_name.'step'}.'" '.$request->{$field_name.'required'}.'></div>';
                $evertical = $evertical .'<div class="form-group"><label class="control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><input type="number" class="form-control" id="" name="'. $request->{'field'.$i} .'" value = "{{ $data->'.$request->{'field'.$i}.' }}" placeholder="'.$request->{$field_name.'placeholder'}.'" max="'.$request->{$field_name.'maxlength'}.'" min="'.$request->{$field_name.'minlength'}.'" step="'.$request->{$field_name.'step'}.'" '.$request->{$field_name.'required'}.'></div>';

                if($i>0 && $i%2==1)
                {
                    $vertical_cols = $vertical_cols . '<div class="clearfix"></div>';
                    $evertical_cols = $evertical_cols . '<div class="clearfix"></div>';
                    $horizontal_cols = $horizontal_cols.'<div class="clearfix"></div>';
                    $ehorizontal_cols = $ehorizontal_cols.'<div class="clearfix"></div>';
                }

                try
                {
                    $formDefination = new FormDefination();
                    $formDefination->table = $request->table;
                    $formDefination->type = $request->{'inputType'.$i};
                    $formDefination->name = str_replace(" ","_",$request->{'field-lbl' . $i});
                    $formDefination->placeholder = $request->{$field_name.'placeholder'};
                    $formDefination->max = $request->{$field_name.'maxlength'};
                    $formDefination->min = $request->{$field_name.'minlength'};
                    $formDefination->step =$request->{$field_name.'step'};
                    $required = $request->{$field_name.'required'} == ""? " " : 'required' ;
                    $formDefination->required = $required;
                    $formDefination->save();
                }
                catch(\Exception $e)
                {
                    echo $e;
                }
            }
            elseif($request->{'inputType'.$i} == 'radio')
            {
                $arr = explode(",",$request->{$field_name.'radio'});
                $vertical_cols_options = "";
                $evertical_cols_options = "";
                $horizontal_options = "";
                $ehorizontal_options = "";
                $vertical_options = "";
                $evertical_options = "";
                foreach($arr as $item)
                {
                    $vertical_cols_options = $vertical_cols_options . '<div class="radio"><label><input type="radio" name="'. $request->{"field".$i} .'"  value="'.$item.'">'.$item.'</label></div>';
                    $evertical_cols_options = $evertical_cols_options . '<div class="radio"><label><input type="radio" name="'.$request->{"field".$i} .'"  value="'.$item.'" {{ $data->'.$request->{'field'.$i}.' == "'.$item.'" ? "checked" : "" }}>'.$item.'</label></div>';
                    $horizontal_options = $horizontal_options . '<div class="radio"> <label> <input type="radio" name="'. $request->{"field".$i}.'" id="" value="'.$item.'">'.$item.'</label></div>';
                    $ehorizontal_options = $ehorizontal_options . '<div class="radio"> <label> <input type="radio" name="'. $request->{"field".$i} .'" id="" value="'.$item.'" {{ $data->'.$request->{'field'.$i}.' == "'.$item.'" ? "checked" : "" }}>'.$item.'</label></div>';
                    $vertical_options = $vertical_options . '<div class="radio"><label><input type="radio" name="'. $request->{"field".$i} .'" value="'.$item.'">'.$item.'</label></div>';
                    $evertical_options = $evertical_options . '<div class="radio"><label><input type="radio" name="'. $request->{"field".$i} .'" value="'.$item.'"  {{ $data->'.$request->{'field'.$i}.' == "'.$item.'" ? "checked" : "" }}>'.$item.'</label></div>';
                }
                $vertical_cols = $vertical_cols . '<div class="form-group col-sm-6"><label class="control-label capitalize">'.$request->{'field-lbl'.$i}.'</label>'.$vertical_cols_options.'</div>';
                $evertical_cols = $evertical_cols . '<div class="form-group col-sm-6"><label class="control-label capitalize">'.$request->{'field-lbl'.$i}.'</label>'.$evertical_cols_options.'</div>';
                $horizontal_cols = $horizontal_cols .'<div class="form-group col-sm-6"><label class="col-lg-2 control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><div class="col-lg-10">'.$horizontal_options.'</div></div>';
                $ehorizontal_cols = $ehorizontal_cols .'<div class="form-group col-sm-6"><label class="col-lg-2 control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><div class="col-lg-10">'.$ehorizontal_options.'</div></div>';
                $horizontal = $horizontal .'<div class="form-group"><label class="col-lg-2 control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><div class="col-lg-10">'.$horizontal_options.'</div></div>';
                $ehorizontal = $ehorizontal .'<div class="form-group"><label class="col-lg-2 control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><div class="col-lg-10">'.$ehorizontal_options.'</div></div>';
                $vertical = $vertical . '<div class="form-group"><label class="control-label capitalize">'.$request->{'field-lbl'.$i}.'</label>'.$vertical_options.'</div>';
                $evertical = $evertical . '<div class="form-group"><label class="control-label capitalize">'.$request->{'field-lbl'.$i}.'</label>'.$evertical_options.'</div>';
                if($i>0 && $i%2==1)
                {
                    $vertical_cols = $vertical_cols . '<div class="clearfix"></div>';
                    $evertical_cols = $evertical_cols . '<div class="clearfix"></div>';
                    $horizontal_cols = $horizontal_cols.'<div class="clearfix"></div>';
                    $ehorizontal_cols = $ehorizontal_cols.'<div class="clearfix"></div>';
                }

                try
                {
                    $formDefination = new FormDefination();
                    $formDefination->table = $request->table;
                    $formDefination->type = $request->{'inputType'.$i};
                    $formDefination->name = str_replace(" ","_",$request->{'field-lbl' . $i});
                    $formDefination->value = $request->{$field_name.'radio'};
                    $required = $request->{$field_name.'required'} == ""? " " : 'required' ;
                    $formDefination->required = $required;
                    $formDefination->save();
                }
                catch(\Exception $e)
                {
                    echo $e;
                }
            }
            elseif($request->{'inputType'.$i} == 'range')
            {
                $vertical_cols = $vertical_cols .'<div class="form-group col-sm-6"><label class="control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><input type="range" class="form-control" id="" name="'. $request->{'field'.$i} .'" value="'.$request->{$field_name.'value'}.'" max="'.$request->{$field_name.'maxlength'}.'" min="'.$request->{$field_name.'minlength'}.'" step="'.$request->{$field_name.'step'}.'" '.$request->{$field_name.'required'}.'></div>';
                $evertical_cols = $evertical_cols .'<div class="form-group col-sm-6"><label class="control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><input type="range" class="form-control" id="" name="'. $request->{'field'.$i} .'" value="'.$request->{$field_name.'value'}.'" max="'.$request->{$field_name.'maxlength'}.'" min="'.$request->{$field_name.'minlength'}.'" step="'.$request->{$field_name.'step'}.'" '.$request->{$field_name.'required'}.'></div>';
                $horizontal_cols = $horizontal_cols.'<div class="form-group col-sm-6"> <label class="col-lg-2 control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><div class="col-lg-10"> <input type="range" name="'. $request->{'field'.$i} .'" id="" class="form-control"  value="'.$request->{$field_name.'value'}.'" max="'.$request->{$field_name.'maxlength'}.'" min="'.$request->{$field_name.'minlength'}.'" step="'.$request->{$field_name.'step'}.'" '.$request->{$field_name.'required'}.'></div></div>';
                $ehorizontal_cols = $ehorizontal_cols.'<div class="form-group col-sm-6"> <label class="col-lg-2 control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><div class="col-lg-10"> <input type="range" name="'. $request->{'field'.$i} .'" id="" class="form-control"  value="'.$request->{$field_name.'value'}.'" max="'.$request->{$field_name.'maxlength'}.'" min="'.$request->{$field_name.'minlength'}.'" step="'.$request->{$field_name.'step'}.'" '.$request->{$field_name.'required'}.'></div></div>';
                $horizontal = $horizontal.'<div class="form-group"> <label class="col-lg-2 control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><div class="col-lg-10"> <input type="range" id="" class="form-control" name="'. $request->{'field'.$i} .'" value="'.$request->{$field_name.'value'}.'" max="'.$request->{$field_name.'maxlength'}.'" min="'.$request->{$field_name.'minlength'}.'" step="'.$request->{$field_name.'step'}.'" '.$request->{$field_name.'required'}.'></div></div>';
                $ehorizontal = $ehorizontal.'<div class="form-group"> <label class="col-lg-2 control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><div class="col-lg-10"> <input type="range" id="" class="form-control" name="'. $request->{'field'.$i} .'" value="'.$request->{$field_name.'value'}.'" max="'.$request->{$field_name.'maxlength'}.'" min="'.$request->{$field_name.'minlength'}.'" step="'.$request->{$field_name.'step'}.'" '.$request->{$field_name.'required'}.'></div></div>';
                $vertical = $vertical .'<div class="form-group"><label class="control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><input type="range" class="form-control" id="" value="'.$request->{$field_name.'value'}.'" name="'. $request->{'field'.$i} .'" max="'.$request->{$field_name.'maxlength'}.'" min="'.$request->{$field_name.'minlength'}.'" step="'.$request->{$field_name.'step'}.'" '.$request->{$field_name.'required'}.'></div>';
                $evertical = $evertical .'<div class="form-group"><label class="control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><input type="range" class="form-control" id="" value="'.$request->{$field_name.'value'}.'" name="'. $request->{'field'.$i} .'" max="'.$request->{$field_name.'maxlength'}.'" min="'.$request->{$field_name.'minlength'}.'" step="'.$request->{$field_name.'step'}.'" '.$request->{$field_name.'required'}.'></div>';
                if($i>0 && $i%2==1)
                {
                    $vertical_cols = $vertical_cols . '<div class="clearfix"></div>';
                    $evertical_cols = $evertical_cols . '<div class="clearfix"></div>';
                    $horizontal_cols = $horizontal_cols.'<div class="clearfix"></div>';
                    $ehorizontal_cols = $ehorizontal_cols.'<div class="clearfix"></div>';
                }

                try
                {
                    $formDefination = new FormDefination();
                    $formDefination->table = $request->table;
                    $formDefination->type = $request->{'inputType'.$i};
                    $formDefination->name = str_replace(" ","_",$request->{'field-lbl' . $i});
                    $formDefination->placeholder = $request->{$field_name.'placeholder'};
                    $formDefination->max = $request->{$field_name.'maxlength'};
                    $formDefination->min = $request->{$field_name.'minlength'};
                    $formDefination->step =$request->{$field_name.'step'};
                    $required = $request->{$field_name.'required'} == ""? " " : 'required' ;
                    $formDefination->required = $required;
                    $formDefination->save();
                }
                catch(\Exception $e)
                {
                    echo $e;
                }
            }
            elseif($request->{'inputType'.$i} == 'textarea')
            {
                $vertical_cols = $vertical_cols .'<div class="form-group col-sm-6"><label class="control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><textarea name="'. $request->{'field'.$i} .'" class="form-control" id="" placeholder="'.$request->{$field_name.'placeholder'}.'" maxlength="'.$request->{$field_name.'maxlength'}.'" minlength="'.$request->{$field_name.'minlength'}.'" rows="'.$request->{$field_name.'rows'}.'" '.$request->{$field_name.'required'}.'></textarea></div>';
                $evertical_cols = $evertical_cols .'<div class="form-group col-sm-6"><label class="control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><textarea value = "{{ $data->'.$request->{'field'.$i}.' }}" name="'. $request->{'field'.$i} .'" class="form-control" id="" placeholder="'.$request->{$field_name.'placeholder'}.'" maxlength="'.$request->{$field_name.'maxlength'}.'" minlength="'.$request->{$field_name.'minlength'}.'" rows="'.$request->{$field_name.'rows'}.'" '.$request->{$field_name.'required'}.'></textarea></div>';
                $horizontal_cols = $horizontal_cols.'<div class="form-group col-sm-6"> <label class="col-lg-2 control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><div class="col-lg-10"> <textarea name="'. $request->{'field'.$i} .'" id="" class="form-control"  placeholder="'.$request->{$field_name.'placeholder'}.'" maxlength="'.$request->{$field_name.'maxlength'}.'" minlength="'.$request->{$field_name.'minlength'}.'" rows="'.$request->{$field_name.'rows'}.'" '.$request->{$field_name.'required'}.'></textarea></div></div>';
                $ehorizontal_cols = $ehorizontal_cols.'<div class="form-group col-sm-6"> <label class="col-lg-2 control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><div class="col-lg-10"> <textarea name="'. $request->{'field'.$i} .'" id="" class="form-control" value = "{{ $data->'.$request->{'field'.$i}.' }}"  placeholder="'.$request->{$field_name.'placeholder'}.'" maxlength="'.$request->{$field_name.'maxlength'}.'" minlength="'.$request->{$field_name.'minlength'}.'" rows="'.$request->{$field_name.'rows'}.'" '.$request->{$field_name.'required'}.'></textarea></div></div>';
                $horizontal = $horizontal.'<div class="form-group"> <label class="col-lg-2 control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><div class="col-lg-10"> <textarea id="" class="form-control" name="'. $request->{'field'.$i} .'" placeholder="'.$request->{$field_name.'placeholder'}.'" maxlength="'.$request->{$field_name.'maxlength'}.'" minlength="'.$request->{$field_name.'minlength'}.'" rows="'.$request->{$field_name.'rows'}.'" '.$request->{$field_name.'required'}.'></textarea></div></div>';
                $ehorizontal = $ehorizontal.'<div class="form-group"> <label class="col-lg-2 control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><div class="col-lg-10"> <textarea id="" class="form-control" name="'. $request->{'field'.$i} .'" value = "{{ $data->'.$request->{'field'.$i}.' }}" placeholder="'.$request->{$field_name.'placeholder'}.'" maxlength="'.$request->{$field_name.'maxlength'}.'" minlength="'.$request->{$field_name.'minlength'}.'" rows="'.$request->{$field_name.'rows'}.'" '.$request->{$field_name.'required'}.'></textarea></div></div>';
                $vertical = $vertical .'<div class="form-group"><label class="control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><textarea class="form-control" id="" placeholder="'.$request->{$field_name.'placeholder'}.'" name="'. $request->{'field'.$i} .'" maxlength="'.$request->{$field_name.'maxlength'}.'" minlength="'.$request->{$field_name.'minlength'}.'" rows="'.$request->{$field_name.'rows'}.'" '.$request->{$field_name.'required'}.'></textarea></div>';
                $evertical = $evertical .'<div class="form-group"><label class="control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><textarea class="form-control" id="" placeholder="'.$request->{$field_name.'placeholder'}.'" value = "{{ $data->'.$request->{'field'.$i}.' }}" name="'. $request->{'field'.$i} .'" maxlength="'.$request->{$field_name.'maxlength'}.'" minlength="'.$request->{$field_name.'minlength'}.'" rows="'.$request->{$field_name.'rows'}.'" '.$request->{$field_name.'required'}.'></textarea></div>';
                if($i>0 && $i%2==1)
                {
                    $vertical_cols = $vertical_cols . '<div class="clearfix"></div>';
                    $evertical_cols = $evertical_cols . '<div class="clearfix"></div>';
                    $horizontal_cols = $horizontal_cols.'<div class="clearfix"></div>';
                    $ehorizontal_cols = $ehorizontal_cols.'<div class="clearfix"></div>';
                }

                try
                {
                    $formDefination = new FormDefination();
                    $formDefination->table = $request->table;
                    $formDefination->type = $request->{'inputType'.$i};
                    $formDefination->name = str_replace(" ","_",$request->{'field-lbl' . $i});
                    $formDefination->placeholder = $request->{$field_name.'placeholder'};
                    $formDefination->max = $request->{$field_name.'maxlength'};
                    $formDefination->min = $request->{$field_name.'minlength'};
                    $formDefination->rows = $request->{$field_name.'rows'};
                    $required = $request->{$field_name.'required'} == ""? " " : 'required' ;
                    $formDefination->required = $required;
                    $formDefination->save();
                }
                catch(\Exception $e)
                {
                    echo $e;
                }
            }
            elseif($request->{'inputType'.$i} == 'checkbox')
            {
                $arr = explode(",",$request->{$field_name.'checkbox'});
                $vertical_cols_options = "";
                $evertical_cols_options = "";
                $horizontal_options = "";
                $ehorizontal_options = "";
                $vertical_options = "";
                $evertical_options = "";
                foreach($arr as $item)
                {
                    $vertical_cols_options = $vertical_cols_options . '<div class="checkbox"><label><input type="checkbox" name="'.$request->{'field'.$i}.'[]" id="" value="'.$item.'">'.$item.'</label></div>';
                    $evertical_cols_options = $evertical_cols_options . '<div class="checkbox"><label><input type="checkbox" name="'.$request->{'field'.$i}.'[]" id="" value="'.$item.'"{{ $data->'.$request->{'field'.$i}.' ? "checked" :"" }}">'.$item.'</label></div>';
                    $horizontal_options = $horizontal_options . '<div class="checkbox"> <label> <input type="checkbox" name="'.$request->{'field'.$i}.'[]" id="" value="'.$item.'">'.$item.'</label></div>';
                    $ehorizontal_options = $ehorizontal_options . '<div class="checkbox"> <label> <input type="checkbox" name="'.$request->{'field'.$i}.'[]" id="" value="'.$item.'"{{ $data->'.$request->{'field'.$i}.' ? "checked" :"" }}">'.$item.'</label></div>';
                    $vertical_options = $vertical_options . '<div class="checkbox"><label><input type="checkbox" name="'.$request->{'field'.$i}.'[]" id="" value="'.$item.'">'.$item.'</label></div>';
                    $evertical_options = $evertical_options . '<div class="checkbox"><label><input type="checkbox" name="'.$request->{'field'.$i}.'[]" id="" value="'.$item.'"{{ $data->'.$request->{'field'.$i}.' ? "checked" :"" }}>'.$item.'</label></div>';
                }
                $vertical_cols = $vertical_cols . '<div class="form-group col-sm-6"><label class="control-label capitalize">'.$request->{'field-lbl'.$i}.'</label>'.$vertical_cols_options.'</div>';
                $evertical_cols = $evertical_cols . '<div class="form-group col-sm-6"><label class="control-label capitalize">'.$request->{'field-lbl'.$i}.'</label>'.$evertical_cols_options.'</div>';
                $horizontal_cols = $horizontal_cols .'<div class="form-group col-sm-6"><label class="col-lg-2 control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><div class="col-lg-10">'.$horizontal_options.'</div></div>';
                $ehorizontal_cols = $ehorizontal_cols .'<div class="form-group col-sm-6"><label class="col-lg-2 control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><div class="col-lg-10">'.$ehorizontal_options.'</div></div>';
                $horizontal = $horizontal .'<div class="form-group"><label class="col-lg-2 control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><div class="col-lg-10">'.$horizontal_options.'</div></div>';
                $ehorizontal = $ehorizontal .'<div class="form-group"><label class="col-lg-2 control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><div class="col-lg-10">'.$ehorizontal_options.'</div></div>';
                $vertical = $vertical . '<div class="form-group"><label class="control-label capitalize">'.$request->{'field-lbl'.$i}.'</label>'.$vertical_options.'</div>';
                $evertical = $evertical . '<div class="form-group"><label class="control-label capitalize">'.$request->{'field-lbl'.$i}.'</label>'.$evertical_options.'</div>';
                if($i>0 && $i%2==1)
                {
                    $vertical_cols = $vertical_cols . '<div class="clearfix"></div>';
                    $evertical_cols = $evertical_cols . '<div class="clearfix"></div>';
                    $horizontal_cols = $horizontal_cols.'<div class="clearfix"></div>';
                    $ehorizontal_cols = $ehorizontal_cols.'<div class="clearfix"></div>';
                }
                try
                {
                    $formDefination = new FormDefination();
                    $formDefination->table = $request->table;
                    $formDefination->type = $request->{'inputType'.$i};
                    $formDefination->name = str_replace(" ","_",$request->{'field-lbl' . $i});
                    $formDefination->value = $request->{$field_name.'checkbox'};
                    $required = $request->{$field_name.'required'} == ""? " " : 'required' ;
                    $formDefination->required = $required;
                    $formDefination->save();
                }
                catch(\Exception $e)
                {
                    echo $e;
                }
            }
            elseif($request->{'inputType'.$i} == 'select')
            {
                $arr = explode(",",$request->{$field_name.'select'});
                $vertical_cols_options = "";
                $evertical_cols_options = "";
                $horizontal_options = "";
                $ehorizontal_options = "";
                $vertical_options = "";
                $evertical_options = "";
                foreach($arr as $item)
                {
                    $vertical_cols_options = $vertical_cols_options . '<option value="'.$item.'">'.$item.'</option>';
                    $evertical_cols_options = $vertical_cols_options . '<option value="'.$item.'" {{ $data->'.$request->{'field'.$i}.' == "'.$item.'" ? "selected" :"" }}>'.$item.'</option>';
                    $horizontal_options = $horizontal_options . '<option value="'.$item.'">'.$item.'</option>';
                    $ehorizontal_options = $horizontal_options . '<option value="'.$item.'" {{ $data->'.$request->{'field'.$i}.' == "'.$item.'" ? "selected" :"" }}>'.$item.'</option>';
                    $vertical_options = $vertical_options . '<option value="'.$item.'">'.$item.'</option>';
                    $evertical_options = $vertical_options . '<option value="'.$item.'" {{ $data->'.$request->{'field'.$i}.' == "'.$item.'" ? "selected" :"" }}>'.$item.'</option>';
                }
                $vertical_cols = $vertical_cols . '<div class="form-group col-sm-6"><label class="control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><select class="form-control"  name="'. $request->{'field'.$i} .'" >'.$vertical_cols_options.'</select></div>';
                $evertical_cols = $evertical_cols . '<div class="form-group col-sm-6"><label class="control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><select class="form-control"  name="'. $request->{'field'.$i} .'" >'.$evertical_cols_options.'</select></div>';
                $horizontal_cols = $horizontal_cols .'<div class="form-group col-sm-6"><label class="col-lg-2 control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><div class="col-lg-10"><select class="form-control"  name="'. $request->{'field'.$i} .'" >'.$horizontal_options.'</select></div></div>';
                $ehorizontal_cols = $ehorizontal_cols .'<div class="form-group col-sm-6"><label class="col-lg-2 control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><div class="col-lg-10"><select class="form-control"  name="'. $request->{'field'.$i} .'" >'.$ehorizontal_options.'</select></div></div>';
                $horizontal = $horizontal .'<div class="form-group"><label class="col-lg-2 control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><div class="col-lg-10"><select class="form-control" name="'. $request->{'field'.$i} .'" >'.$horizontal_options.'</select></div></div>';
                $ehorizontal = $ehorizontal .'<div class="form-group"><label class="col-lg-2 control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><div class="col-lg-10"><select class="form-control" name="'. $request->{'field'.$i} .'" >'.$ehorizontal_options.'</select></div></div>';
                $vertical = $vertical . '<div class="form-group"><label class="control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><select class="form-control" name="'. $request->{'field'.$i} .'" >'.$vertical_options.'</select></div>';
                $evertical = $evertical . '<div class="form-group"><label class="control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><select class="form-control" name="'. $request->{'field'.$i} .'" >'.$evertical_options.'</select></div>';
                if($i>0 && $i%2==1)
                {
                    $vertical_cols = $vertical_cols . '<div class="clearfix"></div>';
                    $evertical_cols = $evertical_cols . '<div class="clearfix"></div>';
                    $horizontal_cols = $horizontal_cols.'<div class="clearfix"></div>';
                    $ehorizontal_cols = $ehorizontal_cols.'<div class="clearfix"></div>';
                }
                try
                {
                    $formDefination = new FormDefination();
                    $formDefination->table = $request->table;
                    $formDefination->type = $request->{'inputType'.$i};
                    $formDefination->name = str_replace(" ","_",$request->{'field-lbl' . $i});
                    $formDefination->value = $request->{$field_name.'select'};
                    $required = $request->{$field_name.'required'} == ""? " " : 'required' ;
                    $formDefination->required = $required;
                    $formDefination->save();
                }
                catch(\Exception $e)
                {
                    echo $e;
                }
            }
            elseif($request->{'inputType'.$i} == 'multiselect')
            {
                $arr = explode(",",$request->{$field_name.'multiselect'});
                $vertical_cols_options = "";
                $evertical_cols_options = "";
                $horizontal_options = "";
                $ehorizontal_options = "";
                $vertical_options = "";
                $evertical_options = "";
                foreach($arr as $item)
                {
                    $vertical_cols_options = $vertical_cols_options .'<option value="'.$item.'">'.$item.'</option>';
                    $evertical_cols_options = $evertical_cols_options .'<option value="'.$item.'"  {{ in_array("'.$item.'", explode(",", $data->'.$request->{'field'.$i}.')) ? "selected" :"" }}> '.$item.' </option>';
                    $horizontal_options = $horizontal_options . '<option value="'.$item.'">'.$item.'</option>';
                    $ehorizontal_options = $ehorizontal_options . '<option value="'.$item.'"  {{ in_array("'.$item.'", explode(",", $data->'.$request->{'field'.$i}.')) ? "selected" :"" }}> '.$item.' </option>';
                    $vertical_options = $vertical_options .'<option value="'.$item.'">'.$item.'</option>';
                    $evertical_options = $evertical_options .'<option value="'.$item.'"  {{ in_array("'.$item.'", explode(",", $data->'.$request->{'field'.$i}.')) ? "selected" :"" }}> '.$item.' </option>';
                }
                $vertical_cols = $vertical_cols . '<div class="form-group col-sm-6"><label class="control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><select multiple="" class="form-control"  name="'. $request->{'field'.$i} .'[]" >'.$vertical_cols_options.'</select></div>';
                $evertical_cols = $evertical_cols . '<div class="form-group col-sm-6"><label class="control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><select multiple="" class="form-control"  name="'. $request->{'field'.$i} .'[]" >'.$evertical_cols_options.'</select></div>';
                $horizontal_cols = $horizontal_cols .'<div class="form-group col-sm-6"><label class="col-lg-2 control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><div class="col-lg-10"><select multiple="" class="form-control" name="'. $request->{'field'.$i} .'[]" >'.$horizontal_options.'</select></div></div>';
                $ehorizontal_cols = $ehorizontal_cols .'<div class="form-group col-sm-6"><label class="col-lg-2 control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><div class="col-lg-10"><select multiple="" class="form-control" name="'. $request->{'field'.$i} .'[]" >'.$ehorizontal_options.'</select></div></div>';
                $horizontal = $horizontal .'<div class="form-group"><label class="col-lg-2 control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><div class="col-lg-10"><select multiple="" class="form-control" name="'. $request->{'field'.$i} .'[]" >'.$horizontal_options.'</select></div></div>';
                $ehorizontal = $ehorizontal .'<div class="form-group"><label class="col-lg-2 control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><div class="col-lg-10"><select multiple="" class="form-control" name="'. $request->{'field'.$i} .'[]" >'.$ehorizontal_options.'</select></div></div>';
                $vertical = $vertical . '<div class="form-group"><label class="control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><select multiple="" class="form-control" name="'. $request->{'field'.$i} .'[]" >'.$vertical_options.'</select></div>';
                $evertical = $evertical . '<div class="form-group"><label class="control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><select multiple="" class="form-control" name="'. $request->{'field'.$i} .'[]" >'.$evertical_options.'</select></div>';
                if($i>0 && $i%2==1)
                {
                    $vertical_cols = $vertical_cols . '<div class="clearfix"></div>';
                    $evertical_cols = $evertical_cols . '<div class="clearfix"></div>';
                }
                try
                {
                    $formDefination = new FormDefination();
                    $formDefination->table = $request->table;
                    $formDefination->type = $request->{'inputType'.$i};
                    $formDefination->name = str_replace(" ","_",$request->{'field-lbl' . $i});
                    $formDefination->value = $request->{$field_name.'multiselect'};
                    $required = $request->{$field_name.'required'} == ""? " " : 'required' ;
                    $formDefination->required = $required;
                    $formDefination->save();
                }
                catch(\Exception $e)
                {
                    echo $e;
                }
            }
            elseif($request->{'inputType'.$i} == 'email' || $request->{'inputType'.$i} == 'password' || $request->{'inputType'.$i} == 'url')
            {
                $vertical_cols = $vertical_cols .'<div class="form-group col-sm-6"><label class="control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><input type="'. $request->{'inputType'.$i} .'" name="'. $request->{'field'.$i} .'" class="form-control" id="" placeholder="'.$request->{$field_name.'placeholder'}.'" '.$request->{$field_name.'required'}.'></div>';
                $evertical_cols = $evertical_cols .'<div class="form-group col-sm-6"><label class="control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><input type="'. $request->{'inputType'.$i} .'" name="'. $request->{'field'.$i} .'" value = "{{ $data->'.$request->{'field'.$i}.' }}" class="form-control" id="" placeholder="'.$request->{$field_name.'placeholder'}.'" '.$request->{$field_name.'required'}.'></div>';
                $horizontal_cols = $horizontal_cols .'<div class="form-group col-sm-6"> <label class="col-lg-2 control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><div class="col-lg-10"> <input type="'. $request->{'inputType'.$i} .'" name="'. $request->{'field'.$i} .'" id="" class="form-control"  placeholder="'.$request->{$field_name.'placeholder'}.'" '.$request->{$field_name.'required'}.'></div></div>';
                $ehorizontal_cols = $ehorizontal_cols .'<div class="form-group col-sm-6"> <label class="col-lg-2 control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><div class="col-lg-10"> <input type="'. $request->{'inputType'.$i} .'" name="'. $request->{'field'.$i} .'" value = "{{ $data->'.$request->{'field'.$i}.' }}" id="" class="form-control"  placeholder="'.$request->{$field_name.'placeholder'}.'" '.$request->{$field_name.'required'}.'></div></div>';
                $horizontal = $horizontal.'<div class="form-group"> <label class="col-lg-2 control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><div class="col-lg-10"> <input type="'. $request->{'inputType'.$i} .'" id="" class="form-control" name="'. $request->{'field'.$i} .'" placeholder="'.$request->{$field_name.'placeholder'}.'" '.$request->{$field_name.'required'}.'></div></div>';
                $ehorizontal = $ehorizontal.'<div class="form-group"> <label class="col-lg-2 control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><div class="col-lg-10"> <input type="'. $request->{'inputType'.$i} .'" id="" class="form-control" name="'. $request->{'field'.$i} .'" value = "{{ $data->'.$request->{'field'.$i}.' }}" placeholder="'.$request->{$field_name.'placeholder'}.'" '.$request->{$field_name.'required'}.'></div></div>';
                $vertical = $vertical .'<div class="form-group"><label class="control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><input type="'. $request->{'inputType'.$i} .'" class="form-control" id="" name="'. $request->{'field'.$i} .'" placeholder="'.$request->{$field_name.'placeholder'}.'" '.$request->{$field_name.'required'}.'></div>';
                $evertical = $evertical .'<div class="form-group"><label class="control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><input type="'. $request->{'inputType'.$i} .'" class="form-control" id="" name="'. $request->{'field'.$i} .'" value = "{{ $data->'.$request->{'field'.$i}.' }}" placeholder="'.$request->{$field_name.'placeholder'}.'" '.$request->{$field_name.'required'}.'></div>';
                if($i>0 && $i%2==1)
                {
                    $vertical_cols = $vertical_cols . '<div class="clearfix"></div>';
                    $evertical_cols = $evertical_cols . '<div class="clearfix"></div>';
                    $horizontal_cols = $horizontal_cols.'<div class="clearfix"></div>';
                    $ehorizontal_cols = $ehorizontal_cols.'<div class="clearfix"></div>';
                }
                try
                {
                    $formDefination = new FormDefination();
                    $formDefination->table = $request->table;
                    $formDefination->type = $request->{'inputType'.$i};
                    $formDefination->name = str_replace(" ","_",$request->{'field-lbl' . $i});
                    $required = $request->{$field_name.'required'} == ""? " " : 'required' ;
                    $formDefination->required = $required;
                    $formDefination->save();
                }
                catch(\Exception $e)
                {
                    echo $e;
                }
            }
            elseif($request->{'inputType'.$i} == 'none' ){
                try
                {
                    $formDefination = new FormDefination();
                    $formDefination->table = $request->table;
                    $formDefination->type = 'none';
                    $formDefination->name = str_replace(" ","_",$request->{'field-lbl' . $i});
                    $formDefination->save();
                }
                catch(\Exception $e)
                {
                    echo $e;
                }
            }
            else
            {
                $vertical_cols = $vertical_cols .'<div class="form-group col-sm-6"><label class="control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><input type="'. $request->{'inputType'.$i} .'" name="'. $request->{'field'.$i} .'" class="form-control" id="" '.$request->{$field_name.'required'}.'></div>';
                $evertical_cols = $evertical_cols .'<div class="form-group col-sm-6"><label class="control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><input type="'. $request->{'inputType'.$i} .'" name="'. $request->{'field'.$i} .'" value = "{{ $data->'.$request->{'field'.$i}.' }}" class="form-control" id="" '.$request->{$field_name.'required'}.'></div>';
                $horizontal_cols = $horizontal_cols.'<div class="col-sm-6 form-group"> <label class="col-lg-2 control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><div class="col-lg-10"> <input type="'. $request->{'inputType'.$i} .'" name="'. $request->{'field'.$i} .'" id="" class="form-control" '.$request->{$field_name.'required'}.'></div></div>';
                $ehorizontal_cols = $ehorizontal_cols.'<div class="col-sm-6 form-group"> <label class="col-lg-2 control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><div class="col-lg-10"> <input type="'. $request->{'inputType'.$i} .'" name="'. $request->{'field'.$i} .'" id="" value = "{{ $data->'.$request->{'field'.$i}.' }}" class="form-control" '.$request->{$field_name.'required'}.'></div></div>';
                $horizontal = $horizontal.'<div class="form-group"> <label class="col-lg-2 control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><div class="col-lg-10"> <input type="'. $request->{'inputType'.$i} .'" name="'. $request->{'field'.$i} .'" id="" class="form-control" '.$request->{$field_name.'required'}.'></div></div>';
                $ehorizontal = $ehorizontal.'<div class="form-group"> <label class="col-lg-2 control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><div class="col-lg-10"> <input type="'. $request->{'inputType'.$i} .'" name="'. $request->{'field'.$i} .'" id="" value = "{{ $data->'.$request->{'field'.$i}.' }}" class="form-control" '.$request->{$field_name.'required'}.'></div></div>';
                $vertical = $vertical .'<div class="form-group"><label class="control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><input type="'. $request->{'inputType'.$i} .'" class="form-control" id="" name="'. $request->{'field'.$i} .'" '.$request->{$field_name.'required'}.'></div>';
                $evertical = $evertical .'<div class="form-group"><label class="control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><input type="'. $request->{'inputType'.$i} .'" class="form-control" id="" value = "{{ $data->'.$request->{'field'.$i}.' }}" name="'. $request->{'field'.$i} .'" '.$request->{$field_name.'required'}.'></div>';
                if($i>0 && $i%2==1)
                {
                    $vertical_cols = $vertical_cols . '<div class="clearfix"></div>';
                    $evertical_cols = $evertical_cols . '<div class="clearfix"></div>';
                    $horizontal_cols = $horizontal_cols.'<div class="clearfix"></div>';
                    $ehorizontal_cols = $ehorizontal_cols.'<div class="clearfix"></div>';
                }
                try
                {
                    $formDefination = new FormDefination();
                    $formDefination->table = $request->table;
                    $formDefination->type = $request->{'inputType'.$i};
                    $formDefination->name = str_replace(" ","_",$request->{'field-lbl' . $i});
                    $required = $request->{$field_name.'required'} == ""? " " : 'required' ;
                    $formDefination->required = $required;
                    $formDefination->save();
                }
                catch(\Exception $e)
                {
                    echo $e;
                }
            }
        }

        $vertical_cols = $vertical_cols . '<div class="form-group col-sm-6"><div class="col-lg-12"> <input type="reset" value="reset" class="btn btn-default"> <input type="submit" value="submit" class="btn btn-primary"> </div></div><div class="clearfix"></div></form></div></div>';
        $evertical_cols = $evertical_cols . '<div class="form-group col-sm-6"><div class="col-lg-12"> <input type="reset" value="reset" class="btn btn-default"> <input type="submit" value="submit" class="btn btn-primary"> </div></div><div class="clearfix"></div></form></div></div>';
        $horizontal_left = $horizontal_left . $horizontal . '<div class="form-group"><div class="col-lg-10 col-lg-offset-2"> <input type="reset" value="reset" class="btn btn-default"> <input type="submit" value="submit" class="btn btn-primary"> </div></div></fieldset></form></div><div class="col-sm-4 equal" id="extra-div"><div class="row extra" style="height: 100%; width: 98%; margin-left: 1%;"></div></div>';
        $ehorizontal_left = $ehorizontal_left . $ehorizontal . '<div class="form-group"><div class="col-lg-10 col-lg-offset-2"> <input type="reset" value="reset" class="btn btn-default"> <input type="submit" value="submit" class="btn btn-primary"> </div></div></fieldset></form></div><div class="col-sm-4 equal" id="extra-div"><div class="row extra" style="height: 100%; width: 98%; margin-left: 1%;"></div></div>';
        $horizontal_right = $horizontal_right . $horizontal . '<div class="form-group"><div class="col-lg-10 col-lg-offset-2"> <input type="reset" value="reset" class="btn btn-default"> <input type="submit" value="submit" class="btn btn-primary"> </div></div></fieldset></form></div></div>';
        $ehorizontal_right = $ehorizontal_right . $ehorizontal . '<div class="form-group"><div class="col-lg-10 col-lg-offset-2"> <input type="reset" value="reset" class="btn btn-default"> <input type="submit" value="submit" class="btn btn-primary"> </div></div></fieldset></form></div></div>';
        $vertical_left = $vertical_left . $vertical .'<div class="form-group"><div class="col-lg-12"> <input type="reset" value="reset" class="btn btn-default"> <input type="submit" value="submit" class="btn btn-primary"> </div></div></fieldset></form></div><div class="col-sm-4 equal" id="extra-div"><div class="row extra" style="height: 100%; width: 98%; margin-left: 1%;"></div></div></div>';
        $evertical_left = $evertical_left . $evertical .'<div class="form-group"><div class="col-lg-12"> <input type="reset" value="reset" class="btn btn-default"> <input type="submit" value="submit" class="btn btn-primary"> </div></div></fieldset></form></div><div class="col-sm-4 equal" id="extra-div"><div class="row extra" style="height: 100%; width: 98%; margin-left: 1%;"></div></div></div>';
        $vertical_right = $vertical_right . $vertical . '<div class="form-group"><div class="col-lg-12"> <input type="reset" value="reset" class="btn btn-default"> <input type="submit" value="submit" class="btn btn-primary"> </div></div></fieldset></form></div></div>';
        $evertical_right = $evertical_right . $evertical . '<div class="form-group"><div class="col-lg-12"> <input type="reset" value="reset" class="btn btn-default"> <input type="submit" value="submit" class="btn btn-primary"> </div></div></fieldset></form></div></div>';
        $horizontal_center = $horizontal_center . $horizontal .'<div class="form-group"><div class="col-lg-10 col-lg-offset-2"> <input type="reset" value="reset" class="btn btn-default"> <input type="submit" value="submit" class="btn btn-primary"> </div></div></fieldset></form></div></div> ';
        $ehorizontal_center = $ehorizontal_center . $ehorizontal .'<div class="form-group"><div class="col-lg-10 col-lg-offset-2"> <input type="reset" value="reset" class="btn btn-default"> <input type="submit" value="submit" class="btn btn-primary"> </div></div></fieldset></form></div></div> ';
        $vertical_center = $vertical_center . $vertical .'<div class="form-group"><div class="col-lg-12"> <input type="reset" value="reset" class="btn btn-default"> <input type="submit" value="submit" class="btn btn-primary"> </div></div></fieldset></form></div></div>';
        $evertical_center = $evertical_center . $evertical .'<div class="form-group"><div class="col-lg-12"> <input type="reset" value="reset" class="btn btn-default"> <input type="submit" value="submit" class="btn btn-primary"> </div></div></fieldset></form></div></div>';
        $horizontal_cols = $horizontal_cols .'<div class="form-group col-sm-6"><div class="col-lg-10 col-lg-offset-2"> <input type="reset" value="reset" class="btn btn-default"> <input type="submit" value="submit" class="btn btn-primary"> </div></div><div class="clearfix"></div></fieldset></form></div></div>';
        $ehorizontal_cols = $ehorizontal_cols .'<div class="form-group col-sm-6"><div class="col-lg-10 col-lg-offset-2"> <input type="reset" value="reset" class="btn btn-default"> <input type="submit" value="submit" class="btn btn-primary"> </div></div><div class="clearfix"></div></fieldset></form></div></div>';

        $vertical_cols = $vertical_cols . "',";
        $evertical_cols = $evertical_cols . "',";
        $horizontal_left = $horizontal_left . "',";
        $ehorizontal_left = $ehorizontal_left . "',";
        $vertical_left = $vertical_left . "',";
        $evertical_left = $evertical_left . "',";
        $horizontal_right = $horizontal_right . "',";
        $ehorizontal_right = $ehorizontal_right . "',";
        $vertical_right = $vertical_right . "',";
        $evertical_right = $evertical_right . "',";
        $horizontal_center = $horizontal_center . "',";
        $ehorizontal_center = $ehorizontal_center . "',";
        $vertical_center = $vertical_center . "',";
        $evertical_center = $evertical_center . "',";
        $horizontal_cols = $horizontal_cols . "'";
        $ehorizontal_cols = $ehorizontal_cols . "'";

        $formsArray = $formsArray . $vertical_cols . $horizontal_left . $vertical_left . $horizontal_right . $vertical_right . $horizontal_center . $vertical_center . $horizontal_cols.'};';
        $eformsArray = $eformsArray . $evertical_cols . $ehorizontal_left . $evertical_left . $ehorizontal_right . $evertical_right . $ehorizontal_center . $evertical_center . $ehorizontal_cols.'};';


        //        ========================= file handling ============================
        // get content from .js file
        $data = file_get_contents('autocrud/js/custom.js');


        // get specific data from file
        $var_name = 'var ' . $request->table;
        $evar_name = 'var edit_'.$request->table;


        $startsAt = strpos($data, $var_name) + strlen($var_name);
        $endsAt = strpos($data, "};", $startsAt);
        $result = substr($data, $startsAt, $endsAt - $startsAt);
        $result = $var_name .$result . "};";

        $estartsAt = strpos($data, $evar_name) + strlen($evar_name);
        $eendsAt = strpos($data, "};", $estartsAt);
        $eresult = substr($data, $estartsAt, $eendsAt - $estartsAt);
        $eresult = $evar_name .$eresult . "};";

        //remove specific content from variable and store it again in variable
        $data = str_replace($result, '', $data);
        $data = str_replace($eresult, '', $data);

        //add new form details in file at the beginning of file
        $formsArray = $eformsArray . $formsArray . $data;

        //rewrite data to file
        file_put_contents('autocrud/js/custom.js', $formsArray);


        try
        {
            $table = $request->table;
            $is_available = DB::table('page_layouts')
                ->where('table', $table)
                ->first();

            if (!$is_available) {
                $page_layouts = new PageLayout();
                $page_layouts->table = $table;
                $page_layouts->save();
                return view('autocrud::Admin.Single_table.pageLayout', compact('table'));
            }
            else
            {
                return view('autocrud::Admin.Single_table.pageLayout', compact('table','is_available'));
            }
        }
        catch (\Exception $e) {
            echo $e->getMessage();
        }
    }

    public function getFormFields(Request $request){
        $form_definations = DB::table('form_definations')
            ->where('table',$request->table)
            ->get();
        return $form_definations;
    }

    function str_replace_first($from, $to, $subject){
        $from = '/'.preg_quote($from, '/').'/';
        return preg_replace($from, $to, $subject, 1);
    }

    public function updateForm(Request $request){
        $formsArray = 'var '. $request->table .' = {';
        $eformsArray = 'var edit_'. $request->table .' = {';

        $vertical_cols = '"vertical_cols" :' . "'";
        $evertical_cols = '"vertical_cols" :' . "'";
        $horizontal_left = '"horizontal_left" :' . "'";
        $ehorizontal_left = '"horizontal_left" :' . "'";
        $horizontal_right = '"horizontal_right" : ' . "'";
        $ehorizontal_right = '"horizontal_right" : ' . "'";
        $vertical_left = '"vertical_left" : ' . "'";
        $evertical_left = '"vertical_left" : ' . "'";
        $vertical_right = '"vertical_right" : ' . "'";
        $evertical_right = '"vertical_right" : ' . "'";
        $horizontal_center = '"horizontal_center" : ' . "'";
        $ehorizontal_center = '"horizontal_center" : ' . "'";
        $vertical_center = '"vertical_center" : ' . "'";
        $evertical_center = '"vertical_center" : ' . "'";
        $horizontal_cols = '"horizontal_cols" : ' . "'";
        $ehorizontal_cols = '"horizontal_cols" : ' . "'";

        $vertical_cols = $vertical_cols . '<div class=""><center><h3 class="b-bottom heading">Form Preview</h3></center></div><div class="row"><div class="col-sm-10 col-sm-offset-1 shadow" id="form-div-center" style="background-color: #fff;border-radius:5px; "><form class="cols-form"><legend>YOUR FORM TITLE</legend>';
        $evertical_cols = $evertical_cols . '<div class=""><center><h3 class="b-bottom heading">Form Preview</h3></center></div><div class="row"><div class="col-sm-10 col-sm-offset-1 shadow" id="form-div-center" style="background-color: #fff;border-radius:5px; "><form class="cols-form"><legend>YOUR FORM TITLE</legend>';
        $horizontal_left = $horizontal_left . '<div class=""><center><h3 class="b-bottom heading">Form Preview</h3></center></div><div class="row"><div class="col-md-6 col-sm-offset-1 shadow" id="form-div" style="background-color: #fff;border-radius:5px; "><form class="form-horizontal"><fieldset><legend>YOUR FORM TITLE</legend>';
        $ehorizontal_left = $ehorizontal_left . '<div class=""><center><h3 class="b-bottom heading">Form Preview</h3></center></div><div class="row"><div class="col-md-6 col-sm-offset-1 shadow" id="form-div" style="background-color: #fff;border-radius:5px; "><form class="form-horizontal"><fieldset><legend>YOUR FORM TITLE</legend>';
        $horizontal_right = $horizontal_right . '<div class=""><center><h3 class="b-bottom heading">Form Preview</h3></center></div><div class="row"><div class="col-sm-4 equal col-sm-offset-1" id="extra-div-right"><div class="row extra" style="height: 100%; width: 98%; margin-left: 1%;"></div></div><div class="col-md-6 shadow" id="form-div-right" style="background-color: #fff;border-radius:5px; "><form class="form-horizontal"><fieldset><legend>YOUR FORM TITLE</legend>';
        $ehorizontal_right = $ehorizontal_right . '<div class=""><center><h3 class="b-bottom heading">Form Preview</h3></center></div><div class="row"><div class="col-sm-4 equal col-sm-offset-1" id="extra-div-right"><div class="row extra" style="height: 100%; width: 98%; margin-left: 1%;"></div></div><div class="col-md-6 shadow" id="form-div-right" style="background-color: #fff;border-radius:5px; "><form class="form-horizontal"><fieldset><legend>YOUR FORM TITLE</legend>';
        $vertical_left = $vertical_left . '<div class=""><center><h3 class="b-bottom heading">Form Preview</h3></center></div><div class="row"><div class="col-md-6 col-sm-offset-1 shadow" id="form-div" style="background-color: #fff;border-radius:5px; "><form class="form"><fieldset><legend>YOUR FORM TITLE</legend>';
        $evertical_left = $evertical_left . '<div class=""><center><h3 class="b-bottom heading">Form Preview</h3></center></div><div class="row"><div class="col-md-6 col-sm-offset-1 shadow" id="form-div" style="background-color: #fff;border-radius:5px; "><form class="form"><fieldset><legend>YOUR FORM TITLE</legend>';
        $vertical_right = $vertical_right . '<div class=""><center><h3 class="b-bottom heading">Form Preview</h3></center></div><div class="row"><div class="col-sm-4 col-sm-offset-1 equal" id="extra-div-right"><div class="row extra" style="height: 100%; width: 98%; margin-left: 1%;"></div></div><div class="col-md-6 shadow" id="form-div-right" style="background-color: #fff;border-radius:5px; "><form class="form"><fieldset><legend>YOUR FORM TITLE</legend>';
        $evertical_right = $evertical_right . '<div class=""><center><h3 class="b-bottom heading">Form Preview</h3></center></div><div class="row"><div class="col-sm-4 col-sm-offset-1 equal" id="extra-div-right"><div class="row extra" style="height: 100%; width: 98%; margin-left: 1%;"></div></div><div class="col-md-6 shadow" id="form-div-right" style="background-color: #fff;border-radius:5px; "><form class="form"><fieldset><legend>YOUR FORM TITLE</legend>';
        $horizontal_center = $horizontal_center . '<div class=""><center><h3 class="b-bottom heading">Form Preview</h3></center></div><div class="row"><div class="col-sm-6 col-sm-offset-3 shadow" id="form-div-center" style="background-color: #fff;border-radius:5px; "><form class="form-horizontal"><fieldset><legend>YOUR FORM TITLE</legend>';
        $ehorizontal_center = $ehorizontal_center . '<div class=""><center><h3 class="b-bottom heading">Form Preview</h3></center></div><div class="row"><div class="col-sm-6 col-sm-offset-3 shadow" id="form-div-center" style="background-color: #fff;border-radius:5px; "><form class="form-horizontal"><fieldset><legend>YOUR FORM TITLE</legend>';
        $vertical_center = $vertical_center . '<div class=""><center><h3 class="b-bottom heading">Form Preview</h3></center></div><div class="row"><div class="col-sm-6 col-sm-offset-3 shadow" id="form-div-center" style="background-color: #fff;border-radius:5px; "><form class="form"><fieldset><legend>YOUR FORM TITLE</legend>';
        $evertical_center = $evertical_center . '<div class=""><center><h3 class="b-bottom heading">Form Preview</h3></center></div><div class="row"><div class="col-sm-6 col-sm-offset-3 shadow" id="form-div-center" style="background-color: #fff;border-radius:5px; "><form class="form"><fieldset><legend>YOUR FORM TITLE</legend>';
        $horizontal_cols = $horizontal_cols . '<div class=""><center><h3 class="b-bottom heading">Form Preview</h3></center></div><div class="row"><div class="col-sm-10 col-sm-offset-1 shadow" id="form-div-center" style="background-color: #fff;border-radius:5px; "><form class="form-horizontal cols-form"><fieldset><legend>YOUR FORM TITLE</legend>';
        $ehorizontal_cols = $ehorizontal_cols . '<div class=""><center><h3 class="b-bottom heading">Form Preview</h3></center></div><div class="row"><div class="col-sm-10 col-sm-offset-1 shadow" id="form-div-center" style="background-color: #fff;border-radius:5px; "><form class="form-horizontal cols-form"><fieldset><legend>YOUR FORM TITLE</legend>';

        $horizontal = "";
        $ehorizontal = "";
        $vertical = "";
        $evertical = "";

        //remove all entry from form_definations if requested table is exist.
        if (DB::table('form_definations')->where('table',$request->table)->exists())
        {
            DB::table('form_definations')
                ->where('table',$request->table)
                ->delete();
        }
        for($i=0; $i<$request->no_of_fields; $i++)
        {
            $field_name = $request->{'field'.$i};
            if($request->{'inputType'.$i} == 'text')
            {
                if(trim($request->{$field_name.'pattern'}," ") == "")
                {
                    $vertical_cols = $vertical_cols .'<div class="form-group col-sm-6"><label class="control-label capitalize capitalize">'.$request->{'field-lbl'.$i}.'</label><input type="text" class="form-control" name="'. $request->{'field'.$i} .'" id="" placeholder="'.$request->{$field_name.'placeholder'}.'" maxlength="'.$request->{$field_name.'maxlength'}.'" minlength="'.$request->{$field_name.'minlength'}.'" '.$request->{$field_name.'required'}.'></div>';
                    $evertical_cols = $evertical_cols .'<div class="form-group col-sm-6"><label class="control-label capitalize capitalize">'.$request->{'field-lbl'.$i}.'</label><input type="text" class="form-control" name="'. $request->{'field'.$i} .'" value = "{{ $data->'.$request->{'field'.$i}.' }}" id="" placeholder="'.$request->{$field_name.'placeholder'}.'" maxlength="'.$request->{$field_name.'maxlength'}.'" minlength="'.$request->{$field_name.'minlength'}.'" '.$request->{$field_name.'required'}.'></div>';
                    $horizontal_cols = $horizontal_cols . '<div class="form-group col-sm-6"> <label class="col-lg-2 control-label capitalize capitalize">'.$request->{'field-lbl'.$i}.'</label><div class="col-lg-10"> <input type="text" id="" name="'. $request->{'field'.$i} .'" class="form-control"  placeholder="'.$request->{$field_name.'placeholder'}.'" maxlength="'.$request->{$field_name.'maxlength'}.'" minlength="'.$request->{$field_name.'minlength'}.'" '.$request->{$field_name.'required'}.'></div></div>';
                    $ehorizontal_cols = $ehorizontal_cols . '<div class="form-group col-sm-6"> <label class="col-lg-2 control-label capitalize capitalize">'.$request->{'field-lbl'.$i}.'</label><div class="col-lg-10"> <input type="text" id="" name="'. $request->{'field'.$i} .'" class="form-control" value = "{{ $data->'.$request->{'field'.$i}.' }}" placeholder="'.$request->{$field_name.'placeholder'}.'" maxlength="'.$request->{$field_name.'maxlength'}.'" minlength="'.$request->{$field_name.'minlength'}.'" '.$request->{$field_name.'required'}.'></div></div>';
                    $horizontal = $horizontal . '<div class="form-group"> <label class="col-lg-2 control-label capitalize capitalize">'.$request->{'field-lbl'.$i}.'</label><div class="col-lg-10"> <input type="text" id="" class="form-control" name="'. $request->{'field'.$i} .'" placeholder="'.$request->{$field_name.'placeholder'}.'" maxlength="'.$request->{$field_name.'maxlength'}.'" minlength="'.$request->{$field_name.'minlength'}.'" '.$request->{$field_name.'required'}.'></div></div>';
                    $ehorizontal = $ehorizontal . '<div class="form-group"> <label class="col-lg-2 control-label capitalize capitalize">'.$request->{'field-lbl'.$i}.'</label><div class="col-lg-10"> <input type="text" id="" class="form-control" name="'. $request->{'field'.$i} .'" placeholder="'.$request->{$field_name.'placeholder'}.'" value = "{{ $data->'.$request->{'field'.$i}.' }}" maxlength="'.$request->{$field_name.'maxlength'}.'" minlength="'.$request->{$field_name.'minlength'}.'" '.$request->{$field_name.'required'}.'></div></div>';
                    $vertical = $vertical . '<div class="form-group"><label class="control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><input type="text" class="form-control" id="" name="'. $request->{'field'.$i} .'" placeholder="'.$request->{$field_name.'placeholder'}.'" maxlength="'.$request->{$field_name.'maxlength'}.'" minlength="'.$request->{$field_name.'minlength'}. '" '.$request->{$field_name.'required'}.'></div>';
                    $evertical = $evertical . '<div class="form-group"><label class="control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><input type="text" class="form-control" id="" value = "{{ $data->'.$request->{'field'.$i}.' }}" name="'. $request->{'field'.$i} .'" placeholder="'.$request->{$field_name.'placeholder'}.'" maxlength="'.$request->{$field_name.'maxlength'}.'" minlength="'.$request->{$field_name.'minlength'}. '" '.$request->{$field_name.'required'}.'></div>';
                }
                else
                {
                    $vertical_cols = $vertical_cols .'<div class="form-group col-sm-6"><label class="control-label capitalize capitalize">'.$request->{'field-lbl'.$i}.'</label><input type="text" class="form-control" name="'. $request->{'field'.$i} .'" id="" placeholder="'.$request->{$field_name.'placeholder'}.'" maxlength="'.$request->{$field_name.'maxlength'}.'" minlength="'.$request->{$field_name.'minlength'}.'" pattern="'.$request->{$field_name.'pattern'}.'" '.$request->{$field_name.'required'}.'></div>';
                    $evertical_cols = $evertical_cols .'<div class="form-group col-sm-6"><label class="control-label capitalize capitalize">'.$request->{'field-lbl'.$i}.'</label><input type="text" value = "{{ $data->'.$request->{'field'.$i}.' }}" class="form-control" name="'. $request->{'field'.$i} .'" id="" placeholder="'.$request->{$field_name.'placeholder'}.'" maxlength="'.$request->{$field_name.'maxlength'}.'" minlength="'.$request->{$field_name.'minlength'}.'" pattern="'.$request->{$field_name.'pattern'}.'" '.$request->{$field_name.'required'}.'></div>';
                    $horizontal_cols = $horizontal_cols . '<div class="form-group col-sm-6"> <label class="col-lg-2 control-label capitalize capitalize">'.$request->{'field-lbl'.$i}.'</label><div class="col-lg-10"> <input type="text" id="" name="'. $request->{'field'.$i} .'" class="form-control"  placeholder="'.$request->{$field_name.'placeholder'}.'" maxlength="'.$request->{$field_name.'maxlength'}.'" minlength="'.$request->{$field_name.'minlength'}.'" pattern="'.$request->{$field_name.'pattern'}.'" '.$request->{$field_name.'required'}.'></div></div>';
                    $ehorizontal_cols = $ehorizontal_cols . '<div class="form-group col-sm-6"> <label class="col-lg-2 control-label capitalize capitalize">'.$request->{'field-lbl'.$i}.'</label><div class="col-lg-10"> <input type="text" id="" name="'. $request->{'field'.$i} .'" value = "{{ $data->'.$request->{'field'.$i}.' }}" class="form-control"  placeholder="'.$request->{$field_name.'placeholder'}.'" maxlength="'.$request->{$field_name.'maxlength'}.'" minlength="'.$request->{$field_name.'minlength'}.'" pattern="'.$request->{$field_name.'pattern'}.'" '.$request->{$field_name.'required'}.'></div></div>';
                    $horizontal = $horizontal . '<div class="form-group"> <label class="col-lg-2 control-label capitalize capitalize">'.$request->{'field-lbl'.$i}.'</label><div class="col-lg-10"> <input type="text" id="" class="form-control" name="'. $request->{'field'.$i} .'" placeholder="'.$request->{$field_name.'placeholder'}.'" maxlength="'.$request->{$field_name.'maxlength'}.'" minlength="'.$request->{$field_name.'minlength'}.'" pattern="'.$request->{$field_name.'pattern'}.'" '.$request->{$field_name.'required'}.'></div></div>';
                    $ehorizontal = $ehorizontal . '<div class="form-group"> <label class="col-lg-2 control-label capitalize capitalize">'.$request->{'field-lbl'.$i}.'</label><div class="col-lg-10"> <input type="text" id="" class="form-control" name="'. $request->{'field'.$i} .'" value = "{{ $data->'.$request->{'field'.$i}.' }}" placeholder="'.$request->{$field_name.'placeholder'}.'" maxlength="'.$request->{$field_name.'maxlength'}.'" minlength="'.$request->{$field_name.'minlength'}.'" pattern="'.$request->{$field_name.'pattern'}.'" '.$request->{$field_name.'required'}.'></div></div>';
                    $vertical = $vertical . '<div class="form-group"><label class="control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><input type="text" class="form-control" id="" name="'. $request->{'field'.$i} .'" placeholder="'.$request->{$field_name.'placeholder'}.'" maxlength="'.$request->{$field_name.'maxlength'}.'" minlength="'.$request->{$field_name.'minlength'}.'" pattern="'.$request->{$field_name.'pattern'}.'" '.$request->{$field_name.'required'}.'></div>';
                    $evertical = $evertical . '<div class="form-group"><label class="control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><input type="text" value = "{{ $data->'.$request->{'field'.$i}.' }}" class="form-control" id="" name="'. $request->{'field'.$i} .'" placeholder="'.$request->{$field_name.'placeholder'}.'" maxlength="'.$request->{$field_name.'maxlength'}.'" minlength="'.$request->{$field_name.'minlength'}.'" pattern="'.$request->{$field_name.'pattern'}.'" '.$request->{$field_name.'required'}.'></div>';
                }

                if($i>0 && $i%2==1)
                {
                    $vertical_cols = $vertical_cols . '<div class="clearfix"></div>';
                    $evertical_cols = $evertical_cols . '<div class="clearfix"></div>';
                    $horizontal_cols = $horizontal_cols . '<div class="clearfix"></div>';
                    $ehorizontal_cols = $ehorizontal_cols . '<div class="clearfix"></div>';
                }

                try {
                    $formDefination = new FormDefination();
                    $formDefination->table = $request->table;
                    $formDefination->type = $request->{'inputType' . $i};
                    $formDefination->name = str_replace(" ","_",$request->{'field-lbl' . $i});
                    $formDefination->placeholder = $request->{$field_name . 'placeholder'};
                    $formDefination->max = $request->{$field_name . 'maxlength'};
                    $formDefination->min = $request->{$field_name . 'minlength'};
                    $formDefination->pattern = $request->{$field_name . 'pattern'};
                    $required = $request->{$field_name . 'required'} == "" ? " " : 'required';
                    $formDefination->required = $required;
                    $formDefination->save();
                }
                catch(\Exception $e)
                {
                    echo $e;
                }
            }
            elseif($request->{'inputType'.$i} == 'number')
            {
                $vertical_cols = $vertical_cols .'<div class="form-group col-sm-6"><label class="control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><input type="number" class="form-control" id="" name="'. $request->{'field'.$i} .'" placeholder="'.$request->{$field_name.'placeholder'}.'" max="'.$request->{$field_name.'maxlength'}.'" min="'.$request->{$field_name.'minlength'}.'" step="'.$request->{$field_name.'step'}.'" '.$request->{$field_name.'required'}.'></div>';
                $evertical_cols = $evertical_cols .'<div class="form-group col-sm-6"><label class="control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><input type="number" class="form-control" id="" name="'. $request->{'field'.$i} .'" value = "{{ $data->'.$request->{'field'.$i}.' }}" placeholder="'.$request->{$field_name.'placeholder'}.'" max="'.$request->{$field_name.'maxlength'}.'" min="'.$request->{$field_name.'minlength'}.'" step="'.$request->{$field_name.'step'}.'" '.$request->{$field_name.'required'}.'></div>';
                $horizontal_cols = $horizontal_cols.'<div class="form-group col-sm-6"> <label class="col-lg-2 control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><div class="col-lg-10"> <input type="number" name="'. $request->{'field'.$i} .'" id="" class="form-control"  placeholder="'.$request->{$field_name.'placeholder'}.'" max="'.$request->{$field_name.'maxlength'}.'" min="'.$request->{$field_name.'minlength'}.'" step="'.$request->{$field_name.'step'}.'" '.$request->{$field_name.'required'}.'></div></div>';
                $ehorizontal_cols = $ehorizontal_cols.'<div class="form-group col-sm-6"> <label class="col-lg-2 control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><div class="col-lg-10"> <input type="number" name="'. $request->{'field'.$i} .'" value = "{{ $data->'.$request->{'field'.$i}.' }}" id="" class="form-control"  placeholder="'.$request->{$field_name.'placeholder'}.'" max="'.$request->{$field_name.'maxlength'}.'" min="'.$request->{$field_name.'minlength'}.'" step="'.$request->{$field_name.'step'}.'" '.$request->{$field_name.'required'}.'></div></div>';
                $horizontal = $horizontal.'<div class="form-group"> <label class="col-lg-2 control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><div class="col-lg-10"> <input type="number" id="" class="form-control" name="'. $request->{'field'.$i} .'" placeholder="'.$request->{$field_name.'placeholder'}.'" max="'.$request->{$field_name.'maxlength'}.'" min="'.$request->{$field_name.'minlength'}.'" step="'.$request->{$field_name.'step'}.'" '.$request->{$field_name.'required'}.'></div></div>';
                $ehorizontal = $ehorizontal.'<div class="form-group"> <label class="col-lg-2 control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><div class="col-lg-10"> <input type="number" id="" class="form-control" value = "{{ $data->'.$request->{'field'.$i}.' }}" name="'. $request->{'field'.$i} .'" placeholder="'.$request->{$field_name.'placeholder'}.'" max="'.$request->{$field_name.'maxlength'}.'" min="'.$request->{$field_name.'minlength'}.'" step="'.$request->{$field_name.'step'}.'" '.$request->{$field_name.'required'}.'></div></div>';
                $vertical = $vertical .'<div class="form-group"><label class="control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><input type="number" class="form-control" id="" name="'. $request->{'field'.$i} .'" placeholder="'.$request->{$field_name.'placeholder'}.'" max="'.$request->{$field_name.'maxlength'}.'" min="'.$request->{$field_name.'minlength'}.'" step="'.$request->{$field_name.'step'}.'" '.$request->{$field_name.'required'}.'></div>';
                $evertical = $evertical .'<div class="form-group"><label class="control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><input type="number" class="form-control" id="" name="'. $request->{'field'.$i} .'" value = "{{ $data->'.$request->{'field'.$i}.' }}" placeholder="'.$request->{$field_name.'placeholder'}.'" max="'.$request->{$field_name.'maxlength'}.'" min="'.$request->{$field_name.'minlength'}.'" step="'.$request->{$field_name.'step'}.'" '.$request->{$field_name.'required'}.'></div>';

                if($i>0 && $i%2==1)
                {
                    $vertical_cols = $vertical_cols . '<div class="clearfix"></div>';
                    $evertical_cols = $evertical_cols . '<div class="clearfix"></div>';
                    $horizontal_cols = $horizontal_cols.'<div class="clearfix"></div>';
                    $ehorizontal_cols = $ehorizontal_cols.'<div class="clearfix"></div>';
                }

                try
                {
                    $formDefination = new FormDefination();
                    $formDefination->table = $request->table;
                    $formDefination->type = $request->{'inputType'.$i};
                    $formDefination->name = str_replace(" ","_",$request->{'field-lbl' . $i});
                    $formDefination->placeholder = $request->{$field_name.'placeholder'};
                    $formDefination->max = $request->{$field_name.'maxlength'};
                    $formDefination->min = $request->{$field_name.'minlength'};
                    $formDefination->step =$request->{$field_name.'step'};
                    $required = $request->{$field_name.'required'} == ""? " " : 'required' ;
                    $formDefination->required = $required;
                    $formDefination->save();
                }
                catch(\Exception $e)
                {
                    echo $e;
                }
            }
            
            elseif($request->{'inputType'.$i} == 'radio')
            {
                $arr = explode(",",$request->{$field_name.'radio'});
                $vertical_cols_options = "";
                $evertical_cols_options = "";
                $horizontal_options = "";
                $ehorizontal_options = "";
                $vertical_options = "";
                $evertical_options = "";
                foreach($arr as $item)
                {
                    $vertical_cols_options = $vertical_cols_options . '<div class="radio"><label><input type="radio" name="'. $request->{"field".$i} .'"  value="'.$item.'">'.$item.'</label></div>';
                    $evertical_cols_options = $evertical_cols_options . '<div class="radio"><label><input type="radio" name="'.$request->{"field".$i} .'"  value="'.$item.'" {{ $data->'.$request->{'field'.$i}.' == "'.$item.'" ? "checked" : "" }}>'.$item.'</label></div>';
                    $horizontal_options = $horizontal_options . '<div class="radio"> <label> <input type="radio" name="'. $request->{"field".$i}.'" id="" value="'.$item.'">'.$item.'</label></div>';
                    $ehorizontal_options = $ehorizontal_options . '<div class="radio"> <label> <input type="radio" name="'. $request->{"field".$i} .'" id="" value="'.$item.'" {{ $data->'.$request->{'field'.$i}.' == "'.$item.'" ? "checked" : "" }}>'.$item.'</label></div>';
                    $vertical_options = $vertical_options . '<div class="radio"><label><input type="radio" name="'. $request->{"field".$i} .'" value="'.$item.'">'.$item.'</label></div>';
                    $evertical_options = $evertical_options . '<div class="radio"><label><input type="radio" name="'. $request->{"field".$i} .'" value="'.$item.'"  {{ $data->'.$request->{'field'.$i}.' == "'.$item.'" ? "checked" : "" }}>'.$item.'</label></div>';
                }
                $vertical_cols = $vertical_cols . '<div class="form-group col-sm-6"><label class="control-label capitalize">'.$request->{'field-lbl'.$i}.'</label>'.$vertical_cols_options.'</div>';
                $evertical_cols = $evertical_cols . '<div class="form-group col-sm-6"><label class="control-label capitalize">'.$request->{'field-lbl'.$i}.'</label>'.$evertical_cols_options.'</div>';
                $horizontal_cols = $horizontal_cols .'<div class="form-group col-sm-6"><label class="col-lg-2 control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><div class="col-lg-10">'.$horizontal_options.'</div></div>';
                $ehorizontal_cols = $ehorizontal_cols .'<div class="form-group col-sm-6"><label class="col-lg-2 control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><div class="col-lg-10">'.$ehorizontal_options.'</div></div>';
                $horizontal = $horizontal .'<div class="form-group"><label class="col-lg-2 control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><div class="col-lg-10">'.$horizontal_options.'</div></div>';
                $ehorizontal = $ehorizontal .'<div class="form-group"><label class="col-lg-2 control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><div class="col-lg-10">'.$ehorizontal_options.'</div></div>';
                $vertical = $vertical . '<div class="form-group"><label class="control-label capitalize">'.$request->{'field-lbl'.$i}.'</label>'.$vertical_options.'</div>';
                $evertical = $evertical . '<div class="form-group"><label class="control-label capitalize">'.$request->{'field-lbl'.$i}.'</label>'.$evertical_options.'</div>';
                if($i>0 && $i%2==1)
                {
                    $vertical_cols = $vertical_cols . '<div class="clearfix"></div>';
                    $evertical_cols = $evertical_cols . '<div class="clearfix"></div>';
                    $horizontal_cols = $horizontal_cols.'<div class="clearfix"></div>';
                    $ehorizontal_cols = $ehorizontal_cols.'<div class="clearfix"></div>';
                }

                try
                {
                    $formDefination = new FormDefination();
                    $formDefination->table = $request->table;
                    $formDefination->type = $request->{'inputType'.$i};
                    $formDefination->name = str_replace(" ","_",$request->{'field-lbl' . $i});
                    $formDefination->value = $request->{$field_name.'radio'};
                    $required = $request->{$field_name.'required'} == ""? " " : 'required' ;
                    $formDefination->required = $required;
                    $formDefination->save();
                }
                catch(\Exception $e)
                {
                    echo $e;
                }
            }
            elseif($request->{'inputType'.$i} == 'range')
            {
                $vertical_cols = $vertical_cols .'<div class="form-group col-sm-6"><label class="control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><input type="range" class="form-control" id="" name="'. $request->{'field'.$i} .'" value="'.$request->{$field_name.'value'}.'" max="'.$request->{$field_name.'maxlength'}.'" min="'.$request->{$field_name.'minlength'}.'" step="'.$request->{$field_name.'step'}.'" '.$request->{$field_name.'required'}.'></div>';
                $evertical_cols = $evertical_cols .'<div class="form-group col-sm-6"><label class="control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><input type="range" class="form-control" id="" name="'. $request->{'field'.$i} .'" value="'.$request->{$field_name.'value'}.'" max="'.$request->{$field_name.'maxlength'}.'" min="'.$request->{$field_name.'minlength'}.'" step="'.$request->{$field_name.'step'}.'" '.$request->{$field_name.'required'}.'></div>';
                $horizontal_cols = $horizontal_cols.'<div class="form-group col-sm-6"> <label class="col-lg-2 control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><div class="col-lg-10"> <input type="range" name="'. $request->{'field'.$i} .'" id="" class="form-control"  value="'.$request->{$field_name.'value'}.'" max="'.$request->{$field_name.'maxlength'}.'" min="'.$request->{$field_name.'minlength'}.'" step="'.$request->{$field_name.'step'}.'" '.$request->{$field_name.'required'}.'></div></div>';
                $ehorizontal_cols = $ehorizontal_cols.'<div class="form-group col-sm-6"> <label class="col-lg-2 control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><div class="col-lg-10"> <input type="range" name="'. $request->{'field'.$i} .'" id="" class="form-control"  value="'.$request->{$field_name.'value'}.'" max="'.$request->{$field_name.'maxlength'}.'" min="'.$request->{$field_name.'minlength'}.'" step="'.$request->{$field_name.'step'}.'" '.$request->{$field_name.'required'}.'></div></div>';
                $horizontal = $horizontal.'<div class="form-group"> <label class="col-lg-2 control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><div class="col-lg-10"> <input type="range" id="" class="form-control" name="'. $request->{'field'.$i} .'" value="'.$request->{$field_name.'value'}.'" max="'.$request->{$field_name.'maxlength'}.'" min="'.$request->{$field_name.'minlength'}.'" step="'.$request->{$field_name.'step'}.'" '.$request->{$field_name.'required'}.'></div></div>';
                $ehorizontal = $ehorizontal.'<div class="form-group"> <label class="col-lg-2 control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><div class="col-lg-10"> <input type="range" id="" class="form-control" name="'. $request->{'field'.$i} .'" value="'.$request->{$field_name.'value'}.'" max="'.$request->{$field_name.'maxlength'}.'" min="'.$request->{$field_name.'minlength'}.'" step="'.$request->{$field_name.'step'}.'" '.$request->{$field_name.'required'}.'></div></div>';
                $vertical = $vertical .'<div class="form-group"><label class="control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><input type="range" class="form-control" id="" value="'.$request->{$field_name.'value'}.'" name="'. $request->{'field'.$i} .'" max="'.$request->{$field_name.'maxlength'}.'" min="'.$request->{$field_name.'minlength'}.'" step="'.$request->{$field_name.'step'}.'" '.$request->{$field_name.'required'}.'></div>';
                $evertical = $evertical .'<div class="form-group"><label class="control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><input type="range" class="form-control" id="" value="'.$request->{$field_name.'value'}.'" name="'. $request->{'field'.$i} .'" max="'.$request->{$field_name.'maxlength'}.'" min="'.$request->{$field_name.'minlength'}.'" step="'.$request->{$field_name.'step'}.'" '.$request->{$field_name.'required'}.'></div>';
                if($i>0 && $i%2==1)
                {
                    $vertical_cols = $vertical_cols . '<div class="clearfix"></div>';
                    $evertical_cols = $evertical_cols . '<div class="clearfix"></div>';
                    $horizontal_cols = $horizontal_cols.'<div class="clearfix"></div>';
                    $ehorizontal_cols = $ehorizontal_cols.'<div class="clearfix"></div>';
                }

                try
                {
                    $formDefination = new FormDefination();
                    $formDefination->table = $request->table;
                    $formDefination->type = $request->{'inputType'.$i};
                    $formDefination->name = str_replace(" ","_",$request->{'field-lbl' . $i});
                    $formDefination->placeholder = $request->{$field_name.'placeholder'};
                    $formDefination->max = $request->{$field_name.'maxlength'};
                    $formDefination->min = $request->{$field_name.'minlength'};
                    $formDefination->step =$request->{$field_name.'step'};
                    $required = $request->{$field_name.'required'} == ""? " " : 'required' ;
                    $formDefination->required = $required;
                    $formDefination->save();
                }
                catch(\Exception $e)
                {
                    echo $e;
                }
            }
            elseif($request->{'inputType'.$i} == 'textarea')
            {
                $vertical_cols = $vertical_cols .'<div class="form-group col-sm-6"><label class="control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><textarea name="'. $request->{'field'.$i} .'" class="form-control" id="" placeholder="'.$request->{$field_name.'placeholder'}.'" maxlength="'.$request->{$field_name.'maxlength'}.'" minlength="'.$request->{$field_name.'minlength'}.'" rows="'.$request->{$field_name.'rows'}.'" '.$request->{$field_name.'required'}.'></textarea></div>';
                $evertical_cols = $evertical_cols .'<div class="form-group col-sm-6"><label class="control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><textarea value = "{{ $data->'.$request->{'field'.$i}.' }}" name="'. $request->{'field'.$i} .'" class="form-control" id="" placeholder="'.$request->{$field_name.'placeholder'}.'" maxlength="'.$request->{$field_name.'maxlength'}.'" minlength="'.$request->{$field_name.'minlength'}.'" rows="'.$request->{$field_name.'rows'}.'" '.$request->{$field_name.'required'}.'></textarea></div>';
                $horizontal_cols = $horizontal_cols.'<div class="form-group col-sm-6"> <label class="col-lg-2 control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><div class="col-lg-10"> <textarea name="'. $request->{'field'.$i} .'" id="" class="form-control"  placeholder="'.$request->{$field_name.'placeholder'}.'" maxlength="'.$request->{$field_name.'maxlength'}.'" minlength="'.$request->{$field_name.'minlength'}.'" rows="'.$request->{$field_name.'rows'}.'" '.$request->{$field_name.'required'}.'></textarea></div></div>';
                $ehorizontal_cols = $ehorizontal_cols.'<div class="form-group col-sm-6"> <label class="col-lg-2 control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><div class="col-lg-10"> <textarea name="'. $request->{'field'.$i} .'" id="" class="form-control" value = "{{ $data->'.$request->{'field'.$i}.' }}"  placeholder="'.$request->{$field_name.'placeholder'}.'" maxlength="'.$request->{$field_name.'maxlength'}.'" minlength="'.$request->{$field_name.'minlength'}.'" rows="'.$request->{$field_name.'rows'}.'" '.$request->{$field_name.'required'}.'></textarea></div></div>';
                $horizontal = $horizontal.'<div class="form-group"> <label class="col-lg-2 control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><div class="col-lg-10"> <textarea id="" class="form-control" name="'. $request->{'field'.$i} .'" placeholder="'.$request->{$field_name.'placeholder'}.'" maxlength="'.$request->{$field_name.'maxlength'}.'" minlength="'.$request->{$field_name.'minlength'}.'" rows="'.$request->{$field_name.'rows'}.'" '.$request->{$field_name.'required'}.'></textarea></div></div>';
                $ehorizontal = $ehorizontal.'<div class="form-group"> <label class="col-lg-2 control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><div class="col-lg-10"> <textarea id="" class="form-control" name="'. $request->{'field'.$i} .'" value = "{{ $data->'.$request->{'field'.$i}.' }}" placeholder="'.$request->{$field_name.'placeholder'}.'" maxlength="'.$request->{$field_name.'maxlength'}.'" minlength="'.$request->{$field_name.'minlength'}.'" rows="'.$request->{$field_name.'rows'}.'" '.$request->{$field_name.'required'}.'></textarea></div></div>';
                $vertical = $vertical .'<div class="form-group"><label class="control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><textarea class="form-control" id="" placeholder="'.$request->{$field_name.'placeholder'}.'" name="'. $request->{'field'.$i} .'" maxlength="'.$request->{$field_name.'maxlength'}.'" minlength="'.$request->{$field_name.'minlength'}.'" rows="'.$request->{$field_name.'rows'}.'" '.$request->{$field_name.'required'}.'></textarea></div>';
                $evertical = $evertical .'<div class="form-group"><label class="control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><textarea class="form-control" id="" placeholder="'.$request->{$field_name.'placeholder'}.'" value = "{{ $data->'.$request->{'field'.$i}.' }}" name="'. $request->{'field'.$i} .'" maxlength="'.$request->{$field_name.'maxlength'}.'" minlength="'.$request->{$field_name.'minlength'}.'" rows="'.$request->{$field_name.'rows'}.'" '.$request->{$field_name.'required'}.'></textarea></div>';
                if($i>0 && $i%2==1)
                {
                    $vertical_cols = $vertical_cols . '<div class="clearfix"></div>';
                    $evertical_cols = $evertical_cols . '<div class="clearfix"></div>';
                    $horizontal_cols = $horizontal_cols.'<div class="clearfix"></div>';
                    $ehorizontal_cols = $ehorizontal_cols.'<div class="clearfix"></div>';
                }

                try
                {
                    $formDefination = new FormDefination();
                    $formDefination->table = $request->table;
                    $formDefination->type = $request->{'inputType'.$i};
                    $formDefination->name = str_replace(" ","_",$request->{'field-lbl' . $i});
                    $formDefination->placeholder = $request->{$field_name.'placeholder'};
                    $formDefination->max = $request->{$field_name.'maxlength'};
                    $formDefination->min = $request->{$field_name.'minlength'};
                    $formDefination->rows = $request->{$field_name.'rows'};
                    $required = $request->{$field_name.'required'} == ""? " " : 'required' ;
                    $formDefination->required = $required;
                    $formDefination->save();
                }
                catch(\Exception $e)
                {
                    echo $e;
                }
            }
            elseif($request->{'inputType'.$i} == 'checkbox')
            {
                $arr = explode(",",$request->{$field_name.'checkbox'});
                $vertical_cols_options = "";
                $evertical_cols_options = "";
                $horizontal_options = "";
                $ehorizontal_options = "";
                $vertical_options = "";
                $evertical_options = "";
                foreach($arr as $item)
                {
                    $vertical_cols_options = $vertical_cols_options . '<div class="checkbox"><label><input type="checkbox" name="'.$request->{'field'.$i}.'[]" id="" value="'.$item.'">'.$item.'</label></div>';
                    $evertical_cols_options = $evertical_cols_options . '<div class="checkbox"><label><input type="checkbox" name="'.$request->{'field'.$i}.'[]" id="" value="'.$item.'"{{ $data->'.$request->{'field'.$i}.' ? "checked" :"" }}">'.$item.'</label></div>';
                    $horizontal_options = $horizontal_options . '<div class="checkbox"> <label> <input type="checkbox" name="'.$request->{'field'.$i}.'[]" id="" value="'.$item.'">'.$item.'</label></div>';
                    $ehorizontal_options = $ehorizontal_options . '<div class="checkbox"> <label> <input type="checkbox" name="'.$request->{'field'.$i}.'[]" id="" value="'.$item.'"{{ $data->'.$request->{'field'.$i}.' ? "checked" :"" }}">'.$item.'</label></div>';
                    $vertical_options = $vertical_options . '<div class="checkbox"><label><input type="checkbox" name="'.$request->{'field'.$i}.'[]" id="" value="'.$item.'">'.$item.'</label></div>';
                    $evertical_options = $evertical_options . '<div class="checkbox"><label><input type="checkbox" name="'.$request->{'field'.$i}.'[]" id="" value="'.$item.'"{{ $data->'.$request->{'field'.$i}.' ? "checked" :"" }}>'.$item.'</label></div>';
                }
                $vertical_cols = $vertical_cols . '<div class="form-group col-sm-6"><label class="control-label capitalize">'.$request->{'field-lbl'.$i}.'</label>'.$vertical_cols_options.'</div>';
                $evertical_cols = $evertical_cols . '<div class="form-group col-sm-6"><label class="control-label capitalize">'.$request->{'field-lbl'.$i}.'</label>'.$evertical_cols_options.'</div>';
                $horizontal_cols = $horizontal_cols .'<div class="form-group col-sm-6"><label class="col-lg-2 control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><div class="col-lg-10">'.$horizontal_options.'</div></div>';
                $ehorizontal_cols = $ehorizontal_cols .'<div class="form-group col-sm-6"><label class="col-lg-2 control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><div class="col-lg-10">'.$ehorizontal_options.'</div></div>';
                $horizontal = $horizontal .'<div class="form-group"><label class="col-lg-2 control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><div class="col-lg-10">'.$horizontal_options.'</div></div>';
                $ehorizontal = $ehorizontal .'<div class="form-group"><label class="col-lg-2 control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><div class="col-lg-10">'.$ehorizontal_options.'</div></div>';
                $vertical = $vertical . '<div class="form-group"><label class="control-label capitalize">'.$request->{'field-lbl'.$i}.'</label>'.$vertical_options.'</div>';
                $evertical = $evertical . '<div class="form-group"><label class="control-label capitalize">'.$request->{'field-lbl'.$i}.'</label>'.$evertical_options.'</div>';
                if($i>0 && $i%2==1)
                {
                    $vertical_cols = $vertical_cols . '<div class="clearfix"></div>';
                    $evertical_cols = $evertical_cols . '<div class="clearfix"></div>';
                    $horizontal_cols = $horizontal_cols.'<div class="clearfix"></div>';
                    $ehorizontal_cols = $ehorizontal_cols.'<div class="clearfix"></div>';
                }
                try
                {
                    $formDefination = new FormDefination();
                    $formDefination->table = $request->table;
                    $formDefination->type = $request->{'inputType'.$i};
                    $formDefination->name = str_replace(" ","_",$request->{'field-lbl' . $i});
                    $formDefination->value = $request->{$field_name.'checkbox'};
                    $required = $request->{$field_name.'required'} == ""? " " : 'required' ;
                    $formDefination->required = $required;
                    $formDefination->save();
                }
                catch(\Exception $e)
                {
                    echo $e;
                }
            }
            elseif($request->{'inputType'.$i} == 'select')
            {
                $arr = explode(",",$request->{$field_name.'select'});
                $vertical_cols_options = "";
                $evertical_cols_options = "";
                $horizontal_options = "";
                $ehorizontal_options = "";
                $vertical_options = "";
                $evertical_options = "";
                foreach($arr as $item)
                {
                    $vertical_cols_options = $vertical_cols_options . '<option value="'.$item.'">'.$item.'</option>';
                    $evertical_cols_options = $vertical_cols_options . '<option value="'.$item.'" {{ $data->'.$request->{'field'.$i}.' == "'.$item.'" ? "selected" :"" }}>'.$item.'</option>';
                    $horizontal_options = $horizontal_options . '<option value="'.$item.'">'.$item.'</option>';
                    $ehorizontal_options = $horizontal_options . '<option value="'.$item.'" {{ $data->'.$request->{'field'.$i}.' == "'.$item.'" ? "selected" :"" }}>'.$item.'</option>';
                    $vertical_options = $vertical_options . '<option value="'.$item.'">'.$item.'</option>';
                    $evertical_options = $vertical_options . '<option value="'.$item.'" {{ $data->'.$request->{'field'.$i}.' == "'.$item.'" ? "selected" :"" }}>'.$item.'</option>';
                }
                $vertical_cols = $vertical_cols . '<div class="form-group col-sm-6"><label class="control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><select class="form-control"  name="'. $request->{'field'.$i} .'" >'.$vertical_cols_options.'</select></div>';
                $evertical_cols = $evertical_cols . '<div class="form-group col-sm-6"><label class="control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><select class="form-control"  name="'. $request->{'field'.$i} .'" >'.$evertical_cols_options.'</select></div>';
                $horizontal_cols = $horizontal_cols .'<div class="form-group col-sm-6"><label class="col-lg-2 control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><div class="col-lg-10"><select class="form-control"  name="'. $request->{'field'.$i} .'" >'.$horizontal_options.'</select></div></div>';
                $ehorizontal_cols = $ehorizontal_cols .'<div class="form-group col-sm-6"><label class="col-lg-2 control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><div class="col-lg-10"><select class="form-control"  name="'. $request->{'field'.$i} .'" >'.$ehorizontal_options.'</select></div></div>';
                $horizontal = $horizontal .'<div class="form-group"><label class="col-lg-2 control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><div class="col-lg-10"><select class="form-control" name="'. $request->{'field'.$i} .'" >'.$horizontal_options.'</select></div></div>';
                $ehorizontal = $ehorizontal .'<div class="form-group"><label class="col-lg-2 control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><div class="col-lg-10"><select class="form-control" name="'. $request->{'field'.$i} .'" >'.$ehorizontal_options.'</select></div></div>';
                $vertical = $vertical . '<div class="form-group"><label class="control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><select class="form-control" name="'. $request->{'field'.$i} .'" >'.$vertical_options.'</select></div>';
                $evertical = $evertical . '<div class="form-group"><label class="control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><select class="form-control" name="'. $request->{'field'.$i} .'" >'.$evertical_options.'</select></div>';
                if($i>0 && $i%2==1)
                {
                    $vertical_cols = $vertical_cols . '<div class="clearfix"></div>';
                    $evertical_cols = $evertical_cols . '<div class="clearfix"></div>';
                    $horizontal_cols = $horizontal_cols.'<div class="clearfix"></div>';
                    $ehorizontal_cols = $ehorizontal_cols.'<div class="clearfix"></div>';
                }
                try
                {
                    $formDefination = new FormDefination();
                    $formDefination->table = $request->table;
                    $formDefination->type = $request->{'inputType'.$i};
                    $formDefination->name = str_replace(" ","_",$request->{'field-lbl' . $i});
                    $formDefination->value = $request->{$field_name.'select'};
                    $required = $request->{$field_name.'required'} == ""? " " : 'required' ;
                    $formDefination->required = $required;
                    $formDefination->save();
                }
                catch(\Exception $e)
                {
                    echo $e;
                }
            }
            elseif($request->{'inputType'.$i} == 'multiselect')
            {
                $arr = explode(",",$request->{$field_name.'multiselect'});
                $vertical_cols_options = "";
                $evertical_cols_options = "";
                $horizontal_options = "";
                $ehorizontal_options = "";
                $vertical_options = "";
                $evertical_options = "";
                foreach($arr as $item)
                {
                    $vertical_cols_options = $vertical_cols_options .'<option value="'.$item.'">'.$item.'</option>';
                    $evertical_cols_options = $evertical_cols_options .'<option value="'.$item.'"  {{ in_array("'.$item.'", explode(",", $data->'.$request->{'field'.$i}.')) ? "selected" :"" }}> '.$item.' </option>';
                    $horizontal_options = $horizontal_options . '<option value="'.$item.'">'.$item.'</option>';
                    $ehorizontal_options = $ehorizontal_options . '<option value="'.$item.'"  {{ in_array("'.$item.'", explode(",", $data->'.$request->{'field'.$i}.')) ? "selected" :"" }}> '.$item.' </option>';
                    $vertical_options = $vertical_options .'<option value="'.$item.'">'.$item.'</option>';
                    $evertical_options = $evertical_options .'<option value="'.$item.'"  {{ in_array("'.$item.'", explode(",", $data->'.$request->{'field'.$i}.')) ? "selected" :"" }}> '.$item.' </option>';
                }
                $vertical_cols = $vertical_cols . '<div class="form-group col-sm-6"><label class="control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><select multiple="" class="form-control"  name="'. $request->{'field'.$i} .'[]" >'.$vertical_cols_options.'</select></div>';
                $evertical_cols = $evertical_cols . '<div class="form-group col-sm-6"><label class="control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><select multiple="" class="form-control"  name="'. $request->{'field'.$i} .'[]" >'.$evertical_cols_options.'</select></div>';
                $horizontal_cols = $horizontal_cols .'<div class="form-group col-sm-6"><label class="col-lg-2 control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><div class="col-lg-10"><select multiple="" class="form-control" name="'. $request->{'field'.$i} .'[]" >'.$horizontal_options.'</select></div></div>';
                $ehorizontal_cols = $ehorizontal_cols .'<div class="form-group col-sm-6"><label class="col-lg-2 control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><div class="col-lg-10"><select multiple="" class="form-control" name="'. $request->{'field'.$i} .'[]" >'.$ehorizontal_options.'</select></div></div>';
                $horizontal = $horizontal .'<div class="form-group"><label class="col-lg-2 control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><div class="col-lg-10"><select multiple="" class="form-control" name="'. $request->{'field'.$i} .'[]" >'.$horizontal_options.'</select></div></div>';
                $ehorizontal = $ehorizontal .'<div class="form-group"><label class="col-lg-2 control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><div class="col-lg-10"><select multiple="" class="form-control" name="'. $request->{'field'.$i} .'[]" >'.$ehorizontal_options.'</select></div></div>';
                $vertical = $vertical . '<div class="form-group"><label class="control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><select multiple="" class="form-control" name="'. $request->{'field'.$i} .'[]" >'.$vertical_options.'</select></div>';
                $evertical = $evertical . '<div class="form-group"><label class="control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><select multiple="" class="form-control" name="'. $request->{'field'.$i} .'[]" >'.$evertical_options.'</select></div>';
                if($i>0 && $i%2==1)
                {
                    $vertical_cols = $vertical_cols . '<div class="clearfix"></div>';
                    $evertical_cols = $evertical_cols . '<div class="clearfix"></div>';
                }
                try
                {
                    $formDefination = new FormDefination();
                    $formDefination->table = $request->table;
                    $formDefination->type = $request->{'inputType'.$i};
                    $formDefination->name = str_replace(" ","_",$request->{'field-lbl' . $i});
                    $formDefination->value = $request->{$field_name.'multiselect'};
                    $required = $request->{$field_name.'required'} == ""? " " : 'required' ;
                    $formDefination->required = $required;
                    $formDefination->save();
                }
                catch(\Exception $e)
                {
                    echo $e;
                }
            }
            elseif($request->{'inputType'.$i} == 'email' || $request->{'inputType'.$i} == 'password' || $request->{'inputType'.$i} == 'url')
            {
                $vertical_cols = $vertical_cols .'<div class="form-group col-sm-6"><label class="control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><input type="'. $request->{'inputType'.$i} .'" name="'. $request->{'field'.$i} .'" class="form-control" id="" placeholder="'.$request->{$field_name.'placeholder'}.'" '.$request->{$field_name.'required'}.'></div>';
                $evertical_cols = $evertical_cols .'<div class="form-group col-sm-6"><label class="control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><input type="'. $request->{'inputType'.$i} .'" name="'. $request->{'field'.$i} .'" value = "{{ $data->'.$request->{'field'.$i}.' }}" class="form-control" id="" placeholder="'.$request->{$field_name.'placeholder'}.'" '.$request->{$field_name.'required'}.'></div>';
                $horizontal_cols = $horizontal_cols .'<div class="form-group col-sm-6"> <label class="col-lg-2 control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><div class="col-lg-10"> <input type="'. $request->{'inputType'.$i} .'" name="'. $request->{'field'.$i} .'" id="" class="form-control"  placeholder="'.$request->{$field_name.'placeholder'}.'" '.$request->{$field_name.'required'}.'></div></div>';
                $ehorizontal_cols = $ehorizontal_cols .'<div class="form-group col-sm-6"> <label class="col-lg-2 control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><div class="col-lg-10"> <input type="'. $request->{'inputType'.$i} .'" name="'. $request->{'field'.$i} .'" value = "{{ $data->'.$request->{'field'.$i}.' }}" id="" class="form-control"  placeholder="'.$request->{$field_name.'placeholder'}.'" '.$request->{$field_name.'required'}.'></div></div>';
                $horizontal = $horizontal.'<div class="form-group"> <label class="col-lg-2 control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><div class="col-lg-10"> <input type="'. $request->{'inputType'.$i} .'" id="" class="form-control" name="'. $request->{'field'.$i} .'" placeholder="'.$request->{$field_name.'placeholder'}.'" '.$request->{$field_name.'required'}.'></div></div>';
                $ehorizontal = $ehorizontal.'<div class="form-group"> <label class="col-lg-2 control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><div class="col-lg-10"> <input type="'. $request->{'inputType'.$i} .'" id="" class="form-control" name="'. $request->{'field'.$i} .'" value = "{{ $data->'.$request->{'field'.$i}.' }}" placeholder="'.$request->{$field_name.'placeholder'}.'" '.$request->{$field_name.'required'}.'></div></div>';
                $vertical = $vertical .'<div class="form-group"><label class="control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><input type="'. $request->{'inputType'.$i} .'" class="form-control" id="" name="'. $request->{'field'.$i} .'" placeholder="'.$request->{$field_name.'placeholder'}.'" '.$request->{$field_name.'required'}.'></div>';
                $evertical = $evertical .'<div class="form-group"><label class="control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><input type="'. $request->{'inputType'.$i} .'" class="form-control" id="" name="'. $request->{'field'.$i} .'" value = "{{ $data->'.$request->{'field'.$i}.' }}" placeholder="'.$request->{$field_name.'placeholder'}.'" '.$request->{$field_name.'required'}.'></div>';
                if($i>0 && $i%2==1)
                {
                    $vertical_cols = $vertical_cols . '<div class="clearfix"></div>';
                    $evertical_cols = $evertical_cols . '<div class="clearfix"></div>';
                    $horizontal_cols = $horizontal_cols.'<div class="clearfix"></div>';
                    $ehorizontal_cols = $ehorizontal_cols.'<div class="clearfix"></div>';
                }
                try
                {
                    $formDefination = new FormDefination();
                    $formDefination->table = $request->table;
                    $formDefination->type = $request->{'inputType'.$i};
                    $formDefination->name = str_replace(" ","_",$request->{'field-lbl' . $i});
                    $required = $request->{$field_name.'required'} == ""? " " : 'required' ;
                    $formDefination->required = $required;
                    $formDefination->save();
                }
                catch(\Exception $e)
                {
                    echo $e;
                }
            }
            elseif($request->{'inputType'.$i} == 'none' ){
                try
                {
                    $formDefination = new FormDefination();
                    $formDefination->table = $request->table;
                    $formDefination->type = 'none';
                    $formDefination->name = str_replace(" ","_",$request->{'field-lbl' . $i});
                    $formDefination->save();
                }
                catch(\Exception $e)
                {
                    echo $e;
                }
            }
            else
            {
                $vertical_cols = $vertical_cols .'<div class="form-group col-sm-6"><label class="control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><input type="'. $request->{'inputType'.$i} .'" name="'. $request->{'field'.$i} .'" class="form-control" id="" '.$request->{$field_name.'required'}.'></div>';
                $evertical_cols = $evertical_cols .'<div class="form-group col-sm-6"><label class="control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><input type="'. $request->{'inputType'.$i} .'" name="'. $request->{'field'.$i} .'" value = "{{ $data->'.$request->{'field'.$i}.' }}" class="form-control" id="" '.$request->{$field_name.'required'}.'></div>';
                $horizontal_cols = $horizontal_cols.'<div class="col-sm-6 form-group"> <label class="col-lg-2 control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><div class="col-lg-10"> <input type="'. $request->{'inputType'.$i} .'" name="'. $request->{'field'.$i} .'" id="" class="form-control" '.$request->{$field_name.'required'}.'></div></div>';
                $ehorizontal_cols = $ehorizontal_cols.'<div class="col-sm-6 form-group"> <label class="col-lg-2 control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><div class="col-lg-10"> <input type="'. $request->{'inputType'.$i} .'" name="'. $request->{'field'.$i} .'" id="" value = "{{ $data->'.$request->{'field'.$i}.' }}" class="form-control" '.$request->{$field_name.'required'}.'></div></div>';
                $horizontal = $horizontal.'<div class="form-group"> <label class="col-lg-2 control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><div class="col-lg-10"> <input type="'. $request->{'inputType'.$i} .'" name="'. $request->{'field'.$i} .'" id="" class="form-control" '.$request->{$field_name.'required'}.'></div></div>';
                $ehorizontal = $ehorizontal.'<div class="form-group"> <label class="col-lg-2 control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><div class="col-lg-10"> <input type="'. $request->{'inputType'.$i} .'" name="'. $request->{'field'.$i} .'" id="" value = "{{ $data->'.$request->{'field'.$i}.' }}" class="form-control" '.$request->{$field_name.'required'}.'></div></div>';
                $vertical = $vertical .'<div class="form-group"><label class="control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><input type="'. $request->{'inputType'.$i} .'" class="form-control" id="" name="'. $request->{'field'.$i} .'" '.$request->{$field_name.'required'}.'></div>';
                $evertical = $evertical .'<div class="form-group"><label class="control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><input type="'. $request->{'inputType'.$i} .'" class="form-control" id="" value = "{{ $data->'.$request->{'field'.$i}.' }}" name="'. $request->{'field'.$i} .'" '.$request->{$field_name.'required'}.'></div>';
                if($i>0 && $i%2==1)
                {
                    $vertical_cols = $vertical_cols . '<div class="clearfix"></div>';
                    $evertical_cols = $evertical_cols . '<div class="clearfix"></div>';
                    $horizontal_cols = $horizontal_cols.'<div class="clearfix"></div>';
                    $ehorizontal_cols = $ehorizontal_cols.'<div class="clearfix"></div>';
                }
                try
                {
                    $formDefination = new FormDefination();
                    $formDefination->table = $request->table;
                    $formDefination->type = $request->{'inputType'.$i};
                    $formDefination->name = str_replace(" ","_",$request->{'field-lbl' . $i});
                    $required = $request->{$field_name.'required'} == ""? " " : 'required' ;
                    $formDefination->required = $required;
                    $formDefination->save();
                }
                catch(\Exception $e)
                {
                    echo $e;
                }
            }
        }

        $vertical_cols = $vertical_cols . '<div class="form-group col-sm-6"><div class="col-lg-12"> <input type="reset" value="reset" class="btn btn-default"> <input type="submit" value="submit" class="btn btn-primary"> </div></div><div class="clearfix"></div></form></div></div>';
        $evertical_cols = $evertical_cols . '<div class="form-group col-sm-6"><div class="col-lg-12"> <input type="reset" value="reset" class="btn btn-default"> <input type="submit" value="submit" class="btn btn-primary"> </div></div><div class="clearfix"></div></form></div></div>';
        $horizontal_left = $horizontal_left . $horizontal . '<div class="form-group"><div class="col-lg-10 col-lg-offset-2"> <input type="reset" value="reset" class="btn btn-default"> <input type="submit" value="submit" class="btn btn-primary"> </div></div></fieldset></form></div><div class="col-sm-4 equal" id="extra-div"><div class="row extra" style="height: 100%; width: 98%; margin-left: 1%;"></div></div>';
        $ehorizontal_left = $ehorizontal_left . $ehorizontal . '<div class="form-group"><div class="col-lg-10 col-lg-offset-2"> <input type="reset" value="reset" class="btn btn-default"> <input type="submit" value="submit" class="btn btn-primary"> </div></div></fieldset></form></div><div class="col-sm-4 equal" id="extra-div"><div class="row extra" style="height: 100%; width: 98%; margin-left: 1%;"></div></div>';
        $horizontal_right = $horizontal_right . $horizontal . '<div class="form-group"><div class="col-lg-10 col-lg-offset-2"> <input type="reset" value="reset" class="btn btn-default"> <input type="submit" value="submit" class="btn btn-primary"> </div></div></fieldset></form></div></div>';
        $ehorizontal_right = $ehorizontal_right . $ehorizontal . '<div class="form-group"><div class="col-lg-10 col-lg-offset-2"> <input type="reset" value="reset" class="btn btn-default"> <input type="submit" value="submit" class="btn btn-primary"> </div></div></fieldset></form></div></div>';
        $vertical_left = $vertical_left . $vertical .'<div class="form-group"><div class="col-lg-12"> <input type="reset" value="reset" class="btn btn-default"> <input type="submit" value="submit" class="btn btn-primary"> </div></div></fieldset></form></div><div class="col-sm-4 equal" id="extra-div"><div class="row extra" style="height: 100%; width: 98%; margin-left: 1%;"></div></div></div>';
        $evertical_left = $evertical_left . $evertical .'<div class="form-group"><div class="col-lg-12"> <input type="reset" value="reset" class="btn btn-default"> <input type="submit" value="submit" class="btn btn-primary"> </div></div></fieldset></form></div><div class="col-sm-4 equal" id="extra-div"><div class="row extra" style="height: 100%; width: 98%; margin-left: 1%;"></div></div></div>';
        $vertical_right = $vertical_right . $vertical . '<div class="form-group"><div class="col-lg-12"> <input type="reset" value="reset" class="btn btn-default"> <input type="submit" value="submit" class="btn btn-primary"> </div></div></fieldset></form></div></div>';
        $evertical_right = $evertical_right . $evertical . '<div class="form-group"><div class="col-lg-12"> <input type="reset" value="reset" class="btn btn-default"> <input type="submit" value="submit" class="btn btn-primary"> </div></div></fieldset></form></div></div>';
        $horizontal_center = $horizontal_center . $horizontal .'<div class="form-group"><div class="col-lg-10 col-lg-offset-2"> <input type="reset" value="reset" class="btn btn-default"> <input type="submit" value="submit" class="btn btn-primary"> </div></div></fieldset></form></div></div> ';
        $ehorizontal_center = $ehorizontal_center . $ehorizontal .'<div class="form-group"><div class="col-lg-10 col-lg-offset-2"> <input type="reset" value="reset" class="btn btn-default"> <input type="submit" value="submit" class="btn btn-primary"> </div></div></fieldset></form></div></div> ';
        $vertical_center = $vertical_center . $vertical .'<div class="form-group"><div class="col-lg-12"> <input type="reset" value="reset" class="btn btn-default"> <input type="submit" value="submit" class="btn btn-primary"> </div></div></fieldset></form></div></div>';
        $evertical_center = $evertical_center . $evertical .'<div class="form-group"><div class="col-lg-12"> <input type="reset" value="reset" class="btn btn-default"> <input type="submit" value="submit" class="btn btn-primary"> </div></div></fieldset></form></div></div>';
        $horizontal_cols = $horizontal_cols .'<div class="form-group col-sm-6"><div class="col-lg-10 col-lg-offset-2"> <input type="reset" value="reset" class="btn btn-default"> <input type="submit" value="submit" class="btn btn-primary"> </div></div><div class="clearfix"></div></fieldset></form></div></div>';
        $ehorizontal_cols = $ehorizontal_cols .'<div class="form-group col-sm-6"><div class="col-lg-10 col-lg-offset-2"> <input type="reset" value="reset" class="btn btn-default"> <input type="submit" value="submit" class="btn btn-primary"> </div></div><div class="clearfix"></div></fieldset></form></div></div>';

        $vertical_cols = $vertical_cols . "',";
        $evertical_cols = $evertical_cols . "',";
        $horizontal_left = $horizontal_left . "',";
        $ehorizontal_left = $ehorizontal_left . "',";
        $vertical_left = $vertical_left . "',";
        $evertical_left = $evertical_left . "',";
        $horizontal_right = $horizontal_right . "',";
        $ehorizontal_right = $ehorizontal_right . "',";
        $vertical_right = $vertical_right . "',";
        $evertical_right = $evertical_right . "',";
        $horizontal_center = $horizontal_center . "',";
        $ehorizontal_center = $ehorizontal_center . "',";
        $vertical_center = $vertical_center . "',";
        $evertical_center = $evertical_center . "',";
        $horizontal_cols = $horizontal_cols . "'";
        $ehorizontal_cols = $ehorizontal_cols . "'";

        $formsArray = $formsArray . $vertical_cols . $horizontal_left . $vertical_left . $horizontal_right . $vertical_right . $horizontal_center . $vertical_center . $horizontal_cols.'};';
        $eformsArray = $eformsArray . $evertical_cols . $ehorizontal_left . $evertical_left . $ehorizontal_right . $evertical_right . $ehorizontal_center . $evertical_center . $ehorizontal_cols.'};';

        //        ========================= file handling ============================
        // get content from .js file
        $data = file_get_contents('autocrud/js/custom.js');

        // get specific data from file
        $var_name = 'var ' . $request->table;
        $evar_name = 'var edit_'.$request->table;


        $startsAt = strpos($data, $var_name) + strlen($var_name);
        $endsAt = strpos($data, "};", $startsAt);
        $result = substr($data, $startsAt, $endsAt - $startsAt);
        $result = $var_name .$result . "};";

        $estartsAt = strpos($data, $evar_name) + strlen($evar_name);
        $eendsAt = strpos($data, "};", $estartsAt);
        $eresult = substr($data, $estartsAt, $eendsAt - $estartsAt);
        $eresult = $evar_name .$eresult . "};";

        //remove specific content from variable and store it again in variable
        $data = str_replace($result, '', $data);
        $data = str_replace($eresult, '', $data);

        //add new form details in file at the beginning of file
        $formsArray = $eformsArray . $formsArray . $data;

        //rewrite data to file
        file_put_contents('autocrud/js/custom.js', $formsArray);


        try
        {
            $table = $request->table;
            $is_available = DB::table('page_layouts')
                ->where('table', $table)
                ->first();

            if (!$is_available) {
                $page_layouts = new PageLayout();
                $page_layouts->table = $table;
                $page_layouts->save();
                return view('autocrud::Admin.Single_table.pageLayout', compact('table'));
            }
            else
            {
                return view('autocrud::Admin.Single_table.pageLayout', compact('table','is_available'));
            }
        }
        catch (\Exception $e) {
            echo $e->getMessage();
        }
    }

    public function generateView(Request $request)    {
        //generate directory
        if (!file_exists('../resources/views/user/'.$request->view_name)) {
            mkdir('../resources/views/user/'.$request->view_name, 0777, true);
        }

        $this->generateController($request->view_name,$request->array_name);
        /*===============================================================
        |
        |                   create all views
        |
        ===============================================================*/
        $view_data = $request->view_data;
        $view_data = str_replace('<div class=""><center><h3 class="b-bottom heading">Form Preview</h3></center></div>',"",$view_data);
        // add form action and route
        $view_data = str_replace('<form ','<form method="post" action="{{route('."'".$request->view_name.'.store'."'".')}}"',$view_data);

        // add hidden fields in form such as token and table_name
        $view_data = str_replace('</form>','<input type="hidden" name="_token" value="{{ csrf_token() }}"><input type="hidden" name="table" value="user_infos"></form>',$view_data);

        if($request->form_layout == 'right' || $request->form_layout == 'left')
            $view_data = str_replace('<div class="row extra" style="height: 100%; width: 98%; margin-left: 1%;"></div>',"\n{{-- \n\n\t\t   this is side div, make your own code here : )  \n\n --}}\n",$view_data);

        // add extra div
        $view_data = "@extends('layouts.admin') \n\n\n@section('custom-css')\n<style>\n\n /*write your css here \n\n</style>\n@endsection \n\n\n@section('content')\n".$view_data . "\n@endsection\n\n\n@section('custom-js')\n <script type='text/javascript'>\n\n //add your jquery or javascript code here \n\n </script>\n@endsection";

        file_put_contents('../resources/views/user/'.$request->view_name.'/'.$request->view_name.'.blade.php', $view_data);

        //update table form_layout
        if($request->form_layout == 'left' || $request->form_layout == 'right')
        {
            DB::table('page_layouts')
                ->where('table',$request->array_name)
                ->update(['form_type'=>$request->form_type ,
                    'offset'=>$request->offset ,
                    'form_width'=>$request->form_cols,
                    'side_div'=>$request->extra_cols,
                    'no_of_cols'=>'',
                    'view_name'=>$request->view_name]);
        }
        else if($request->form_layout == 'center')
        {
            DB::table('page_layouts')
                ->where('table',$request->array_name)
                ->update(['form_type'=>$request->form_type,
                    'offset'=>$request->offset,
                    'form_width'=>'',
                    'side_div'=>'',
                    'no_of_cols'=>'',
                    'view_name'=>$request->view_name]);
        }
        else
        {
            DB::table('page_layouts')
                ->where('table',$request->array_name)
                ->update(['form_type'=>$request->form_type,
                    'offset'=>$request->offset,
                    'form_width'=>'',
                    'side_div'=>'',
                    'no_of_cols'=>$request->no_of_cols,
                    'view_name'=>$request->view_name]);
        }




        /*
         *      Generate edit code...
         */
        $view_data = $request->edit_view_data;
        $view_data = str_replace('<div class=""><center><h3 class="b-bottom heading">Form Preview</h3></center></div>',"",$view_data);
        // add form action and route
        $view_data = str_replace('<form ','<form method="post" action="{{url('."'".$request->view_name.'/'."'".'.$id)}}"',$view_data);

        // add hidden fields in form such as token and table_name
        $view_data = str_replace('</form>','<input type="hidden" name="table" value="'.$request->array_name.'"><input type="hidden" name="_method" value="PUT"><input type="hidden" name="_token" value="{{ csrf_token() }}"></form>',$view_data);

        if($request->form_layout ==  'right' || $request->form_layout == 'left')
            $view_data = str_replace('<div class="row extra" style="height: 100%; width: 98%; margin-left: 1%;"></div>',"\n{{-- \n\n\t\t   this is side div, make your own code here : )  \n\n --}}\n",$view_data);

        // add extra div
        $view_data = "@extends('layouts.admin') \n\n\n@section('custom-css')\n<style>\n\n /*write your css here \n\n</style>\n@endsection \n\n\n@section('content')\n".$view_data . "\n@endsection\n\n\n@section('custom-js')\n <script type='text/javascript'>\n\n //add your jquery or javascript code here \n\n </script>\n@endsection";

        file_put_contents('../resources/views/user/'.$request->view_name.'/edit_'.$request->view_name.'.blade.php', $view_data);




        /*
         *        Generate details page code...
         */

        //get fields of table to display...
        $fields = DB::table('form_definations')
            ->where('table',$request->array_name)
            ->get(['name','type']);
        $start = "@extends('layouts.admin') \n\n\n@section('custom-css')\n<style>\n\n /*write your css here*/ \n\n</style>\n@endsection \n\n\n@section('content')\n".'<div class="container-fluid"><div><legend class="heading">'. str_replace("_"," ",$request->array_name) .' details</legend></div><form class="table-form" id="'.$request->array_name.'_details"><div class="col-sm-8">{{ $data->links() }}</div><div class="col-sm-4"><ul class="nav navbar-nav pull-right"><li><input type="button" value="view" class="view_btn btn btn-success"></li><li><input type="button" value="edit" class="edit_btn btn btn-warning"></li><li><input type="button" value="delete" class="delete_btn btn btn-danger"></li></ul></div>';

        $columns = Schema::getColumnListing($request->array_name);
        $columns = array_diff($columns, array('id', 'created_at', 'updated_at'));

        //generate table...
        $table = '<table class="table table-hover dt"><thead><tr><th>select</th>';
        $i = 1;
        foreach($fields as $item)
        {
            if($item->type != 'none')
                $table .= '<th>'.str_replace("_"," ",$item->name).'</th>';
        }
        $table.='</tr></thead><tbody>@foreach($data as $item)<tr><td><input type="checkbox" name="id[]" class="chkbx" value="{{ $item->id }}"></td>';
        foreach($fields as $item)
        {
            if($item->type != 'none')
                $table .= '<td>{{ $item->'. $columns[$i++] .' }}</td>';
            else
                $i++;
        }
        $table .= '</tr>@endforeach</tbody></table>';
        //end code...
        $end = '<input type="hidden" name="_token" value="{{ csrf_token() }}"><input type="hidden" id="route_name" value="'. $request->view_name .'" ><div class="method_type"></div></form></div>'."\n@endsection\n\n\n@section('custom-js')\n <script type='text/javascript'>\n\n //add your jquery or javascript code here \n\n </script>\n@endsection";

        file_put_contents('../resources/views/user/'.$request->view_name.'/'.$request->view_name.'_details.blade.php', $start.$table.$end);



        /*
         *      Generate show page code...
         */
        $start = "@extends('layouts.admin') \n\n\n@section('custom-css')\n<style>\n\n /*write your css here*/ \n\n</style>\n@endsection \n\n\n@section('content')\n".'<div class="container-fluid"><div><legend class="heading">'. str_replace("_"," ",$request->array_name) .' details</legend></div><div class="col-sm-offset-3 col-sm-6 shadow"><center>';

        //generate table...
        $table = '<table class="table table-hover td"><thead><tr> <th>Field</th><th>Value</th></tr></thead><tbody>';

        $i=1;
        foreach($fields as $item)
        {
            if($item->type != 'none')
                $table .= '<tr><td>'.$item->name.'</td><td>{{ $data->'.$columns[$i++].' }}</td></tr>';
            else
                $i++;
        }
        $table .= '</tbody></table>';

        //end code...
        $end = '</center></div></div>'."\n@endsection\n\n\n@section('custom-js')\n <script type='text/javascript'>\n\n //add your jquery or javascript code here \n\n </script>\n@endsection";

        file_put_contents('../resources/views/user/'.$request->view_name.'/'.$request->view_name.'_show.blade.php', $start.$table.$end);

        return view('autocrud::Admin.done');}

    protected function generateController($view_name,$array_name)
    {
        //create Route
        $data = file_get_contents('../routes/web.php');
        if(!strpos($data,'Route::resource("'. $view_name .'","'. $view_name .'Controller");'))
        {
            file_put_contents('../routes/web.php',$data."\n".'Route::resource("'. $view_name .'","'. $view_name .'Controller");');
        }

        // get all the fields of table
        $fields = DB::table('form_definations')
            ->where('table',$array_name)
            ->get(['type']);

        $columns = Schema::getColumnListing($array_name);
        $columns = array_diff($columns, array('id', 'created_at', 'updated_at'));

        $head = "<?php\n\nnamespace App\Http\Controllers;\n\nuse App\UserInfo;\nuse Illuminate\Http\Request;\nuse Illuminate\Support\Facades\DB;\nuse Illuminate\Support\Facades\Input;\nclass ". $view_name ."Controller extends Controller\n{";

        $index = "\n\n\t//Display a listing of the resource.\n\tpublic function index()\n\t{\n\t\t".'$data = DB::table("'.$array_name.'")->paginate(300);'."\n\t\t".'return view("user.'.$view_name.'.'.$view_name.'_details",compact("data"));'."\n\t}";

        $create = "\n\n\t//Display a listing of the resource.\n\tpublic function create()\n\t{\n\t\t".'return view("user.'.$view_name.'.'.$view_name.'");'."\n\t}";

        //insert query
        $insert= "";
        $i=1;
        foreach($fields as $field)
        {
            if($field->type == 'multiselect')
            {
                $insert.="\n\t\t\t\t".'"'.$columns[$i].'" => implode("," , $request->'.$columns[$i++].'),';
            }
            elseif($field->type == 'date')
            {
                $insert.="\n\t\t\t\t".'"'.$columns[$i].'" => date("Y-m-d",strtotime($request->'.$columns[$i++].')),';
            }
            elseif($field->type == 'none')
            {
                $i++;
            }
            else
            {
                $insert.="\n\t\t\t\t".'"'.$columns[$i].'" => $request->'.$columns[$i++].',';
            }
        }
        $insert = substr($insert, 0, -1);
        $insert .= "\n\t\t\t]\n\t\t);";
        $store = "\n\n\t// Store a newly created resource in storage.\n\tpublic function store(Request ".'$request'.")\n\t{"."\n\t\t DB::table('".$array_name."')->insert(\n\t\t\t[".$insert."\n\t\t".'return redirect(route("'.$view_name.'.create"));'."\n\t}";

        $show = "\n\n\t// Display the specified resource.\n\tpublic function show(".'$id'.")\n\t{\n\t\t".'$data = DB::table("'.$array_name.'")'."\n\t\t\t".'->find($id);'."\n\t\t".'return view("user.'.$view_name.'.'.$view_name.'_show",compact("data"));'."\n\t}";

        $edit = "\n\n\t// Show the form for editing the specified resource.\n\tpublic function edit(".'$id'.")\n\t{\n\t\t".'$data = DB::table("'.$array_name.'")'."\n\t\t\t".'->find($id);'."\n\t\t".'return view("user.'.$view_name.'.edit_'.$view_name.'",compact("data","id"));'."\n\t}";

        $update = "\n\n\t// Store a newly created resource in storage.\n\tpublic function update(Request ".'$request , $id'.")\n\t{\n\t\t DB::table('".$array_name."')\n\t\t\t->where('id',".'$id'.")->update(\n\t\t\t[".$insert."\n\t\t".'return redirect(route("'.$view_name.'.index"));'."\n\t}";

        $delete = "\n\n\t// Remove the specified resource from storage.\n\tpublic function destroy(".'$id'.")\n\t{\n\t\t DB::table('".$array_name."')\n\t\t\t->where('id',".'$id'.")->delete();\n\t\t".'return redirect(route("'.$view_name.'.index"));'."\n\t}";

        $end = "\n\n}";
        file_put_contents('../app/Http/Controllers/'. $view_name .'Controller.php', $head . $index . $create . $store . $show . $edit . $update . $delete . $end );
    }

    public function getColsList(Request $request)
    {
        $table = $request->table;
        $columns = Schema::getColumnListing($table);
        return $columns;
    }

    public function getJoinFields(Request $request)
    {
        $fields = DB::table('join_tables')
            ->where('view_name',$request->refid)
            ->get(['field_name','table_name']);

        return $fields;
    }

    public function sortTableFields($refno)
    {
        $data = Cache::get('extra_data');
        if(!empty($data))
        {
            $i = 0;
            foreach($data as $item)
            {
                $t = explode("*-*",$item);
                if(trim($t[1]) == "")
                    unset($data[$i]);
                $i++;
            }
        }
        $fields = DB::table('join_tables')
            ->where('view_name',$refno)
            ->get(['field_name','table_name']);
        return view('autocrud::Admin.Single_table.sortTableFields',compact('fields','data','refno'));
    }
}
