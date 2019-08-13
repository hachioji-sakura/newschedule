<?php
//echo "Program Start.\n";

ini_set( 'display_errors', 0 );

$request_repeat = $argv[1];

$request_min_month = $argv[2];

$request_min_day = $argv[3];

$request_max_month = $argv[4];

$request_max_day = $argv[5];
require_once "/home/hachiojisakura/www/sakura01/schedule/const/const.inc";
require_once "/home/hachiojisakura/www/sakura01/schedule/func.inc";
require_once("/home/hachiojisakura/www/sakura01/schedule/const/login_func.inc");
require_once("/home/hachiojisakura/www/sakura01/schedule/const/token.php");
ini_set('include_path', CLIENT_LIBRALY_PATH);
require_once "Google/autoload.php";
set_time_limit(60);
define(API_TOKEN, '7511a32c7b6fd3d085f7c6cbe66049e7');
// ****** メイン処理ここから ******

//$result = check_user($db, "1");

// 20160522 セッション管理を追加
//$dbh->beginTransaction();
//$result = set_current_session();

$calender_auth = new GoogleCalenderAuth();

$service = $calender_auth->getCalenderService();

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

// Google カレンダーからデータを取得
$calList = $service->calendarList->listCalendarList();
$err_flag = false;
$errArray = array();

// For temporary
$now = date('Y/m/d');
$dbh=new PDO('mysql:host=mysql720.db.sakura.ne.jp;dbname=hachiojisakura_calendar;charset=utf8',DB_USER,DB_PASSWD2);
$dbh->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
// For temporary End.

$work_list = get_work_list($dbh);

class GoogleCalenderAuth {

	private static $client;
	private static $service;

	public static function getCalenderService() {
		if (! isset(self::$client)) {
			self::createClient();
		}
		
		if (! isset(self::$service)) {
			self::$service = new Google_Service_Calendar(self::$client);
		}
		return self::$service;
	}

	private static function createClient() {
		self::$client = new Google_Client();
		//self::$client->setApplicationName('Application Name');
		self::$client->setClientId(CLIENT_ID);

		$credential = new Google_Auth_AssertionCredentials(
											SERVICE_ACCOUNT_NAME,
											array('https://www.googleapis.com/auth/calendar.readonly'),
											file_get_contents(KEY_FILE)
											);
		self::$client->setAssertionCredentials($credential);
	}
}

