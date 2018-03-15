<?php

namespace Hiren\Autocrud\Controllers;

use Carbon\Carbon;
use App\JoinTable;
use App\CommonField;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Cache;

class DisplayDataController extends Controller
{

    //public function __construct()
    //{
       //$this->middleware('auth', ['except' => ['multiTables','generateTable','commonFields','storeCommon','createExtraFields','sortTableFields','createMultiTableView']]);
    //}


    public function multiTables()
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
        return view('autocrud::Admin.Display_data.multiTables',compact('table_list','size','options'));
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
        return redirect()->route('tcommonFields', ['refno' => $refno]);
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
        return view('autocrud::Admin.Display_data.mCommonFields',compact('data'));
    }


    public function storeCommon(Request $request)
    {
        $n = $request->no_of_table;
        $fields_array = array_chunk($request->cmn , $n);
        $refno = Cache::get('refno');

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


        $refid = $refno;
        return view('autocrud::Admin.Display_data.extraFields',compact('refid'));

    }



    public function createExtraFields(Request $request)
    {
        // get all the label name
        $labels = $request->field_lbl;
        //get all types calc, condi...
        $type = explode(",", $request->type);
        //get name of all the condition field
        $all_uid = explode(",",$request->allUID);

        //remove last ,(comma) from array
        array_pop($type);
        array_shift($all_uid);
        $i = 1;
        $k = 0;
        $cond = 0;
        $extra_data = array();
        foreach ($type as $item)
        {
            if ($item)
            {
                if($item == "calculate")
                {
                    $opr = $request->{"calcopr" . $i};
                    $sel = $request->{"calcselect" . $i};
                    $vardata = "";
                    for($j=0; $j<3; $j++)
                    {
                        $vardata .= $opr[$j]." ";
                        if($j < 2)
                            $vardata .= $sel[$j]." ";
                    }
                    $extra_data[$k] = $labels[$k] . "*-*" . $vardata;
                }
                else
                {
                    $extra_data[$k] = $labels[$k] . "*-*" . $request->{$all_uid[$cond] . "t"} . "*-*" . $request->{$all_uid[$cond] . "at"} . "*-*" . $request->{$all_uid[$cond++] . "et"};
                }
                $k++;
                $i++;
            }
        }

        if($extra_data)
            Cache::put('extra_data',$extra_data,Carbon::now()->addMinutes(180));
        return $this->sortTableFields($request->refid);
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
        return view('autocrud::Admin.Display_data.sortTableFields',compact('fields','data','refno'));
    }


    public function createMultiTableView(Request $request)
    {
        $view_name = $request->name_of_view;
        /*
         *  Generate route
         */
        $data = file_get_contents('../routes/web.php');
        if(!strpos($data,'Route::get("'. $view_name .'","'. $view_name .'Controller@index");'))
        {
            file_put_contents('../routes/web.php',$data."\n".'Route::get("'. $view_name .'","'. $view_name .'Controller@index");');
        }


        /*
         * Generate controller
         */
        $tables = DB::table('join_tables')
            ->where('view_name',$request->refno)
            ->groupBy('table_name')
            ->get(['table_name','common_key']);


        $common = DB::table('common_fields')
            ->select('table1','field1','table2','field2')
            ->where('refid',$request->refno)
            ->get();

        $n = count($tables);
        $head = "<?php\n\nnamespace App\Http\Controllers;\n\nuse App\UserInfo;\nuse Illuminate\Http\Request;\nuse Illuminate\Support\Facades\DB;\nuse Illuminate\Support\Facades\Input;\nclass ". $view_name ."Controller extends Controller\n{";

        $index = "\n\n\t//Display a listing of the resource.\n\tpublic function index()\n\t{\n\t\t".'$data = DB::table("'.$tables[0]->table_name .'")';

        foreach($common as $item)
        {
            $index .= "\n\t\t\t->join(".'"' . $item->table2 . '" , "' . $item->table1 . '.' . $item->field1 . '" , "=" , "' . $item->table2 . '.' . $item->field2 . '")' ;
        }

        //add select fields
        $order = $request->order;
        $i=0;
        //remove all the extra added fields
        foreach($order as $item)
        {
            if(strpos($item,'~~') == false)
                unset($order[$i]);
            $i++;
        }
        $index .= "\n\t\t\t".'->select(';
        foreach($order as $item)
        {
            $t = explode("~~",$item);
            $index.='"'. $t[1] .'.'. $t[0] .'",';
        }
        $index = rtrim($index,",");
        $index.=')';


        // add filters like where-orWhere-whereIn-etc...
        for($i=0; $i<=$request->no_of_filter; $i++)
        {
            if($request{"filterType".$i} == "orderBy" || $request{"filterType".$i} == "groupBy" || $request{"filterType".$i} == "whereNull" || $request{"filterType".$i} == "whereNotNull")
            {
                $select_value = explode("~~",$request->{"filterselect" . $i});
                $index.= "\n\t\t\t".'->'.$request{"filterType".$i}.'("'.$select_value[0].'.'.$select_value[1].'")';
            }
            else if($request{"filterType".$i} == "whereBetween" || $request{"filterType".$i} == "whereNotBetween")
            {
                $select_value = explode("~~",$request->{"filterselect" . $i});
                $index.= "\n\t\t\t".'->'.$request{"filterType".$i}.'("'.$select_value[0].'.'.$select_value[1].'",['.$request{"from".$i}.','.$request{"to".$i}.'])';
            }
            else if($request{"filterType".$i} == "whereColumn" ){
                $first_select_value = explode("~~",$request->{"1filterselect" . $i});
                $second_select_value = explode("~~",$request->{"2filterselect" . $i});
                if(trim($request{"relation".$i}))
                    $index.= "\n\t\t\t".'->'.$request{"filterType".$i}.'("'.$first_select_value[0].'.'.$first_select_value[1].'" , "'. $request{"relation".$i}.'" , "'. $second_select_value[0].'.'.$second_select_value[1] .'")';
                else
                    $index.= "\n\t\t\t".'->'.$request{"filterType".$i}.'("'.$first_select_value[0].'.'.$first_select_value[1].'" , "'. $second_select_value[0].'.'.$second_select_value[1] .'")';
            }
            else if($request{"filterType".$i} == "where" || $request{"filterType".$i} == "orWhere")
            {
                $select_value = explode("~~",$request->{"filterselect" . $i});
                if(trim($request{"relation".$i}))
                    $index.= "\n\t\t\t".'->'.$request{"filterType".$i}.'("'.$select_value[0].'.'.$select_value[1].'" , "' . $request{"relation".$i} . '" , ';
                else
                    $index.= "\n\t\t\t".'->'.$request{"filterType".$i}.'("'.$select_value[0].'.'.$select_value[1].'" , ';

                if(is_numeric($request->{"value" . $i}))
                    $index .= $request->{"value" . $i} ;
                else
                    $index .= '"'.$request->{"value" . $i}.'"' ;

                $index .= ')';
            }
            else if($request{"filterType".$i} == "")
            {}
            else
            {
                $value_array = explode(",",$request->{"array".$i});
                $index.= "\n\t\t\t".'->'.$request{"filterType".$i}. '" , [';

                foreach($value_array as $item)
                    $index .= $item . ',';

                $index = rtrim($index,",");
                $index .= '])';
            }
        }

        //create get[] data query
        $index .= "\n\t\t\t".'->paginate(300);'."\n\t\t".'return view("user.'.$view_name.'.'.$view_name.'_details",compact("data"));'."\n\t}";

        $end = "\n\n}";

        file_put_contents('../app/Http/Controllers/'. $view_name .'Controller.php', $head . $index . $end );

        //================================================ controller generation code complete =========================

        /*
         * Generate View
         */

        //folder bane che teno code
        if (!file_exists('../resources/views/user/'.$view_name)) {
            mkdir('../resources/views/user/'.$view_name, 0777, true);
        }
        $extra = Cache::get('extra_data');

        $start = "@extends('layouts.admin') \n\n\n@section('custom-css')\n<style>\n\n /*write your css here*/ \n\n</style>\n@endsection \n\n\n@section('content')\n".'<div class="container-fluid"><div><legend class="heading">'. str_replace("_"," ",$request->array_name) .' details</legend></div><form class="table-form" id="'.$request->array_name.'_details"><div class="col-sm-8">{{ $data->links() }}</div>';

        //================================= generate table =======================================
        // generate table heading
        $table = '<table class="table table-hover dt"><thead><tr>';
        $order = $request->order;
        foreach($order as $item) {
            if (strpos($item, '~~') == true)
            {
                $t = explode("~~",$item);
                $table .= "<th>" . str_replace("_"," ",$t[0]) . "</th>";
            }
            else
            {
                $table .= "<th>" . $item . "</th>";
            }
        }
        $table.='</tr></thead><tbody>@foreach($data as $item)<tr>';

        // generate table body
        foreach($order as $item) {
            //table fields
            if (strpos($item, '~~') == true)
            {
                $t = explode("~~",$item);
                $table .= '<td>{{ $item->' . $t[0] . ' }}</td>';
            }
            // extra added fields
            else
            {
                $table .= '<td>{{ ';
                foreach($extra as $item_field)
                {
                    $t = explode("*-*",$item_field);
                    //match filed heading
                    if($t[0] == $item)
                    {
                        //calculate filed code
                        if(sizeof($t) < 3)
                        {
                            $tmp = explode(" ",$t[1]);
                            foreach($tmp as $tmp_item)
                            {
                                if(strpos($tmp_item,"~~") == true)
                                {
                                    $tmps = explode("~~",$tmp_item);
                                    $table .= '$item->'.$tmps[1];
                                }
                                else
                                {
                                    $table .= " ".$tmp_item . " ";
                                }
                            }
                        }
                        //coditional filed code
                        else
                        {
                            //add condition
                            $tmp = explode(" ",$t[1]);
                            foreach($tmp as $tmp_item)
                            {
                                if(strpos($tmp_item,"~~") == true)
                                {
                                    $tmps = explode("~~",$tmp_item);
                                    $table .= '$item->'.$tmps[1];
                                }
                                else
                                {
                                    $table .= " ".$tmp_item . " ";
                                }
                            }
                            $table .= " ? ";

                            // add if conditon true
                            $tmp = explode(" ",$t[2]);
                            foreach($tmp as $tmp_item)
                            {
                                if(strpos($tmp_item,"~~") == true)
                                {
                                    $tmps = explode("~~",$tmp_item);
                                    $table .= '$item->'.$tmps[1];
                                }
                                else
                                {
                                    $table .= " ".$tmp_item . " ";
                                }
                            }
                            $table .= " : ";

                            // add if condition false
                            $tmp = explode(" ",$t[3]);
                            foreach($tmp as $tmp_item)
                            {
                                if(strpos($tmp_item,"~~") == true)
                                {
                                    $tmps = explode("~~",$tmp_item);
                                    $table .= '$item->'.$tmps[1];
                                }
                                else
                                {
                                    $table .= " ".$tmp_item . " ";
                                }
                            }
                        }
                    }
                }
                $table .= ' }}</td>';
            }
        }
        $table .= '</tr>@endforeach</tbody></table>';
        //================================== end code ==============================================
        $end = "\n@endsection\n\n\n@section('custom-js')\n <script type='text/javascript'>\n\n //add your jquery or javascript code here \n\n </script>\n@endsection";

        file_put_contents('../resources/views/user/'.$view_name.'/'.$view_name.'_details.blade.php', $start.$table.$end);

        return view('autocrud::Admin.done');
    }
}
