<?php
// This routine update specified year month schedule ( both m2m and self study ) from 
// tbl_season_schedule(m2m) and tbl_season_class_entry_data(m2m and selfstudy) into tbl_season_class_entry_date.
// syntax: php update_season_calender_batch.php STARTYEAR STARTMONTH ENDMONTH 
// necessary condition: ENDMONTH - STARTMONTH = 1.

//ini_set( 'display_errors', 0 );

$request_startyear = $argv[1];
if (!$request_startyear){
	$err_flag = true;
	$message = 'Syntax error: correct syntax is php upload_season_calender_batch.php STARTYEAR STARTMONTH ENDMONTH';
	goto error_label;
}

$request_startmonth = $argv[2];
if (!$request_startmonth){
	$err_flag = true;
	$message = 'Syntax error: correct syntax is php upload_season_calender_batch.php STARTYEAR STARTMONTH ENDMONTH';
	goto error_label;
}

if (strlen($request_startmonth) === 1) {	// filling leading zero.
	$request_startmonth_str = '0'.$request_month;
} else {
	$request_startmonth_str = $request_startmonth;
}

if ($request_startmonth_str == '12') {		// if start month is December then next year.
	$plusoneyear = (int)($request_startyear) + 1;
	$request_endyear = (string)$plusoneyear;
} else {
	$request_endyear = $request_startyear;
}

$request_endmonth = $argv[3];
if (!$request_endmonth){
	$err_flag = true;
	$message = 'Syntax error: correct syntax is php upload_season_calender_batch.php STARTYEAR STARTMONTH ENDMONTH';
	goto error_label;
}