while(true) {
	if ($err_flag == true) { break; }
		// カレンダー種別ごとの処理

foreach ($calList->getItems() as $calender) {
	$googlecal_id = $calender["id"];
var_dump($calender['summary']);
	if ($err_flag == true) { 
		var_dump($err_flag);
		break; 
	}

	// maxResultsのデフォルト値は250件
	$objMinDateTime = new DateTime();
        $objMaxDateTime = new DateTime();

	$year = 2019;
	$minmonth = $request_min_month;
	$minday = $request_min_day;
	$maxmonth = $request_max_month;
	$maxday = $request_max_day;
        $objMinDateTime->setTimeStamp(mktime(0, 0, 0, $minmonth, $minday, $year));
        $objMaxDateTime->setTimeStamp(mktime(23, 59, 59, $maxmonth, $maxday, $year));

	$minDateTime = $objMinDateTime->format(DateTime::ISO8601);
        $maxDateTime = $objMaxDateTime->format(DateTime::ISO8601);

	if ($request_repeat=='repeat') { 
			// 繰り返しスケジュールの取得 'singleEvents'=>'False'にしても結果変わらず
		$optParams = array('maxResults' => 500, 'timeMin' => $minDateTime, 'timeMax' => $maxDateTime, 'orderBy'=>'startTime', 'singleEvents'=>'True');
	} else {
		$optParams = array('maxResults' => 500, 'timeMin' => $minDateTime, 'timeMax' => $maxDateTime, 'orderBy'=>'startTime', 'singleEvents'=>'True');
	}
			// イベントリストの取得
	$events = $service->events->listEvents($calender['id'], $optParams);
			// カレンダー名から場所、作業内容、レッスン名、コース名を取得 →　後で必要に応じて上書き
	while(true) {
		if ($err_flag == true) { break; }
		// イベントごと（カレンダーに入力された予定ごと）に処理をする
	foreach ($events->getItems() as $event) {
		$googleevent_id = $event["id"];
		$event["calender_summary"] = $calender["summary"];

	  	$temporary = 0; // Initialization.
	  	$trial_id = " "; // Initialization.
	  	$alternate = " "; // Initialization.
	  	$altsched_id = 0; // Initialization.
	  	$teacher_id = 0 ; // Initialization.
	  	$student_no = 0; // Initialization.
	  	$user_id = 0 ; // Initialization.
	  	$cancel = " "; // Initialization.
	  	$lecture_id = 0 ; // Initialization.
	  	$lesson_id = 0 ; // Initialization.
	  	$course_id = 0 ; // Initialization.
	  	$subject_id = 0 ; // Initialization.
	  	$work_id = 0 ; // Initialization.
	  	$repetition_id = " " ; // Initialization.
	  	$absent1_num = 0; // Initialization.
	  	$absent2_num = 0; // Initialization.
	  	$trial_num = 0; // Initialization.
		$start_timestamp = null;
		$end_timestamp = null;

				// Event Summary からマルチバイトを除去
		$tmp_event_summary = str_replace(array("　", "（", "）", "：", "︰", "＊"), array(" ", "(", ")", ":", ":", "*"), trim($event['summary']));
                if (preg_match("/^\*/", $tmp_event_summary, $matches, PREG_OFFSET_CAPTURE) == 1) {
       		         // 20160306 先頭に＊がある場合は処理をとばす
       	         	$asterisk_array[] = $tmp_event_summary;
       	         	continue;
  		}

		if (preg_match("/(\(仮\)|\(not\s*defin|\(not\s*confirm|temporary)/i", $tmp_event_summary, $matches, PREG_OFFSET_CAPTURE) == 1) {
			$temporary = 1;
			$tmp_event_summary = str_replace(array("(仮)"), array(""), trim($event_summary));
		}
		$recurrence_id = " ";
		$rrule = " ";
		$recurrence_id = $event['recurringEventId'] ;
		if ($request_repeat=='repeat') { //繰り返し情報のカレンダーへの入力
			if(!$recurrence_id) {
				// 繰り返しでないスケジュールは処理対象から除外する
				continue;
			}
			$original_repeat_event = $service->events->get($googlecal_id,$event['recurringEventId']);
			$rrule = $original_repeat_event['recurrence'][0];
//var_dump($original_repeat_event);
			$rrule_array = analyze_rrule($rrule);
			$kind = $rrule_array[0][0];
			$dayofmonth = $rrule_array[0][1];
			$dayofweek = $rrule_array[0][2];
			$untildate = $rrule_array[0][3];
			$wkst = $rrule_array[0][4];
			if ($kind =='w' && $dayofweek ==' '){ // weekly but dayofweek is not specified.
				$dateObj = new DateTime($event['start']['dateTime']);
                        	$start_timestamp = $dateObj -> getTimestamp();
                        	$start_date = getdate($start_timestamp);
                        	$wday = $start_date['wday'];
                        	$weekdaylabel = array('SU','MO','TU','WE','TH','FR','SA');
				$dayofweek = $weekdaylabel[$wday];
			}
		}
		if (mb_strpos($event['calender_summary'], "事務") !== FALSE) {
			$dateObj = new DateTime($event['start']['dateTime']);
                        $start_timestamp = $dateObj -> getTimestamp();
//                      $end_timestamp = $event['event_end_timestamp'];
			$dateObj = new DateTime($event['end']['dateTime']);
                        $end_timestamp = $dateObj -> getTimestamp();
                        //$diff_hours = ($end_timestamp - $start_timestamp) / (60*60);
                        //$absent_flag = 0;
                        $cancel = " ";
                        $blocks = explode(':', $tmp_event_summary);
                        $user_id = ''; $staff_no = ''; $staff_name = '';
                        $work = "ow";	 	// setting work shortname
                        foreach ($blocks as $block) {
                                if (preg_match("/(\S+ \S+) さん/u", $block, $matches) == 1) {
                                        $staff_name = $matches[1];
                                        $sql = "SELECT no FROM tbl_staff WHERE name = ?";
                                        $stmt = $db->prepare($sql);
					$stmt->bindValue(1, $staff_name, PDO::PARAM_STR);
                                        $stmt->execute();
					$result = $stmt->fetch(PDO::FETCH_ASSOC);
					$staff_no = (int)$result["no"];
					$user_id = $staff_no + 200000 ;
                                } else if (preg_match("/休み/u", $block, $matches) == 1) {
                                     // $absent_flag = 1;
                                        $cancel = "a";
                                }
                        }

			if ($request_repeat != 'repeat'){	
				// 個別スケジュール	
				$result = insert_calender_event($dbh,$event,$start_timestamp,$end_timestamp,$repetition_id,$user_id,$teacher_id,$student_no,$lecture_id,$work,$free,$cancel,$alternate,$altsched_id,$trial_id,$repeattimes,$place_id,$temporary,$comment,$googlecal_id,$googleevent_id,$recurrence_id,$absent1_num,$absent2_num,$trial_num,$monthly_fee_flag,$subject_id);
			} else {
				// くりかえしスケジュール	
				$result = insert_calender_repeatevent($dbh,$event,$start_timestamp,$end_timestamp,$repetition_id,$user_id,$teacher_id,$student_no,$lecture_id,$work,$free,$place_id,$comment,$kind,$googlecal_id,$googleevent_id,$recurrence_id,$rrule,$dayofmonth,$dayofweek,$untildate,$wkst,$subject_id);

			}
			if ($result === false) {
				$err_flag = true;
      				break;
			}
                        //$sql =
                        //        "INSERT INTO tbl_event_staff ".
                        //        "(event_id, staff_no, staff_cal_name, event_year, event_month, event_day, event_start_timestamp, event_end_timestamp, event_diff_hours, place_id, absent_flag,".
                        //        " cal_id, cal_summary, cal_evt_summary, cal_evt_location, cal_evt_description, cal_evt_updated_timestamp, insert_datetime, update_datetime) VALUES".
                        //        "(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,now(),now())";
 		
			continue;
               
		 }  // スタッフの場合の処理終了
              			// カレンダーの予定ごとから生徒ごとの配列に変換する
               $event_param_array = get_event_param($db, $event, $errArray, $target_teacher_id);

                if ($event_param_array === false) {
                        if ($target_teacher_id) { continue; }
                        $err_flag = true;
		        break;
                }

                foreach ($event_param_array as $event_param) {
				// Initialization.

			$repetition_id = " ";
			$free = " ";
			$repeattimes = 0;
			$comment = " ";
			if (is_null($event['start']['dateTime']) === true) {
				// 開始日付のないものは処理しない
				continue;
			} else {
                	        $start_timestamp = DateTime::createFromFormat(DateTime::ISO8601, $event['start']['dateTime'])->getTimestamp();
                	}
			if (is_null($event['end']) === true) {
				// 終了日付のないものは処理しない
				continue;
			} else {
                       		$end_timestamp = DateTime::createFromFormat(DateTime::ISO8601, $event['end']['dateTime'])->getTimestamp();
               		}
			$lesson_id = $event_param[15];
			$subject_id = $event_param[16];
			$course_id = $event_param[17];
			if ($lesson_id && $subject_id && $course_id) {
				$sql = "SELECT lecture_id FROM tbl_lecture WHERE lesson_id = ? AND course_id=? AND subject_id= ? ";
       	               		$stmt = $db->prepare($sql);
				$stmt->bindValue(1, $lesson_id, PDO::PARAM_INT);
				$stmt->bindValue(2, $course_id, PDO::PARAM_INT);
				$stmt->bindValue(3, $subject_id, PDO::PARAM_INT);
				$stmt->execute();
                       		$result = $stmt->fetch(PDO::FETCH_ASSOC);
                       		$lecture_id = $result['lecture_id'];
			}
					// making new id
			$student_no = (int)$event_param[1];
			$teacher_id = (int)$event_param[18] + 100000 ;
			if ($student_no) {
				$user_id = $student_no;
			} else {
				$user_id = $teacher_id;
			}
			$place_id = $event_param[19];
			if ( $event_param[20] == 0){
				$calcel = ' ';
			} else if ($event_param[20] == '1'){
				$cancel = 'a1';
			} else if ($event_param[20] == '2'){
				$cancel = 'a2';
			}
			$trial_id = $event_param[21];
			$interview_flag = $event_param[22];
			$work = $event_param[40];
			$alternate = ' ';
			if ($event_param[23] == '1') {
				$alternate = 'a';
			}
			$absent1_num = $event_param[24];
			$absent2_num = $event_param[25];
			$trial_num = $event_param[26];
			$monthly_fee_flag = $event_param[39];
			if ($request_repeat != 'repeat'){	
				// 個別スケジュール		
			$result = insert_calender_event($dbh,$event,$start_timestamp,$end_timestamp,$repetition_id,$user_id,$teacher_id,$student_no,$lecture_id,$work,$free,$cancel,$alternate,$altsched_id,$trial_id,$repeattimes,$place_id,$temporary,$comment,$googlecal_id,$googleevent_id,$recurrence_id,$absent1_num,$absent2_num,$trial_num,$monthly_fee_flag,$subject_id);
			} else {
				// 繰り返しスケジュール		
			$result = insert_calender_repeatevent($dbh,$event,$start_timestamp,$end_timestamp,$repetition_id,$user_id,$teacher_id,$student_no,$lecture_id,$work,$free,$place_id,$comment,$kind,$googlecal_id,$googleevent_id,$recurrence_id,$rrule,$dayofmonth,$dayofweek,$untildate,$wkst,$subject_id);

			}
			if ($result === false) {
				$err_flag = true;
      				break;
			}
                }  // foreach for a $event_param
                if ($err_flag == true) break;
	}  // foreach ($events->getItems() as $event) {

	if ($err_flag == true) { break; } // break:foreach ($calList->getItems() as $calender)

	//} // 一つの予定に対する処理 foreach
	$pageToken = $events->getNextPageToken();
	if ($pageToken) {
       		 $optParams = array('maxResults' => 500, 'timeMin' => $minDateTime, 'timeMax' => $maxDateTime, 'pageToken' => $pageToken, 'orderBy'=>'startTime', 'singleEvents'=>'True');
        	$events = $service->events->listEvents($calender['id'], $optParams);
	} else {
       		break; // break:while(true) Inner
	}
	}   // Repeat while(true) Inner. 
} // for each . カレンダー種別ごとの繰り返し
  if ($err_flag == true) { break; } // break:while(true)
  $pageToken = $calList->getNextPageToken();
  if ($pageToken) {
 	$optParams = array('pageToken' => $pageToken);
    	$calList = $service->calendarList->listCalendarList($optParams);
  } else {
  	 break;
  }
} //repeat while (true) Outer. 

