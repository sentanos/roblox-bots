<?php
	/*
	
	ROBLOX actually has an API for getting group roles including their IDs...
	BUT IT'S BROKEN (found out the hard way).
	From all the groups I've seen this API will often miss one of the ranks; however, in case
	it's ever fixed here's a function that uses it:
	
	function getRoleSets($group) {
		$ranks = array();
		$roles = json_decode(file_get_contents("https://www.roblox.com/api/groups/$group/RoleSets/"),true);
		foreach($roles as $role => $array) {
			$ranks[$array['Rank']] = $array['ID'];
		}
		return $ranks;
	}
	
	This is much faster than the following one, if ROBLOX does fix their API you should use it.
	It returns both the regular rank array and another array that has the rank matched with its role.
	To get this do:
	list($ranks,$roles) = getRoleSets($group);
	
	If you want the best performance put in the ranks manually.
	
	*/
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
		return array($ranks,array_flip($ids));
	}
	function getRoleSet($ranks,$rank) {
		foreach($ranks as $num => $roleset) {
			if ($rank == $num) {
				return $roleset;
			}
		}
	}
?>
