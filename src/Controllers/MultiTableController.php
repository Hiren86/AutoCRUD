<?php

namespace Hiren\Autocrud\Controllers;

use App\JoinTable;
use Carbon\Carbon;
use App\PageLayout;
use App\CommonField;
use App\FormDefination;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Schema;

class MultiTableController extends Controller
{

    //public function __construct()
    //{
       //$this->middleware('auth', ['except' => ['index','generateTable','commonFields','mStoreCommon','mRemoveDuplicateAndSort','mformFields','mCreateFormFields','mDesignForm','mCreateLayout','mGenerateView','generateController']]);
    //}

    // dispay view of tables selection for multiple table CRUD
    public function index()
    {
        if (DB::connection()->getDatabaseName()) {
            $dbname = "Tables_in_" . DB::connection()->getDatabaseName();
        }
        $tables = DB::select('SHOW TABLES');
        $table_list = array();

        foreach ($tables as $table)
            array_push($table_list, $table->$dbname);

        $table_list = array_diff($table_list, array('users', 'migrations', 'password_resets','form_definations','page_layouts','common_fields','join_tables'));

        $size = sizeof($table_list) - 1;
        $options = '';
        foreach($table_list as $item)
            $options .= "<option value=".$item.">".$item."</option>";
        return view('autocrud::Admin.Multi_tables.mSelectTable',compact('table_list','size','options'));
    }

    public function generateTable(Request $request)
    {
        if (!DB::table('join_tables')->where('view_name',$request->refid)->exists())
        {
            $fields = Input::get('fields');

            foreach($fields as $field)
            {
                $field = explode(" ",$field);
                $join_table = new JoinTable();
                $join_table->table_name = $field[1];
                $join_table->field_name = $field[0];
                $join_table->view_name = $request->refid;
                $join_table->union_ref_id = '';
                $join_table->save();
            }
        }

        $refno = $request->refid;
        return redirect()->route('mCommonFields',$refno);
    }

    public function commonFields($refno)
    {
        $n = DB::table('join_tables')
            ->select('table_name')
            ->groupBy('table_name')
            ->orderBy('id')
            ->where('view_name',$refno)
            ->get();
        $data = array();
        $i = 0;
        foreach($n as $item)
        {
            ${"data" + $i} = DB::table('join_tables')
                ->where('table_name',$item->table_name)
                ->where('view_name',$refno)
                ->get(['field_name','table_name']);

            array_push($data,${"data" + $i++});
        }
        Cache::put('refno',$refno,Carbon::now()->addMinutes(360));
        return view('autocrud::Admin.Multi_tables.mCommonFields',compact('data'));
    }

    public function mStoreCommon(Request $request)
    {
        $n = $request->no_of_table;
        $fields_array = array_chunk($request->cmn , $n);
        $refno = Cache::get('refno');

        //remove old data
        DB::table('common_fields')
            ->where('refid',$refno)
            ->delete();

        //store common fields as per user input
        foreach($fields_array as $fields)
        {
            $comman_field =  new CommonField();
            $flg = 0;
            foreach($fields as $field)
            {
                if($field)
                {
                    if($flg == 0)
                    {
                        $field = explode("~",$field);
                        $comman_field->refid = $refno;
                        $comman_field->table1 = $field[0];
                        $comman_field->field1 = $field[1];
                        $flg = 1;
                    }
                    else
                    {
                        $field = explode("~",$field);
                        $comman_field->table2 = $field[0];
                        $comman_field->field2 = $field[1];
                    }
                }
            }
            $comman_field->save();
        }

        return redirect()->route('mRemoveDuplicateAndSort');
    }

    public function mRemoveDuplicateAndSort()
    {
        $refno = Cache::get('refno');
        $fields = DB::table('join_tables')
            ->select('table_name','field_name')
            ->where('view_name',$refno)
            ->get();

        return view('autocrud::Admin.Multi_tables.mremoveDuplicateAndSort',compact('fields','refno'));
    }

    public function mformFields(Request $request)
    {
        try
        {
            if (Cache::get('refno'))
            {
                $columns = $request->order;
                $refno = $request->refno;

                // udpdate refid in common filds table.
                DB::table('common_fields')
                    ->where('refid',$refno)
                    ->update(['refid'=>$request->name_of_view]);

                $i = 1;
                foreach($columns as $item)
                {
                    $item = explode('~',$item);
                    DB::table('join_tables')
                        ->where(['view_name'=>$refno])
                        ->update(['view_name'=>$request->name_of_view]);

                    DB::table('join_tables')
                        ->where(['view_name'=>$request->name_of_view ,'field_name'=>$item[0] , 'table_name'=>$item[1]])
                        ->update(['is_unique'=>1]);
                }
                return $this->mCreateFormFields($request->name_of_view);
            }
        }
        catch(Exception $e)
        {
            echo $e;
        }
    }

    private function mCreateFormFields($name_of_view)
    {
        $columns_list = DB::table('join_tables')
            ->select('field_name','table_name')
            ->where('view_name',$name_of_view)
            ->where('is_unique',1)
            ->get();

        $columns = array();
        foreach($columns_list as $item)
        {
            array_push($columns,$item->table_name . "~" . $item->field_name  );
        }

        $table = $name_of_view;
        return view('autocrud::Admin.Multi_tables.mCreateFormField',compact('table','columns'));
    }

