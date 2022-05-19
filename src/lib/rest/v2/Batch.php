<?php

namespace lib\rest\v2;

use lib\http\BaseHandler;
use lib\http\Request;
use lib\utils\HttpUtils;

class Batch extends BaseHandler
{
    private $v2BatchDebug = false;
    /**
     * @param Request $request
     * @return bool
     */
    public function post($request)
    {
        $a_data = array();
        // read incoming data
        $input = $request->getRawContent();

        /*var_dump($input);
        flush();
        die();*/


        // grab multipart boundary from content type header
        preg_match('/boundary=(.*)$/', $request->getParam("content_type"), $matches);

        // content type is probably regular form-encoded
        if (!count($matches)) {
            return null;
        }

        $boundary = $matches[1];


        if ($this->v2BatchDebug) {
            file_put_contents("batch.log", "REQUEST:" . $input . "\r\n", FILE_APPEND);
            file_put_contents("batch.log", "\r\n", FILE_APPEND);
        }

        $parsed = $this->parseData($boundary, $input);

        $result = array();


        if ($this->v2BatchDebug) {
            file_put_contents("batch.log", "PARSING:" . sizeof($parsed) . "\r\n", FILE_APPEND);
            file_put_contents("batch.log", "\r\n", FILE_APPEND);
        }

        for ($i = 0; $i < sizeof($parsed); $i++) {
            $item = $parsed[$i];

            $item->ResultStatus = 200;

            if ($item->Method == "get") {
                $item->ResultData = HttpUtils::download($item->Action);
            } else if ($item->Method == "get") {
                $item->ResultData = HttpUtils::upload($item->Action, $item->Data, "application/atom+xml");
            }
            array_push($result, $item);
        }


        $response = $this->elaborate($result);

        if ($this->v2BatchDebug) {
            file_put_contents("batch.log", "RESULT:" . $response . "\r\n", FILE_APPEND);
            file_put_contents("batch.log", "\r\n", FILE_APPEND);
        }
    }

    private function buildSubBatch($src)
    {

        $res = new SubBatch();
        $i = 0;
        $res->Action = null;
        $res->ContentId = null;
        for (; $i < sizeof($src); $i++) {
            $li = $src[$i];

            if (starts_with($li, "Content-ID")) {
                $res->ContentId = substr($li, strlen("Content-ID:") + 1);
            } else if (starts_with($li, "POST")) {
                $res->Action = substr($li, strlen("POST") + 1);
                $res->Method = "post";
            } else if (starts_with($li, "GET")) {
                $res->Action = substr($li, strlen("GET") + 1);
                $res->Method = "get";
                break;
            } else if (starts_with($li, "PUT")) {
                $res->Action = substr($li, strlen("PUT") + 1);
                $res->Method = "put";
            } else if (starts_with($li, "DELETE")) {
                $res->Action = substr($li, strlen("DELETE") + 1);
                $res->Method = "delete";
            } else if (starts_with($li, "Content-Length")) {
                $res->ContentLength = substr($li, strlen("Content-Length:") + 1);
                $i += 2;
                break;
            }
        }
        if ($res->Action != null) {

            $http = indexOf($res->Action, " HTTP");
            if ($http > 0) {
                $res->Action = substr($res->Action, 0, $http);
            }
            if (indexOf($res->Action, "http") != 0) {
                $res->Action = HttpUtils::currentUrl($res->Action, $this->properties);
            }
        }
        //Add the space after the method
        $res->Data = "";

        while ($i < sizeof($src) && $res->Method != "get") {
            if ($res->Data != "") {
                $res->Data .= "\n" . $src[$i];
            } else {
                $res->Data .= $src[$i];
            }
            $i++;
        }

        return $res;
    }

    private function parseData($boundary, $input)
    {

        $boundary = trim($boundary, '\"');

        //$boundary = "--".$boundary;
        // split content by boundary and get rid of last -- element
        $a_blocks = explode("--" . $boundary, $input); // preg_split("/-+$boundary/", $input);
        //echo $boundary."  ";
        //var_dump($input);die();
        $result = array();

        foreach ($a_blocks as $block) {
            if (empty($block)) {

                continue;
            }

            $splitted = preg_split('/\R/', $block);


            $subBatch = $this->buildSubBatch($splitted);

            if ($subBatch->Action != "") {
                array_push($result, $subBatch);
            }
        }


        return $result;
    }

    private function elaborate($requests)
    {
        $randBound = $this->randomNumber(strlen("ff6a932f-7ca9-4926-9ae0-0e12776eacbf"));
        $randBoundSub = $this->randomNumber(strlen("ff6a932f-7ca9-4926-9ae0-0e12776eacbf"));
        $boundary = "batchresponse_" . $randBound;

        http_response_code(202); //accepted


        $result = "";

        header("DataServiceVersion: 1.0;");
        header("Content-Type: multipart/mixed; boundary=" . $boundary);
        header("Cache-Control: no-cache");
        //header("Content-Type: multipart/mixed");
        //header("X-Content-Type-Options: nosniff");
        //header("X-XSS-Protection: 1; mode=block");
        for ($i = 0; $i < sizeof($requests); $i++) {
            $request = $requests[$i];
            $result .= "--" . $boundary . "\r\n";
            $result .= "Content-Type: application/http\r\n";
            $result .= "Content-Transfer-Encoding: binary\r\n";

            $result .= "\r\n";
            $result .= "HTTP/1.1 " . $request->ResultStatus . " ";
            if ($request->ResultStatus == 200) {
                $result .= "OK\r\n";
            } else {
                $result .= "KO\r\n";
            }
            $result .= "Cache-Control: no-cache\r\n";
            $result .= "DataServiceVersion: 2.0;\r\n";
            if (is_numeric($request->ResultData)) {
                $result .= "Content-Type: text/plain;charset=utf-8\r\n";
            } else {
                $result .= "Content-Type: application/atom+xml;type=feed;charset=utf-8\r\n";
            }
            if ($request->ContentId != null) {
                $result .= "Content-ID: " . $request->ContentId . "\r\n";
            }
            $result .= "Content-Length: " . strlen($request->ResultData) . "\r\n";

            $result .= "\r\n";
            $result .= $request->ResultData . "\r\n";
        }
        $result .= "--" . $boundary . "--\r\n";

        //file_put_contents("batch.log","RESULT:\r\n".$result."\r\nENDOFRESULT", FILE_APPEND);
        header("Content-Length: " . strlen($result));


        echo $result;
        flush();

        return $result;
    }

    private function randomNumber($length)
    {
        $result = '';

        for ($i = 0; $i < $length; $i++) {
            $result .= mt_rand(0, 9);
        }

        return $result;
    }
}