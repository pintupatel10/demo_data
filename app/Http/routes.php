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
Route::post('api/user/reset_password','api\UserController@reset_password');
Route::post('api/user/forget_password','api\UserController@forget_password');

Route::post('api/user/get_all_school','api\UserController@get_all_school');
Route::post('api/user/get_school_detail','api\UserController@get_school_detail');
Route::post('api/user/get_classes','api\UserController@get_classes');
Route::post('api/user/get_classes_simple','api\UserController@get_classes_simple');
Route::post('api/user/get_division','api\UserController@get_division');
Route::post('api/user/get_students','api\UserController@get_students');
//Route::post('api/user/get_present_absent','api\UserController@get_present_absent');
Route::post('api/user/get_year_report','api\UserController@get_year_report');
Route::post('api/user/get_month_report','api\UserController@get_month_report');
Route::post('api/user/get_send_notification','api\UserController@get_send_notification');
Route::post('api/user/get_receive_notification','api\UserController@get_receive_notification');

Route::post('api/user/get_my_child','api\UserController@get_my_child');
Route::post('api/user/get_device','api\UserController@get_device');

Route::post('api/user/get_device_report_student','api\UserController@get_device_report_student');
Route::post('api/user/get_attendance_report','api\UserController@get_attendance_report');
Route::post('api/user/get_student_attendance_for_teacher','api\UserController@get_student_attendance_for_teacher');
Route::post('api/user/send_notification_sms','api\UserController@send_notification_sms');

Route::post('api/user/get_present_student_list','api\UserController@get_present_student_list');
Route::post('api/user/get_absent_student_list','api\UserController@get_absent_student_list');
Route::post('api/user/get_all_student_list','api\UserController@get_all_student_list');
Route::post('api/user/make_student_present_leave','api\UserController@make_student_present_leave');
Route::post('api/user/make_student_absent','api\UserController@make_student_absent');


Route::post('api/user/get_present_staff_list','api\UserController@get_present_staff_list');
Route::post('api/user/get_absent_staff_list','api\UserController@get_absent_staff_list');
Route::post('api/user/get_all_staff_list','api\UserController@get_all_staff_list');
Route::post('api/user/make_staff_present_leave','api\UserController@make_staff_present_leave');
Route::post('api/user/make_staff_absent','api\UserController@make_staff_absent');

Route::post('api/user/test_notification','api\UserController@test_notification');
Route::post('api/user/test_sms','api\UserController@test_sms');

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
Route::get('child', 'DashboardController@child');
Route::resource('parents', 'ParentsController');
Route::resource('division', 'DivisionController');
Route::resource('device', 'DeviceController');
Route::resource('calendar', 'CalendarController');
Route::post('attendance/staff', 'AttendanceController@staff_attendance');
Route::resource('attendance','AttendanceController');
Route::resource('notification','NotificationController');

//Route::post('report/get_report','ReportController@get_report');
Route::get('report/export','ReportController@export');
Route::resource('report','ReportController');

Route::get('attendance/get_classes/{id}','AttendanceController@get_classes');
Route::get('attendance/get_division/{class_id}/{school_id}','AttendanceController@get_division');
Route::get('attendance/get_student/{class_id}/{school_id}','AttendanceController@get_student');

Route::get('class/get_classes/{id}','ClassController@get_classes');
Route::get('division/division/{id}','DivisionController@product_line');

Route::get('parents/get_students/{str}/{selectedValues}','ParentsController@get_students');

Route::get('parents/school_students/{school_id}','ParentsController@school_students');

Route::get('report/get_device/{school_id}','ReportController@get_device');
Route::get('report/get_staff_teacher/{school_id}','ReportController@get_staff_teacher');

Route::get('/clear-cache', function() {
    $exitCode = Artisan::call('cache:clear');
});

