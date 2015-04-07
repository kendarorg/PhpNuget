<?php 
  if (!function_exists('http_response_code')) {
        function http_response_code($code = NULL) {

            if ($code !== NULL) {

                switch ($code) {
                    case 100: $text = 'Continue'; break;
                    case 101: $text = 'Switching Protocols'; break;
                    case 200: $text = 'OK'; break;
                    case 201: $text = 'Created'; break;
                    case 202: $text = 'Accepted'; break;
                    case 203: $text = 'Non-Authoritative Information'; break;
                    case 204: $text = 'No Content'; break;
                    case 205: $text = 'Reset Content'; break;
                    case 206: $text = 'Partial Content'; break;
                    case 300: $text = 'Multiple Choices'; break;
                    case 301: $text = 'Moved Permanently'; break;
                    case 302: $text = 'Moved Temporarily'; break;
                    case 303: $text = 'See Other'; break;
                    case 304: $text = 'Not Modified'; break;
                    case 305: $text = 'Use Proxy'; break;
                    case 400: $text = 'Bad Request'; break;
                    case 401: $text = 'Unauthorized'; break;
                    case 402: $text = 'Payment Required'; break;
                    case 403: $text = 'Forbidden'; break;
                    case 404: $text = 'Not Found'; break;
                    case 405: $text = 'Method Not Allowed'; break;
                    case 406: $text = 'Not Acceptable'; break;
                    case 407: $text = 'Proxy Authentication Required'; break;
                    case 408: $text = 'Request Time-out'; break;
                    case 409: $text = 'Conflict'; break;
                    case 410: $text = 'Gone'; break;
                    case 411: $text = 'Length Required'; break;
                    case 412: $text = 'Precondition Failed'; break;
                    case 413: $text = 'Request Entity Too Large'; break;
                    case 414: $text = 'Request-URI Too Large'; break;
                    case 415: $text = 'Unsupported Media Type'; break;
                    case 500: $text = 'Internal Server Error'; break;
                    case 501: $text = 'Not Implemented'; break;
                    case 502: $text = 'Bad Gateway'; break;
                    case 503: $text = 'Service Unavailable'; break;
                    case 504: $text = 'Gateway Time-out'; break;
                    case 505: $text = 'HTTP Version not supported'; break;
                    default:
                        exit('Unknown http status code "' . htmlentities($code) . '"');
                    break;
                }

                $protocol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0');

                header($protocol . ' ' . $code . ' ' . $text);

                $GLOBALS['http_response_code'] = $code;

            } else {

                $code = (isset($GLOBALS['http_response_code']) ? $GLOBALS['http_response_code'] : 200);

            }

            return $code;

        }
    }


require_once(dirname(__FILE__)."/../../root.php");
require_once(__ROOT__."/settings.php");
require_once(__ROOT__."/inc/api_users.php");
require_once(__ROOT__."/inc/commons/url.php");
require_once(__ROOT__."/inc/commons/http.php");
require_once(__ROOT__."/inc/api_nuget.php");




function respondBatch($batch) {
	$boundary = 'batchresponse_' . str_replace('"', '', mt_rand()) . '===';
	$data = '';
	foreach ($batch as $request) {
		$data .= '--' . $boundary . "\r\n"	
			. "Content-Type: application/http\r\n"
			. "Content-Transfer-Encoding: binary\r\n\r\n"
			. $request . "\r\n";
	}
	$data .= '--' . $boundary . '--';
	
		
	header("Content-Type: multipart/mixed; boundary=".$boundary);
	header("DataServiceVersion: 1.0");
	header("X-Content-Type-Options: nosniff");
	return $data;
}
	
function _parseBatchRequest($batchResponse) {
	$responses = array();
	// figuring out boundary value
	$boundaryStart = strpos($_SERVER["CONTENT_TYPE"], 'boundary=') + 9;
	$boundary = trim(substr($_SERVER["CONTENT_TYPE"], $boundaryStart));
	
	$rawResponses = explode("--$boundary", $batchResponse);
	array_shift($rawResponses);
	array_pop($rawResponses);
	foreach ($rawResponses as $response) {
		$response = str_replace("\r", "", $response);
		$responseStart = strpos($response, "\n\n") ;
		$response = trim(substr($response, $responseStart));
		$responses[] = trim ( $response,  " \t\n\r\0\x0B-"  );
	}
	return $responses;
}

function startsWith($haystack, $needle) {
    // search backwards starting from haystack length characters from the end
    return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== FALSE;
}

function acceptedHeader($header) {
	return startsWith($header, 'Content-Type') ||
		   startsWith($header, 'X-Content-Type-Options') ||
		   startsWith($header, 'Cache-Control');
}

function get_contents($url) {
	$headers = apache_request_headers();
	
	$opts = array('http' =>
		array(
			'header'  => 'Authorization: ' . $_SERVER['HTTP_AUTHORIZATION']
		)
	);

	$context  = stream_context_create($opts);
	$content = file_get_contents($url, false, $context);
	$headers = $http_response_header;

	$header = "DataServiceVersion: 2.0;\n" . implode("\n", array_filter( $headers, "acceptedHeader"));
	return $header . "\n\n" . $content;
}


$content = @file_get_contents('php://input');

foreach (_parseBatchRequest($content) as $response) {
	$info = explode(" ", $response);
	/*
	$url = parse_url($info[1]);
	
	parse_str($url["query"], $_GET);
	
	$pathStart = strpos($url["path"], "v2/") + 3;
	$path = explode("/", substr($url["path"], $pathStart));
	
	$_GET["action"] = strtolower(trim( $path[0],  " \t\n\r\0\x0B-()\$"  ));		
	$count = end((array_values($path)));
	if ($count=='$count') {
		$_GET["count"] = true;
	}
	*/
	$out2 = get_contents($info[1]);
	$batch[] = $out2;
	
}
http_response_code(202);
echo respondBatch($batch);
