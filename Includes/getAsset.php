<?php
	// Downloads ROBLOX asset that can then be uploaded again
	function getAsset($asset) {
		$curl = curl_init("http://www.roblox.com/Asset/?id=$asset");
		curl_setopt_array($curl,array(
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTPHEADER => array(
				'Accept-Encoding: gzip'
			)
		));
		$response = curl_exec($curl);
		return gzdecode($response);
	}
?>
