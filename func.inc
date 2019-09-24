<?php
$result = date_default_timezone_set('Asia/Tokyo');
session_start();
$db = null;
connect_DB($db);
		// added by T.Kobayashi.
$dbh = null;
connect_DBH($dbh);
		// End of added by T.Kobayashi.

mb_regex_encoding("UTF-8");

define('TEACHER', 1);
define('STAFF',   2);

$work_type_list = array (
	"",				// default work
	"面談",
	"試験監督",
	"演習"
);
$work_type_default_wage = array ( 0, 0, 0, 1200);


define('WORK_TYPE_MENDANN',1);
define('WORK_TYPE_SHIKENN_KANTOKU',2);

define('NORMAL_CLASS',1);
define('SEASON_CLASS',2);
define('SATSUN_CLASS',3);

$lesson_list = get_lesson_list($db);
$subject_list = get_subject_list($db);
$type_list = get_type_list($db);
$grade_list = array(0=> "未登録", 1=>"幼児", 2=>"小1", 3=>"小2", 4=>"小3", 5=>"小4", 6=>"小5", 7=>"小6",
										8=>"中1", 9=>"中2", 10=>"中3", 11=>"高1", 12=>"高2", 13=>"高3", 14=>"成人");
$others_kind_list = array("1"=>"授業代", "2"=>"その他", "3"=>"消費税");
$charge_list = array("1"=>"あり", "2"=>"なし");
$kind_list = array(
								array("no"=>"1", "name"=>"授業", "type"=>"0"),
								array("no"=>"2", "name"=>"テキスト", "type"=>"1"),
								array("no"=>"3", "name"=>"模試", "type"=>"1"),
								array("no"=>"4", "name"=>"月会費", "type"=>"0"),
//								array("no"=>"5", "name"=>"入会金", "type"=>"0")
								array("no"=>"6", "name"=>"督促金", "type"=>"0"),
//								array("no"=>"7", "name"=>"夏期講習", "type"=>"0"),
								array("no"=>"8", "name"=>"支払い調整", "type"=>"0"),
						);
/*
$kind_list = array(
								array("no"=>"1", "name"=>"授業", "type"=>"0"),
								array("no"=>"2", "name"=>"テキスト", "type"=>"1"),
								array("no"=>"3", "name"=>"模試", "type"=>"1"),
								array("no"=>"4", "name"=>"月会費", "type"=>"0"),
								array("no"=>"5", "name"=>"入会金", "type"=>"0")
						);
*/

//$place_list[1] = array("no"=>"1","name"=>"本校","calendar_name_list"=>array("English_home","塾_本校"));
$place_list[2] = array("no"=>"2","name"=>"南口校","calendar_name_list"=>array("English_south","習い事_南口校","塾_南口校"));
$place_list[3] = array("no"=>"3","name"=>"北口校３Ｆ","calendar_name_list"=>array("English_north_3F","塾_北口校_3F","習い事_北口校_3F"));
$place_list[4] = array("no"=>"4","name"=>"北口校４Ｆ","calendar_name_list"=>array("English_north_4F","塾_北口校_4F"));
$place_list[5] = array("no"=>"5","name"=>"北口校","calendar_name_list"=>array("English_north","塾_北口校"));
$place_list[6] = array("no"=>"6","name"=>"アローレ校","calendar_name_list"=>array("塾_アローレ校"));
$place_list[7] = array("no"=>"7","name"=>"ダットッチ校","calendar_name_list"=>array("English_dattochi","習い事_dattochi"));
$place_list[8] = array("no"=>"8","name"=>"豊田校","calendar_name_list"=>array("English_toyoda","塾_豊田校"));
$place_list[9] = array("no"=>"9","name"=>"子安校","calendar_name_list"=>array("ピアノ","習い事_子安校","塾_子安校"));
$place_list[10] = array("no"=>"10","name"=>"国立校","calendar_name_list"=>array("塾_国立校","English_Kunitachi"));

$floor_list[1] = array("no"=>"1","name"=>"日野市豊田校","calendar_name"=>"English_toyoda");
$floor_list[2] = array("no"=>"1","name"=>"日野市豊田校","calendar_name"=>"塾_豊田校");
$floor_list[3] = array("no"=>"2","name"=>"北口校２Ｆ","calendar_name"=>"事務_北口校");
$floor_list[4] = array("no"=>"2","name"=>"北口校２Ｆ","calendar_name"=>"事務_本校");
$floor_list[5] = array("no"=>"3","name"=>"北口校３Ｆ","calendar_name"=>"English_north_3F");
$floor_list[6] = array("no"=>"3","name"=>"北口校３Ｆ","calendar_name"=>"塾_北口校_3F");
$floor_list[7] = array("no"=>"3","name"=>"北口校３Ｆ","calendar_name"=>"習い事_北口校_3F");
$floor_list[8] = array("no"=>"4","name"=>"北口校４Ｆ","calendar_name"=>"English_north_4F");
$floor_list[9] = array("no"=>"4","name"=>"北口校４Ｆ","calendar_name"=>"塾_北口校_4F");
$floor_list[10] = array("no"=>"5","name"=>"南口校","calendar_name"=>"English_south");
$floor_list[11] = array("no"=>"5","name"=>"南口校","calendar_name"=>"習い事_南口校");
$floor_list[12] = array("no"=>"5","name"=>"南口校","calendar_name"=>"塾_南口校");
$floor_list[13] = array("no"=>"5","name"=>"南口校","calendar_name"=>"事務_南口校");
$floor_list[14] = array("no"=>"6","name"=>"国立校","calendar_name"=>"塾_国立校");
$floor_list[15] = array("no"=>"6","name"=>"国立校","calendar_name"=>"English_Kunitachi");
$floor_list[16] = array("no"=>"7","name"=>"子安校","calendar_name"=>"ピアノ");
$floor_list[17] = array("no"=>"7","name"=>"子安校","calendar_name"=>"習い事_子安校");
$floor_list[18] = array("no"=>"7","name"=>"子安校","calendar_name"=>"塾_子安校");
$floor_list[19] = array("no"=>"7","name"=>"子安校","calendar_name"=>"English_koyasu");
$floor_list[20] = array("no"=>"8","name"=>"ダットッチ校","calendar_name"=>"English_dattochi");
$floor_list[21] = array("no"=>"8","name"=>"ダットッチ校","calendar_name"=>"習い事_dattochi");
$floor_list[22] = array("no"=>"9","name"=>"アローレ校","calendar_name"=>"塾_アローレ校");

$season_class_title = '2019年夏期講習';
$season_course_id = 4; // 夏期講習
//$season_course_id = 5; // 冬期講習
//$season_course_id = 6; // 春期講習

/* Old season class course
$season_course_list = array(
	"1"=>array("course_id"=>"1","course_name"=>"スタンダード"),
	"2"=>array("course_id"=>"2","course_name"=>"スタンダードプラス"),
	"3"=>array("course_id"=>"3","course_name"=>"マンツーマン")
	);
*/
$season_course_list = array(
	"1"=>array("course_id"=>"1","course_name"=>"個別60分コース"),
	"2"=>array("course_id"=>"2","course_name"=>"個別90分コース"),
	"3"=>array("course_id"=>"3","course_name"=>"個別120分コース")
	);
	
$sat_sun_class_date_list = array(

"2018/09/01",
"2018/09/02",
"2018/09/08",
"2018/09/09",
"2018/09/15",
"2018/09/16",
"2018/09/17",
"2018/09/22",
"2018/09/23",
"2018/09/24",
"2018/09/29",
"2018/09/30",
"2018/10/06",
"2018/10/07",
"2018/10/08",
"2018/10/13",
"2018/10/14",
"2018/10/20",
"2018/10/21",
"2018/10/27",
"2018/10/28",

"2018/11/03",
"2018/11/04",
"2018/11/10",
"2018/11/11",
"2018/11/17",
"2018/11/18",
"2018/11/23",
"2018/11/24",
"2018/11/25",
"2018/12/01",
"2018/12/02",
"2018/12/08",
"2018/12/09",
"2018/12/15",
"2018/12/16",

"2019/01/12",
"2019/01/13",
"2019/01/14",
"2019/01/19",
"2019/01/20",
"2019/01/26",
"2019/01/27",
"2019/02/02",
"2019/02/03",
"2019/02/09",
"2019/02/10",
"2019/02/11",
"2019/02/16",
"2019/02/17",
"2019/02/23",
"2019/02/24",

"2019/03/02",
"2019/03/03",
"2019/03/09",
"2019/03/10",
"2019/03/16",
"2019/03/17",
"2019/04/13",
"2019/04/14",
"2019/04/20",
"2019/04/21",
"2019/04/27",
"2019/04/28",
"2019/04/29",

"2019/05/01",
"2019/05/02",
"2019/05/03",
"2019/05/04",
"2019/05/05",
"2019/05/06",
"2019/05/11",
"2019/05/12",
"2019/05/18",
"2019/05/19",
"2019/05/25",
"2019/05/26",
"2019/06/01",
"2019/06/02",
"2019/06/08",
"2019/06/09",
"2019/06/15",
"2019/06/16",
"2019/06/22",
"2019/06/23",
"2019/06/29",
"2019/06/30",

"2019/07/06",
"2019/07/07",
"2019/07/13",
"2019/07/14",
"2019/07/15",

"2019/09/01",
"2019/09/07",
"2019/09/08",
"2019/09/14",
"2019/09/15",
"2019/09/16",
"2019/09/21",
"2019/09/22",
"2019/09/23",
"2019/09/28",
"2019/09/29",
"2019/10/05",
"2019/10/06",
"2019/10/12",
"2019/10/13",
"2019/10/14",
"2019/10/19",
"2019/10/20",
"2019/10/22",
"2019/10/26",
"2019/10/27",

"2019/11/02",
"2019/11/03",
"2019/11/04",
"2019/11/09",
"2019/11/10",
"2019/11/16",
"2019/11/17",
"2019/11/23",
"2019/11/24",
"2019/11/30",
"2019/12/01",
"2019/12/07",
"2019/12/08",
"2019/12/14",
"2019/12/15"

);
foreach ($sat_sun_class_date_list as $i=>$item) { if ($i==0) { $sat_sun_class_date_list_string = "('$item'"; } else { $sat_sun_class_date_list_string = $sat_sun_class_date_list_string.",'$item'"; } }
$sat_sun_class_date_list_string = $sat_sun_class_date_list_string.")";

$date_list = array(
"2019/07/20",
"2019/07/21",
"2019/07/22",
"2019/07/23",
"2019/07/24",
"2019/07/25",
"2019/07/26",
"2019/07/27",
"2019/07/28",
"2019/07/29",
"2019/07/30",
"2019/07/31",
"2019/08/01",
"2019/08/02",
"2019/08/03",
"2019/08/04",
"2019/08/05",
"2019/08/06",
"2019/08/07",
"2019/08/08",
"2019/08/09",
"2019/08/10",
"2019/08/11",
"2019/08/12",
"2019/08/13",
"2019/08/14",
"2019/08/15",
"2019/08/16",
"2019/08/17",
"2019/08/18",
"2019/08/19",
"2019/08/20",
"2019/08/21",
"2019/08/22",
"2019/08/23",
"2019/08/24",
"2019/08/25",
"2019/08/26",
"2019/08/27",
"2019/08/28",
"2019/08/29",
"2019/08/30",
"2019/08/31"
	);

$date_list_array = array(
"2019/7" => $date_list,
"2019/3" =>  array(
"2019/03/18",
"2019/03/19",
"2019/03/20",
"2019/03/21",
"2019/03/22",
"2019/03/23",
"2019/03/24",
"2019/03/25",
"2019/03/26",
"2019/03/27",
"2019/03/28",
"2019/03/29",
"2019/03/30",
"2019/03/31",
"2019/04/01",
"2019/04/02",
"2019/04/03",
"2019/04/04",
"2019/04/05",
"2019/04/06",
"2019/04/07"
),
"2018/12" => array(
"2018/12/22",
"2018/12/23",
"2018/12/24",
"2018/12/25",
"2018/12/26",
"2018/12/27",
"2018/12/28",
"2018/12/29",
"2018/12/30",
"2019/01/03",
"2019/01/04",
"2019/01/05",
"2019/01/06",
"2019/01/07",
"2019/01/08",
"2019/01/09",
"2019/01/10",
"2019/01/11"
),
"2018/7" => array(
	"2018/07/23",
	"2018/07/24",
	"2018/07/25",
	"2018/07/26",
	"2018/07/27",

	"2018/07/29",
	"2018/07/30",
	"2018/07/31",
	"2018/08/01",
	"2018/08/02",
	"2018/08/03",

	"2018/08/05",
	"2018/08/06",
	"2018/08/07",
	"2018/08/08",
	"2018/08/09",
	"2018/08/10",

	"2018/08/12",
	"2018/08/13",
	"2018/08/14",
	"2018/08/15",
	"2018/08/16",
	"2018/08/17",

	"2018/08/19",
	"2018/08/20",
	"2018/08/21",
	"2018/08/22",
	"2018/08/23",
	"2018/08/24",

	"2018/08/26",
	"2018/08/27",
	"2018/08/28",
	"2018/08/29",
	"2018/08/30",
	"2018/08/31"
)
);

foreach ($date_list as $i=>$item) { if ($i==0) { $date_list_string = "('$item'"; } else { $date_list_string = $date_list_string.",'$item'"; } }
$date_list_string = $date_list_string.")";
$time_list = array(
	"09:00",
	"09:30",
	"10:00",
	"10:30",
	"11:00",
	"11:30",
	"12:00",
	"12:30",
	"13:00",
	"13:30",
	"14:00",
	"14:30",
	"15:00",
	"15:30",
	"16:00",
	"16:30",
	"17:00",
	"17:30",
	"18:00",
	"18:30",
	"19:00",
	"19:30",
	"20:00"
	);
$default_stime=4;
$default_etime=14;


$jyuku_wage_type_list = array(
"小学生（１人）",
"小学生（２人）",
"中学生（１人）",
"中学生受験生（１人）",
"中学生（２人）",
"中学生（３人）",
"中学生（４人）",
"中学生（５人以上）",
"高校生（１人）",
"高校生（２人）",
"高校生受験生（１人）",
"小学生（３人）",
"小学生（４人）",
"小学生（５人以上）",
"小学生受験生（１人）"
);

$eng_wage_type_list = array(
"Private",
"Group: 2",
"Group: 5 over",
"英検・TOEIC・TOEFL",
"Group: 3",
"Group: 4",
);

$piano_wage_type_list = array(
"-"
);

$naraigoto_wage_type_list = array(
"-"
);

$crew_list = array(
"",
"講師勤務時間1",
"講師勤務時間2",
"講師勤務時間3",
"講師勤務時間4",
"講師勤務時間5",
"講師勤務時間6",
"講師勤務時間7",
"講師勤務時間8",
"講師勤務時間9",
"講師勤務時間10"
);

$jyuku_default_wage = array( 1300, 1600, 1300, 1300, 1600, 1700, 1800, 1800, 1500, 1800, 1500, 0,0,0);
$jyuku_default_crew = array(    1,    3,    1,    1,    3,    4,    5,    5,    2,    5,    2, 0,0,0);

$attendStatusList = array( "出席", "休み１", "休み２", "振替", "休み１休講", "休み２当日", "休み２規定回数以上" );
$attendStatusList_eng = array( "Attend", "Absent1", "Absent2", "make-up", "Absent1 No class", "Absent2 Today", "Absent2 over limit" );

$weekday_array = array("0" => "日", "1" => "月", "2" => "火", "3" => "水", "4" => "木", "5" => "金", "6" => "土");
$weekday_array_eng = array("0" => "Sun", "1" => "Mon", "2" => "Tue", "3" => "Wen", "4" => "Thu", "5" => "Fri", "6" => "Sat");

define('LESSON60', 1);
define('LESSON90', 2);
define('LESSON120', 3);

// 授業日数設定
if ($season_course_id == 4) {
	define('LESSON_DATE_COUNT_1',15);   //夏期講習
	define('LESSON_DATE_COUNT_2',20);
} else {
	define('LESSON_DATE_COUNT_1',10);   //春期・冬期講習
	define('LESSON_DATE_COUNT_2',15);
}

// １時間あたりのマンツーマン受講料
$lesson_fee_table[0] = array(
	0  => array(			//"未登録"
				),
	1  => array(			//"幼児"
				),
	2  => array(			//"小１"
					LESSON60  => 4500,
					LESSON90  => 4250,
					LESSON120 => 4000
				),
	3  => array(			//"小２"
					LESSON60  => 4500,
					LESSON90  => 4250,
					LESSON120 => 4000
				),
	4  => array(			//"小３"
					LESSON60  => 4500,
					LESSON90  => 4250,
					LESSON120 => 4000
				),
	5  => array(			//"小４"
					LESSON60  => 4500,
					LESSON90  => 4250,
					LESSON120 => 4000
				),
	6  => array(			//"小５"
					LESSON60  => 4500,
					LESSON90  => 4250,
					LESSON120 => 4000
				),
	7  => array(			//"小６"
					LESSON60  => 4500,
					LESSON90  => 4250,
					LESSON120 => 4000
				),
	8  => array(			//"中１"
					LESSON60  => 4500,
					LESSON90  => 4250,
					LESSON120 => 4000
				),
	9  => array(			//"中２"
					LESSON60  => 4500,
					LESSON90  => 4250,
					LESSON120 => 4000
				),
	10 => array(			//"中３"
					LESSON60  => 5000,
					LESSON90  => 4750,
					LESSON120 => 4500
				),
	11 => array(			//"高１"
					LESSON60  => 5000,
					LESSON90  => 4750,
					LESSON120 => 4500
				),
	12 => array(			//"高２"
					LESSON60  => 5000,
					LESSON90  => 4750,
					LESSON120 => 4500
				),
	13 => array(			//"高３"
					LESSON60  => 5500,
					LESSON90  => 5250,
					LESSON120 => 5000
				),
	14 => array(			//"成人"
				),
);

// 1日当たりの演習解説受講料
$exercise_fee_table[0] = array(
	LESSON60  => array( 1000, 800, 600, 600 ),
	LESSON90  => array(  800, 550, 450, 450 ),
	LESSON120 => array(  600, 450, 400, 400 )
);

// 20170830 2017夏期講習の特別対応
$sql = "SELECT member_no FROM tbl_divided_payment WHERE year=2017 and month=8 and type_id=4";
$stmt = $db->prepare($sql);
$stmt->execute();
$except20170830 = $stmt->fetch(PDO::FETCH_NUM);
if ($except20170830 === false) { $except20170830 = array(); }

// データベース接続
function connect_DB(&$db) {
	try{
		$db = new PDO(PDO_DSN, DB_USER, DB_PASSWD);
		$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	}catch (PDOException $e){
		//$err_msg = 'Connection failed: ' . $e->getMessage();
		//exit($err_msg);
		exit('Connection failed');
	}
}
// データベース接続2
function connect_DBH(&$dbh) {
	try{
		$dbh = new PDO(PDO_DSN2, DB_USER, DB_PASSWD2);
		$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	}catch (PDOException $e){
		//$err_msg = 'Connection failed: ' . $e->getMessage();
		//exit($err_msg);
		exit('Connection failed');
	}
}

