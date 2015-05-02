<?php
  /**/
	if (!$_GET || !array_key_exists('key',$_GET) || $_GET['key'] != 'GET_KEY') {
		die('FAILURE: Incorrect/missing validation key.');
	}
	$base = './roblox-bots-master';
	include_once $base.'/Includes/getRoles.php';
	include_once $base.'/Includes/login.php';
	include_once $base.'/Includes/getPostData.php';
	include_once $base.'/changeRank.php';
	include_once $base.'/shout.php';
	libxml_use_internal_errors(true); // Hide DOMDocument warnings (though your errors should be turned off anyways)
	$group = 18; // Change this to your group ID
	$cookieTime = $base.'/Private/cookieTime.txt';
	if (!file_exists($cookieTime)) {
		file_put_contents($cookieTime,0);
	}
	$cookie = $base.'/Private/cookie';
	if (time()-file_get_contents($cookieTime) > 86400) {
		login($cookie,'username','password');
		file_put_contents($cookieTime,time());
	}
	$data = getPostData(true);
	if (!$data || !array_key_exists('Validate',$data) || $data['Validate'] != 'POST_KEY') {
		die('FAILURE: Incorrect/missing validation key.');
	}
	switch($data['Action']) {
		case 'setRank':
			list($ranks,$roles) = getRoleSets($group);
			echo updateRank($data,$group,$data['Parameter1'],$data['Parameter2'],$cookie,$ranks,$roles,9,$base.'/Private/gxcsrf.txt');
			break;
		case 'shout':
			echo shout($data,$cookie,$group,$data['Parameter1']);
			break;
		default:
			die('No action!');
	}
?>
