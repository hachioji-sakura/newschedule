<?php
// This routine load specified year month schedule ( both m2m and self study ) from 
// tbl_season_schedule(m2m) and tbl_season_class_entry_data(m2m and selfstudy) into tbl_schedule_onetime.
// syntax: php upload_season_calender_batch.php STARTYEAR STARTMONTH ENDMONTH [replace]
// replace is the option to load data even if there exist target month's data in tbl_schedule_onetime.

ini_set( 'display_errors', 0 );
error_reporting(0);
$errArray = array();

$request_startyear = $_POST['startyear'];
if (!$request_startyear){
	$err_flag = true;
	$message = 'Syntax error: correct syntax is php upload_season_calender_batch.php STARTYEAR STARTMONTH ENDMONTH';
	array_push($errArray,$message);
	goto error_label;
}

$request_startmonth = $_POST['startmonth'];
if (!$request_startmonth){
	$err_flag = true;
	$message = 'Syntax error: correct syntax is php upload_season_calender_batch.php STARTYEAR STARTMONTH ENDMONTH';
	array_push($errArray,$message);
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

$request_endmonth = $_POST['endmonth'];
if (!$request_endmonth){
	$err_flag = true;
	$message = 'Syntax error: correct syntax is php upload_season_calender_batch.php STARTYEAR STARTMONTH ENDMONTH';
	array_push($errArray,$message);
	goto error_label;
}

if (strlen($request_endmonth) === 1) {	// filling leading zero.
	$request_endmonth_str = '0'.$request_endmonth;
} else {
	$request_endmonth_str = $request_endmonth;
}

$request_mode = $_POST['mode'] ;
if ($request_mode ){
	$request_mode = 'replace';
}

require_once "./const/const.inc";
require_once "./func.inc";
require_once("./const/login_func.inc");
require_once("./const/token.php");
ini_set('include_path', CLIENT_LIBRALY_PATH);
set_time_limit(60);
define(API_TOKEN, '7511a32c7b6fd3d085f7c6cbe66049e7');

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

$sql = "SELECT COUNT(*) AS COUNT FROM tbl_schedule_onetime WHERE ( work_id=?  OR work_id=? ) AND delflag=0 AND ymd BETWEEN ? AND ?";
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
		$message = 'The schedule is already registerd. If you want to append the data, use mode=replace .';
		array_push($errArray,$message);
		goto error_label;
	}
				// check m2m data both tbl_schedule_onetime and tbl_season_schedule at first.

	// 1st cycle. Check season_class schedule on tbl_schedule_onetime that is not confired yet. 
	// if there is, logical delete the data.
	$sql = "UPDATE tbl_schedule_onetime SET delflag=1,deletetime=?,updateuser=-1 ";
	$sql .= " WHERE confirm!='f' AND ( work_id=?  OR work_id=? ) AND ymd BETWEEN ? AND ?";
	$stmt = $dbh->prepare($sql);
	$stmt->bindValue(1, $now, PDO::PARAM_STR);
	$stmt->bindValue(2, $target_work_id, PDO::PARAM_INT);
	$stmt->bindValue(3, $target_work_id2, PDO::PARAM_INT);
	$stmt->bindValue(4, $startofmonth, PDO::PARAM_STR);
	$stmt->bindValue(5, $endofmonth, PDO::PARAM_STR);
	$stmt->execute();

} else if ($already_exist == 0) {		// load new data.
	if ($request_mode == 'replace') {	// replace option is specified but no data exist..
		$err_flag = true;
		$message = 'The schedule is not registerd yet. Remove replace option and try again.';
		array_push($errArray,$message);
		goto error_label;
	}
}

	// 2nd cycle. If the date is already confirmed on tbl_schedule_onetime, skip the update. 
	// if the data is not confirm or not found , then insert the data .
	// delete previous schedule .

			// tbl_season_scheduleからman2manデータの取得
$startyearmonth_percent = $request_startyear.'/'.$request_startmonth_str.'%';
$endyearmonth_percent = $request_endyear.'/'.$request_endmonth_str.'%';
			// retrieve tbl_season_class_entry_date ( this has no teacher data.)
