<?php
	/*
	
	This logs in to a user and stores all cookies in a file (built-in feature of cURL). Just put in a path and it will create the file for you.
	After logging in just use this file as the COOKIEFILE and COOKIEJAR of other cURL requests.
	Make sure to put the correct details or it will go to captcha and you won't have any easy way of actually solving it.
	
	*/
	function login($cookie,$username,$password) {
		$login = array(
			'userName'        => $username,
			'password'        => $password,
			'isCaptchaOn'     => false,
			'challenge'       => '',
			'captchaResponse' => ''
		);
		$curl = curl_init('http://www.roblox.com/Services/Secure/LoginService.asmx/ValidateLogin'); // There are many links you can login from
		curl_setopt_array($curl,array(
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_POST => true,
			CURLOPT_POSTFIELDS => json_encode($login),
			CURLOPT_HTTPHEADER => array(
				'Content-Type: application/json; charset=utf-8'	
			),
			CURLOPT_COOKIEFILE => $cookie,
			CURLOPT_COOKIEJAR => $cookie // Actually save the cookies it's returning!
		));
		$response = curl_exec($curl);
		curl_close($curl);
		$responsearray = json_decode($response,true)['d'];
		if ($responsearray['IsValid'] == true) {
			return $responsearray['Message'];
		} else {
			return 'Login Failure! Error: '.$responsearray['Message']; // You can change this to die()
		}
	}
?>
