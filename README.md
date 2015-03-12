payment-neweb
============

藍新信用卡串接


前置設定
---------------
1.設定conf/creditcard.php裡的 $cms_cfg['creditcard']['MerchantNumber'] (特店編號)和 $cms_cfg['creditcard']['code']。<br/>
2.設定conf/creditcard.php第8行的$cms_cfg['creditcard']['params']['OrderURL']、$cms_cfg['creditcard']['params']['ReturnURL']，改為測試環境接收授權結果的url<br/>
3.設定conf/creditcard.php第11行的$cms_cfg['exe_mode']，設為testing(測試)或running(正式)，主要影響到會使用哪一個串接網址<br/>
4.依實際環境修改documents/fields_for_order.sql，為訂單資料表加上授權寫入的相關欄位<br/>


測試流程
---------------
1.執行card-test1.php，輸入訂單號碼及訂單價格，以及國旅卡、紅利折抵選項.<br/>
2.前述訂單號碼及訂單價格由card-test2.php接收後，依documents/mPP 4 0 2 技術參考手冊 - NPM.pdf第12頁的一般商店傳送參數說明，以post方式傳給藍新伺服器.<br/>
3.之後會進入線上刷頁頁面，此時如果要測紅利折抵，請參考documents/mPP 4 0 2 技術參考手冊 - NPM.pdf第18頁的卡號<br/>
4.送出刷卡頁後，金流端會先將結果丟給$cms_cfg['creditcard']['params']['OrderURL']，這裡可進行修改訂單的狀態。
5.金流端在判斷與$cms_cfg['creditcard']['params']['OrderURL']連結是正常後(HTTP Code為200)，會再丟結果給$cms_cfg['creditcard']['params']['ReturnURL']。
5.$cms_cfg['creditcard']['params']['ReturnURL']輸出回傳的資訊.


api說明
---------------

### Model_Order_Payment_Neweb::__construct($config)

    1.$config: 即conf/credictcard.php裡的$cms_cfg['creditcard'].


### Model_Order_Payment_Neweb::checkout($o_id,$total_price,$extra_info=array())

    1.$o_id:訂單號碼.
    2.$total_price:訂單價格.
    3.$extra_info:額外的欄位，預設是空陣列，也就是不加新欄位，如果要加新欄位，請以關聯式陣列輸入，例如: array('email'=>'xxxx@some.domain','tel'=>'88881888').


### Model_Order_Payment_Neweb::update_order($db,$result)

    1.$db: 即libs/libs-mysql.php類別的實體物件。請使用本專案的libs/libs-mysql.php，因為有使用到新增的prefix().
    2.$result: 即藍新伺服器回傳的結果，即$_POST.
> 說明:測試流程是以此方法傳回更新訂單的sql。實際上因為已傳入$db，所以可以直接在方法裡面直接執行查詢.<br/>
> 　　 除了訂單編號重複的錯誤之外(PRC=8)，無論授權成功或失敗都留有更新訂單的敘述.<br/>
>     增加更新訂單後傳回該筆訂單記錄的敘述。正式套用時可解除註解。