$sql = "SELECT date,stime,etime,member_id FROM tbl_season_class_entry_date ";
$sql .= " WHERE date LIKE ? OR date LIKE ? ORDER BY date,member_id";
$stmt = $db->prepare($sql);
$stmt->bindValue(1, $startyearmonth_percent, PDO::PARAM_STR);
$stmt->bindValue(2, $endyearmonth_percent, PDO::PARAM_STR);

$stmt->execute();
$season_entry_date_array = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ( $season_entry_date_array as $season_entry_date_row ) {
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

        $datewithslash = $season_entry_date_row['date'];
        $datewithhyphen = mb_ereg_replace('/','-',$datewithslash);
			// replace '/' with '-'

        $starttime = $season_entry_date_row['stime'];
	$timestamp_str = $datewithhyphen.' '.$starttime.':00';
	$dateObj = new DateTime($timestamp_str);
	$start_timestamp = $dateObj->getTimestamp();

        $endtime = $season_entry_date_row['etime'];
	$timestamp_str = $datewithhyphen.' '.$endtime.':00';
	$dateObj = new DateTime($timestamp_str);
	$end_timestamp = $dateObj->getTimestamp();

        $user_id = (int)$season_entry_date_row['member_id'] ;

			// check the target schedule is registered on the tbl_schedule_onetime.
	$onetime_schedule_status = check_target_schedule($dbh,$datewithhyphen,$start_timestamp,$end_timestamp,$user_id);

	if ($onetime_schedule_status == 'new'){
		// insert.
	} else if ($onetime_schedule_status == 'confirmed'){
		$message = "Error:already confirmed:user_id=$user_id,date=$datewithhyphen";
		array_push($errArray,$message);
		// skip insert process.
		continue;
	}

        $student_no = $user_id ;
        $student_id = (string)$student_no ;
	$student_id_len = strlen($student_id);
	if ($student_id_len == 1) {
                $student_id_complete = '00000'.$student_id;
        } else if ($student_id_len == 2) {
                $student_id_complete = '0000'.$student_id;
        } else if ($student_id_len == 3) {
                $student_id_complete = '000'.$student_id;
        } else if ($student_id_len == 4) {
                $student_id_complete = '00'.$student_id;
        } else if ($student_id_len == 5) {
                $student_id_complete = '0'.$student_id;
        }

					// tbl_season_schedule has total schedule both m2m and selfstudy.
	$sql = "SELECT stime,etime,lnum,teacher_no,course_id,lesson_id,subject_id FROM tbl_season_schedule ";
	$sql .= " WHERE date = ? AND member_no = ?";
	$stmt = $db->prepare($sql);
	$stmt->bindValue(1, $datewithslash, PDO::PARAM_STR);
	$stmt->bindValue(2, $student_id_complete, PDO::PARAM_STR);
	$stmt->execute();
	$season_schedule_array = $stmt->fetchAll(PDO::FETCH_ASSOC);
						//m2m schedule.
	foreach ( $season_schedule_array as $season_schedule_row ) {
				// initialization.
 		$temporary = 0; 
	  	$trial_id = ""; 
 	 	$alternate = ""; 
  		$altsched_id = 0; 
  		$cancel = ""; 
  		$cancel_reason = ""; 
  		$confirm = ""; 
  		$repetition_id = "" ; 
  		$absent1_num = 0; 
  		$absent2_num = 0; 
  		$trial_num = 0; 
  		$comment = ""; 

        	$place_id = 3 ; // Hachioji north 3F.
  		$work = 'season';
        	$lesson_id = (int)$season_schedule_row['lesson_id'] ;
        	$course_id = (int)$season_schedule_row['course_id'] ;
        	$subject_id = (int)$season_schedule_row['subject_id'] ;
        	$teacher_id = (int)$season_schedule_row['teacher_no'] ;
        	$teacher_id = $teacher_id + 100000 ;

		$starttime = $season_schedule_row['stime'];
		$timestamp_str = $datewithslash.' '.$starttime.':00';
		$dateObj = new DateTime($timestamp_str);
		$start_timestamp = $dateObj->getTimestamp();

		$endtime = $season_schedule_row['etime'];
		$timestamp_str = $datewithslash.' '.$endtime.':00';
		$dateObj = new DateTime($timestamp_str);
		$end_timestamp = $dateObj->getTimestamp();

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
				// 個別スケジュールへの挿入		
				// m2m のスケジュールをまず挿入する
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
					$recurrence_id,
					$absent1_num,
					$absent2_num,
					$trial_num,
					$subject_id);
	} 	// m2m のデータ投入終了
			// Initialization.
 	$temporary = 0; 
  	$trial_id = ""; 
  	$alternate = ""; 
  	$altsched_id = 0; 
  	$teacher_id = 0 ; 
  	$student_no = 0; 
  	$cancel = ""; 
  	$cancel_reason = ""; 
  	$confirm = ""; 
  	$work_id = 0 ; 
  	$repetition_id = "" ; 
  	$absent1_num = 0; 
  	$absent2_num = 0; 
  	$trial_num = 0; 
  	$comment = ""; 

        $starttime = $season_entry_date_row['stime'];
	$timestamp_str = $datewithslash.' '.$starttime.':00';
	$dateObj = new DateTime($timestamp_str);
	$startofday_ts = $dateObj->getTimestamp();

        $endtime = $season_entry_date_row['etime'];
	$timestamp_str = $datewithslash.' '.$endtime.':00';
	$dateObj = new DateTime($timestamp_str);
	$endofday_ts = $dateObj->getTimestamp();

	$attend_status = $season_entry_date_row['attend_status'];

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
					// 全体時間からm2mの時間を引いて演習時間を求める
	$status = insert_selfstudy_schedule($db,$dbh,$student_id_complete,$startofday_ts,$endofday_ts,$lecture_id,$place_id,$subject_id,$attend_status);
					// Search for m2m schedule.
} 	// end of for each tbl_season_classentry_data.

