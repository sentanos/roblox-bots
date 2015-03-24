<?php
	/*
	
	Get the XCSRF token required to make rank change requests.
	Update this every single time you make one.
	
	*/
	include'http_parse_headers.php'; // Requires http_parse_headers
	function getToken($cookieFile) {
		$url = 'http://www.roblox.com/groups/api/change-member-rank'; // Change rank url.
		$curl = curl_init($url);
		curl_setopt_array($curl,array(
			CURLOPT_HEADER => true,
			CURLOPT_POST => true,
			CURLOPT_COOKIEFILE => $cookieFile, // I'm not sure if logging in is actually required for getting your token - I will look into it.
			CURLOPT_COOKIEJAR => $cookieFile,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_HTTPHEADER => array(
				'Content-Length: 0'
			)
		));
		$response = curl_exec($curl);
		$header = substr($response,0,curl_getinfo($curl,CURLINFO_HEADER_SIZE)); // Get the response header
		curl_close($curl);
		$xcsrf = http_parse_headers($header)['X-CSRF-TOKEN']; // Get the X-CSRF-TOKEN in the response header.
		return $xcsrf;
	}
?>
