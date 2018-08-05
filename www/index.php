<?php
require("./common.php");

if (preg_match("/^\d{4}$/", $_GET['year'])) {
	$_year = $_GET['year'];
}

header_("Main");

?>
<!--<p style="font-weight:bold;">A little birdy has informed me that this year, TJ schedules may not be listing room numbers or teachers. This app has been updated accordingly. Now you will enter schedules by section ID, filling in any additional details you have in later stages (room numbers, course names, and teachers).</p>-->
<!--<p style="font-weight:bold;">Update 2009-09-02 11:21 PDT: I've learned that the schedules this year do not have full section IDs, and rather just course IDs, which are insufficiently specific. Feel free to use the period number to fill out the section IDs, though this will lead to false positives on who is in the same class. I'll try to have a workaround by this evening or tomorrow. Please address your complaints to the TJ administration who are withholding information from you.</p>
<p style="font-weight:bold;">Update 2009-09-02 12:37 PDT: Apparently schedules are updated on Intranet, complete with teacher listings. Your login may still work.</p>
<p style="font-weight:bold; color:#ff0000;">Update 2009-09-02 23:37 PDT: You can now set the room number for courses you know about on the "edit schedule" page. This should reduce collisions in common courses such as TA. Let me know if there are any lingering issues.</p>-->
<p>Welcome to the Schedule Comparator, a service of Jeffsweb.net, celebrating <strong>over five years</strong> in its current state!</p>
<p>As always, the <strong>don't be an ass</strong> rule is still in effect.  Please don't add false names, teachers, courses, or change other people's data - it isn't worth anyone's time.</p>
<p>The Schedule Comparator will never ask you for your TJ username or password.  You should <strong>never</strong> give account information to anyone or enter your TJ username or password on <em>any</em> site save for those sites officially run by the school, such as the Intranet.</p>
<p>Problems, complaints, praise, or other feedback? Email me: <strong>jgrafton+scheduler</strong>, followed by the <strong>at sign</strong>, followed by <strong>gmail</strong>, followed by the <strong>first three letters</strong> of the word <strong>company</strong>.</p>
<!--<p><strong>New for 2009-2010:</strong> Improved display of schedules. Schedule entry by section ID.</p>-->
<p>
<a href="/scheduler/a/">Add a schedule</a>
</p>
<?
footer();
?>
