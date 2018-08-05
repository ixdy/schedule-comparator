<?php
require("./.mysql.php");

// Automatically figure out the proper year, breaking at July.
// This can be overridden by users.
if (date('n') < 7) {
  $_year = date('Y') - 1;
} else {
  $_year = date('Y');
}
$_url = "https://jeffsweb.net$_SERVER[REQUEST_URI]";

function header_($title, $student_name = "")
{
global $start_time, $_year, $year_str, $_url;
$year_str = $_year . '-' . ($_year + 1);
$start_time = microtime_float();

#ob_start("ob_gzhandler");

$mime = "text/html";
$charset = "iso-8859-1";

if (stristr($_SERVER["HTTP_ACCEPT"], "application/xhtml+xml")) {
  $mime = "application/xhtml+xml";
}
if (stristr($_SERVER["HTTP_USER_AGENT"],"W3C_Validator")) {
  $mime = "application/xhtml+xml";
}

//header("Content-Type: $mime;charset=$charset");
header("Vary: Accept");

if ($mime == "application/xhtml+xml") {
  echo "<?xml version='1.0' encoding='$charset' ?>\n";
}
?>
<!DOCTYPE html>
<html>
 <head>
  <title>Schedule Comparator (<?=$year_str?>) - <?=$title?></title>
  <link rel="stylesheet" type="text/css" href="/scheduler/style" />
  <link rel="canonical" href="<?=$_url?>" />
  <meta name="title" content="Schedule Comparator (<?=$year_str?>) - <?=$title?>" />
  <meta name="description" content="<?php if ($student_name != "") echo "$student_name's schedule for the $year_str school year."; else echo "Schedule Comparator - scheduling information for TJHSST's $year_str school year."; ?>" />
  <meta name="robots" content="noindex, nofollow" />
  <link rel="image_src" href="//jeffsweb.net/scheduler/tjseal.png" />
 </head>
 <body>
  <h1>Schedule Comparator (<?=$year_str?>) - <?=$title?></h1>

  <div class="search_bar">
<?php
	student_box();
	teacher_box();
	course_id_box();
?>
  </div>
 
<?php
}


