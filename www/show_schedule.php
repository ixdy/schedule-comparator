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

$student_id = intval($_GET['student_id']);
$teacher_id = intval($_GET['teacher_id']);
$course_id = $_GET['course_id'];

if ($student_id == 0 && $teacher_id == 0 && $course_id == "")
{
	header("Location: /scheduler/");
	exit;
}

function get_header_color($data) {
	$header_color = '#c0c0ff';
	if ($data['length'] == 'year')
	{
	  $header_color = '#c0c0ff';
	}
	else if ($data['term'] < 3)
	{
	  if ($data['term'] % 2)
	    $header_color = '#ffc0c0';
	  else
	    $header_color = '#ee9090';
	}
	else
	{
	  if ($data['term'] % 2)
	    $header_color = '#c0ffc0';
	  else
	    $header_color = '#90ee90';
	}
	return $header_color;
}
$query = "";
$edit_text = "";

if ($student_id)
{
	$student_query = @mysql_query("SELECT `student_name`,`year` FROM `students`,`schedules`,`classes` WHERE `students`.`student_id` = '$student_id' AND `students`.`student_id` = `schedules`.`student_id` AND `schedules`.`class_id` = `classes`.`class_id` LIMIT 1");

	if (@mysql_num_rows($student_query) != 1)
	{
		die_error("Invalid student id.");
	}

	list($student_name, $year) = mysql_fetch_array($student_query);

	$_year = $year;

	header_($student_name, $student_name);
	$edit_text = "<a href=\"/scheduler/e/$student_id\">Edit this student's schedule</a>";
	$query = "`student_id` = '$student_id'";
	$want_query = ", `schedules`.`want_to_change`";
}
else if ($teacher_id)
{
	$teacher_query = @mysql_query("SELECT `teacher_name`,`year` FROM `teachers`,`classes` WHERE `teachers`.`teacher_id` = '$teacher_id' AND `teachers`.`teacher_id` = `classes`.`teacher_id` LIMIT 1");

	if (@mysql_num_rows($teacher_query) != 1)
	{
		die_error("Invalid teacher id.");
	}

	list($teacher_name, $year) = mysql_fetch_array($teacher_query);

	$_year = $year;

	header_($teacher_name, $teacher_name);
	$query = "teachers.teacher_id = '$teacher_id'";
	$want_query = "";
}
else
{
	if (!preg_match("/^([0-9]{4})-([a-zA-Z0-9]{6})$/", $course_id, $matches)) {
		die_error("Invalid course ID.");
	}

	$_year = $matches[1];
	$query = "`year` = '$matches[1]' AND classes.section_id LIKE '$matches[2]-__'";
	$want_query = "";

	header_("Course $matches[2]");
}

$schedule_query = @mysql_query("SELECT DISTINCT classes.class_id, `period`, `term`, `length`, `room_number`, `class_name`, teachers.teacher_id, `teacher_name`, `section_id` $want_query FROM `schedules` INNER JOIN `classes` ON classes.class_id = schedules.class_id  LEFT JOIN `teachers` on teachers.teacher_id = classes.teacher_id WHERE $query ORDER BY `period` ASC, `term` ASC");

$schedule_data = array();
while ($data = @mysql_fetch_assoc($schedule_query)) {
	$schedule_data[] = $data;
}
?>

<!--<p style="font-weight:bold; color:#ff0000;">Update 2009-09-02 23:37 PDT: You can now set the room number for courses you know about on the "edit schedule" page. This should reduce collisions in common courses such as TA. Let me know if there are any lingering issues.</p>-->

<h2>Operations</h2>
<ul>
 <?php if ($edit_text != "") echo "<li>$edit_text</li>\n"; ?>
 <li><script type="text/javascript">function fbs_click() {u=location.href;t=document.title;window.open('https://www.facebook.com/sharer.php?u='+encodeURIComponent(u)+'&amp;t='+encodeURIComponent(t),'sharer','toolbar=0,status=0,width=626,height=436');return false;}</script><a href="https://www.facebook.com/share.php?u=<?=urlencode($_url)?>" onclick="return fbs_click()" target="_blank" class="fb_share_link">Share on Facebook</a></li>
 <li><img src="/scheduler/twitter.png" width="16" height="16" alt="Twitter logo" style='vertical-align: top; ' /> <a href="https://twitter.com/home?status=My%20TJ%20class%20schedule%20for%20<?=urlencode($year_str)?>%3a%20<?=urlencode($_url)?>" target="_blank">Share on Twitter</a></li>
 <li><script src="https://apis.google.com/js/plusone.js"></script><div class="g-plus" data-action="share"></div></li>
 <li><script src="https://platform.tumblr.com/v1/share.js"></script><a href="https://www.tumblr.com/share" title="Share on Tumblr" style="display:inline-block; text-indent:-9999px; overflow:hidden; width:129px; height:20px; background:url('https://platform.tumblr.com/v1/share_3.png') top left no-repeat transparent;">Share on Tumblr</a></li>
 <li>Link directly to this schedule in your LiveJournal or blog with this URL: <?=$_url?></li>
</ul>

