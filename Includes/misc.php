<?php
	// These are just a few miscellaneous ROBLOX APIs in php function form that you might want to use.
	function UsernameFromID($userId) {
		return json_decode(file_get_contents("http://api.roblox.com/users/$userId"),true)['Username'];
	}
	function IDFromUsername($username) {
		return json_decode(file_get_contents("http://api.roblox.com/users/get-by-username?username=$username"),true)['Id'];
	}
	function getRankInGroup($userId,$groupId) {
		return (int)simplexml_load_file("http://www.roblox.com/Game/LuaWebService/HandleSocialRequest.ashx?method=GetGroupRank&playerid=$userId&groupid=$groupId");
	}
	function fetchShout($cookie,$group) {
		// Log in for correct permissions
		$curl = curl_init("http://www.roblox.com/My/Groups.aspx?gid=$group");
		curl_setopt_array($curl,array(
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_COOKIEFILE => $cookie,
			CURLOPT_COOKIEJAR => $cookie
		));
		$response = curl_exec($curl);
		curl_close($curl);
		$doc = new DOMDocument();
		$doc->loadHTML($response);
		return $doc->getElementById('ctl00_cphRoblox_GroupStatusPane_StatusTextField')->textContent;
	}
?>
