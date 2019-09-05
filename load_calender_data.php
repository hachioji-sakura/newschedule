<?php
//echo "Program Start.\n";

ini_set( 'display_errors', 0 );
$err_flag = false ;

$request_year = $_GET['year'];
$request_year = str_replace("'","",$request_year);
$request_year = str_replace('"',"",$request_year);

$request_month = $_GET['month'];
$request_month = str_replace("'","",$request_month);
$request_month = str_replace('"',"",$request_month);

require_once "./const/const.inc";
require_once "./func.inc";
require_once "./const.inc";
require_once("./const/login_func.inc");
require_once("./const/token.php");
ini_set('include_path', CLIENT_LIBRALY_PATH);
//require_once "Google/autoload.php";
set_time_limit(60);
define(API_TOKEN, '7511a32c7b6fd3d085f7c6cbe66049e7');

define('CONST_ALTERNATE','振替:');
define('CONST_ABSENT','休み:');
define('CONST_ABSENT1','休み1:');
define('CONST_ABSENT2','休み2:');
define('CONST_ABSENT3','休み3:');
define('CONST_ABSENTOFF','休講');
define('CONST_ABSENTLATE','当日');
define('CONST_COLON',':');
define('CONST_INTERVIEW1',':三者面談1');
define('CONST_INTERVIEW2',':三者面談2');
define('CONST_INTERVIEW3',':面談');
define('CONST_TRIAL',':無料体験');
define('CONST_SENSEI',' 先生');
define('CONST_SAMA',' 様');
define('CONST_SAN',' さん');
define('CONST_FAMILY','ファミリー(');
define('CONST_GROUP','グループ(');
define('CONST_CLOSING',')');


// ****** メイン処理ここから ******

//echo($request_year);
//echo($request_month);

$teacher_list = get_teacher_list($db);

$member_list = get_member_list($db);

$lesson_list = get_lesson_list($db);

$subject_list = get_subject_list($db);

if (!$request_year){
	$err_flag = true;
	goto exit_label;
}

if ($request_year < 2015 ){
	$err_flag = true;
	goto exit_label;
}
$request_year = (int)$request_year;

if (!$request_month){
	$err_flag = true;
	goto exit_label;
}
if ($request_month < 1 || $request_month > 12){
	$err_flag = true;
	goto exit_label;
}
$request_month = (int)$request_month;

$request_startdate = $request_year.'-'.$request_month.'-'.'01'; 

$endtimestamp = mktime(0,0,0,$request_month + 1,0,$request_year);
$enddate = getdate($endtimestamp);
$request_enddate = $request_year.'-'.$request_month.'-'.$enddate['mday'];


// 20160522 セッション管理を追加
//$dbh->beginTransaction();
//$result = set_current_session();

mb_regex_encoding("UTF-8");
$teacher_list = get_teacher_list($db);
$member_list = get_member_list($db);
			// レッスンリストの取得

$now = date('Y-m-d H:i:s');
//$dbh=new PDO('mysql:host=mysql720.db.sakura.ne.jp;dbname=hachiojisakura_calendar;charset=utf8',DB_USER,DB_PASSWD2);
$dbh->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);

