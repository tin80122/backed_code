<?php
$headers = array(
    'Accept:application/json, text/javascript, */*; q=0.01',
    'Accept-Encoding:gzip, deflate',
    'Accept-Language:zh-TW,zh;q=0.9,en-US;q=0.8,en;q=0.7,zh-CN;q=0.6',
    'Connection:keep-alive',
    'Content-Length:669',
    'Content-Type:application/x-www-form-urlencoded; charset=UTF-8',
    'Cookie:wordpress_6652f2f7fe51fc9e21166b7ae4d25710=TiN80122%7C1519034243%7CVnAkDERQvx0QODvXXhjEVE3UMYoV8BdCGVTbsySDmOO%7C02e09d8542bd029b28f0ce4b19e7d864b51a82004e232398e125d847d6a89b16; PHPSESSID=1tmnaqiv26tipv7tjimffefoj3; wpcc_variant_6652f2f7fe51fc9e21166b7ae4d25710=zh-tw; wordpress_logged_in_6652f2f7fe51fc9e21166b7ae4d25710=TiN80122%7C1519034243%7CVnAkDERQvx0QODvXXhjEVE3UMYoV8BdCGVTbsySDmOO%7Ce7a246621b7a1181cc22aa6f4035a98a5d0eb2719b8f8829dc39e3bd4290b6ce; wp-settings-time-9139=1517824654; ee_cookie_test=ect5a7847d7b0de79.53066176',
    'Host:pennytai.org',
    'Origin:http://pennytai.org',
    'Referer:http://pennytai.org/registration-checkout/?uts=1517826354',
    'User-Agent:Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/64.0.3282.140 Safari/537.36',
    'X-Requested-With:XMLHttpRequest'
);

$url = "http://pennytai.org/wp-admin/admin-ajax.php";
$data = [
    'ee_reg_qstn[1325][fname]'=>'王曉明',
    'ee_reg_qstn[1325][email]'=>'123456@gmail.com',
    'ee_reg_qstn[1564][phone]:0952112345',
    'ee-spco-attendee_information-reg-step-form[1-2dae8d207373caaca82fba1c94fc60a5][additional_attendee_reg_info]:1',
    'ee-spco-attendee_information-reg-step-form[1-2dae8d207373caaca82fba1c94fc60a5][primary_registrant]:1-2dae8d207373caaca82fba1c94fc60a5',
    'action'=>'process_reg_step',
    'next_step'=>'finalize_registration',
    'e_reg_url_link'=>'',
    'revisit'=>'',
    'process_form_submission'=>1,
    'ee_front_ajax'=>1,
    'noheader'=>true,
    'step'=>'attendee_information',
    'EESID'=>'4ad5e3d0d06423d612e114afb1fe2499',
    'generate_reg_form'=>1,
    'revisit'=>'',
    'e_reg_url_link'=>''
];


//exit('123');
for($i=0;$i<=5000;$i++){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, urlencode($url));
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER,$headers);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    $output = curl_exec($ch);
    curl_close($ch);

    if($output==false){
        echo 'No.'.$i.': false<br>';
        continue;
    }else{
        var_dump($output);
        break;
    }
    sleep(1);
}

?>