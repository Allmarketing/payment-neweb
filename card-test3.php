<?php
require_once "TP/class.TemplatePower.inc.php";
require_once "class/model/order/payment/neweb.php"; 
require_once "conf/config.inc.php";
require_once "conf/creditcard.php";
require_once "conf/database.php";
include_once("libs/libs-mysql.php");
$db = new DB($cms_cfg['db_host'],$cms_cfg['db_user'],$cms_cfg['db_password'],$cms_cfg['db_name'],$cms_cfg['tb_prefix']);
$tpl = new TemplatePower("test3.html");
$tpl->prepare();
$card = new Model_Order_Payment_Neweb($cms_cfg['creditcard']);
//$sql = $card->update_order($db,$_POST);
$tpl->gotoBlock("_ROOT");
//$tpl->assign("UPDATE_ORDER_SQL",$sql);
foreach($_POST as $k=>$v){
    $tpl->assign("MSG_".$k,$v);
    if($k=="PRC"){
        $tpl->assign("MSG_".$k."_STR",  Model_Order_Payment_Returncode_Esun::$code[$_POST[$k]]);
    }
}
ob_start();
print_r($_POST);
file_put_contents("tmp/returnData0.txt", ob_get_clean());
$tpl->printToScreen();


