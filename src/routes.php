<?php

//page not found
Route::get('/404', function () {
    return abort(404);
});

//create single table CRUD
Route::get('autocrud','Hiren\Autocrud\Controllers\SingleTableController@selectTable');
Route::get('createView','Hiren\Autocrud\Controllers\SingleTableController@createView');
Route::get('pageLayout','Hiren\Autocrud\Controllers\SingleTableController@pageLayout');
Route::get('createLayout','Hiren\Autocrud\Controllers\SingleTableController@createLayout');
Route::get('createFormField','Hiren\Autocrud\Controllers\SingleTableController@createFormField');
Route::get('getFormFields','Hiren\Autocrud\Controllers\SingleTableController@getFormFields');
Route::post('designForm','Hiren\Autocrud\Controllers\SingleTableController@designForm');
Route::post('updateForm','Hiren\Autocrud\Controllers\SingleTableController@updateForm');
Route::post('generateView','Hiren\Autocrud\Controllers\SingleTableController@generateView');
Route::get('getColsList','Hiren\Autocrud\Controllers\SingleTableController@getColsList');
Route::get('getJoinFields','Hiren\Autocrud\Controllers\SingleTableController@getJoinFields');


//create multi table CRUD
Route::post('mGenerateTable','Hiren\Autocrud\Controllers\MultiTableController@generateTable');
Route::get('multiTableCRUD','Hiren\Autocrud\Controllers\MultiTableController@index');
Route::get('mformFields','Hiren\Autocrud\Controllers\MultiTableController@mformFields');
Route::post('mGenerateView','Hiren\Autocrud\Controllers\MultiTableController@mGenerateView');
Route::get('mCreateLayout','Hiren\Autocrud\Controllers\MultiTableController@mCreateLayout');
Route::get('mcommonFields/{refno}','Hiren\Autocrud\Controllers\MultiTableController@commonFields')->name('mCommonFields');
Route::get('mRemoveDuplicateAndSort','Hiren\Autocrud\Controllers\MultiTableController@mRemoveDuplicateAndSort')->name('mRemoveDuplicateAndSort');
Route::get('mStoreCommon','Hiren\Autocrud\Controllers\MultiTableController@mStoreCommon');
Route::post('mDesignForm','Hiren\Autocrud\Controllers\MultiTableController@mDesignForm');


//display multi table data
Route::get('multiTables','Hiren\Autocrud\Controllers\DisplayDataController@multiTables');
Route::post('generateTable','Hiren\Autocrud\Controllers\DisplayDataController@generateTable');
Route::post('createExtraFields','Hiren\Autocrud\Controllers\DisplayDataController@createExtraFields');
Route::get('commonFields/{refno}','Hiren\Autocrud\Controllers\DisplayDataController@commonFields')->name('tcommonFields');
Route::get('storeCommon','Hiren\Autocrud\Controllers\DisplayDataController@storeCommon');
Route::post('createMultiTableView','Hiren\Autocrud\Controllers\DisplayDataController@createMultiTableView');
