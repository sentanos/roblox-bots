<?php
	/*
	
	This one is longer because ROBLOX doesn't have any type of API for handling join requests.
	The only way to do it is as a normal user would, by selecting a choice on the group admin page.
	That also means that you need to input a username instead of a userId (and this is the only case).
	
	*/
	function handleJoinRequest($cookie,$group,$username,$choice/*Accept or Decline - No default here to make sure you know what you're doing*/) {
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
		$text = $choiceNumber==1 ? 'ed' : 'd';
		return "$username's join request $text.";
	}
?>
