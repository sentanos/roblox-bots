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