    public function mDesignForm(Request $request)
    {
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
            $t = explode("~",$request->{'field'.$i});
            if($request->{'inputType'.$i} == 'text')
            {
                if(trim($request->{$field_name.'pattern'}," ") == "")
                {
                    $vertical_cols = $vertical_cols .'<div class="form-group col-sm-6"><label class="control-label capitalize capitalize">'.$request->{'field-lbl'.$i}.'</label><input type="text" class="form-control" name="'. $t[1] .'" id="" placeholder="'.$request->{$field_name.'placeholder'}.'" maxlength="'.$request->{$field_name.'maxlength'}.'" minlength="'.$request->{$field_name.'minlength'}.'" '.$request->{$field_name.'required'}.'></div>';
                    $evertical_cols = $evertical_cols .'<div class="form-group col-sm-6"><label class="control-label capitalize capitalize">'.$request->{'field-lbl'.$i}.'</label><input type="text" class="form-control" name="'. $t[1] .'" value = "{{ $data->'.$t[1].' }}" id="" placeholder="'.$request->{$field_name.'placeholder'}.'" maxlength="'.$request->{$field_name.'maxlength'}.'" minlength="'.$request->{$field_name.'minlength'}.'" '.$request->{$field_name.'required'}.'></div>';
                    $horizontal_cols = $horizontal_cols . '<div class="form-group col-sm-6"> <label class="col-lg-2 control-label capitalize capitalize">'.$request->{'field-lbl'.$i}.'</label><div class="col-lg-10"> <input type="text" id="" name="'. $t[1] .'" class="form-control"  placeholder="'.$request->{$field_name.'placeholder'}.'" maxlength="'.$request->{$field_name.'maxlength'}.'" minlength="'.$request->{$field_name.'minlength'}.'" '.$request->{$field_name.'required'}.'></div></div>';
                    $ehorizontal_cols = $ehorizontal_cols . '<div class="form-group col-sm-6"> <label class="col-lg-2 control-label capitalize capitalize">'.$request->{'field-lbl'.$i}.'</label><div class="col-lg-10"> <input type="text" id="" name="'. $t[1] .'" class="form-control" value = "{{ $data->'.$t[1].' }}" placeholder="'.$request->{$field_name.'placeholder'}.'" maxlength="'.$request->{$field_name.'maxlength'}.'" minlength="'.$request->{$field_name.'minlength'}.'" '.$request->{$field_name.'required'}.'></div></div>';
                    $horizontal = $horizontal . '<div class="form-group"> <label class="col-lg-2 control-label capitalize capitalize">'.$request->{'field-lbl'.$i}.'</label><div class="col-lg-10"> <input type="text" id="" class="form-control" name="'. $t[1] .'" placeholder="'.$request->{$field_name.'placeholder'}.'" maxlength="'.$request->{$field_name.'maxlength'}.'" minlength="'.$request->{$field_name.'minlength'}.'" '.$request->{$field_name.'required'}.'></div></div>';
                    $ehorizontal = $ehorizontal . '<div class="form-group"> <label class="col-lg-2 control-label capitalize capitalize">'.$request->{'field-lbl'.$i}.'</label><div class="col-lg-10"> <input type="text" id="" class="form-control" name="'. $t[1] .'" placeholder="'.$request->{$field_name.'placeholder'}.'" value = "{{ $data->'.$t[1].' }}" maxlength="'.$request->{$field_name.'maxlength'}.'" minlength="'.$request->{$field_name.'minlength'}.'" '.$request->{$field_name.'required'}.'></div></div>';
                    $vertical = $vertical . '<div class="form-group"><label class="control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><input type="text" class="form-control" id="" name="'. $t[1] .'" placeholder="'.$request->{$field_name.'placeholder'}.'" maxlength="'.$request->{$field_name.'maxlength'}.'" minlength="'.$request->{$field_name.'minlength'}. '" '.$request->{$field_name.'required'}.'></div>';
                    $evertical = $evertical . '<div class="form-group"><label class="control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><input type="text" class="form-control" id="" value = "{{ $data->'.$t[1].' }}" name="'. $t[1] .'" placeholder="'.$request->{$field_name.'placeholder'}.'" maxlength="'.$request->{$field_name.'maxlength'}.'" minlength="'.$request->{$field_name.'minlength'}. '" '.$request->{$field_name.'required'}.'></div>';
                }
                else
                {
                    $vertical_cols = $vertical_cols .'<div class="form-group col-sm-6"><label class="control-label capitalize capitalize">'.$request->{'field-lbl'.$i}.'</label><input type="text" class="form-control" name="'. $t[1] .'" id="" placeholder="'.$request->{$field_name.'placeholder'}.'" maxlength="'.$request->{$field_name.'maxlength'}.'" minlength="'.$request->{$field_name.'minlength'}.'" pattern="'.$request->{$field_name.'pattern'}.'" '.$request->{$field_name.'required'}.'></div>';
                    $evertical_cols = $evertical_cols .'<div class="form-group col-sm-6"><label class="control-label capitalize capitalize">'.$request->{'field-lbl'.$i}.'</label><input type="text" value = "{{ $data->'.$t[1].' }}" class="form-control" name="'. $t[1] .'" id="" placeholder="'.$request->{$field_name.'placeholder'}.'" maxlength="'.$request->{$field_name.'maxlength'}.'" minlength="'.$request->{$field_name.'minlength'}.'" pattern="'.$request->{$field_name.'pattern'}.'" '.$request->{$field_name.'required'}.'></div>';
                    $horizontal_cols = $horizontal_cols . '<div class="form-group col-sm-6"> <label class="col-lg-2 control-label capitalize capitalize">'.$request->{'field-lbl'.$i}.'</label><div class="col-lg-10"> <input type="text" id="" name="'. $t[1] .'" class="form-control"  placeholder="'.$request->{$field_name.'placeholder'}.'" maxlength="'.$request->{$field_name.'maxlength'}.'" minlength="'.$request->{$field_name.'minlength'}.'" pattern="'.$request->{$field_name.'pattern'}.'" '.$request->{$field_name.'required'}.'></div></div>';
                    $ehorizontal_cols = $ehorizontal_cols . '<div class="form-group col-sm-6"> <label class="col-lg-2 control-label capitalize capitalize">'.$request->{'field-lbl'.$i}.'</label><div class="col-lg-10"> <input type="text" id="" name="'. $t[1] .'" value = "{{ $data->'.$t[1].' }}" class="form-control"  placeholder="'.$request->{$field_name.'placeholder'}.'" maxlength="'.$request->{$field_name.'maxlength'}.'" minlength="'.$request->{$field_name.'minlength'}.'" pattern="'.$request->{$field_name.'pattern'}.'" '.$request->{$field_name.'required'}.'></div></div>';
                    $horizontal = $horizontal . '<div class="form-group"> <label class="col-lg-2 control-label capitalize capitalize">'.$request->{'field-lbl'.$i}.'</label><div class="col-lg-10"> <input type="text" id="" class="form-control" name="'. $t[1] .'" placeholder="'.$request->{$field_name.'placeholder'}.'" maxlength="'.$request->{$field_name.'maxlength'}.'" minlength="'.$request->{$field_name.'minlength'}.'" pattern="'.$request->{$field_name.'pattern'}.'" '.$request->{$field_name.'required'}.'></div></div>';
                    $ehorizontal = $ehorizontal . '<div class="form-group"> <label class="col-lg-2 control-label capitalize capitalize">'.$request->{'field-lbl'.$i}.'</label><div class="col-lg-10"> <input type="text" id="" class="form-control" name="'. $t[1] .'" value = "{{ $data->'.$t[1].' }}" placeholder="'.$request->{$field_name.'placeholder'}.'" maxlength="'.$request->{$field_name.'maxlength'}.'" minlength="'.$request->{$field_name.'minlength'}.'" pattern="'.$request->{$field_name.'pattern'}.'" '.$request->{$field_name.'required'}.'></div></div>';
                    $vertical = $vertical . '<div class="form-group"><label class="control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><input type="text" class="form-control" id="" name="'. $t[1] .'" placeholder="'.$request->{$field_name.'placeholder'}.'" maxlength="'.$request->{$field_name.'maxlength'}.'" minlength="'.$request->{$field_name.'minlength'}.'" pattern="'.$request->{$field_name.'pattern'}.'" '.$request->{$field_name.'required'}.'></div>';
                    $evertical = $evertical . '<div class="form-group"><label class="control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><input type="text" value = "{{ $data->'.$t[1].' }}" class="form-control" id="" name="'. $t[1] .'" placeholder="'.$request->{$field_name.'placeholder'}.'" maxlength="'.$request->{$field_name.'maxlength'}.'" minlength="'.$request->{$field_name.'minlength'}.'" pattern="'.$request->{$field_name.'pattern'}.'" '.$request->{$field_name.'required'}.'></div>';
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
                    $formDefination->name = str_replace(" ","_",$field_name . "~" . $request->{'field-lbl' . $i});
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
                $vertical_cols = $vertical_cols .'<div class="form-group col-sm-6"><label class="control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><input type="number" class="form-control" id="" name="'. $t[1] .'" placeholder="'.$request->{$field_name.'placeholder'}.'" max="'.$request->{$field_name.'maxlength'}.'" min="'.$request->{$field_name.'minlength'}.'" step="'.$request->{$field_name.'step'}.'" '.$request->{$field_name.'required'}.'></div>';
                $evertical_cols = $evertical_cols .'<div class="form-group col-sm-6"><label class="control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><input type="number" class="form-control" id="" name="'. $t[1] .'" value = "{{ $data->'.$t[1].' }}" placeholder="'.$request->{$field_name.'placeholder'}.'" max="'.$request->{$field_name.'maxlength'}.'" min="'.$request->{$field_name.'minlength'}.'" step="'.$request->{$field_name.'step'}.'" '.$request->{$field_name.'required'}.'></div>';
                $horizontal_cols = $horizontal_cols.'<div class="form-group col-sm-6"> <label class="col-lg-2 control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><div class="col-lg-10"> <input type="number" name="'. $t[1] .'" id="" class="form-control"  placeholder="'.$request->{$field_name.'placeholder'}.'" max="'.$request->{$field_name.'maxlength'}.'" min="'.$request->{$field_name.'minlength'}.'" step="'.$request->{$field_name.'step'}.'" '.$request->{$field_name.'required'}.'></div></div>';
                $ehorizontal_cols = $ehorizontal_cols.'<div class="form-group col-sm-6"> <label class="col-lg-2 control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><div class="col-lg-10"> <input type="number" name="'. $t[1] .'" value = "{{ $data->'.$t[1].' }}" id="" class="form-control"  placeholder="'.$request->{$field_name.'placeholder'}.'" max="'.$request->{$field_name.'maxlength'}.'" min="'.$request->{$field_name.'minlength'}.'" step="'.$request->{$field_name.'step'}.'" '.$request->{$field_name.'required'}.'></div></div>';
                $horizontal = $horizontal.'<div class="form-group"> <label class="col-lg-2 control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><div class="col-lg-10"> <input type="number" id="" class="form-control" name="'. $t[1] .'" placeholder="'.$request->{$field_name.'placeholder'}.'" max="'.$request->{$field_name.'maxlength'}.'" min="'.$request->{$field_name.'minlength'}.'" step="'.$request->{$field_name.'step'}.'" '.$request->{$field_name.'required'}.'></div></div>';
                $ehorizontal = $ehorizontal.'<div class="form-group"> <label class="col-lg-2 control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><div class="col-lg-10"> <input type="number" id="" class="form-control" value = "{{ $data->'.$t[1].' }}" name="'. $t[1] .'" placeholder="'.$request->{$field_name.'placeholder'}.'" max="'.$request->{$field_name.'maxlength'}.'" min="'.$request->{$field_name.'minlength'}.'" step="'.$request->{$field_name.'step'}.'" '.$request->{$field_name.'required'}.'></div></div>';
                $vertical = $vertical .'<div class="form-group"><label class="control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><input type="number" class="form-control" id="" name="'. $t[1] .'" placeholder="'.$request->{$field_name.'placeholder'}.'" max="'.$request->{$field_name.'maxlength'}.'" min="'.$request->{$field_name.'minlength'}.'" step="'.$request->{$field_name.'step'}.'" '.$request->{$field_name.'required'}.'></div>';
                $evertical = $evertical .'<div class="form-group"><label class="control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><input type="number" class="form-control" id="" name="'. $t[1] .'" value = "{{ $data->'.$t[1].' }}" placeholder="'.$request->{$field_name.'placeholder'}.'" max="'.$request->{$field_name.'maxlength'}.'" min="'.$request->{$field_name.'minlength'}.'" step="'.$request->{$field_name.'step'}.'" '.$request->{$field_name.'required'}.'></div>';

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
                    $formDefination->name = str_replace(" ","_",$field_name . "~" . $request->{'field-lbl' . $i});
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
                    $vertical_cols_options = $vertical_cols_options . '<div class="radio"><label><input type="radio" name="'. $t[1] .'"  value="'.$item.'">'.$item.'</label></div>';
                    $evertical_cols_options = $evertical_cols_options . '<div class="radio"><label><input type="radio" name="'.$t[1] .'"  value="'.$item.'" {{ $data->'.$request->{'field'.$i}.' == "'.$item.'" ? "checked" : "" }}>'.$item.'</label></div>';
                    $horizontal_options = $horizontal_options . '<div class="radio"> <label> <input type="radio" name="'. $t[1].'" id="" value="'.$item.'">'.$item.'</label></div>';
                    $ehorizontal_options = $ehorizontal_options . '<div class="radio"> <label> <input type="radio" name="'. $t[1] .'" id="" value="'.$item.'" {{ $data->'.$request->{'field'.$i}.' == "'.$item.'" ? "checked" : "" }}>'.$item.'</label></div>';
                    $vertical_options = $vertical_options . '<div class="radio"><label><input type="radio" name="'. $t[1] .'" value="'.$item.'">'.$item.'</label></div>';
                    $evertical_options = $evertical_options . '<div class="radio"><label><input type="radio" name="'. $t[1] .'" value="'.$item.'"  {{ $data->'.$request->{'field'.$i}.' == "'.$item.'" ? "checked" : "" }}>'.$item.'</label></div>';
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
                    $formDefination->name = str_replace(" ","_",$field_name . "~" . $request->{'field-lbl' . $i});
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
                $vertical_cols = $vertical_cols .'<div class="form-group col-sm-6"><label class="control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><input type="range" class="form-control" id="" name="'. $t[1] .'" value="'.$request->{$field_name.'value'}.'" max="'.$request->{$field_name.'maxlength'}.'" min="'.$request->{$field_name.'minlength'}.'" step="'.$request->{$field_name.'step'}.'" '.$request->{$field_name.'required'}.'></div>';
                $evertical_cols = $evertical_cols .'<div class="form-group col-sm-6"><label class="control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><input type="range" class="form-control" id="" name="'. $t[1] .'" value="'.$request->{$field_name.'value'}.'" max="'.$request->{$field_name.'maxlength'}.'" min="'.$request->{$field_name.'minlength'}.'" step="'.$request->{$field_name.'step'}.'" '.$request->{$field_name.'required'}.'></div>';
                $horizontal_cols = $horizontal_cols.'<div class="form-group col-sm-6"> <label class="col-lg-2 control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><div class="col-lg-10"> <input type="range" name="'. $t[1] .'" id="" class="form-control"  value="'.$request->{$field_name.'value'}.'" max="'.$request->{$field_name.'maxlength'}.'" min="'.$request->{$field_name.'minlength'}.'" step="'.$request->{$field_name.'step'}.'" '.$request->{$field_name.'required'}.'></div></div>';
                $ehorizontal_cols = $ehorizontal_cols.'<div class="form-group col-sm-6"> <label class="col-lg-2 control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><div class="col-lg-10"> <input type="range" name="'. $t[1] .'" id="" class="form-control"  value="'.$request->{$field_name.'value'}.'" max="'.$request->{$field_name.'maxlength'}.'" min="'.$request->{$field_name.'minlength'}.'" step="'.$request->{$field_name.'step'}.'" '.$request->{$field_name.'required'}.'></div></div>';
                $horizontal = $horizontal.'<div class="form-group"> <label class="col-lg-2 control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><div class="col-lg-10"> <input type="range" id="" class="form-control" name="'. $t[1] .'" value="'.$request->{$field_name.'value'}.'" max="'.$request->{$field_name.'maxlength'}.'" min="'.$request->{$field_name.'minlength'}.'" step="'.$request->{$field_name.'step'}.'" '.$request->{$field_name.'required'}.'></div></div>';
                $ehorizontal = $ehorizontal.'<div class="form-group"> <label class="col-lg-2 control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><div class="col-lg-10"> <input type="range" id="" class="form-control" name="'. $t[1] .'" value="'.$request->{$field_name.'value'}.'" max="'.$request->{$field_name.'maxlength'}.'" min="'.$request->{$field_name.'minlength'}.'" step="'.$request->{$field_name.'step'}.'" '.$request->{$field_name.'required'}.'></div></div>';
                $vertical = $vertical .'<div class="form-group"><label class="control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><input type="range" class="form-control" id="" value="'.$request->{$field_name.'value'}.'" name="'. $t[1] .'" max="'.$request->{$field_name.'maxlength'}.'" min="'.$request->{$field_name.'minlength'}.'" step="'.$request->{$field_name.'step'}.'" '.$request->{$field_name.'required'}.'></div>';
                $evertical = $evertical .'<div class="form-group"><label class="control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><input type="range" class="form-control" id="" value="'.$request->{$field_name.'value'}.'" name="'. $t[1] .'" max="'.$request->{$field_name.'maxlength'}.'" min="'.$request->{$field_name.'minlength'}.'" step="'.$request->{$field_name.'step'}.'" '.$request->{$field_name.'required'}.'></div>';
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
                    $formDefination->name = str_replace(" ","_",$field_name . "~" . $request->{'field-lbl' . $i});
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
                $vertical_cols = $vertical_cols .'<div class="form-group col-sm-6"><label class="control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><textarea name="'. $t[1] .'" class="form-control" id="" placeholder="'.$request->{$field_name.'placeholder'}.'" maxlength="'.$request->{$field_name.'maxlength'}.'" minlength="'.$request->{$field_name.'minlength'}.'" rows="'.$request->{$field_name.'rows'}.'" '.$request->{$field_name.'required'}.'></textarea></div>';
                $evertical_cols = $evertical_cols .'<div class="form-group col-sm-6"><label class="control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><textarea value = "{{ $data->'.$t[1].' }}" name="'. $t[1] .'" class="form-control" id="" placeholder="'.$request->{$field_name.'placeholder'}.'" maxlength="'.$request->{$field_name.'maxlength'}.'" minlength="'.$request->{$field_name.'minlength'}.'" rows="'.$request->{$field_name.'rows'}.'" '.$request->{$field_name.'required'}.'></textarea></div>';
                $horizontal_cols = $horizontal_cols.'<div class="form-group col-sm-6"> <label class="col-lg-2 control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><div class="col-lg-10"> <textarea name="'. $t[1] .'" id="" class="form-control"  placeholder="'.$request->{$field_name.'placeholder'}.'" maxlength="'.$request->{$field_name.'maxlength'}.'" minlength="'.$request->{$field_name.'minlength'}.'" rows="'.$request->{$field_name.'rows'}.'" '.$request->{$field_name.'required'}.'></textarea></div></div>';
                $ehorizontal_cols = $ehorizontal_cols.'<div class="form-group col-sm-6"> <label class="col-lg-2 control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><div class="col-lg-10"> <textarea name="'. $t[1] .'" id="" class="form-control" value = "{{ $data->'.$t[1].' }}"  placeholder="'.$request->{$field_name.'placeholder'}.'" maxlength="'.$request->{$field_name.'maxlength'}.'" minlength="'.$request->{$field_name.'minlength'}.'" rows="'.$request->{$field_name.'rows'}.'" '.$request->{$field_name.'required'}.'></textarea></div></div>';
                $horizontal = $horizontal.'<div class="form-group"> <label class="col-lg-2 control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><div class="col-lg-10"> <textarea id="" class="form-control" name="'. $t[1] .'" placeholder="'.$request->{$field_name.'placeholder'}.'" maxlength="'.$request->{$field_name.'maxlength'}.'" minlength="'.$request->{$field_name.'minlength'}.'" rows="'.$request->{$field_name.'rows'}.'" '.$request->{$field_name.'required'}.'></textarea></div></div>';
                $ehorizontal = $ehorizontal.'<div class="form-group"> <label class="col-lg-2 control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><div class="col-lg-10"> <textarea id="" class="form-control" name="'. $t[1] .'" value = "{{ $data->'.$t[1].' }}" placeholder="'.$request->{$field_name.'placeholder'}.'" maxlength="'.$request->{$field_name.'maxlength'}.'" minlength="'.$request->{$field_name.'minlength'}.'" rows="'.$request->{$field_name.'rows'}.'" '.$request->{$field_name.'required'}.'></textarea></div></div>';
                $vertical = $vertical .'<div class="form-group"><label class="control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><textarea class="form-control" id="" placeholder="'.$request->{$field_name.'placeholder'}.'" name="'. $t[1] .'" maxlength="'.$request->{$field_name.'maxlength'}.'" minlength="'.$request->{$field_name.'minlength'}.'" rows="'.$request->{$field_name.'rows'}.'" '.$request->{$field_name.'required'}.'></textarea></div>';
                $evertical = $evertical .'<div class="form-group"><label class="control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><textarea class="form-control" id="" placeholder="'.$request->{$field_name.'placeholder'}.'" value = "{{ $data->'.$t[1].' }}" name="'. $t[1] .'" maxlength="'.$request->{$field_name.'maxlength'}.'" minlength="'.$request->{$field_name.'minlength'}.'" rows="'.$request->{$field_name.'rows'}.'" '.$request->{$field_name.'required'}.'></textarea></div>';
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
                    $formDefination->name = str_replace(" ","_",$field_name . "~" . $request->{'field-lbl' . $i});
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
                    $formDefination->name = str_replace(" ","_",$field_name . "~" . $request->{'field-lbl' . $i});
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
                $vertical_cols = $vertical_cols . '<div class="form-group col-sm-6"><label class="control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><select class="form-control"  name="'. $t[1] .'" >'.$vertical_cols_options.'</select></div>';
                $evertical_cols = $evertical_cols . '<div class="form-group col-sm-6"><label class="control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><select class="form-control"  name="'. $t[1] .'" >'.$evertical_cols_options.'</select></div>';
                $horizontal_cols = $horizontal_cols .'<div class="form-group col-sm-6"><label class="col-lg-2 control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><div class="col-lg-10"><select class="form-control"  name="'. $t[1] .'" >'.$horizontal_options.'</select></div></div>';
                $ehorizontal_cols = $ehorizontal_cols .'<div class="form-group col-sm-6"><label class="col-lg-2 control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><div class="col-lg-10"><select class="form-control"  name="'. $t[1] .'" >'.$ehorizontal_options.'</select></div></div>';
                $horizontal = $horizontal .'<div class="form-group"><label class="col-lg-2 control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><div class="col-lg-10"><select class="form-control" name="'. $t[1] .'" >'.$horizontal_options.'</select></div></div>';
                $ehorizontal = $ehorizontal .'<div class="form-group"><label class="col-lg-2 control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><div class="col-lg-10"><select class="form-control" name="'. $t[1] .'" >'.$ehorizontal_options.'</select></div></div>';
                $vertical = $vertical . '<div class="form-group"><label class="control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><select class="form-control" name="'. $t[1] .'" >'.$vertical_options.'</select></div>';
                $evertical = $evertical . '<div class="form-group"><label class="control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><select class="form-control" name="'. $t[1] .'" >'.$evertical_options.'</select></div>';
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
                    $formDefination->name = str_replace(" ","_",$field_name . "~" . $request->{'field-lbl' . $i});
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
                    $formDefination->name = str_replace(" ","_",$field_name . "~" . $request->{'field-lbl' . $i});
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
                $vertical_cols = $vertical_cols .'<div class="form-group col-sm-6"><label class="control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><input type="'. $request->{'inputType'.$i} .'" name="'. $t[1] .'" class="form-control" id="" placeholder="'.$request->{$field_name.'placeholder'}.'" '.$request->{$field_name.'required'}.'></div>';
                $evertical_cols = $evertical_cols .'<div class="form-group col-sm-6"><label class="control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><input type="'. $request->{'inputType'.$i} .'" name="'. $t[1] .'" value = "{{ $data->'.$t[1].' }}" class="form-control" id="" placeholder="'.$request->{$field_name.'placeholder'}.'" '.$request->{$field_name.'required'}.'></div>';
                $horizontal_cols = $horizontal_cols .'<div class="form-group col-sm-6"> <label class="col-lg-2 control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><div class="col-lg-10"> <input type="'. $request->{'inputType'.$i} .'" name="'. $t[1] .'" id="" class="form-control"  placeholder="'.$request->{$field_name.'placeholder'}.'" '.$request->{$field_name.'required'}.'></div></div>';
                $ehorizontal_cols = $ehorizontal_cols .'<div class="form-group col-sm-6"> <label class="col-lg-2 control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><div class="col-lg-10"> <input type="'. $request->{'inputType'.$i} .'" name="'. $t[1] .'" value = "{{ $data->'.$t[1].' }}" id="" class="form-control"  placeholder="'.$request->{$field_name.'placeholder'}.'" '.$request->{$field_name.'required'}.'></div></div>';
                $horizontal = $horizontal.'<div class="form-group"> <label class="col-lg-2 control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><div class="col-lg-10"> <input type="'. $request->{'inputType'.$i} .'" id="" class="form-control" name="'. $t[1] .'" placeholder="'.$request->{$field_name.'placeholder'}.'" '.$request->{$field_name.'required'}.'></div></div>';
                $ehorizontal = $ehorizontal.'<div class="form-group"> <label class="col-lg-2 control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><div class="col-lg-10"> <input type="'. $request->{'inputType'.$i} .'" id="" class="form-control" name="'. $t[1] .'" value = "{{ $data->'.$t[1].' }}" placeholder="'.$request->{$field_name.'placeholder'}.'" '.$request->{$field_name.'required'}.'></div></div>';
                $vertical = $vertical .'<div class="form-group"><label class="control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><input type="'. $request->{'inputType'.$i} .'" class="form-control" id="" name="'. $t[1] .'" placeholder="'.$request->{$field_name.'placeholder'}.'" '.$request->{$field_name.'required'}.'></div>';
                $evertical = $evertical .'<div class="form-group"><label class="control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><input type="'. $request->{'inputType'.$i} .'" class="form-control" id="" name="'. $t[1] .'" value = "{{ $data->'.$t[1].' }}" placeholder="'.$request->{$field_name.'placeholder'}.'" '.$request->{$field_name.'required'}.'></div>';
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
                    $formDefination->name = str_replace(" ","_",$field_name . "~" . $request->{'field-lbl' . $i});
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
                    $formDefination->name = str_replace(" ","_",$field_name . "~" . $request->{'field-lbl' . $i});
                    $formDefination->save();
                }
                catch(\Exception $e)
                {
                    echo $e;
                }
            }
            else
            {
                $vertical_cols = $vertical_cols .'<div class="form-group col-sm-6"><label class="control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><input type="'. $request->{'inputType'.$i} .'" name="'. $t[1] .'" class="form-control" id="" '.$request->{$field_name.'required'}.'></div>';
                $evertical_cols = $evertical_cols .'<div class="form-group col-sm-6"><label class="control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><input type="'. $request->{'inputType'.$i} .'" name="'. $t[1] .'" value = "{{ $data->'.$t[1].' }}" class="form-control" id="" '.$request->{$field_name.'required'}.'></div>';
                $horizontal_cols = $horizontal_cols.'<div class="col-sm-6 form-group"> <label class="col-lg-2 control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><div class="col-lg-10"> <input type="'. $request->{'inputType'.$i} .'" name="'. $t[1] .'" id="" class="form-control" '.$request->{$field_name.'required'}.'></div></div>';
                $ehorizontal_cols = $ehorizontal_cols.'<div class="col-sm-6 form-group"> <label class="col-lg-2 control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><div class="col-lg-10"> <input type="'. $request->{'inputType'.$i} .'" name="'. $t[1] .'" id="" value = "{{ $data->'.$t[1].' }}" class="form-control" '.$request->{$field_name.'required'}.'></div></div>';
                $horizontal = $horizontal.'<div class="form-group"> <label class="col-lg-2 control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><div class="col-lg-10"> <input type="'. $request->{'inputType'.$i} .'" name="'. $t[1] .'" id="" class="form-control" '.$request->{$field_name.'required'}.'></div></div>';
                $ehorizontal = $ehorizontal.'<div class="form-group"> <label class="col-lg-2 control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><div class="col-lg-10"> <input type="'. $request->{'inputType'.$i} .'" name="'. $t[1] .'" id="" value = "{{ $data->'.$t[1].' }}" class="form-control" '.$request->{$field_name.'required'}.'></div></div>';
                $vertical = $vertical .'<div class="form-group"><label class="control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><input type="'. $request->{'inputType'.$i} .'" class="form-control" id="" name="'. $t[1] .'" '.$request->{$field_name.'required'}.'></div>';
                $evertical = $evertical .'<div class="form-group"><label class="control-label capitalize">'.$request->{'field-lbl'.$i}.'</label><input type="'. $request->{'inputType'.$i} .'" class="form-control" id="" value = "{{ $data->'.$t[1].' }}" name="'. $t[1] .'" '.$request->{$field_name.'required'}.'></div>';
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
                    $formDefination->name = str_replace(" ","_",$field_name . "~" . $request->{'field-lbl' . $i});
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
                return view('autocrud::Admin.Multi_tables.mPageLayout', compact('table'));
            }
            else
            {
                return view('autocrud::Admin.Multi_tables.mPageLayout', compact('table','is_available'));
            }
        }
        catch (\Exception $e) {
            echo $e->getMessage();
        }
    }

    public function mCreateLayout(Request $request)
    {
        try
        {
            $table = $request->table;
            $layout = $request->layout;
            DB::table('page_layouts')
                ->where('table',$request->table)
                ->update(['layout'=>$layout]);

            return view('autocrud::Admin.Multi_tables.mCreateLayout',compact('layout','table'));
        }
        catch(\Exception $e)
        {
            echo $e;
        }
    }


    public function mGenerateView(Request $request)
    {
        //create directory
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
            $tmp = explode("~",$item->name);
            if($item->type != 'none')
                $table .= '<th>'.str_replace("_"," ",$tmp[1]).'</th>';
        }
        $table.='</tr></thead><tbody>@foreach($data as $item)';

        $table .= '<tr><td><input type="checkbox" name="id[]" class="chkbx" value="{{ ';

        $common = DB::table('common_fields')
            ->select('field1','field2')
            ->where('refid',$request->array_name)
            ->get();

        foreach($common as $item)
        {
            $table .= '$item->' . $item->field1 . " . '~' . " ;
        }
        $table  = substr($table,0,-8);
        $table .= ' }}"></td>';
        foreach($fields as $item)
        {
            $tmp = explode("~",$item->name);
            if($item->type != 'none')
                $table .= '<td>{{ $item->'. $tmp[1] .' }}</td>';
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
        $table = '<table class="table table-hover"><thead><tr> <th>Field</th><th>Value</th></tr></thead><tbody>';

        $i=1;
        foreach($fields as $item)
        {
            $tmp = explode("~",$item->name);
            if($item->type != 'none')
                $table .= '<tr><td>'.str_replace("_"," ",$tmp[1]).'</td><td>{{ $data->'.$tmp[1].' }}</td></tr>';
            else
                $i++;
        }
        $table .= '</tbody></table>';

        //end code...
        $end = '</center></div></div>'."\n@endsection\n\n\n@section('custom-js')\n <script type='text/javascript'>\n\n //add your jquery or javascript code here \n\n </script>\n@endsection";

        file_put_contents('../resources/views/user/'.$request->view_name.'/'.$request->view_name.'_show.blade.php', $start.$table.$end);

        return view('autocrud::Admin.done');
    }


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
            ->get(['name','type']);

        //convert json object to array.
        $fields = json_decode($fields,true);

        //sort array by table name
        usort($fields, function($x, $y) {
            return strcasecmp($x['name'] , $y['name']);
        });

        //create single array
        $new_ar = array();
        foreach($fields as $item)
        {
            $items = explode("~",$item['name']);
            array_push($items,$item['type']);
            array_push($new_ar,$items);
        }

        //create group of table array
        $result = array();
        foreach($new_ar as $k => $v) {
            $result[$v[0]][$k] = $v;
        }

        //get common fields
        $common = DB::table('common_fields')
            ->select('table1','field1','table2','field2')
            ->where('refid',$array_name)
            ->get();

        $all_fields_index = DB::table("join_tables")
            ->select('table_name','field_name')
            ->where('view_name',$array_name)
            ->get();

        /*
         * code for controller declaration and add packages
         */
        $head = "<?php\n\nnamespace App\Http\Controllers;\n\nuse App\UserInfo;\nuse Illuminate\Http\Request;\nuse Illuminate\Support\Facades\DB;\nuse Illuminate\Support\Facades\Input;\nclass ". $view_name ."Controller extends Controller\n{";


        /*
         * code for index method
         */
        $index = "\n\n\t//Display a listing of the resource.\n\tpublic function index()\n\t{\n\t\t".'$data = DB::table("'.$common['0']->table1.'")';
        //generate join
        foreach($common as $item)
        {
            $index .= "\n\t\t\t->join(".'"' . $item->table2 . '" , "' . $item->table1 . '.' . $item->field1 . '" , "=" , "' . $item->table2 . '.' . $item->field2 . '")' ;
        }
        //generate select
        $index .= "\n\t\t\t->select(";
        foreach($all_fields_index as $item)
        {
            $index .= '"' . $item->table_name . '.' . $item->field_name . '" , ';
        }
        //remove last comma(,)
        $index = substr($index, 0, -2);
        $index .=')';

        $index .= "\n\t\t\t".'->paginate(300);'."\n\t\t".'return view("user.'.$view_name.'.'.$view_name.'_details",compact("data"));'."\n\t}";

        /*
         * generate create mehtod
         */
        $create = "\n\n\t//Display a listing of the resource.\n\tpublic function create()\n\t{\n\t\t".'return view("user.'.$view_name.'.'.$view_name.'");'."\n\t}";


        /*
         * generate store method
         */
        $insert= "";
        $i = 0;

        //get table name order by user selection form jion_table
        $table_order = DB::table('join_tables')
            ->select('table_name')
            ->where('view_name',$array_name)
            ->groupBy('table_name')
            ->orderBy('id')
            ->get();

        $sorted_table = array();
        foreach($table_order as $table_name)
        {
            array_push($sorted_table,$result[$table_name->table_name]);
        }

        $rid_type = array();
        $flg_for_rid = 0;
        $flg_for_insert = 0;
        $flg_prev = 0;
        $rid_type_counter = 0;
        $rid_key = 0;
        $insert_array = array();
        $insert_array_key = 0;
        $rid_array = array();
        foreach($sorted_table as $tables)
        {
            //if first table then generate insert code without rid.
            if($flg_for_insert == 0)
            {
                $insert .= "\n\t\tDB::table('".$table_order[$i]->table_name."')->insert([";
                foreach($tables as $table)
                {
                    if($table[3] == 'multiselect')
                    {
                        $insert.="\n\t\t\t".'"'.$table[1].'" => implode("," , $request->'.$table[1].'),';
                    }
                    elseif($table[3] == 'date')
                    {
                        $insert.="\n\t\t\t".'"'.$table[1].'" => date("Y-m-d",strtotime($request->'.$table[1].')),';
                    }
                    elseif($table[3] == 'none')
                    {

                    }
                    else
                    {
                        $insert.="\n\t\t\t".'"'.$table[1].'" => $request->'.$table[1].',';
                    }
                }
                $flg_for_insert = 1;
                $insert = substr($insert, 0, -1);
                $insert .= "\n\t\t]);\n";
            }
            //if it's not first table then it generate insert code with rid.
            else
            {
                $insert_array[$insert_array_key] = "\n\t\tDB::table('".$table_order[$i]->table_name."')->insert([";
                if($flg_prev == 0)
                {
                    foreach($tables as $table)
                    {
                        if($table[3] == 'multiselect')
                        {
                            $insert_array[$insert_array_key].="\n\t\t\t".'"'.$table[1].'" => implode("," , $request->'.$table[1].'),';
                        }
                        elseif($table[3] == 'date')
                        {
                            $insert_array[$insert_array_key].="\n\t\t\t".'"'.$table[1].'" => date("Y-m-d",strtotime($request->'.$table[1].')),';
                        }
                        elseif($table[3] == 'none')
                        {

                        }
                        else
                        {
                            $insert_array[$insert_array_key].="\n\t\t\t".'"'.$table[1].'" => $request->'.$table[1].',';
                        }
                    }
                    if(sizeof($sorted_table) > 2)
                        $insert_array[$insert_array_key] .= "\n\t\t\t".'"'. $common[$rid_type_counter]->field2 .'" =>  $rid0->' . $common[$rid_type_counter]->field1;
                    else
                        $insert_array[$insert_array_key] .= "\n\t\t\t".'"'. $common[$rid_type_counter]->field2 .'" =>  $rid0->' . $common[$rid_type_counter]->field1;

                    $flg_prev = 1;
                }
                else
                {
                    foreach($tables as $table)
                    {
                        if($table[3] == 'multiselect')
                        {
                            $insert_array[$insert_array_key].="\n\t\t\t".'"'.$table[1].'" => implode("," , $request->'.$table[1].'),';
                        }
                        elseif($table[3] == 'date')
                        {
                            $insert_array[$insert_array_key].="\n\t\t\t".'"'.$table[1].'" => date("Y-m-d",strtotime($request->'.$table[1].')),';
                        }
                        elseif($table[3] == 'none')
                        {

                        }
                        else
                        {
                            $insert_array[$insert_array_key].="\n\t\t\t".'"'.$table[1].'" => $request->'.$table[1].',';
                        }
                    }
                    if($rid_type[$rid_type_counter + 1] == "c")
                    {
                        if($rid_type[$rid_type_counter] == "d")
                            $rid_key++;

                        $insert_array[$insert_array_key] .= "\n\t\t\t".'"'. $common[$rid_type_counter + 1]->field2 .'" =>  $rid'. $rid_key .'->' . $common[$rid_type_counter + 1]->field1 ;
                    }
                    else
                    {
                        $rid_key = $rid_key + 1;
                        $insert_array[$insert_array_key] .= "\n\t\t\t".'"'. $common[$rid_type_counter + 1]->field2 .'" =>  $rid'. $rid_key .'->' . $common[$rid_type_counter + 1]->field1 ;
                    }
                    $rid_type_counter++;
                }
                $insert_array[$insert_array_key] .= "\n\t\t]);\n";
                $insert_array_key++;
            }

            if($flg_for_rid == 0)
            {
                $n = sizeof($common);
                $k = 0;
                $f=0;
                // if $n = 1 then
                if($n == 1)
                {
                    for($j=0; $j<$n; $j++)
                    {
                        $insert .= "\n\t\t".'$rid'.$k++.' = '."DB::table('".$table_order[$i]->table_name."')\n\t\t\t->latest()\n\t\t\t->first(['".$common[$j]->field1."']);\n";
                        array_push($rid_type,'c');
                        $f=1;
                    }
                }
                // if $n > 1
                for($j=0; $j<$n-1; $j++)
                {
                    //if both reference key is same
                    if($common[$j]->table1 == $common[$j+1]->table1 && $common[$j]->field1 == $common[$j+1]->field1)
                    {
                        //if add first time
                        array_push($rid_type,'c');
                        if($f == 0)
                        {
                            $rid_array[$k] = "\n\t\t".'$rid'.$k++.' = '."DB::table('".$common[$j]->table1."')\n\t\t\t->latest()\n\t\t\t->first(['".$common[$j]->field1."']);\n";
                            array_push($rid_type,'c');
                            $f=1;
                        }
                        else
                        {
                            $rid_array[$k] = "\n\t\t".'$rid'.$k++.' = '."DB::table('".$common[$j+1]->table1."')\n\t\t\t->latest()\n\t\t\t->first(['".$common[$j+1]->field1."']);\n";
                            array_push($rid_type,'c');
                        }
                    }
                    //if reference key are different
                    else
                    {
                        // if reference key table are same
                        array_push($rid_type,'d');
                        if($common[$j]->table1 == $common[$j+1]->table1)
                        {
                            $rid_array[$k] = "\n\t\t".'$rid'.$k++.' = '."DB::table('".$common[$j]->table1."')\n\t\t\t->latest()\n\t\t\t->first(['".$common[$j+1]->field1."']);\n";
                        }
                        // if reference key table are different
                        else
                        {
                            // if record insert first time and, next element of array is diffrent
                            if($f == 0)
                            {
                                array_push($rid_type,'d');
                                $rid_array[$k] = "\n\t\t".'$rid'.$k++.' = '."DB::table('".$common[$j]->table1."')\n\t\t\t->latest()\n\t\t\t->first(['".$common[$j+1]->field1."']);\n";
                                $rid_array[$k] = "\n\t\t".'$rid'.$k++.' = '."DB::table('".$common[$j+1]->table1."')\n\t\t\t->latest()\n\t\t\t->first(['".$common[$j+1]->field1."']);\n";
                                $f = 1;
                            }
                            // if record is not insert first time.
                            else
                                $rid_array[$k] = "\n\t\t".'$rid'.$k++.' = '."DB::table('".$common[$j+1]->table1."')\n\t\t\t->latest()\n\t\t\t->first(['".$common[$j+1]->field1."']);\n";
                        }
                    }
                }
                $flg_for_rid = 1;
            }
            $i++;
        }
        $rid_key = 1;
        $insert_array_key = 0;
        $flg = 0;
        $cnt = 0;
        $i = 0;
        $all_common = 0;

        $total_field = sizeof($fields);
        foreach($rid_type as $item)
        {
            if($item != "c");
            {
                $all_common = 1;
                break;
            }
        }

        foreach($rid_type as $item)
        {

            if($all_common == 1 && $i == $total_field)
                break;
            if($i++ < 1)
                continue;
            if($flg == 0)
            {
                if($cnt > 0 && $item == 'c' )
                {
                    $insert .= $insert_array[$insert_array_key];
                }
                else
                {
                    $insert .= $rid_array[$rid_key];
                    $insert .= $insert_array[$insert_array_key];
                }
                $rid_key++;
                if($item == 'c')
                {
                    $flg=1;
                }
            }
            else
            {
                if($item == 'c')
                {
                    $insert .= $insert_array[$insert_array_key];
                }
                else
                {
                    $insert .= $rid_array[$rid_key];
                    $insert .= $insert_array[$insert_array_key];
                    $rid_key++;
                }
            }
            $insert_array_key++;
            $cnt++;
        }

        //concate insert generated code with store
        $store = "\n\n\t// Store a newly created resource in storage.\n\tpublic function store(Request ".'$request'.")\n\t{".$insert."\n\t\t".'return redirect(route("'.$view_name.'.create"));'."\n\t}";


        /*
         * generate show method code
         */
        $show = "\n\n\t// Display the specified resource.\n\tpublic function show(".'$id'.")\n\t{\n\t\t".'$data = DB::table("'.$common['0']->table1.'")';
        //generate join
        foreach($common as $item)
        {
            $show .= "\n\t\t\t->join(".'"' . $item->table2 . '" , "' . $item->table1 . '.' . $item->field1 . '" , "=" , "' . $item->table2 . '.' . $item->field2 . '")' ;
        }
        //generate select
        $show .= "\n\t\t\t->select(";
        foreach($new_ar as $item)
        {
            $show .= '"' . $item[0] . '.' . $item[1] . '" , ';
        }
        //remove last comma(,)
        $show = substr($show, 0, -2);
        $show .=')';

        //add where condition
        $show .= "\n\t\t\t->where('". $common[0]->table1 .".". $common[0]->field1 ."',". '$id' .")";
        $show .= "\n\t\t\t".'->first();'."\n\t\t".'return view("user.'.$view_name.'.'.$view_name.'_show",compact("data"));'."\n\t}";



        /*
         * generate edit method code
         */
        $edit = "\n\n\t// Show the form for editing the specified resource.\n\tpublic function edit(".'$id'.")\n\t{\n\t\t".'$data = DB::table("'.$common['0']->table1.'")';
        //generate join
        foreach($common as $item)
        {
            $edit .= "\n\t\t\t->join(".'"' . $item->table2 . '" , "' . $item->table1 . '.' . $item->field1 . '" , "=" , "' . $item->table2 . '.' . $item->field2 . '")' ;
        }

        //generate select
        $edit .= "\n\t\t\t->select(";
        foreach($new_ar as $item)
        {
            $edit .= '"' . $item[0] . '.' . $item[1] . '" , ';
        }
        //remove last comma(,)
        $edit = substr($edit, 0, -2);
        $edit .=')';

        //add where condition
        $edit .= "\n\t\t\t->where('". $common[0]->table1 .".". $common[0]->field1 ."',". '$id' .")";
        $edit .= "\n\t\t\t".'->first();'."\n\t\t".'return view("user.'.$view_name.'.edit_'.$view_name.'",compact("data","id"));'."\n\t}";


        /*
         * Generate update
         */
        $update= "";
        $i = 0;

        //get table name order by user selection form jion_table
        $table_order = DB::table('join_tables')
            ->select('table_name')
            ->where('view_name',$array_name)
            ->groupBy('table_name')
            ->orderBy('id')
            ->get();

        $sorted_table = array();
        foreach($table_order as $table_name)
        {
            array_push($sorted_table,$result[$table_name->table_name]);
        }

        $rid_type = array();
        $flg_for_rid = 0;
        $flg_for_insert = 0;
        $flg_prev = 0;
        $rid_type_counter = 0;
        $rid_key = 0;
        $update_array = array();
        $update_array_key = 0;
        $t = 0;
        $rid_array = array();
        foreach($sorted_table as $tables)
        {
            //if first table then generate insert code without rid.
            if($flg_for_insert == 0)
            {
                $update .= "\n\t\tDB::table('".$table_order[$i]->table_name."')\n\t\t->where('" . $common[$t]->field1 . "',". '$id[0]' .")\n\t\t->update([";
                foreach($tables as $table)
                {
                    if($table[3] == 'multiselect')
                    {
                        $update.="\n\t\t\t".'"'.$table[1].'" => implode("," , $request->'.$table[1].'),';
                    }
                    elseif($table[3] == 'date')
                    {
                        $update.="\n\t\t\t".'"'.$table[1].'" => date("Y-m-d",strtotime($request->'.$table[1].')),';
                    }
                    elseif($table[3] == 'none')
                    {

                    }
                    else
                    {
                        $update.="\n\t\t\t".'"'.$table[1].'" => $request->'.$table[1].',';
                    }
                }
                $flg_for_insert = 1;
                $update = substr($update, 0, -1);
                $update .= "\n\t\t]);\n";
            }
            //if it's not first table then it generate insert code with rid.
            else
            {
                $update_array[$update_array_key] = "\n\t\tDB::table('".$table_order[$i]->table_name."')\n\t\t->where('" . $common[0]->field1 . "',". '$id['.$t++.']' .")\n\t\t->update([";
                if($flg_prev == 0)
                {
                    foreach($tables as $table)
                    {
                        if($table[3] == 'multiselect')
                        {
                            $update_array[$update_array_key].="\n\t\t\t".'"'.$table[1].'" => implode("," , $request->'.$table[1].'),';
                        }
                        elseif($table[3] == 'date')
                        {
                            $update_array[$update_array_key].="\n\t\t\t".'"'.$table[1].'" => date("Y-m-d",strtotime($request->'.$table[1].')),';
                        }
                        elseif($table[3] == 'none')
                        {

                        }
                        else
                        {
                            $update_array[$update_array_key].="\n\t\t\t".'"'.$table[1].'" => $request->'.$table[1].',';
                        }
                    }
                    if(sizeof($sorted_table) > 2)
                        $update_array[$update_array_key] .= "\n\t\t\t".'"'. $common[$rid_type_counter]->field2 .'" =>  $rid0->' . $common[$rid_type_counter]->field1;
                    else
                        $update_array[$update_array_key] .= "\n\t\t\t".'"'. $common[$rid_type_counter]->field2 .'" =>  $rid0->' . $common[$rid_type_counter]->field1;

                    $flg_prev = 1;
                }
                else
                {
                    foreach($tables as $table)
                    {
                        if($table[3] == 'multiselect')
                        {
                            $update_array[$update_array_key].="\n\t\t\t".'"'.$table[1].'" => implode("," , $request->'.$table[1].'),';
                        }
                        elseif($table[3] == 'date')
                        {
                            $update_array[$update_array_key].="\n\t\t\t".'"'.$table[1].'" => date("Y-m-d",strtotime($request->'.$table[1].')),';
                        }
                        elseif($table[3] == 'none')
                        {

                        }
                        else
                        {
                            $update_array[$update_array_key].="\n\t\t\t".'"'.$table[1].'" => $request->'.$table[1].',';
                        }
                    }
                    if($rid_type[$rid_type_counter + 1] == "c")
                    {
                        if($rid_type[$rid_type_counter] == "d")
                            $rid_key++;

                        $update_array[$update_array_key] .= "\n\t\t\t".'"'. $common[$rid_type_counter + 1]->field2 .'" =>  $rid'. $rid_key .'->' . $common[$rid_type_counter + 1]->field1 ;
                    }
                    else
                    {
                        $rid_key = $rid_key + 1;
                        $update_array[$update_array_key] .= "\n\t\t\t".'"'. $common[$rid_type_counter + 1]->field2 .'" =>  $rid'. $rid_key .'->' . $common[$rid_type_counter + 1]->field1 ;
                    }
                    $rid_type_counter++;
                }
                $update_array[$update_array_key] .= "\n\t\t]);\n";
                $update_array_key++;
            }

            if($flg_for_rid == 0)
            {
                $n = sizeof($common);
                $k = 0;
                $f=0;
                // if $n = 1 then
                if($n == 1)
                {
                    for($j=0; $j<$n; $j++)
                    {
                        $update .= "\n\t\t".'$rid'.$k++.' = '."DB::table('".$table_order[$i]->table_name."')\n\t\t\t->latest()\n\t\t\t->first(['".$common[$j]->field1."']);\n";
                        array_push($rid_type,'c');
                        $f=1;
                    }
                }
                // if $n > 1
                for($j=0; $j<$n-1; $j++)
                {
                    if($common[$j]->table1 == $common[$j+1]->table1 && $common[$j]->field1 == $common[$j+1]->field1)
                    {
                        array_push($rid_type,'c');
                        if($f == 0)
                        {
                            $rid_array[$k] = "\n\t\t".'$rid'.$k.' = '."DB::table('".$common[$j]->table1."')\n\t\t\t->where('" . $common[$j]->field1 . "',". '$id['.$k++.']' .")\n\t\t\t->first(['".$common[$j]->field1."']);\n";
                            array_push($rid_type,'c');
                            $f=1;
                        }
                        else
                        {
                            $rid_array[$k] = "\n\t\t".'$rid'.$k++.' = '."DB::table('".$common[$j+1]->table1."')\n\t\t\t->latest()\n\t\t\t->first(['".$common[$j+1]->field1."']);\n";
                            array_push($rid_type,'c');
                        }
                    }
                    else
                    {
                        array_push($rid_type,'d');
                        if($common[$j]->table1 == $common[$j+1]->table1)
                        {
                            $rid_array[$k] = "\n\t\t".'$rid'.$k++.' = '."DB::table('".$common[$j]->table1."')\n\t\t\t->where('" . $common[$j+1]->field1 . "',". '$id['.$k++.']' .")\n\t\t\t->first(['".$common[$j+1]->field1."']);\n";
                        }
                        else
                        {
                            if($f == 0)
                            {
                                array_push($rid_type,'d');
                                $rid_array[$k] = "\n\t\t".'$rid'.$k.' = '."DB::table('".$common[$j]->table1."')\n\t\t\t->where('" . $common[$j+1]->field1 . "',". '$id['.$k++.']' .")\n\t\t\t->first(['".$common[$j+1]->field1."']);\n";
                                $rid_array[$k] = "\n\t\t".'$rid'.$k.' = '."DB::table('".$common[$j+1]->table1."')\n\t\t\t->where('" . $common[$j+1]->field1 . "',". '$id['.$k++.']' .")\n\t\t\t->first(['".$common[$j+1]->field1."']);\n";
                                $f = 1;
                            }
                            else
                                $rid_array[$k] = "\n\t\t".'$rid'.$k.' = '."DB::table('".$common[$j+1]->table1."')\n\t\t\t->where('" . $common[$j+1]->field1 . "',". '$id['.$k++.']' .")\n\t\t\t->first(['".$common[$j+1]->field1."']);\n";
                        }
                    }
                }
                $flg_for_rid = 1;
            }
            $i++;
        }

        $rid_key = 1;
        $update_array_key = 0;
        $flg = 0;
        $i = 0;
        foreach($rid_type as $item)
        {
            if($all_common == 1 && $i == $total_field)
                break;
            if($i++ < 1)
                continue;
            if($flg == 0)
            {
                $update .= $rid_array[$rid_key];
                $update .= $update_array[$update_array_key];
                $rid_key++;
                if($item == 'c')
                    $flg=1;
            }
            else
            {
                if($item == 'c')
                {
                    $update .= $update_array[$update_array_key];
                }
                else
                {
                    $update .= $rid_array[$rid_key];
                    $update .= $update_array[$update_array_key];
                    $rid_key++;
                }
            }
            $update_array_key++;
        }

        //concate insert generated code with store
        $update_header = "\n\n\t// Update specific record in Resources.\n\tpublic function update(Request ".'$request , $id'.")\n\t{\n\t\t".'$id = explode("~",$id);'.$update."\n\t\t".'return redirect(route("'.$view_name.'.create"));'."\n\t}";


        /*
         * generate delete method code.
         */
        $delete = "\n\n\t// Remove the specified resource from storage.\n\tpublic function destroy(".'$id'.")\n\t{\n\t\t";

        // delete entry from all the tables
        $flg = 0;
        $t = 0;
        $delete .= "\n\t\t".'$id = explode("~",$id);';
        foreach($common as $item)
        {
            if($flg == 0)
            {
                $delete .= "\n\n\t\tDB::table('".$item->table1."')\n\t\t\t->where('". $item->field1 ."',".'$id[0]'.")->delete();";
                $flg = 1;
            }
            $delete .= "\n\n\t\tDB::table('".$item->table2."')\n\t\t\t->where('". $item->field2 ."',".'$id['. $t++ .']'.")->delete();";
        }

        $delete .= "\n\n\t\t".'return redirect(route("'.$view_name.'.index"));'."\n\t}";


        /*
         * generate controller file in Controllers directory.
         */
        $end = "\n\n}";
        file_put_contents('../app/Http/Controllers/'. $view_name .'Controller.php', $head . $index . $create . $store . $show . $edit . $update_header . $delete . $end );}
}