//if ($err_flag == true) {
//	$dbh->rollBack();
//} else {
//        $db->commit();
//}

// ****** メイン処理ここまで ******

/************* Single Insert ****************/

function insert_calender_event(&$dbh,$event,$start_timestamp,$end_timestamp,$repetition_id,$user_id,$teacher_id,$student_no,$lecture_id,$work,$free,$cancel,$alternate,$altsched_id,$trial_id,$repeattimes,$place_id,$temporary,$comment,$googlecal_id,$googleevent_id,$recurrence_id,$absent1_num,$absent2_num,$trial_num,$monthly_fee_flag,$subject_id ) {

global $work_list;
global $subject_list;

try{
	$event_id = $event["id"];
					// Event Location には場所が記録されていない。カレンダー名が場所を記録している。
	$startymd = date('Y-m-d',$start_timestamp);
	$starttime = date('H:i:s',$start_timestamp);
	$endymd = date('Y-m-d',$end_timestamp);
	$endtime = date('H:i:s',$end_timestamp);
	if ($startymd != $endymd) {
					// 開始日と終了日が異なる 
		goto exit_label;
	}
	$ymd = $startymd;	
	$event_updated_timestamp = $event['updated'];
					// tbl_schedule_onetimeに挿入する項目の設定
	$repetition_id = 0; // 定期的スケジュールの識別子。暫定で０とする
//	$free = ' ';  // 現時点で自由時間の予定は入力していない
//	$repeattimes = 0; //一時的に０にしておく 
//	$entrytime =    // 暫定的に設定しない
	$updatetime = date('Y-m-d H-i-s',$event_updated_timestamp); 
	$comment =  " " ;	// 一時的に０にしておく 
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
						// not Repeting
	$sql = "INSERT INTO tbl_schedule_onetime (".
//	$sql = "INSERT INTO tbl_schedule_onetime_test (".
	" repetition_id, user_id,teacher_id,student_no,ymd,starttime,endtime,lecture_id,subject_expr,work_id,free,cancel,alternate,altsched_id,trial_id, ".
	" absent1_num,absent2_num,trial_num,repeattimes,place_id,temporary,entrytime,updatetime,updateuser,comment,googlecal_id,googleevent_id,recurrence_id ,monthly_fee_flag".
	" ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
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
	$stmt->bindValue(13, $alternate, PDO::PARAM_STR);
	$stmt->bindValue(14, $altsched_id, PDO::PARAM_STR);
	$stmt->bindValue(15, $trial_id, PDO::PARAM_STR);
	$stmt->bindValue(16, $absent1_num, PDO::PARAM_INT);
	$stmt->bindValue(17, $absent2_num, PDO::PARAM_INT);
	$stmt->bindValue(18, $trial_num, PDO::PARAM_INT);
	$stmt->bindValue(19, $repeattimes, PDO::PARAM_INT);
	$stmt->bindValue(20, $place_id, PDO::PARAM_INT);
	$stmt->bindValue(21, $temporary, PDO::PARAM_INT);
	$stmt->bindValue(22, $entrytime, PDO::PARAM_STR);
	$stmt->bindValue(23, $event_updated_timestamp, PDO::PARAM_STR);
	$stmt->bindValue(24, $updateuser, PDO::PARAM_INT);
	$stmt->bindValue(25, $comment, PDO::PARAM_STR);
	$stmt->bindValue(26, $googlecal_id, PDO::PARAM_STR);
	$stmt->bindValue(27, $googleevent_id, PDO::PARAM_STR);
	$stmt->bindValue(28, $recurrence_id, PDO::PARAM_STR);
	$stmt->bindValue(29, $monthly_fee_flag, PDO::PARAM_INT);
//var_dump($sql);
	$stmt->execute();
exit_label:
}catch (PDOException $e){
	print_r('insert_calender_event:failed: ' . $e->getMessage());
	return false;
}
return $event_no;
} // End:event_insert

