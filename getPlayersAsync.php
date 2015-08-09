<?php
	/*
	
	This gets all the usernames and userIds of all users of a specific rank from a specific group (and exports them in json format).
	
	[Get] Parameters:
		group: The group you want to index
		rank: Which rank you want to index
		getAll: Indexes all ranks of the group ID
		raw: Set this to false and it shows get time and player count
		online: Set this to true and it only gets online players
		limit: Limits number of players indexed PER RANK (won't limit everything in get all; -1 for no limit, which is default)
	Examples:
		/getPlayersAsync.php?group=18&rank=255
		/getPlayersAsync.php?getAll=6079&limit=100&raw=false
		[with whatever parameters you'd like]
	
	I want to give a really special thanks to Casualist for helping me fix a critical bug that occured with certain groups.
	The first request would error but ONLY FOR CERTAIN GROUPS, which is what threw me off so much. I didn't know what would be different on different group pages (I'm still not sure)
	With the help of Casualist's working bot I tracked the problem down to a SINGLE EXTRA INPUT that was being picked up by getFullPostArray.
	The reason it exists on some pages and not others I still do not know.
	
	I was also able to implement delta downloading using his example so that it wouldn't be redownloading the entire page every request, just the stuff that changed.
	You would think that'd make it faster but its such a small amount of data it doesn't actually make a difference (and you aren't loading the images, obviously).
	It might actually make it slower but I won't revert unless I'm sure.
	
	+ Changed the post array to a whitelist now instead of a blacklist, which solves A TON of problems and actually shortens the script.
	
	*This script uses asynchronous requests with curl_multi. This is a lifesaver, the script is exponentially faster but there is a catch:
	because curl_multi sends multiple requests in a single thread it can eat up a LOT of memory. To prevent PHP from running out of memory, I made a limit to how many requests can be sent at once with curl_multi.
	This variable (multiLimit) may be set differently on different servers. If your server has a lot of memory you can set it higher. 
	On one of my servers I have to set it to 10 but on a better one I can set nearly up to 1000, experiment with changing it to find what's best for you - if you see errors and missing players that means it was too high for your server.
	
	A public demo is available on my site, it's pretty fast and can grab 10000 players in 15-20 seconds:
	http://roblobots.cf/getPlayersAsync.php
	
	*/
	include_once './roblox-bots-master/Includes/getRoles.php';
	include_once './roblox-bots-master/Includes/getPostArray.php';
	libxml_use_internal_errors(true); // Hide DomDocument parse warnings
	set_time_limit(0); // May take a while, don't want it to time out!
	$raw = array_key_exists('raw',$_GET) && $_GET['raw'] == 'false' ? false : true; // (Default to true)
	$online = array_key_exists('online',$_GET) && $_GET['online'] == 'true' ? true : false; // (Default to false)
	$limit = array_key_exists('limit',$_GET) ? $_GET['limit'] : -1; // (Default to -1, no limit)
	$multiLimit = 100;
	function getPlayersOnPage($html,$array,$limit,$online) {
		$doc = new DOMDocument();
		$doc->loadHTML($html);
		$find = new DomXPath($doc);
		$nodes = $find->query("//div[contains(@class,'Avatar')]");
		foreach ($nodes as $node) {
			if ($limit != -1 && count($array) >= $limit) {
				break;
			}
			$link = $find->query('a',$node)->item(0);
			$img = $find->query('span/img',$node)->item(0);
			if (!$online || $img->getAttribute('src') == '../images/online.png') {
				preg_match('#\d+#',$link->getAttribute('href'),$matches);
				// ..User.aspx?ID=(number)
				array_push($array,array($link->getAttribute('title') => (int)$matches[0]));
			}
		}
		return $array;
	}
	function getPlayers($ranks,$raw,$group,$rank,$limit,$online,$multiLimit) {
		$players = array();
		$role = getRoleSet($ranks,$rank);
		$start = time();
		$url = "http://www.roblox.com/Groups/group.aspx?gid=$group";
		$curl = curl_init($url);
		// Start off by just getting the page
		// We need the correct validation before sending other requests
		curl_setopt_array($curl,array(
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_USERAGENT => 'Mozilla' // For some reason I have to do this...
		));
		$response = curl_exec($curl);
		// Include __VIEWSTATE, __EVENTVALIDATION, and __VIEWSTATEGENERATOR in the next post array
		// Set the rank we want to search from while we're at it
		$nextPost = getPostArray($response,
			array(
				'ctl00$cphRoblox$rbxGroupRoleSetMembersPane$dlRolesetList' => $role,
				'ctl00$cphRoblox$rbxGroupRoleSetMembersPane$currentRoleSetID' => $role,
				'__ASYNCPOST' => 'true', // DELTA
			)
		);
		$vs = $nextPost['__VIEWSTATE'];
		$ev = $nextPost['__EVENTVALIDATION'];
		curl_setopt_array($curl,array(
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_POST => true,
			CURLOPT_POSTFIELDS => $nextPost
		));
		$response = curl_exec($curl);
		$doc = new DOMDocument();
		$doc->loadHTML($response);
		$find = new DomXPath($doc);
		// Do a dance to get the number of pages
		foreach($find->query("(//div[contains(@id,'ctl00_cphRoblox_rbxGroupRoleSetMembersPane_dlUsers_Footer_ctl01_Div1')]//div[contains(@class,'paging_pagenums_container')])[1]") as $node) {
			$pages = $node->textContent;
		}
		if (!isset($pages)) {
			$pages = 1;
		}
		curl_setopt_array($curl,array(
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_POST => true,
			CURLOPT_POSTFIELDS => $nextPost
		));
		$response = curl_exec($curl);
		curl_close($curl);
		$players = getPlayersOnPage($response,$players,$limit,$online);
		if ($pages > $multiLimit) {
			$maxml = ceil($pages/$multiLimit);
		} else {
			$maxml = 1;
		}
		for ($ml = 0; $ml < $maxml; $ml++) {
			if ($limit != -1 && count($players) >= $limit) {
				break;
			}
			$requests = array();
			$multi = curl_multi_init();
			
			$max = ($ml+1)*$multiLimit;
			if ($max > $pages) {
				$max = $pages-1;
			}
			
			for ($i = 1+$ml*$multiLimit; $i <= $max; $i++) {
				if ($limit != -1 && count($players) >= $limit) {
					break;
				}
				$nextPost = array(
					'__VIEWSTATE' => $vs,
					'__EVENTVALIDATION' => $ev,
					'__ASYNCPOST' => 'true',
					'ctl00$cphRoblox$rbxGroupRoleSetMembersPane$currentRoleSetID' => $role,
					'ctl00$cphRoblox$rbxGroupRoleSetMembersPane$dlUsers_Footer$ctl01$HiddenInputButton' => '', // For some reason this is required
					'ctl00$cphRoblox$rbxGroupRoleSetMembersPane$dlUsers_Footer$ctl01$PageTextBox' => $i+1 // Next page
				);
				$curl = curl_init($url);
				curl_setopt_array($curl,array(
					CURLOPT_RETURNTRANSFER => true,
					CURLOPT_POST => true,
					CURLOPT_POSTFIELDS => $nextPost,
					CURLOPT_USERAGENT => 'Mozilla'
				));
				array_push($requests,$curl);
				curl_multi_add_handle($multi,$curl);
			}
			
			do {
				curl_multi_exec($multi, $running);
				curl_multi_select($multi);
			} while ($running > 0);
			
			foreach($requests as $index => $request) {
				$data = curl_multi_getcontent($request);
				$players = getPlayersOnPage($data,$players,$limit,$online);
				curl_close($request);
				curl_multi_remove_handle($multi, $request);
			}
			
			curl_multi_close($multi);
		}
		
		if (!$raw) {
			echo 'Get time: '.(time()-$start).' seconds<br>Players: '.count($players).'<br><br>';
		}
		return $players;
	}
	if (array_key_exists('group',$_GET)) {
		$group = $_GET['group'];
		list($ranks,$roles) = getRoleSets($group);
	}
	if (array_key_exists('getAll',$_GET)) {
		$group = $_GET['getAll'];
		list($ranks,$roles) = getRoleSets($_GET['getAll']);
		$all = array();
		foreach ($ranks as $rank=>$id) {
			$all = array_merge($all,getPlayers($ranks,$raw,$group,$rank,$limit,$online,$multiLimit));
		}
		echo json_encode($all);
	} else if (array_key_exists('rank',$_GET)) {
		echo json_encode(getPlayers($ranks,$raw,$group,$_GET['rank'],$limit,$online,$multiLimit));
	}
?>
