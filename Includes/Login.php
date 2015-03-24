function login($username,$password) {
		$login = array(
			'userName'        => $username,
			'password'        => $password,
			'isCaptchaOn'     => false,
			'challenge'       => '',
			'captchaResponse' => ''
		);
		$curl = curl_init('https://www.roblox.com/Services/Secure/LoginService.asmx/ValidateLogin');
		curl_setopt_array($curl,array(
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_POST => true,
			CURLOPT_POSTFIELDS => json_encode($login),
			CURLOPT_HTTPHEADER => array(
				'Content-Type: application/json; charset=utf-8'	
			),
			CURLOPT_COOKIEFILE => $cookie,
			CURLOPT_COOKIEJAR => $cookie
		));
		$response = curl_exec($curl);
		curl_close($curl);
		$responsearray = json_decode($response,true)['d'];
		if ($responsearray['IsValid'] == true) {
			return $response;
		} else {
			die('Login Failure! Error: '.$responsearray['Message']);
		}
	}
