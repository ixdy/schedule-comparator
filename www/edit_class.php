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

if ($_SERVER['REQUEST_METHOD'] == "POST")
{
	$class_id = intval($_POST['class_id']);
}
else
{
	$class_id = intval($_GET['class_id']);
}

$class_search = mysql_query("SELECT `class_name`, `teacher_name`, `teachers`.`teacher_id`, `room_number`, `period`, `term`, `length`, `year`, `section_id` FROM `classes` LEFT JOIN `teachers` on teachers.teacher_id = classes.teacher_id WHERE `class_id` = '$class_id'");

if (mysql_num_rows($class_search) < 1)
{
	die_error("Invalid class id.");
}

$data = mysql_fetch_assoc($class_search);
$_year = $data['year'];

/*if ($data['class_name'] != "" && $data['teacher_name'] != "" && $data['room_number'] != "")
{
	if ($_GET['referer'] == "")
		header("Location: /scheduler/");
	else
		header("Location: $_GET[referer]");
	exit;
}
*/

if ($_SERVER['REQUEST_METHOD'] == "POST")
{
	/*
	$room_number = trim($_POST['room_number']);
	if (preg_match("/^\d{3}$/", $room_number))
	{
		$update_query = mysql_query("UPDATE `classes` SET `room_number` = '$room_number' WHERE `class_id` = '$class_id'");
	}
	*/
	$class_name = trim(strip_tags($_POST['class_name']));
	if ($class_name != "")
	{
		$update_query = mysql_query("UPDATE `classes` SET `class_name` = '$class_name' WHERE `class_id` = '$class_id'");
	}

	$teacher_id = intval($_POST['teacher_id']);
	$teacher_name = trim(strip_tags($_POST['teacher_name']));

	if ($teacher_id || $teacher_name != "")
	{
		// see if teacher_id is valid
		$teacher_check = mysql_query("SELECT `teacher_name` FROM `teachers` WHERE `teacher_id` = '$teacher_id'");
		if (mysql_num_rows($teacher_check) < 1)
		{
			$teacher_id = 0;
		}
		if ($teacher_name != "")
		{
			$teacher_check2 = mysql_query("SELECT `teachers`.`teacher_id` FROM `teachers`,`classes` WHERE `teacher_name` LIKE '$teacher_name' AND `teachers`.`teacher_id` = `classes`.`teacher_id` AND `classes`.`year` = '$_year' LIMIT 1");
			if (mysql_num_rows($teacher_check2) > 0)
			{
				list($teacher_id) = mysql_fetch_array($teacher_check2);
			}
			else
			{
				$new_teacher_query = mysql_query("INSERT INTO `teachers` SET `teacher_name` = '$teacher_name'");
				$teacher_id = mysql_insert_id();
			}
		}
		
		if ($teacher_id)
			$update_query = mysql_query("UPDATE `classes` SET `teacher_id` = '$teacher_id' WHERE `class_id` = '$class_id'");
	}


	header("Location: /scheduler/c/$class_id?referer=" . urlencode($_POST['referer']));
	exit;
}

header_("Edit Class");

?>
<p>Please copy class data <em>exactly</em> as it is written on your schedule.</p>
<?php
if ($_GET['referer'] != "") {
	$link = $_GET['referer'];
} else if ($_SERVER['HTTP_REFERER'] != "") {
	$link = $_SERVER['HTTP_REFERER'];
} else {
	$link = "";
}

if ($link != "") {
	echo "<p><a href=\"$link\">Go back.</a></p>\n";
}
?>
<form action="/scheduler/c/<?=$class_id?>" method="post" onreset="return confirm('Are you sure you want to reset this form?');">
<table style="border-style: none;">
<tr><td>Period:</td><td><?=$data['period']?></td></tr>
<tr><td>Section ID:</td><td><?=htmlentities($data['section_id'])?></td></tr>
<tr><td>Term:</td><td><?=get_term($data['term'], $data['length'])?></td></tr>
<tr><td>Room:</td><td>
<?php
/*if ($data['room_number'] != "")
{*/
	echo get_room($data['room_number']);
/*}
else
{
	echo "<input type=\"text\" name=\"room_number\" size=\"4\" /> <em>(3 digits; see \"Special Rooms\" table below.)</em>";
}*/
?></td></tr>
<tr><td>Subject:</td><td>
<?php
/*if ($data['class_name'] != "")
{
	echo $data['class_name'];
}
else
{ */
	echo "<input type=\"text\" name=\"class_name\" size=\"25\" value=\"".htmlentities($data["class_name"])."\" />";
//}
?></td></tr>

<tr><td>Teacher:</td><td>
<?php
/*if ($data['teacher_name'] != "")
{
	echo $data['teacher_name'];
}
else
{*/
?>
<select name="teacher_id">
<option value="">Select a teacher</option>
<option value="">NEW TEACHER (not listed)</option>
<option value="">------------------------</option>
<?php
$teacher_query = mysql_query("SELECT `teachers`.`teacher_id`, `teacher_name` FROM `teachers`,`classes` WHERE `teacher_name` IS NOT NULL AND `classes`.`teacher_id` = `teachers`.`teacher_id` AND `classes`.`year` = '$_year' GROUP BY `teachers`.`teacher_id` ORDER BY `teacher_name` ASC");

while($teacher_data = mysql_fetch_assoc($teacher_query))
{
	$selected = "";
	if ($data['teacher_id'] == $teacher_data['teacher_id']) {
		$selected = "selected=\"selected\"";
	}
	echo "<option value=\"$teacher_data[teacher_id]\" $selected>".htmlentities($teacher_data["teacher_name"])."</option>\n";
}
?>
</select>
<input type="text" name="teacher_name" size="25" />
<?php
//}
?></td></tr>
<tr><td colspan="2">
<input type="hidden" name="class_id" value="<?=$class_id?>" />
<input type="hidden" name="referer" value="<?=$link?>" />
<input type="submit" value="Save Changes" /> <input type="reset" value="Reset Form" />
</td></tr>
</table>
</form>
<?
footer();
?>
