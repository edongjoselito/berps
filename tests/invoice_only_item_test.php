<?php
// Isolate the endpoint with in-memory persistence; no real invoice writes.
$source = file_get_contents(__DIR__ . '/../application/controllers/Page.php');
$start = strpos($source, '  function addInvoiceOnlyItem()');
$end = strpos($source, '  function updateInvoiceDates()', $start);
function redirect($url) {}
function show_error($message, $code) { throw new RuntimeException((string) $code); }
function show_404() { throw new RuntimeException('404'); }
class SeasonalStore {
    public $invoice, $payload, $scope = array(), $items, $committed = false, $rolledBack = false, $ok = true;
    function __construct() { $this->invoice = (object) array('orderID'=>4059,'recurringTemplateID'=>3997,'recurringFrequency'=>'monthly','invoiceStat'=>'active','TotalDue'=>1000,'AmountPaid'=>200); }
    function getInvoiceByOrderID($id,$tenant) { return $id===4059 && $tenant===7 ? $this->invoice : null; }
    function trans_begin() {}
    function query($sql,$args) { if ($args!==array(4059,7)) throw new RuntimeException('Wrong lock scope'); return $this; }
    function row() { return $this->invoice; }
    function where($key,$value) { $this->scope[$key]=$value; return $this; }
    function update($table,$data) { $this->payload=$data; }
    function trans_status() { return $this->ok; }
    function trans_commit() { $this->committed=true; return true; }
    function trans_rollback() { $this->rolledBack=true; }
}
class SeasonalInput {
    public $verb='post', $data=array('id'=>4059,'description'=>"Seasonal service\nAdditional work",'quantity'=>'2','unit'=>'quarter','unitPrice'=>'150.50');
    function method(){return $this->verb;}
    function post($key){return $this->data[$key]??null;}
}
class SeasonalSession {
    public $messages=array();
    function userdata($key){return 7;}
    function set_flashdata($key,$message){$this->messages[$key]=$message;}
}
eval('class SeasonalSubject {
 public $input,$session,$CashModel,$db,$admin=true;
 function _getInvoiceOnlyUnitChoices($settingsID){return array("each","month","year","quarter","pcs");}
 function _is_admin_user(){return $this->admin;}
 function _is_staff_user(){return false;}
 function _can_current_staff_access_invoice($invoice){return true;}
 function _normalizeRecurringFrequency($value){return $value;}
 function _normalizeInvoiceItemAmount($value){return is_numeric($value)?round((float)$value,2):null;}
 function _ensureInvoiceItemsTable(){}
 function _loadInvoiceItems($invoice,$tenant){return array(array("itemDescription"=>"Base service","lineTotal"=>1000));}
 function _persistInvoiceItems($id,$tenant,$items){if($id!==4059 || $tenant!==7)throw new RuntimeException("Template write attempted");$this->db->items=$items;}
'.substr($source,$start,$end-$start).'}');
foreach(array('child','template','invalid','denied','get','failure','standalone','invalid-unit') as $case){
 $s=new SeasonalSubject; $s->input=new SeasonalInput; $s->session=new SeasonalSession; $s->db=$s->CashModel=new SeasonalStore;
 if($case==='template')$s->db->invoice->recurringTemplateID=0;
 if($case==='standalone'){$s->db->invoice->recurringTemplateID=0;$s->db->invoice->recurringFrequency='none';}
 if($case==='invalid')$s->input->data['unitPrice']='-1';
 if($case==='invalid-unit')$s->input->data['unit']='invalid';
 if($case==='denied')$s->admin=false;
 if($case==='get')$s->input->verb='get';
 if($case==='failure')$s->db->ok=false;
 $error=null;try{$s->addInvoiceOnlyItem();}catch(RuntimeException $e){$error=$e->getMessage();}
 if($error!== (array('denied'=>'403','get'=>'405')[$case]??null))throw new RuntimeException('Unexpected response '.$case);
 if(in_array($case,array('child','standalone'))){
  if(!$s->db->committed || count($s->db->items)!==2 || $s->db->items[1]['itemDurationUnit']!=='quarter' || $s->db->payload!==array('TotalDue'=>1301.0,'Balance'=>1101.0)
   || $s->db->scope!==array('orderID'=>4059,'settingsID'=>7))throw new RuntimeException('Bad invoice-only save');
 }elseif($case==='failure'){
  if(!$s->db->rolledBack || $s->db->committed || !isset($s->session->messages['danger']))throw new RuntimeException('Failed save did not roll back');
 }elseif($s->db->payload!==null || $s->db->items!==null){throw new RuntimeException('Rejected request wrote data');}
}
echo "Passed 8 invoice-only item checks.\n";
