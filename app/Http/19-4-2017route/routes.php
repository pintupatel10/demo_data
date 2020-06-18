<?php

Route::auth();
Route::get('dashboard', 'DashboardController@index');

Route::get('/', function () {
    return redirect('dashboard');
});

// --------CMS--------------//

Route::group(['prefix' => '{project_id}'], function($project_id){
    Route::resource('users', 'UserController');
});

Route::get('student/import', 'StudentController@import_excel');
Route::get('student/school_class/{id}', 'StudentController@school_class');
Route::get('student/export', 'StudentController@export');
Route::post('student/import_data', 'StudentController@import_data');

Route::get('staff/import', 'StaffController@import_excel');
Route::get('staff/export', 'StaffController@export');
Route::post('staff/import_data', 'StaffController@import_data');

/* For API */

Route::post('api/user/login','api\UserController@login');
Route::post('api/user/get_all_school','api\UserController@get_all_school');
Route::post('api/user/get_school_detail','api\UserController@get_school_detail');
Route::post('api/user/get_classes','api\UserController@get_classes');
Route::post('api/user/get_students','api\UserController@get_students');
//Route::post('api/user/get_present_absent','api\UserController@get_present_absent');
Route::post('api/user/get_year_report','api\UserController@get_year_report');
Route::post('api/user/get_month_report','api\UserController@get_month_report');





//Route::post('api/user/login','api\StaffController@login');


Route::post('api/user/getdata','api\StaffController@getdata');
Route::post('api/user/add_attendance','api\StaffController@add_attendance');

Route::get('parents/import', 'ParentsController@import_excel');
Route::get('parents/export', 'ParentsController@export');
Route::post('parents/import_data', 'ParentsController@import_data');

Route::resource('users', 'UserController');
Route::resource('school', 'SchoolController');
Route::resource('class', 'ClassController');
Route::resource('student', 'StudentController');
Route::resource('staff', 'StaffController');
Route::resource('parents', 'ParentsController');
Route::resource('division', 'DivisionController');
Route::resource('device', 'DeviceController');
Route::resource('calendar', 'CalendarController');
Route::post('attendance/staff', 'AttendanceController@staff_attendance');
Route::resource('attendance','AttendanceController');
Route::resource('notification','NotificationController');

Route::resource('report','ReportController');
Route::post('report/export','ReportController@export');


Route::get('attendance/get_classes/{id}','AttendanceController@get_classes');
Route::get('attendance/get_division/{class_id}/{school_id}','AttendanceController@get_division');
Route::get('attendance/get_student/{class_id}/{school_id}','AttendanceController@get_student');

Route::get('class/get_classes/{id}','ClassController@get_classes');
Route::get('division/division/{id}','DivisionController@product_line');
Route::get('parents/school_students/{school_id}','ParentsController@school_students');

Route::get('/clear-cache', function() {
    $exitCode = Artisan::call('cache:clear');
});

