<?php
	// This is simply an example file so that you know how to work everything (specifically logging in and cookies).
	include_once dirname(dirname(__FILE__)).'/Includes/getRoles.php';
	include_once dirname(dirname(__FILE__)).'/Includes/login.php';
	include_once dirname(dirname(__FILE__)).'/changeRank.php';
	libxml_use_internal_errors(true); // Hide DOMDocument warnings (though your errors should be turned off anyways)
	$group = 18; // Change this to your group ID
	$cookieTime = dirname(dirname(__FILE__)).'/Private/cookieTime.txt';
	if (!file_exists($cookieTime)) {
		file_put_contents($cookieTime,0);
	}
	$cookie = dirname(dirname(__FILE__)).'/Private/cookie';
	if (time()-file_get_contents($cookieTime) > 86400) {
		login($cookie,'Killer6199','mandie');
		file_put_contents($cookieTime,time());
	}
	list($ranks,$roles) = getRoleSets($group);
	echo updateRank($group,2470023,13,$cookie,$ranks,$roles);
	// Update rank in group $group of user 2470023 to rank 13 using cookie file $cookie, ranks array $ranks, and roles array $roles.
?>
