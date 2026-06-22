<?php

class Sender
{

    function sendCSVResponse($csvData, $fileName)
    {
        http_response_code(200);
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        echo $csvData;
        exit;
    }

    function sendComplexXLSResponse(&$data, $fileName){
        $simpleXls = GlobalRegistry::getTransient("SimpleXLSXGen");
        http_response_code(200);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        if(!is_array($data))return;
        if(count($data)>0) {
            $simpleXls->fromArray($data)->downloadAs($fileName.".xlsx");
        }

        exit;
    }

    function sendSimpleXLSResponse(&$data,$labels, $fileName)
    {
        $simpleXls = GlobalRegistry::getTransient("SimpleXLSXGen");
        http_response_code(200);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        if(!is_array($data))return;

        if(count($data)>0) {
            /*$headers = [];
            foreach ($data[0] as $key => $value) {
                if(isset($labels[$key])){
                    $headers[]=$labels[$key];
                }else {
                    $headers[] = $key;
                }
            }
            $exportData = array_merge([$headers], $data);
            $simpleXls->fromArray($exportData)->downloadAs($fileName.".xlsx");*/


            $headers=[];
            foreach ($data[0] as $key => $value) {
                if(isset($labels[$key])){
                    $headers[$key]=translate($labels[$key]);
                }else {
                    $headers[$key] = translate($key);
                }
            }
            $reports = GlobalRegistry::get("Reports");
            $data = $reports->reorderListAndAddHeaders($data,$headers);
            $spr = new Spreadsheet();
            $spr->import($data,$headers);
            $spr->setRowStyle("A1","background-color: #ffeea7;font-weight: bold;");

            $final = [];
            $spr->export($final);
            $this->sendComplexXLSResponse($final,$fileName);
        }

        exit;
    }


    function sendErrorResponse($message = 'Error', $statusCode = 500, $errors = [])
    {
        /*$this->sendJsonResponse([
            'message' => $message,
            'success' => false,
            'errors' => $errors
        ], $statusCode);*/

        $audit = GlobalRegistry::get('audit');

        if(!SHOW_EXACT_ERRORS){
            $message = translate("GENERIC_ERROR");
        }
        $response = [
            'status' => 'error',
            'success' => false,
            'message' => $message,
            'errors'=>[]
        ];

        if($errors!==null) {
            if(is_string($errors)) {
                $response['errors'];
                $response['errors'][]=$errors;
            }else if(is_array($errors && !empty($errors))){
                $response['errors'] = $errors;
            }
        }
        $globalErrors = $audit->getGlobalErrors();
        for($i=0;$i<sizeof($globalErrors);$i++){
            $response['errors'][]=$globalErrors[$i];
        }
        for($i=0;$i<sizeof($response['errors']);$i++){
            try {
                $erri = $response['errors'][$i];
                if(strpos(strtolower($erri),"exception")!==false)continue;
                $spl = preg_split("/\r\n|\r|\n/", $erri);
                if (sizeof($spl) > 0) {
                    $partial = trim($spl[0]);
                    if (strlen($partial) > 50) {
                        $partial = substr($partial, 0, 50) . "...";
                    }
                    if($i==0) {
                        $response['message'] = $partial;
                    }else{
                        $response['message'] .= ", " . $partial;
                    }
                }
            }catch (Exception $e){
                error_log("ERROR parsing error ".$e->getMessage());
            }
        }

        $this->sendJsonResponse($response, $statusCode);
    }

    function sendSuccessResponse($data=[], $statusCode = 200)
    {
        $this->sendJsonResponse([
            'success' => true,
            'data' => $data
        ], $statusCode);
    }

    function sendJsResponse($data, $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/javascript');
        echo $data;
        exit;
    }


    function sendJsonResponse($data, $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