// 全角スペースと半角スペースを除く
function trim_all_space($str) {
	$str = preg_replace('/　/', '', $str);//全角スペースを除く
	$str = preg_replace('/\s+/', '', $str);//連続する半角スペースを除く
	return $str;
}


// 年、月、日の取得
// 返り値:array(年、月、日)
function get_date_array($date="") {
	if ($date == "") {
  	$date = date("Y-m-d");
	}
	$date_array = sscanf($date, "%d-%d-%d");
	return $date_array;
}

// 1日分の予定を取得
function get_schedule(&$db, $member_id, $year, $month, $day) {
	$sql = "SELECT * FROM tbl_schedule
					 WHERE member_id = ? AND schedule_year = ? AND schedule_month = ? AND schedule_day = ?";
	$stmt = $db->prepare($sql);
	$stmt->execute(array($member_id, $year, $month, $day));
	$schedule_list = $stmt->fetchAll(PDO::FETCH_BOTH);
	return $schedule_list;
}


// 教室一覧を取得
function get_lesson_list(&$db) {
	$sql = "SELECT * FROM tbl_lesson";
	$stmt = $db->prepare($sql);
	$stmt->execute();
	$lesson_array = $stmt->fetchAll(PDO::FETCH_BOTH);
	$lesson_list = array();
	foreach ( $lesson_array as $row ) {
		$lesson_list[$row["lesson_id"]] = $row["lesson_name"];
	}
	return $lesson_list;
}

// 科目名の一覧を取得
function get_subject_list(&$db) {
	$sql = "SELECT * FROM tbl_subject";
	$stmt = $db->prepare($sql);
	$stmt->execute();
	$subject_array = $stmt->fetchAll(PDO::FETCH_BOTH);
	$subject_list = array();
	foreach ( $subject_array as $row ) {
		$subject_list[$row["subject_id"]] = mb_convert_kana($row["subject_name"], "n", "utf-8");
	}
	return $subject_list;
}

// 20160124に変更
// コース名の一覧を取得
function get_course_list(&$db) {
	$sql = "SELECT * FROM tbl_course where del_flag = '0'";
	$stmt = $db->prepare($sql);
	$stmt->execute();
	$course_array = $stmt->fetchAll(PDO::FETCH_BOTH);
	$course_list = array();
	foreach ( $course_array as $row ) {
		$course_list[$row["course_id"]] = $row;
	}
	return $course_list;
}

/*
// 2015/06/12 複数科目対応
// メンバーのすべての科目の一覧を取得
function get_member_subject_list(&$db, $member_no) {
	$subject_list = array();
	$member_array = get_member($db, array("tbl_member.no = ?"), array($member_no));
	foreach ($member_array["fee_list"] as $fee) {
		$subject_array = array();
		$subject_array["subject_id"] = $fee["subject_id"];
		$subject_array["subject_name"] = $fee["subject_name"];
		$subject_array["plural_flag"] = $fee["plural_flag"];
		$subject_list[$subject_array["subject_id"]] = $subject_array;
		if ($fee["plural_flag"] == 1) {
 			$plural_subjects_list = get_child_subjects_list($db, array("tbl_plural_subjects.plural_subjects_id = ?"), array($subject_array["subject_id"]));
			foreach ($plural_subjects_list as $plural_subjects) {
				$subject_array = array();
				$subject_array["subject_id"] = $plural_subjects["subject_id"];
				$subject_array["subject_name"] = $plural_subjects["subject_name"];
				$subject_array["plural_flag"] = $plural_subjects["plural_flag"];
				$subject_list[$subject_array["subject_id"]] = $subject_array;
			}
		}
	}
	return $subject_list;
}
*/

/*
// 2015/06/12 複数科目対応
// 複数科目の子リストを取得
function get_child_subjects_list($db, $param_array=array(), $value_array=array()) {
	$subjects_list = array();
	$cmd = 
		"SELECT ".
		" tbl_subject.subject_id as subject_id, ".
		" tbl_subject.subject_name as subject_name, ".
		" tbl_subject.plural_flag as plural_flag ".
		" FROM tbl_plural_subjects, tbl_subject".
		" WHERE tbl_plural_subjects.subject_id = tbl_subject.subject_id";
	if(count($param_array) > 0){
    $cmd .= " AND " . join(" AND ",$param_array);
  }
	$stmt = $db->prepare($cmd);
	$stmt->execute($value_array);
	$subjects_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
	return $subjects_list;
}
*/
/*
// 複数科目の親リストを取得
function get_parent_subject_list($db, $param_array=array(), $value_array=array()) {
	$subjects_list = array();
	$cmd = 
		"SELECT ".
		" tbl_plural_subjects.plural_subjects_id as plural_subjects_id, ".
		" tbl_subject.subject_name as subject_name, ".
		" tbl_subject.plural_flag as plural_flag, ".
		" tbl_plural_subjects.subject_id as subject_id ".
		" FROM tbl_plural_subjects, tbl_subject ".
		" WHERE tbl_plural_subjects.plural_subjects_id = tbl_subject.subject_id";
	if(count($param_array) > 0){
    $cmd .= " AND " . join(" AND ",$param_array);
  }
	$stmt = $db->prepare($cmd);
	$stmt->execute($value_array);
	$subjects_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
	return $subjects_list;
}
*/

// 受講タイプ一覧を取得
function get_type_list(&$db) {
	$sql = "SELECT * FROM tbl_type";
	$stmt = $db->prepare($sql);
	$stmt->execute();
	$type_array = $stmt->fetchAll(PDO::FETCH_BOTH);
	$type_list = array();
	foreach ( $type_array as $row ) {
		$type_list[$row["type_id"]] = $row["type_name"];
	}
	return $type_list;
}

// メンバーを取得
// 取得できなかった場合は、nullを返す
function get_member($db, $param_array=array(), $value_array=array()) {
	// 頻繁に列追加があるので*で取得する
	$cmd = 
		"SELECT * FROM tbl_member";
	if(count($param_array) > 0){
    $cmd .= " WHERE " . join(" AND ",$param_array);
  }
	$stmt = $db->prepare($cmd);
	$stmt->execute($value_array);
	$member_array = $stmt->fetchAll(PDO::FETCH_ASSOC);
	$member_list = array();
	foreach ( $member_array as $row ) {
		$param_array = array("tbl_fee.member_no = ?");
		$value_array = array($row["no"]);
		$row["fee_list"] = get_fee_list($db, $param_array, $value_array);
		$row["m_fee_list"] = get_m_fee_list($db, array("tbl_monthly_fee.member_no = ?"), array($row["no"]));
		$member_list[] = $row;
	}
	if (count($member_list) > 0) {
		return $member_list[0];
	} else {
		return null;
	}
}

// メンバー一覧を取得
// 改ページ対応なし
// $param_array : kind = ?
// $value_array : 3
// $order_array : name
function get_member_list($db, $param_array=array(), $value_array=array(), $order_array=array(), $all_student_flag="0") {
	$cmd = 
		"SELECT * FROM tbl_member";
		 //FROM tbl_member, tbl_member_lesson, tbl_lesson";
 	//$tmp_where_array = array();
  //foreach($where_array as $key => $item){
  // 	array_push($tmp_where_array,"$key = '$item'");
  //}
	//$cmd .= " where tbl_member.id = tbl_member_lesson.member_id
	//					and tbl_member_lesson.lesson_id = tbl_lesson.id";
	// 20160119 del_flag条件を変更
	if ($all_student_flag == "1") {
	// 削除でない場合（現生徒と前生徒を抽出）
	  $cmd .= " where (tbl_member.del_flag = '0' or tbl_member.del_flag = '2')";	
	  if(count($param_array) > 0){
	    $cmd .= " and " . join(" and ",$param_array);
	  }
	} else if ($all_student_flag == "2") {
	// 削除も含むすべての場合（前生徒と現生徒と削除済み生徒のすべてを抽出）
	  if(count($param_array) > 0){
	    $cmd .= " where " . join(" and ",$param_array);
	  }
	} else if ($all_student_flag == "3") {
	// 前生徒のみの場合
	  $cmd .= " where tbl_member.del_flag = '2'";	
	  if(count($param_array) > 0){
	    $cmd .= " and " . join(" and ",$param_array);
	  }
	} else {
	// 現生徒のみの場合
	  $cmd .= " where tbl_member.del_flag = '0'";	
	  if(count($param_array) > 0){
	    $cmd .= " and " . join(" and ",$param_array);
	  }
	}
  if(count($order_array) > 0){
    $cmd .= "	order by " . join(" , ",$order_array);
  }
	else {
		$cmd .= "	order by tbl_member.furigana";
	}
	$stmt = $db->prepare($cmd);
	$stmt->execute($value_array);
	$member_array = $stmt->fetchAll(PDO::FETCH_ASSOC);
	$member_list = array();
	foreach ( $member_array as $row ) {
		if ($row["name"] == "体験生徒") { continue; }
		$param_array = array("tbl_fee.member_no = ?");
		$value_array = array($row["no"]);
		$row["fee_list"] = get_fee_list($db, $param_array, $value_array);
		$row["m_fee_list"] = get_m_fee_list($db, array("tbl_monthly_fee.member_no = ?"), array($row["no"]));
		$member_list[$row["no"]] = $row;
	}
	return $member_list;
}


// 20151007追加
// 簡易メンバー一覧を取得（授業料なし）
// 改ページ対応なし
// $param_array : kind = ?
// $value_array : 3
// $order_array : name
function get_simple_member_list($db, $param_array=array(), $value_array=array(), $order_array=array(), $all_student_flag="0") {
	$cmd = 
		"SELECT * FROM tbl_member";
	// 20151015 del_flag条件を追加
  //$cmd .= " where tbl_member.del_flag = '0'";
  //if(count($param_array) > 0){
  //  $cmd .= " and " . join(" and ",$param_array);
  //}
	// 20160119 del_flag条件を変更
	if ($all_student_flag == "1") {
	// 削除でない場合（現生徒と前生徒を抽出）
	  $cmd .= " where (tbl_member.del_flag = '0' or tbl_member.del_flag = '2')";	
	  if(count($param_array) > 0){
	    $cmd .= " and " . join(" and ",$param_array);
	  }
	} else if ($all_student_flag == "2") {
	// 削除も含むすべての場合（前生徒と現生徒と削除済み生徒のすべてを抽出）
	  if(count($param_array) > 0){
	    $cmd .= " where " . join(" and ",$param_array);
	  }
	} else {
	// 現生徒のみの場合
	  $cmd .= " where tbl_member.del_flag = '0'";	
	  if(count($param_array) > 0){
	    $cmd .= " and " . join(" and ",$param_array);
	  }
	}
  if(count($order_array) > 0){
    $cmd .= "	order by " . join(" , ",$order_array);
  }
	else {
		$cmd .= "	order by tbl_member.furigana";
	}
	$stmt = $db->prepare($cmd);
	$stmt->execute($value_array);
	$member_array = $stmt->fetchAll(PDO::FETCH_ASSOC);
	$member_list = array();
	foreach ( $member_array as $row ) {
		$member_list[$row["no"]] = $row;
	}
	return $member_list;
}

// メンバー数を取得
function get_member_cnt($db, $param_array=array(), $value_array=array(), $all_student_flag="0") {
	$cmd = 
		"SELECT
				count(*) as count
		 FROM tbl_member";
	// 20160119 del_flag条件を変更
	if ($all_student_flag == "1") {
	// 削除でない場合（現生徒と前生徒を抽出）
	  $cmd .= " where (tbl_member.del_flag = '0' or tbl_member.del_flag = '2')";	
	  if(count($param_array) > 0){
	    $cmd .= " and " . join(" and ",$param_array);
	  }
	} else if ($all_student_flag == "2") {
	// 削除も含むすべての場合（前生徒と現生徒と削除済み生徒のすべてを抽出）
	  if(count($param_array) > 0){
	    $cmd .= " where " . join(" and ",$param_array);
	  }
	} else {
	// 現生徒のみの場合
	  $cmd .= " where tbl_member.del_flag = '0'";	
	  if(count($param_array) > 0){
	    $cmd .= " and " . join(" and ",$param_array);
	  }
	}
	$stmt = $db->prepare($cmd);
	$stmt->execute($value_array);
	$member_array = $stmt->fetchAll(PDO::FETCH_ASSOC);
	$member_cnt = $member_array[0]["count"];
	return $member_cnt;
}


// 料金一覧を取得
function get_fee_list($db, $param_array=array(), $value_array=array()) {
/*
	$cmd = 
		"SELECT
				tbl_fee.fee_no as fee_no,
				tbl_lesson.lesson_id as lesson_id,
				tbl_lesson.lesson_name as lesson_name,
				tbl_subject.subject_id as subject_id,
				tbl_subject.subject_name as subject_name,
				tbl_subject.plural_flag as plural_flag,
				tbl_type.type_id as type_id,
				tbl_type.type_name as type_name,
				tbl_fee.fee as fee,
				tbl_fee.family_minus_price as family_minus_price
		 FROM tbl_fee, tbl_lesson, tbl_subject, tbl_type";
*/
	$cmd = 
		"SELECT
				tbl_fee.fee_no as fee_no,
				tbl_lesson.lesson_id as lesson_id,
				tbl_lesson.lesson_name as lesson_name,
				tbl_subject.subject_id as subject_id,
				tbl_subject.subject_name as subject_name,
				tbl_course.course_id as course_id,
				tbl_course.course_name as course_name,
				tbl_teacher.no as teacher_id,
				tbl_teacher.name as teacher_name,
				tbl_fee.fee as fee,
				tbl_fee.family_minus_price as family_minus_price,
				tbl_fee.additional_fee as additional_fee,
				tbl_fee.temp_flag as temp_flag
		 FROM tbl_fee, tbl_lesson, tbl_subject, tbl_course, tbl_teacher";
  if(count($param_array) > 0){
    $cmd .= " WHERE " . join(" AND ",$param_array) . "AND";
  } else {
		$cmd .= " WHERE ";
	}
	$cmd .= " tbl_fee.lesson_id = tbl_lesson.lesson_id
		 AND tbl_fee.subject_id = tbl_subject.subject_id
		 AND tbl_fee.course_id = tbl_course.course_id
		 AND tbl_fee.teacher_id = tbl_teacher.no
		 ORDER BY tbl_lesson.lesson_id, tbl_course.course_id, tbl_subject.subject_id, tbl_teacher.furigana";
/*
	// 先生の入力なしの場合
	$cmd = 
		"SELECT
				tbl_fee.fee_no as fee_no,
				tbl_lesson.lesson_id as lesson_id,
				tbl_lesson.lesson_name as lesson_name,
				tbl_subject.subject_id as subject_id,
				tbl_subject.subject_name as subject_name,
				tbl_course.course_id as course_id,
				tbl_course.course_name as course_name,
				tbl_fee.fee as fee,
				tbl_fee.family_minus_price as family_minus_price
		 FROM tbl_fee, tbl_lesson, tbl_subject, tbl_course";
  if(count($param_array) > 0){
    $cmd .= " WHERE " . join(" AND ",$param_array) . "AND";
  } else {
		$cmd .= " WHERE ";
	}
	$cmd .= " tbl_fee.lesson_id = tbl_lesson.lesson_id
		 AND tbl_fee.subject_id = tbl_subject.subject_id
		 AND tbl_fee.course_id = tbl_course.course_id
		 ORDER BY tbl_lesson.lesson_id, tbl_course.course_id, tbl_subject.subject_id";
*/
// 20150826 夏期講習を並べるため、type_idとsubject_idの順を入れ替える
//ORDER BY tbl_lesson.lesson_id, tbl_subject.subject_id, tbl_type.type_id";
//echo $cmd;
	$stmt = $db->prepare($cmd);
	$stmt->execute($value_array);
	$fee_array = $stmt->fetchAll(PDO::FETCH_ASSOC);
	$fee_list = array();
	foreach ( $fee_array as $row ) {
		//$lesson_subject_list[$row["lesson_id"]][$row["subject_id"]] = $row;
		$fee_list[] = $row;
	}
//var_dump($fee_list);
	return $fee_list;
}

// 料金一覧の数を取得
function get_fee_cnt($db, $param_array=array(), $value_array=array()) {
	$cmd = 
		"SELECT
				count(*) as count
		 FROM tbl_fee";
  if(count($param_array) > 0){
    $cmd .= " WHERE " . join(" AND ",$param_array);
  }
	$stmt = $db->prepare($cmd);
	$stmt->execute($value_array);
	$fee_array = $stmt->fetchAll(PDO::FETCH_ASSOC);
	$fee_cnt = $fee_array[0]["count"];
	return $fee_cnt;
}

function get_m_fee_list($db, $param_array=array(), $value_array=array()) {
	$cmd = 
		"SELECT
				tbl_monthly_fee.fee_no as fee_no,
				tbl_lesson.lesson_id as lesson_id,
				tbl_lesson.lesson_name as lesson_name,
				tbl_subject.subject_id as subject_id,
				tbl_subject.subject_name as subject_name,
				tbl_course.course_id as course_id,
				tbl_course.course_name as course_name,
				tbl_monthly_fee.fee as fee,
				tbl_monthly_fee.minus_price as minus_price
		 FROM tbl_monthly_fee, tbl_lesson, tbl_subject, tbl_course";
  if(count($param_array) > 0){
    $cmd .= " WHERE " . join(" AND ",$param_array) . "AND";
  } else {
		$cmd .= " WHERE ";
	}
	$cmd .= " tbl_monthly_fee.lesson_id = tbl_lesson.lesson_id
		 AND tbl_monthly_fee.subject_id = tbl_subject.subject_id
		 AND tbl_monthly_fee.course_id = tbl_course.course_id
		 ORDER BY tbl_lesson.lesson_id, tbl_course.course_id, tbl_subject.subject_id";
	$stmt = $db->prepare($cmd);
	$stmt->execute($value_array);
	$fee_array = $stmt->fetchAll(PDO::FETCH_ASSOC);
	$fee_list = array();
	foreach ( $fee_array as $row ) {
		$fee_list[] = $row;
	}
	return $fee_list;
}

// calculate_fee.phpのcaluculate_buying_textbook関数と同じ内容
// テキストブック購入一覧を取得
// 改ページ対応なし
// $param_array : year = ?
// $value_array : 2015
// $order_array : name
function get_buying_textbook_list($db, $param_array=array(), $value_array=array(), $order_array=array()) {
	$sql = 
		"SELECT
				tbl_buying_textbook.buying_no as buying_no,
				tbl_buying_textbook.member_no as member_no,
				tbl_buying_textbook.year as year,
				tbl_buying_textbook.month as month,
				tbl_buying_textbook.input_year as input_year,
				tbl_buying_textbook.input_month as input_month,
				tbl_buying_textbook.input_day as input_day,
				tbl_buying_textbook.text_subject_id as text_subject_id,
				tbl_buying_textbook.grade as grade,
				tbl_buying_textbook.name as name,
				tbl_buying_textbook.price as price,
				tbl_buying_textbook.lesson_id as lesson_id,
				tbl_buying_textbook.kind as kind
		 FROM tbl_buying_textbook";
  if(count($param_array) > 0){
    $sql .= " where " . join(" and ",$param_array);
  }
  if(count($order_array) > 0){
    $sql .= "	order by " . join(" , ",$order_array);
  }
	else {
		//$sql .= "	order by tbl_buying_textbook.year, tbl_buying_textbook.month, tbl_buying_textbook.day";
		$sql .= "	order by tbl_buying_textbook.buying_no";
	}
	$stmt = $db->prepare($sql);
	$stmt->execute($value_array);
	$buying_textbook_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
	return $buying_textbook_list;
}

