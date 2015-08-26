<?php
	/*
	
	This one is longer because ROBLOX doesn't have any type of API for handling join requests.
	The only way to do it is as a normal user would, by selecting a choice on the group admin page.
	That also means that you need to input a username instead of a userId (and this is the only case).
	
	EDIT: So apparently ROBLOX now does have a join request API...
	But you need a join request ID, WHICH YOU CAN ONLY GET FROM THE GROUP ADMIN PAGE ANYWYAS.
	In fact, it's actually more complicated and long now - two big parts have to be included.
	Way to go, ROBLOX.
	
	*/
	include_once 'Includes/getPostArray.php';
	include_once 'Includes/http_parse_headers.php';
	function handleJoinRequest($cookie,$group,$username,$choice/*Accept or Decline - No default here to make sure you know what you're doing*/,$save='hxcsrf.txt',$requestId=-1) {
		$xcsrf = file_exists($save) ? file_get_contents($save) : '';
		$url = "http://www.roblox.com/My/GroupAdmin.aspx?gid=$group";
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
			$nextPost = getPostArray($response,
				array(
					'ctl00$ctl00$cphRoblox$cphMyRobloxContent$JoinRequestsSearchBox' => $username,
					'ctl00$ctl00$cphRoblox$cphMyRobloxContent$JoinRequestsSearchButton' => 'Search'	
				)
			);
			curl_close($curl);
			$curl = curl_init($url);
			curl_setopt_array($curl,array(
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_POST => true,
				CURLOPT_POSTFIELDS => $nextPost,
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
		$curl = curl_init('http://www.roblox.com/group/handle-join-request');
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
