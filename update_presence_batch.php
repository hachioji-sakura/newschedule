<?php
// This routine update specified year month schedule from 
// tbl_teacher_presence_report into tbl_schedule_onetime.
// syntax: php update_presence_batch.php YEAR MONTH  

//ini_set( 'display_errors', 0 );

$request_year = $argv[1];
if (!$request_year){
	$err_flag = true;
	$message = 'Syntax error: correct syntax is php update_presence_batch.php YEAR MONTH ';
	goto error_label;
}

$request_month = $argv[2];
if (!$request_month){
	$err_flag = true;
	$message = 'Syntax error: correct syntax is php update_presence_batch.php YEAR MONTH ';
	goto error_label;
}

require_once "./const/const.inc";
require_once "./func.inc";
require_once("./const/login_func.inc");
require_once("./const/token.php");

//ini_set('include_path', CLIENT_LIBRALY_PATH);
//set_time_limit(60);
//define(API_TOKEN, '7511a32c7b6fd3d085f7c6cbe66049e7');

mb_regex_encoding("UTF-8");
			// 科目リストの取得
$now = date('Y-m-d H:i:s');

define('ATTEND_1','出席');
define('ATTEND_2','Attend');
define('ABSENT1_1','休み1');
define('ABSENT1_2','Absent1');
define('NOCLASS_1','休み1休講');
define('NOCLASS_2','Absent1 No class');
define('ABSENT2_1','休み2');
define('ABSENT2_2','Absent2');
define('OVERLIMIT_1','休み2規定回数以上');
define('OVERLIMIT_2','Absent2 over limit');
define('TODAY_1','休み2当日');
define('TODAY_2','Absent2 Today');
define('ALTERNATE_1','振替');
define('ALTERNATE_2','make-up');

define('CONST_RANGE','～');
define('CONST_TODAY','当日');
define('CONST_OVERLIMIT','規定回数以上');
define('CONST_NOCLASS','休講');
define('CONST_DAY','日');
define('CONST_MONTH','月');

						// Getting teacher_presence_report.
$sql = "SELECT teacher_id,year,month,date,time,member_no,name,presence,insert_timestamp,update_timestamp FROM tbl_teacher_presence_report ";
$sql = $sql ." WHERE year=? AND month=? ";

$stmt = $db->prepare($sql);
$stmt->bindValue(1, $request_year, PDO::PARAM_INT);
$stmt->bindValue(2, $request_month, PDO::PARAM_INT);
$stmt->execute();
$presence_report_array = $stmt->fetchAll(PDO::FETCH_ASSOC);
//var_dump($presence_report_array);

foreach ( $presence_report_array as $presence_report_row ) {

       	$user_id_onetime = (int)$presence_report_row['member_no'] ;	// converting member_no into user_id for learning management system.
       	$teacher_id_onetime = 100000 + (int)$presence_report_row['teacher_id'] ;	// converting teacher_id for learning management system.

       	$date_str = $presence_report_row['date'] ;
						// 日を取り除く
	$date_str = mb_ereg_replace(CONST_DAY,'',$date_str);
						// 月をハイフンで置き換える
	$date_str = mb_ereg_replace(CONST_MONTH,'-',$date_str);
						// 先頭に'YYYY-'をつける
	$year_str = (string)$request_year;
	$year_str = $year_str . '-' ;
	$date_str = $year_str . $date_str ;

	$start_end_time_str = $presence_report_row['time'] ;
//	$match_num = preg_match( "/～/", $start_end_time_str, $matches, PREG_OFFSET_CAPTURE);

	$start_time_str = mb_substr($start_end_time_str,0,5);
	$start_time_str = $start_time_str . ':00'; 	// making timestamp string.

	$end_time_str = mb_substr($start_end_time_str,8,5);
	$end_time_str = $end_time_str . ':00';		// making timestamp string.
//var_dump($date_str);
//var_dump($start_time_str);
//var_dump($end_time_str);

	$sql = "SELECT id,cancel,cancel_reason FROM tbl_schedule_onetime ";
//	$sql = "SELECT id,cancel,cancel_reason FROM tbl_schedule_onetime_test ";
	$sql = $sql ." WHERE user_id=? and teacher_id=? and ymd=? AND starttime=? AND endtime=? ";
	$stmt = $dbh->prepare($sql);
	$stmt->bindValue(1, $user_id_onetime, PDO::PARAM_INT);
	$stmt->bindValue(2, $teacher_id_onetime, PDO::PARAM_INT);
	$stmt->bindValue(3, $date_str, PDO::PARAM_STR);		// string attribute.
	$stmt->bindValue(4, $start_time_str, PDO::PARAM_STR);	// string attribute.
	$stmt->bindValue(5, $end_time_str, PDO::PARAM_STR);	// string attribute.
//var_dump($date_str);
//var_dump($start_time_str);
//var_dump($end_time_str);
	$stmt->execute();
	$onetime_array = $stmt->fetchAll(PDO::FETCH_ASSOC);
//$rslt_onetime = $stmt->fetch(PDO::FETCH_ASSOC);
//var_dump($rslt_onetime);
	if (!$onetime_array){					// not found.
		$error_reason = 'not found';
		$message = insert_error_record($dbh,$presence_report_row,$error_reason);
		continue;
	}
	foreach ( $onetime_array as $onetime_row ) {
				// update tbl_schedule_onetime accordingly.
				// target onetime scheduile is basically only one .
		$message = update_onetime_schedule($dbh,$presence_report_row,$onetime_row);
	}
}
error_label:
	if ($err_flag === true){
		var_dump($message);
	}