// calculate_fee.phpのcaluculate_others関数と同じ内容
// その他購入一覧を取得
// 改ページ対応なし
// $param_array : year = ?
// $value_array : 2015
// $order_array : name
function get_others_list($db, $param_array=array(), $value_array=array(), $order_array=array()) {
	global $except20170830;
	$sql = 
		"SELECT
				tbl_others.others_no as others_no,
				tbl_others.member_no as member_no,
				tbl_others.year as year,
				tbl_others.month as month,
				tbl_others.name as name,
				tbl_others.price as price,
				tbl_others.tax_flag as tax_flag,
				tbl_others.lesson_id as lesson_id,
				tbl_others.type_id as type_id,
				tbl_others.kind as kind,
				tbl_others.memo as memo,
				tbl_others.charge as charge
		 FROM tbl_others";
  if(count($param_array) > 0){
    $sql .= " where " . join(" and ",$param_array);
  }
  if(count($order_array) > 0){
    $sql .= "	order by " . join(" , ",$order_array);
  }
	else {
		//$sql .= "	order by tbl_others.year, tbl_others.month";
		$sql .= "	order by tbl_others.others_no";
	}
	$stmt = $db->prepare($sql);
	$stmt->execute($value_array);
	$others_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

	// 20170830 2017夏期講習の特別対応
	if (strpos($sql,'charge=?')!==false) {
		foreach($others_list as $key=>$item) {
			if ($item["year"] == "2017" && $item["month"] == "8" && 
				$item["kind"] == 7 && $item["charge"] == 1 && 
				array_search($item["member_no"], $except20170830) !== false) {
				unset($others_list[$key]);
			}
		}
	}

	return $others_list;
}

// calculate_fee.phpのcaluculate_entrance_fee関数と同じ内容
// その他購入一覧を取得
// 改ページ対応なし
// $param_array : year = ?
// $value_array : 2015
// $order_array : name
function get_entrance_fee_list($db, $param_array=array(), $value_array=array(), $order_array=array()) {
	$sql = 
		"SELECT
				tbl_entrance_fee.entrance_fee_no as entrance_fee_no,
				tbl_entrance_fee.member_no as member_no,
				tbl_entrance_fee.year as year,
				tbl_entrance_fee.month as month,
				tbl_entrance_fee.price as price,
				tbl_entrance_fee.memo as memo
		 FROM tbl_entrance_fee";
  if(count($param_array) > 0){
    $sql .= " where " . join(" and ",$param_array);
  }
  if(count($order_array) > 0){
    $sql .= "	order by " . join(" , ",$order_array);
  }
	else {
		//$sql .= "	order by tbl_entrance_fee.year, tbl_entrance_fee.month";
		$sql .= "	order by tbl_entrance_fee.entrance_fee_no";
	}
	$stmt = $db->prepare($sql);
	$stmt->execute($value_array);
	$entrance_fee_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
	return $entrance_fee_list;
}



// 改ページ対応なし
// $param_array : year = ?
// $value_array : 2015
// $order_array : name
function get_divided_payment_list($db, $param_array=array(), $value_array=array(), $order_array=array()) {
	$sql = 
		"SELECT
				tbl_divided_payment.payment_no as payment_no,
				tbl_divided_payment.member_no as member_no,
				tbl_divided_payment.year as year,
				tbl_divided_payment.month as month,
				tbl_divided_payment.lesson_id as lesson_id,
				tbl_divided_payment.type_id as type_id,
				tbl_divided_payment.time as time,
				tbl_divided_payment.payment_price as payment_price,
				tbl_divided_payment.memo as memo
		 FROM tbl_divided_payment";
  if(count($param_array) > 0){
    $sql .= " where " . join(" and ",$param_array);
  }
  if(count($order_array) > 0){
    $sql .= "	order by " . join(" , ",$order_array);
  }
	else {
		$sql .= "	order by tbl_divided_payment.year, tbl_divided_payment.month";
	}
	$stmt = $db->prepare($sql);
	$stmt->execute($value_array);
	$divided_payment_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
	return $divided_payment_list;
}


// 改ページ対応なし
// $param_array : year = ?
// $value_array : 2015
// $order_array : name
function get_divided_payment_detail_list($db, $param_array=array(), $value_array=array(), $order_array=array()) {
	$sql = 
		"SELECT
				tbl_divided_payment_detail.payment_no as payment_no,
				tbl_divided_payment_detail.time_no as time_no,
				tbl_divided_payment_detail.payment_year as payment_year,
				tbl_divided_payment_detail.payment_month as payment_month,
				tbl_divided_payment_detail.price as price
		 FROM tbl_divided_payment_detail";
  if(count($param_array) > 0){
    $sql .= " where " . join(" and ",$param_array);
  }
  if(count($order_array) > 0){
    $sql .= "	order by " . join(" , ",$order_array);
  }
	else {
		$sql .= "	order by tbl_divided_payment_detail.time_no";
	}
	$stmt = $db->prepare($sql);
	$stmt->execute($value_array);
	$divided_payment_detail_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
	return $divided_payment_detail_list;
}

function get_both_divided_payment_list($db, $param_array=array(), $value_array=array(), $order_array=array()) {
		$sql = 
			"SELECT
					tbl_divided_payment.payment_no as payment_no,
					tbl_divided_payment.member_no as member_no,
					tbl_divided_payment.year as year,
					tbl_divided_payment.month as month,
					tbl_divided_payment.lesson_id as lesson_id,
					tbl_divided_payment.type_id as type_id,
					tbl_divided_payment.time as time,
					tbl_divided_payment.payment_price as payment_price,
					tbl_divided_payment.memo as memo,
					tbl_divided_payment_detail.payment_no as payment_no,
					tbl_divided_payment_detail.time_no as time_no,
					tbl_divided_payment_detail.payment_year as payment_year,
					tbl_divided_payment_detail.payment_month as payment_month,
					tbl_divided_payment_detail.price as price
			 FROM tbl_divided_payment, tbl_divided_payment_detail";
		$sql .= " where tbl_divided_payment_detail.payment_no = tbl_divided_payment.payment_no";
	  if(count($param_array) > 0){
	    $sql .= " and " . join(" and ",$param_array);
	  }
	  if(count($order_array) > 0){
	    $sql .= "	order by " . join(" , ",$order_array);
	  }
		else {
			$sql .= "	order by tbl_divided_payment.year, tbl_divided_payment.month";
		}
		$stmt = $db->prepare($sql);
		$stmt->execute($value_array);
		$divided_payment_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
 		return $divided_payment_list;
	}


// 予定一覧を取得
// $param_array : array("schedule_year = ?")
// $value_array : array("2015")
// $order_array : array("schedule_start_datetime")
function get_schedule_list($db, $param_array=array(), $value_array=array(), $order_array=array()) {
	$cmd = 
		"SELECT
				tbl_schedule.member_id as id,
				tbl_schedule.schedule_year as year,
        tbl_schedule.schedule_month as month,
				tbl_schedule.schedule_day as day,
				tbl_schedule.schedule_start_datetime as start_datetime,
				tbl_schedule.schedule_start_hour as start_hour,
				tbl_schedule.schedule_start_minute as start_minute,
				tbl_schedule.schedule_end_datetime as end_datetime,
				tbl_schedule.schedule_end_hour as end_hour,
				tbl_schedule.schedule_end_minute as end_minute,
				tbl_schedule.schedule_date_format as date_format,
				tbl_schedule.schedule_diff_hours as diff_hours,
				tbl_schedule.lesson_id as lesson_id,
				tbl_schedule.subject_id as subject_id,
				tbl_schedule.repeat_flag as repeat_flag
		 FROM tbl_schedule";
  if(count($param_array) > 0){
    $cmd .= " where " . join(" and ",$param_array);
  }
  if(count($order_array) > 0){
    $cmd .= "	order by " . join(" , ",$order_array);
  }
	else {
		$cmd .= "	order by tbl_schedule.member_id, tbl_schedule.schedule_start_datetime";
	}
	$stmt = $db->prepare($cmd);

//echo $cmd;
//var_dump($value_array);

	$stmt->execute($value_array);
	$schedule_array = $stmt->fetchAll(PDO::FETCH_ASSOC);
	if ($stmt->errorCode()) {
		$stmt->errorInfo();
	}
	$schedule_list = array();
	foreach ( $schedule_array as $row ) {
		//$schedule_list[$row["id"]] = $row;
		$schedule_list[] = $row;
	}
	return $schedule_list;
}

// イベント一覧
// 予定一覧を取得
// $param_array : array("event_year = ?")
// $value_array : array("2015")
// $order_array : array("event_start_datetime")
function get_event_list($db, $param_array=array(), $value_array=array(), $order_array=array()) {
	//tbl_event.event_date_format as date_format,
	// 20150727	「tbl_event.member_id as member_id,」を「tbl_event.member_no as member_no,」に修正
	// 20160423 tbl_event.teacher_id as teacher_idを追加
	$cmd = 
		"SELECT
				tbl_event.event_id as event_id,
				tbl_event.member_no as member_no,
				tbl_event.event_year as year,
        tbl_event.event_month as month,
				tbl_event.event_day as day,
				tbl_event.event_start_timestamp as start_timestamp,
				tbl_event.event_start_hour as start_hour,
				tbl_event.event_start_minute as start_minute,
				tbl_event.event_end_timestamp as end_timestamp,
				tbl_event.event_end_hour as end_hour,
				tbl_event.event_end_minute as end_minute,
				tbl_event.event_diff_hours as diff_hours,
				tbl_event.lesson_id as lesson_id,
				tbl_event.subject_id as subject_id,
				tbl_event.place_id as place_id,
				tbl_event.course_id as course_id,
				tbl_event.teacher_id as teacher_id,
				tbl_event.absent_flag as absent_flag,
				tbl_event.alternative_flag as alternative_flag,
				tbl_event.trial_flag as trial_flag,
				tbl_event.interview_flag as interview_flag,
				tbl_event.absent1_num as absent1_num,
				tbl_event.absent2_num as absent2_num,
				tbl_event.trial_num as trial_num,
				tbl_event.repeat_flag as repeat_flag,
				tbl_event.cal_evt_summary as cal_evt_summary,
				tbl_event.cal_attendance_data as cal_attendance_data,
				tbl_event.cal_summary as cal_summary,
				tbl_event.recurringEvent as recurringEvent,
				tbl_event.monthly_fee_flag as monthly_fee_flag
		 FROM tbl_event";
				//tbl_event.repeat_flag as repeat_flag,
				//tbl_event.cal_id as cal_id,
				//tbl_event.cal_summary as cal_summary,
				//tbl_event.cal_location as cal_location,
				//tbl_event.cal_description as cal_description,
				//tbl_event.cal_updated_timestamp as cal_updated_timestamp
  if(count($param_array) > 0){
    $cmd .= " where " . join(" and ",$param_array);
  }
  if(count($order_array) > 0){
    $cmd .= "	order by " . join(" , ",$order_array);
  }
	else {
		$cmd .= "	order by tbl_event.event_id";
	}
	$stmt = $db->prepare($cmd);
//echo $cmd;
//var_dump($value_array);
	$stmt->execute($value_array);
	$event_array = $stmt->fetchAll(PDO::FETCH_ASSOC);
	if ($stmt->errorCode()) {
		$stmt->errorInfo();
	}
	$event_list = array();
	foreach ( $event_array as $row ) {
		//$event_list[$row["id"]] = $row;
		$event_list[] = $row;
	}
	return $event_list;
}

function sanitize($str) {
	return htmlentities($str);
}

// レッスン選択プルダウンメニュー表示
function disp_lesson_menu($lesson_list, $name, $select_lesson_id = "") {
	$html = "<select name='".$name."'>\n";
	$html .= "<option value=''></option>\n";	// 「未選択」文字列削除
	foreach ($lesson_list as $id => $name) {
		if ($select_lesson_id == $id) {
			$html .= "<option value='".$id."' selected>".$name."</option>\n";
		} else {
			$html .= "<option value='".$id."'>".$name."</option>\n";
		}
	}
	$html .= "</select>\n";
	echo $html;
}

 // 科目選択プルダウンメニュー表示
function disp_subject_menu($subject_list, $name, $select_subject_id = "") {
	$html = "<select name='".$name."'>\n";
	$html .= "<option value=''></option>\n";	// 「未選択」文字列削除
	foreach ($subject_list as $id => $name) {
			if ($select_subject_id == $id && $select_subject_id != "") {
				$html .= "<option value='".$id."' selected>".$name."</option>\n";
			} else {
				$html .= "<option value='".$id."'>".$name."</option>\n";
			}
	}
	$html .= "</select>\n";
	echo $html;
}

// タイプ選択プルダウンメニュー表示
function disp_type_menu($type_list, $name, $select_type_id = "") {
	$html = "<select name='".$name."'>\n";
	$html .= "<option value=''></option>\n";	// 「未選択」文字列削除
	foreach ($type_list as $id => $name) {
		if ($select_type_id == $id) {
			$html .= "<option value='".$id."' selected>".$name."</option>\n";
		} else {
			$html .= "<option value='".$id."'>".$name."</option>\n";
		}
	}
	$html .= "</select>\n";
	echo $html;
}

// コース選択プルダウンメニュー表示
function disp_course_menu($course_list, $name, $select_course_id = "", $onchange = "") {
	if ($onchange) {
		$html = "<select name='".$name."' onchange='".$onchange.";'>\n";
	} else {
		$html = "<select name='".$name."'>\n";
	}
	$html .= "<option value=''></option>\n";	// 「未選択」文字列削除
	foreach ($course_list as $id => $course) {
		if ($select_course_id == $course["course_id"]) {
			$html .= "<option value='".$course["course_id"]."' selected>".$course["course_name"]."</option>\n";
		} else {
			$html .= "<option value='".$course["course_id"]."'>".$course["course_name"]."</option>\n";
		}
	}
	$html .= "</select>\n";
	echo $html;
}

// 先生選択プルダウンメニュー表示
function disp_teacher_menu($teacher_list, $name, $select_teacher_id = "") {
	$html = "<select name='".$name."'>\n";
	$html .= "<option value=''></option>\n";	// 「未選択」文字列削除
	foreach ($teacher_list as $teacher) {
		if ($select_teacher_id == $teacher["no"]) {
			$html .= "<option value='".$teacher["no"]."' selected>".$teacher["name"]."</option>\n";
		} else {
			$html .= "<option value='".$teacher["no"]."'>".$teacher["name"]."</option>\n";
		}
	}
	$html .= "</select>\n";
	echo $html;
}


// 種類選択プルダウンメニュー表示
function disp_kind_menu($kind_list, $name, $type="", $select_kind_id="", $onchange = "") {
	$tmp_kind_list = get_kind_array($type);
	if ($onchange) {
		$html = "<select name='".$name."' onchange='".$onchange.";'>\n";
	} else {
		$html = "<select name='".$name."'>\n";
	}
	$html .= "<option value=''></option>\n";	// 「未選択」文字列削除
	foreach ($tmp_kind_list as $id => $name) {
		if ($select_kind_id == $id) {
			$html .= "<option value='".$id."' selected>".$name."</option>\n";
		} else {
			$html .= "<option value='".$id."'>".$name."</option>\n";
		}
	}
	$html .= "</select>\n";
	echo $html;
}


// プルダウンメニュー表示
function disp_pulldown_menu($list, $select_name, $select = "", $onchange = "", &$str_out = 0) {
	if ($onchange) {
		$html = "<select name='$select_name' onchange='$onchange;'>\n";
	} else {
		$html = "<select name='$select_name'>\n";
	}
	$html .= "<option value=''></option>\n";	// 「未選択」文字列削除
	foreach ($list as $item) {
		$item1 = str_replace("<","&lt;",$item);
		$item1 = str_replace(">","&gt;",$item);
		$item1 = str_replace("&","&amp;",$item);
		$item1 = str_replace("'","&#39;",$item);
		if ($select == $item) {
			$html .= "<option value='$item1' selected>$item</option>\n";
		} else {
			$html .= "<option value='$item1'>$item</option>\n";
		}
	}
	$html .= "</select>\n";
	if ($str_out===0) { echo $html; } else { $str_out = $html; }
}


function disp_pulldown_menu1($list, $select_name, $select = "", $onchange = "", &$str_out = 0) {
	if ($onchange) {
		$html = "<select name='$select_name' onchange='$onchange;'>\n";
	} else {
		$html = "<select name='$select_name'>\n";
	}
	foreach ($list as $item) {
		$item1 = str_replace("<","&lt;",$item);
		$item1 = str_replace(">","&gt;",$item);
		$item1 = str_replace("&","&amp;",$item);
		$item1 = str_replace("'","&#39;",$item);
		if ($select == $item) {
			$html .= "<option value='$item1' selected>$item</option>\n";
		} else {
			$html .= "<option value='$item1'>$item</option>\n";
		}
	}
	$html .= "</select>\n";
	if ($str_out===0) { echo $html; } else { $str_out = $html; }
}


function get_kind_array($type="") {
	global $kind_list;
	$kind_array = array();
	foreach ($kind_list as $kind) {
		if ($type === "" || $type === $kind["type"]) {
			$kind_array[$kind["no"]] = $kind["name"];
		}
	}
	return $kind_array;
}

