<?php
	include_once'Includes/GetPostArray.php';
	function shout($cookie,$group,$msg) {
		$url = "http://www.roblox.com/My/Groups.aspx?gid=$group";
		$curl = curl_init($url);
		curl_setopt_array($curl,array(
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_COOKIEFILE => $cookie,
			CURLOPT_COOKIEJAR => $cookie
		));
		$response = curl_exec($curl);
		$nextPost = getPostArray(substr($response,curl_getinfo($curl,CURLINFO_HEADER_SIZE)),
			array(
				'ctl00$cphRoblox$GroupStatusPane$StatusTextBox' => $msg,
				'ctl00$cphRoblox$GroupStatusPane$StatusSubmitButton' => 'Group Shout'	
			)
		);
		curl_close($curl);
		$curl = curl_init($url);
		curl_setopt_array($curl,array(
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_POST => true,
			CURLOPT_POSTFIELDS => $nextPost,
			CURLOPT_COOKIEFILE => $cookie,
			CURLOPT_COOKIEJAR => $cookie
		));
		if (curl_exec($curl)) {
			return "Shouted $msg.";
		}
		return 'Failure';
	}
?>