// the end of main program. 

function insert_error_record(&$dbh,$presence_report_row,$error_reason){
		// This function insert error record into the tbl_teacher_presence_report_error.

	$now = date('Y-m-d H:i:s');;

try {
        $sql = "INSERT INTO tbl_teacher_presence_report_error( ";
        $sql .= "teacher_id,year,month,date,time,member_no,name,presence,insert_timestamp,update_timestamp,error_reason,error_timestamp)";
        $sql .= " VALUES (?,?,?,?,?,?,?,?,?,?,?,?)";
        $stmt = $dbh->prepare($sql);
        $stmt->bindValue(1,$presence_report_row['teacher_id'], PDO::PARAM_STR);
        $stmt->bindValue(2,$presence_report_row['year'], PDO::PARAM_INT);
        $stmt->bindValue(3,$presence_report_row['month'], PDO::PARAM_INT);
        $stmt->bindValue(4,$presence_report_row['date'], PDO::PARAM_STR);
        $stmt->bindValue(5,$presence_report_row['time'], PDO::PARAM_STR);
        $stmt->bindValue(6,$presence_report_row['member_no'], PDO::PARAM_STR);
        $stmt->bindValue(7,$presence_report_row['name'], PDO::PARAM_STR);
        $stmt->bindValue(8,$presence_report_row['presence'], PDO::PARAM_STR);
        $stmt->bindValue(9,$presence_report_row['insert_timestamp'], PDO::PARAM_STR);
        $stmt->bindValue(10,$presence_report_row['update_timestamp'], PDO::PARAM_STR);
        $stmt->bindValue(11,$error_reason, PDO::PARAM_STR);
        $stmt->bindValue(12,$now, PDO::PARAM_STR);
        $stmt->execute();
	return ('');

}catch (PDOException $e){
        return('DB Access Error.');
}
}		// end of function.

function update_onetime_schedule(&$dbh,$presence_report_row,$onetime_row){
// This function update tbl_schedule_onetime according tbl_teacher_presence_report.

$alternate = '' ; 	// initialization.

switch ($presence_report_row['presence']){
case ATTEND_1:
case ATTEND_2:
	$cancel = '';
	$calcel_reason = '';
	$confirm = 'f';
	break;
case ABSENT1_1:
case ABSENT1_2:
	$cancel = 'a1';
	$calcel_reason = '';
	$confirm = 'a1';
	break;
case NOCLASS_1:
case NOCLASS_2:
	$cancel = 'a1';
	$calcel_reason = CONST_NOCLASS;
	$confirm = 'a1';
	break;
case ABSENT2_1:
case ABSENT2_2:
	$cancel = 'a2';
	$calcel_reason = '';
	$confirm = 'a2';
	break;
case OVERLIMIT_1:
case OVERLIMIT_2:
	$cancel = 'a2';
	$calcel_reason = CONST_OVERLIMIT;
	$confirm = 'a2';
	break;
case TODAY_1:
case TODAY_2:
	$cancel = 'a2';
	$calcel_reason = CONST_TODAY;
	$confirm = 'f';
	break;
case ALTERNATE_1:
case ALTERNATE_2:
	$cancel = '';
	$calcel_reason = '';
	$confirm = 'f';
	$alternate = 'a';
	break;

} // end of switch.

	$now = date('Y-m-d H:i:s');;
	$updateuser=200000;	// system user.
//var_dump($confirm);

       		 // update tbl_schedule_onetime based on tbl_teacher_presence_report.
try {
        $sql = "UPDATE tbl_schedule_onetime set cancel=?,cancel_reason=?,confirm=?,alternate=?,updatetime=?,updateuser=? WHERE id=? ";
//        $sql = "UPDATE tbl_schedule_onetime_test set cancel=?,cancel_reason=?,confirm=?,alternate=?,updatetime=?,updateuser=? WHERE id=? ";
        $stmt = $dbh->prepare($sql);
        $stmt->bindValue(1,$cancel, PDO::PARAM_STR);
        $stmt->bindValue(2,$cancel_reason, PDO::PARAM_STR);
        $stmt->bindValue(3,$confirm, PDO::PARAM_STR);
        $stmt->bindValue(4,$alternate, PDO::PARAM_STR);
        $stmt->bindValue(5,$now, PDO::PARAM_STR);
        $stmt->bindValue(6,$updateuser, PDO::PARAM_STR);
        $stmt->bindValue(7,$onetime_row['id'], PDO::PARAM_INT);
        $stmt->execute();

exit_label:
return ('');

}catch (PDOException $e){
        return('DB Access Error.');
}
}
