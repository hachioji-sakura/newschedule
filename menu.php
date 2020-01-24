<?php
require_once("./const/const.inc");
require_once("./func.inc");
require_once("./const/login_func.inc");
$result = check_user($db, "1");

$year = date("Y");
$month = date("n");
$year1 = $year;
$month1 = $month;

$month = $month-1;
if ($month<1) { $year--; $month+=12; }

$year2 = $year;
$month2 = $month;
if ($month2<1) { $year2--; $month2+=12; }
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta name="robots" content="noindex,nofollow">
<link rel="stylesheet" type="text/css" href="./script/style.css">
<script type="text/javascript" src="./script/calender.js"></script>
<script type="text/javascript">
function updateAlert() {
	if (parseInt(document.form1.y.value)*100+parseInt(document.form1.m.value) < <?= $year2*100+$month2?>) { alert("先々月以前のカレンダーデータは取り込めません。"); return false; }
	return window.confirm( document.form1.y.value+"年"+document.form1.m.value+"月分のカレンダーデータを上書きします。よろしいですか？" );
}
function updateAlert1() {
	if (parseInt(document.form2.y.value)*100+parseInt(document.form2.m.value) < <?= $year2*100+$month2?>) { alert("先々月以前の請求データは更新できません。"); return false; }
	return window.confirm( document.form2.y.value+"年"+document.form2.m.value+"月分の請求データを上書きします。よろしいですか？" );
}
function blankCheck() {
	if (document.FORM_ENTRY_CSV_UPLOAD.upfile.value == '') {
			alert ("ファイルを指定して下さい。");
				return false;
		}
		return true;
}
</script>
</head>
<body>
<div id="header">
	事務システム
</div>

<div id="content" align="center">
<div id="wrapper">

<h3>本部メニュー</h3>

<div align="left"> 
	<h4>■ 月謝計算</h4>
		<ol>
			<li><b>カレンダーデータの取り込み</b><br>
				<form name="form1" method="post" action="get_calender_data.php">
				<input type="text" name="y" value="<?php echo $year1; ?>" size="4">年&nbsp;
				<input type="text" name="m" value="<?php echo $month1; ?>" size="4">月分<br>
				<input type="radio" name="kari_ignore" value="0" checked>仮を無視しない&nbsp;
				<input type="radio" name="kari_ignore" value="1">仮を無視する<br>
				<input type="submit" value="取り込む" onclick="return updateAlert();">
				<font color="red" size="-1">* 処理結果が表示されるまでしばらくお待ちください</font>
				</form>
			<li><b>生徒の登録</b><br>
				<form method="post" action="student_fee_add.php">
				<input type="submit" value="生徒の新規登録へ">
				</form>
				<form method="post" action="student_fee_list.php">
				<input type="submit" value="登録済み生徒一覧へ"><br>
				<b><font color="green" size="-1">「登録済み生徒一覧」から入会金やテキスト代やその他金額や授業料分割支払を<br>入力することができます</font></b>
				</form>
			<li><b>振込者名の登録</b><br>
				<form method="post" action="furikomisha_add.php">
				<input type="submit" value="振込者名の新規登録へ">
				</form>
				<form method="post" action="furikomisha_list.php">
				<input type="submit" value="登録済み振込者名一覧へ"><br>
				</form>
			<li><b>先生・事務員の登録</b><br>
				<form method="post" action="teacher_add.php">
				<input type="submit" value="先生の新規登録へ">
				</form>
				<form method="post" action="teacher_list.php">
				<input type="submit" value="登録済み先生一覧へ">
				</form>
				<form method="post" action="staff_add.php">
				<input type="submit" value="事務員の新規登録へ">
				</form>
				<form method="post" action="staff_list.php">
				<input type="submit" value="登録済み事務員一覧へ"><br>
				</form>
			<li><b>科目の登録・削除</b><br>
				<form method="post" action="subject_edit.php">
				<input type="submit" value="科目の登録・削除">
				</form>
<!--
			<li><b>（明細書上部に表示される）その他項目の表示</b><br>
				<form method="post" action="others_edit_ym.php">
				<input type="text" name="y" value="<?php echo $year; ?>" size="4">年&nbsp;
				<input type="text" name="m" value="<?php echo $month; ?>" size="4">月分&nbsp;
				<input type="submit" value="表示">
				<font color="blue" size="-1">* 指定された年月のその他項目を、まとめて表示します</font>
				</form>
			<li><b>部門別合計の表示</b><br>
				<form method="get" action="save_statement.php">
				<font color="red" size="-1">◆ 明細書データを保存してから、部門別合計を算出します。</font><br>
				<input type="text" name="y" value="<?php echo $year; ?>" size="4">年&nbsp;
				<input type="text" name="m" value="<?php echo $month; ?>" size="4">月分&nbsp;
				<input type="submit" value="明細書データを保存する">
				</form>