/************* Repeat Insert ****************/


function insert_calender_repeatevent(&$dbh,$event,$start_timestamp,$end_timestamp,$repetition_id,$user_id,$teacher_id,$student_no,$lecture_id,$work,$free,$place_id,$comment,$kind,$googlecal_id,$googleevent_id,$recurrence_id,$rrule,$dayofmonth,$dayofweek,$untildate,$wkst,$subject_id ) {

global $work_list;
global $subject_list;

try{
					// Event Location には場所が記録されていない。カレンダー名が場所を記録している。
	$startymd = date('Y-m-d',$start_timestamp);
	$starttime = date('H:i:s',$start_timestamp);
	$endymd = date('Y-m-d',$end_timestamp);
	$enddate = $untildate;
	$endtime = date('H:i:s',$end_timestamp);
	if ($startymd != $endymd) {
					// 開始日と終了日が異なる 
		goto exit_label;
	}
	$ymd = $startymd;

		// 既に当該エントリが登録済かをチェックする
	$sql = "SELECT count(*) AS COUNT FROM tbl_schedule_repeat WHERE delflag = 0 AND user_id = ? AND recurrence_id = ? ";
       	$stmt = $dbh->prepare($sql);
	$stmt->bindValue(1, $user_id, PDO::PARAM_INT);
	$stmt->bindValue(2, $recurrence_id, PDO::PARAM_STR);
	$stmt ->execute();
       	$result = $stmt->fetchColumn();
	if ($result != 0){
		// The schedule is already exist.
		goto exit_label;
	}
	
	$event_updated_timestamp = $event['updated'];
					// tbl_schedule_onetimeに挿入する項目の設定
	$repetition_id = 0; // 定期的スケジュールの識別子。暫定で０とする
//	$free = ' ';  // 現時点で自由時間の予定は入力していない
//	$repeattimes = 0; //一時的に０にしておく 
//	$entrytime =    // 暫定的に設定しない
	$updatetime = date('Y-m-d H-i-s',$event_updated_timestamp); 
	$comment =  " " ;	// 一時的にスペースにしておく 
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

						// Repeting
	$sql = "INSERT INTO tbl_schedule_repeat (".
	" user_id,teacher_id,student_no,subject_expr,group_lesson_id,kind,dayofweek,dayofmonth,startdate,enddate,starttime,endtime, ".
	" lecture_id,work_id,free,place_id,entrytime,updatetime,updateuser,comment,googlecal_id,googleevent_id,recurrence_id,rrule,wkst,untildate ".
	" ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
//var_dump($sql);
	$stmt = $dbh->prepare($sql);
	$stmt->bindValue(1, $user_id, PDO::PARAM_INT);
	$stmt->bindValue(2, $teacher_id, PDO::PARAM_INT);
	$stmt->bindValue(3, $student_no, PDO::PARAM_INT);
	$stmt->bindValue(4, $subject_expr, PDO::PARAM_STR);
	$stmt->bindValue(5, $group_lesson_id, PDO::PARAM_STR);
	$stmt->bindValue(6, $kind, PDO::PARAM_STR);
	$stmt->bindValue(7, $dayofweek, PDO::PARAM_STR);
	$stmt->bindValue(8, $dayofmonth, PDO::PARAM_STR);
	$stmt->bindValue(9, $startdate, PDO::PARAM_STR);
	$stmt->bindValue(10, $enddate, PDO::PARAM_STR);
	$stmt->bindValue(11, $starttime, PDO::PARAM_STR);
	$stmt->bindValue(12, $endtime, PDO::PARAM_STR);
	$stmt->bindValue(13, $lecture_id, PDO::PARAM_INT);
	$stmt->bindValue(14, $work_id, PDO::PARAM_INT);
	$stmt->bindValue(15, $free, PDO::PARAM_STR);
	$stmt->bindValue(16, $place_id, PDO::PARAM_INT);
	$stmt->bindValue(17, $entrytime, PDO::PARAM_STR);
	$stmt->bindValue(18, $event_updated_timestamp, PDO::PARAM_STR);
	$stmt->bindValue(19, $updateuser, PDO::PARAM_INT);
	$stmt->bindValue(20, $comment, PDO::PARAM_STR);
	$stmt->bindValue(21, $googlecal_id, PDO::PARAM_STR);
	$stmt->bindValue(22, $googleevent_id, PDO::PARAM_STR);
	$stmt->bindValue(23, $recurrence_id, PDO::PARAM_STR);
	$stmt->bindValue(24, $rrule, PDO::PARAM_STR);
	$stmt->bindValue(25, $wkst, PDO::PARAM_STR);
	$stmt->bindValue(26, $untildate, PDO::PARAM_STR);
	$stmt->execute();
exit_label:
}catch (PDOException $e){
	print_r('insert_calender_event:failed: ' . $e->getMessage());
	return false;
}
return $event_no;
} // End:event_insert


