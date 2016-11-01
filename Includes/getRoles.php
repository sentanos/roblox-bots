<?php
	function getRoleSets($group) {
		$ranks = array();
		$roles = json_decode(file_get_contents("http://www.roblox.com/api/groups/$group/RoleSets/"),true);
		foreach($roles as $role => $array) {
			$ranks[$array['Rank']] = $array['ID'];
		}
		return $ranks;
	}
	function getRoleSet($ranks,$rank) {
		foreach($ranks as $num => $roleset) {
			if ($rank == $num) {
				return $roleset;
			}
		}
	}
?>
