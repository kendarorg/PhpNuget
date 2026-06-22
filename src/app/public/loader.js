let scriptsToLoad = [];
function loadScripts(baseUrl, ...urls) {
    if(!baseUrl.endsWith("/")){
        baseUrl+="/";
    }

    urls.forEach(url => {
        if(url.startsWith("/")){
            url = url.substring(1);
        }
        let completeUrl = baseUrl + url;
        scriptsToLoad.push(()=>{return loadScript(completeUrl)});
    });
}

function loadScript(src) {
    return new Promise((resolve, reject) => {
        src = src.replace(/\\/g, '/');
        if(src.endsWith(".js")){
            const s = document.createElement("script");
            s.src = src;
            s.onload = resolve;
            s.onerror = reject;
            document.head.appendChild(s);
        }else if(src.endsWith(".css")){
            const s = document.createElement("link");
            s.rel = "stylesheet";
            s.href = src;
            s.onload = resolve;
            s.onerror = reject;
            document.head.appendChild(s);
        }
    });
}

document.addEventListener('DOMContentLoaded', function () {
    globalScript();
    let currentScriptToLoad = null;
    for(let i=0;i<scriptsToLoad.length;i++){
        let currentScript = scriptsToLoad[i];
        if(currentScriptToLoad===null){
            currentScriptToLoad = currentScript();
        }else{
            let tmp = currentScriptToLoad.then(currentScript);
            currentScriptToLoad = tmp;
        }
    }
    if(currentScriptToLoad){
        currentScriptToLoad.then(()=>{
            const event = new CustomEvent("scriptsLoaded", {});

            document.dispatchEvent(event);
        })
    }
});