function footer()
{
global $start_time, $num_students;
$end_time = microtime_float();

echo "<p style=\"font-size: xx-small; border-top-style: solid; border-top-width: 1px; border-top-color: #000000\">This site is not in any way affiliated with the <a href=\"http://www.tjhsst.edu/\">Thomas Jefferson High School for Science and Technology</a>.  Currently serving $num_students students.</p>";
echo "\n<!-- Page generated in " . round($end_time - $start_time, 4) . " seconds -->\n";
?>
<?php
if (0) {
?>
<p>
 <a href="http://validator.w3.org/check?uri=referer"><img src="/scheduler/valid-xhtml11.png" alt="Valid XHTML 1.1!" height="31" width="88" /></a>
 <a href="http://jigsaw.w3.org/css-validator/check/referer"><img style="border:0; width:88px; height:31px" src="/scheduler/vcss.png" alt="Valid CSS!" /></a>
 <a rel="license" href="https://creativecommons.org/licenses/by-nc-sa/3.0/us/"><img alt="Creative Commons License" style="border-width:0" src="https://i.creativecommons.org/l/by-nc-sa/3.0/us/88x31.png" /></a>
 <a href="https://www.nearlyfreespeech.net/"><img src="https://www.nearlyfreespeech.net/logos/nfsn88x31logo.gif" alt="NearlyFreeSpeech" height="31" width="88" /></a>
 <!--<img src="/analytics/scheduler" width="1" height="1" alt="" />-->
</p>
<?php } ?>
	</body>
	</html>
	<?php
	ob_flush();
	exit;
	}


	function microtime_float()
	{
		list($usec, $sec) = explode(" ", microtime());
		return ((float)$usec + (float)$sec);
	} 

	$num_students = 0;

	function student_box()
	{
		global $num_students, $_year, $student_id;
		$student_query = @mysql_query("SELECT `students`.`student_id`, `student_name` FROM `students`, `schedules`, `classes` WHERE `student_name` IS NOT NULL AND `year` = '$_year' AND `students`.`student_id` = `schedules`.`student_id` AND `classes`.`class_id` = `schedules`.`class_id` GROUP BY `students`.`student_id` ORDER BY `student_name` ASC");
	?>
	<div class="search_box">
	<form action="/scheduler/bouncer.php" method="get" class="search_box">
	<select name="student_id" onchange="if(this.value != '') this.parentNode.submit();">
		<option value="">Choose a student</option>
		<option value="">----------------</option>
	<?php
		while ($data = mysql_fetch_assoc($student_query))
		{
			echo "\t<option value=\"$data[student_id]\"";
			if ($student_id == $data["student_id"]) {
				echo " selected=\"selected\"";
			}
			echo ">".htmlentities($data["student_name"])."</option>\n";
			$num_students++;
		}
	?>
	</select>
	<input type="submit" value="Go!" />
	</form>
	</div>
	<?php
	}

	function teacher_box()
	{
		global $_year, $teacher_id;
		$teacher_query = @mysql_query("SELECT `teachers`.`teacher_id`, `teacher_name` FROM `teachers`, `classes` WHERE `teacher_name` IS NOT NULL AND `year` = '$_year' AND `teachers`.`teacher_id` = `classes`.`teacher_id` GROUP BY `teachers`.`teacher_id` ORDER BY `teacher_name` ASC");
		//@mysql_query("SELECT `teacher_id`, `teacher_name` FROM `teachers` WHERE `teacher_name` IS NOT NULL ORDER BY `teacher_name` ASC");
	?>
	<div class="search_box">
	<form action="/scheduler/bouncer.php" method="get" class="search_box">
	<select name="teacher_id" onchange="if(this.value != '') this.parentNode.submit();">
		<option value="">Choose a teacher</option>
		<option value="">----------------</option>
	<?php
		while ($data = mysql_fetch_assoc($teacher_query))
		{
			echo "\t;<option value=\"$data[teacher_id]\"";
			if ($teacher_id == $data["teacher_id"]) {
				echo " selected=\"selected\"";
			}
			echo ">".htmlentities($data["teacher_name"])."</option>\n";
		}
	?>
	</select>
	<input type="submit" value="Go!" />
	</form>
	</div>
	<?php
	}

	function course_id_box()
	{
		global $_year, $course_id;
		$course_id_query = @mysql_query("SELECT DISTINCT SUBSTRING(`section_id`, 1, 6) AS `course_id` FROM `classes` WHERE `section_id` IS NOT NULL AND `year` = '$_year' ORDER BY `course_id` ASC");
	?>
	<div class="search_box">
	<form action="/scheduler/bouncer.php" method="get" class="search_box">
	<select name="course_id" onchange="if(this.value != '') this.parentNode.submit();">
		<option value="">Choose a course ID</option>
		<option value="">----------------</option>
	<?php
		while ($data = mysql_fetch_assoc($course_id_query))
		{
			echo "\t<option value=\"$_year-$data[course_id]\"";
			if ($course_id == "$_year-$data[course_id]") {
				echo " selected=\"selected\"";
			}
			echo ">".htmlentities($data["course_id"])."</option>\n";
		}
	?>
	</select>
	<input type="submit" value="Go!" />
	</form>
	</div>
	<?php
	}


	function get_term($term, $length)
	{
		if ($length == "year")
		$return = "Year";
	else if ($term < 3)
		$return = "Fall";
	else
		$return = "Spring";
	if ($length == "quarter")
	{
		if ($term % 2)
			$return .= " 1";
		else
			$return .= " 2";
	}

	return $return;
}

$special_rooms = array(
	968 => '242A',
	969 => 'Auditorium',
	970 => 'Band',
	971 => 'Chorus',
	972 => 'Drama',
	973 => 'Gym',
	974 => 'Orchestra',
	975 => 'Planetarium',
	976 => 'Weight',
	977 => 'Cafeteria',
	978 => 'Mentorship'
	);

function get_room ($room_number)
{
	global $special_rooms;
	if ($special_rooms[$room_number] != "")
		return $special_rooms[$room_number];
	else if ($room_number != "" && $room_number < 100)
		return "T$room_number";
	else
		return $room_number;
}


function print_special_rooms()
{
global $special_rooms;
?>
<table style="display: inline;">
<tr><th colspan="2">Special Rooms</th></tr>
<?php
	echo "<tr><td style='padding-right: 10px'>Trailers</td><td>Remove the T and input the number, prefixed by 1 or 2 zeros to 3 digits</td></tr>\n";
	foreach ($special_rooms as $fake => $text)
	{
		echo "<tr><td style='padding-right: 10px'>$text</td><td>$fake</td></tr>\n";
	}
	echo "</table>\n";
}

function die_error($error_message)
{
	header_("Error");
	echo "<p>$error_message</p>\n<p>Please check your query and try again.  If the error persists, please contact me at <em>jgrafton+scheduler at gmail dot com</em>, as it may be indicative of a bug.</p>\n";
	footer();
}
