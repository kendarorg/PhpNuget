<?php

$destination = "http://localhost:8020/phpnuget/api/v2/\$batch";
 
$eol = "\r\n";
$data = '';

/*
?$filter=Title eq 'Antlr'
?$filter=Title eq 'Microsoft ASP.NET MVC'

/api/v2/Packages/?$filter=Title eq 'Microsoft ASP.NET MVC'
/api/v2/Packages/?$filter=Title eq 'Antlr'

*/

 
$mime_boundary=md5(time());
 
 
$data .= '--' . $mime_boundary . $eol;
$data .= 'Content-Type: application/http' . $eol ;
$data .= 'Content-Transfer-Encoding: binary' . $eol ;
$data .= 'Content-ID: <b29c5de2-0db4-490b-b421-6a51b598bd22+2>' . $eol ;
$data .= $eol;
$getter = "/api/v2/Packages/?"."$"."filter=".urlencode("Title eq 'Antlr'");
$data .= "GET ".$getter . $eol;
$data .= $eol;

 
$data .= '--' . $mime_boundary . $eol;
$data .= 'Content-Type: application/http' . $eol ;
$data .= 'Content-Transfer-Encoding: binary' . $eol ;
$data .= 'Content-ID: <b29c5de2-0db4-490b-b421-6a51b598bd22+1>' . $eol ;
$data .= $eol;
$getter = "/api/v2/Packages/?"."$"."filter=".urlencode("Title eq 'Microsoft ASP.NET MVC'");
$data .= "GET ".$getter . $eol;
$data .= $eol;


$data .= "--" . $mime_boundary . "--" . $eol . $eol; // finish with two eol's!!
 
$params = array('http' => array(
                  'method' => 'POST',
                  'header' => 'Content-Type: multipart/mixed; boundary=' . $mime_boundary . $eol,
                  'content' => $data
               ));
 
$ctx = stream_context_create($params);
$response = @file_get_contents($destination, FILE_TEXT, $ctx);

echo $response;
?>