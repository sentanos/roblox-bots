<?php
	error_reporting(E_ALL & ~E_WARNING);
	//header('Content-type: text/plain');
	set_time_limit(0);
	$group = 18;
	/*$ranks = array(
		0 => 262,
		1 => 202,
		2 => 7202188,
		3 => 155451,
		4 => 154905,
		5 => 125,
		6 => 3691688,
		7 => 3691687,
		8 => 158913,
		9 => 6900080,
		10 => 155436,
		12 => 7488905,
		13 => 8347557,
		14 => 7488907,
		15 => 155871,
		16 => 7582704,
		255 => 53
	);*/
	/*$ranks = array();
	$roles = json_decode(file_get_contents("http://www.roblox.com/api/groups/$group/RoleSets/"),true);
	foreach($roles as $role => $array) {
		$ranks[$array['Rank']] = $array['ID'];
	}*/
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
		//return $preDefined;
		//substr($response,curl_getinfo($curl,CURLINFO_HEADER_SIZE))
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
			CURLOPT_RETURNTRANSFER => true,
			//CURLOPT_USERAGENT => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_6_8) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2272.104 Safari/537.36'
		));
		$response = curl_exec($curl);
		$nextPost = getPostArray(substr($response,curl_getinfo($curl,CURLINFO_HEADER_SIZE)),
			array(
				//'ctl00$ScriptManager' => 'ctl00$cphRoblox$rbxGroupRoleSetMembersPane$GroupMembersUpdatePanel|ctl00$cphRoblox$rbxGroupRoleSetMembersPane$dlRolesetList',
				'__EVENTTARGET' => 'ctl00$cphRoblox$rbxGroupRoleSetMembersPane$dlRolesetList',
				'ctl00$cphRoblox$rbxGroupRoleSetMembersPane$dlRolesetList' => getRoleSet($rank),
				//'ctl00$cphRoblox$rbxGroupRoleSetMembersPane$dlUsers_Footer$ctl01$PageTextBox' => '1',
				//'ctl00$cphRoblox$rbxGroupRoleSetMembersPane$dlUsers_Footer$ctl01$HiddenInputButton' => ''
			)
		);
		// Set rank to search
		//curl_close($curl);
		//$curl = curl_init($url);
		//die(var_export($nextPost));
		curl_setopt_array($curl,array(
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_POST => true,
			CURLOPT_POSTFIELDS => $nextPost
		));
		$response = curl_exec($curl);
		//die($response);
		$doc = new DOMDocument();
		$doc->loadHTML($response);
		$find = new DomXPath($doc);
		foreach($find->query("(//div[contains(@id,'ctl00_cphRoblox_rbxGroupRoleSetMembersPane_dlUsers_Footer_ctl01_Div1')]//div[contains(@class,'paging_pagenums_container')])[1]")/* Find the number of pages */ as $node) {
			$pages = $node->textContent;
		}
		if (!isset($pages)) {
			$pages = 1;
		}
		//die('Pages: '.$pages);
		$start = time();
		for ($i = 1; $i <= $pages; $i++) {
			//die("i = $i, pages = $pages");
			$players = getPlayersOnPage($response,$players);
			//die(var_export($players));
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