function get_family(&$db, $family_data) {
        global $member_list, $grade_list;
        $family_array = array();
        $absent1_num = 0;
        $absent2_num = 0;
        $trial_num = 0;
        $trial_id = " ";
        $work = " ";
        $cancel = " ";
        $absent_flag = "0";     // 全員が休みの場合
        $trial_flag = "0";      // 全員が無料体験の場合
        $interview_flag = "0";  // 全員が面談か三者面談の場合
        $cal_name = "";         // スペースが入っている明細書の宛先用
        $tmp_cal_name = "";     // スペースが入っていない比較用
        $tmp_db_name = "";      // スペースが入っていない比較用

        	// 前後の半角スペースを削除しておく
        $family_data = trim($family_data);

        	// 全員が無料体験の場合　()の中に「:無料体験」が入る
        if (preg_match("/(無料体験|体験|trial|Trial)/", $family_data, $matches, PREG_OFFSET_CAPTURE) == 1) {
                $trial_flag = "1";
                $trial = "1";
        }
        	// 全員は無料体験でないが誰かは無料体験の場合があるかどうか不明。確認が>必要。
        if (preg_match("/三者面談1|面談1/", $family_data, $matches, PREG_OFFSET_CAPTURE) == 1) {
                $interview_flag = "1";
                $work = "i1";
        } else if (preg_match("/三者面談2|面談2|三者面談/", $family_data, $matches, PREG_OFFSET_CAPTURE) == 1) {
                $interview_flag = "2";
                $work = "i2";
        } else if (preg_match("/面談/", $family_data, $matches, PREG_OFFSET_CAPTURE) == 1) {
                $interview_flag = "3";
                $work = "i3";
        }

        		// 全員が振替の場合　()の中に「:振替」が入る
        if (preg_match("/振替|alternative|Alternative/", $family_data, $matches, PREG_OFFSET_CAPTURE) == 1) {
                $alternative_flag = "1";
                $alternate = "a";
        }    
 	$search_array = array("休み3","Absent3","absent3","休み2","Absent2","absent2","休み1","Absent1",
                                "absent1","休み","Absent","absent",":","振替","alternative","Alternative","make-up","Today","No_class","No class",
                                "当日");
        $replace_array = array("", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "");
        $tmp1_cal_name = str_replace($search_array, $replace_array, $family_data);
        $search_array = array("無料体験","体験","trial","Trial","三者面談1","面談1","三者面談2","面談2","三者面談","面談","休講");
        $replace_array = array("", "", "", "", "", "", "", "", "", "", "");
        $tmp2_cal_name = str_replace($search_array, $replace_array, $tmp1_cal_name);
        $tmp_cal_name = str_replace(array(" "), array(""), $tmp2_cal_name);// 比較のため半角スペースも除去

        if ( $trial_flag != "0" ) { $cal_name = "体験生徒"; $tmp_cal_name = $cal_name; }

       	$member_no = "";
        foreach ($member_list as $no => $member) {

            $tmp_db_name = str_replace(array("　", " "), array(" ", ""), $member['name']);// 比較のため半角スペースも除去
            if (preg_match("/^".preg_quote($tmp_db_name,"/")."様/u", $tmp_cal_name, $name_matches, PREG_OFFSET_CAPTURE) == 1) {
                    $member_no = $member["no"];
                    break;
            }
        } // End:foreach ($member_list as $id => $member)

        if ($member_no == "") {
                if (strpos($family_data, "様") !== FALSE || preg_match("/[A-Za-z]+/", $family_data, $matches, PREG_OFFSET_CAPTURE) == 1) {
                                // add_studentは、登録に成功したらmember_noを、>登録に失敗したらfalseを返す
 	         }
        }
        if (preg_match("/^(休み3|Absent3|absent3)/", $family_data, $matches, PREG_OFFSET_CAPTURE) == 1) {
                $absent_flag = "3";
                $cancel = "a3";
        }
        if (preg_match("/^(休み2|Absent2|absent2)/", $family_data, $matches, PREG_OFFSET_CAPTURE) == 1) {
                $absent_flag = "2";
                $cancel = "a2";
        }
        if (preg_match("/^(休み1|Absent1|absent1)/", $family_data, $matches, PREG_OFFSET_CAPTURE) == 1) {
                $absent_flag = "1";
                $cancel = "a1";
        }
        		//休み1、休み2のとき「休み」と「休み1」または「休み2」があってしまうため、あとからチェックする
        if ($absent_flag != "1" && $absent_flag != "2" && $absent_flag != "3") {
                	// 授業料が発生する休みの人数を取得する
        	$absent2_num = preg_match_all( "/休み2|Absent2|absent2/", $family_data, $matches, PREG_PATTERN_ORDER);
                	// 授業料が発生しない休みの人数を取得する
                $tmp1_absent1_num = preg_match_all( "/休み1|Absent1|absent1/", $family_data, $matches, PREG_PATTERN_ORDER);
                	// 休みのとき
                $tmp2_absent1_num = preg_match_all( "/休み[^1-2]|Absent[^1-2]|absent[^1-2]/", $family_data, $matches, PREG_PATTERN_ORDER);
                $absent1_num = $tmp1_absent1_num + $tmp2_absent1_num;

                if ($member_no) {
                        $member_count = count(explode(' ',$member_list[$member_no]['name']))-1;
                        if ($member_count==$absent1_num) {$absent_flag=1;$cancel="a1"; }
                        if ($member_count==$absent2_num) {$absent_flag=2;$cancel="a2"; }
                }
        }
	$grdade = '';
        foreach($grade_list as $key1=>$str) { if (strpos($family_data,$str)!==false) { $grade=$key1; } }

        $student_array = array("id"=>"", "no"=>$member_no, "kind"=>"student", "absent_flag"=>$absent_flag, "trial_flag"=>$trial_flag,
                               "interview_flag"=>$interview_flag,  "alternative_flag"=>$alternative_flag,
                   "cal_name"=>$cal_name, "absent1_num"=>$absent1_num, "absent2_num"=>$absent2_num, "trial_num"=>$trial_num, "attendance_data"=>$family_data,
                               "grade"=>$grade);

        return $student_array;
}  // end of function get_family()

