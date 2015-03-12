<?php
require_once "returncode/notcreditcard.php";
class Model_Order_Payment_Neweb_Notcreditcard {
    //put your code here
    protected $config;
    protected $code;
    protected $mode;
    protected $codedata = array();
    //串接網址
    protected $url = array(
        'testing' => "http://testmaple2.neweb.com.tw/CashSystemFrontEnd/Payment",
        'running' => "",
    );
    //查詢網址
    protected $qurl = array(
        'testing' => "http://testmaple2.neweb.com.tw/CashSystemFrontEnd/Query",
        'running' => "",
    );
    protected $template = "templates/ws-cart-card-transmit-tpl.html";
    protected $qtemplate = "templates/ws-cart-card-query-tpl.html";
    function __construct($config) {
        $this->config = $config;
        $this->code = $config['code'];
        $this->mode = $config['exe_mode'];
        $this->codedata['merchantnumber'] = $config['merchantnumber'];
        $this->codedata = array_merge($this->codedata,$this->config['params']);
    }
    //結帳
    function checkout($o_id,$total_price,$extra_info=array()){
        $this->codedata['ordernumber'] = strtoupper($o_id);
        $this->codedata['amount'] = $total_price;
        if(!empty($extra_info)){
            foreach($extra_info as $k => $v){
                $this->codedata[$k] = $v;
//                if(!isset($this->codedata[$k])){
//                    $this->codedata[$k] = $v;
//                }
            }
        }
        $code = $this->make_code($this->codedata);
        $this->codedata['hash'] = $code[1];
        if($this->codedata['returnvalue']==1){
            $ch  = curl_init();
            curl_setopt($ch, CURLOPT_URL, $this->url[$this->mode]);
//            curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-type: multipart/form-data"));
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 15);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($this->codedata));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $returnValue = curl_exec($ch);
            parse_str($returnValue,$result);
            if($this->validate($result)){
                App::getHelper('session')->paymentInfo = $result;
                $query = array(
                    'status' => 'OK',
                    'pno' => $o_id,
                );
                //寫入訂單付款資料表
                $result['o_id'] = $result['ordernumber'];
                App::getHelper('dbtable')->order_paymentinfo->insert($result);
            }else{
                $query = array(
                    'status' => 'FAIL',
                    'pno' => $o_id,
                );
                //設定訂單為授權失敗
                $order = array(
                    'o_id'     => $o_id,
                    'o_status' => 21,
                );
                App::getHelper('dbtable')->order->writeData($order);
            }
            header("location: ".$this->codedata['nexturl']."?".  http_build_query($query));
        }else{
            $tpl = new TemplatePower($this->template);
            $tpl->prepare();
            foreach($this->codedata as $k => $v){
                if(!empty($v)){
                    $tpl->newBlock("CARD_FIELD_LIST");
                    $tpl->assign(array(
                        "TAG_KEY"   => $k,
                        "TAG_VALUE" => $v,
                    ));
                }
            }
//            $tpl->assignGlobal("TAG_INPUT_STR",$code[0]);
//            $tpl->newBlock("CARD_FIELD_LIST");
//            $tpl->assign(array(
//                "TAG_KEY"   => 'hash',
//                "TAG_VALUE" => $code[1]
//            ));
            $tpl->assignGlobal("AUTHORIZED_URL",$this->url[$this->mode]);
            $tpl->printToScreen();
        }
        die();
    }
    //查詢
    function query($o_id,$total_price,$extra_info=array()){
        $this->codedata['ordernumber'] = strtoupper($o_id);
        $this->codedata['amount'] = $total_price;
        if(!empty($extra_info)){
            foreach($extra_info as $k => $v){
                $this->codedata[$k] = $v;
            }
        }
        $tpl = new TemplatePower($this->template);
        $tpl->prepare();
        foreach($this->codedata as $k => $v){
            if(!empty($v)){
                $tpl->newBlock("CARD_FIELD_LIST");
                $tpl->assign(array(
                    "TAG_KEY"   => $k,
                    "TAG_VALUE" => $v,
                ));
            }
        }
        $code = $this->make_code($this->codedata);
        $tpl->assignGlobal("TAG_INPUT_STR",$code[0]);
        $tpl->newBlock("CARD_FIELD_LIST");
        $tpl->assign(array(
            "TAG_KEY"   => 'hash',
            "TAG_VALUE" => $code[1]
        ));
        $tpl->assignGlobal("AUTHORIZED_URL",$this->qurl[$this->mode]);
        $tpl->printToScreen();
        die();
    }    
    //製作押碼
    function make_code($codedata,$direction='out'){
        if($direction=='out'){
            $input_str = $codedata['merchantnumber'].$this->code.$codedata['amount'].$codedata['ordernumber'];
        }elseif($direction=='in'){
            $input_str = "rc=0&bankid=".$codedata['bankid']."&virtualaccount=".$codedata['virtualaccount']."&amount=".$codedata['amount']."&merchantnumber=".$codedata['merchantnumber']."&ordernumber=".$codedata['ordernumber']."&code=".$this->code;
        }elseif($direction=='in2'){
            $input_str = "merchantnumber=".$codedata['merchantnumber'].
                    "&ordernumber=".$codedata['ordernumber'].
                    "&serialnumber=".$codedata['serialnumber'].
                    "&writeoffnumber=".$codedata['writeoffnumber'].
                    "&timepaid=".$codedata['timepaid'].
                    "&paymenttype=".$codedata['paymenttype'].
                    "&amount=".$codedata['amount'].
                    "&tel=".$codedata['tel'].$this->code;
        }
        return array($input_str,md5($input_str));
    }
    //更新訂單
    function update_order(Dbtable_Order $db,$result){
        if($this->validatePay($result)){
            $order = $db->getData($result['ordernumber'])->getDataRow(" o_id,o_email ");
            $order['o_status']=1;
            $db->writeData($order);
            //更新付款資訊
            $result['o_id'] = $result['ordernumber'];
            App::getHelper('dbtable')->order_paymentinfo->writeData($result);    
            //寄發繳款通知信
            $tpl = App::getHelper('main')->get_mail_tpl('receipt-notification');
            $tpl->newBlock("SHOPPING_ORDER");
            $tpl->assign("MSG_O_ID",$result['ordernumber']);
            $mailContent = $tpl->getOutputContent();
            App::getHelper('main')->ws_mail_send(App::getHelper('session')->sc_email,$order['o_email'],$mailContent,"繳款通知","","",null,1);
//            $db->query($sql,true);
//            $sql = "select * from ".$db->prefix("order")." where o_id='".$oid."'";
//            return $db->query_firstRow($sql,true);
//            return $sql;
        }
    }
    //驗證回傳結果
    function validate($result){
        $code = $this->make_code($result,'in');
        return ($result['checksum']==$code[1]);
    }
    //驗證繳款通知
    function validatePay($result){
        $code = $this->make_code($result,'in2');
        return ($result['hash']==$code[1]);
    }
    
}