function check_student($db, &$errArray, $student) {
	$errFlag = 0;
	mb_regex_encoding("UTF-8");
	if ($student["name"] == "") {
		$errFlag = 1;
 		array_push($errArray, "氏名を入力してください。");
	}
	if (mb_strlen($student["name"], "UTF-8") > 18) {
		$errFlag = 1;
 		array_push($errArray, "氏名を１８文字以内で入力してください。");
	}
/*
	if ($student["sei"] == "") {
		$errFlag = 1;
 		array_push($errArray, "氏名（姓）を入力してください。");
	}
	if (mb_strlen($student["sei"], "UTF-8") > 13) {
		$errFlag = 1;
 		array_push($errArray, "氏名（姓）を１３文字以内で入力してください。");
	}
	if ($student["mei"] == "") {
		$errFlag = 1;
 		array_push($errArray, "氏名（名）を入力してください。");
	}
	if (mb_strlen($student["mei"], "UTF-8") > 13) {
		$errFlag = 1;
 		array_push($errArray, "氏名（名）を１３文字以内で入力してください。");
	}
*/
	$param_array = array();
	$value_array = array();
	if ($student["no"]) {
		array_push($param_array, "tbl_member.no <> ?");	// 同一の名前を重複して登録しないように完全一致で検索
		array_push($value_array, $student["no"]);
	}
	array_push($param_array, "tbl_member.name = ?");	// 同一の名前を重複して登録しないように完全一致で検索
	array_push($value_array, $student["name"]);
/*
	array_push($param_array, "tbl_member.sei = ?");
	array_push($value_array, $student["sei"]);
	array_push($param_array, "tbl_member.mei = ?");
	array_push($value_array, $student["mei"]);
*/
	$member_cnt = get_member_cnt($db, $param_array, $value_array);
	if ($member_cnt > 0) {
		$errFlag = 1;
 		array_push($errArray, "入力された姓と名はすでに登録されています。");
	}
	// ふりがな
	if ($student["furigana"] == "") {
		$errFlag = 1;
 		array_push($errArray, "ふりがなを入力してください。");
	}
	if (mb_strlen($student["furigana"], "UTF-8") > 40) {
		$errFlag = 1;
 		array_push($errArray, "ふりがなを４０文字以内で入力してください。");
	}
	// 月会費
	if ($student["membership_fee"] == "") {
		$errFlag = 1;
 		array_push($errArray, "月会費を入力してください。");
	} else if (preg_match("/^[0-9]+$/", $student["membership_fee"]) == false|| strlen($student["membership_fee"]) > 8) {
		$errFlag = 1;
 		array_push($errArray, "月会費を半角数字８文字以内で入力してください。");
	}
	// 授業料の税種別
	if (empty($student["tax_flag"]) == true && $student["tax_flag"] != 0) {
		$errFlag = 1;
 		array_push($errArray, "授業料の税種別を選択してください。");
	} else if (preg_match("/^[0-1]{1}$/", $student["tax_flag"]) == false) {
		$errFlag = 1;
 		array_push($errArray, "授業料の税種別を選択してください。");
	}

	// CID
	if ($student["cid"] != "") {
		//if (preg_match("/^[0-9a-zA-Z\-\_]+$/", $student["cid"]) == false) {
		if (preg_match("/^[0-9a-zA-Z]+$/", $student["cid"]) == false) {
			$errFlag = 1;
	 		array_push($errArray, "CIDを正しく入力してください。");
		}
	}
	// スプレッドシートID
	if ($student["sheet_id"] != "") {
		if (preg_match("/^[0-9a-zA-Z\-\_]+$/", $student["sheet_id"]) == false|| strlen($student["sheet_id"]) > 50) {
			$errFlag = 1;
	 		array_push($errArray, "シートIDを半角英数５０文字以内で入力してください。");
		}
	}
	// ステータス（削除フラグ）
	if ($student["del_flag"] != "") {
		if (preg_match("/^[0-2]{1}$/", $student["del_flag"]) == false) {
			$errFlag = 1;
	 		array_push($errArray, "ステータスを選択してください。");
		}
	}
	// 授業料の税表示（税フラグ）
	if ($student["tax_flag"] != "") {
		if (preg_match("/^[0,1]{1}$/", $student["tax_flag"]) == false) {
			$errFlag = 1;
	 		array_push($errArray, "授業料の税表示フラグを選択してください。");
		}
	}
	if ($errFlag == 0) {
		return true;
	} else {
		return false;
	}
}

function check_buying_textbook_list(&$errArray, $buying_textbook_array) {
	$errFlag = 0;
	if ($buying_textbook_array["year"] == "" || 
			preg_match("/^[0-9]+$/", $buying_textbook_array["year"]) == false || 
			strlen($buying_textbook_array["year"]) < 4 || 
			$buying_textbook_array["year"] < 2015) {
		$errFlag = 1;
 		array_push($errArray, "年を半角数字４桁で正しく入力してください。");
	}
	if ($buying_textbook_array["month"] == "" || 
			preg_match("/^[0-9]+$/", $buying_textbook_array["month"]) == false || 
			$buying_textbook_array["month"] < 1 || $buying_textbook_array["month"] > 12) {
		$errFlag = 1;
 		array_push($errArray, "月を半角数字２桁以内で正しく入力してください。");
	}
	if ($buying_textbook_array["input_year"] == "" || 
			preg_match("/^[0-9]+$/", $buying_textbook_array["input_year"]) == false || 
			strlen($buying_textbook_array["input_year"]) < 4 || 
			$buying_textbook_array["input_year"] < 2015) {
		$errFlag = 1;
 		array_push($errArray, "入力日（年）を半角数字４桁で正しく入力してください。");
	}
	if ($buying_textbook_array["input_month"] == "" || 
			preg_match("/^[0-9]+$/", $buying_textbook_array["input_month"]) == false || 
			$buying_textbook_array["input_month"] < 1 || $buying_textbook_array["input_month"] > 12) {
		$errFlag = 1;
 		array_push($errArray, "入力日（月）を半角数字２桁以内で正しく入力してください。");
	}
	if ($buying_textbook_array["input_day"] == "" || 
			preg_match("/^[0-9]+$/", $buying_textbook_array["input_day"]) == false|| 
			$buying_textbook_array["input_day"] < 1 || $buying_textbook_array["input_day"] > 31) {
		$errFlag = 1;
 		array_push($errArray, "入力日（日）を半角数字２桁以内で入力してください。");
	}

	if (($buying_textbook_array["input_year"] != "" && $buying_textbook_array["input_month"] != "" && $buying_textbook_array["input_day"] != "") &&
			checkdate ($buying_textbook_array["input_month"], $buying_textbook_array["input_day"], $buying_textbook_array["input_year"]) === false) {
		$errFlag = 1;
 		array_push($errArray, "入力日の年月日を正しく入力してください。");
	}
/*
20150731暫定対応
	if ($others_array["member_no"] == "" || 
			preg_match("/^[0-9]{6}$/", $others_array["member_no"]) == false) {
		$errFlag = 1;
 		array_push($errArray, "生徒を選択してください。");
	}
*/
// 20151028 name列はvarchar(200)へ変更
	if ($buying_textbook_array["name"] == "" || 
		mb_strlen($buying_textbook_array["name"], "UTF-8") > 80) {
		$errFlag = 1;
 		array_push($errArray, "テキストブック名を８０文字以内で入力してください。");
	}
	if ($buying_textbook_array["price"] == "" || 
			preg_match("/^[0-9]+$/", $buying_textbook_array["price"]) == false || strlen($buying_textbook_array["price"]) > 8) {
		$errFlag = 1;
 		array_push($errArray, "金額を半角数字８桁以内で入力してください。");
	}
	if ($buying_textbook_array["lesson_id"] == "" || preg_match("/^[0-9]{1,2}$/", $buying_textbook_array["lesson_id"]) == false) {
		$errFlag = 1;
		array_push($errArray, "教室を選択してください。");
	}
	if ($buying_textbook_array["kind"] == "" || preg_match("/^[0-9]{1,2}$/", $buying_textbook_array["kind"]) == false) {
		$errFlag = 1;
		array_push($errArray, "種類を選択してください。");
	}
	if ($errFlag == 0) {
		return true;
	} else {
		return false;
	}
}

function check_others_list(&$errArray, $others_array) {
	$errFlag = 0;
	global $kind_list;
	if ($others_array["year"] == "" || 
		preg_match("/^[0-9]+$/", $others_array["year"]) == false || 
		strlen($others_array["year"]) < 4 || 
		$others_array["year"] < 2015) {
		$errFlag = 1;
 		array_push($errArray, "年を半角数字４桁で正しく入力してください。");
	}
	if ($others_array["month"] == "" || 
		preg_match("/^[0-9]+$/", $others_array["month"]) == false || 
		$others_array["month"] < 1 || $others_array["month"] > 12) {
		$errFlag = 1;
 		array_push($errArray, "月を半角数字２桁以内で正しく入力してください。");
	}
/*
20150731暫定対応
	if ($others_array["member_no"] == "" || 
			preg_match("/^[0-9]{6}$/", $others_array["member_no"]) == false) {
		$errFlag = 1;
 		array_push($errArray, "生徒を選択してください。");
	}
*/
	if ($others_array["lesson_id"] == "" || 
		preg_match("/^[0-9]{1,20}$/", $others_array["lesson_id"]) == false || 
		$others_array["lesson_id"] < 1 || $others_array["lesson_id"] > 4) {
		$errFlag = 1;
 		array_push($errArray, "教室を選択してください。");
	}
/*
// 20151114 未使用
	if ($others_array["type_id"] == "" || 
		preg_match("/^[0-9]{1,20}$/", $others_array["type_id"]) == false || 
		$others_array["type_id"] < 1 || $others_array["type_id"] > 4) {
		$errFlag = 1;
 		array_push($errArray, "タイプを選択してください。");
	}
*/
	if ($others_array["kind"] == "" || 
		preg_match("/^[0-9]{1,20}$/", $others_array["kind"]) == false || 
		$others_array["kind"] < 1 || $others_array["kind"] > count($kind_list)+2) {
		$errFlag = 1;
 		array_push($errArray, "種類を選択してください。");
	}
	if ($others_array["name"] == "" || 
		mb_strlen($others_array["name"], "UTF-8") > 30) {
		$errFlag = 1;
 		array_push($errArray, "項目名を３０文字以内で入力してください。");
	}
	// 20160308 マイナス金額も入力できるようにする
	//if ($others_array["price"] == "" || 
	//	preg_match("/^[0-9]+$/", $others_array["price"]) == false || strlen($others_array["price"]) > 8) {
	//	$errFlag = 1;
 	//	array_push($errArray, "金額を半角数字８桁以内で入力してください。");
	//}
	if ($others_array["price"] == "" || 
		preg_match("/^[\-0-9]+$/", $others_array["price"]) == false || strlen($others_array["price"]) > 8) {
		$errFlag = 1;
 		array_push($errArray, "金額を半角数字８桁以内で入力してください。");
	}
	if (mb_strlen($others_array["memo"], "UTF-8") > 50) {
		$errFlag = 1;
 		array_push($errArray, "備考を５０文字以内で入力してください。");
	}
	if ($errFlag == 0) {
		return true;
	} else {
		return false;
	}
}


function check_entrance_fee_list(&$errArray, $entrance_fee_array) {
	$errFlag = 0;
	global $kind_list;
	if ($entrance_fee_array["year"] == "" || 
		preg_match("/^[0-9]+$/", $entrance_fee_array["year"]) == false || 
		strlen($entrance_fee_array["year"]) < 4 || 
		$entrance_fee_array["year"] < 2015) {
		$errFlag = 1;
 		array_push($errArray, "年を半角数字４桁で正しく入力してください。");
	}
	if ($entrance_fee_array["month"] == "" || 
		preg_match("/^[0-9]+$/", $entrance_fee_array["month"]) == false || 
		$entrance_fee_array["month"] < 1 || $entrance_fee_array["month"] > 12) {
		$errFlag = 1;
 		array_push($errArray, "月を半角数字２桁以内で正しく入力してください。");
	}
/*
20150731暫定対応
	if ($entrance_fee_array["member_no"] == "" || 
			preg_match("/^[0-9]{6}$/", $entrance_fee_array["member_no"]) == false) {
		$errFlag = 1;
 		array_push($errArray, "生徒を選択してください。");
	}
*/
	if ($entrance_fee_array["price"] == "" || 
		preg_match("/^[0-9]+$/", $entrance_fee_array["price"]) == false || strlen($entrance_fee_array["price"]) > 8) {
		$errFlag = 1;
 		array_push($errArray, "金額を半角数字８桁以内で入力してください。");
	}
	if (mb_strlen($entrance_fee_array["memo"], "UTF-8") > 50) {
		$errFlag = 1;
 		array_push($errArray, "備考を５０文字以内で入力してください。");
	}
	if ($errFlag == 0) {
		return true;
	} else {
		return false;
	}
}



// 分割情報をチェック
function check_divided_payment(&$errArray, $payment_array) {
	$errFlag = 0;
	if ($payment_array["year"] == "" || 
			preg_match("/^[0-9]{4}$/", $payment_array["year"]) == false || 
			$payment_array["year"] < 2015) {
		$errFlag = 1;
 		array_push($errArray, "年を半角数字４桁で正しく入力してください。");
	}
	if ($payment_array["month"] == "" || 
			preg_match("/^[0-9]{1,2}$/", $payment_array["month"]) == false || 
			$payment_array["month"] < 1 || $payment_array["month"] > 12) {
		$errFlag = 1;
 		array_push($errArray, "月を半角数字２桁以内で正しく入力してください。");
	}
	if ($payment_array["time"] == "" || 
			preg_match("/^[0-9]{1,2}$/", $payment_array["time"]) !== 1) {
		$errFlag = 1;
 		array_push($errArray, "回数を半角数字２桁以内で正しく入力してください。");
	}
//	if ($payment_array["payment_price"] === "" || 
//		preg_match("/^[0-9]{1,8}$/", $payment_array["payment_price"]) !== 1) {
//		$errFlag = 1;
// 		array_push($errArray, "合計授業料金を半角数字８桁以内で正しく入力してください。".$payment_array["payment_price"]);
//	}
	// memoは任意項目
	if ($errFlag == 0) {
		return true;
	} else {
		return false;
	}
}

// 分割詳細情報をチェック
function check_divided_payment_detail($db, &$errArray, $detail_array) {
	$errFlag = 0;
	if ($detail_array["time_no"] == "" || 
		preg_match("/^[0-9]{1,2}$/", $detail_array["time_no"]) !== 1 || 
		$detail_array["time_no"] < 1) {
		$errFlag = 1;
 		array_push($errArray, "回数を半角数字２桁以内で正しく入力してください。");
	}
	if ($detail_array["payment_year"] == "" || 
		preg_match("/^[0-9]{4}$/", $detail_array["payment_year"]) !== 1 || 
		$detail_array["payment_year"] < 2015) {
		$errFlag = 1;
 		array_push($errArray, "年を半角数字４桁以内で正しく入力してください。");
	}
	if ($detail_array["payment_month"] == "" || 
		preg_match("/^[0-9]{1,2}$/", $detail_array["payment_month"]) !== 1 || 
		$detail_array["payment_month"] < 1 || $detail_array["payment_month"] > 12) {
		$errFlag = 1;
 		array_push($errArray, "月を半角数字２桁以内で正しく入力してください。");
	}
	if ($detail_array["price"] == "" || 
		preg_match("/^[0-9]{1,8}$/", $detail_array["price"]) !== 1) {
		$errFlag = 1;
 		array_push($errArray, "支払金額を半角数字８桁以内で正しく入力してください。");
	}
	if ($errFlag == 0) {
		return true;
	} else {
		return false;
	}
}


function check_fee_list($db, &$errArray, $student_no, $fee_list) {
	$errFlag = 0;
	$tmp_fee_list = $fee_list;
	foreach ($fee_list as $key => $item) {
		if ($item["lesson_id"] == "" || preg_match("/^[0-9]+$/", $item["lesson_id"]) == false || strlen($item["lesson_id"]) > 2) {
			$errFlag = 1;
	 		array_push($errArray, ($key+1)."行目、レッスンを選択してください。");
		}
		// 20150925 ピアノも科目を選択するように修正
		// 20160128 英会話も科目を選択するように修正
		if ($item["lesson_id"] == "1" || $item["lesson_id"] == "3" || $item["lesson_id"] == "4") {
		// レッスンが塾、ピアノ、習い事の場合
			if ($item["subject_id"] == "" || $item["subject_id"] == "0" || preg_match("/^[0-9]+$/", $item["subject_id"]) == false || strlen($item["subject_id"]) > 2) {
				$errFlag = 1;
		 		array_push($errArray, ($key+1)."行目、科目を選択してください。");
			}
		} else {
 		// レッスンが英会話の場合
			//if ($item["subject_id"] == "" || $item["subject_id"] > "0") {
			//	$errFlag = 1;
		 	//	array_push($errArray, ($key+1)."行目、塾、ピアノ、習い事でない場合は「科目なし」を選択してください。");
			//}
			if ($item["subject_id"] == "" || $item["subject_id"] == "0" || preg_match("/^[0-9]{1,2}$/", $item["subject_id"]) == false) {
				$errFlag = 1;
		 		array_push($errArray, ($key+1)."行目、科目で講師名または「英会話」を選択してください。");
			}
		}
		//if ($item["type_id"] == "" || preg_match("/^[0-9]+$/", $item["type_id"]) == false || strlen($item["type_id"]) > 2) {
		if ($item["course_id"] == "" || preg_match("/^[0-9]+$/", $item["course_id"]) == false || strlen($item["course_id"]) > 2) {
			$errFlag = 1;
	 		array_push($errArray, ($key+1)."行目、コースを入力してください。");
		}
		if ($item["teacher_id"] == "" || preg_match("/^[0-9]+$/", $item["teacher_id"]) == false) {
			$errFlag = 1;
	 		array_push($errArray, ($key+1)."行目、先生を選択してください。");
		}
		if ($item["fee"] === "" || preg_match("/^[.0-9]+$/", $item["fee"]) == false) {
			$errFlag = 1;
	 		array_push($errArray, ($key+1)."行目、１時間あたりの料金を数字で入力してください。");
		}
		if (strlen($item["fee"]) > 8) {
			$errFlag = 1;
	 		array_push($errArray, ($key+1)."行目、１時間あたりの料金を８桁以内で入力してください。");
		}
		// ファミリーのマイナス金額
		if ($item["family_minus_price"] != "") {
			if (preg_match("/^[0-9]+$/", $item["family_minus_price"]) == false|| strlen($item["family_minus_price"]) > 8) {
				$errFlag = 1;
		 		array_push($errArray, "ファミリーのマイナス金額を半角数字８文字以内で入力してください。");
			}
		}
		if ($item["fee_no"] == "") {
		// 新規登録時
			// フォームで、同じ内容がないかチェックする
			$result = check_duplicate_fee($db, $fee_list, $item["lesson_id"], $item["subject_id"], $item["course_id"], $item["teacher_id"]);
			if (!$result) {
				$errFlag = 1;
		 		array_push($errArray, ($key+1)."行目、入力されたレッスン・科目・タイプ・先生は他の行と重複しています。");
			}
		} else {
		// 更新時
			// DBに、同じ内容が登録されていないかチェックする
			//$result = check_duplicate_fee2($db, $student_no, $item["lesson_id"], $item["subject_id"], $item["type_id"], $item["fee_no"]);
			//if (!$result) {
			//	$errFlag = 1;
		 	//	array_push($errArray, ($key+1)."行目、入力されたレッスン・科目・コース・先生はすでに登録されています。");
			//}
			// フォームで、同じ内容がないかチェックする
			$result = check_duplicate_fee($db, $fee_list, $item["lesson_id"], $item["subject_id"], $item["course_id"], $item["teacher_id"]);
			if (!$result) {
				$errFlag = 1;
		 		array_push($errArray, ($key+1)."行目、入力されたレッスン・科目・タイプ・先生は他の行と重複しています。");
			}
		}
	}
	if ($errFlag == 0) {
		return true;
	} else {
		return false;
	}
}