try{

	$sql = "SELECT insert_timestamp FROM tbl_fixed WHERE year=? AND month=?";
	$stmt = $dbh->prepare($sql);
	$stmt->bindValue(1, $request_year, PDO::PARAM_INT);
	$stmt->bindValue(2, $request_month, PDO::PARAM_INT);
	$stmt->execute();
	$rslt = $stmt->fetchAll(PDO::FETCH_ASSOC);

	if (!$rslt){ // not found
		print_r('target data is not commited.');
		goto exit_label;
	}

	$sql = "SELECT id, ".
	"repetition_id, user_id,teacher_id,student_no,ymd,starttime,endtime,lecture_id,work_id,free,cancel,cancel_reason,alternate,altsched_id,trial_id, ".
	"absent1_num,absent2_num,trial_num,repeattimes,place_id,temporary,entrytime,updatetime,updateuser,comment,googlecal_id,googleevent_id,recurrence_id".
	" FROM tbl_schedule_onetime WHERE ymd BETWEEN ? AND ? ";
	$stmt = $dbh->prepare($sql);
	$stmt->bindValue(1, $request_startdate, PDO::PARAM_STR);
	$stmt->bindValue(2, $request_enddate, PDO::PARAM_STR);
	$stmt->execute();
        $schedule_array = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ( $schedule_array as $row ) {
		$schedule_id	=	$row[id];
		$repetition_id	=	$row[repetition_id];
		$user_id 	=	(int)$row[user_id];
		$teacher_id	=	$row[teacher_id];
		$member_no	=	sprintf("%06d",$row[student_no]);
		$ymd		=	$row[ymd];
		$starttime	=	$row[starttime];
		$endtime	=	$row[endtime];
		$lecture_id	=	(int)$row[lecture_id];
		$work_id	=	$row[work_id];
		$free		=	$row[free];
		$cancel		=	$row[cancel];
		$cancel_reason	=	$row[cancel_reason];
		$alternate	=	$row[alternate];
		$altsched_id	=	(int)$row[altsched_id];
		$trial_id	=	$row[trial_id];
		$absent1_num	=	$row[absent1_num];
		$absent2_num	=	$row[absent2_num];
		$trial_num	=	$row[trial_num];
		$repeattimes	=	$row[repeattimes];
		$place_id	=	$row[place_id];
		$temporary	=	$row[temporary];
		$entrytime	=	$row[entrytime];
		$updated_timestamp	=	$row[updatetime];
		$googlecal_id	=	$row[googlecal_id];
		$googleevent_id =	$row[googleevent_id];
		$recurrence_id	=	$row[recurrence_id];
						// DB データの変換処理
		if ( $temporary > 0 && $temporary < 110 ) {
				// temporary. target data should be omitted.
			continue;
		}

		sscanf($ymd,'%d-%d-%d',$event_year,$event_month,$event_day);	

		$event_start_timestring = $ymd.' '.$starttime;
		$event_start_timestamp = strtotime($event_start_timestring);
		sscanf($starttime,'%d:%d:%d',$event_start_hour,$event_start_minute,$event_start_second);	

		$event_end_timestring = $ymd.' '.$endtime;
		$event_end_timestamp = strtotime($event_end_timestring);
		sscanf($endtime,'%d:%d:%d',$event_end_hour,$event_end_minute,$event_end_second);	

		$event_diff_hours = ($event_end_timestamp - $event_start_timestamp) / (60*60);

		$lecture_list = get_lecture_vector($db,$lecture_id);
		$evt_summary = '';			// Initialization.
		$row_cnt = count($lecture_list) ;
		if ($row_cnt  > 0) {
			$lesson_id = (int)$lecture_list[lesson_id];
			$course_id = (int)$lecture_list[course_id];
			$subject_id = (int)$lecture_list[subject_id];
			if ($course_id == '2' ) {		// Group
				$evt_summary = CONST_GROUP ;
			} else if ($course_id == '3' ) {  // family
				$evt_summary = CONST_FAMILY ;
			}
		}
			// making $evt_summary from tbl_schedule_onetime.

							// 休み処理
		if ($cancel_reason == CONST_ABSENTLATE ) { 
			$evt_summary = $evt_summary.CONST_ABSENTLATE;
			$evt_summary = $evt_summary.CONST_COLON;
		} else if ($cancel_reason == CONST_ABSENTOFF ) { 
			$evt_summary = $evt_summary.CONST_ABSENTOFF;
			$evt_summary = $evt_summary.CONST_COLON;
		} 
		if ($cancel == 'a1') { 
			$absent_flag = '1'; 
			$evt_summary = $evt_summary.CONST_ABSENT1;
			$event_diff_hours = 0;
		} else if ($cancel == 'a2') { 
			$absent_flag = '2';
			$evt_summary = $evt_summary.CONST_ABSENT2;
		} else if ($cancel == 'a3') {
			$absent_flag = '3'; 
			$evt_summary = $evt_summary.CONST_ABSENT3;
		} else if ($cancel == 'a') { 
			$absent_flag = '1'; 
			$evt_summary = $evt_summary.CONST_ABSENT;
			$event_diff_hours = 0;
		} else { $absent_flag = '0'; }

							// 振替処理
		if ($alternate !==' ' || $altsched_id !== 0 ) { 
			$alternative_flag = '1' ;  
//			$event_diff_hours = 0;
			$evt_summary = $evt_summary.CONST_ALTERNATE;
		} else {
			$alternative_flag = ' ';
		} 
							// 名前を文字列にする処理
		if ($user_id > 200000 ) { // staff
			
			$sql = "SELECT name FROM tbl_staff where no = ?";
			$stmt = $db->prepare($sql);
			$staff_no = $user_id - 200000;
			$stmt->bindValue(1,$staff_no, PDO::PARAM_INT);
			$stmt->execute();
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			$staff_cal_name = $result['name'];
			$evt_summary = $evt_summary.$staff_cal_name;
			$event_summary = $event_summary.$CONST_SAN;
		} else if ($user_id > 100000 ) { // teacher
			$member_cal_name = ' ';
			foreach ($teacher_list as $teacher) {
				if ($teacher['no'] == $user_id - 100000){
					$evt_summary = $evt_summary.$teacher['name'];
					$evt_summary = $evt_summary.CONST_SENSEI;
				}
			}
		} else if ($user_id > 0 ) { // student
			$member_cal_name = ' ';
			foreach ($member_list as $member) {
				if ($member['no'] == $user_id ){
					$member_cal_name = $member['name'];
					$grade = $member['grade'];
					$evt_summary = $evt_summary.$member_cal_name;
					$evt_summary = $evt_summary.CONST_SAMA;
				}
			}
		}
							// 面談を文字列にする処理
		if ($work == 'i1'){
			$interview_flag = '1';   
			$evt_summary = $evt_summary.CONST_INTERVIEW1;
		} else if ($work == 'i2'){
			$interview_flag = '2';   
			$evt_summary = $evt_summary.CONST_INTERVIEW2;
		} else if ($work == 'i3'){
			$interview_flag = '3';   
			$evt_summary = $evt_summary.CONST_INTERVIEW3;
		}

		if ($course_id == 2 || $course_id == 3 ) {		// Group or Family
			$evt_summary = $evt_summary.CONST_CLOSING ;
		}

		if ($subject_id) {		// setting subject name 
			$evt_summary = $evt_summary.CONST_COLON ;
			$evt_summary = $evt_summary.$subject_list[$subject_id];
		}

		if ($teacher_id) {		// setting teacher name 
			$evt_summary = $evt_summary.CONST_COLON ;
			foreach ($teacher_list as $teacher) {
				if ($teacher['no'] == $teacher_id - 100000){
					$evt_summary = $evt_summary.$teacher['name'];
					$evt_summary = $evt_summary.CONST_SENSEI;
				}
			}
		}
							// 体験を文字列にする処理
		if ($trial_id === '0' || $trial_id === ' ' ){
			$trial_flag = '0'; 
		} else {
			$trial_flag = '1'; 
			$evt_summary = $evt_summary.CONST_TRIAL;
		}  


		if ($user_id > 200000 ) {	// スタッフの場合
			$staff_no = $user_id - 200000 ;
                        $sql = "INSERT INTO tbl_event_staff ".
                        "(event_id, staff_no, staff_cal_name, event_year, event_month, event_day, event_start_timestamp, ".
                        " event_end_timestamp, event_diff_hours, place_id, absent_flag,".
                        " cal_id, cal_summary, cal_evt_summary, cal_evt_location, cal_evt_description, update_datetime".
                        " ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
			$stmt = $db->prepare($sql);
			$stmt->bindValue(1, $schedule_id, PDO::PARAM_INT);
			$stmt->bindValue(2, $staff_no, PDO::PARAM_INT);
			$stmt->bindValue(3, $staff_cal_name, PDO::PARAM_STR);  
			$stmt->bindValue(4, $event_year, PDO::PARAM_STR);  
			$stmt->bindValue(5, $event_month, PDO::PARAM_STR);  
			$stmt->bindValue(6, $event_day, PDO::PARAM_STR);  
			$stmt->bindValue(7, $event_start_timestamp, PDO::PARAM_STR);  
			$stmt->bindValue(8, $event_end_timestamp, PDO::PARAM_STR);  
			$stmt->bindValue(9, $event_diff_hours, PDO::PARAM_INT);  
			$stmt->bindValue(10, $place_id, PDO::PARAM_STR);  
			$stmt->bindValue(11, $absent_flag, PDO::PARAM_STR);  
			$stmt->bindValue(12, $googlecal_id, PDO::PARAM_STR);   
			$stmt->bindValue(13, $googlecal_summary, PDO::PARAM_STR);  // NULL値をセット 
			$stmt->bindValue(14, $evt_summary, PDO::PARAM_STR);   
			$stmt->bindValue(15, $googlecal_evt_location, PDO::PARAM_STR);  // NULL値をセット 
			$stmt->bindValue(16, $googlecal_evt_description, PDO::PARAM_STR);  // NULL値をセット 
			$stmt->bindValue(17, $now, PDO::PARAM_STR);   
			$stmt->execute();
               
		   // スタッフの場合の処理終了
		 }  else {			// スタッフでない場合
			if ($user_id > 100000){ // 先生の場合
				$member_no = 0; // 生徒がいない先生のスケジュール
			} 
			if ($teacher_id > 100000){ // 先生の場合
				$teacher_id = $teacher_id - 100000 ;
			} 
			$member_kind = 'student';

			$recurringEvent = '0' ;
			$repeat_flag = $repeattimes;  

                	$sql = "INSERT INTO tbl_event (".
                        " event_id, member_no, ".
                        " member_id, member_cal_name, member_kind, ".
                        " event_year, event_month, event_day, ".
                        " event_start_timestamp, event_start_hour, event_start_minute, ".
                        " event_end_timestamp, event_end_hour, event_end_minute, ".
                        " event_diff_hours, ".
                        " lesson_id, subject_id, course_id, teacher_id, place_id, absent_flag, trial_flag, interview_flag, alternative_flag,".
                        " absent1_num, absent2_num, trial_num, repeat_flag, ".
                        " cal_id, cal_summary, cal_evt_summary, cal_attendance_data, cal_evt_location, cal_evt_description, update_datetime, seikyu_year, seikyu_month,".
                        " recurringEvent, grade, monthly_fee_flag ".
                        " ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
                        $stmt = $db->prepare($sql);
			$stmt->bindValue(1, $schedule_id, PDO::PARAM_STR);
			$stmt->bindValue(2, $member_no, PDO::PARAM_STR);
			$stmt->bindValue(3, $member_id, PDO::PARAM_STR); 	// NULL値をセット
			$stmt->bindValue(4, $member_cal_name, PDO::PARAM_STR);  
			$stmt->bindValue(5, $member_kind, PDO::PARAM_STR);
			$stmt->bindValue(6, $event_year, PDO::PARAM_STR);  
			$stmt->bindValue(7, $event_month, PDO::PARAM_STR);  
			$stmt->bindValue(8, $event_day, PDO::PARAM_STR);  
			$stmt->bindValue(9, $event_start_timestamp, PDO::PARAM_STR);  
			$stmt->bindValue(10, $event_start_hour, PDO::PARAM_STR);  
			$stmt->bindValue(11, $event_start_minute, PDO::PARAM_STR);  
			$stmt->bindValue(12, $event_end_timestamp, PDO::PARAM_STR);  
			$stmt->bindValue(13, $event_end_hour, PDO::PARAM_STR);  
			$stmt->bindValue(14, $event_end_minute, PDO::PARAM_STR);  
			$stmt->bindValue(15, $event_diff_hours, PDO::PARAM_STR);  
			$stmt->bindValue(16, $lesson_id, PDO::PARAM_STR);  
			$stmt->bindValue(17, $subject_id, PDO::PARAM_STR);  
			$stmt->bindValue(18, $course_id, PDO::PARAM_STR);  
			$stmt->bindValue(19, $teacher_id, PDO::PARAM_STR);  
			$stmt->bindValue(20, $place_id, PDO::PARAM_STR);  
			$stmt->bindValue(21, $absent_flag, PDO::PARAM_STR);
			$stmt->bindValue(22, $trial_flag, PDO::PARAM_STR);  
			$stmt->bindValue(23, $interview_flag, PDO::PARAM_STR);  
			$stmt->bindValue(24, $alternative_flag, PDO::PARAM_STR);  
			$stmt->bindValue(25, $absent1_num, PDO::PARAM_INT);  
			$stmt->bindValue(26, $absent2_num, PDO::PARAM_INT);  
			$stmt->bindValue(27, $trial_num, PDO::PARAM_INT);  
			$stmt->bindValue(28, $repeat_flag, PDO::PARAM_INT);  
			$stmt->bindValue(29, $googlecal_id, PDO::PARAM_STR);   
			$stmt->bindValue(30, $googlecal_summary, PDO::PARAM_STR);  // NULL値をセット 
			$stmt->bindValue(31, $evt_summary, PDO::PARAM_STR);  
			$cal_attendance_data = $evt_summary ; 
			$stmt->bindValue(32, $cal_attendance_data, PDO::PARAM_STR);  // $evt_summary と同じ値をセット 
			$stmt->bindValue(33, $googlecal_evt_location, PDO::PARAM_STR);  // NULL値をセット 
			$stmt->bindValue(34, $googlecal_evt_description, PDO::PARAM_STR);  // NULL値をセット 
			$stmt->bindValue(35, $now, PDO::PARAM_STR);   
			$stmt->bindValue(36, $request_year, PDO::PARAM_STR);   
			$stmt->bindValue(37, $request_month, PDO::PARAM_STR);   
			$stmt->bindValue(38, $recurringEvent, PDO::PARAM_STR);   
			$stmt->bindValue(39, $grade, PDO::PARAM_STR);   
			$stmt->bindValue(40, $monthly_fee_flag, PDO::PARAM_STR);  // NULL値をセット 
			$stmt->execute();
		}	// スタッフでない場合
        }		// end of foreach

exit_label:
}catch (PDOException $e){
	print_r('insert_calender_event:failed: ' . $e->getMessage());
	return false;
}

// ****** メイン処理ここまで ******


// レクチャIDからレッスンID,コースID、科目IDを取得する
function get_lecture_vector(&$db,$lecture_id) {
        $sql = "SELECT lesson_id,course_id,subject_id FROM tbl_lecture WHERE lecture_id = ?";
        $stmt = $db->prepare($sql);
	$stmt->bindValue(1, $lecture_id, PDO::PARAM_INT);   
        $stmt->execute();
        $lecture_array = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $lecture_list = array();
        foreach ( $lecture_array as $row ) {
                $lecture_list = $row;
        }
        return $lecture_list;
}

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

        <h4><font color="red">カレンダーデータべースに取り込むことができませんでした。</font></h4>

<?php
        if (count($errArray) > 0) {
                foreach( $errArray as $error) {
?>
                        <font color="red"><?= $error ?></font><br><br>
<?php
                }
        }
} else {
}
?>

</div>
</body>
</html>