error_label:
	if ($err_flag === true){
//		var_dump($message);
	}
//} // the end of main program. 

/************* Single Insert ****************/

function insert_calender_event(&$dbh,$start_timestamp,$end_timestamp,$repetition_id,$user_id,$teacher_id,$student_no,$lecture_id,$work,$free,$cancel,$cancel_reason,$alternate,$altsched_id,$trial_id,$repeattimes,$place_id,$temporary,$comment,$recurrence_id,$absent1_num,$absent2_num,$trial_num,$subject_id ) {

global $work_list;
global $subject_list;
global $now;

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
	$updateuser = -1;
try{
						// not Repeting
	$sql = "INSERT INTO tbl_schedule_onetime (".
	" repetition_id, user_id,teacher_id,student_no,ymd,starttime,endtime,lecture_id,subject_expr,work_id,free,cancel,cancel_reason, ".
	" alternate,altsched_id,trial_id, absent1_num,absent2_num,trial_num,repeattimes,place_id,temporary,entrytime,updateuser, ".
	" comment,recurrence_id ".
	" ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
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
	$stmt->bindValue(24, $updateuser, PDO::PARAM_INT);
	$stmt->bindValue(25, $comment, PDO::PARAM_STR);
	$stmt->bindValue(26, $recurrence_id, PDO::PARAM_STR);
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

function insert_selfstudy_schedule(&$db,&$dbh,$student_id_complete,$startofday_ts,$endofday_ts,$lecture_id,$place_id,$subject_id,$attend_status ) {
				// for a given season schedule(member_id,startofdayts,endofdayts), make up selfstudy schedule. 
global $work_list;
global $subject_list;
global $now;

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

try{
	$sql = "SELECT date,stime,etime,member_no,lesson_id,subject_id,course_id FROM tbl_season_schedule WHERE member_no=? AND date=? ORDER BY stime";
	$stmt = $db->prepare($sql);
	$stmt->bindValue(1, $student_id_complete, PDO::PARAM_STR);	// 6桁の数値を表す文字列。先頭０．
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
  			$student_no = (int)$student_id_complete; 
  			$user_id = (int)$student_id_complete; 
  			$cancel = ""; 
  			$cancel_reason = ""; 
  			$repetition_id = "" ; 
  			$absent1_num = 0; 
  			$absent2_num = 0; 
  			$trial_num = 0; 
  			$comment = ""; 
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
					$recurrence_id,
					$absent1_num,
					$absent2_num,
					$trial_num,
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
  		$student_no = (int)$student_id_complete; 
  		$user_id = (int)$student_id_complete; 
  		$cancel = ""; 
  		$cancel_reason = ""; 
  		$repetition_id = "" ; 
  		$absent1_num = 0; 
  		$absent2_num = 0; 
  		$trial_num = 0; 
  		$comment = ""; 
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
				$recurrence_id,
				$absent1_num,
				$absent2_num,
				$trial_num,
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

function check_target_schedule(&$dbh,$datewithhyphen,$start_timestamp,$end_timestamp,$user_id){
			// This function check every season record on tbl_season_schedule.
			// If the schedule is already confirmed then skip the insert.
			// If the schedule is not confirmed, then delete previous schedule and insert the schedule on tbl_schedule_onetime.
			// if the schedule is not found , just insert the schedule to tbl_schedule_onetime.
			// return value: $onetimestatus 'new','confirmed','notconfirmed' 
	$starttime_str = date("H:i:s",$start_timestamp);
	$endtime_str = date("H:i:s",$end_timestamp);
try{
			// select from tbl_schedule_onetime.
	$sql = "SELECT COUNT(*) AS COUNT FROM tbl_schedule_onetime WHERE delflag=0 AND ymd=? AND starttime>=? AND endtime<=? AND user_id=? ";
	$stmt = $dbh->prepare($sql);
	$stmt->bindValue(1, $datewithhyphen, PDO::PARAM_STR);
	$stmt->bindValue(2, $starttime_str, PDO::PARAM_STR);
	$stmt->bindValue(3, $endtime_str, PDO::PARAM_STR);
	$stmt->bindValue(4, $user_id, PDO::PARAM_INT);
	$stmt->execute();
	$already_exist = (int)$stmt->fetchColumn();
	if ($already_exist == 0) {

			// if target data is notfound.
			$onetimestatus = 'new';
	} else {
			// if target data is found.
			// check if target data is confirmed.
		$sql = "SELECT COUNT(*) AS COUNT FROM tbl_schedule_onetime WHERE delflag=0 AND ymd=? AND starttime>=? AND endtime<=? AND user_id=? ";
		$sql .= "AND confirm='f'";
		$stmt = $dbh->prepare($sql);
		$stmt->bindValue(1, $datewithhyphen, PDO::PARAM_STR);
		$stmt->bindValue(2, $starttime_str, PDO::PARAM_STR);
		$stmt->bindValue(3, $endtime_str, PDO::PARAM_STR);
		$stmt->bindValue(4, $user_id, PDO::PARAM_INT);
		$stmt->execute();
		$confirmed_exist = (int)$stmt->fetchColumn();
		if ($confirmed_exist == 0 ){
			// if target data is notconfirmed.
			$onetimestatus = 'notconfirmed';
		} else {
			// if target data is confirmed.
			$onetimestatus = 'confirmed';
		}
			// if target data is notconfirmed.
	}

check_target_schedule_exit_label:
	return($onetimestatus);

}catch (PDOException $e){
	print_r('check_target_schedule:failed: ' . $e->getMessage());
	return false;
}
}

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF8">
<meta name="robots" content="noindex,nofollow">
<title>事務システム</title>
<style type="text/css">
<!--
 -->
</style>
<script type = "text/javascript">
<!--
-->
</script>
<link rel="stylesheet" type="text/css" href="./script/style.css">
<script type="text/javascript" src="./script/calender.js"></script>
</head>
<body>
<div align="center">
<?php
if ($err_flag == true) {
?>
	<a href='menu.php'><h4>季節講習スケジュールを取り込むことができませんでした。メニュー画面に戻る</h4></a>
<?php
        if (count($errArray) > 0) {
                foreach( $errArray as $error) {
?>
                        <font color="red"><?= $error ?></font><br><br>
<?php
                }
	}
} else {
	if (count($errArray) > 0) {
?>
	<a href='menu.php'><h4>季節講習スケジュールを取り込みましたがエラーがありました。メニュー画面に戻る</h4></a>
<?php
       		foreach( $errArray as $error) {
?>
               	        <font color="red"><?= $error ?></font><br><br>
<?php
       		}
       	} else {
?>
	<a href='menu.php'><h4>正常終了しました。メニュー画面に戻る</h4></a>
<?php
	}
}
?>
</div>
</body>
</html>