// 2015/06/12 複数科目対応
// 科目の新規登録時に、フォームに同じ内容がないかチェックする
// $fee_arrayは、入力欄に入力された値
// 重複があればfalse、重複がなければtrue
function check_duplicate_fee(&$db, $fee_array, $lesson_id, $subject_id, $course_id, $teacher_id) {
	$cnt = 0;
//echo $lesson_id."-".$subject_id."-".$course_id."-".$teacher_id."\n";
//var_dump($fee_array);
	foreach ($fee_array as $fee) {
		if ($fee["lesson_id"] == $lesson_id && $fee["subject_id"] == $subject_id && $fee["course_id"] == $course_id && $fee["teacher_id"] == $teacher_id) {
			$cnt++;
		}
/*
		$plural_subjects_list = get_parent_subject_list($db, array("tbl_plural_subjects.plural_subjects_id = ?"), array($fee["subject_id"]));
//var_dump($plural_subjects_list);
		foreach ($plural_subjects_list as $plural_subjects) {
			if ($fee["lesson_id"] == $lesson_id && $plural_subjects["subject_id"] == $subject_id && $fee["type_id"] == $type_id) {
				$cnt++;
			}
    }
*/
	}

	if ($cnt > 1) {
		return false;
	} else {
		return true;
	}
}

/*
// 2015/06/12 複数科目対応
// 科目の更新時に、DBに同じ内容がないかチェックする
// 重複があればfalse、重複がなければtrue
function check_duplicate_fee2(&$db, $member_no, $lesson_id, $subject_id, $type_id, $fee_no) {
	//　入力された科目そのものをチェック
	$param_array = array();
	$value_array = array();
	array_push($param_array, "tbl_fee.fee_no <> ?");
	array_push($value_array, $fee_no);
	array_push($param_array, "tbl_fee.member_no = ?");
	array_push($value_array, $member_no);
	array_push($param_array, "tbl_fee.lesson_id = ?");
	array_push($value_array, $lesson_id);
	array_push($param_array, "tbl_fee.subject_id = ?");
	array_push($value_array, $subject_id);
	array_push($param_array, "tbl_fee.type_id = ?");
	array_push($value_array, $type_id);
	$fee_cnt = get_fee_cnt($db, $param_array, $value_array);
	if ($fee_cnt > 0) {
		return false;
	}
	$member_array = get_member($db, array("tbl_member.no = ?"), array($member_no));
	// 入力された科目が複数科目の場合、複数科目に含まれる科目をチェック
	$plural_subjects_list = get_parent_subject_list($db, array("tbl_plural_subjects.plural_subjects_id = ?"), array($subject_id));
//var_dump($plural_subjects_list);
	foreach ($plural_subjects_list as $plural_subjects) {
			$param_array = array();
			$value_array = array();
			array_push($param_array, "tbl_fee.fee_no <> ?");
			array_push($value_array, $fee_no);
			array_push($param_array, "tbl_fee.member_no = ?");
			array_push($value_array, $member_no);
			array_push($param_array, "tbl_fee.lesson_id = ?");
			array_push($value_array, $lesson_id);
			array_push($param_array, "tbl_fee.subject_id = ?");
			array_push($value_array, $plural_subjects["subject_id"]);
			array_push($param_array, "tbl_fee.type_id = ?");
			array_push($value_array, $type_id);
			$fee_cnt = get_fee_cnt($db, $param_array, $value_array);
			if ($fee_cnt > 0) {
				return false;
			}
		}
		return true;
	}
*/

function check_number(&$errArray, $name, $value, $not_empty=false) {
	$errFlag = 0;
	if ($not_empty == true && $value == "") {
		$errFlag = 1;
 		array_push($errArray, $name."を入力してください。");
	} else if (preg_match("/^[0-9]+$/", $value) == false) {
		$errFlag = 1;
 		array_push($errArray, $name."を数字で入力してください。");
	}
	if ($errFlag == 0) {
		return true;
	} else {
		return false;
	}
}


function insert_student(&$db, $student_array) {
	$errFlag = 0;
	try {
			$stmt = $db->prepare("INSERT INTO tbl_member_no (insert_timestamp, update_timestamp) VALUES (now(), now())");
			$stmt->execute();
			$last_no = $db->lastInsertId();
			$sql = "INSERT INTO tbl_member (no, id, name, furigana, sei, mei, kind, grade, ".
						" passwd, sheet_id, membership_fee, cid, del_flag, tax_flag, insert_timestamp, update_timestamp, jyukensei, ".
						" birth_year, birth_month, birth_day, grade_adj, fee_free, yuge_price, gender, mail_address ".
						" ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, now(), now(), ?, ?, ?, ?, ?, ?, ?, ?, ?)";

			$stmt = $db->prepare($sql);
			$stmt->bindParam(1, $no);
			$stmt->bindParam(2, $id);
			$stmt->bindParam(3, $name);
			$stmt->bindParam(4, $furigana);
			$stmt->bindParam(5, $sei);
			$stmt->bindParam(6, $mei);
			$stmt->bindParam(7, $kind);
			$stmt->bindParam(8, $grade);
			$stmt->bindParam(9, $passwd);
			$stmt->bindParam(10, $sheet_id);
			$stmt->bindParam(11, $membership_fee);
			$stmt->bindParam(12, $cid);
			$stmt->bindParam(13, $del_flag);
			$stmt->bindParam(14, $tax_flag);
			$stmt->bindParam(15, $jyukensei);
			$stmt->bindParam(16, $birth_year);
			$stmt->bindParam(17, $birth_month);
			$stmt->bindParam(18, $birth_day);
			$stmt->bindParam(19, $grade_adj);
			$stmt->bindParam(20, $fee_free);
			$stmt->bindParam(21, $yuge_price);
			$stmt->bindParam(22, $gender);
			$stmt->bindParam(23, $mail_address);
			$no = sprintf("%06d", $last_no);
			$id = null;		// ログインのためのidはあとから
			$name = $student_array["name"];
			$furigana = $student_array["furigana"];
			//if ($student_array["mei"] != "") {
			//	$name = $student_array["sei"]."　".$student_array["mei"]; // 全角スペースでつなぐ
			//	$mei = $student_array["mei"];
			//} else {
			//	$name = $student_array["sei"];
			//	$mei = null;
			//}
			$sei = $student_array["sei"];
			$mei = $student_array["mei"];
			$kind = 3;		// 生徒は「3」
			$grade = $student_array["grade"];
			$passwd = null;	// ログインのためのパスワードはあとから
			$sheet_id = $student_array["sheet_id"];
			$membership_fee = $student_array["membership_fee"];
			$cid = $student_array["cid"];
			$del_flag = $student_array["del_flag"];
			$tax_flag = $student_array["tax_flag"];
			$jyukensei = $student_array["jyukensei"];
			$birth_year = $student_array["birth_year"];
			$birth_month = $student_array["birth_month"];
			$birth_day = $student_array["birth_day"];
			$grade_adj = $student_array["grade_adj"];
			$fee_free = $student_array["fee_free"];
			$yuge_price = $student_array["yuge_price"];
			$gender = $student_array["gender"];
			$mail_address = $student_array["mail_address"];
			$stmt->execute();
		}catch (PDOException $e){
			$errFlag = 1;
			throw $e;
		  //print('Error:'.$e->getMessage());
		}
	if ($errFlag == 0) {
		return $no;
	} else {
		return false;
	}
}

function update_student(&$db, $student_array) {
	$errFlag = 0;
	try {
			$sql = "UPDATE tbl_member SET ".
						" id=?, name=?, furigana=?, sei=?, mei=?, kind=?, grade=?, ".
						" passwd=?, sheet_id=?, membership_fee=?, cid=?, del_flag=?, tax_flag=?, update_timestamp=now(), jyukensei=?, ".
						" birth_year=?, birth_month=?, birth_day=?, grade_adj=?, fee_free=?, yuge_price=?, gender=?, mail_address=? ".
						" WHERE no=?";
			$stmt = $db->prepare($sql);
			$stmt->bindParam(1, $id);
			$stmt->bindParam(2, $name);
			$stmt->bindParam(3, $furigana);
			$stmt->bindParam(4, $sei);
			$stmt->bindParam(5, $mei);
			$stmt->bindParam(6, $kind);
			$stmt->bindParam(7, $grade);
			$stmt->bindParam(8, $passwd);
			$stmt->bindParam(9, $sheet_id);
			$stmt->bindParam(10, $membership_fee);
			$stmt->bindParam(11, $cid);
			$stmt->bindParam(12, $del_flag);
			$stmt->bindParam(13, $tax_flag);
			$stmt->bindParam(14, $jyukensei);
			$stmt->bindParam(15, $birth_year);
			$stmt->bindParam(16, $birth_month);
			$stmt->bindParam(17, $birth_day);
			$stmt->bindParam(18, $grade_adj);
			$stmt->bindParam(19, $fee_free);
			$stmt->bindParam(20, $yuge_price);
			$stmt->bindParam(21, $gender);
			$stmt->bindParam(22, $mail_address);
			$stmt->bindParam(23, $no);
			$id = null;		// ログインのためのidはあとから
			$name = $student_array["name"];
			$furigana = $student_array["furigana"];
			//if ($student_array["mei"] != "") {
			//	$name = $student_array["sei"]."　".$student_array["mei"]; // 全角スペースでつなぐ
			//	$mei = $student_array["mei"];
			//} else {
			//	$name = $student_array["sei"];
			//	$mei = null;
			//}
			// 20160525修正 $student_array["sei"]と$student_array["mei"]は未定義だった
			//$sei = $student_array["sei"];
			//$mei = $student_array["mei"];
			$sei = null;
			$mei = null;
			$kind = 3;		// 生徒は「3」
			$grade = $student_array["grade"];
			$passwd = null;	// ログインのためのパスワードはあとから
			$sheet_id = $student_array["sheet_id"];
			$membership_fee = $student_array["membership_fee"];
			$cid = $student_array["cid"];
			$del_flag = $student_array["del_flag"];
			$tax_flag = $student_array["tax_flag"];
			$jyukensei = $student_array["jyukensei"];
			$birth_year = $student_array["birth_year"];
			$birth_month = $student_array["birth_month"];
			$birth_day = $student_array["birth_day"];
			$grade_adj = $student_array["grade_adj"];
			$fee_free = $student_array["fee_free"];
			$yuge_price = $student_array["yuge_price"];
			$gender = $student_array["gender"];
			$mail_address = $student_array["mail_address"];
			$no = $student_array["no"];
			$stmt->execute();
	}catch (PDOException $e){
		$errFlag = 1;
	  //print('Error:'.$e->getMessage());
		throw $e;
	}
	if ($errFlag == 0) {
		return true;
	} else {
		return false;
	}
}

function delete_student(&$db, $student_no) {
		$errFlag = 0;
		try{
			// 20160118 メンバー情報に削除フラグを立てて削除するように変更
			//$stmt = $db->prepare("DELETE FROM tbl_member WHERE tbl_member.no = ?");
			//$stmt->execute(array($student_no));
			//$stmt = $db->prepare("DELETE FROM tbl_fee WHERE tbl_fee.member_no = ?");
			//$stmt->execute(array($student_no));
			$stmt = $db->prepare("UPDATE tbl_member SET del_flag = '1' WHERE tbl_member.no = ?");
			$stmt->execute(array($student_no));
		}catch (PDOException $e){
			$errFlag = 1;
			throw $e;
		  //print('Error:'.$e->getMessage());
		}
	if ($errFlag == 0) {
		return true;
	} else {
		return false;
	}
}


function edit_fee(&$db, $student_no, $fee_list, &$errArray) {
	$errFlag = 0;
	foreach ($fee_list as $key => $item) {
		try {
				if ($item["fee_no"] && $item["fee_no"] > 0) {
				// 更新時
					$result = update_fee($db, $item);
					if (!$result) {
						$errFlag = 1;
		      	break;
					}
				} else {
		    // 新規登録時
					$result = insert_fee($db, $student_no, $item);
					if (!$result) {
						$errFlag = 1;
		      	break;
					}
				}
		}catch (PDOException $e){
			$errFlag = 1;
			//throw $e;
			// 行を表示するためここでメッセージをセットする
			if (strpos($e->getMessage(), "Duplicate") !== FALSE) {
				array_push($errArray, ($key+1)."行目、レッスン・科目・コースが重複しています。");
			} else {
				array_push($errArray, ($key+1)."行目、登録中にエラーが発生しました。");
			}
		  //print('Error:'.$e->getMessage());
		}
	}
	if ($errFlag == 0) {
		return true;
	} else {
		return false;
	}

}

function insert_fee(&$db, $no, $fee_array) {
	$errFlag = 0;
	try{
		$stmt = $db->prepare("INSERT INTO tbl_fee_no (insert_timestamp, update_timestamp) VALUES (now(), now())");
		$stmt->execute();
		$last_fee_no = $db->lastInsertId();
		$sql = "INSERT INTO tbl_fee (fee_no, member_no, lesson_id, subject_id, course_id, teacher_id, fee,".
					" family_minus_price, additional_fee, insert_timestamp, update_timestamp, temp_flag".
					" ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, now(), now(), ?)";
		$stmt = $db->prepare($sql);
		$stmt->bindParam(1, $fee_no);
		$stmt->bindParam(2, $student_no);
		$stmt->bindParam(3, $lesson_id);
		$stmt->bindParam(4, $subject_id);
		$stmt->bindParam(5, $course_id);
		$stmt->bindParam(6, $teacher_id);
		$stmt->bindParam(7, $fee);
		$stmt->bindParam(8, $family_minus_price);
		$stmt->bindParam(9, $additional_fee);
		$stmt->bindParam(10, $temp_flag);
		$fee_no = $last_fee_no;
		$student_no = $no;
		$subject_id = $fee_array["subject_id"];
		$lesson_id = $fee_array["lesson_id"];
		$course_id = $fee_array["course_id"];
		$teacher_id = $fee_array["teacher_id"];
		$fee = $fee_array["fee"];
		$family_minus_price = $fee_array["family_minus_price"];
		$additional_fee = $fee_array["additional_fee"];
		$temp_flag = $fee_array["temp_flag"];
		$stmt->execute();
	}catch (PDOException $e){
		$errFlag = 1;
	  //print('Error:'.$e->getMessage());
		throw $e;
	}
	if ($errFlag == 0) {
		return true;
	} else {
		return false;
	}
}

function update_fee(&$db, $fee_array) {
	$errFlag = 0;
	try{
		$sql = "UPDATE tbl_fee SET lesson_id=?, subject_id=?, course_id=?, teacher_id=?, fee=?,".
					" family_minus_price=?, additional_fee=?, update_timestamp=now(),temp_flag=?".
					" WHERE tbl_fee.fee_no = ?";
		$stmt = $db->prepare($sql);
		$stmt->bindParam(1, $lesson_id);
		$stmt->bindParam(2, $subject_id);
		$stmt->bindParam(3, $course_id);
		$stmt->bindParam(4, $teacher_id);
		$stmt->bindParam(5, $fee);
		$stmt->bindParam(6, $family_minus_price);
		$stmt->bindParam(7, $additional_fee);
		$stmt->bindParam(8, $temp_flag);
		$stmt->bindParam(9, $fee_no);
		$subject_id = $fee_array["subject_id"];
		$lesson_id = $fee_array["lesson_id"];
		//$type_id = $fee_array["type_id"];
		$course_id = $fee_array["course_id"];
		$teacher_id = $fee_array["teacher_id"];
		$fee = $fee_array["fee"];
		$family_minus_price = $fee_array["family_minus_price"];
		$additional_fee = $fee_array["additional_fee"];
		$temp_flag = $fee_array["temp_flag"];
		$fee_no = $fee_array["fee_no"];
		$stmt->execute();
	}catch (PDOException $e){
		$errFlag = 1;
		throw $e;
	  //print('Error:'.$e->getMessage());
	}
	if ($errFlag == 0) {
		return true;
	} else {
		return false;
	}
}

function delete_fee(&$db, $delete_fee_no) {
	$errFlag = 0;
	try{
		$sql = "DELETE FROM tbl_fee ".
					 "WHERE tbl_fee.fee_no = ?";
		$stmt = $db->prepare($sql);
		$stmt->execute(array($delete_fee_no));
	}catch (PDOException $e){
		$errFlag = 1;
		throw $e;
	  //print('Error:'.$e->getMessage());
	}
	if ($errFlag == 0) {
		return true;
	} else {
		return false;
	}
}

function edit_m_fee(&$db, $student_no, $fee_list, &$errArray) {
	$errFlag = 0;
	foreach ($fee_list as $key => $item) {
		try {
				if ($item["fee_no"] && $item["fee_no"] > 0) {
				// 更新時
					$result = update_m_fee($db, $item);
					if (!$result) {
						$errFlag = 1;
		      	break;
					}
				} else {
		    // 新規登録時
					$result = insert_m_fee($db, $student_no, $item);
					if (!$result) {
						$errFlag = 1;
		      	break;
					}
				}
		}catch (PDOException $e){
			$errFlag = 1;
			//throw $e;
			// 行を表示するためここでメッセージをセットする
			if (strpos($e->getMessage(), "Duplicate") !== FALSE) {
				array_push($errArray, "月謝 ".($key+1)."行目、レッスン・科目・コースが重複しています。");
			} else {
				array_push($errArray, "月謝 ".($key+1)."行目、登録中にエラーが発生しました。");
			}
		  print('Error:'.$e->getMessage());
		}
	}
	if ($errFlag == 0) {
		return true;
	} else {
		return false;
	}

}

function insert_m_fee(&$db, $no, $fee_array) {
	$errFlag = 0;
	try{
		$stmt = $db->prepare("INSERT INTO tbl_monthly_fee_no (insert_timestamp, update_timestamp) VALUES (now(), now())");
		$stmt->execute();
		$last_fee_no = $db->lastInsertId();
		$sql = "INSERT INTO tbl_monthly_fee (fee_no, member_no, lesson_id, subject_id, course_id, fee, minus_price, insert_timestamp, update_timestamp".
					" ) VALUES (?, ?, ?, ?, ?, ?, ?, now(), now())";
		$stmt = $db->prepare($sql);
		$stmt->bindParam(1, $fee_no);
		$stmt->bindParam(2, $student_no);
		$stmt->bindParam(3, $lesson_id);
		$stmt->bindParam(4, $subject_id);
		$stmt->bindParam(5, $course_id);
		$stmt->bindParam(6, $fee);
		$stmt->bindParam(7, $minus_price);
		$fee_no = $last_fee_no;
		$student_no = $no;
		$subject_id = $fee_array["subject_id"];
		$lesson_id = $fee_array["lesson_id"];
		$course_id = $fee_array["course_id"];
		$fee = $fee_array["fee"];
		$minus_price = $fee_array["minus_price"];
		$stmt->execute();
	}catch (PDOException $e){
		$errFlag = 1;
		throw $e;
	}
	if ($errFlag == 0) {
		return true;
	} else {
		return false;
	}
}