-->
			<li><b>明細書PDFファイルの表示</b><br>
				<form method="post" action="./detail_pdf_list.php">
				<input type="submit" value="PDFファイル一覧へ">
				</form>
			<li><b>レポート</b><br>
				<form method="post" action="./report.php">
				<input type="text" name="y" value="<?php echo $year; ?>" size="4">年&nbsp;
				<input type="text" name="m" value="<?php echo $month; ?>" size="4">月分&nbsp;
				（過去表示期間<input type="text" name="mnum" value="1" size="2">ヵ月）&nbsp;
				<input type="submit" value="表示">
<!--
			<li><b>月謝振込確認</b><br>
				<form method="post" action="./seikyu_list.php">
				<input type="text" name="y" value="<?php echo $year; ?>" size="4">年&nbsp;
				<input type="text" name="m" value="<?php echo $month; ?>" size="4">月分&nbsp;
				<input type="submit" value="確認">
-->
				</form>
<!--
		</ol>
	<h4>■ 年月別一覧</h4>
		<ol>
-->
			<li><b>入会金</b><br>
				<form method="post" action="entrance_fee_edit_ym.php">
				<input type="text" name="y" value="<?php echo $year; ?>" size="4">年&nbsp;
				<input type="text" name="m" value="<?php echo $month; ?>" size="4">月分&nbsp;
				<input type="submit" value="表示">
				</form>
			<li><b>テキスト代</b><br>
				<form method="post" action="buying_textbook_edit_ym.php">
				<input type="text" name="y" value="<?php echo $year; ?>" size="4">年&nbsp;
				<input type="text" name="m" value="<?php echo $month; ?>" size="4">月分&nbsp;
				<input type="submit" value="表示">
				</form>
			<li><b>その他項目</b><br>
				<form method="post" action="others_edit_ym.php">
				<input type="text" name="y" value="<?php echo $year; ?>" size="4">年&nbsp;
				<input type="text" name="m" value="<?php echo $month; ?>" size="4">月分&nbsp;
				<input type="submit" value="表示">
				</form>
			<li><b>休み回数チェック</b><br>
				<form method="get" action="absent_list.php">
				<input type="text" name="y" value="<?php echo $year; ?>" size="4">年&nbsp;
				<input type="text" name="m" value="<?php echo $month; ?>" size="4">月分&nbsp;
				<input type="submit" value="表示">
				</form>
			<li><b>生徒一覧表示</b><br>
				<form method="get" action="student_list.php">
				<input type="text" name="y" value="<?php echo $year; ?>" size="4">年&nbsp;
				<input type="text" name="m" value="<?php echo $month; ?>" size="4">月分&nbsp;
				<input type="submit" value="表示">
				</form>
			<li><b>請求明細書送付準備（明細書をスプレッドシートおよびpdfファイルに出力）</b><br>
				<form method="get" action="all_output_pdf.php">
				<input type="text" name="y" value="<?php echo $year; ?>" size="4">年&nbsp;
				<input type="text" name="m" value="<?php echo $month; ?>" size="4">月分&nbsp;
				<input type="submit" value="出力">
				</form>
			<li><b>部門別合計</b><br>
				<form method="get" action="total_list.php">
				<input type="text" name="y" value="<?php echo $year; ?>" size="4">年&nbsp;
				<input type="text" name="m" value="<?php echo $month; ?>" size="4">月分&nbsp;
				<input type="submit" value="表示">
				</form>
			<li><b>請求データの更新</b><br>
				<form name="form2" method="get" action="save_statement.php">
				<input type="text" name="y" value="<?php echo $year1; ?>" size="4">年&nbsp;
				<input type="text" name="m" value="<?php echo $month1; ?>" size="4">月分&nbsp;
				<input type="submit" value="更新" onclick="return updateAlert1();"><br>
				<b><font color="green" size="-1">カレンダーデータの取り込みは行わず請求データの更新を行います。</font></b><br>
				<b><font color="green" size="-1">請求データ更新後は、「請求明細書送付準備」を改めて行う必要があります。</font></b>
				</form>
		</ol>
	<h4>■ 給与計算</h4>
		<ol>
			<li><b>講師・事務員の給与計算</b><br>
				<form method="post" action="check_list.php">
				<input type="text" name="y" value="<?php echo $year; ?>" size="4">年&nbsp;
				<input type="text" name="m" value="<?php echo $month; ?>" size="4">月分&nbsp;
				<input type="submit" value="表示">
				</form>
			<li><b>勤務報告補助システム（勤務報告チェック用）</b><br>
				<a href="https://docs.google.com/spreadsheets/d/1JLZoZQvJ0RV_KBgUs0TAmUqZ3T0C5NHSKmnF01OvGE0/edit#gid=34633175">スプレッドシート表示</a>
				<br><br>
			<li><b>出席簿</b><br>
				<form method="post" action="teacher_work_time_check_list.php">
				<input type="text" name="y" value="<?php echo $year1; ?>" size="4">年&nbsp;
				<input type="text" name="m" value="<?php echo $month1; ?>" size="4">月分&nbsp;
				<input type="submit" value="表示">
				</form>
			<li><b>給与調整</b><br>
				<form method="post" action="payadj_edit_ym.php">
				<input type="text" name="y" value="<?php echo $year; ?>" size="4">年&nbsp;
				<input type="text" name="m" value="<?php echo $month; ?>" size="4">月分&nbsp;
				<input type="submit" value="表示">
				</form>
		</ol>
	<h4>■ マッチング</h4>
		<ol>
			<li><b>銀行明細書アップロード</b><br>
				<a href="../bank-check/bank-upload.html">アップロードフォーム表示</a>
			<li><b>月謝振込確認</b><br>
				<form method="get" action="../bank-check/bank-check.php">
				<input type="text" name="y" value="<?php echo $year; ?>" size="4">年&nbsp;
				<input type="text" name="m" value="<?php echo $month; ?>" size="4">月分&nbsp;
				（過去表示期間<input type="text" name="l" value="6" size="2">ヵ月）&nbsp;
				<input type="submit" value="表示">
				</form>
		</ol>
	<h4>■ 期間講習スケジュール</h4>
		<ol>
			<li><b>期間講習申込みフォームCSVファイルアップロード</b><br>
				<form action="./season_class_entry_form.php" name="FORM_ENTRY_CSV_UPLOAD" method="post" enctype="multipart/form-data" onsubmit="return blankCheck()">
				<input type="hidden" name="class_type" value="season_class">
				<input type=file name="upfile" size="200"><br>
				<input type=submit name=sub value="アップロード実行">
				</form>
			<li><b>期間講習スケジュール</b><br>
				<form method="get" action="season_class_schedule.php">
				<input type="hidden" name="class_type" value="season_class">
				<input type="hidden" name="edit" value="1">
				<input type="submit" value="スケジュール調整">
				</form>
				<form method="get" action="season_class_schedule.php">
				<input type="hidden" name="class_type" value="season_class">
				<input type="hidden" name="view_type" value="student">
				<input type="submit" value="表示">
				</form>
			<li><b>期間講習生徒スケジュール・受講料</b><br>
				<form method="get" action="season_class_student_schedule.php">
				<input type="hidden" name="class_type" value="season_class">
				<input type="submit" value="表示">
				</form>
			<li><b>期間講習生徒配布用スケジュール</b><br>
				<form method="post" action="season_class_student_schedule.php">
				<input type="hidden" name="class_type" value="season_class">
				<input type="hidden" name="mode" value="1">
				<input type="submit" value="表示">
				</form>
		</ol>
	<h4>■ 土日講習スケジュール</h4>
		<ol>
			<li><b>土日講習申込みフォームCSVファイルアップロード</b><br>
				<form action="./season_class_entry_form.php" name="FORM_ENTRY_CSV_UPLOAD" method="post" enctype="multipart/form-data" onsubmit="return blankCheck()">
				<input type="hidden" name="class_type" value="sat_sun_class">
				<input type=file name="upfile" size="200"><br>
				<input type=submit name=sub value="アップロード実行">
				<font color="red" size="-1">* CSVファイルでは取り消し線など文字情報以外の修飾情報は無効です。</font>
				</form>
			<li><b>土日講習スケジュール</b><br>
				<form method="get" action="season_class_schedule.php">
				<input type="text" name="y" value="<?php echo $year1; ?>" size="4">年&nbsp;
				<select name="m">
				<option value="9">9-10月</option>
				<option value="11" selected>11-12月</option>
				</select>&nbsp;
				<input type="hidden" name="class_type" value="sat_sun_class">
				<input type="hidden" name="edit" value="1">
				<input type="submit" value="スケジュール調整">
				</form>
				<form method="get" action="season_class_schedule.php">
				<input type="text" name="y" value="<?php echo $year1; ?>" size="4">年&nbsp;
				<select name="m">
				<option value="9">9-10月</option>
				<option value="11" selected>11-12月</option>
				</select>&nbsp;
				<input type="hidden" name="class_type" value="sat_sun_class">
				<input type="hidden" name="view_type" value="student">
				<input type="submit" value="表示">
				</form>
			<li><b>土日講習生徒スケジュール・受講料</b><br>
				<form method="get" action="season_class_student_schedule.php">
				<input type="text" name="y" value="<?php echo $year1; ?>" size="4">年&nbsp;
				<select name="m">
				<option value="9">9-10月</option>
				<option value="11" selected>11-12月</option>
				</select>&nbsp;
				<input type="hidden" name="class_type" value="sat_sun_class">
				<input type="submit" value="表示">
				</form>
			<li><b>土日講習生徒配布用スケジュール</b><br>
				<form method="post" action="season_class_student_schedule.php">
				<input type="text" name="y" value="<?php echo $year1; ?>" size="4">年&nbsp;
				<select name="m">
				<option value="9">9-10月</option>
				<option value="11" selected>11-12月</option>
				</select>&nbsp;
				<input type="hidden" name="class_type" value="sat_sun_class">
				<input type="hidden" name="mode" value="1">
				<input type="submit" value="表示">
				</form>
		</ol>
	<h4>■ テキスト</h4>
		<ol>
			<li><b>テキストの登録</b><br>
				<form method="get" action="text_DB.php">
				<input type="submit" value="登録・更新・一覧">
				</form>
			<li><b>発注一覧</b><br>
		</ol>
	<br>
	<br>
	<h4>■ 事務システムパスワード</h4>
			<form method="get" action="set_password.php">