if (strlen($request_endmonth) === 1) {	// filling leading zero.
	$request_endmonth_str = '0'.$request_endmonth;
} else {
	$request_endmonth_str = $request_endmonth;
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
$subject_list = get_subject_list($db);
			// コースリストの取得
$course_list = get_course_list($db);

$now = date('Y-m-d H:i:s');
//$dbh=new PDO('mysql:host=mysql720.db.sakura.ne.jp;dbname=hachiojisakura_calendar;charset=utf8',DB_USER,DB_PASSWD2);
//$dbh->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);

$work_list = get_work_list($dbh);  // making work list.

define('ATTEND','出席');
define('SEASON',10);
define('SELFSTUDY',5);

$const_attend = ATTEND;		
$target_work_id = SEASON;		// for season and weekend seminar m2m only. shortname is 'season'.
$target_work_id2 = SELFSTUDY;		// for season and weekend seminar selfstudy only. shortname is 'ss'.

						// Getting onetime m2m schedule.
$startdate = $request_startyear.'-'.$request_startmonth_str.'-01';
$enddte = $request_endyear.'-'.$request_endmonth_str.'-31';
$target_work_id = SEASON;		// for season and weekend seminar m2m only. shortname is 'season'.

//$sql = "SELECT ymd,user_id,teacher_id,stime,etime FROM tbl_schedule_onetime WHERE delflag=0 AND ymd BETWEEN ? AND ? ";
$sql = "SELECT ymd,user_id,teacher_id,stime,etime FROM tbl_schedule_onetime_test WHERE delflag=0 AND ymd BETWEEN ? AND ? ";
$sql .= " AND work_id=? AND confirm='f' ";

$stmt = $dbh->prepare($sql);
$stmt->bindValue(1, $startdate, PDO::PARAM_STR);
$stmt->bindValue(2, $enddate, PDO::PARAM_STR);
$stmt->bindValue(3, $target_work_id, PDO::PARAM_INT);	// season man2man.
$stmt->execute();
$onetime_schedule_array = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ( $season_schedule_array as $row ) {

       	$date_season = $row['ymd'] ;
	$date_season_slash = str_replace('-','/',$date_season);		// replace '-' with '/'
       	$member_no_str = (string)$row['user_id'] ;
	$member_no_str_len = strlen($member_no_str);
        if ($member_no_str_len == 1) {
                $member_no_str_complete = '00000'.$member_no_str;
        } else if ($member_no_str_len == 2) {
                $member_no_str_complete = '0000'.$member_no_str;
        } else if ($member_no_str_len == 3) {
                $member_no_str_complete = '000'.$member_no_str;
        } else if ($member_no_str_len == 4) {
                $member_no_str_complete = '00'.$member_no_str;
        } else if ($member_no_str_len == 5) {
                $member_no_str_complete = '0'.$member_no_str;
        }

       	$teacher_no_season = 100000 + (int)$row['teacher_id'] ;	// converting teacher_id for learning management system.
       	$stime = $row['stime'] ;
	$stime_str = $stime.':00';
       	$etime = $row['etime'] ;
	$etime_str = $etime.':00';
						// update attend status.
	$sql = "UPDATE tbl_season_schedule SET attend_status=? WHERE date=? AND member_no=? AND teacher_id=? AND stime=? AND etime=? AND attend_status!=?";
	$stmt = $dbh->prepare($sql);
	$stmt->bindValue(1, $const_attend, PDO::PARAM_STR);
	$stmt->bindValue(2, $date_season_slash, PDO::PARAM_STR);
	$stmt->bindValue(3, $member_no_str_complete, PDO::PARAM_STR);		// string attribute.
	$stmt->bindValue(4, $teacher_no_season, PDO::PARAM_INT);	// integer attribute.
	$stmt->bindValue(5, $stime_str, PDO::PARAM_STR);
	$stmt->bindValue(6, $etime_str, PDO::PARAM_STR);
	$stmt->bindValue(7, $const_attend, PDO::PARAM_STR);
	$stmt->execute();
}

$target_work_id2 = SELFSTUDY;		// for season and weekend seminar m2m only. shortname is 'season'.

//$sql = "SELECT ymd,user_id,teacher_id,stime,etime FROM tbl_schedule_onetime WHERE delflag=0 AND ymd BETWEEN ? AND ? ";
$sql = "SELECT ymd,user_id,teacher_id,stime,etime FROM tbl_schedule_onetime_test WHERE delflag=0 AND ymd BETWEEN ? AND ? ";
$sql .= " AND work_id=? AND confirm='f' ";

$stmt = $dbh->prepare($sql);
$stmt->bindValue(1, $startdate, PDO::PARAM_STR);
$stmt->bindValue(2, $enddate, PDO::PARAM_STR);
$stmt->bindValue(3, $target_work_id2, PDO::PARAM_INT);	// selfstudy.
$stmt->execute();
$onetime_schedule_array = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ( $season_schedule_array as $row ) {

       	$date_season = $row['ymd'] ;
	$date_season_slash = str_replace('-','/',$date_season);		// replace '-' with '/'
       	$member_no_str = (string)$row['user_id'] ;
	$member_no_str_len = strlen($member_no_str);
        if ($member_no_str_len == 1) {
                $member_no_str_complete = '00000'.$member_no_str;
        } else if ($member_no_str_len == 2) {
                $member_no_str_complete = '0000'.$member_no_str;
        } else if ($member_no_str_len == 3) {
                $member_no_str_complete = '000'.$member_no_str;
        } else if ($member_no_str_len == 4) {
                $member_no_str_complete = '00'.$member_no_str;
        } else if ($member_no_str_len == 5) {
                $member_no_str_complete = '0'.$member_no_str;
        }
       	$stime = $row['stime'] ;
	$stime_str = $stime.':00';
       	$etime = $row['etime'] ;
	$etime_str = $etime.':00';
					// Update attend status according study management system.
	$sql = "UPDATE tbl_season_class_entry_date SET attend_status=? WHERE date=? AND member_no=? AND stime=? AND etime=? AND attend_status!=?";
	$stmt = $dbh->prepare($sql);
	$stmt->bindValue(1, $const_attend, PDO::PARAM_STR);
	$stmt->bindValue(2, $date_season_slash, PDO::PARAM_STR);
	$stmt->bindValue(3, $member_no_str_complete, PDO::PARAM_STR);
	$stmt->bindValue(4, $stime_str, PDO::PARAM_STR);
	$stmt->bindValue(5, $etime_str, PDO::PARAM_STR);
	$stmt->bindValue(6, $const_attend, PDO::PARAM_STR);
	$stmt->execute();
}

error_label:
	if ($err_flag === true){
		var_dump($message);
	}
//} // the end of main program. 

?>