<h2>At a Glance</h2>
<table style="border: 1px #aaaaaa solid; border-bottom: 0px; padding: 0px; margin: 10px 5px 30px 5px;" cellpadding="0" cellspacing="0">
<tr><th style='padding:5px; border-bottom: 1px #aaaaaa solid; border-right: 1px #aaaaaa solid;'>Period</th><th colspan="4" style='padding:5px; border-bottom: 1px #aaaaaa solid; border-right: 1px #aaaaaa solid;'>Fall</th><th colspan="4" style='padding:5px; border-bottom: 1px #aaaaaa solid;'>Spring</th>
<?php
$last_period = 0;
$last_term = 1;
$last_length = 'year';
foreach ($schedule_data as $data) {
	$header_color = get_header_color($data);
	$style = "style='background-color:$header_color; padding:5px 15px 5px 10px; border-bottom: 1px #eeeeee solid;'";
	$stylesection = "style='background-color:$header_color; padding:5px 10px 5px 20px; border-bottom: 1px #eeeeee solid;'";
	if ($data['period'] != $last_period || $data['term'] == $last_term || $last_length == 'year') {
		echo "</tr>\n<tr><td align='center' style='padding:5px; border-bottom: 1px #aaaaaa solid; border-right: 1px #aaaaaa solid;'><a href='#p$data[period]'>$data[period]</a></td>";
		if ($data['term'] == 3) {
			echo "<td colspan=\"4\">&nbsp;</td>";
		}
	}

	$room = ($data['room_number'] == "" ? "<em>unknown</em>" : get_room($data['room_number']));
	$teacher = ($data['teacher_name'] == "" ? "<em>unknown</em>" : htmlentities($data['teacher_name']));
	$class = ($data['class_name'] == "" ? "<em>unknown</em>" : htmlentities($data['class_name']));
	echo "<td $stylesection>$data[section_id]</td><td $style>$room</td><td $style>$teacher</td><td $style>$class</td>";

	if ($data['length'] == 'year') {
		echo "<td colspan='4' $style>&nbsp;</td>";
	}
	$last_period = $data['period'];
	$last_term = $data['term'];
	$last_length = $data['length'];
}
?>
</tr>
</table>


<h2 style='margin-bottom:10px'>Classes in Detail</h2>
<table style="border: 0px; padding: 0px; margin: 0px;">
<tr><td>
<?php
$last_period = 0;
foreach ($schedule_data as $data) {
	if ($data['period'] != $last_period) {
		echo "<h3><a id='p$data[period]'>Period $data[period]</a></h3>\n";
	}
	$left_margin = '';
	if ($data['term'] != 0) {
		$left_margin = 'margin-left: ' . ($data['term'] * 20) . 'px;';
	}
?>
<table style="margin-bottom: 20px; <?=$left_margin;?> border-width: 1px; border-color: #aaaaaa; border-style: solid; width: 100%;" cellspacing="0">
<?php
$header_color = get_header_color($data);
?>
<tr><th class="schedule" colspan="4" style="border-bottom-width: 1px; border-bottom-color: #aaaaaa; border-bottom-style: solid; background-color: <?=$header_color?>;">Period <?=$data['period']?>: <?=get_term($data['term'], $data['length'])?><?php
if ($data['want_to_change'] == 1) 
	echo " <em>(want switch)</em>";
?></th></tr>
<tr><td class="schedule" style="border-width: 1px; border-color: #aaaaaa; border-bottom-style: solid; border-right-style: solid; width: 25%;"><a href="/scheduler/x/<?=$_year?>-<?=substr($data['section_id'], 0, 6)?>"><?=substr($data['section_id'], 0, 6)?></a><?=substr($data['section_id'], 6)?></td>
<td class="schedule" style="border-width: 1px; border-color: #aaaaaa; border-bottom-style: solid; border-right-style: solid; width: 25%;"><?=get_room($data['room_number'])?></td>
<td class="schedule" style="border-width: 1px; border-color: #aaaaaa; border-bottom-style: solid; border-right-style: solid; width: 25%;"><?=htmlentities($data['class_name'])?></td>
<td class="schedule" style="border-width: 1px; border-color: #aaaaaa; border-bottom-style: solid; width: 25%;"><a href="/scheduler/t/<?=$data['teacher_id']?>"><?=htmlentities($data['teacher_name'])?></a></td></tr>
<?php
/*if ($data['teacher_name'] == "" || $data['class_name'] == "")
	{
		echo "<tr><td colspan=\"4\"><em>Data for this class is incomplete.  <a href=\"/scheduler/c/$data[class_id]\">Fill it in.</a></em></td></tr>\n";
	}
*/
	$classmates_query = mysql_query("SELECT `student_name`, students.student_id, `want_to_change` FROM `schedules` INNER JOIN `students` ON schedules.student_id = students.student_id WHERE `class_id` = '$data[class_id]' ORDER BY `student_name` ASC");

	for($i = 0; $data2 = mysql_fetch_assoc($classmates_query); $i++)
	{
		if ($i % 4 == 0)
			echo "<tr>";
		echo "<td class=\"schedule\"><a href=\"/scheduler/s/$data2[student_id]\">$data2[student_name]";
		if ($data2['want_to_change'] == 1)
			echo " <strong>(want switch)</strong>";
		echo "</a></td>";
		if ($i % 4 == 3)
			echo "</tr>\n";
	}
	if ($i % 4 != 0)
		echo "</tr>\n";

	echo "<tr><td colspan=\"4\" style=\"border-top: 1px #aaaaaa solid; text-align:right; font-size:x-small;\"><em><a href=\"/scheduler/c/$data[class_id]\">Edit data for this class</a></em></td></tr>\n";
	
	echo "</table>\n\n";

	$last_period = $data['period'];	
}

echo "</td></tr>\n</table>\n";

footer();
?>