　　　<input type="submit" value="パスワード変更">
	<br>
			</form>
			<form method="get" action="get_calender_insert.php">
	<h4>■ googleカレンダー取り込み</h4>
　　　<input type="submit" value="googleカレンダー取り込み">
			</form>
	<br>
			</form>
			<form method="get" action="get_calender_insert.php">
	<h4>■ googleカレンダー取り込み(繰り返しのみ)</h4>
	<input type="hidden" name="repeat" value="y">
　　　  <input type="submit" value="googleカレンダー取り込み(繰り返しのみ）">
			</form>
	<br>
	<h4>■ カレンダーDB取り込み</h4>
	<form method="get" action="load_calender_data.php">
	<input type="text" name="year" value="<?php echo $year1; ?>" size="4">年&nbsp;
	<input type="text" name="month" value="<?php echo $month1; ?>" size="4">月分&nbsp;
	<input type="submit" value="表示">
	</form>
	<br>
	<h4>■ 季節講習スケジュール取り込み</h4>
	<form method="post" action="upload_season_calender.php">
	開始年：<input type="text" name="startyear" value="" size="4">年&nbsp;
	開始月：<input type="text" name="startmonth" value="" size="4">月&nbsp;
	終了月（開始月＋１）:<input type="text" name="endmonth" value="" size="4">月&nbsp;
	replaceモード：<input type="text" name="mode" value="<?php echo $replace; ?>" size="4">モード&nbsp;
	<input type="submit" value="実行">
	</form>
	<br>
	<br>
<!--
	<h4>■ サポートセンター</h4>
		<ol>
			<li><b>授業可能予定一覧（生徒用）</b><br>
			<a href="../student/m/teacher_list.php">授業可能予定</a><br><br>
			<li><b>先生の授業可能予定の登録（サポートセンター用）</b><br>
			<a href="./available_schedule_edit.php">授業可能予定の登録</a>
-->
<!--
			<br><br>
			<li><b>授業予定表（パソコン版）</b><br>
			<a href="schedule_teacher_pc.php?y=<?= $year ?>&m=<?= $month ?>">授業予定表へ</a>
-->
<!--
		</ol>
	<h4>■ 授業予定カレンダー</h4>
		<ol>
			<li><b>サンプル：予定カレンダー（塾）の表示</b><br>
			<a href="schedule.php?y=<?= $year ?>&m=<?= $month ?>">予定カレンダー（塾）へ</a>
		</ol>
-->
</div>

</div>
</div>
</body></html>

