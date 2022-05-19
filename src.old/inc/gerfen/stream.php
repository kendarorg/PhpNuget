<?php
/**
* Parse raw HTTP request data
* http://www.chlab.ch/blog/archives/php/manually-parse-raw-http-data-php
*
* Pass in $a_data as an array. This is done by reference to avoid copying
* the data around too much.
*
* Any files found in the request will be added by their field name to the
* $data['files'] array.
*
* @param   array  Empty array to fill with data
* @return  array  Associative array of request data
*/
function parse_raw_http_request(array &$a_data)
{
    // read incoming data
    $input = file_get_contents('php://input');
    
    // grab multipart boundary from content type header
    preg_match('/boundary=(.*)$/', $_SERVER['CONTENT_TYPE'], $matches);
    
    // content type is probably regular form-encoded
    if (!count($matches))
    {
        // we expect regular puts to containt a query string containing data
        parse_str(urldecode($input), $a_data);
        return $a_data;
    }
    
    $boundary = $matches[1];
    
    // split content by boundary and get rid of last -- element
    $a_blocks = preg_split("/-+$boundary/", $input);
    array_pop($a_blocks);
    
    // loop data blocks
    foreach ($a_blocks as $id => $block)
    {
        if (empty($block))
            continue;
        
        // you'll have to var_dump $block to understand this and maybe replace \n or \r with a visibile char
        
        // parse uploaded files
        if (strpos($block, 'application/octet-stream') !== FALSE)
        {
            // match "name", then everything after "stream" (optional) except for prepending newlines
            preg_match("/name=\"([^\"]*)\".*stream[\n|\r]+([^\n\r].*)?$/s", $block, $matches);
            $a_data['files'][$matches[1]] = $matches[2];
        }
        // parse all other fields
        else
        {
            if (strpos($block, 'filename') !== FALSE)
            {
                // match "name" and optional value in between newline sequences
                preg_match('/name=\"([^\"]*)\"; filename=\"([^\"]*)\"[\n|\r]+([^\n\r].*)?\r$/s', $block, $matches);
                preg_match('/Content-Type: (.*)?/', $matches[3], $mime);
                
                // match the mime type supplied from the browser
                $image = preg_replace('/Content-Type: (.*)[^\n\r]/', '', $matches[3]);
                
                // get current system path and create tempory file name & path
                $path = sys_get_temp_dir().'/php'.substr(sha1(rand()), 0, 6);
                
                // write temporary file to emulate $_FILES super global
                $err = file_put_contents($path, $image);
                
                // Did the user use the infamous &lt;input name="array[]" for multiple file uploads?
                if (preg_match('/^(.*)\[\]$/i', $matches[1], $tmp)) {
                    $a_data[$tmp[1]]['name'][] = $matches[2];
                    } else {
                    $a_data[$matches[1]]['name'][] = $matches[2];
                }
                
                // Create the remainder of the $_FILES super global
                $a_data[$tmp[1]]['type'][] = $mime[1];
                $a_data[$tmp[1]]['tmp_name'][] = $path;
                $a_data[$tmp[1]]['error'][] = ($err === FALSE) ? $err : 0;
                $a_data[$tmp[1]]['size'][] = filesize($path);
            }
            else
            {
                // match "name" and optional value in between newline sequences
                preg_match('/name=\"([^\"]*)\"[\n|\r]+([^\n\r].*)?\r$/s', $block, $matches);
                
                if (preg_match('/^(.*)\[\]$/i', $matches[1], $tmp)) {
                    $a_data[$tmp[1]][] = $matches[2];
                    } else {
                    $a_data[$matches[1]] = $matches[2];
                }
            }
        }
    }
}