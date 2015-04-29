<?php
	include_once 'Includes/http_parse_headers.php';
	function message($cookie,$id,$subject,$body,$save='../Private/mxcsrf.txt') {
		$xcsrf = file_exists($save) ? file_get_contents($save) : '';
		$curl = curl_init('http://www.roblox.com/messages/send');
		$send = array(
			'subject' => $subject,
			'body' => $body,
			'recipientid' => $id,
			'cacheBuster' => time()
		);
		curl_setopt_array($curl,array(
			CURLOPT_HEADER => true,
			CURLOPT_HTTPHEADER => array(                                                          
				"X-CSRF-TOKEN: $xcsrf"
			),
			CURLOPT_POST => true,
			CURLOPT_POSTFIELDS => $send,
			CURLOPT_COOKIEFILE => $cookie,
			CURLOPT_COOKIEJAR => $cookie,
			CURLOPT_RETURNTRANSFER => true
		));
		$response = curl_exec($curl);
		$headerSize = curl_getinfo($curl,CURLINFO_HEADER_SIZE);
		$responseCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		if ($responseCode != 200) {
			if ($responseCode == 403) { // 403 XCSRF Token Validation Failed
				$header = http_parse_headers(substr($response,0,$headerSize));
				$xcsrf = $header['X-CSRF-TOKEN'];
				file_put_contents($save,$xcsrf);
				return message($cookie,$id,$body,$subject,$save);
			}
		}
		$json = json_decode(substr($response,$headerSize),true);
		if ($json['success']) {
			return "Sent message $subject to $id.";
		} else {
			$error = $json['shortMessage'];
			return "Error sending message $subject to $id: $error";
		}
	}
?>
