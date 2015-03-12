<?php
$cms_cfg['creditcard']['MerchantNumber'] = "";//特店代碼
$cms_cfg['creditcard']['params']['ApproveFlag'] = "1";   //授權指標，授權指標預設為1
$cms_cfg['creditcard']['params']['DepositFlag'] = "1";   //請款指標，授權指標預設為1
$cms_cfg['creditcard']['params']['Englishmode'] = "0";   //中英文版本指標，0為中文版，1為英文版，授權指標預設為0
$cms_cfg['creditcard']['params']['iphonepage'] = "0";    //手機刷卡頁版本指標，0 為一般電腦版， 1 為iPhone版，預設值為0
$cms_cfg['creditcard']['params']['OrderURL'] = "";      //訂單回傳網址，授權完成後，用以接收回傳結果之頁面，由商家提供
$cms_cfg['creditcard']['params']['ReturnURL'] = "";     //交易回傳網址，交易完成後，用以顯示交易結果之頁面，由商家提供
$cms_cfg['creditcard']['params']['op'] = "AcceptPayment";  //交易模式
$cms_cfg['creditcard']['code'] = "";//驗證碼
$cms_cfg['creditcard']['exe_mode'] = "testing";  //執行模式: testing or running
?>