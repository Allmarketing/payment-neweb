<?php
require_once "returncode/neweb.php";
class Model_Order_Payment_Neweb {
    //put your code here
    protected $config;
    protected $code;
    protected $mode;
    protected $codedata = array();
    protected $url = array(
        'testing' => "https://testmaple2.neweb.com.tw/NewebmPP/cdcard.jsp",
        'running' => "",
    );
    protected $template = "templates/ws-cart-card-transmit-tpl.html";
    function __construct($config) {
        $this->config = $config;
        $this->code = $config['code'];
        $this->mode = $config['exe_mode'];
        $this->codedata['MerchantNumber'] = $config['MerchantNumber'];
        $this->codedata = array_merge($this->codedata,$this->config['params']);
    }
    //結帳
    function checkout($o_id,$total_price,$extra_info=array()){
        $this->codedata['OrderNumber'] = strtoupper($o_id);
        $this->codedata['Amount'] = $total_price;
        if(!empty($extra_info)){
            foreach($extra_info as $k => $v){
                $this->codedata[$k] = $v;
//                if(!isset($this->codedata[$k])){
//                    $this->codedata[$k] = $v;
//                }
            }
        }
        $tpl = new TemplatePower($this->template);
        $tpl->prepare();
        foreach($this->codedata as $k => $v){
            $tpl->newBlock("CARD_FIELD_LIST");
            $tpl->assign(array(
                "TAG_KEY"   => $k,
                "TAG_VALUE" => $v,
            ));
        }
        $code = $this->make_code($this->codedata);
        $tpl->assignGlobal("TAG_INPUT_STR",$code[0]);
        $tpl->newBlock("CARD_FIELD_LIST");
        $tpl->assign(array(
            "TAG_KEY"   => 'checksum',
            "TAG_VALUE" => $code[1]
        ));
        $tpl->assignGlobal("AUTHORIZED_URL",$this->url[$this->mode]);
        $tpl->printToScreen();
        die();
    }
    //製作押碼
    function make_code($codedata,$direction='out'){
        if($direction=='out'){
            $input_str = $codedata['MerchantNumber'].$codedata['OrderNumber'].$this->code.$codedata['Amount'];
        }elseif($direction=='in'){
            $input_str = $codedata['MerchantNumber'].$codedata['OrderNumber'].$codedata['PRC'].$codedata['SRC'].$this->code.$codedata['Amount'];
        }
        return array($input_str,md5($input_str));
    }
    //更新訂單
    function update_order(Dbtable_Order $db,$result){
        $oid = $result['OrderNumber'];
        $result['o_id'] = $oid;
        if($result['PRC']=='0'){ //交易成功
            if($this->validate($result)){
                //更新訂單資料
                $result['o_status'] = 1;
                //寄發繳款通知信
                $tpl = App::getHelper('main')->get_mail_tpl('receipt-notification');
                $tpl->newBlock("SHOPPING_ORDER");
                $tpl->assign("MSG_O_ID",$result['o_id']);
                $mailContent = $tpl->getOutputContent();
                $order = $db->getData($oid)->getDataRow("o_email");
                App::getHelper('main')->ws_mail_send(App::getHelper('session')->sc_email,$order['o_email'],$mailContent,"繳款通知","","",null,1);                
            }else{
                throw new Exception("return result doesn't valiated!");
            }
        }else{
            //更新訂單狀態
            if($result['PRC']!='8'){ //錯誤原因非訂單編號重複
                $result['o_status'] = 21;
            }
        }
        $db->writeData($result, "", 'update');
        //$db->query($sql,true);
        //$sql = "select * from ".$db->prefix("order")." where o_id='".$oid."'";
        //return $db->query_firstRow($sql,true);
//        return $sql;
    }
    //驗證回傳結果
    function validate($result){
        $code = $this->make_code($result,'in');
        return ($result['CheckSum']==$code[1]);
    }
    
}
