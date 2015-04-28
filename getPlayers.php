<?php
	/*
	
	This gets all the usernames and userIds of all users of a specific rank from a specific group (and exports them in json format).
	It works for the most part, but for some reason will not work with all groups.
	When trying to use this on specific groups ROBLOX will error on the first request.
	[LINE 72]
	
	For example:
	/getPlayers.php?group=18&rank=255
	works
	
	/getPlayers.php?group=1101003&rank=255
	does not work (there is an owner)
	
	I debugged forever and couldn't figure it out, only track it down to where it was happening, any help would be appreciated!
	
	*/

	//header('Content-type: text/plain');
	include_once 'Includes/getRoles.php';
	include_once 'Includes/getPostArray.php';
	libxml_use_internal_errors(true); // Hide DomDocument parse warnings
	set_time_limit(0); // May take a while, don't want it to time out!
	$raw = isset($_GET['raw']) && $_GET['raw'] ? true : false;
	function nextPage($curl,$response) {
		$nextPost = getFullPostArray(substr($response,curl_getinfo($curl,CURLINFO_HEADER_SIZE)),
			array(
				'__EVENTTARGET' => 'ctl00$cphRoblox$rbxGroupRoleSetMembersPane$dlUsers_Footer$ctl02$ctl00'
			)
		);
		curl_setopt_array($curl,array(
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_POST => true,
			CURLOPT_POSTFIELDS => $nextPost
		));
		return curl_exec($curl);
	}
	function getPlayersOnPage($html,$array) {
		$doc = new DOMDocument();
		$doc->loadHTML($html);
		$find = new DomXPath($doc);
		$nodes = $find->query("//div[contains(@id,'ctl00_cphRoblox_rbxGroupRoleSetMembersPane_GroupMembersUpdatePanel')]//span[contains(@class,'Name')]//a[@href]");
		// Find: Div with particular ID, spans that have the attribute "Name" in that div, and links with href attributes.
		foreach ($nodes as $node) {
			preg_match('#\d+#',$node->getAttribute('href'),$matches);
			// ..User.aspx?ID=(number)
			array_push($array,array($node->textContent => (int)$matches[0]));
		}
		return $array;
	}
	function getPlayers($raw,$group,$rank) {
		$players = array();
		$url = "http://www.roblox.com/Groups/group.aspx?gid=$group";
		$curl = curl_init($url);
		curl_setopt_array($curl,array(
			CURLOPT_RETURNTRANSFER => true
		));
		$response = curl_exec($curl);
		$nextPost = getFullPostArray(substr($response,curl_getinfo($curl,CURLINFO_HEADER_SIZE)),
			array(
				'__EVENTTARGET' => 'ctl00$cphRoblox$rbxGroupRoleSetMembersPane$dlRolesetList',
				'ctl00$cphRoblox$rbxGroupRoleSetMembersPane$dlRolesetList' => getRoleSet($rank)
			)
		);
		// Set rank to search
		curl_setopt_array($curl,array(
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_POST => true,
			CURLOPT_POSTFIELDS => $nextPost
		));
		$response = curl_exec($curl);
		$doc = new DOMDocument();
		$doc->loadHTML($response);
		$find = new DomXPath($doc);
		foreach($find->query("(//div[contains(@id,'ctl00_cphRoblox_rbxGroupRoleSetMembersPane_dlUsers_Footer_ctl01_Div1')]//div[contains(@class,'paging_pagenums_container')])[1]")/* Find the number of pages */ as $node) {
			$pages = $node->textContent;
		}
		if (!isset($pages)) {
			$pages = 1;
		}
		$start = time();
		for ($i = 1; $i <= $pages; $i++) {
			$players = getPlayersOnPage($response,$players);
			$nextPost = getFullPostArray(substr($response,curl_getinfo($curl,CURLINFO_HEADER_SIZE)),
				array(
					'ctl00$ScriptManager' => 'ctl00$cphRoblox$rbxGroupRoleSetMembersPane$GroupMembersUpdatePanel|ctl00$cphRoblox$rbxGroupRoleSetMembersPane$dlUsers_Footer$ctl01$HiddenInputButton',
					'ctl00$cphRoblox$rbxGroupRoleSetMembersPane$dlUsers_Footer$ctl01$PageTextBox' => $i+1 //Next page
				)
			);
			curl_setopt_array($curl,array(
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_POST => true,
				CURLOPT_POSTFIELDS => $nextPost
			));
			$response = curl_exec($curl);
		}
		if (!$raw) {
			echo 'Get time: ' . (time()-$start) . ' seconds<br>Players: '. count($players) .'<br><br>';
		}
		return $players;
	}
	if (isset($_GET['group'])) {
		$group = $_GET['group'];
		$ranks = getRoleSets($group);
	}
	if (isset($_GET['getAll'])) {
		$group = $_GET['getAll'];
		$ranks = getRoleSets($_GET['getAll']);
		$all = array();
		foreach ($ranks as $rank=>$id) {
			$all = array_merge($all,getPlayers($raw,$group,$rank));
		}
		echo json_encode($all);
	} else if (isset($_GET['rank'])) {
		echo json_encode(getPlayers($raw,$group,$_GET['rank']));
	}
?>
