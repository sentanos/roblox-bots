<?php
	// For getting post data from ROBLOX that might be gzipped/json encoded.
	function getPostData($json = false) {
		$post = file_get_contents('php://input');
		$data = (ord(substr($post,0,1)) == 31 ? gzinflate(substr($post,10,-8)) : $post); // FULL CREDIT TO VOILIAX FOR THIS LINE, I LITERALLY JUST COPIED IT OFF HIS FILE
		return $json ? json_decode($data,true) : $data; // Assumed that the json is assosciative (string keys)
	}
?>
