<?php
	include_once 'common/common.php';
	session_start();
	$succ = FALSE;
	$labelid = $_GET['label'];
	$userid = $_SESSION['USERNAME'];
	$picid = $_SESSION['picid'];
	
	
	$conn = mysql_connect($cfg_dbhost,$cfg_dbuser,$cfg_dbpwd);
	if (!$conn)
	{
		die("Connecttion to data failed:".mysql_error());
	}
	mysql_select_db('esp');
	
	$addlabel = "insert into labelforgame values('$labelid','$userid','$picid');";
	$executeadd = mysql_query($addlabel);
	$partner = "SELECT * from player where userid = (select partid from player where userid = '$userid');";
	$executep = mysql_query($partner);
	if($executep){
		while($row = mysql_fetch_object($executep)){
			if($row -> status == 2){
				$getlabels = "SELECT * from labelforgame where player = '$row->userid'
				 and labelid = '$labelid' and picid = '$picid';";
				$executelabels = mysql_query($getlabels);
				if(mysql_num_rows($executelabels) <> 0){
					$succ = TRUE;
				}
			}
		}
	}
	
	mysql_close($conn);
	if ($succ) {
		$pairid=$_SESSION['pairid'];
		$db = new mysqli($cfg_dbhost,$cfg_dbuser,$cfg_dbpwd,$cfg_dbname);
		$picarry = get_pic($db);
		$insertquery = "insert into game(pairid,picid,status) values('$pairid','$picarry[picid]',0)";
		$db->query($insertquery);
		$sql="select @@IDENTITY as id";
		$result = $db->query($sql);
		$gameidarray = $result->fetch_array();
		$gameid = $gameidarray['id'];
		$updatepair="UPDATE gamepair SET currentgame = '$gameid' where id='$_SESSION[pairid]';";
		$db->query($updatepair);
		$updates="UPDATE game SET status = '1' where id='$_SESSION[gameid]';";
		$db->query($updates);
		$countsql = "select count(*) as c from label where picid='$_SESSION[picid]' and content = '$_GET[label]'";
		$count_result = $db->query($countsql);
		$temp = $count_result->fetch_assoc();
		$num = $temp['c'];
		if($num == 0){
			$insertsql = "insert into label values('$_SESSION[picid]','$_GET[label]',1)";
			$db->query($insertsql);
		}
		else {
			$updatesql = "update label set times = times + 1 where picid='$_SESSION[picid]' and content = '$_GET[label]'";
			$db->query($updatesql);
		}
		$num_result = $result->num_rows;
		$updates="UPDATE player SET status = '5' where userid='$_SESSION[partnerid]';";
		$_SESSION['picid'] = $picarry["picid"];
		$db->query($updates);
		echo json_encode(array('matched'=>'true',"url"=> $picarry["url"],"picid"=>$picarry["picid"],"gameid"=>$gameid));
	}
	else{
		echo json_encode(array('matched'=>'false'));
	}
?>