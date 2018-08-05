<?php

$student_id = intval($_GET['student_id']);
$teacher_id = intval($_GET['teacher_id']);
$course_id = $_GET['course_id'];

if ($student_id != 0)
{
	header("Location: /scheduler/s/$student_id");
}
else if ($teacher_id != 0)
{
	header("Location: /scheduler/t/$teacher_id");
}
else if (preg_match("/^[0-9]{4}-[a-zA-Z0-9]{6}$/", $course_id))
{
	header("Location: /scheduler/x/$course_id");
}
else 
{
	header("Location: /scheduler/");
}

exit;
?>
