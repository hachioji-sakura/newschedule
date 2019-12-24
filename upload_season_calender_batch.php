<?php
// This routine load specified year month schedule ( both m2m and self study ) from 
// tbl_season_schedule(m2m) and tbl_season_class_entry_data(m2m and selfstudy) into tbl_schedule_onetime.
// syntax: php upload_season_calender_batch.php STARTYEAR STARTMONTH ENDMONTH [replace]
// replace is the option to load data even if there exist target month's data in tbl_schedule_onetime.

//ini_set( 'display_errors', 0 );
error_reporting(0);

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

if ($request_startmonth_str == '12') {		// if December then next year.
	$plusoneyear = (int)($request_startyear) + 1;
	$request_endyear = $plusoneyear;
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

$request_mode = $argv[4] ;
if ($request_mode && $request_mode != 'replace'){
	$err_flag = true;
	$message = 'Syntax error: correct syntax is php upload_season_calender_batch.php STARTYEAR STARTMONTH ENDYEAR ENDMONTH';
	goto error_label;
}

require_once "./const/const.inc";
require_once "./func.inc";
require_once("./const/login_func.inc");
require_once("./const/token.php");
//ini_set('include_path', CLIENT_LIBRALY_PATH);
//set_time_limit(60);
//define(API_TOKEN, '7511a32c7b6fd3d085f7c6cbe66049e7');

// ****** メイン処理ここから ******

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
define('ABSENT1','休み１');
define('ABSENT2','休み２');
define('ABSENT2TODAY','休み２当日');
define('TODAY','当日');
define('ALTERNATE','振替');

define('SEASON',10);
define('SELFSTUDY',5);

$const_attend = ATTEND;		
$const_absent1 = ABSENT1;		
$const_absent2 = ABSENT2;		
$const_today = TODAY;		
$const_alternate = ALTERNATE;		

$target_work_id = SEASON;		// for season and weekend seminar m2m only. shortname is 'season'.
$target_work_id2 = SELFSTUDY;		// for season and weekend seminar selfstudy only. shortname is 'ss'.

			// check whether schedule for the month is set.
$startofmonth = $request_startyear.'-'.$request_startmonth_str.'-01';
$endofmonth = $request_endyear.'-'.$request_endmonth_str.'-31';

$sql = "SELECT COUNT(*) AS COUNT FROM tbl_schedule_onetime WHERE ( work_id=?  OR work_id=? ) AND ymd BETWEEN ? AND ?";
//$sql = "SELECT COUNT(*) AS COUNT FROM tbl_schedule_onetime_test WHERE ( work_id=?  OR work_id=?) AND ymd BETWEEN ? AND ?";
$stmt = $dbh->prepare($sql);
$stmt->bindValue(1, $target_work_id, PDO::PARAM_INT);
$stmt->bindValue(2, $target_work_id2, PDO::PARAM_INT);
$stmt->bindValue(3, $startofmonth, PDO::PARAM_STR);
$stmt->bindValue(4, $endofmonth, PDO::PARAM_STR);
$stmt->execute();
$already_exist = (int)$stmt->fetchColumn();
if ($already_exist > 0) {			// Already exsit target year month data.
	if ($request_mode != 'replace') {	// replace option is not specified.
		$err_flag = true;
		$message = 'The schedule is already registerd. If you want to append the data, use force option.';
		goto error_label;
	}
				// check m2m data both tbl_schedule_onetime and tbl_season_schedule at first.

	$startyearmonth_percent = $request_startyear.'/'.$request_startmonth_str.'%';
	$endyearmonth_percent = $request_endyear.'/'.$request_endmonth_str.'%';

						// Getting season schedule.

	$sql = "SELECT date,member_no,teacher_no,COUNT(*) FROM tbl_season_schedule WHERE (date LIKE ? OR date LIKE ?) AND attend_status=? ";
	$sql .= " GROUP BY date,member_no,teacher_no";
	
	$stmt = $db->prepare($sql);
	$stmt->bindValue(1, $startyearmonth_percent, PDO::PARAM_STR);
	$stmt->bindValue(2, $endyearmonth_percent, PDO::PARAM_STR);
	$stmt->bindValue(3, $const_attend, PDO::PARAM_STR);
	$stmt->execute();
	$season_schedule_array = $stmt->fetchAll(PDO::FETCH_ASSOC);

						// Getting onetime schedule.
	$startdate = $request_startyear.'-'.$request_startmonth_str.'-01';
	$enddate = $request_endyear.'-'.$request_endmonth_str.'-31';

	$target_work_id = SEASON;		// for season and weekend seminar m2m only. shortname is 'season'.

	$sql = "SELECT ymd,user_id,teacher_id,COUNT(*) FROM tbl_schedule_onetime WHERE delflag=0 AND ymd BETWEEN ? AND ? ";
//	$sql = "SELECT ymd,user_id,teacher_id,COUNT(*) FROM tbl_schedule_onetime_test WHERE delflag=0 AND ymd BETWEEN ? AND ? ";
	$sql .= " AND work_id=? AND confirm='f' GROUP BY ymd,user_id,teacher_id";

	$stmt = $dbh->prepare($sql);
	$stmt->bindValue(1, $startdate, PDO::PARAM_STR);
	$stmt->bindValue(2, $enddate, PDO::PARAM_STR);
	$stmt->bindValue(3, $target_work_id, PDO::PARAM_INT);
	$stmt->execute();
	$onetime_schedule_array = $stmt->fetchAll(PDO::FETCH_ASSOC);

	$season_cnt = 0; 	// initialization.
	$onetime_cnt = 0; 	// initialization.

	foreach ( $season_schedule_array as $seasonrow ) {

        	$date_season = $seasonrow['date'] ;
		$date_season_ = str_replace('/','-',$date_season); 
        	$member_no_season = (int)$seasonrow['member_no'] ;
        	$teacher_no_season = (int)$seasonrow['teacher_no'] +100000 ;	// converting teacher_id from learning management system.
					// Data value Check.
        	if ($date_season_ != $onetime_schedule_array[$onetime_cnt]['ymd'] 
        		|| (string)$member_no_season != $onetime_schedule_array[$onetime_cnt]['user_id']
        		|| (string)$teacher_no_season != $onetime_schedule_array[$onetime_cnt]['teacher_id'] ) {
					// mismatch between learning management system and office management system.
			$err_flag = true;

			$message = 'attended data mismatch between season and onetime schedule. Extract onetime schedule again.';
			goto error_label;
		}
		$season_cnt++;
		$onetime_cnt++;
	}
				// check self study data both tbl_schedule_onetime and tbl_season_class_entry_date at first.
						// Getting season schedule. both man2man and selfstudy.

	$sql = "SELECT date,member_id,COUNT(*) FROM tbl_season_class_entry_date WHERE (date LIKE ? OR date LIKE ?) AND attend_status=? ";
	$sql .= " GROUP BY date,member_id";
	$stmt = $db->prepare($sql);
	$startyearmonth_percent = $request_startyear.'/'.$request_startmonth_str.'%';
	$endyearmonth_percent = $request_endyear.'/'.$request_endmonth_str.'%';
	$stmt->bindValue(1, $startyearmonth_percent, PDO::PARAM_STR);
	$stmt->bindValue(2, $endyearmonth_percent, PDO::PARAM_STR);
	$stmt->bindValue(3, $const_attend, PDO::PARAM_STR);
	$stmt->execute();
	$season_schedule_array = $stmt->fetchAll(PDO::FETCH_ASSOC);

						// Getting onetime schedule. ( both man2man and selfstudy).
	$startdate = $request_startyear.'-'.$request_startmonth_str.'-01';
	$enddte = $request_endyear.'-'.$request_endmonth_str.'-31';

	$sql = "SELECT ymd,user_id,COUNT(*) FROM tbl_schedule_onetime WHERE delflag=0 AND ymd BETWEEN ? AND ? ";
//	$sql = "SELECT ymd,user_id,COUNT(*) FROM tbl_schedule_onetime_test WHERE delflag=0 AND ymd BETWEEN ? AND ? ";
	$sql .= " AND ( work_id=?  OR work_id=?) AND confirm='f' GROUP BY ymd,user_id";
	$stmt = $dbh->prepare($sql);
	$stmt->bindValue(1, $startdate, PDO::PARAM_STR);
	$stmt->bindValue(2, $enddate, PDO::PARAM_STR);
	$stmt->bindValue(3, $target_work_id, PDO::PARAM_INT);
	$stmt->bindValue(4, $target_work_id2, PDO::PARAM_INT);
	$stmt->execute();
	$onetime_schedule_array = $stmt->fetchAll(PDO::FETCH_ASSOC);
	$season_cnt = 0; 	// initialization.
	$onetime_cnt = 0; 	// initialization.

	foreach ( $season_schedule_array as $seasonrow ) {

        	$date_season = $seasonrow['date'] ;
        	$member_no_season = (int)$seasonrow['member_id'] ;
        	$group_cnt_season = (int)$seasonrow['COUNT'] ;
									// Data value Check.
        	if ($date_season != $onetime_schedule_array[$onetime_cnt]['ymd'] 
        		|| $member_no_season != $onetime_schedule_array[$onetime_cnt]['user_id']) {

			$err_flag = true;
			$message = 'Attend data mismatch between season and onetime schedule. Extract onetime schedule again.';
			goto error_label;
		}
		$season_cnt++;
		$onetime_cnt++;
	}

				// logical delete target data before loading new data.

	$sql = "UPDATE tbl_schedule_onetime SET delflag= 1 WHERE (work_id=?  OR work_id=?) AND ymd BETWEEN ? AND ?";
//	$sql = "UPDATE tbl_schedule_onetime_test SET delflag= 1 WHERE (work_id=?  OR work_id=?) AND ymd BETWEEN ? AND ?";
	$stmt = $dbh->prepare($sql);
	$stmt->bindValue(1, $target_work_id, PDO::PARAM_INT);
	$stmt->bindValue(2, $target_work_id2, PDO::PARAM_INT);
	$stmt->bindValue(3, $startdate, PDO::PARAM_STR);
	$stmt->bindValue(4, $enddate, PDO::PARAM_STR);
	$stmt->execute();

} else if ($already_exist == 0) {		// load new data.
	if ($request_mode == 'replace') {	// replace option is specified but no data exist..
		$err_flag = true;
		$message = 'The schedule is not registerd yet. Remove replace option and try again.';
		goto error_label;
	}
}

			// tbl_season_scheduleからman2manデータの取得
$startyearmonth_percent = $request_startyear.'/'.$request_startmonth_str.'%';
$endyearmonth_percent = $request_endyear.'/'.$request_endmonth_str.'%';
$sql = "SELECT date,stime,etime,lnum,teacher_no,member_no,lesson_id,subject_id,course_id,attend_status FROM tbl_season_schedule ";
$sql .= " WHERE date LIKE ? OR date LIke ? ORDER BY date,member_no";
$stmt = $db->prepare($sql);
$stmt->bindValue(1, $startyearmonth_percent, PDO::PARAM_STR);
$stmt->bindValue(2, $endyearmonth_percent, PDO::PARAM_STR);

$stmt->execute();
$season_schedule_array = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ( $season_schedule_array as $row ) {
	  			 // Initialization.
 	$temporary = 0; 
  	$trial_id = ""; 
  	$alternate = ""; 
  	$altsched_id = 0; 
  	$teacher_id = 0 ; 
  	$student_no = 0; 
  	$user_id = 0 ; 
	
  	$cancel = ""; 
  	$cancel_reason = ""; 
  	$lecture_id = 0 ; 
  	$lesson_id = 0 ; 
  	$course_id = 0 ; 
  	$subject_id = 0 ; 
  	$work_id = 0 ; 
  	$repetition_id = "" ; 
  	$absent1_num = 0; 
  	$absent2_num = 0; 
  	$trial_num = 0; 
	$start_timestamp = null;
	$end_timestamp = null;
  	$comment = ""; 
  	$temp_name = ""; 

        $date = $row['date'];

        $starttime = $row['stime'];
	$timestamp_str = $date.' '.$starttime.':00';
	$dateObj = new DateTime($timestamp_str);
	$start_timestamp = $dateObj->getTimestamp();

        $endtime = $row['etime'];
	$timestamp_str = $date.' '.$endtime.':00';
	$dateObj = new DateTime($timestamp_str);
	$end_timestamp = $dateObj->getTimestamp();

        $lnum = $row['lnum'];

        $teacher_id = (int)$row['teacher_no'] + 100000;
        $user_id = (int)$row['member_no'] ;
        $student_no = $user_id ;
        $lesson_id = (int)$row['lesson_id'] ;
        $course_id = (int)$row['course_id'] ;
        $subject_id = (int)$row['subject_id'] ;
        $place_id = 3 ; // Hachioji north 3F.
  	$work = 'season';

	$sql = "SELECT lecture_id FROM tbl_lecture WHERE lesson_id = ? AND course_id=? AND subject_id= ? ";
      	$stmt = $db->prepare($sql);
	$stmt->bindValue(1, $lesson_id, PDO::PARAM_INT);
	$stmt->bindValue(2, $course_id, PDO::PARAM_INT);
	$stmt->bindValue(3, $subject_id, PDO::PARAM_INT);
	$stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $lecture_id = $result['lecture_id'];

	if ( is_null($lecture_id)){
		$lecture_id = 88;	// setting default value.
	}

        $attend_status = $row['attend_status'];

	switch ($attend_status) {
	case ABSENT1:
		$cancel = 'a1';
		break;
	case ABSENT2:
		$cancel = 'a2';
		break;
	case ABSENT2TODAY:
		$cancel = 'a2';
  		$cancel_reason = TODAY; 
		break;
	case ATTEND:
		$confirm = 'f';
		break;
	case ALTERNATE:
		$alternate = 'a';
		$altsched_id = -1;
		break;
	}


				// 個別スケジュールへの挿入		
	$result = insert_calender_event($dbh,
					$start_timestamp,
					$end_timestamp,
					$repetition_id,
					$user_id,
					$teacher_id,
					$student_no,
					$lecture_id,
					$work,
					$free,
					$cancel,
					$cancel_reason,
					$alternate,
					$altsched_id,
					$trial_id,
					$repeattimes,
					$place_id,
					$temporary,
					$comment,
					$temp_name,
					$googlecal_id,
					$googleevent_id,
					$googleexpression,
					$recurrence_id,
					$absent1_num,
					$absent2_num,
					$trial_num,
					$monthly_fee_flag,
					$subject_id);
} 	// end of for each.



			// tbl_season_class_entry_dateからman2manと演習を含むスケジュールの取得,man2manを除く必要がある
$startyearmonth_percent = $request_startyear.'/'.$request_startmonth_str.'%';
$endyearmonth_percent = $request_endyear.'/'.$request_endmonth_str.'%';

$sql = "SELECT date,stime,etime,member_id,season_course_id,attend_status FROM tbl_season_class_entry_date WHERE date LIKE ? OR date LIKE ?";
$sql .= " ORDER BY date,member_id";
$stmt = $db->prepare($sql);
$stmt->bindValue(1, $startyearmonth_percent, PDO::PARAM_STR);
$stmt->bindValue(2, $endyearmonth_percent, PDO::PARAM_STR);
$stmt->execute();
$season_schedule_array = $stmt->fetchAll(PDO::FETCH_ASSOC);
			// 1件ごとの処理
foreach ( $season_schedule_array as $row ) {
	  			 // Initialization.
 	$temporary = 0; 
  	$trial_id = ""; 
  	$alternate = ""; 
  	$altsched_id = 0; 
  	$teacher_id = 0 ; 
  	$student_no = 0; 
  	$user_id = 0 ; 
  	$cancel = ""; 
  	$cancel_reason = ""; 
  	$confirm = ""; 
  	$lecture_id = 0 ; 
  	$lesson_id = 0 ; 
  	$course_id = 0 ; 
  	$subject_id = 0 ; 
  	$work_id = 0 ; 
  	$repetition_id = "" ; 
  	$absent1_num = 0; 
  	$absent2_num = 0; 
  	$trial_num = 0; 
	$start_timestamp = null;
	$end_timestamp = null;
  	$comment = ""; 
  	$temp_name = ""; 

        $date = $row['date'];

        $starttime = $row['stime'];
	$timestamp_str = $date.' '.$starttime.':00';
	$dateObj = new DateTime($timestamp_str);
	$startofday_ts = $dateObj->getTimestamp();

        $endtime = $row['etime'];
	$timestamp_str = $date.' '.$endtime.':00';
	$dateObj = new DateTime($timestamp_str);
	$endofday_ts = $dateObj->getTimestamp();

        $member_id = $row['member_id'] ;	// 6桁の文字列。先頭は０
        $lesson_id = (int)$row['lesson_id'] ;
        $course_id = (int)$row['course_id'] ;

	$work = 'ss';
	$lesson_id = 1 ; 	// 塾
	$course_id = 9 ;	// weekend seminar (fixed value for temporary). 
	$subject_id = 0 ;

	$sql = "SELECT lecture_id FROM tbl_lecture WHERE lesson_id = ? AND course_id=? AND subject_id= ? ";
      	$stmt = $db->prepare($sql);
	$stmt->bindValue(1, $lesson_id, PDO::PARAM_INT);
	$stmt->bindValue(2, $course_id, PDO::PARAM_INT);
	$stmt->bindValue(3, $subject_id, PDO::PARAM_INT);
	$stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
       	$lecture_id = $result['lecture_id'];

        $place_id = 3 ; // Hachioji north 3F.

	$attend_status = $row['attend_status'];
					// 全体時間からm2mの時間を引いて演習時間を求める
	$status = insert_selfstudy_schedule($db,$dbh,$member_id,$startofday_ts,$endofday_ts,$lecture_id,$place_id,$subject_id,$attend_status);
					// Search for m2m schedule.


} 	// end of for each.

error_label:
	if ($err_flag === true){
		var_dump($message);
	}
//} // the end of main program. 

/************* Single Insert ****************/

function insert_calender_event(&$dbh,$start_timestamp,$end_timestamp,$repetition_id,$user_id,$teacher_id,$student_no,$lecture_id,$work,$free,$cancel,$cancel_reason,$alternate,$altsched_id,$trial_id,$repeattimes,$place_id,$temporary,$comment,$temp_name,$googlecal_id,$googleevent_id,$googleexpression,$recurrence_id,$absent1_num,$absent2_num,$trial_num,$monthly_fee_flag,$subject_id ) {

global $work_list;
global $subject_list;
global $now;

try{
	$startymd = date('Y-m-d',$start_timestamp);
	$starttime = date('H:i:s',$start_timestamp);
	$endymd = date('Y-m-d',$end_timestamp);
	$endtime = date('H:i:s',$end_timestamp);
	if ($startymd != $endymd) {
					// 開始日と終了日が異なる 
		goto exit_label;
	}
	$ymd = $startymd;	
					// tbl_schedule_onetimeに挿入する項目の設定
	if ($recurrence_id !== "") {
		$repetition_id = -1; // 定期的スケジュールの識別子。暫定で-1とする
	}
					// converting work shortname into work_id
	foreach ($work_list as $workitem) {
		if (mb_strpos($workitem["shortname"], $work)!==false) {
                        $work_id = $workitem["id"];
                        break;  // for each
                }
        }  // end of for each.

	if ($subject_id) {
             $subject_expr = $subject_list[$subject_id];
        }

	if ($user_id==0) { goto exit_label;}
						// not Repeting
	$sql = "INSERT INTO tbl_schedule_onetime (".
//	$sql = "INSERT INTO tbl_schedule_onetime_test (".
	" repetition_id, user_id,teacher_id,student_no,ymd,starttime,endtime,lecture_id,subject_expr,work_id,free,cancel,cancel_reason, ".
	" alternate,altsched_id,trial_id, absent1_num,absent2_num,trial_num,repeattimes,place_id,temporary,entrytime,updatetime,updateuser, ".
	" comment,temp_name,googlecal_id,googleevent_id,googleexpression,recurrence_id ,monthly_fee_flag".
	" ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
	$stmt = $dbh->prepare($sql);
	$stmt->bindValue(1, $repetition_id, PDO::PARAM_INT);
	$stmt->bindValue(2, $user_id, PDO::PARAM_INT);
	$stmt->bindValue(3, $teacher_id, PDO::PARAM_INT);
	$stmt->bindValue(4, $student_no, PDO::PARAM_INT);
	$stmt->bindValue(5, $ymd, PDO::PARAM_STR);
	$stmt->bindValue(6, $starttime, PDO::PARAM_STR);
	$stmt->bindValue(7, $endtime, PDO::PARAM_STR);
	$stmt->bindValue(8, $lecture_id, PDO::PARAM_INT);
	$stmt->bindValue(9, $subject_expr, PDO::PARAM_STR);
	$stmt->bindValue(10, $work_id, PDO::PARAM_INT);
	$stmt->bindValue(11, $free, PDO::PARAM_STR);
	$stmt->bindValue(12, $cancel, PDO::PARAM_STR);
	$stmt->bindValue(13, $cancel_reason, PDO::PARAM_STR);
	$stmt->bindValue(14, $alternate, PDO::PARAM_STR);
	$stmt->bindValue(15, $altsched_id, PDO::PARAM_STR);
	$stmt->bindValue(16, $trial_id, PDO::PARAM_STR);
	$stmt->bindValue(17, $absent1_num, PDO::PARAM_INT);
	$stmt->bindValue(18, $absent2_num, PDO::PARAM_INT);
	$stmt->bindValue(19, $trial_num, PDO::PARAM_INT);
	$stmt->bindValue(20, $repeattimes, PDO::PARAM_INT);
	$stmt->bindValue(21, $place_id, PDO::PARAM_INT);
	$stmt->bindValue(22, $temporary, PDO::PARAM_INT);
	$stmt->bindValue(23, $now, PDO::PARAM_STR);
	$stmt->bindValue(24, $event_updated_timestamp, PDO::PARAM_STR);
	$stmt->bindValue(25, $updateuser, PDO::PARAM_INT);
	$stmt->bindValue(26, $comment, PDO::PARAM_STR);
	$stmt->bindValue(27, $temp_name, PDO::PARAM_STR);
	$stmt->bindValue(28, $googlecal_id, PDO::PARAM_STR);
	$stmt->bindValue(29, $googleevent_id, PDO::PARAM_STR);
	$stmt->bindValue(30, $googleexpression, PDO::PARAM_STR);
	$stmt->bindValue(31, $recurrence_id, PDO::PARAM_STR);
	$stmt->bindValue(32, $monthly_fee_flag, PDO::PARAM_INT);
//var_dump($sql);
	$stmt->execute();
	return true;
exit_label:
}catch (PDOException $e){
	print_r('insert_calender_event:failed: ' . $e->getMessage());
	return false;
}
return $event_no;
} // End:event_insert

// 作業名の一覧を取得
function get_work_list(&$dbh) {
        $sql = "SELECT * FROM tbl_work ";
        $stmt = $dbh->prepare($sql);
        $stmt->execute();
        $work_array = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $work_list = array();
        foreach ( $work_array as $row ) {
                $work_list[$row["id"]] = $row;
        }
        return $work_list;
}

/************* Selfstudy schedule Insert ****************/

function insert_selfstudy_schedule(&$db,&$dbh,$member_id,$startofday_ts,$endofday_ts,$lecture_id,$place_id,$subject_id,$attend_status ) {
				// for a given season schedule(member_id,startofdayts,endofdayts), make up selfstudy schedule. 
global $work_list;
global $subject_list;
global $now;

try{
	$result = true;
	$startymd = date('Y/m/d',$startofday_ts);
	$endymd = date('Y/m/d',$endofday_ts); 
	if ($startymd != $endymd) {
					// 開始日と終了日が異なる 
		goto exit_label;
	}
	$work = "ss" ; 		// 自習
			// converting work shortname into work_id
	foreach ($work_list as $workitem) {
		if (mb_strpos($workitem["shortname"], $work)!==false) {
                        $work_id = $workitem["id"];
                        break;  // for each
                }
        }  // end of for each.

	if ($subject_id) {
             $subject_expr = $subject_list[$subject_id];
        }

	$sql = "SELECT date,stime,etime,member_no,lesson_id,subject_id,course_id FROM tbl_season_schedule WHERE member_no=? AND date=? ORDER BY stime";
	$stmt = $db->prepare($sql);
	$stmt->bindValue(1, $member_id, PDO::PARAM_STR);	// 6桁の数値を表す文字列。先頭０．
	$stmt->bindValue(2, $startymd, PDO::PARAM_STR);
	$stmt->execute();
	$season_schedule_array = $stmt->fetchAll(PDO::FETCH_ASSOC);
	$reccnt = 0;
	$crnt_ts = $startofday_ts;

	foreach ( $season_schedule_array as $row ) {

		$reccnt = $reccnt + 1;

        	$m2mstime = $row['stime'];
		$m2mstime_str = $row['date'].' '.$m2mstime.':00';
		$dateObj = new DateTime($m2mstime_str);
		$m2mstime_ts = $dateObj->getTimestamp();

        	$m2metime = $row['etime'] ;
		$m2metime_str = $row['date'].' '.$m2metime.':00';
		$dateObj = new DateTime($m2metime_str);
		$m2metime_ts = $dateObj->getTimestamp();

		if ($m2metime_ts === $endofday_ts) {
				// 当日のスケジュールの終端に到達した。挿入するデータなし。
			break;
		} else if ($m2mstime_ts === $crnt_ts) {
				// m2mから始まる。演習は後。
			$crnt_ts = $m2metime_ts;
			continue;
		} else  if ($m2mstime_ts > $crnt_ts) { 		//次のman2manが始まるまでに自習時間がある
			$start_timestamp = $crnt_ts ;
			$end_timestamp = $m2mstime_ts ;
			$crnt_ts = $m2metime_ts;

	  			 // Initialization.
		 	$temporary = 0; 
  			$trial_id = ""; 
 		 	$alternate = ""; 
  			$altsched_id = 0; 
  			$teacher_id = 0 ; 
  			$student_no = $member_id; 
  			$user_id = $member_id; 
  			$cancel = ""; 
  			$cancel_reason = ""; 
  			$repetition_id = "" ; 
  			$absent1_num = 0; 
  			$absent2_num = 0; 
  			$trial_num = 0; 
  			$comment = ""; 
  			$temp_name = ""; 
  			$confirm = ""; 

			switch ($attend_status) {
			case ABSENT1:
				$cancel = 'a1';
				break;
			case ABSENT2:
				$cancel = 'a2';
				break;
			case ABSENT2TODAY:
				$cancel = 'a2';
  				$cancel_reason = TODAY; 
				break;
			case ATTEND:
				$confirm = 'f';
				break;
			case ALTERNATE:
				$alternate = 'a';
				$altsched_id = -1;
				break;
			}
			$result = insert_calender_event($dbh,
					$start_timestamp,
					$end_timestamp,
					$repetition_id,
					$user_id,
					$teacher_id,
					$student_no,
					$lecture_id,
					$work,
					$free,
					$cancel,
					$cancel_reason,
					$alternate,
					$altsched_id,
					$trial_id,
					$repeattimes,
					$place_id,
					$temporary,
					$comment,
					$temp_name,
					$googlecal_id,
					$googleevent_id,
					$googleexpression,
					$recurrence_id,
					$absent1_num,
					$absent2_num,
					$trial_num,
					$monthly_fee_flag,
					$subject_id);
		}		// end of if

	}		// end of foreach.
			// no more man2man recod but not reach the end of day. Then insert selfstudy record.

	if ($m2metime_ts < $endofday_ts) { 		//その日の終了時間まで自習時間がある
		$start_timestamp = $crnt_ts ;
		$end_timestamp = $endofday_ts ;

	  			 // Initialization.
	 	$temporary = 0; 
  		$trial_id = ""; 
 	 	$alternate = ""; 
  		$altsched_id = 0; 
  		$teacher_id = 0 ; 
  		$student_no = $member_id; 
  		$user_id = $member_id ; 
  		$cancel = ""; 
  		$cancel_reason = ""; 
  		$repetition_id = "" ; 
  		$absent1_num = 0; 
  		$absent2_num = 0; 
  		$trial_num = 0; 
  		$comment = ""; 
  		$temp_name = ""; 
		switch ($attend_status) {
		case ABSENT1:
			$cancel = 'a1';
			break;
		case ABSENT2:
			$cancel = 'a2';
			break;
		case ABSENT2TODAY:
			$cancel = 'a2';
  			$cancel_reason = TODAY; 
			break;
		case ATTEND:
			$confirm = 'f';
			break;
		case ALTERNATE:
			$alternate = 'a';
			$altsched_id = -1;
			break;
		}

		$result = insert_calender_event($dbh,
				$start_timestamp,
				$end_timestamp,
				$repetition_id,
				$user_id,
				$teacher_id,
				$student_no,
				$lecture_id,
				$work,
				$free,
				$cancel,
				$cancel_reason,
				$alternate,
				$altsched_id,
				$trial_id,
				$repeattimes,
				$place_id,
				$temporary,
				$comment,
				$temp_name,
				$googlecal_id,
				$googleevent_id,
				$googleexpression,
				$recurrence_id,
				$absent1_num,
				$absent2_num,
				$trial_num,
				$monthly_fee_flag,
				$subject_id);
	}		// end of if

        return $result;
//var_dump($sql);
exit_label:
}catch (PDOException $e){
	print_r('insert_selfstudy_schedule:failed: ' . $e->getMessage());
	return false;
}
} // End:event_insert

?>
