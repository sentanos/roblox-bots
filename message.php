<?php
	include_once 'Includes/http_parse_headers.php';
	function message($cookie,$id,$subject='None',$body='None',$save='../Private/mxcsrf.txt') {
		if (file_exists($save)) {
			$xcsrf = file_get_contents($save);
		} else {
			$xcsrf = '';
		}
		$curl = curl_init('http://www.roblox.com/messages/send');
		$send = array(
			'subject' => $subject,
			'body' => $body,
			'recipientid' => $id,
			'cacheBuster' => time()
		);
		/* The reason it's sent in json is because I ran into problems where the body and subject would be switched in some cases when I wasn't using it
		I'm not actually sure if sending it in json makes a difference but I think it's better than just sending it a urlencoded array*/
		curl_setopt_array($curl,array(
			CURLOPT_HEADER => true,
			CURLOPT_HTTPHEADER => array(                                                          
				"X-CSRF-TOKEN: $xcsrf",
				'Content-Type: application/json'
			),
			CURLOPT_POST => true,
			CURLOPT_POSTFIELDS => json_encode($send),
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
		if ($json['success'] == true) {
			return "Sent message $subject to $id.";
		} else {
			$error = $json['shortMessage'];
			return "Error sending message $subject to $id: $error";
		}
	}
?>
