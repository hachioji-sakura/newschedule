<?php
//echo "Program Start.\n";

ini_set( 'display_errors', 0 );

$request_course_id = $argv[1];
if (!$request_course_id){
	$err_flag = true;
	$message = 'Syntax error: php get_season_calender_insert_batch.php COURSE_ID YEAR MONTH';
	goto error_label;
}

$request_year = $argv[2];
if (!$request_year){
	$err_flag = true;
	$message = 'Syntax error: php get_season_calender_insert_batch.php COURSE_ID YEAR MONTH';
	goto error_label;
}

$request_month = $argv[3] ;
if (!$request_month){
	$err_flag = true;
	$message = 'Syntax error: php get_season_calender_insert_batch.php COURSE_ID YEAR MONTH';
	goto error_label;
}

$request_force = $argv[4] ;
if ($request_force && $request_force != 'force'){
	$err_flag = true;
	$message = 'Syntax error: php get_season_calender_insert_batch.php COURSE_ID YEAR MONTH force';
	goto error_label;
}

require_once "/home/hachiojisakura/www/sakura01/schedule/const/const.inc";
require_once "/home/hachiojisakura/www/sakura01/schedule/func.inc";
require_once("/home/hachiojisakura/www/sakura01/schedule/const/login_func.inc");
require_once("/home/hachiojisakura/www/sakura01/schedule/const/token.php");
ini_set('include_path', CLIENT_LIBRALY_PATH);
set_time_limit(60);
define(API_TOKEN, '7511a32c7b6fd3d085f7c6cbe66049e7');

define(CONST_ABSENTLATE, '当日');
define(CONST_ABSENTLATE_ENG, 'Today');

// ****** メイン処理ここから ******

mb_regex_encoding("UTF-8");
			// 先生リストの取得
$teacher_list = get_teacher_list($db);
			// 生徒リストの取得
$member_list = get_member_list($db);
			// レッスンリストの取得
$lesson_list = get_lesson_list($db);
			// 科目リストの取得
$subject_list = get_subject_list($db);
			// コースリストの取得
$course_list = get_course_list($db);

$now = date('Y-m-d H:i:s');
$dbh=new PDO('mysql:host=mysql720.db.sakura.ne.jp;dbname=hachiojisakura_calendar;charset=utf8',DB_USER,DB_PASSWD2);
$dbh->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);

$work_list = get_work_list($dbh);
switch ($request_course_id ) {  
case '4' :  // Summer seminar
	$target_year1 = $request_year;
	$target_year2 = '';
	$target_work_id = 10;
	break;
case '5' :   // Winter seminar
	$target_year1 = $request_year;
	$next_year = (int)$target_year1 + 1;
	$target_year2 = (string)$next_year ;
	$target_work_id = 11;
	break;
case '6' :   // Spring seminar
	$target_year1 = $request_year;
	$target_year2 = '';
	$target_work_id = 12;
	break;
case '9' :   // Weekend seminar
	$target_year1 = $request_year;
	$target_year2 = '';
	$target_work_id = 13;
	break;
} // switch.
			// check whether schedule for the month is set.
if (!$request_force) {	// force option is not specified.
 	$startofmonth = $request_year.'-'.$request_month.'-01';
 	$endofmonth = $request_year.'-'.$request_month.'-31';
//	$sql = "SELECT COUNT(*) AS COUNT FROM tbl_schedule_onetime WHERE course_id=? AND ymd BETWEEN ? AND ?";
	$sql = "SELECT COUNT(*) AS COUNT FROM tbl_schedule_onetime_test WHERE work_id=? AND ymd BETWEEN ? AND ?";
	$stmt = $dbh->prepare($sql);
	$stmt->bindValue(1, $target_work_id, PDO::PARAM_INT);
	$stmt->bindValue(2, $startofmonth, PDO::PARAM_STR);
	$stmt->bindValue(3, $endofmonth, PDO::PARAM_STR);
	$stmt->execute();
	$already_exist = (int)$stmt->fetchColumn();
	if ($already_exist > 0) {
		$err_flag = true;
		$message = 'The schedule is already registerd. If you want append the data, use force option.';
		goto error_label;
	}
}

			// tbl_season_scheduleからデータの取得
$sql = "SELECT date,stime,etime,lnum,teacher_no,member_no,lesson_id,subject_id,course_id FROM tbl_season_schedule WHERE course_id=? AND date LIKE ?";
$stmt = $db->prepare($sql);
$stmt->bindValue(1, $request_course_id, PDO::PARAM_STR);
$target_year1_percent = $target_year1.'/'.$monnth_str.'%';
$stmt->bindValue(2, $target_year1_percent, PDO::PARAM_STR);
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

	switch ($course_id) {
	 case 4:
	  	$work = 'summer';
		$subject_id = 0 ;
		break;
	 case 5:
	  	$work = 'winter';
		$subject_id = 0 ;
		break;
	 case 6:
	  	$work = 'spring';
		$subject_id = 0 ;
		break;
	 case 9:
	  	$work = 'weekend';
		$subject_id = 0 ;
		break;
	}

	$sql = "SELECT lecture_id FROM tbl_lecture WHERE lesson_id = ? AND course_id=? AND subject_id= ? ";
      	$stmt = $db->prepare($sql);
	$stmt->bindValue(1, $lesson_id, PDO::PARAM_INT);
	$stmt->bindValue(2, $course_id, PDO::PARAM_INT);
	$stmt->bindValue(3, $subject_id, PDO::PARAM_INT);
	$stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
               	$lecture_id = $result['lecture_id'];
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
//	$event_id = $event["id"];
	$startymd = date('Y-m-d',$start_timestamp);
	$starttime = date('H:i:s',$start_timestamp);
	$endymd = date('Y-m-d',$end_timestamp);
	$endtime = date('H:i:s',$end_timestamp);
	if ($startymd != $endymd) {
					// 開始日と終了日が異なる 
		goto exit_label;
	}
	$ymd = $startymd;	
//	$event_updated_timestamp = $event['updated'];
					// tbl_schedule_onetimeに挿入する項目の設定
	if ($recurrence_id !== "") {
		$repetition_id = -1; // 定期的スケジュールの識別子。暫定で-1とする
	}
//	$updatetime = date('Y-m-d H-i-s',$event_updated_timestamp); 
			// converting work shortname into work_id
	foreach ($work_list as $workitem) {
		if (mb_strpos($workitem["shortname"], $work)!==FALSE) {
                        $work_id = $workitem["id"];
                        break;  // for each
                }
        }  // end of for each.

	if ($subject_id) {
             $subject_expr = $subject_list[$subject_id];
        }

	if ($user_id==0) { goto exit_label;}
						// not Repeting
//	$sql = "INSERT INTO tbl_schedule_onetime (".
	$sql = "INSERT INTO tbl_schedule_onetime_test (".
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
exit_label:
}catch (PDOException $e){
	print_r('insert_calender_event:failed: ' . $e->getMessage());
	return false;
}
return $event_no;
} // End:event_insert

// 作業名の一覧を取得
function get_work_list(&$db) {
        $sql = "SELECT * FROM tbl_work ";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $work_array = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $work_list = array();
        foreach ( $work_array as $row ) {
                $work_list[$row["id"]] = $row;
        }
        return $work_list;
}
?>
