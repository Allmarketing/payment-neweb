<?php
require_once "TP/class.TemplatePower.inc.php";
require_once "class/model/order/payment/neweb.php"; 
require_once "conf/creditcard.php";
if($_POST){
    $card = new Model_Order_Payment_Neweb($cms_cfg['creditcard']);
    $extra_info = array();
    if($_POST['use_ctravel']){
        $extra_info['ctravel_startdate']=$_POST['ctravel_startdate'];
        $extra_info['ctravel_enddate']=$_POST['ctravel_enddate'];
        $extra_info['ctravel_zipcode']=$_POST['ctravel_zipcode'];
    }
    if($_POST['use_redemption']){
        $extra_info['redemption']=1;
    }
    if($_POST['noDeposit']){
        $extra_info['DepositFlag']=0;
    }
    $card->checkout($_POST['orderid'], $_POST['price'],$extra_info);
}

