<?php
require("./common.php");

if (preg_match("/^\d{4}$/", $_GET['year'])) {
	$_year = $_GET['year'];
}

header_("Main");

?>
<p>Welcome to the Schedule Comparator, a service of Jeffsweb.net, celebrating 13+ years!</p>
<p>Please be kind, as this is a bespoke web application from a bygone era. Please don't add false names, teachers, courses, or change other people's data - it isn't that amusing.</p>
<p>The Schedule Comparator will never ask you for your TJ username or password.  You should <strong>never</strong> give account information to anyone or enter your TJ username or password on <em>any</em> site save for those sites officially run by the school, such as the Intranet.</p>
<p><strong>New for 2018:</strong> the Schedule Comparator is <a href="https://github.com/ixdy/schedule-comparator/">now on GitHub</a>. Feel free to send me pull requests or file issues there. (If you discover how to compromise my ancient PHP, I'd also appreciate patches.)
<p>Problems, complaints, praise, or other feedback? Email me: <strong>jgrafton+scheduler</strong>, followed by the <strong>at sign</strong>, followed by <strong>gmail</strong>, followed by the <strong>first three letters</strong> of the word <strong>company</strong>.</p>
<p>
<a href="/scheduler/a/">Add a schedule</a>
</p>
<?
footer();
?>
