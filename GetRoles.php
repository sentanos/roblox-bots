<?php
	/*
	
	ROBLOX actually has an API for getting group roles including their IDs...
	BUT IT'S BROKEN (found out the hard way).
	From all the groups I've seen this API will often miss one of the ranks; however, in case
	it's ever fixed here's a function that uses it:
	
	function getRoleSets($group) {
		$ranks = array();
		$roles = json_decode(file_get_contents("http://www.roblox.com/api/groups/$group/RoleSets/"),true);
		foreach($roles as $role => $array) {
			$ranks[$array['Rank']] = $array['ID'];
		}
		return $ranks;
	}
	
	This is much faster than the following one, if ROBLOX does fix their API you should use it.
	
	*/
	function getRoleSets($group) {
		$roles = json_decode(file_get_contents("http://api.roblox.com/groups/$group"),true)['Roles']; // First, get all the group roles.
		$ids = array();
		$ranks = array();
		$url = "http://www.roblox.com/Groups/group.aspx?gid=$group";
		$doc = new DOMDocument();
		$doc->loadHTMLFile($url); // Load up the group page (contains role ids)
		$find = new DomXPath($doc); // XPath is love, XPath is life
		$nodes = $find->query('//select[contains(@id,\'ctl00_cphRoblox_rbxGroupRoleSetMembersPane_dlRolesetList\')]//option[@value]'); // Get <select> that has a certain ID and then get <option>'s inside that select with a value value.
		foreach ($nodes as $node) {
			$ids[$node->textContent] = $node->getAttribute('value'); // For each option get its textContent (role name) and value attribute (role ID)
		}
		foreach ($ids as $name => $id) {
			foreach ($roles as $array) {
				if ($name == $array['Name']) {
					$ranks[$array['Rank']] = $id; // Iterate through 
				}
			}
		}
		return $ranks;
	}
?>