function update_m_fee(&$db, $fee_array) {
	$errFlag = 0;
	try{
		$sql = "UPDATE tbl_monthly_fee SET lesson_id=?, subject_id=?, course_id=?, fee=?, minus_price=?, update_timestamp=now() ".
					 "WHERE tbl_monthly_fee.fee_no = ?";
		$stmt = $db->prepare($sql);
		$stmt->bindParam(1, $lesson_id);
		$stmt->bindParam(2, $subject_id);
		$stmt->bindParam(3, $course_id);
		$stmt->bindParam(4, $fee);
		$stmt->bindParam(5, $minus_price);
		$stmt->bindParam(6, $fee_no);
		$subject_id = $fee_array["subject_id"];
		$lesson_id = $fee_array["lesson_id"];
		$course_id = $fee_array["course_id"];
		$fee = $fee_array["fee"];
		$minus_price = $fee_array["minus_price"];
		$fee_no = $fee_array["fee_no"];
		$stmt->execute();
	}catch (PDOException $e){
		$errFlag = 1;
		throw $e;
	}
	if ($errFlag == 0) {
		return true;
	} else {
		return false;
	}
}

function delete_m_fee(&$db, $delete_fee_no) {
	$errFlag = 0;
	try{
		$sql = "DELETE FROM tbl_monthly_fee ".
					 "WHERE tbl_monthly_fee.fee_no = ?";
		$stmt = $db->prepare($sql);
		$stmt->execute(array($delete_fee_no));
	}catch (PDOException $e){
		$errFlag = 1;
		throw $e;
	}
	if ($errFlag == 0) {
		return true;
	} else {
		return false;
	}
}

function get_calculated_list($member_list, $year, $month) {
	foreach ($member_list as $member_no => $member) {
		$calculator = new calculate_fees();
		$result = $calculator->calculate($member_no, $year, $month);
		if ($result == false) {
			return false;
		}
		$student = array();
		$student["no"] = $member["no"];
		$student["name"] = $member["name"];
		$student["sheet_id"] = $member["sheet_id"];
		$student["cid"] = $member["cid"];
		$student["tax_flag"] = $member["tax_flag"];
		// 20160416 振込者名を追加
		if (isset($member["furikomisha_name"]) == true) {
			$student["furikomisha_name"] = $member["furikomisha_name"];
		}
		$student["total_hours"] = $calculator->get_total_hours();;
		$student["total_fees"] = $calculator->get_total_fees();
		$student["membership_fee"] = $calculator->get_membership_fee();
		$student["textbook_price"] = $calculator->get_textbook_price();
		$student["others_price"] = $calculator->get_others_price();
		$student["others_price_no_charge"] = $calculator->get_others_price_no_charge();
		$student["entrance_fee"] = $calculator->get_entrance_fee();
		//$student["others_price_without_entrance_fee"] = $calculator->get_others_price_without_entrance_fee();
		$student["divided_price"] = $calculator->get_divided_price();
		$student["simple_total_price"] = $calculator->get_simple_total_price();
		$student["consumption_tax_price"] = $calculator->get_consumption_tax_price();
		$student["last_total_price"] = $calculator->get_last_total_price();
		$student["lesson_detail_list"] = $calculator->get_lesson_detail_list();
		$student["buying_textbook_list"] = $calculator->get_buying_textbook_list();
		$student["others_list"] = $calculator->get_others_list();
		$student["divided_payment_list"] = $calculator->get_divided_payment_list();
		$student_list[$member["no"]] = $student;
		$calculator = null;
	}
	//uasort($student_list, 'cmp');
	return $student_list;
}

// 合計金額が多い順（降順）
function cmp($a, $b) {
	// カンマを取り除く
	$tmp_a = str_replace (",", "", $a["last_total_fees"]);
	$tmp_b = str_replace (",", "", $b["last_total_fees"]);
  if ($tmp_a == $tmp_b) {
    return 0;
  }
  return ($tmp_a > $tmp_b) ? -1 : 1;
}

function get_total_list($member_list, $year, $month) {
	$total_list = array();
	foreach ($member_list as $member_no => $member) {
		$calculator = new calculate_fees();
		$result = $calculator->calculate($member_no, $year, $month);
		if ($result == false) {
			return false;
		}
		$lesson_detail_list = array();
		$lesson_detail_list = $calculator->get_lesson_detail_list();
		foreach ($lesson_detail_list as $lesson_detail) {
		// $lesson_detailは科目ごとのリスト
			$lesson_id = "";
			$lesson_id = $lesson_detail["lesson_id"];
			foreach ($lesson_detail["event_list"] as $event) {
				$fees = 0;
				$fees = (int)str_replace(",","",$event["fees"]);
				if (array_key_exists($lesson_id, $total_list) === true) {
				// すでに科目がtotal_listにあるとき
					$total_list[$lesson_id]["price"] = $total_list[$lesson_id]["price"] + $fees;
				} else {
				// まだ科目がtotal_listにないとき
					$total_list[$lesson_id]["price"] = $fees;
				}
			}
		}
		$calculator = null;
	}
	return $total_list;
}

function check_input_year_month($year, $month) {
	if (isset($year) === false || empty($year) === true) {
		return false;
	} else {
		if (preg_match("/^[0-9]+$/", $year) == false || $year < 2015) {
			return false;
		}
	}
	if (isset($month) === false || empty($month) === true) {
		return false;
	} else {
		if (preg_match("/^[0-9]+$/", $month) == false || $month < 1 || $month > 12) {
			return false;
		}
	}
	return true;
}

// 先生一覧を取得
// 改ページ対応なし
// $param_array : kind = ?
// $order_array : name
function get_teacher_list($db, $param_array=array(), $value_array=array(), $order_array=array(), $all_flag=0) {
	$cmd = 
		"SELECT * FROM tbl_teacher";
	switch($all_flag) {
	case 0:	$cmd .= " where tbl_teacher.del_flag = '0'"; break;
	case 1:	$cmd .= " where tbl_teacher.del_flag = '0' or tbl_teacher.del_flag = '2'"; break;
	case 2:	$cmd .= " where tbl_teacher.del_flag >= '0'"; break;
	case 3:	$cmd .= " where tbl_teacher.del_flag = '2'"; break;
	}
	if(count($param_array) > 0){
		$cmd .= " and " . join(" and ",$param_array);
	}
	if(count($order_array) > 0){
		$cmd .= "	order by " . join(" , ",$order_array);
	}
	else {
		$cmd .= "	order by tbl_teacher.furigana";
	}
	$stmt = $db->prepare($cmd);
	$stmt->execute($value_array);
	$teacher_array = $stmt->fetchAll(PDO::FETCH_ASSOC);
	$teacher_list = array();
	foreach ( $teacher_array as $row ) {
		$teacher_list[$row["no"]] = $row;
	}
	return $teacher_list;
}


// 予定一覧を取得
// $param_array : array("schedule_year = ?")
// $value_array : array("2015")
// $order_array : array("schedule_start_datetime")
function get_available_schedule_list($db, $param_array=array(), $value_array=array(), $order_array=array()) {
	$cmd = 
		"SELECT
				tbl_available_schedule.schedule_no as schedule_no,
				tbl_available_schedule.teacher_no as teacher_no,
        tbl_available_schedule.day_of_week as day_of_week,
				tbl_available_schedule.start_hour as start_hour,
				tbl_available_schedule.start_minute as start_minute,
				tbl_available_schedule.end_hour as end_hour,
				tbl_available_schedule.end_minute as end_minute,
				concat(tbl_available_schedule.start_hour,tbl_available_schedule.start_minute) as start_time,
				concat(tbl_available_schedule.end_hour,tbl_available_schedule.end_minute) as end_time,
				tbl_available_schedule.place as place
		 FROM tbl_available_schedule";
  if(count($param_array) > 0){
    $cmd .= " where " . join(" and ",$param_array);
  }
  if(count($order_array) > 0){
    $cmd .= "	order by " . join(" , ",$order_array);
  }
	else {
		$cmd .= "	order by tbl_available_schedule.teacher_no, tbl_available_schedule.day_of_week,";
		$cmd .= "	tbl_available_schedule.start_hour, tbl_available_schedule.start_minute,";
		$cmd .= "	tbl_available_schedule.end_hour, tbl_available_schedule.end_minute";
	}
	$stmt = $db->prepare($cmd);

//echo $cmd;
//var_dump($value_array);

	$stmt->execute($value_array);
	$schedule_array = $stmt->fetchAll(PDO::FETCH_ASSOC);
	if ($stmt->errorCode()) {
		$stmt->errorInfo();
	}
	$schedule_list = array();
	foreach ( $schedule_array as $row ) {
		//$schedule_list[$row["id"]] = $row;
		$schedule_list[] = $row;
	}

//var_dump($schedule_list);

	return $schedule_list;
}


// reportで使用
function get_salary_data(&$db, $year, $month, $param_array=array(), $value_array=array()) {
	try {
		$sql = "SELECT ".
					"tbl_teacher.lesson_id as lesson_id, ".
					"tbl_teacher.no as teacher_no, ".
					"tbl_teacher.name as teacher_name, ".
					"tbl_salary.salary_price as salary_price ".
	 				"FROM tbl_salary, tbl_teacher ".
	 				"WHERE tbl_salary.teacher_no = tbl_teacher.no ".
					"AND tbl_salary.work_year = ? ".
					"AND  tbl_salary.work_month = ? ";
		if(count($param_array) > 0){
	    $sql .= " AND " . join(" AND ",$param_array);
	  }
		$sql .= " ORDER BY tbl_teacher.lesson_id, tbl_teacher.furigana";
//echo $sql;
		$stmt = $db->prepare($sql);
		$tmp_value_array = array_merge(array($year, $month), $value_array);
//var_dump($tmp_value_array);
		$stmt->execute($tmp_value_array);
		$salary_data = $stmt->fetchAll(PDO::FETCH_BOTH);
	} catch (Exception $e) {
//var_dump($e);
//		array_push($errArray, $e->getMessage());
  	return false;
	}
	if (count($salary_data) < 1) {
		$salary_data = array();
	}
//var_dump($student_data);
	return $salary_data;
}

// reportで使用
function get_lesson_data(&$db, $year, $month, $param_array=array(), $value_array=array()) {
	try {
		$sql = "SELECT * FROM view_lesson ".
					"WHERE view_lesson.year = ? ".
					"AND view_lesson.month = ?";
		if(count($param_array) > 0){
	    $sql .= " AND " . join(" AND ",$param_array);
	  }
		$sql .= " ORDER BY lesson_id, teacher_id";
//echo $sql;
		$stmt = $db->prepare($sql);
		$tmp_value_array = array_merge(array($year, $month),$value_array);
//var_dump($tmp_value_array);
		$stmt->execute($tmp_value_array);
		$lesson_data = $stmt->fetchAll(PDO::FETCH_BOTH);
		if (count($lesson_data) < 1) {
			return false;
	  }
	} catch (Exception $e) {
		//array_push($errArray, $e->getMessage());
//var_dump($e);
  	return false;
	}
	if (count($lesson_data) < 1) {
		$lesson_data = array();
	}
	return $lesson_data;
}



function insert_teacher(&$db, $teacher_array) {
	$errFlag = 0;
	try {
			$last_no = get_teacher_cnt($db, array(), array(), "2")+1;
			$sql = "INSERT INTO tbl_teacher (no, name, furigana, lesson_id, del_flag,".
						" mail_address, password, initial_password, transport_cost, transport_DOW,".
						" transport_limit, gennsenn_choushuu_shubetu, huyou_ninnzuu, jyuuminnzei1, jyuuminnzei2,".
						" transport_mcost, ".
						" transport_dcost1_Sun, transport_dcost1_Mon, transport_dcost1_Tue, transport_dcost1_Wen, transport_dcost1_Thr, transport_dcost1_Fri, transport_dcost1_Sat,".
						" transport_dcost2_Sun, transport_dcost2_Mon, transport_dcost2_Tue, transport_dcost2_Wen, transport_dcost2_Thr, transport_dcost2_Fri, transport_dcost2_Sat,".
						" insert_timestamp, update_timestamp".
						" ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, now(), now())";

			$stmt = $db->prepare($sql);
			$stmt->bindParam(1, $no);
			$stmt->bindParam(2, $name);
			$stmt->bindParam(3, $furigana);
			$stmt->bindParam(4, $lesson_id);
			$stmt->bindParam(5, $del_flag);
			$stmt->bindParam(6, $mail_address);
			$stmt->bindParam(7, $password);
			$stmt->bindParam(8, $initial_password);
			$stmt->bindParam(9, $transport_cost);
			$stmt->bindParam(10,$transport_DOW);
			$stmt->bindParam(11,$transport_limit);
			$stmt->bindParam(12,$gennsenn_choushuu_shubetu);
			$stmt->bindParam(13,$huyou_ninnzuu);
			$stmt->bindParam(14,$jyuuminnzei1);
			$stmt->bindParam(15,$jyuuminnzei2);
			$stmt->bindParam(16,$transport_mcost);
			$stmt->bindParam(17,$transport_dcost1_Sun);
			$stmt->bindParam(18,$transport_dcost1_Mon);
			$stmt->bindParam(19,$transport_dcost1_Tue);
			$stmt->bindParam(20,$transport_dcost1_Wen);
			$stmt->bindParam(21,$transport_dcost1_Thr);
			$stmt->bindParam(22,$transport_dcost1_Fri);
			$stmt->bindParam(23,$transport_dcost1_Sat);
			$stmt->bindParam(24,$transport_dcost2_Sun);
			$stmt->bindParam(25,$transport_dcost2_Mon);
			$stmt->bindParam(26,$transport_dcost2_Tue);
			$stmt->bindParam(27,$transport_dcost2_Wen);
			$stmt->bindParam(28,$transport_dcost2_Thr);
			$stmt->bindParam(29,$transport_dcost2_Fri);
			$stmt->bindParam(30,$transport_dcost2_Sat);
			$no = sprintf("%06d", $last_no);
			$name = $teacher_array["name"];
			$furigana = $teacher_array["furigana"];
			$lesson_id = $teacher_array["lesson_id"];
			$del_flag = $teacher_array["del_flag"];
			$mail_address = $teacher_array["mail_address"];
			$password = openssl_encrypt($teacher_array["password"], 'AES-128-ECB', PASSWORD_KEY);
			$initial_password = $teacher_array["initial_password"];
			$transport_cost = $teacher_array["transport_cost"];
			$transport_DOW = $teacher_array["transport_DOW"];
			$transport_limit = $teacher_array["transport_limit"];
			$gennsenn_choushuu_shubetu = $teacher_array["gennsenn_choushuu_shubetu"];
			$huyou_ninnzuu = $teacher_array["huyou_ninnzuu"];
			$jyuuminnzei1 = $teacher_array["jyuuminnzei1"];
			$jyuuminnzei2 = $teacher_array["jyuuminnzei2"];
			$transport_mcost  = $teacher_array["transport_mcost"];
			$transport_dcost1_Sun = $teacher_array["transport_dcost1"][0];
			$transport_dcost1_Mon = $teacher_array["transport_dcost1"][1];
			$transport_dcost1_Tue = $teacher_array["transport_dcost1"][2];
			$transport_dcost1_Wen = $teacher_array["transport_dcost1"][3];
			$transport_dcost1_Thr = $teacher_array["transport_dcost1"][4];
			$transport_dcost1_Fri = $teacher_array["transport_dcost1"][5];
			$transport_dcost1_Sat = $teacher_array["transport_dcost1"][6];
			$transport_dcost2_Sun = $teacher_array["transport_dcost2"][0];
			$transport_dcost2_Mon = $teacher_array["transport_dcost2"][1];
			$transport_dcost2_Tue = $teacher_array["transport_dcost2"][2];
			$transport_dcost2_Wen = $teacher_array["transport_dcost2"][3];
			$transport_dcost2_Thr = $teacher_array["transport_dcost2"][4];
			$transport_dcost2_Fri = $teacher_array["transport_dcost2"][5];
			$transport_dcost2_Sat = $teacher_array["transport_dcost2"][6];
			$stmt->execute();
		}catch (PDOException $e){
			$errFlag = 1;
			throw $e;
		  //print('Error:'.$e->getMessage());
		}
	if ($errFlag == 0) {
		return $no;
	} else {
		return false;
	}
}

function get_teacher_cnt($db, $param_array=array(), $value_array=array(), $all_flag="0") {
	$cmd = 
		"SELECT
				count(*) as count
		 FROM tbl_teacher";
	if ($all_flag == "1") {
	// 削除でない場合（現生徒と前生徒を抽出）
	  $cmd .= " where (tbl_teacher.del_flag = '0' or tbl_teacher.del_flag = '2')";	
	  if(count($param_array) > 0){
	    $cmd .= " and " . join(" and ",$param_array);
	  }
	} else if ($all_flag == "2") {
	// 削除も含むすべての場合（前生徒と現生徒と削除済み生徒のすべてを抽出）
	  if(count($param_array) > 0){
	    $cmd .= " where " . join(" and ",$param_array);
	  }
	} else {
	// 現生徒のみの場合
	  $cmd .= " where tbl_teacher.del_flag = '0'";	
	  if(count($param_array) > 0){
	    $cmd .= " and " . join(" and ",$param_array);
	  }
	}
	$stmt = $db->prepare($cmd);
	$stmt->execute($value_array);
	$teacher_array = $stmt->fetchAll(PDO::FETCH_ASSOC);
	$teacher_cnt = $teacher_array[0]["count"];
	return $teacher_cnt;
}

function check_teacher($db, &$errArray, $teacher) {
	$errFlag = 0;
	mb_regex_encoding("UTF-8");
	if ($teacher["name"] == "") {
		$errFlag = 1;
 		array_push($errArray, "氏名を入力してください。");
	}
	if (mb_strlen($teacher["name"], "UTF-8") > 18) {
		$errFlag = 1;
 		array_push($errArray, "氏名を１８文字以内で入力してください。");
	}
	$param_array = array();
	$value_array = array();
	if ($teacher["no"]) {
		array_push($param_array, "tbl_teacher.no <> ?");	// 同一の名前を重複して登録しないように完全一致で検索
		array_push($value_array, $teacher["no"]);
	}
	array_push($param_array, "tbl_teacher.name = ?");	// 同一の名前を重複して登録しないように完全一致で検索
	array_push($value_array, $teacher["name"]);
	$teacher_cnt = get_teacher_cnt($db, $param_array, $value_array);
	if ($teacher_cnt > 0) {
		$errFlag = 1;
 		array_push($errArray, "入力された氏名はすでに登録されています。");
	}
	// ふりがな
	if ($teacher["furigana"] == "") {
		$errFlag = 1;
 		array_push($errArray, "ふりがなを入力してください。（英語名の場合は英語名のまま）");
	}
	if (mb_strlen($teacher["furigana"], "UTF-8") > 40) {
		$errFlag = 1;
 		array_push($errArray, "ふりがな（または英語名）を４０文字以内で入力してください。");
	}
	if ($errFlag == 0) {
		return true;
	} else {
		return false;
	}
}

