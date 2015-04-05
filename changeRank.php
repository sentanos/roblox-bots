<?php
	/*
	
	RankLimit can be used to restrict people from changing the rank of people who aren't a low rank.
	Don't use it (don't pass anything as an argument) if you want this to be faster.
	
	I implemented the login and xcsrf getting by using Voiliax's concept - only getting it if it's invalid (much more efficient). 
	
	*/
	include_once'Includes/Login.php';
	include_once'Includes/GetRoles.php'
	include_once'Includes/http_parse_headers.php';
	function updateRank($userId,$rank,$xcsrf='',$cookie,$rankLimit=255) {
		if ($rankLimit && $rankLimit < 255) {
			$currentRank = (int)simplexml_load_file("http://www.roblox.com/Game/LuaWebService/HandleSocialRequest.ashx?method=GetGroupRank&playerid=$userId&groupid=$group"); // Get user's rank
			if ($rank > $rankLimit || $currentRank > $rankLimit) { // Check if the rank you are trying to change them to and their rank abide to the rank limit
				return "Settings restrict the system from changing any rank over $rankLimit.";
			}
		}
		$url = "http://www.roblox.com/groups/api/change-member-rank?groupId=$group&newRoleSetId=".getRoleSet($rank)."&targetUserId=$userId";
		$curl = curl_init($url);
		curl_setopt_array($curl,array(
			CURLOPT_HEADER => true,
			CURLOPT_HTTPHEADER => array(                                                          
				"X-CSRF-TOKEN: $xcsrf",
				'Content-Length: 0'
			),
			CURLOPT_POST => true,
			CURLOPT_COOKIEFILE => $cookie,
			CURLOPT_COOKIEJAR => $cookie,
			CURLOPT_RETURNTRANSFER => true
		));
		$response = curl_exec($curl);
		$headerSize = curl_getinfo($curl,CURLINFO_HEADER_SIZE);
		$header = http_parse_headers(substr($response,0,$headerSize));
		if ($header['Set-Cookie']) {
			login();
			return updateRank($userId,$rank,$xcsrf,$cookie,$rankLimit);
		} else if (curl_getinfo($curl, CURL_HTTP_CODE) == 403) {
			$xcsrf = $header['X-CSRF-TOKEN'];
			return updateRank($userId,$rank,$xcsrf,$cookie,$rankLimit);
		}
		$response = substr($response,$headerSize);
		curl_close($curl);
		if (json_decode($response,true)['success'] == false) {
			return 'Invalid promoting permissions.';
		} else {
			$current = getRoleSet($currentRank);
			$new = getRoleSet($rank);
			return "Successfully changed rank of user $userId from ". $roles[$current] .' to '. $roles[$new] .'.';
		}
	}
?>