function get_student(&$db, $student_data, $trial_flag) {
	// 「休み」「面談」付きの生徒データを解析する
        global $member_list, $grade_list;
        $student_array = array();
        $absent_flag = 0;
        $interview_flag = 0;
        $alternative_flag = 0;
        $member_no = "";
        $cal_name = "";
        $tmp_cal_name = "";
        $tmp_db_name = "";
        $cancel = "";
        $alternate = "";
        $work = "";
        $words = explode(":", $student_data);//「(」「)」を外したもの
        $grade='';
        foreach ($words as $key => $word) {
                $word = trim($word);
                foreach($grade_list as $key1=>$str) { 
			if (strpos($word,$str)!==false) { $grade=$key1; }
		}
                if (mb_strpos($word, "休み2") !== FALSE || mb_strpos($word, "休み２") !== FALSE || strpos($word, "bsent2") !== FALSE) {
                        $absent_flag = "2";
                        $cancel = "a2";
        	}
   		else if (mb_strpos($word, "休み1") !== FALSE || mb_strpos($word, "休み１") !== FALSE || strpos($word, "bsent1") !== FALSE) {
	                // 講師都合の休み。授業料が発生しない
                        $absent_flag = "1";
                        $cancel = "a1";
        	}
       		         // 講師都合の休み
        	else if (mb_strpos($word, "休み3") !== FALSE || mb_strpos($word, "休み３") !== FALSE || strpos($word, "bsent3") !== FALSE) {
                	// 先生都合の休み。授業料が発生しない
                        $absent_flag = "3";
                        $cancel = "a3";
        	}
		else if (mb_strpos($word, "三者面談1") !== FALSE) {
                	// 入塾後、無料の三者面談
                        $interview_flag = "1";
                        $work = "i1";
                }
        	else if (mb_strpos($word, "三者面談2") !== FALSE) {
                	// 入塾後、有料の三者面談
                        $interview_flag = "2";
                        $work = "i2";
                }
        	else if (mb_strpos($word, "面談") !== FALSE) {
                	// 入塾前、無料の三者面談
                        $interview_flag = "3";
                        $work = "i3";
                }
        	else if (($key == 0) && (mb_strpos($word, "振替") !== FALSE || stripos($word, "alternative") !== FALSE || stripos($word, "make-up") !== FALSE || stripos($word, "make up") !== FALSE || stripos($word, "makeup") !== FALSE)) {
                        $alternative_flag = "1";
                        $alternate = "a";
                }
                else if (($key == 0 && $absent_flag == 0) || ($key == 1 && $absent_flag == 1) || ($key == 1 && $absent_flag == 2) ||
                                        ($key == 1 && $absent_flag == 3) || ($key == 1 && $alternative_flag == 1)) {
	                        	// 名前の部分。名前の前には「休み」しかない
                        if (preg_match("/(.*)様|(.*)/", $word, $matches, PREG_OFFSET_CAPTURE) == 1) {
       		                         // 1つ目の様のあとは、何が入力してあったとしても名前として扱わない。
                                if ($matches[1][0] != "") {
               		                 // 様がある場合
                                        $cal_name = $matches[1][0];
                                } else {
                       		         // 様がない場合
                                        $cal_name = $matches[2][0];
                                }
                                $tmp_cal_name = str_replace(array(" "), array(""), $cal_name);// 半角スペースも除去
                        }
                        if ( $trial_flag != "0" ) { $tmp_cal_name = "体験生徒"; }

                        foreach ($member_list as $no => $member) {
                    		$tmp_db_name = str_replace(array("　", " "), array(" ", ""), $member['name']);// 半角スペースも除去
                                if (preg_match("/^".preg_quote($tmp_db_name,"/")."$/", $tmp_cal_name, $name_matches, PREG_OFFSET_CAPTURE) == 1) {
                                        $member_no = $member["no"];
                                        break;
                                }
                        } // End:foreach ($member_list as $id => $member)
                }
        }       // End:foreach ($words as $key => $word)
        	// absent1_num、absent2_num、trial_numは、ファミリーの時のみ使用するため、「0」を入れておく
		// $student_arrayに”work"を追加　by T.Kobayashi
        $student_array = array("id"=>"", "no"=>$member_no, "kind"=>"student", "absent_flag"=>$absent_flag, "trial_flag"=>$trial_flag,"interview_flag"=>$interview_flag, "alternative_flag"=>$alternative_flag,"cal_name"=>$cal_name, "absent1_num"=>0, "absent2_num"=>0, "trial_num"=>0, "attendance_data"=>$student_data,"grade"=>$grade, "work"=>$work);

        return $student_array;

}       // Endoffunction

// １つのイベント（カレンダーに入力された予定）ごとに処理をする
// イベントデータが生徒の時は、生徒情報の配列を返す
// イベントデータが先生の時は、nullを返す

