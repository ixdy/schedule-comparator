<?php
/*
   Copyright 2004 Jeff Grafton

   Licensed under the Apache License, Version 2.0 (the "License");
   you may not use this file except in compliance with the License.
   You may obtain a copy of the License at

       http://www.apache.org/licenses/LICENSE-2.0

   Unless required by applicable law or agreed to in writing, software
   distributed under the License is distributed on an "AS IS" BASIS,
   WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
   See the License for the specific language governing permissions and
   limitations under the License.
*/

require("./common.php");
// debug
//$_year = 2004;

$_num_periods = 8;
if ($_year >= 2010)
{
	$_num_periods = 7;
} 

if ($_SERVER['REQUEST_METHOD'] == 'POST')
{	
	if ($_POST['disclaimer_acked'] != "1") {
		die_error("You must acknowledge the disclaimer.");
	}
	$student_id = intval($_POST['student_id']);
	$schedule_year_query = @mysql_query("SELECT `year` FROM `students`,`schedules`,`classes` WHERE `students`.`student_id` = '$student_id' AND `students`.`student_id` = `schedules`.`student_id` AND `schedules`.`class_id` = `classes`.`class_id` LIMIT 1");
	if (mysql_num_rows($schedule_year_query) < 1)
	{
		$student_id = 0;
		$year = $_year;
	}
	else
	{
		list($year) = mysql_fetch_array($schedule_year_query);
	}

	if (!$student_id && !preg_match("/^[A-Za-z\\\'\-]+\s([A-Za-z\\\'\-]+\s?)+$/", $_POST['student_name']))
	{
		die_error("Your name was invalid.  Please include your full name and try again.");
	}
	
	$student_name = $_POST['student_name'];

	for ($i = 1; $i <= $_num_periods; $i++)
	{
/*		if (!preg_match("/^(\d{3}\??)((:(\d{3}\??))?\.(\d{3}\??)(:(\d{3}\??))?)?$/", $_POST['period'][$i], $matches))
		{
			die_error("The room number(s) you supplied for period $i are invalid.  Please check them and try again.");
		}
*/		
		if (!preg_match("/^([a-zA-Z0-9]{6}-[a-zA-Z0-9]{2}\??)(\.([a-zA-Z0-9]{6}-[a-zA-Z0-9]{2}\??))?$/", $_POST['period'][$i], $matches))
		{
			die_error("The section ID(s) you supplied for period $i are invalid.  Please check them and try again.");
		}

		preg_match("/^([0-9]{0,3})\.?([0-9]{0,3})$/", $_POST['period_room'][$i], $room_matches);

		$j = 0;
	
		if ($matches[1] != "" && $matches[3] == "")
		{
			$periods[$i][$j]['section_id'] = strtoupper($matches[1]);
			$periods[$i][$j]['term'] = 1;
			$periods[$i][$j]['length'] = 'year';
			$periods[$i][$j]['want_to_change'] = (strpos($matches[1], "?")?1:0); 
			if ($room_matches[1] != "") {
				$periods[$i][$j]['room_number'] = $room_matches[1];
			}
			else {
				$periods[$i][$j]['room_number'] = "";
			}
			$j++;
		}
		else
 		{
			$periods[$i][$j]['section_id'] = strtoupper($matches[1]);
			$periods[$i][$j]['term'] = 1;
			$periods[$i][$j]['length'] = 'semester';
			$periods[$i][$j]['want_to_change'] = (strpos($matches[1], "?")?1:0); 
			if ($room_matches[1] != "") {
				$periods[$i][$j]['room_number'] = $room_matches[1];
			}
			else {
				$periods[$i][$j]['room_number'] = "";
			}
			$j++;

			$periods[$i][$j]['section_id'] = strtoupper($matches[3]);
			$periods[$i][$j]['term'] = 3;
			$periods[$i][$j]['length'] = 'semester';
			$periods[$i][$j]['want_to_change'] = (strpos($matches[3], "?")?1:0); 
			if ($room_matches[2] != "") {
				$periods[$i][$j]['room_number'] = $room_matches[2];
			}
			else {
				$periods[$i][$j]['room_number'] = "";
			}
			$j++;
		}
	}

	if (!$student_id)
	{
		$student_id_query = @mysql_query("SELECT `students`.`student_id` FROM `students`,`schedules`,`classes` WHERE `students`.`student_name` LIKE '$student_name' AND `students`.`student_id` = `schedules`.`student_id` AND `schedules`.`class_id` = `classes`.`class_id` AND `classes`.`year` = '$year' LIMIT 1");
		if (mysql_num_rows($student_id_query) > 0)
		{
			list($student_id) = mysql_fetch_array($student_id_query);
		}
		else
		{
			$new_student_query = mysql_query("INSERT INTO `students` SET `student_name` = '$student_name'");
			$student_id = mysql_insert_id();
		}
	}

	$purge_query = mysql_query("DELETE FROM `schedules` WHERE `student_id` = '$student_id'");
	
	$ip_address = ($_SERVER['X_FORWARDED_FOR'] != "" ? $_SERVER['X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR']);
	$log_query = mysql_query("INSERT INTO `ip_log` SET `student_id` = '$student_id', `ip_address` = '$ip_address'");

	foreach ($periods as $period => $classes)
	{
		foreach ($classes as $class)
		{
			$class_id = "";

			if ($class[room_number] == "") {
				$room_query = "`room_number` is NULL";
			}
			else {
				$room_query = "`room_number` = '$class[room_number]'";
			}

			$class_search = mysql_query("SELECT `class_id` FROM `classes` WHERE `section_id` LIKE '$class[section_id]' AND `period` = '$period' AND `term` = '$class[term]' AND `length` = '$class[length]' AND `year` = '$year' AND $room_query");
			if (mysql_num_rows($class_search) > 0)
			{
				list($class_id) = mysql_fetch_array($class_search);
			}
			else
			{
				if ($class[room_number] == "") {
					$room_ins = "";
				}
				else {
					$room_ins = ", `room_number` = '$class[room_number]'";
				}
				$new_class_query = mysql_query("INSERT INTO `classes` SET `section_id` = '$class[section_id]', `period` = '$period', `term` = '$class[term]', `length` = '$class[length]', `year` = '$year' $room_ins");
				$class_id = mysql_insert_id();
			}

			$add_class_schedule_query = mysql_query("INSERT INTO `schedules` SET `student_id` = '$student_id', `class_id` = '$class_id', `want_to_change` = '$class[want_to_change]'");
		}
	}
	
	header("Location: /scheduler/s/$student_id");
	exit;
}

