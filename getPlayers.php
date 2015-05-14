<?php
	/*
	
	This gets all the usernames and userIds of all users of a specific rank from a specific group (and exports them in json format).
	
	I want to give a really special thanks to Casualist for helping me fix a critical bug that occured with certain groups.
	The first request would error but ONLY FOR CERTAIN GROUPS, which is what threw me off so much. I didn't know what would be different on different group pages (I'm still not sure)
	With the help of Casualist's working bot I tracked the problem down to a SINGLE EXTRA INPUT that was being picked up by getFullPostArray (It also requires a UserAgent).
	The reason it exists on some pages and not others I still do not know.
	
	I was also able to implement delta downloading using his example so that it wouldn't be redownloading the entire page every request, just the stuff that changed.
	You would think that makes a difference but its such a small amount of data it doesn't actually make a difference (and you aren't loading the images, obviously).
	It might actually make it slower but I won't revert unless I'm sure.
	
	*/

	//header('Content-type: text/plain');
	include_once 'Includes/getRoles.php';
	include_once 'Includes/getPostArray.php';
	libxml_use_internal_errors(true); // Hide DomDocument parse warnings
	set_time_limit(0); // May take a while, don't want it to time out!
	$raw = array_key_exists('raw',$_GET) && $_GET['raw'] == 'false' ? false : true; // (Default to true)
	function getPlayersOnPage($html,$array,$full=false) {
		$doc = new DOMDocument();
		$doc->loadHTML($html);
		$find = new DomXPath($doc);
		if ($full) {
			// So that if we're getting players from the full page we don't take players from the clan
			$query = "//div[contains(@id,'ctl00_cphRoblox_rbxGroupRoleSetMembersPane_GroupMembersUpdatePanel')]//span[contains(@class,'Name')]//a[@href]";
			// Find: Div with particular ID, spans that have the attribute "Name" in that div, and links with href attributes.
		} else {
			$query = "//span[contains(@class,'Name')]//a[@href]";
		}
		$nodes = $find->query($query);
		foreach ($nodes as $node) {
			preg_match('#\d+#',$node->getAttribute('href'),$matches);
			// ..User.aspx?ID=(number)
			array_push($array,array($node->textContent => (int)$matches[0]));
		}
		return $array;
	}
	function getPlayers($ranks,$raw,$group,$rank) {
		$players = array();
		$role = getRoleSet($ranks,$rank);
		$start = time();
		$url = "http://www.roblox.com/Groups/group.aspx?gid=$group";
		$curl = curl_init($url);
		curl_setopt_array($curl,array(
			CURLOPT_RETURNTRANSFER => true
		));
		$response = curl_exec($curl);
		$nextPost = getFullPostArray(substr($response,curl_getinfo($curl,CURLINFO_HEADER_SIZE)),
			array(
				'ctl00$cphRoblox$rbxGroupRoleSetMembersPane$dlRolesetList' => $role
			)
		);
		unset($nextPost['ctl00$cphRoblox$GroupSearchBar$SearchButton']); // This thing is the devil, it is the only reason this script didn't work on some groups before
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
		// Do the first page manually because it is a full page (not a partial)
		$players = getPlayersOnPage($response,$players,true);
		$nextPost = getFullPostArray($response,
			array(
				'__ASYNCPOST' => 'true',
				'ctl00$cphRoblox$rbxGroupRoleSetMembersPane$dlRolesetList' => $role,
				'ctl00$cphRoblox$rbxGroupRoleSetMembersPane$dlUsers_Footer$ctl01$PageTextBox' => 2
			)
		);
		unset($nextPost['ctl00$cphRoblox$GroupSearchBar$SearchButton']);
		curl_setopt_array($curl,array(
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_POST => true,
			CURLOPT_USERAGENT => 'Mozilla', // For some reason I have to do this...
			CURLOPT_POSTFIELDS => $nextPost
		));
		$response = curl_exec($curl);
		for ($i = 2; $i <= $pages; $i++) {
			$players = getPlayersOnPage($response,$players);
			preg_match('#\|__VIEWSTATE\|(.*?)\|.*\|__EVENTVALIDATION\|(.*?)\|#',$response,$inputs);
			$nextPost = getFullPostArray($response,
				array(
					'__VIEWSTATE' => $inputs[1],
					'__EVENTVALIDATION' => $inputs[2],
					'__ASYNCPOST' => 'true',
					'ctl00$cphRoblox$rbxGroupRoleSetMembersPane$dlRolesetList' => $role,
					'ctl00$cphRoblox$rbxGroupRoleSetMembersPane$dlUsers_Footer$ctl01$PageTextBox' => $i+1 // Next page
				)
			);
			unset($nextPost['ctl00$cphRoblox$GroupSearchBar$SearchButton']); // BURN IT
			curl_setopt_array($curl,array(
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_POST => true,
				CURLOPT_POSTFIELDS => $nextPost
			));
			$response = curl_exec($curl);
		}
		if (!$raw) {
			echo 'Get time: '.(time()-$start).' seconds<br>Players: '.count($players).'<br><br>';
		}
		return $players;
	}
	if (isset($_GET['group'])) {
		$group = $_GET['group'];
		list($ranks,$roles) = getRoleSets($group);
	}
	if (isset($_GET['getAll'])) {
		$group = $_GET['getAll'];
		list($ranks,$roles) = getRoleSets($_GET['getAll']);
		$all = array();
		foreach ($ranks as $rank=>$id) {
			$all = array_merge($all,getPlayers($ranks,$raw,$group,$rank));
		}
		echo json_encode($all);
	} else if (isset($_GET['rank'])) {
		echo json_encode(getPlayers($ranks,$raw,$group,$_GET['rank']));
	}
?>
