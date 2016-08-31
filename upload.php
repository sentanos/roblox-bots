<?php
	/*
	
	This is for uploading assets either by raw XML data or a string for uploading a LocalScript (ease for Script Builders).
	You can use this in conjunction with getAsset to upload other (untrusted) assets into your model and ultimately your place.
	Just use:
	include_once'Private/getAsset.php';
	
	This returns assetVersionId, so if you're trying to put the model get the return of the function and do:
	game:GetService'InsertService':LoadAssetVersion(assetVersionId) -- returns the model
	
	*/
	function upload($cookie,$asset,$data,$local = false) {
		if ($local) {
			$send = "<roblox xmlns:xmime='http://www.w3.org/2005/05/xmlmime' xmlns:xsi='http://www.w3.org/2001/XMLSchema-instance' xsi:noNamespaceSchemaLocation='http://www.roblox.com/roblox.xsd' version='4'>
						<External>null</External>
						<External>nil</External>
						<Item class='LocalScript'>
							<Properties>
								<bool name='Disabled'>false</bool>
								<Content name='LinkedSource'><null></null></Content>
								<string name='Name'>Local</string>
								<ProtectedString name='Source'><![CDATA[$data]]></ProtectedString>
							</Properties>
						</Item>
					</roblox>";
		} else {
			$send = $data;
		}
		$curl = curl_init("https://www.roblox.com/Data/Upload.ashx?assetid=$asset");
		curl_setopt_array($curl,array(
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_POST => true,
			CURLOPT_POSTFIELDS => $send,
			CURLOPT_HTTPHEADER => array(
				'Content-Type: application/xml; charset=utf-8',
				'Content-Length: '.strlen($send)
			),
			CURLOPT_COOKIEFILE => $cookie,
			CURLOPT_COOKIEJAR => $cookie
		));
		return curl_exec($curl);
	}
?>
