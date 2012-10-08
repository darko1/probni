<?php

include('MyMail.class.php');
include('DbMail.class.php');

 

if (!empty($_POST)){
	$db= new DbMail();
	$amail= new MyMail($_POST['To'],$_POST['From'],$_POST['Subject'],"<pre>".$_POST['Message']."</pre>");
	$amail->addCC($_POST['Cc']);
	$amail->addBCC($_POST['Bcc']);
	
	//$amail->AddAttachment('mail.zip');
	//$amail->AddAttachment('sendmail.php');
	$amail->RegAfterSend($db);
	$amail->send();
	 header('Location: maillog.php');
}
?>