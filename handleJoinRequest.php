<?php
	include_once 'Includes/getPostArray.php';
	include_once 'Includes/http_parse_headers.php';
	function handleJoinRequest($cookie,$group,$username,$choice/*Accept or Decline - No default here to make sure you know what you're doing*/,$save='hxcsrf.txt',$requestId=-1) {
		$xcsrf = file_exists($save) ? file_get_contents($save) : '';
		$url = "https://www.roblox.com/My/GroupAdmin.aspx?gid=$group";
		switch($choice) {
			case 'Accept':
				$choiceNumber = 1;
				break;
			case 'Decline':
				$choiceNumber = 2;
				break;
			default:
				die('Invalid choice.');
		}
		if ($requestId === -1) { // This is so that if the function is being re called with the request ID already received you don't go through the whole process again (because it takes up a lot of time)
			$curl = curl_init($url);
			curl_setopt_array($curl,array(
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_COOKIEFILE => $cookie,
				CURLOPT_COOKIEJAR => $cookie
			));
			$response = curl_exec($curl);
			curl_close($curl);
			preg_match('#Roblox\.GroupAdmin\.InitializeGlobalVars\(.*".*", "(.*)", .*\)#', $response, $matches);
			$searchPath = $matches[1];
			$curl = curl_init("http://www.roblox.com$searchPath?groupId=$group&username=$username");
			curl_setopt_array($curl,array(
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_COOKIEFILE => $cookie,
				CURLOPT_COOKIEJAR => $cookie
			));
			$response = curl_exec($curl);
			$doc = new DOMDocument();
			$doc->loadHTML($response);
			$find = new DomXPath($doc);
			$nodes = $find->query("//span[contains(@class,'btn-control btn-control-medium accept-join-request')][1]");
			foreach($nodes as $node) {
				$requestId = $node->getAttribute('data-rbx-join-request');
			}
		}
		$curl = curl_init('https://www.roblox.com/group/handle-join-request');
		$post = array(
			'groupJoinRequestId' => $requestId,
			'accept' => $choiceNumber==1 ? true : false
		);
		curl_setopt_array($curl,array(
			CURLOPT_POST => true,
			CURLOPT_POSTFIELDS => json_encode($post),
			CURLOPT_HEADER => true,
			CURLOPT_HTTPHEADER => array(                                                           
				"X-CSRF-TOKEN: $xcsrf",
				'Content-Length: '.strlen(json_encode($post)),
				'Content-Type: application/json; charset=UTF-8'
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
				$header = http_parse_headers(substr($response,0,$headerSize));
				$xcsrf = $header['X-CSRF-TOKEN'];
				file_put_contents($save,$xcsrf);
				return handleJoinRequest($cookie,$group,$username,$choice,$save,$requestId);
			}
		}
		$text = $choiceNumber==1 ? 'ed' : 'd';
		return "$choice$text $username's join request.";
	}
?>
