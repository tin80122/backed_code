/*RESTful API Examples*/

Route::get('/app/psrs/{resubmit?}',function($resubmit=1){
    return view('psrs',array('resubmit'=>$resubmit));
});
Route::post('/app/psrs/post','PsrsController@psrs_post');
Route::post('/app/psrs/log','PsrsController@psrs_log');
Route::post('/app/psrs/log_detail','PsrsController@psrs_log_detail');
Route::post('/app/psrs/actions','PsrsController@actions');

Route::post('/app/aim/upload','AimController@upload_file');
Route::get('/app/aim/history','AimController@history');
