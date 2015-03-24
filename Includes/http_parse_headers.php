<?php
  if (!function_exists('http_parse_headers')) {
		function http_parse_headers ($raw_headers) {
			$headers = [];
			
			foreach (explode("\n", $raw_headers) as $i => $h) {
				$h = explode(':', $h, 2);
				
				if (isset($h[1])) {
					$headers[$h[0]] = trim($h[1]);
				}
			}
			
			return $headers;
		}
	}
?>
