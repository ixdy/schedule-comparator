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
