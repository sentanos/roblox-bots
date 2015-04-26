<?php
	/*
	
	This is the only API that I decided not to offer completed automated and immediate support without configuration.
	The reason is because it would require a lot more value inputs from the user for a small setting (though entirely possible).
	I considered that exile won't be used that often anyways. Considering, I thought it would be better to let the user configure.
	
	I'm talking about senderRoleSetId, you have to input that manually. This single API is the only case where you will have to do this type of configuration.
	ROBLOX's reasons for even requiring it I do not know (personal guess: coder's convenience).
	Other than that I think ROBLOX did a good job making this API - very straightforward excluding that problem.
	
	Anyways, it's not that hard to get it. Just get the roleSet ID of the rank the logged in user is and pass that.
	Personally, I wouldn't even put it as an argument, just directly into the post array below.
	
	*/
	include_once 'Includes/http_parse_headers.php';
	function exile($cookie,$group,$senderRoleSetId,$userId,$deletePosts = false,$save='../Private/excsrf.txt') {
		if (file_exists($save)) {
			$xcsrf = file_get_contents($save);
		} else {
			$xcsrf = '';
		}
		$curl = curl_init('http://www.roblox.com/My/Groups.aspx/ExileUserAndDeletePosts');
		$post = array(
			'userId' => $userId,
			'deleteAllPostsOption' => $deletePosts,
			'rolesetId' => $senderRoleSetId,
			'selectedGroupId' => $group
		);
		curl_setopt_array($curl,array(
			CURLOPT_POST => true,
			CURLOPT_POSTFIELDS => json_encode($post),
			CURLOPT_HEADER => true,
			CURLOPT_HTTPHEADER => array(                                                           
				"X-CSRF-TOKEN: $xcsrf",
				'Content-Length: '.strlen(json_encode($post)),
				'Content-Type: application/json; charset=utf-8'
			),
			CURLOPT_COOKIEFILE => $cookie,
			CURLOPT_COOKIEJAR => $cookie,
			CURLOPT_RETURNTRANSFER => true
		));
		$response = curl_exec($curl);
		$headerSize = curl_getinfo($curl,CURLINFO_HEADER_SIZE);
		$responseCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		if ($responseCode != 200) {
			if ($responseCode == 403) { // 403 XCSRF Token Validation Failed
				$header = http_parse_headers2(substr($response,0,$headerSize));
				$xcsrf = $header['X-CSRF-TOKEN'];
				file_put_contents($save,$xcsrf);
				return exile($cookie,$group,$senderRoleSetId,$userId,$deletePosts,$save);
			}
		}
		$delete = $deletePosts ? 'deleted' : 'did not delete';
		return "Exiled user $userId, $delete posts.";
	}
?>