function get_event_param($db, $event, &$errArray, $target_teacher_id) {

// 	$event['calender_summary']を$event['summary']に変更。 by T.kobayashi

        global $kari_ignore;

        mb_regex_encoding("UTF-8");
        $param_array = array();
        $errMessage = "";

        global $member_list;
        global $place_list;
        global $course_list;
        // ■初期処理
        // 全角のスペース、かっこ、セミコロン、アスタリスクを半角に
        $event_summary = str_replace(array("　", "（", "）", "：", "︰", "＊"), array(" ", "(", ")", ":", ":", "*"), trim($event['summary']));
        $event_summary = mb_convert_kana($event_summary, "n", "utf-8");
        $event_description = str_replace("　", " ", trim($event['description']));
        $event_location = str_replace(array("　","１","２","３"), array(" ","1","2","3"), trim($event['location']));

        $monthly_fee_flag = 0;
        if (mb_strpos($event_summary, ":月謝") !== FALSE) { $monthly_fee_flag = 1; }
        $teacher_list = get_teacher_list($db);
        $teacher_id = 0;
        $tmp_event_summary = str_replace(array("　", "（", "）", "：", "︰", "＊"), array(" ", "(", ")", ":", ":", "*"), trim($event['summary']));
        $tmp_event_summary = str_replace(array(" "), array(""), $tmp_event_summary);// 半角スペースも除去
        foreach ($teacher_list as $teacher) {
                $name = str_replace(array("　", "（", "）", "：", "︰", "＊"), array(" ", "(", ")", ":", ":", "*"), trim($teacher["name"]));
                $name = str_replace(array(" "), array(""), $name);// 半角スペー>スも除去
                if (mb_strpos($tmp_event_summary, $name) !== FALSE) {
                	$teacher_id = $teacher["no"];
                        break;
                }
        }
        if ($target_teacher_id && $teacher_id != $target_teacher_id) { return false; }
        // ■日時と時間を取得
        $start_timestamp = null;
        $end_timestamp = null;
        $diff_hours = 0;
        $start_timestamp = $event['event_start_timestamp'];
        $end_timestamp = $event['event_end_timestamp'];
        if (is_null($start_timestamp) === false && is_null($end_timestamp) === false) {
                $diff_hours = ($end_timestamp - $start_timestamp) / (60*60);
        }
        	// ■生徒情報を取得
        $member_array = array();
        $course_id = "";
        $type_id = "";

	foreach ($course_list as $course) {
                if ($course["course_id"] == "1") {
                        continue;
                }
//		if (preg_match( "/^(\(仮\))?".$course["course_name"]."/", $tmp_event_summary, $matches, PREG_OFFSET_CAPTURE)===1) {
		if (mb_strpos( $tmp_event_summary,$course["course_name"])!==FALSE) {
			if ( (int)$course["course_id"] > 3 && (int)$course["course_id"] < 7 ) {
				// 夏期講習、冬季講習、春季講習の文字列がヒットしても何もしない
				break;
			}
                        $course_id = $course["course_id"];
                        $type_id = $course["type_id"];
                        break;  // for each
                }  else if (empty($course["course_name_english"]) === false) {
//                        if (preg_match( "/^(\(仮\))?".$course["course_name_english"]."|".ucwords($course["course_name_english"])."/", $event_summary, $matches, PREG_OFFSET_CAPTURE)===1) {
                        if (mb_strpos($tmp_event_summary,$course["course_name_english"])!==FALSE) {
                                $course_id = $course["course_id"];
                                $type_id = $course["type_id"];
                                break;
                        }
                }
        }  // end of for each.

       	if ($course_id == "") {
        	$course_id  = "1";
               	$type_id = "1";
        }
//var_dump($type_id);
	if ($type_id == "3") {
/**************************************
	family
**************************************/
                $work = "fm";
                // ファミリーのとき「()」は、一つ
                // １つの家族の複数の生徒で一人の生徒とする
		$match_num = preg_match( "/\((.*?)\)/", $tmp_event_summary, $matches, PREG_OFFSET_CAPTURE);
                if ($match_num === false || $match_num !== 1) {
                        $errMessage = "ファミリーの場合「()」で生徒氏名をくくっ>てください。<br>";
                        $errMessage .= $event['calender_summary']."カレンダー&nbsp;".date("Y/m/d H:i", $start_timestamp)."～".date("H:i", $end_timestamp)."<br>";
                        $errMessage .= $event['summary'];
                        $errArray[] = $errMessage;
                        return false;
                }
                $student_no = get_family($db, $matches[1][0]);
		$member_array[] = $student_no;

 	} else if ($type_id == "2") {
/**************************************
	group
**************************************/
                $work = "gl";
 	      		// グループとGroupの2種類がある
                $tmp_match_num = preg_match_all( "/\((.*?)\)/", $tmp_event_summary, $tmp_matches, PREG_PATTERN_ORDER);

		if ($tmp_match_num === false || $tmp_match_num < 1) {
                        $errMessage = "「()」で生徒氏名をくくってください。<br>";
                        $errMessage .= $event['calender_summary']."カレンダー&nbsp;".date("Y/m/d H:i", $start_timestamp)."～".date("H:i", $end_timestamp)."<br>";
                        $errMessage .= $event['summary'];
                        $errArray[] = $errMessage;
//var_dump($tmp_event_summary);
//var_dump($event['start']);
//var_dump($event['end']);
//var_dump($event['summary']);
//                        return false;
                }

                // 「(」「)」でくくられた一人ずつ処理をする
                for ($i=0; $i<$tmp_match_num; $i++) {
                        $trial_flag = "0";
                        if (preg_match("/[:：︰]\s*(無料体験|体験|\btrial\b)/i",$tmp_matches[1][$i] ) == 1) {
                                $trial_flag = "1";
                        }
                        $student = get_student($db, $tmp_matches[1][$i], $trial_flag);
                        $member_array[] = $student ;
                }

        } else {      //man2mman
/**************************************
	man2man
**************************************/
                $trial_flag = "0";
                if (preg_match("/[:：︰]\s*(無料体験|体験|\btrial\b)/i",$event_summary ) == 1) {
                        $trial_flag = "1";
                }
                $work = "m2m";
		$student = get_student($db, $tmp_event_summary, $trial_flag);
		$member_array[] = $student;
	}   	// end of man2man

	$updated_timestamp = $event['event_updated_timestamp'];
	$lesson_list = get_lesson_list($db);
       	$lesson_id = 0;
        if (mb_strpos($event['calender_summary'],"English") !== FALSE) {
       	        $lesson_id = "2";
       	} else {
       		foreach ($lesson_list as $id => $name) {
       	                if (mb_strpos($event['calender_summary'], $name) !== FALSE) {
      		         	$lesson_id = $id;
               		         break; // for each
                       	} // end of if
                } // end of foreach
        } // end of else
			// ■科目IDを取得（タイトルから「数学」などを取得）
       	$subject_id = "0";
        		// 英会話教室でない場合
       	if ($lesson_id != "2") {
       		$subject_list = get_subject_list($db);
               	foreach ($subject_list as &$word) {
               		if (mb_strpos($word, "・") !== FALSE) {
                       		$tmp_item_list = explode("・", $word);
                               	sort($tmp_item_list);
                               	$word = implode("・", $tmp_item_list);
                       	} // end of if
               	} // end of foreach
               	unset($word);
                		// テーブルのデ>フォルトはnull。もし0が入っていたらcal_descriptionで入力値を確認する。
               	$word_list = explode(":", $event_summary);
       		foreach ($word_list as $word) {
               		if (mb_strpos($word, "・") !== FALSE) {
					// 科目が複数指定されている場合
                       		$tmp_item_list = array();
                       		$item_list = explode("・", $word);
                       		foreach ($item_list as $item) {
                                        // 科目の後ろについているかもしれないメ>モを取り除くため、半角スペースと(の以外の文字列を取得する
                                       	$match_num = preg_match( "/[^ \(]+/", $item, $item_matches, PREG_OFFSET_CAPTURE);
                               		$tmp_item_list[] = $item_matches[0][0];
                               	}
                               	sort($tmp_item_list);
                               	$word = implode("・", $tmp_item_list);
			}
               		$match_num = preg_match( "/[^ \(]+/", $word, $word_matches, PREG_OFFSET_CAPTURE);

                       	$result = array_search($word_matches[0][0], $subject_list);
      			if ($result !== false) {
       				$subject_id = $result;
                       	}
                       	if ($subject_id != "0") {
			       		  // 科目が取得できたら、ループから出る
                               	break;
                       	}
               	} // end of for each.
       	} // end of $lesson_id !=2 .


       	if ($subject_id == "5") {
        		// 科目がそろばんだったら、教室を習い事にする
       		 $lesson_id = "4";
       	}
       	if ($lesson_id == "2" && $subject_id == "0") {
       		 $subject_id = "23";
       	}
 	if ($lesson_id == "3" && $subject_id == "0") {
       		 $subject_id = "18";
       	}
        		// 塾で「$subject_id = 0」ならば、未定とする（未定はsubject_idが8）
       	if ($lesson_id == "1" && $subject_id == "0") {
       		 $subject_id = "8";
       	}
        // ■生徒ごとにパラメータを配列に格納

        foreach ($member_array as $member) {
                // お休み1の場合は、無料体験でも面談でも普通の授業でも、ファミリーでも、時間を0にする
                // お休み1の時、時間をDBに「0時間」を登録したいので、ここで登録>する
                // $diff_hoursは、$member_array配列のすべての人がグループで共用>しているため、$diff_hoursを退避する
                $tmp_diff_hours = $diff_hours;
                if ($member["absent_flag"] == "1") {
                        $tmp_diff_hours = 0;
                }
                // お休み3の場合は、無料体験でも面談でも普通の授業でも、ファミリーでも、時間を0にする
                if ($member["absent_flag"] == "3") {
                        $tmp_diff_hours = 0;
                }
                // 振替の場合は、無料体験でも面談でも普通の授業でも、ファミリー>でも、時間を0にする
                if ($member["alternative_flag"] == "1") {
                        $tmp_diff_hours = 0;
                }
                if (strpos($event["event_summary"], "面談") !== FALSE) {
                        $member["interview_flag"] = "3";
                }
		// 場所の設定
       	$place_id = 0;
        foreach ($place_list as $place) {
       		foreach ($place["calendar_name_list"] as $calendar_name) {
       	        	if ($event["calender_summary"] == $calendar_name) {
       	        		 $place_id = $place["no"];
       	                   break;
       		         } else if (preg_match("/^(.+)?".$calendar_name."$/", $event["calender_summary"]) === 1) {
       		 		$place_id = $place["no"];
               	        	break;
               		} // else if 
       		} // foreach
       		if ($place_id != 0) {
       			break;
       		} // if
        } // for each $place
//var_dump($member);
// $param_array[40]に$workを追加 , $event[summary]に変更　by T.Kobayashi
                $param_array[] = array($event["event_id"], $member["no"],$member["id"], $member["cal_name"],$member["kind"],date("Y", $start_timestamp), date("n", $start_timestamp), date("j", $start_timestamp),$start_timestamp, date("H", $start_timestamp), date("i", $start_timestamp),$end_timestamp, date("H", $end_timestamp), date("i", $end_timestamp), $tmp_diff_hours, $lesson_id, $subject_id, $course_id, $teacher_id, $place_id, $member["absent_flag"], $member["trial_flag"], $member["interview_flag"],$member["alternative_flag"],$member["absent1_num"], $member["absent2_num"], $member["trial_num"], null, $event["calender_id"], $event["summary"], $event["event_summary"], $member["attendance_data"], $event["event_location"], $event["event_description"], $updated_timestamp, $year, $month, $event["recurringEvent"], $member["grade"], $monthly_fee_flag,$work); 

        } // end of foreach
        return $param_array;
}  // end of function get_event_param()

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

