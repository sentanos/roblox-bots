<?php
	//header('Content-type: text/plain');
	set_time_limit(0);
	function getRoleSets($group) {
		$roles = json_decode(file_get_contents("http://api.roblox.com/groups/$group"),true)['Roles'];
		$ids = array();
		$ranks = array();
		$url = "http://www.roblox.com/Groups/group.aspx?gid=$group";
		$doc = new DOMDocument();
		$doc->loadHTMLFile($url);
		$find = new DomXPath($doc);
		$nodes = $find->query('//select[contains(@id,\'ctl00_cphRoblox_rbxGroupRoleSetMembersPane_dlRolesetList\')]//option[@value]');
		foreach ($nodes as $node) {
			$ids[$node->textContent] = $node->getAttribute('value');
		}
		foreach ($ids as $name => $id) {
			foreach ($roles as $array) {
				if ($name == $array['Name']) {
					$ranks[$array['Rank']] = $id;
				}
			}
		}
		return $ranks;
	}
	$ranks = getRoleSets($group);
	function getRoleSet($getrank) {
		global $ranks;
		foreach($ranks as $rank => $roleset) {
			if ($getrank == $rank) {
				return $roleset;
			}
		}
	}
	function getPostArray($html,$preDefined) {
		$doc = new DOMDocument();
		$doc->loadHTML($html);
		$find = new DomXPath($doc);
		$vars = $find->query('//input[@name]'); // Find all inputs with the "name" attribute
		$newPost = $preDefined; // Put in predefined
		foreach($vars as $var) {
			if (!array_key_exists($var->getAttribute('name'),$newPost)/* Make sure the input isn't already set by predefined */) {
				$newPost[$var->getAttribute('name')] = $var->getAttribute('value');
			}
		}
		return $newPost;
	}
	function nextPage($curl,$response) {
		$nextPost = getPostArray(substr($response,curl_getinfo($curl,CURLINFO_HEADER_SIZE)),
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
	function getPlayers($rank) {
		global $group;
		$players = array();
		$url = "http://www.roblox.com/Groups/group.aspx?gid=$group";
		$curl = curl_init($url);
		curl_setopt_array($curl,array(
			CURLOPT_RETURNTRANSFER => true
		));
		$response = curl_exec($curl);
		$nextPost = getPostArray(substr($response,curl_getinfo($curl,CURLINFO_HEADER_SIZE)),
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
			$nextPost = getPostArray(substr($response,curl_getinfo($curl,CURLINFO_HEADER_SIZE)),
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
		echo 'Get time: ' . (time()-$start) . ' seconds<br>Players: '. count($players) .'<br><br>';
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
			$all = array_merge($all,getPlayers($rank));
		}
		echo json_encode($all);
	} else if (isset($_GET['rank'])) {
		echo json_encode(getPlayers($_GET['rank']));
	}
?>
