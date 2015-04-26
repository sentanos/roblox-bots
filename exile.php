<?php
  function exile($userId,$deletePosts = false) {
		global $group;
		global $cookie;
		global $xcsrf;
		$curl = curl_init('http://www.roblox.com/My/Groups.aspx/ExileUserAndDeletePosts');
		$post = array(
			'userId' => $userId,
			'deleteAllPostsOption' => $deletePosts,
			'rolesetId' => 5023364,
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
		if (curl_exec($curl)) {
			logAction('Exile',$userId,'Delete posts: '.$deletePosts);
			return "Attempted to exile user $userId.";
		}
		return 'Failure';
	}
?>
