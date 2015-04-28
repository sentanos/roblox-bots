<?php
	/*
	
	These are so that some requests are valid.
	The first one is a whitelist that only grabs required fields, the second gets everything it can find on the page.
	
	*/
	function getPostArray($html,$preDefined) {
		$doc = new DOMDocument();
		$doc->loadHTML($html);
		$postVars = array(
			'__VIEWSTATE',
			'__VIEWSTATEGENERATOR',
			'__EVENTVALIDATION'
		);
		$newPost = $preDefined;
		foreach($postVars as $var) {
			$newPost[$var] = $doc->getElementById($var)->getAttribute('value');
		}
		return $newPost;
	}
	function getFullPostArray($html,$preDefined) {
		$doc = new DOMDocument();
		$doc->loadHTML($html);
		$find = new DomXPath($doc);
		$vars = $find->query('//input[@name]'); // Find all inputs with the "name" attribute
		$newPost = $preDefined; // Put in predefined
		foreach($vars as $var) {
			if (!array_key_exists($var->getAttribute('name'),$newPost)/* Make sure the input isn't already set by predefined */) {
				$newPost[$var->getAttribute('name')] = $var->getAttribute('value');
			}
		}
		return $newPost;
	}
?>
