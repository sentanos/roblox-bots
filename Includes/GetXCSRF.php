<?php
  function getToken() {
		$url = 'http://www.roblox.com/groups/api/change-member-rank';
		global $cookie;
		global $xcsrf;
		$curl = curl_init($url);
		curl_setopt_array($curl,array(
			CURLOPT_HEADER => true,
			CURLOPT_POST => true,
			CURLOPT_COOKIEFILE => $cookie,
			CURLOPT_COOKIEJAR => $cookie,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_HTTPHEADER => array(
				'Content-Length: 0'	
			)
		));
		$response = curl_exec($curl);
		$header = substr($response,0,curl_getinfo($curl,CURLINFO_HEADER_SIZE));
		curl_close($curl);
		$xcsrf = http_parse_headers($header)['X-CSRF-TOKEN'];
		return $xcsrf;
	}
?>
