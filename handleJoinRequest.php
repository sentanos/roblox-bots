<?php
  function handleJoinRequest($username,$choice/*Accept or Decline*/) {
		global $group;
		global $cookie;
		$url = "http://www.roblox.com/My/GroupAdmin.aspx?gid=$group";
		switch($choice) {
			case 'Accept':
				$choiceNumber = 1;
				break;
			case 'Decline':
				$choiceNumber = 2;
				break;
			default:
				die('Invalid choice.');
		}
		$curl = curl_init($url);
		curl_setopt_array($curl,array(
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_COOKIEFILE => $cookie,
			CURLOPT_COOKIEJAR => $cookie
		));
		$response = curl_exec($curl);
		curl_close($curl);
		$nextPost = getPostArray(substr($response,curl_getinfo($curl,CURLINFO_HEADER_SIZE)),
			array(
				'ctl00$ctl00$cphRoblox$cphMyRobloxContent$JoinRequestsSearchBox' => $username,
				'ctl00$ctl00$cphRoblox$cphMyRobloxContent$JoinRequestsSearchButton' => 'Search'	
			)
		);
		$curl = curl_init($url);
		curl_setopt_array($curl,array(
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_POST => true,
			CURLOPT_POSTFIELDS => $nextPost,
			CURLOPT_COOKIEFILE => $cookie,
			CURLOPT_COOKIEJAR => $cookie
		));
		$response = curl_exec($curl);
		$nextPost = getPostArray(substr($response,curl_getinfo($curl,CURLINFO_HEADER_SIZE)),
			array(
				'ctl00$ctl00$cphRoblox$cphMyRobloxContent$lvGroupJoinRequests$ctrl0$ctl00$Button'.$choiceNumber => $choice	
			)
		);
		curl_setopt_array($curl,array(
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_POST => true,
			CURLOPT_POSTFIELDS => $nextPost,
			CURLOPT_COOKIEFILE => $cookie,
			CURLOPT_COOKIEJAR => $cookie
		));
		if (curl_exec($curl)) {
			curl_close($curl);
			logAction('Handle Join Request',$username,$choice);
			return "$username => $choice";
		}
		return 'Failure';
	}
?>
