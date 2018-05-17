<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Illuminate\Support\Facades\Auth;
use Log;
use App\Http\Requests;
use Exception;
use Excel;
#https://github.com/Maatwebsite/Laravel-Excel

class UploadFormFileController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->test_opt = FALSE;
        $this->time = date("Y-m-d H:i:s");
    }

    public function importFile($request)
    {
        if($request->hasFile('file')){
            $path = $request->file('file')->getRealPath();
            $data = Excel::load($path, function($reader){})->get()->toArray();
            $data_countor = count($data);
            if($data_countor==0)
                throw new Exception("Sorry, an error occurs: File can't empty.<br>Please check it.");
            $data_col_countor = count($data[0]);
            if($data_col_countor<4)
                throw new Exception("Sorry, an error occurs : File open failed.<br>Please check your file type.");

            return $data;
        }
    }

    public function request_post(Request $request)
    {
        $r = [];
    	try{
            $price_list = $request->input('price_list');
            if(strlen($price_list)==0)
                throw new Exception("Price list can't empty.");
            $type = $request->input('type');
            if(empty($type))
                throw new Exception("Type can't empty.");
            $set_date = $request->input('set_date');
            $file = $request->file('file');
            $file_name = $file->getClientOriginalName();
            $file_type = $file->getClientOriginalExtension();
            $types_ary = array('xls','XLS','csv','CSV');
            if(!in_array($file_type,$types_ary)){
                throw new Exception("Sorry, File : $file_name<br> $file_type type not allowed");
            }
            
            $res = $this->importFile($request);
            if(isset($res[0]))
                $mode = isset($res[0][0])?'multi_sheets':'one_sheet';
            else
                $mode = 'empty';
                
            if($mode=='one_sheet'){
            
                $titles = [
                    'merchantprefix'=>1,
                    'upc'=>1,
                    'merchantsku'=>1,
                    'description'=>0,
                    'price'=>1,
                    'map'=>0,
                    'casepack'=>0,
                    'caseweight'=>0,
                    'msrp'=>0,
                    'qtyavailable'=>0
                ];

                $col_len = count($titles);
                
                //1.Clean format of file ($res),Skip key is null or '' and not exact 10
				foreach($res as $row_key => $row){
                    $key = 1;
                    $col_sum = 0;
                    foreach($row as $col_key =>$col_val){
                        $col_val = trim($col_val);
                        if($col_val==NULL)
                            $col_sum++;
                        
                        if($key>10){
                            if(empty($col_key)){
                                unset($res[$row_key][$col_key]);
                            }else{
                                throw new Exception("The format of the submitted price list is invalid,your columns is over 10.<br>Please download the price list file and follow its format.<br>Please note that it is required to have exact ".$col_len." columns in the price list."); 
                            }
                         }elseif(count($row)<10){
                            throw new Exception("The format of the submitted price list is invalid,your columns is under 10.<br>Please download the price list file and follow its format.<br>Please note that it is required to have exact ".$col_len." columns in the price list.");
                         }
                        if($col_sum==$col_len)
                            unset($res[$row_key]);        
                        $key++;
                    }
                }

                //check header str is valid or not
                $valid = 0;
                $msg = "";
                foreach($res[0] as $col_key => $col_val){
                    $col_key = strtolower($col_key);
                    if(array_key_exists($col_key,$titles)){
                        $valid++;
                    }else{
                        $msg .= $col_key.",";
                    }
                }
                if($valid!=$col_len){
                    $msg = substr($msg,0,-1);
                    throw new Exception("Sorry, an error occurs in File : ".$file_name."<br>\"".$msg."\" is an invalid header.<br>Please download the price list file and follow its format.");
                }

                //2.Check content of file
                $error_msg = "";
                $skip_idx_total_num = 2;
                $tmp_prefix = "";
                foreach($res as $line => &$row):
                    foreach($row as $col_key => &$col_val):
                            $col_val = trim($col_val);
                            //2-1.Check Mandatory cols
                            $key = 0;
                            if($col_key=='merchantprefix'){
                                if(strlen($col_val)!=0){
                                    if($tmp_prefix!='' && $tmp_prefix!=$col_val){
                                        $error_msg = "Multi Merchant prefix were found.<br>Only one is allowed for a single submission.";
                                    }    
                                    $tmp_prefix = $col_val;
                                }
                            }else if($col_key=='upc'){
                                $col_val = str_replace('A/','',$col_val);
                            }else if($col_key=='price' || $col_key=='map'){
                                if(preg_match('/([\d\.]+)/',$col_val,$m)){
                                        $col_val = $m[1];
                                }
                            }else if($col_key=='description'){
                                 $col_val = str_replace("\n"," ",$col_val);
                            }
                    endforeach;
                endforeach;
                if($tmp_prefix==''){
                    $error_msg = "No Merchant prefix is found.<br>Please fill in one at least.";
                }
                if(strlen($error_msg)!=0)
                    throw new Exception("Sorry, an error occurs in File : $file_name<br>".$error_msg);

                $merchant_prefix = $tmp_prefix;
                $csv = [];
                foreach($res as $line => $idx):
                        $csv[$line] = [
                            'price_list' => $price_list,
                            'type' => $type,
                            'camel_sales_rank_date' => $set_date,
                            'status' => "submitted",
                            'merchant_prefix' => $tmp_prefix,
                            'upc' => $idx['upc'],
                            'merchant_sku' => $idx['merchantsku'],
                            'description' => $idx['description'],
                            'price' => $idx['price'],
                            'map' => $idx['map'],
                            'case_pack' => $idx['casepack'],
                            'case_weight' => $idx['caseweight'],
                            'msrp' => $idx['msrp'],
                            'qty_available' => $idx['qtyavailable'],
                            'upload_datetime' => $this->time,
                            'user_id' => Auth::user()->id,
                            'send_to' => $request->input('send_to')
                        ];
                endforeach;
            }else if($mode=='multi_sheets'){
                throw new Exception("Sorry, an error occurs in File : ". $file_name."<br>The PSR platform dosen't support excel files containing multiple sheets/tabs.");
            }else if($mode=='empty'){
                throw new Exception("Sorry, File : $file_name<br>
                The content of the submitted file is empty.<br>
                At least one record is required.");
            }

            $this->insert_nums_data_to_db($csv,$this->test_opt);
            
		}catch(Exception $e){
            $r['error'] = substr($e->getMessage(),0,1024)."\n";
            Log::error($e->getMessage());
        }

        if(!isset($r['error'])){
            $r['status'] = 'success';
        }
        return $r;
    }


}
