<?php
require_once("../config.inc");
class TranslationsApi extends BaseApis
{
    var $translator;
    var $jsSource= <<<JS
var translations = new Map();

function translate(...args){
    return translateInternal(...args);
}
function translateInternal(...args){
    
    if (args.length === 0) {
        showError("Called with no parameters");
        return 'NOARGS';
    }
    const messageIdIndex = String(args[0]).toUpperCase();

    if (translations && translations.has(messageIdIndex)) {
        let result = translations.get(messageIdIndex);

        for (let i = 1; i < args.length; i++) {
            if (!(i in args)) {
                args.join(" ");
            }

            const arg = args[i];
            let newArg = null;

            if (arg === null || arg === undefined) {
                newArg = "N/A";
            } else if (Array.isArray(arg)) {
                newArg = translateInternal(...arg);
            } else {
                newArg = translateInternal(arg);
            }

            result = result.replace(/:\\?:/, newArg);
        }

        return result.replace(/:\\?:/g, "");
    } else {
        //console.error('Missing message id: '+messageIdIndex);
    }

    return args.join(" ");
}
JS;
    public function __construct()
    {
        parent::__construct();
        $this->translator = GlobalRegistry::get("translator");
    }

    function apiCallGet(){


        $result[] = $this->jsSource;
        $result[]="translations.set(\"DEFAULT_LANGUAGE\",\"".GlobalRegistry::get("DEFAULT_LANGUAGE")."\");";
        $result[]="translations.set(\"HOST_PATH\",\"".GlobalRegistry::get("HOST_PATH")."\");";
        $result[]="translations.set(\"COMPANY_LOGO\",\"".GlobalRegistry::get("COMPANY_LOGO")."\");";
        $result[]="translations.set(\"API_URL\",\"".GlobalRegistry::get("API_URL")."\");";

        $all = $this->translator->getAll();
        $allowed = ["REQUIRED","LOGIN","PASSWORD","RESET_PASSWORD","ERROR"];
        if($this->auth->isUserLoggedIn()) {
            $allowed = [];
        }

        foreach ($all as $messageId => $translation) {
            $messageId = strtoupper($messageId);
            if(count($allowed)==0 || in_array($messageId, $allowed)) {
                $translation = str_replace('"','\\"',$translation);
                $result[] = "translations.set(\"$messageId\", \"$translation\");";
            }

        }


        $this->sender->sendJsResponse(join("\n",$result));
    }
}

$api = new TranslationsApi();
$api->handle();