// 繰り返しルール(rrule)を解析
function analyze_rrule($rrule) {

	$dayofweek = ' ';
	$dayofmonth = ' ';
	$wkst = ' ';

		// FREQの取得
	if (mb_strpos($rrule,'MONTHLY') !== FALSE ){
		$kind = 'm';
	} else if (mb_strpos($rrule,'WEEKLY') !== FALSE ){
		$kind = 'w';
	} else {
		$kind = ' ';
	}
		// BYDAYの取得
	if (preg_match('/BYDAY=[A-Z0-9]+/u',$rrule,$matches,PREG_OFFSET_CAPTURE) == 1 ){
		$bydaylen = mb_strlen($matches[0][0]);
		$byday = mb_substr($matches[0][0],6,$bydaylen-6);
		if (mb_strlen($byday) === 2 ){
			$dayofweek = $byday;
		} else {
			$dayofmonth = $byday;
		}
	}
		// UNTILの取得
	if (preg_match('/UNTIL=[A-Z0-9]+/u',$rrule,$matches,PREG_OFFSET_CAPTURE) == 1 ){

		$untildate = mb_substr($matches[0][0],6,8);
	}
		// WKSTの取得
	if (preg_match('/WKST=[A-Z]+/u',$rrule,$matches,PREG_OFFSET_CAPTURE) == 1 ){
		$wkst = mb_substr($matches[0][0],5,2);
	}

	$rrule_array[] = array($kind,$dayofmonth,$dayofweek,$untildate,$wkst);
        return $rrule_array;
}

//function insert_event(&$db, $event_param_array) {
//                try{
//                	$sql = "INSERT INTO tbl_event (".
//                        " event_id, member_no, ".
//                        " member_id, member_cal_name, member_kind, ".
//                        " event_year, event_month, event_day, ".
//                        " event_start_timestamp, event_start_hour, event_start_minute, ".
//                        " event_end_timestamp, event_end_hour, event_end_minute, ".
//                        " event_diff_hours, ".
//                        " lesson_id, subject_id, course_id, teacher_id, place_id, absent_flag, trial_flag, interview_flag, alternative_flag,".
//                        " absent1_num, absent2_num, trial_num, repeat_flag, ".
//                        " cal_id, cal_summary, cal_evt_summary, cal_attendance_data, cal_evt_location, cal_evt_description, cal_evt_updated_timestamp, seikyu_year, seikyu_month,".
//                        " recurringEvent, grade, monthly_fee_flag, ".
//                        " insert_datetime ".
//                        " ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,  ?, ?, ?, ?, ?, ?, ?, ?,  ?, ?, ?, ?, ?, now())";
//                        $stmt = $db->prepare($sql);
//                        $stmt->execute($event_param_array);
//                        $event_no = $db->lastInsertId();
//                }catch (PDOException $e){
//                        print_r('insert_event:failed: ' . $e->getMessage());
//                                return false;
//                }
//                return $event_no;
//} // End:event_insert($db, $event)
?>