function update_teacher(&$db, $teacher_array) {
	$errFlag = 0;
	try {
		$sql = "UPDATE tbl_teacher SET ".
					" name=?, furigana=?, lesson_id=?, del_flag=?, mail_address=?, worker_code=?, lesson_id2=?,".
					" transport_cost=?, transport_DOW=?,".
					" transport_limit=?, gennsenn_choushuu_shubetu=?, huyou_ninnzuu=?, jyuuminnzei1=?, jyuuminnzei2=?,".
					" bank_no=?, bank_branch_no=?, bank_acount_type=?, bank_acount_no=?, bank_acount_name=?,".
					" transport_mcost=?, ".
					" transport_dcost1_Sun=?, transport_dcost1_Mon=?, transport_dcost1_Tue=?, transport_dcost1_Wen=?, transport_dcost1_Thr=?, transport_dcost1_Fri=?, transport_dcost1_Sat=?,".
					" transport_dcost2_Sun=?, transport_dcost2_Mon=?, transport_dcost2_Tue=?, transport_dcost2_Wen=?, transport_dcost2_Thr=?, transport_dcost2_Fri=?, transport_dcost2_Sat=?,".
					" transport_zero=?, ".
					" update_timestamp=now()".
					" WHERE no=?";
		$stmt = $db->prepare($sql);
		$stmt->bindParam(1, $name);
		$stmt->bindParam(2, $furigana);
		$stmt->bindParam(3, $lesson_id);
		$stmt->bindParam(4, $del_flag);
		$stmt->bindParam(5, $mail_address);
		$stmt->bindParam(6, $worker_code);
		$stmt->bindParam(7, $lesson_id2);
		$stmt->bindParam(8, $transport_cost);
		$stmt->bindParam(9, $transport_DOW);
		$stmt->bindParam(10, $transport_limit);
		$stmt->bindParam(11, $gennsenn_choushuu_shubetu);
		$stmt->bindParam(12, $huyou_ninnzuu);
		$stmt->bindParam(13, $jyuuminnzei1);
		$stmt->bindParam(14, $jyuuminnzei2);
		$stmt->bindParam(15,$bank_no);
		$stmt->bindParam(16,$bank_branch_no);
		$stmt->bindParam(17,$bank_acount_type);
		$stmt->bindParam(18,$bank_acount_no);
		$stmt->bindParam(19,$bank_acount_name);
		$stmt->bindParam(20,$transport_mcost);
		$stmt->bindParam(21,$transport_dcost1_Sun);
		$stmt->bindParam(22,$transport_dcost1_Mon);
		$stmt->bindParam(23,$transport_dcost1_Tue);
		$stmt->bindParam(24,$transport_dcost1_Wen);
		$stmt->bindParam(25,$transport_dcost1_Thr);
		$stmt->bindParam(26,$transport_dcost1_Fri);
		$stmt->bindParam(27,$transport_dcost1_Sat);
		$stmt->bindParam(28,$transport_dcost2_Sun);
		$stmt->bindParam(29,$transport_dcost2_Mon);
		$stmt->bindParam(30,$transport_dcost2_Tue);
		$stmt->bindParam(31,$transport_dcost2_Wen);
		$stmt->bindParam(32,$transport_dcost2_Thr);
		$stmt->bindParam(33,$transport_dcost2_Fri);
		$stmt->bindParam(34,$transport_dcost2_Sat);
		$stmt->bindParam(35,$transport_zero);
		$stmt->bindParam(36, $no);
		$no = $teacher_array["no"];
		$name = $teacher_array["name"];
		$furigana = $teacher_array["furigana"];
		$lesson_id = $teacher_array["lesson_id"];
		$del_flag = $teacher_array["del_flag"];
		$worker_code = $teacher_array["worker_code"];
		$mail_address = $teacher_array["mail_address"];
		$lesson_id2 = $teacher_array["lesson_id2"];
		$transport_cost = $teacher_array["transport_cost"];
		$transport_DOW = $teacher_array["transport_DOW"];
		$transport_limit = $teacher_array["transport_limit"];
		$gennsenn_choushuu_shubetu = $teacher_array["gennsenn_choushuu_shubetu"];
		$huyou_ninnzuu = $teacher_array["huyou_ninnzuu"];
		$jyuuminnzei1 = $teacher_array["jyuuminnzei1"];
		$jyuuminnzei2 = $teacher_array["jyuuminnzei2"];
		$bank_no          = $teacher_array["bank_no"];
		$bank_branch_no   = $teacher_array["bank_branch_no"];
		$bank_acount_type = $teacher_array["bank_acount_type"];
		$bank_acount_no   = $teacher_array["bank_acount_no"];
		$bank_acount_name = $teacher_array["bank_acount_name"];
		$transport_mcost  = $teacher_array["transport_mcost"];
		$transport_dcost1_Sun = $teacher_array["transport_dcost1"][0];
		$transport_dcost1_Mon = $teacher_array["transport_dcost1"][1];
		$transport_dcost1_Tue = $teacher_array["transport_dcost1"][2];
		$transport_dcost1_Wen = $teacher_array["transport_dcost1"][3];
		$transport_dcost1_Thr = $teacher_array["transport_dcost1"][4];
		$transport_dcost1_Fri = $teacher_array["transport_dcost1"][5];
		$transport_dcost1_Sat = $teacher_array["transport_dcost1"][6];
		$transport_dcost2_Sun = $teacher_array["transport_dcost2"][0];
		$transport_dcost2_Mon = $teacher_array["transport_dcost2"][1];
		$transport_dcost2_Tue = $teacher_array["transport_dcost2"][2];
		$transport_dcost2_Wen = $teacher_array["transport_dcost2"][3];
		$transport_dcost2_Thr = $teacher_array["transport_dcost2"][4];
		$transport_dcost2_Fri = $teacher_array["transport_dcost2"][5];
		$transport_dcost2_Sat = $teacher_array["transport_dcost2"][6];
		$transport_zero  = $teacher_array["transport_zero"];
		$stmt->execute();
	}catch (PDOException $e){
		$errFlag = 1;
	  //print('Error:'.$e->getMessage());
		throw $e;
	}
	if ($errFlag == 0) {
		return true;
	} else {
		return false;
	}
}

function delete_teacher(&$db, $teacher_no) {
		$errFlag = 0;
		try{
			// 20160118 メンバー情報に削除フラグを立てて削除するように変更
			$stmt = $db->prepare("UPDATE tbl_teacher SET del_flag = '1' WHERE tbl_teacher.no = ?");
			$stmt->execute(array($teacher_no));
		}catch (PDOException $e){
			$errFlag = 1;
			throw $e;
		  //print('Error:'.$e->getMessage());
		}
	if ($errFlag == 0) {
		return true;
	} else {
		return false;
	}
}


function insert_furikomisha(&$db, $furikomisha_array) {
	$errFlag = 0;
	try {
			$stmt = $db->prepare("SELECT MAX(no) AS max_no FROM tbl_furikomisha");
			$stmt->execute();
			$last_no = $stmt->fetchAll(PDO::FETCH_ASSOC)[0]['max_no']+1;
			$sql = "INSERT INTO tbl_furikomisha (no, member_no, member_name, furikomisha_name, del_flag, insert_timestamp, update_timestamp".
						" ) VALUES (?, ?, ?, ?, ?, now(), now())";

			$stmt = $db->prepare($sql);
			$stmt->bindParam(1, $no);
			$stmt->bindParam(2, $member_no);
			$stmt->bindParam(3, $member_name);
			$stmt->bindParam(4, $furikomisha_name);
			$stmt->bindParam(5, $del_flag);
			$no = sprintf("%06d", $last_no);
			$member_no = $furikomisha_array["member_no"];
			$member_name = $furikomisha_array["member_name"];
			$furikomisha_name = $furikomisha_array["furikomisha_name"];
			$del_flag = $furikomisha_array["del_flag"];
			$stmt->execute();
		}catch (PDOException $e){
			$errFlag = 1;
			throw $e;
		  //print('Error:'.$e->getMessage());
		}
	if ($errFlag == 0) {
		return $no;
	} else {
		return false;
	}
}

function get_furikomisha_cnt($db, $param_array=array(), $value_array=array(), $all_flag="0") {
	$cmd = 
		"SELECT
				count(*) as count
		 FROM tbl_furikomisha";
	if ($all_flag == "1") {
	// 削除でない場合（現振込者と前振込者を抽出）
	  $cmd .= " where (tbl_furikomisha.del_flag = '0' or tbl_furikomisha.del_flag = '2')";	
	  if(count($param_array) > 0){
	    $cmd .= " and " . join(" and ",$param_array);
	  }
	} else if ($all_flag == "2") {
	// 削除も含むすべての場合（前振込者と現振込者と削除済み振込者のすべてを抽出）
	  if(count($param_array) > 0){
	    $cmd .= " where " . join(" and ",$param_array);
	  }
	} else {
	// 現振込者のみの場合
	  $cmd .= " where tbl_furikomisha.del_flag = '0'";	
	  if(count($param_array) > 0){
	    $cmd .= " and " . join(" and ",$param_array);
	  }
	}
	$stmt = $db->prepare($cmd);
	$stmt->execute($value_array);
	$furikomisha_array = $stmt->fetchAll(PDO::FETCH_ASSOC);
	$furikomisha_cnt = $furikomisha_array[0]["count"];
	return $furikomisha_cnt;
}

function check_furikomisha($db, &$errArray, $furikomisha) {
	$errFlag = 0;
	mb_regex_encoding("UTF-8");
	if ($furikomisha["furikomisha_name"] == "") {
		$errFlag = 1;
 		array_push($errArray, "振込者名を入力してください。");
	}
	if (mb_strlen($furikomisha["furikomisha_name"], "UTF-8") > 18) {
		$errFlag = 1;
 		array_push($errArray, "振込者名を１８文字以内で入力してください。");
	}
	if ($furikomisha["member_name"] == "") {
		$errFlag = 1;
 		array_push($errArray, "振込生徒名を指定してください。");
	}
	$param_array = array();
	$value_array = array();
	if ($furikomisha["no"]) {
		array_push($param_array, "tbl_furikomisha.no <> ?");	// 同一の名前を重複して登録しないように完全一致で検索
		array_push($value_array, $furikomisha["no"]);
	}
	array_push($param_array, "tbl_furikomisha.furikomisha_name = ?");	// 同一の名前を重複して登録しないように完全一致で検索
	array_push($value_array, $furikomisha["furikomisha_name"]);
	$furikomisha_cnt = get_furikomisha_cnt($db, $param_array, $value_array);
	if ($furikomisha_cnt > 0) {
		$errFlag = 1;
 		array_push($errArray, "入力された振込者名はすでに登録されています。");
	}
	
	$array = explode(':',$furikomisha["member_no"]); $array1 = array();
	foreach ( $array as $item ) {
		try{
			$stmt = $db->prepare("SELECT member_no FROM tbl_furikomisha WHERE member_no like '%".$item."%'");
			$stmt->execute();
			$furikomisha_array = $stmt->fetchAll(PDO::FETCH_ASSOC);
		}catch (PDOException $e){
			$errFlag = 1;
			array_push($errArray, $e->getMessage()); break;
		}
		foreach ($furikomisha_array as $key => $item) { 
			$array1 = array_unique ( array_merge ( explode(':',$item["member_no"]), $array1 ));
		}
	}
	$array1 = array_diff( $array1, $array );
	foreach ($array1 as $item) {
		try{
			$stmt = $db->prepare("SELECT name FROM tbl_member WHERE no='".$item."'");
			$stmt->execute();
			$array = $stmt->fetchAll(PDO::FETCH_ASSOC);
		}catch (PDOException $e){
			$errFlag = 1;
			array_push($errArray, $e->getMessage()); break;
		}
		array_push($errArray, "生徒:".$array[0]['name']."を追加指定してください。");
	}
	
	if ($errFlag == 0) {
		return true;
	} else {
		return false;
	}
}

function update_furikomisha(&$db, $furikomisha_array) {
	$errFlag = 0;
	try {
			$sql = "UPDATE tbl_furikomisha SET ".
						" member_no=?, member_name=?, furikomisha_name=?, del_flag=?, update_timestamp=now() ".
						" WHERE no=?";
			$stmt = $db->prepare($sql);
			$stmt->bindParam(1, $member_no);
			$stmt->bindParam(2, $member_name);
			$stmt->bindParam(3, $furikomisha_name);
			$stmt->bindParam(4, $del_flag);
			$stmt->bindParam(5, $no);
			$member_no = $furikomisha_array["member_no"];
			$member_name = $furikomisha_array["member_name"];
			$furikomisha_name = $furikomisha_array["furikomisha_name"];
			$del_flag = $furikomisha_array["del_flag"];
			$no = $furikomisha_array["no"];
			$stmt->execute();
	}catch (PDOException $e){
		$errFlag = 1;
	  //print('Error:'.$e->getMessage());
		throw $e;
	}
	if ($errFlag == 0) {
		return true;
	} else {
		return false;
	}
}

function delete_furikomisha(&$db, $furikomisha_no) {
		$errFlag = 0;
		try{
			$stmt = $db->prepare("DELETE FROM tbl_furikomisha WHERE tbl_furikomisha.no = ?");
			$stmt->execute(array($furikomisha_no));
		}catch (PDOException $e){
			$errFlag = 1;
			throw $e;
		  //print('Error:'.$e->getMessage());
		}
	if ($errFlag == 0) {
		return true;
	} else {
		return false;
	}
}

function get_lesson_array(&$db, $teacher_id, $year, $month) {
$group_lesson_list = array();
try{
		$sql = "SELECT
    lesson_id,
    subject_id,
    course_id,
    teacher_id,
    diff_hours,
    FROM_UNIXTIME(start_timestamp, '%Y') as year,
    FROM_UNIXTIME(start_timestamp, '%m')+0 as month,
    FROM_UNIXTIME(start_timestamp, '%d')+0 as day,
    start_timestamp,
    end_timestamp,
    sum(fees) as lesson_fees,
    count(student_id) as count,
    min(student_id) as student_id_min,
    (end_timestamp-start_timestamp)/(60*60) as lesson_hours,
	min(absent_flag) as absent_flag_min
  FROM tbl_statement_detail
WHERE teacher_id = ?
AND FROM_UNIXTIME(start_timestamp, '%Y') = ?
AND FROM_UNIXTIME(start_timestamp, '%m')+0 = ?
GROUP BY teacher_id, start_timestamp, end_timestamp, lesson_id, subject_id, course_id";
//echo $sql;
		$stmt = $db->prepare($sql);
		$stmt->execute(array($teacher_id, $year, $month));
		$group_lesson_list = $stmt->fetchAll(PDO::FETCH_BOTH);
		//var_dump($value_array);
} catch (Exception $e) {
	// 処理を中断するほどの致命的なエラー
	throw $e;
	return false;
}
return $group_lesson_list;
}

function get_working_time_array(&$db, $teacher_id, $year, $month) {
	$sql = "SELECT * FROM tbl_declare_working_time ".
		"where teacher_id=$teacher_id and year=$year and month=$month ".
		"order by tbl_declare_working_time.day, tbl_declare_working_time.stime";
	$stmt = $db->prepare($sql);
	$stmt->execute();
	return $stmt->fetchAll(PDO::FETCH_BOTH);
}

function check_declare_working_time(&$db, $teacher_id, $year, $month, $lesson_array, $working_time_array) {
	$sql = 
			"SELECT * FROM tbl_statement, tbl_statement_detail ".
			"where tbl_statement.statement_no = tbl_statement_detail.statement_no ".
	    "and tbl_statement.seikyu_year=$year and tbl_statement.seikyu_month=$month ".
	    "and tbl_statement_detail.teacher_id=$teacher_id ".
			"order by tbl_statement.seikyu_year, tbl_statement.seikyu_month";
	$stmt = $db->prepare($sql);
	$stmt->execute();
	$statement_list = $stmt->fetchAll(PDO::FETCH_BOTH);
		
	foreach ((array) $statement_list as $key => $value) {
	    $sort1[$key] = $value['date'];
	    $sort2[$key] = $value['time'];
	}
	array_multisort(
					$sort1, SORT_ASC, SORT_NATURAL, 
					$sort2, SORT_ASC, SORT_NATURAL, 
					$statement_list);

	foreach ($statement_list as $statement) {
		if (($statement["date"] == $lastdate) && ($statement["time"] == $lasttime)) { continue; }
		$statement_list1[] = $statement;
		$lastdate=$statement["date"]; $lasttime=$statement["time"];
	}
	$i=0; $matchFlag=true;
	foreach ($statement_list1 as $statement) {
		$working_data = $working_time_array[$i++];
		$str01 = "0.00";
		foreach ($lesson_array as $lesson_data) {
			if (($lesson_data["start_timestamp"] == $statement["start_timestamp"]) && ($lesson_data["end_timestamp"] == $statement["end_timestamp"])) {
				if (strpos($statement["comment"],"振替")!==FALSE) {
					$str01 = sprintf( "%4.2f", ($lesson_data["end_timestamp"] - $lesson_data["start_timestamp"]) / (60*60) );
				} else { $str01 = $statement["diff_hours"]; }
				break;
			}
		}
		if ($str01 == "0.00") { continue; }
//ob_start();var_dump($statement);$errArray[]=ob_get_contents();ob_end_clean();
//ob_start();var_dump($working_data);$errArray[]=ob_get_contents();ob_end_clean();
		if ($working_data['time'] != $str01*60) { $matchFlag=false; break; }
		preg_match( '/月(\d+)日/', $statement['date'], $matches );
		if ($working_data['day'] != $matches[1]) { $matchFlag=false; break; }
		preg_match( '/(\d+:\d+) ～ /', $statement['time'], $matches );
		if ($working_data['stime'] != $matches[1]) { $matchFlag=false; break; }
//			if ($lesson_list[$working_data['lesson_id']]   != $statement["lesson_name"]) { $matchFlag=false; break; }
//			if ($subject_list[$working_data['subject_id']] != $statement["subject_name"]) { $matchFlag=false; break; }
//			if ($course_list[$working_data['course_id']]["course_name"] != $statement["course_name"]) { $matchFlag=false; break; }
	}
	if ($working_time_array[$i]) { $matchFlag=false; }
	return $matchFlag;
}

/* old season class course
function isSeasonClassExercise( $event ) {
	if (($event["season_course_id"] == 1 || $event["season_course_id"] == 2) && 
		$event["subject_id"]	== "" && 
		$event["teacher_id"]	== "")	return true;
	return false;
}
*/
function isSeasonClassExercise( $event ) {
	if ($event["subject_id"]	== "" && 
			$event["teacher_id"]	== "")	return true;
	return false;
}

function errMsgfileName() {
	return './errmsg-'.session_id();
}

function errMsgFileLog( $errmsg ) {
	$errmsgfile = errMsgfileName();
	file_put_contents( $errmsgfile, $errmsg, FILE_APPEND );
}

function errMsgFileCheck( &$errArray=false ) {
	$errmsgfile = errMsgfileName();
	if (file_exists( $errmsgfile )) {
		$errmsg = file_get_contents( $errmsgfile );
		$errmsg_lines = array_unique( explode( "\n",$errmsg ) );
		if ($errArray !== false) { foreach ($errmsg_lines as $line) { array_push( $errArray,$line ); } }
		unlink( $errmsgfile );
	}
}

errMsgFileCheck();


