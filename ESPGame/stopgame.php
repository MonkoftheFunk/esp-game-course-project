<?php
	include_once 'common/common.php';
	session_start();
	$conn = mysql_connect($cfg_dbhost,$cfg_dbuser,$cfg_dbpwd);
	if (!$conn)
	{
		die("Connecttion to data failed:".mysql_error());
	}
	mysql_select_db('esp');
	
	$parter = get_parter();
	
	$updateStopGame="update player set status='$_POST[operation]',partid=DEFAULT where userid='$_SESSION[USERNAME]';";
	mysql_query($updateStopGame);
	if ($parter){
		$notifyParterStop = "update player set status='4' where userid='$parter->userid';";
		mysql_query($notifyParterStop);
	}				 
	
	
	session_destroy();
?>