$student_id = intval($_GET['student_id']);
$old_data_query = @mysql_query("SELECT `student_name`,`year` FROM `students`,`schedules`,`classes` WHERE `students`.`student_id` = '$student_id' AND `students`.`student_id` = `schedules`.`student_id` AND `schedules`.`class_id` = `classes`.`class_id` LIMIT 1");
if (mysql_num_rows($old_data_query) > 0)
{
	list($student_name, $year) = mysql_fetch_array($old_data_query);
	$_year = $year;
}
else
	$student_id = 0;


header_("Edit Schedule");

?>
<p style="font-weight:bold">Please remember that any data you add here is released into the public domain for anybody on the Internet to view. </p>

<div style="border-top-color: #000000; border-top-style: solid; border-top-width: 1px;">
<p>List <strong>section IDs</strong> for each period.  These take the form <em>######-##</em>. (Note: some digits may actually be letters.) <strong>If a full section ID is not available, use the period number for the last two digits.</strong></p>
<p>Separate semesters by a period (.)<!--, quarters by a colon (:)-->.</p>
<p>List courses you are planning to switch by appending a question mark (?) to the section ID (e.g. 011663-09?).</p>
<p><strong style="color:#ff0000;">New!</strong> You can list room numbers for any periods you know about. Room numbers are three digits long and follow the same pattern for denoting semesters as above.</p>
<table style="border: 0px; margin: 5px;">
<tr><th colspan="2">Examples:</th></tr>
<tr><td>Full year course:</td><td>115000-35</td></tr>
<tr><td>Two semesters:</td><td>319966-04.649800-21</td></tr>
<!--<tr><td>Two fall quarter classes:</td><td>112:103.204</td></tr>
<tr><td>Four quarter classes:</td><td>203:123.105:112</td></tr>-->
</table>

<form action="/scheduler/e/<?=$student_id?>" method="post" style="" onreset="return confirm('Are you sure you want to undo changes?');">
<table style="border: 0px; margin: 10px; display: inline">
<tr><td>Student name:</td><td colspan="2"><?=(!$student_id ? "<input type=\"text\" name=\"student_name\" size=\"25\" required=\"required\" />" : "<input type=\"hidden\" name=\"student_id\" value=\"$student_id\" />$student_name")?></td></tr>
<tr><td>&nbsp;</td><td>Section IDs</td><td>Room numbers (optional)</td></tr>
<?php
for ($i = 1; $i <= $_num_periods; $i++)
{
	$val = "";
	$roomval = "";
	if ($student_id)
	{
		$period_query = mysql_query("SELECT `section_id`, `room_number`, `term`, `want_to_change` FROM `classes`,`schedules` WHERE `student_id` = '$student_id' AND `period` = '$i' AND schedules.class_id = classes.class_id ORDER BY `term` ASC");
		while ($period_data = mysql_fetch_assoc($period_query))
		{
			$section_id = $period_data['section_id'];
			$section_id .= ($period_data['want_to_change']?"?":"");
			$room_number = $period_data['room_number'];
			switch ($period_data['term'])
			{
				case 1:	$val .= "$section_id";
					$roomval .= "$room_number";
					break;
				case 2:	
				case 4:	$val .= ":$section_id";
					$roomval .= ":$room_number";
					break;
				case 3:	$val .= ".$section_id";
					$roomval .= ".$room_number";
					break;
			}
		}
	}
	echo "<tr><td>Period $i:</td><td><input type=\"text\" name=\"period[$i]\" size=\"20\" value=\"$val\" required=\"required\" /></td><td><input type=\"text\" name=\"period_room[$i]\" size=\"10\" value=\"$roomval\" /></td></tr>\n";
}
?>
</table>
<p>
Remember that any information you put into this form is available on the Internet for anyone to see.<br />
<strong>By submitting scheduling information on this site you waive me (jgrafton) of any liability of whatever may happen to said information.</strong><br />
<label><input type="checkbox" name="disclaimer_acked" value="1" required="required" />Acknowledged</label>
</p>
<p><input type="submit" value="Save Schedule" /> <input type="reset" value="Undo Changes" /></p>
<?php
print_special_rooms();
?>
</form>
</div>
<?
footer();
?>