// 事務員一覧を取得
// 改ページ対応なし
// $param_array : kind = ?
// $order_array : name
function get_staff_list($db, $param_array=array(), $value_array=array(), $order_array=array(), $all_flag=0) {
	$cmd = 
		"SELECT *
		 FROM tbl_staff";
	switch($all_flag) {
	case 0:	$cmd .= " where tbl_staff.del_flag = '0'"; break;
	case 1:	$cmd .= " where tbl_staff.del_flag = '0' or tbl_staff.del_flag = '2'"; break;
	case 2:	$cmd .= " where tbl_staff.del_flag >= '0'"; break;
	case 3:	$cmd .= " where tbl_staff.del_flag = '2'"; break;
	}
  if(count($param_array) > 0){
    $cmd .= " and " . join(" and ",$param_array);
  }
  if(count($order_array) > 0){
    $cmd .= "	order by " . join(" , ",$order_array);
  }
	else {
		$cmd .= "	order by tbl_staff.furigana";
	}
	$stmt = $db->prepare($cmd);
	$stmt->execute($value_array);
	$staff_array = $stmt->fetchAll(PDO::FETCH_ASSOC);
	$staff_list = array();
	foreach ( $staff_array as $row ) {
		$staff_list[$row["no"]] = $row;
	}
	return $staff_list;
}

function insert_staff(&$db, $staff_array) {
	$errFlag = 0;
	try {
			$last_no = get_staff_cnt($db, array(), array(), "2")+1;
			$sql = "INSERT INTO tbl_staff (no, name, furigana, del_flag, mail_address, password, initial_password,".
						" transport_cost, transport_DOW,".
						" transport_limit, gennsenn_choushuu_shubetu, huyou_ninnzuu, jyuuminnzei1, jyuuminnzei2,".
						" insert_timestamp, update_timestamp".
						" ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, now(), now())";

			$stmt = $db->prepare($sql);
			$stmt->bindParam(1, $no);
			$stmt->bindParam(2, $name);
			$stmt->bindParam(3, $furigana);
			$stmt->bindParam(4, $del_flag);
			$stmt->bindParam(5, $mail_address);
			$stmt->bindParam(6, $password);
			$stmt->bindParam(7, $initial_password);
			$stmt->bindParam(8, $transport_cost);
			$stmt->bindParam(9, $transport_DOW);
			$stmt->bindParam(10, $transport_limit);
			$stmt->bindParam(11, $gennsenn_choushuu_shubetu);
			$stmt->bindParam(12, $huyou_ninnzuu);
			$stmt->bindParam(13, $jyuuminnzei1);
			$stmt->bindParam(14, $jyuuminnzei2);
			$no = sprintf("%06d", $last_no);
			$name = $staff_array["name"];
			$furigana = $staff_array["furigana"];
			$del_flag = $staff_array["del_flag"];
			$mail_address = $staff_array["mail_address"];
			$password = openssl_encrypt($staff_array["password"], 'AES-128-ECB', PASSWORD_KEY);
			$initial_password = $staff_array["initial_password"];
			$transport_cost = $staff_array['transport_cost'];
			$transport_DOW = $staff_array['transport_DOW'];
			$transport_limit = $staff_array['transport_limit'];
			$gennsenn_choushuu_shubetu = $staff_array['gennsenn_choushuu_shubetu'];
			$huyou_ninnzuu = $staff_array['huyou_ninnzuu'];
			$jyuuminnzei1 = $staff_array['jyuuminnzei1'];
			$jyuuminnzei2 = $staff_array['jyuuminnzei2'];
			$stmt->execute();
		}catch (PDOException $e){
			$errFlag = 1;
			throw $e;
		  //print('Error:'.$e->getMessage());
		}
	if ($errFlag == 0) {
		return $no;
	} else {
		return false;
	}
}

function get_staff_cnt($db, $param_array=array(), $value_array=array(), $all_flag="0") {
	$cmd = 
		"SELECT
				count(*) as count
		 FROM tbl_staff";
	if ($all_flag == "1") {
	// 削除でない場合（現生徒と前生徒を抽出）
	  $cmd .= " where (tbl_staff.del_flag = '0' or tbl_staff.del_flag = '2')";	
	  if(count($param_array) > 0){
	    $cmd .= " and " . join(" and ",$param_array);
	  }
	} else if ($all_flag == "2") {
	// 削除も含むすべての場合（前生徒と現生徒と削除済み生徒のすべてを抽出）
	  if(count($param_array) > 0){
	    $cmd .= " where " . join(" and ",$param_array);
	  }
	} else {
	// 現生徒のみの場合
	  $cmd .= " where tbl_staff.del_flag = '0'";	
	  if(count($param_array) > 0){
	    $cmd .= " and " . join(" and ",$param_array);
	  }
	}
	$stmt = $db->prepare($cmd);
	$stmt->execute($value_array);
	$staff_array = $stmt->fetchAll(PDO::FETCH_ASSOC);
	$staff_cnt = $staff_array[0]["count"];
	return $staff_cnt;
}

function check_staff($db, &$errArray, $staff) {
	$errFlag = 0;
	mb_regex_encoding("UTF-8");
	if ($staff["name"] == "") {
		$errFlag = 1;
 		array_push($errArray, "氏名を入力してください。");
	}
	if (mb_strlen($staff["name"], "UTF-8") > 18) {
		$errFlag = 1;
 		array_push($errArray, "氏名を１８文字以内で入力してください。");
	}
	$param_array = array();
	$value_array = array();
	if ($staff["no"]) {
		array_push($param_array, "tbl_staff.no <> ?");	// 同一の名前を重複して登録しないように完全一致で検索
		array_push($value_array, $staff["no"]);
	}
	array_push($param_array, "tbl_staff.name = ?");	// 同一の名前を重複して登録しないように完全一致で検索
	array_push($value_array, $staff["name"]);
	$staff_cnt = get_staff_cnt($db, $param_array, $value_array);
	if ($staff_cnt > 0) {
		$errFlag = 1;
 		array_push($errArray, "入力された氏名はすでに登録されています。");
	}
	// ふりがな
	if ($staff["furigana"] == "") {
		$errFlag = 1;
 		array_push($errArray, "ふりがなを入力してください。（英語名の場合は英語名のまま）");
	}
	if (mb_strlen($staff["furigana"], "UTF-8") > 40) {
		$errFlag = 1;
 		array_push($errArray, "ふりがな（または英語名）を４０文字以内で入力してください。");
	}
	if ($errFlag == 0) {
		return true;
	} else {
		return false;
	}
}

function update_staff(&$db, $staff_array) {
	$errFlag = 0;
	try {
		if ($staff_array["password"]) {
			$sql = "UPDATE tbl_staff SET ".
						" name=?, furigana=?, del_flag=?, mail_address=?, password=?, worker_code=?, transport_cost=?, transport_DOW=?,".
						" transport_limit=?, gennsenn_choushuu_shubetu=?, huyou_ninnzuu=?, jyuuminnzei1=?, jyuuminnzei2=?,".
						" bank_no=?, bank_branch_no=?, bank_acount_type=?, bank_acount_no=?, bank_acount_name=?,".
						" transport_zero=?, ". 
						" update_timestamp=now()".
						" WHERE no=?";
			$stmt = $db->prepare($sql);
			$stmt->bindParam(1, $name);
			$stmt->bindParam(2, $furigana);
			$stmt->bindParam(3, $del_flag);
			$stmt->bindParam(4, $mail_address);
			$stmt->bindParam(5, $password);
			$stmt->bindParam(6, $worker_code);
			$stmt->bindParam(7, $transport_cost);
			$stmt->bindParam(8, $transport_DOW);
			$stmt->bindParam(9, $transport_limit);
			$stmt->bindParam(10, $gennsenn_choushuu_shubetu);
			$stmt->bindParam(11, $huyou_ninnzuu);
			$stmt->bindParam(12, $jyuuminnzei1);
			$stmt->bindParam(13, $jyuuminnzei2);
			$stmt->bindParam(14,$bank_no);
			$stmt->bindParam(15,$bank_branch_no);
			$stmt->bindParam(16,$bank_acount_type);
			$stmt->bindParam(17,$bank_acount_no);
			$stmt->bindParam(18,$bank_acount_name);
			$stmt->bindParam(19,$transport_zero);
			$stmt->bindParam(20, $no);
			$password = openssl_encrypt($staff_array["password"], 'AES-128-ECB', PASSWORD_KEY);
		} else {
			$sql = "UPDATE tbl_staff SET ".
						" name=?, furigana=?, del_flag=?, mail_address=?, worker_code=?, transport_cost=?, transport_DOW=?,".
						" transport_limit=?, gennsenn_choushuu_shubetu=?, huyou_ninnzuu=?, jyuuminnzei1=?, jyuuminnzei2=?,".
						" bank_no=?, bank_branch_no=?, bank_acount_type=?, bank_acount_no=?, bank_acount_name=?,".
						" transport_zero=?, ". 
						" update_timestamp=now()".
						" WHERE no=?";
			$stmt = $db->prepare($sql);
			$stmt->bindParam(1, $name);
			$stmt->bindParam(2, $furigana);
			$stmt->bindParam(3, $del_flag);
			$stmt->bindParam(4, $mail_address);
			$stmt->bindParam(5, $worker_code);
			$stmt->bindParam(6, $transport_cost);
			$stmt->bindParam(7, $transport_DOW);
			$stmt->bindParam(8, $transport_limit);
			$stmt->bindParam(9, $gennsenn_choushuu_shubetu);
			$stmt->bindParam(10, $huyou_ninnzuu);
			$stmt->bindParam(11, $jyuuminnzei1);
			$stmt->bindParam(12, $jyuuminnzei2);
			$stmt->bindParam(13,$bank_no);
			$stmt->bindParam(14,$bank_branch_no);
			$stmt->bindParam(15,$bank_acount_type);
			$stmt->bindParam(16,$bank_acount_no);
			$stmt->bindParam(17,$bank_acount_name);
			$stmt->bindParam(18,$transport_zero);
			$stmt->bindParam(19, $no);
		}
		$no = $staff_array["no"];
		$name = $staff_array["name"];
		$furigana = $staff_array["furigana"];
		$del_flag = $staff_array["del_flag"];
		$worker_code = $staff_array["worker_code"];
		$mail_address = $staff_array["mail_address"];
		$transport_cost = $staff_array["transport_cost"];
		$transport_DOW = $staff_array["transport_DOW"];
		$transport_limit = $staff_array["transport_limit"];
		$transport_zero = $staff_array["transport_zero"];
		$gennsenn_choushuu_shubetu = $staff_array["gennsenn_choushuu_shubetu"];
		$huyou_ninnzuu = $staff_array["huyou_ninnzuu"];
		$jyuuminnzei1 = $staff_array["jyuuminnzei1"];
		$jyuuminnzei2 = $staff_array["jyuuminnzei2"];
		$bank_no          = $staff_array["bank_no"];
		$bank_branch_no   = $staff_array["bank_branch_no"];
		$bank_acount_type = $staff_array["bank_acount_type"];
		$bank_acount_no   = $staff_array["bank_acount_no"];
		$bank_acount_name = $staff_array["bank_acount_name"];
		$stmt->execute();
	}catch (PDOException $e){
		$errFlag = 1;
	  //print('Error:'.$e->getMessage());
		throw $e;
	}
	if ($errFlag == 0) {
		return true;
	} else {
		return false;
	}
}

function delete_staff(&$db, $staff_no) {
		$errFlag = 0;
		try{
			// 20160118 メンバー情報に削除フラグを立てて削除するように変更
			$stmt = $db->prepare("UPDATE tbl_staff SET del_flag = '1' WHERE tbl_staff.no = ?");
			$stmt->execute(array($staff_no));
		}catch (PDOException $e){
			$errFlag = 1;
			throw $e;
		  //print('Error:'.$e->getMessage());
		}
	if ($errFlag == 0) {
		return true;
	} else {
		return false;
	}
}

function get_tatekae_list($db, $param_array=array(), $value_array=array(), $order_array=array()) {
	$sql = 
		"SELECT * FROM tbl_tatekae";
  if(count($param_array) > 0){
    $sql .= " where " . join(" and ",$param_array);
  }
  if(count($order_array) > 0){
    $sql .= "	order by " . join(" , ",$order_array);
  }
	$stmt = $db->prepare($sql);
	$stmt->execute($value_array);
	$tatekae_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

	return $tatekae_list;
}

function get_payadj_list($db, $param_array=array(), $value_array=array(), $order_array=array()) {
	$sql = 
		"SELECT * FROM tbl_payadj";
  if(count($param_array) > 0){
    $sql .= " where " . join(" and ",$param_array);
  }
  if(count($order_array) > 0){
    $sql .= "	order by " . join(" , ",$order_array);
  }
	$stmt = $db->prepare($sql);
	$stmt->execute($value_array);
	$payadj_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

	return $payadj_list;
}

function get_student_join_month($db, $member_no) {
	// 徳田さんは旧生徒扱い
	if ($member_no == '001088') {
		return '2018/08';
	}
	try {
		$sql = "SELECT FROM_UNIXTIME(MIN(start_timestamp),'%Y/%m') FROM tbl_statement_detail WHERE student_id = \"$member_no\" ";
		$stmt = $db->query($sql);
		$rslt = $stmt->fetch(PDO::FETCH_NUM);
		$month1 = $rslt[0];
		$sql = "SELECT MIN(date) FROM tbl_season_class_entry_date WHERE member_id = \"$member_no\" ";
		$stmt = $db->query($sql);
		$rslt = $stmt->fetch(PDO::FETCH_NUM);
		$month2 = substr($rslt[0],0,7);
		
		if ($month1) {
			if ($month2) {
				if ($month1 < $month2) return $month1;
			} else {
				return $month1;
			}
		}
		return $month2;
	} catch (PDOException $e){
		//throw $e;
		print('Error:'.$e->getMessage());
	}
}

function get_season_fee_table($db) {
	global $lesson_fee_table, $exercise_fee_table;
	try{
		$stmt = $db->query("SELECT * FROM tbl_season_class_lesson_fee");
		$rslt = $stmt->fetchAll(PDO::FETCH_ASSOC);
		foreach ($rslt as $item) {
			$lesson_fee_table[$item['no']-1][$item['grade']][$item['course']] = $item['fee'];
		}
		$stmt = $db->query("SELECT * FROM tbl_season_class_exercise_fee");
		$rslt = $stmt->fetchAll(PDO::FETCH_ASSOC);
		foreach ($rslt as $item) {
			$exercise_fee_table[$item['no']-1][$item['course']][$item['type']] = $item['fee'];
		}
	} catch (PDOException $e){
		//throw $e;
		print('Error:'.$e->getMessage());
	}
}

function get_season_fee_type($db, $member_no) {
		if (get_student_join_month($db, $member_no)<'2018/09')
			return 0;
		else
			return 1;
}

function get_grade($db, $member, $year, $month, $april_start) {
	$birth_year  = $member['birth_year'];
	$birth_month = $member['birth_month'];
	$birth_day   = $member['birth_day'];
	$grade = $member['grade'];
	if ($grade) {
		$grade -= date('Y') - $year;
		if ($april_start) {
			if ($month<4) $grade--;
		}
	} else if ($birth_year && $birth_month && $birth_day) {
		$grade = $year-5-$birth_year;
		if ($birth_month<4) { $grade++; }
		if ($birth_month==4 && $birth_day==1) { $grade++; }
		if ($april_start) {
			if ($month<4) { $grade--; }
		}
		$grade += $member["grade_adj"];
	} else {
		return null;
	}
	if ($grade<1) { $grade=1; }
	if ($grade>14) { $grade=14; }
	return $grade;
}

function get_lesson_count($db, $member_no, $year, $month, $lesson_id, $course_id) {
	$stmt = $db->query("SELECT DAYOFWEEK(FROM_UNIXTIME(event_start_timestamp)) FROM tbl_event WHERE member_no = '$member_no'".
		" AND event_year = '$year' AND event_month = '$month'".
		" AND lesson_id = '$lesson_id' AND course_id = '$course_id'".
		" AND cal_evt_summary NOT LIKE '%(仮)%' AND cal_evt_summary NOT LIKE '%（仮）%'".
		" AND recurringEvent = 1"
		);
	$rslt = $stmt->fetchAll(PDO::FETCH_NUM);
	if (!$rslt) return null;
	if (count($rslt)<3) return null;
	$rslt = array_unique(array_column($rslt,0));
	return count($rslt);
}

function get_lesson_length($db, $member_no, $year, $month, $lesson_id, $course_id) {
	$stmt = $db->query("SELECT SUM(event_diff_hours) FROM tbl_event WHERE member_no = '$member_no'".
		" AND event_year = '$year' AND event_month = '$month'".
		" AND lesson_id = '$lesson_id' AND course_id = '$course_id'".
		" AND recurringEvent = 1".
		" AND event_diff_hours != 0".
		" AND cal_evt_summary NOT LIKE '%(仮)%' AND cal_evt_summary NOT LIKE '%（仮）%'".
		" GROUP BY event_day"
		);
	$rslt = $stmt->fetchAll(PDO::FETCH_NUM);
	if (!$rslt) return null;
	$rslt = array_unique(array_column($rslt,0));
	if (count($rslt)>1) return null;
	return floor($rslt[0]*60);
}

function get_default_fee($db, $member, $year, $month, $lesson_id, $course_id) {
	try {
		$member_no = $member['no'];
		$lesson_count = get_lesson_count($db, $member_no, $year, $month, $lesson_id, $course_id);
		if (!$lesson_count) return "授業回数不確定のため授業料自動登録できません。";
		$lesson_length = get_lesson_length($db, $member_no, $year, $month, $lesson_id, $course_id);
		if (!$lesson_length) return "授業時間不確定のため授業料自動登録できません。";
		$ent_month = get_student_join_month($db, $member_no);
		$grade = get_grade($db, $member, $year, $month, ($ent_month>='2018/09'));

//echo "$member_no, $lesson_id, $course_id, $ent_month, $grade, $lesson_count, $lesson_length.";

		$stmt = $db->query("SELECT lesson_fee FROM tbl_lesson_fee".
			"	WHERE lesson_id = '$lesson_id' AND course_id = '$course_id'".
			" AND lesson_grade <= '$grade'".
			" AND lesson_count = '$lesson_count'".
			" AND lesson_length = '$lesson_length'".
			" AND (start_month = '' OR start_month<='$ent_month')".
			" AND (end_month = '' OR end_month>='$ent_month')".
			" ORDER BY lesson_grade DESC"
			);
		$rslt = $stmt->fetchAll(PDO::FETCH_NUM);
		
		$fee = $rslt[0][0];
		
		if ($fee && $lesson_id==1 && $member['jyukensei']) {
			if ($ent_month>='2018/09') {
				// 小学生受験生　+1000円
				if ($grade>1 && $grade<8) $fee += 1000;
			} else {
				// 小４/小５受験生 +500円　小６受験生　+1000円
				switch ($grade) {
				case 5:
				case 6: $fee += 500; break;
				case 7: $fee +=1000; break;
				}
			}
		}
		
		return $fee;
		
	} catch (Exception $e) {
		return $e->getMessage();
	}
}

?>
