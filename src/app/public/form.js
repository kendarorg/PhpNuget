// 1️⃣ Utilities
// noinspection JSCheckFunctionSignatures

/*function translate(...args){
    return args.join(" ");
}*/

const Singleton = (function () {
    let instance = null;

    function createInstance() {
        return new Map();
    }

    return {
        setValue(index,data=null){
            index = index.toLowerCase();
            let ins = this.getInstance()
            ins.set(index,data);
            return data;
        },
        getValue(index,orDefault=null){

            index = index.toLowerCase();
            let ins = this.getInstance();
            if(!ins.has(index)){
                if(typeof orDefault === "function"){
                    ins.set(index,orDefault());
                }else{
                    ins.set(orDefault);
                }
            }
            if(ins.has(index)){
                return ins.get(index);
            }

            return null;
        },
        getInstance() {
            if (!instance) {
                instance = createInstance();
            }
            return instance;
        }
    };
})();

Singleton.setValue("counter",1);

function goSavingBack(newUrl){
    localStorage.setItem("back",window.location.href);
    window.location.href = newUrl;
}

function hasBack()
{
    return localStorage.getItem("back")!==null;
}

function goBackOrDefault(newUrl){
    let backStored = localStorage.getItem("back");
    localStorage.setItem("back",null);
    if(backStored && backStored!==null && backStored!=="null"){
        window.location.href = backStored;
    }else{
        window.location.href = newUrl;
    }

}

function isString(input) {
    return  typeof input?.startsWith === 'function';
}

function extractUrl(url=null){
    if(url===null)url = window.location.href;
    return url.split('?')[0] || '';
}

function extractQueryParams(url=null){
    if(url===null)url = window.location.href;
    let queryString =  url.split('?')[1] || '';
    const params = new URLSearchParams(queryString);
    return new Map(params.entries());

}

function getQueryParam(key,url=null){
    if(url===null)url = window.location.href;
    let queryString =  url.split('?')[1] || '';
    const params = new URLSearchParams(queryString);
    let parx = new Map(params.entries());
    return parx.get(key);
}

function hasQueryParam(key,url=null){
    if(url===null)url = window.location.href;
    let queryString =  url.split('?')[1] || '';
    const params = new URLSearchParams(queryString);
    let parx = new Map(params.entries());
    return parx.has(key);
}

function setupBoundingRect(relativeTo, dropdown) {
    const rect = relativeTo.getBoundingClientRect();

    dropdown.style.top = rect.bottom + "px";
    dropdown.style.left = rect.left + "px";
    dropdown.style.width = rect.width + "px";
}

class Fetcher {
    constructor({
                    url, isJson = true,
                    withWaitingWheel = true,
                    method = "GET",
                    asDownload = false,
                    query = new Map(),
                    headers = new Map(),
                    body=null
                }) {
        this.asDownload = asDownload;
        this.url = url;
        this.method = method.toUpperCase();
        this.query = query;
        this.waitingWheel = withWaitingWheel;
        this.success = function (data) {
            return data;
        };
        this.error = function () {
        };
        this.isJson = isJson;
        this.isJsonResponse = isJson;
        this.headers = headers;
        this.body = body;
        if(this.isJson){
            this.asJsonType();
        }
        this.withJsonResponse(this.isJsonResponse);
    }

    withMethod(method) {
        this.method = method;
        return this;
    }

    withWaitingWheel() {
        this.waitingWheel = true;
        return this;
    }

    withHeader(key, value) {
        this.headers.set(key, value);
        return this;
    }

    withQuery(key, value) {
        this.query.set(key, value);
        return this;
    }

    asJsonType() {
        this.isJson = true;
        this.headers.set('Content-Type', 'application/json');
        return this;
    }

    withJsonResponse(isJson=true){
        this.isJsonResponse=isJson;
        if(isJson){
            this.headers.set('Accept', 'application/json');
        }else{
            this.headers.delete("Accept");
        }
        return this;
    }

    asMimeType(accept, contentType) {
        this.isJson=false;
        this.headers.set('Accept', accept);
        this.headers.set('Content-Type', contentType);
        return this;
    }

    /**
     * Set the callback to be called when the fetch succeeds.
     * callback(response,data,headers,code)
     * If json already jsonized
     * @param callback
     */
    onSuccess(callback) {
        this.success = callback;
        return this;
    }

    /**
     * Set the callback to be called when the fetch fail.
     * callback(response,data,headers,code)
     * If json already jsonized
     * @param callback
     */
    onError(callback) {
        this.error = callback;
        return this;
    }

    withBody(body) {
        this.body = body;
        return this;
    }

    withDownload(){
        this.asDownload =true;
        return this;
    }

    async fetch() {
        let url = this.url;
        const paramsMap = extractQueryParams(url);
        url = extractUrl(url);
        const totalParams = new Map([...paramsMap, ...this.query]);
        totalParams.set("method", this.method);
        if (totalParams.size > 0) {
            url += "?" + new URLSearchParams(Object.fromEntries(totalParams)).toString();
        }

        let realMethod = this.method;
        if(realMethod==="PUT")realMethod="POST";
        if(realMethod==="DELETE")realMethod="GET";
        let pars = {
            method: realMethod,
            headers: this.headers,
        }
        if (this.body) {
            if (this.isJson) {
                pars.body = JSON.stringify(this.body);
            } else {
                pars.body = this.body;
            }
        }
        if (this.waitingWheel) {
            startWaitingWheel();
        }
        try {
            let response = await fetch(url, pars);
            if (response.ok === false) {
                stopWaitingWheel();
                let error = {
                    message: response.statusText + "(" + response.status + ")"
                }
                let showErrorForReal = true;
                if (this.error) {
                    showErrorForReal = this.error(error, response.headers, response.status, response.statusText);
                }
                if (showErrorForReal === true) {
                    showError(translate("ERROR"), error.message);
                }
                return null;
            }

            if (this.isJsonResponse) {
                let jsonResponse = await response.json();
                stopWaitingWheel();
                const data = jsonResponse.data;
                if (data && jsonResponse.success === true) {
                    if (this.success) {
                        return this.success(data, response.headers, response.status, response.statusText);
                    }
                    return data;
                } else {
                    console.error(data);
                    let showErrorForReal = true;
                    if (this.error) {
                        showErrorForReal = this.error(data, response.headers, response.status, response.statusText);
                    }
                    if (showErrorForReal === true) {
                        showError(translate("ERROR"), data.message);
                    }
                    return null;
                }
            } else if(!this.asDownload){
                stopWaitingWheel();
                if (this.success) {
                    this.success(response.data, response.headers, response.status, response.statusText);
                }
                return response.data;
            }else{
                const blob = await response.blob();

                let filename = "download.bin"; // fallback

                const disposition = response.headers.get("Content-Disposition");
                if (disposition && disposition.includes("filename")) {
                    const match = disposition.match(/filename\*?=(?:UTF-8'')?["']?([^;"']+)/);
                    if (match && match[1]) {
                        filename = decodeURIComponent(match[1]);
                    }
                }

                const blobUrl = window.URL.createObjectURL(blob);

                const a = document.createElement("a");
                a.href = blobUrl;
                a.download = filename; // or get from headers
                document.body.appendChild(a);
                a.click();

                a.remove();
                window.URL.revokeObjectURL(blobUrl);
                stopWaitingWheel();
            }
        } catch (e) {
            stopWaitingWheel();
            console.error(e);
            let showErrorForReal = true;
            if (this.error) {
                showErrorForReal = this.error(e, {}, -1, null);
            }
            if (showErrorForReal === true) {
                showError(translate("ERROR"), e.message);
            }
            return null;
        }
    }
}

/**
 * SPINNNER
 */

(function () {
    'use strict';


    if (document.getElementById("page-loader")) return;
    // Create the div element
    const loaderDiv = document.createElement('div');
    loaderDiv.id = 'page-loader';
    loaderDiv.className = 'page-loader hidden';

    // Create the spinner div inside it
    const spinnerDiv = document.createElement('div');
    spinnerDiv.className = 'spinner';

    loaderDiv.appendChild(spinnerDiv);

    // Insert it at the very top of the body
    document.body.insertBefore(loaderDiv, document.body.firstChild);


    window.startWaitingWheel = function () {
        document.getElementById("page-loader").classList.remove("hidden");
        document.body.style.overflow = "hidden"; // prevent scrolling
    }

    window.stopWaitingWheel = function () {
        document.getElementById("page-loader").classList.add("hidden");
        document.body.style.overflow = ""; // restore scrolling
    }
})();

/**
 * NOTIFICATION DIALOG
 */

// Custom Dialog Functions
(function () {
    'use strict';

    // Show Alert Function
    window.showAlert = function (type, title, text = null, timeoutInSeconds = 5,callback=null) {
        if(timeoutInSeconds==null)timeoutInSeconds = 5;
        const alertDiv = document.createElement('div');
        alertDiv.className = 'notification-custom-alert';

        const iconMap = {
            info: 'I', warning: '!', error: 'E'
        };
        if (text == null) {
            text = title;
            if (type === 'error') {
                title = translate("ERROR");
                startWaitingWheel();
            } else if (type === 'warning') {
                title = translate("WARNING");
            } else {
                title = translate('INFO');
            }
        }
        let ok = translate("Ok");
        alertDiv.innerHTML = `
            <div class="notification-dialog-header">
                <div class="notification-dialog-icon ${type}">${iconMap[type] || 'i'}</div>
                <div class="notification-dialog-title">${title}</div>
            </div>
            <div class="notification-dialog-content">${text}</div>
            <div class="notification-dialog-actions">
                <button class="notification-dialog-button close" data-result="false">${ok}</button>
            </div>
        `;

        const buttons = alertDiv.querySelectorAll('.notification-dialog-button');
        buttons.forEach(button => {
            button.addEventListener('click', function () {
                alertDiv.classList.remove('show')
                document.body.removeChild(alertDiv);
                if(callback)callback();
                stopWaitingWheel();
            });
        });

        document.body.appendChild(alertDiv);

        // Trigger show animation
        setTimeout(() => {
            alertDiv.classList.add('show');
        }, 10);

        // Auto-remove after specified time
        setTimeout(() => {
            alertDiv.classList.remove('show');
            setTimeout(() => {
                if (alertDiv.parentNode) {
                    document.body.removeChild(alertDiv);
                    if(callback)callback();
                }
            }, 300);
            stopWaitingWheel();
        }, timeoutInSeconds * 1000);
    };

    window.showInfo = function (title, text = null, timeoutInSeconds = 5,callback=null,) {
        if(timeoutInSeconds==null)timeoutInSeconds=5;
        window.showAlert('info', title, text, timeoutInSeconds,callback);
    }
    window.showWarning = function (title, text = null, timeoutInSeconds = 10,callback=null,) {
        if(timeoutInSeconds==null)timeoutInSeconds=10;
        window.showAlert('warning', title, text, timeoutInSeconds,callback);
    }
    window.showError = function (title, text = null, timeoutInSeconds = 15,callback=null,) {
        if(timeoutInSeconds==null)timeoutInSeconds=15;
        window.showAlert('error', title, text, timeoutInSeconds,callback);
    }

    // Show Confirm Function
    window.showConfirm = function (title, callbackOk, callbackKo = null, text = null) {
        const overlay = document.createElement('div');
        overlay.className = 'notification-custom-dialog-overlay';

        const confirmDiv = document.createElement('div');
        confirmDiv.className = 'notification-custom-confirm';
        if (text == null) {
            text = title;
            title = translate("CHOOSE");
        }
        let no = translate("NO");
        let yes = translate("Yes");
        confirmDiv.innerHTML = `
            <div class="notification-dialog-header">
                <div class="notification-dialog-icon info">?</div>
                <div class="notification-dialog-title">${title}</div>
            </div>
            <div class="notification-dialog-content">${text}</div>
            <div class="notification-dialog-actions">
                <button class="notification-dialog-button secondary" data-result="false">${no}</button>
                <button class="notification-dialog-button primary" data-result="true">${yes}</button>
            </div>
        `;

        overlay.appendChild(confirmDiv);
        document.body.appendChild(overlay);

        // Handle button clicks
        const buttons = confirmDiv.querySelectorAll('.notification-dialog-button');
        buttons.forEach(button => {
            button.addEventListener('click', function () {
                const result = this.getAttribute('data-result') === 'true';
                document.body.removeChild(overlay);
                if (callbackOk && typeof callbackOk === 'function' && result) {
                    callbackOk(result);
                }
                if (callbackKo && typeof callbackKo === 'function' && result === false) {
                    callbackKo(result);
                }
            });
        });

        // Focus on Yes button for keyboard accessibility
        confirmDiv.querySelector('.notification-dialog-button.primary').focus();

        // Handle Enter key for Yes and Escape key prevention
        const handleKeydown = function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                document.body.removeChild(overlay);
                const result = true;
                if (callbackOk && typeof callbackOk === 'function' && result) {
                    callbackOk(result);
                }
                if (callbackKo && typeof callbackKo === 'function' && result === false) {
                    callbackKo(result);
                }
                document.removeEventListener('keydown', handleKeydown);
            }
            // Prevent Escape key from closing
            if (e.key === 'Escape') {
                e.preventDefault();
            }
        };

        document.addEventListener('keydown', handleKeydown);

        // Clean up event listener when dialog is closed
        const observer = new MutationObserver(function (mutations) {
            mutations.forEach(function (mutation) {
                if (mutation.type === 'childList' && mutation.removedNodes.length > 0) {
                    for (let node of mutation.removedNodes) {
                        if (node === overlay) {
                            document.removeEventListener('keydown', handleKeydown);
                            observer.disconnect();
                            break;
                        }
                    }
                }
            });
        });

        observer.observe(document.body, {childList: true});
    };


    window.showChoice = function (title, callbackLabelArray, text = null) {
        const overlay = document.createElement('div');
        overlay.className = 'notification-custom-dialog-overlay';

        const confirmDiv = document.createElement('div');
        confirmDiv.className = 'notification-custom-confirm';
        if (text == null) {
            text = title;
            title = translate("CHOOSE");
        }
        let confirmDivInnerHtml = `
            <div class="notification-dialog-header">
                <div class="notification-dialog-icon info">?</div>
                <div class="notification-dialog-title">${title}</div>
            </div>
            <div class="notification-dialog-content">${text}</div>
            <div class="notification-dialog-actions-multi">`;
        let last=callbackLabelArray.length-1;
        callbackLabelArray.forEach((c, idx) => {
            if(idx===last){
                confirmDivInnerHtml+=`<button class="notification-dialog-button primary" data-result="${idx}">${c.label}</button>`
            }else{
                confirmDivInnerHtml+=`<button class="notification-dialog-button secondary" data-result="${idx}">${c.label}</button>`
            }

        });
        confirmDivInnerHtml+=`</div>`;
        confirmDiv.innerHTML = confirmDivInnerHtml;

        overlay.appendChild(confirmDiv);
        document.body.appendChild(overlay);

        // Handle button clicks
        const buttons = confirmDiv.querySelectorAll('.notification-dialog-button');
        buttons.forEach(button => {
            button.addEventListener('click', function () {
                const result = parseInt(this.getAttribute('data-result'));
                let callbackLabel = callbackLabelArray[result];
                document.body.removeChild(overlay);
                callbackLabel.callback();
            });
        });

        // Focus on Yes button for keyboard accessibility
        confirmDiv.querySelector('.notification-dialog-button.primary').focus();

        // Handle Enter key for Yes and Escape key prevention
        const handleKeydown = function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
            }
            // Prevent Escape key from closing
            if (e.key === 'Escape') {
                e.preventDefault();
            }
        };

        document.addEventListener('keydown', handleKeydown);

        // Clean up event listener when dialog is closed
        const observer = new MutationObserver(function (mutations) {
            mutations.forEach(function (mutation) {
                if (mutation.type === 'childList' && mutation.removedNodes.length > 0) {
                    for (let node of mutation.removedNodes) {
                        if (node === overlay) {
                            document.removeEventListener('keydown', handleKeydown);
                            observer.disconnect();
                            break;
                        }
                    }
                }
            });
        });

        observer.observe(document.body, {childList: true});
    };
})();

function debounceAsync(fn, delay = 300) {
    let timer;
    let callId = 0;

    return (...args) => new Promise(resolve => {
        const current = ++callId;
        clearTimeout(timer);
        timer = setTimeout(async () => {
            const result = await fn(...args);
            if (current === callId) resolve(result);
        }, delay);
    });
}

// 2️⃣ Base Node
class Node {

    positionAtBottomLeft(anchor, target) {
        // Get the bounding box of the input relative to the viewport
        const rect = anchor.getBoundingClientRect();

        // Apply the styles to the target div
        target.style.position = 'fixed';
        target.style.left = `${rect.left}px`;   // Align left edges
        target.style.top = `${rect.bottom}px`;  // Place top of div at bottom of input

        // Ensure it's on top of everything else
        target.style.zIndex = '9999';
    }
    constructor({
                    id=null,parent=null,
                    form=null,mainElement=null,
                    style=null,
                    container=null}={}) {
        this.id = id;
        this.parent = parent;
        this.form = form;
        this.mainElement = mainElement;
        this.style = style;
        this.container = container;
        this.conditionToRender =null;
    }
    withId(id){
        this.id = id;
        return this;
    }

    _setMainElement(el) {
        if(this.style){
            el.style = this.style;
        }
        this.mainElement = el;
    }

    _setContainer(el) {
        this.container = el;
    }

    getMainElement() {
        return this.mainElement;
    }

    attachForm(form) {
        this.form = form;
        if(this.id!=null){
            this.form.registerId(this);
        }
        if(this.name!=null){
            this.form.registerName(this);
        }
    }

    withRenderCondition(conditionToRenderCallbackOrValue=false){
        this.conditionToRender = conditionToRenderCallbackOrValue;
        return this;
    }

    shouldRender() {
        if(this.conditionToRender===null)return true;
        if (typeof this.conditionToRender === 'function') {
            return !!this.conditionToRender(); // call the function and coerce to boolean
        }
        return !!this.conditionToRender; // coerce value to boolean
    }

    render(passedContainer=null) {
        if(passedContainer!=null)this.container=passedContainer;
        throw new Error("render() not implemented");
    }

    postRender(){

    }
}

// 3️⃣ Layout Nodes
class Row extends Node {

    constructor(...children) {
        super();
        this.withChildren(...children);
    }
    withChildren(...children) {
        if (!children) return this;
        const realChildrenCount = children.length;
        if (realChildrenCount === 0) return this;
        let missingSpanCount = 0;
        for (let ch of children) {
            if (!(ch.span && ch.span > 0)) {
                missingSpanCount++;
            }
        }

        if (missingSpanCount > 0) {
            let total = Math.ceil(12 / realChildrenCount);
            for (let i = 0; i < children.length; i++) {
                let c = children[i];
                if (c instanceof Column) {
                    c.span = total;
                } else {
                    children[i] = new Column({span: total}, c);
                }
            }
        }
        this.children = children;
        return this;
    }

    attachForm(form) {
        super.attachForm(form);
        if(this.children)this.children.forEach(c => {
            c.parent = this;
            c.attachForm(form);
        });
    }
    postRender() {
        if(!this.shouldRender())return;
        super.postRender();
        if(this.children) {
            this.children.forEach((c, idx) => {
                c.postRender();
            });
        }
    }

    render(passedContainer=null) {
        if(passedContainer!=null)this.container=passedContainer;
        if(!this.shouldRender())return null;
        let container = this.container;

        if(container==null){
            container = document.createElement("div");
            container.className = "f-row";
        }
        if(this.children)this.children.forEach(c => {
            let cx = c.render();
            if(cx)container.appendChild(cx);
        });
        this._setContainer(container);
        return container;
    }
}
class Column extends Node {
    constructor({span=12,classes=null,
                    style=null,...rest} = {}, ...children) {
        super(rest);
        this.span = span;
        this.classes = classes;
        this.style = style;
        this.withChildren(...children);

    }

    withChildren(...children) {
        if (!children) return this;
        const realChildrenCount = children.length;
        if (realChildrenCount === 0) return this;

        let missingSpanCount = 0;
        for (let ch of children) {
            if (!(ch.span && ch.span > 0)) {
                missingSpanCount++;
            }
        }
        if (missingSpanCount > 0) {
            let total = Math.ceil(12 / realChildrenCount);
            for (let i = 0; i < children.length; i++) {
                let c = children[i];
                c.span = total;
            }
        }
        this.children = children;
    }


    attachForm(form) {
        super.attachForm(form);
        if (this.children) {
            this.children.forEach(c => {
                c.parent = this;
                c.attachForm(form);
            });
        }
    }
    postRender() {
        if(!this.shouldRender())return;
        super.postRender();
        if(this.children) {
            this.children.forEach((c, idx) => {
                if (typeof c.postRender === "function") {
                    c.postRender();
                }
            });
        }
    }

    render(passedContainer=null) {
        if(passedContainer!=null)this.container=passedContainer;
        if(!this.shouldRender())return null;
        let container = this.container;
        if(container==null){
            container = document.createElement("div");
            container.className = `f-col f-col-${this.span}`;
        }
        if(this.classes){
            container.classList.add(...this.classes);
        }
        if(this.style){
            container.style = this.style;
        }
        this._setContainer(container);
        if(this.children)this.children.forEach(c => {
            let cx = c.render();
            if(cx)container.appendChild(cx);
        });
        return container;
    }
}



class IconButtonColumn extends Column {
    constructor({span=1,classes=null,style=null,...rest} = {}, ...children) {
        super({span,classes,style,...rest},...children);
        this.span = span;
        this.classes = classes;
        const width = children.length*50;
        const minWidth = width-10;
        this.style = `width:${width}px;min-width:${minWidth}px;display: block !important;`;
    }
}

class Validations {
    static regex(regexp, message = null) {
        return (v) => new RegExp(regexp).test(v) || message || translate("NOT_MATCHING_REGEXP",regexp)
    }

    static email(message = null) {
        return (v) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v) || message || translate('INVALID_EMAIL')
    }

    static required(message = null) {
        return (v) => (v !== null && v !== undefined && v !== '') || message || translate("REQUIRED")
    }

    static minLength(min, message = null) {
        return (v) => (v.length >= min) || message || translate("MIN_LENGTH",min)
    }

    static maxLength(max, message = null) {
        return (v) => (v.length <= max) || message || translate("MAX_LENGTH",max)
    }

    static minValue(min, message = null) {
        return (v) => (v >= min) || message || translate("MIN_VALUE",min)
    }

    static maxValue(max, message = null) {
        return (v) => (v <= max) || message || translate("MAX_VALUE",max)
    }

    static number(characteristics = {decimal: ",", thousands: ""}, message = null) {
        return (v) => {
            let str = v + "";
            if (characteristics.decimal === characteristics.thousands) throw new Error("Decimal and thousands cannot be the same");

            const d = characteristics.decimal.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
            const t = characteristics.thousands.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');

            // optional sign
            // digits + optional thousands before decimal
            // optional decimal part with digits only (NO thousands allowed)
            const regex = new RegExp(`^[-+]?\\d+(?:${t}\\d+)*(?:${d}\\d+)?$`);

            return (regex.test(str)) || message || translate("NOT_A_NUMBER");
        }
    }
}

// 4️⃣ Field Base
class Field extends Node {
    constructor({
                    name,
                    label,
                    altValue=null,
                    sendable = true,
                    required = false,
                    onChange = null,
                    onValidate = null,
                    readonly = false,
                    defaultValue = null,
                    validators = [],
                    ...rest
                }={}) {
        super(rest);
        this.changed = false;
        this.altValue = altValue;
        this.name = name;
        this.label = label;
        this.required = required;
        this.readonly = readonly;
        this.sendable = sendable;
        if (!Array.isArray(validators)) {
            validators = [validators];
        }
        this.validators = validators;
        this.value = defaultValue;
        this.defaultValue = defaultValue;
        this.errors = [];
        this.onChange = onChange;
        this.onValidate = onValidate;
    }

    enable(){
        this.readonly = false;
    }

    disable(){
        this.readonly = true;
    }


    isSendable() {
        return this.sendable;
    }


    attachForm(form) {
        super.attachForm(form);
        form.registerField(this);
    }

    equalsIgnoreCase(a, b) {
        if(a===null && b===null)return true;
        if((typeof a === "undefined") && (typeof  b === "undefined"))return true;
        if(a===null || (typeof a === "undefined"))return false;
        if(b===null || (typeof b === "undefined"))return false;
        if((typeof b === "string")||(typeof a === "string")) {
            try {
                return String(a).toLowerCase() === String(b).toLowerCase();
            }catch (e){}
        }
        return a===b;
    }

    setValue(value, oldValue = null) {
        //if (!this.readonly) {
        this.value = value;
        this.bindValueToUi();
        if(!this.form)return;
        this.form.handleCascade(this.name, this.value);
        if(!this.equalsIgnoreCase(this.value,oldValue)) {
            this.changed=true;
            if (this.onChange) this.onChange(this.value, oldValue, this);
        }else{
            this.changed=false;
        }
        //}
    }

    bindValueToUi(load=false){

    }

    setValueFromJSON(value,json) {
        if(value==null && this.altValue){
            value = getValueByPath(json, this.altValue);
        }
        this.value = value;
        this.bindValueToUi();
        if(!this.form)return;
        this.form.handleCascade(this.name, this.value);
    }

    validate() {
        this.errors = [];

        if (this.required && (this.value === null || this.value === "" || (Array.isArray(this.value) && !this.value.length))) {
            this.errors.push(translate("REQUIRED"));
        }
        for (const v of this.validators) {
            const r = v(this.value);
            if (r !== true) this.errors.push(r);
        }

        if (this.onValidate) {
            this.onValidate(this.errors, this);
        }
        const isOk = this.errors.length === 0;
        if (isOk) {

            this.getMainElement()?.classList.remove("f-error");
        } else {
            this.getMainElement()?.classList.add("f-error");
        }
        return isOk;
    }

    setValid(){
        this.getMainElement()?.classList.remove("f-error");
    }

    setInvalid(){
        this.getMainElement()?.classList.add("f-error");
    }

    close(){

    }

    getErrors() {
        if (this.errors.length === 0) return null;
        return {
            name: this.name, errors: this.errors, item: this
        };
    }

    getErrorsString() {
        if (this.label.trim().endsWith(":")) {
            return this.label + this.errors.join(", ");
        }
        return this.label + ":" + this.errors.join(", ");
    }

    toObject() {
        if (this.value === null || this.value === "") {
            return null;
        }
        return this.value;
    }

    reset(){
        if(this.defaultValue){
            this.setValueFromJSON(this.defaultValue);
        }else{
            this.setValueFromJSON(null);
        }
    }
}


// 5️⃣ Option Field
class OptionField extends Field {
    constructor({
                    options = [],
                    api = null,
                    dependsOn = null,
                    allowUnknownItems = false,
                    allowEmpty = true,
                    maxItems = 5,
                    allowDuplicates = false,
                    ...rest
                }={}) {
        super(rest);
        this.allowUnknowItems = allowUnknownItems;
        this.maxItems = maxItems;
        this.query = "";
        this.allowDuplicates = allowDuplicates;
        this.api = api;
        if (this.api) {
            options = [];
        }
        this.allOptions = [];
        this.options = [];
        let hasEmptyOption = options.some(obj => obj.value === null || obj.value === undefined || obj.value === '');
        if (hasEmptyOption) {
            allowEmpty = true;
        } else if (allowEmpty) {
            this.options.push({value: '', label: "---"});
        }
        for (let o of options) {
            this.options.push(o);
            this.allOptions.push(o);
        }

        this.dependsOn = dependsOn;
        this.allowEmpty = allowEmpty;
        this.clickedItemContent = null;

    }

    compareOptionWithValue(o,v){
        return this.equalsIgnoreCase(o.value,v);
    }

    getDependencyValue() {
        return this.dependsOn ? this.form?.getFieldValue(this.dependsOn) : null;
    }

    adaptResult(newOptions){
        return newOptions;
    }

    async loadOptions(query = "",exact=false) {
        this.options = [];
        if (this.allowEmpty && !exact) {
            this.options.push({value: '', label: "---"});
        }
        let itemsCount = this.maxItems;
        if (!this.api) {
            if(query) {
                let lowerQuery = query.toLowerCase();
                for (let o of this.allOptions) {
                    let label = String(o.label).toLowerCase();
                    let value = String(o.value).toLowerCase();
                    if (label.indexOf(lowerQuery) >= 0 || value.indexOf(lowerQuery) >= 0) {
                        this.options.push(o);
                        if (itemsCount < 0) break;
                        itemsCount--;
                    }
                }
            }
        } else {
            const result =await this.api({query,exact:exact, dependsOn: this.getDependencyValue()});
            const newOptions = this.adaptResult(result);
            if (newOptions) {
                for (const o of newOptions) {
                    this.options.push(o);
                    if (itemsCount < 0) break;
                    itemsCount--;
                }
            }
        }
        if(this.allowUnknowItems){
            let itemFounded = this.options.filter(x => x.value === this.value);
            if(itemFounded.length===0){
                const o={
                    value:this.value,
                    label:this.value
                };
                this.options.push(o);
            }
        }
        this.updateSelectedItem();
    }

    updateSelectedItem(){

    }

    reset() {
        this.value = '';
        this.options = [];
    }
}


class OptionFieldMulti extends OptionField{
    constructor({...rest}={}) {
        rest.allowEmpty=false;
        super(rest);
        this.temporaryValue=null;
    }
    setValueFromJSON(value,json) {
        if(!this.options){
            this.temporaryValue = value;
            return;
        }

        this.temporaryValue= null;
        if(value!=null) {
            if(Array.isArray(value) && value.length>0){
                if(!value[0].hasOwnProperty('value')){
                    value = this.options.filter(item => value.includes(item.value));
                }
            }
        }else{
            value=[];
        }
        this.value = value;
        this.bindValueToUi();
        if(!this.form)return;
        this.form.handleCascade(this.name, this.value);
    }
}

// 6️⃣ Simple Fields
class TextField extends Field {
    constructor({type = "text",step=null,min =null,max=null, ...rest}={}) {
        super(rest);
        this.step = step;
        this.type = type;
        this.min = min;
        this.max = max;
        this.input=null;
    }
    enable(){
        super.enable();
        this.mainElement.readOnly = false;
    }

    disable(){
        super.disable();
        this.mainElement.readOnly = true;
    }
    bindValueToUi(load=false) {
        super.bindValueToUi(load);
        if(this.value===null  || !this.value)this.value='';
        this.input.value=this.value;
    }

    render(passedContainer=null) {
        if(passedContainer!=null)this.container=passedContainer;
        if(!this.shouldRender())return null;
        let container = this.container;
        if(container==null){
            container = document.createElement("div");
            container.className = "f-field";
        }
        if(this.label) {
            const label = document.createElement("label");
            label.textContent = this.label + (this.required ? "*" : "");
            container.appendChild(label);
        }
        const input = document.createElement("input");
        this.input=input;
        if(this.type==="number"){
            input.inputMode="decimal";
            if(this.step)input.step = this.step;
            if(this.min)input.min = this.min;
            if(this.max)input.max = this.max;
        }else if(this.type==="integer"){
            input.type="number";
            if(this.step)input.step = 1;
            if(this.min)input.min = this.min;
            if(this.max)input.max = this.max;
        }else{
            input.type = this.type;
        }
        if(input.type==="password"){
            input.setAttribute("autocomplete", "new-password");
        }
        input.readOnly= this.readonly;
        input.value = this.value ?? "";
        input.oninput = e => {
            let value = e.target.value;
            if(this.type ==="number" || this.type ==="integer") {
                value = value.replace(/[^\d.,]/g, '');
            }

            this.setValue(value, this.value);
            this.validate();
        }
        container.appendChild(input);
        this._setContainer(container);
        this._setMainElement(input);
        return container;
    }
}

class ParagraphField extends Field {
    constructor({text,...rest}={}) {
        super(rest);
        this.defaultValue = text;
    }
    enable(){
        super.enable();
        this.mainElement.readOnly = false;
    }

    disable(){
        super.disable();
        this.mainElement.readOnly = true;
    }
    bindValueToUi(load=false) {
        super.bindValueToUi(load);
        if(this.value===null  || !this.value)this.value='';
        this.input.textContent=this.value;
    }

    render(passedContainer=null) {
        if(passedContainer!=null)this.container=passedContainer;
        if(!this.shouldRender())return null;
        let container = this.container;
        if(container==null){
            container = document.createElement("div");
            container.className = "f-field";
        }
        const input = document.createElement("p");
        this.input=input;
        input.textContent = this.defaultValue;
        container.appendChild(input);
        this._setContainer(container);
        this._setMainElement(input);
        return container;
    }
}


class HiddenField extends TextField{
    constructor({...rest}) {
        rest.type = "hidden";
        super(rest);
    }
    render(passedContainer=null) {
        if(passedContainer!=null)this.container=passedContainer;
        if(!this.shouldRender())return null;
        const input = document.createElement("input");
        this.input=input;
        input.type = "hidden";
        input.value = this.value ?? "";
        input.oninput = e => {
            this.setValue(e.target.value, this.value);
            this.validate();
        }

        this._setContainer(input);
        this._setMainElement(input);
        return input;
    }
}


class TextArea extends TextField {
    constructor({rows=3, ...rest}={}) {
        super(rest);
        this.rows =rows;
    }

    render(passedContainer=null) {
        if(passedContainer!=null)this.container=passedContainer;
        if(!this.shouldRender())return null;
        let container = this.container;
        if(container==null){
            container = document.createElement("div");
            container.className = "f-field";
        }
        if(this.label) {
            const label = document.createElement("label");
            label.textContent = this.label + (this.required ? "*" : "");
            container.appendChild(label);
        }
        const input = document.createElement("textarea");
        this.input=input;
        input.rows = this.rows;
        input.readOnly= this.readonly;
        input.value = this.value ?? "";
        input.oninput = e => {
            this.setValue(e.target.value, this.value);
            this.validate();
        }

        container.appendChild(input);
        this._setContainer(container);
        this._setMainElement(input);
        return container;
    }
}

class CheckboxField extends Field {
    constructor({checkedValue = true, uncheckedValue = false, ...rest}={}) {
        super(rest);
        this.checkedValue = checkedValue;
        this.uncheckedValue = uncheckedValue;
        if(!rest.defaultValue) {
            this.setValue(this.uncheckedValue);
        }
    }
    bindValueToUi(load=false){
        if(!this.mainElement)return;
        if(this.value===this.checkedValue){
            this.mainElement.checked=true;
        }else{
            this.mainElement.checked=false;
        }
    }


    render(passedContainer=null) {
        if(passedContainer!=null)this.container=passedContainer;
        if(!this.shouldRender())return null;
        let container = this.container;
        if(container==null){
            container = document.createElement("div");
            container.className = "f-field";
        }
        const label = document.createElement("label");
        label.className = "f-checkbox";
        const input = document.createElement("input");
        input.type = "checkbox";
        input.disabled = this.readonly;
        input.checked = this.value === this.checkedValue;
        input.onchange = e => {
            this.setValue(e.target.checked ? this.checkedValue : this.uncheckedValue, this.value);
            this.validate();
        }
        label.appendChild(input);
        label.appendChild(document.createTextNode(this.label+(this.required?"*":"")));
        container.appendChild(label);
        this._setContainer(container);
        this._setMainElement(input);
        return container;
    }
}

class DateField extends Field {
    constructor({type = "date", ...rest}={}) {
        super(rest);
        this.type = type;
    }

    bindValueToUi(load=false) {
        super.bindValueToUi(load);
        if(this.value===null  || !this.value)this.value='';
        this.input.value=this.value;
    }

    render(passedContainer=null) {
        if(passedContainer!=null)this.container=passedContainer;
        if(!this.shouldRender())return null;
        let container = this.container;
        if(container==null){
            container = document.createElement("div");
            container.className = "f-field";
        }
        if(this.label) {
            const label = document.createElement("label");
            label.textContent = this.label + (this.required ? "*" : "");
            container.appendChild(label);
        }
        const input = document.createElement("input");
        input.type = this.type;

        input.readOnly= this.readonly;
        input.value = this.value ?? "";
        input.onchange = e => {
            this.setValue(e.target.value, this.value);
            this.validate();
        }
        this.input=input;
        container.appendChild(input);
        this._setContainer(container);
        this._setMainElement(input);
        return container;
    }


    enable(){
        super.enable();
        this.mainElement.readOnly = false;
    }

    disable(){
        super.disable();
        this.mainElement.readOnly = true;
    }

}

// 7️⃣ Select Fields
class SelectField extends OptionField {
    constructor({debounce = 300,alternateSelect=null, ...rest}={}) {
        super(rest);
        this.inputEl = null;
        this.suggestionsEl = null;
        this.alternateSelect = alternateSelect;
    }


    updateSelectedItem(){
        if(this.options){
            let somethingSelected = false;
            for(let i=0;i<this.options.length;i++){
                const op = this.options[i];
                if(op.target) {
                    op.target.selected = this.compareOptionWithValue(op, this.value);
                    if(op.target.selected){
                        somethingSelected=true;
                    }
                }
            }
        }
    }


    bindValueToUi(load=false) {
        super.bindValueToUi(load);
        this.updateSelectedItem();
    }

    render(passedContainer=null) {
        if(passedContainer!=null)this.container=passedContainer;
        if(!this.shouldRender())return null;
        let container = this.container;
        if(container==null){
            container = document.createElement("div");
            container.className = "f-field";
        }
        if(this.label) {
            const label = document.createElement("label");
            label.textContent = this.label + (this.required ? "*" : "");
            container.appendChild(label);
        }
        const select = document.createElement("select");
        select.name = this.name;

        if (this.api) {
            this.loadOptions(this.query).then(() => this.renderOptions());
        }
        const renderOptions = () => {
            select.innerHTML = "";
            let somethingSelected = false;
            this.options.forEach(o => {
                this.clickedItemContent =o;
                const option = document.createElement("option");
                option.value = o.value;
                option.textContent = o.label;
                o.selected = false;
                o.target= option;
                if(this.value!=null){
                    if(this.compareOptionWithValue(o,this.value)){
                        somethingSelected = true;
                        o.selected=true;
                        option.selected = true;
                    }
                }else if (this.compareOptionWithValue(o,this.defaultValue)) {
                    somethingSelected = true;
                    o.selected=true;
                    option.selected = true;
                }
                select.appendChild(option);
            });
            if(!somethingSelected && this.alternateSelect && this.options.length>0){
                this.value = this.alternateSelect(this.value,this.getDependencyValue());
                for(let i=0;i<this.options.length;i++){
                    const op = this.options[i];
                    if(op.target) {
                        op.target.selected = this.compareOptionWithValue(op, this.value);
                        op.selected=op.target.selected;
                    }
                }
            }
        };
        renderOptions();
        select.readOnly= this.readonly;
        select.disabled = this.readonly;
        select.onchange = e => {
            this.setValue(e.target.value, this.value);
        }
        container.appendChild(select);
        this.renderOptions = renderOptions;
        this._setContainer(container);
        this._setMainElement(select);// for refresh if API loads
        return container;
    }
}

class MultiSelectField extends OptionFieldMulti {
    toObject() {
        return this.value ?? [];
    }

    bindValueToUi(load=false) {
        super.bindValueToUi(load);
        this.renderOptions();
    }

    render(passedContainer=null) {
        if(passedContainer!=null)this.container=passedContainer;
        if(!this.shouldRender())return null;
        let container = this.container;
        if(container==null){
            container = document.createElement("div");
            container.className = "f-field";
        }
        if(this.label) {
            const label = document.createElement("label");
            label.textContent = this.label + (this.required ? "*" : "");
            container.appendChild(label);
        }
        const select = document.createElement("select");
        select.multiple = true;

        if (this.api) {
            this.loadOptions(this.query).then(() => this.renderOptions());
        }
        const renderOptions = () => {
            select.innerHTML = "";
            this.options.forEach(o => {
                this.clickedItemContent =o;
                const option = document.createElement("option");
                option.value = o.value;
                option.textContent = o.label;

                if(this.value){
                    this.value.forEach(function(element, index, array) {
                        if(this.compareOptionWithValue(o,element))option.selected = true;
                    });
                }

                select.appendChild(option);
            });
        };
        renderOptions();

        select.readOnly= this.readonly;
        select.onchange = () => this.setValue([...select.selectedOptions].map(opt => opt.value), this.value);

        container.appendChild(select);
        this.renderOptions = renderOptions;
        this._setContainer(container);
        this._setMainElement(select);
        return container;
    }
}

// 8️⃣ Autocomplete Field (TEXT)
class AutocompleteField extends OptionField {
    constructor({
                    allowNewItems = false,
                    debounce = 300,
                    ...rest}={}) {
        super(rest);
        this.prevInputValue=null;
        this.search = debounceAsync(async (query) => {
            await this.loadOptions(query);
            this.refreshSuggestions();
            return this.options;
        }, debounce);
        this.allowNewItems = allowNewItems;
        this.inputEl = null;
        this.suggestionsEl = null;
        this.clickedItem = false;
        this.previous = {
            value:null,
            label:null
        }

    }

    getLabel(){
        return this.inputEl.value;
    }

    setValueFromJSON(value,json) {
        if(value==null && this.altValue){
            this.inputEl.value = getValueByPath(json, this.altValue);
            this.prevInputValue = this.inputEl.value;
            return;
        }
        this.value = value;
        this.bindValueToUi();
        if(!this.form)return;
        this.form.handleCascade(this.name, this.value);
    }

    bindValueToUi(load=false) {
        const me = this;
        super.bindValueToUi(load);
        this.loadOptions(me.value,true)
            .then(()=>{
                let index = me.options.findIndex(x => x.value === me.value);
                if(index>=0){
                    me.inputEl.value = me.options[index].label;
                    me.previous.label = me.options[index].label;
                    me.previous.value = me.options[index].value;

                }else if(me.options && me.options.length===1){
                    me.inputEl.value = me.options[0].label;
                    me.previous.label = me.options[0].label;
                    me.previous.value = me.options[0].value;
                }else if(me.allowNewItems){
                    me.inputEl.value = me.value;
                    if(me.suggestionsEl) {
                        me.suggestionsEl.style.display = "none";
                    }
                }else{
                    me.inputEl.value = null;
                }
            });
    }

    render(passedContainer=null) {
        if(passedContainer!=null)this.container=passedContainer;
        if(!this.shouldRender())return null;
        let container = this.container;
        if(container==null){
            container = document.createElement("div");
            container.className = "f-field f-autocomplete";
        }
        if(this.label) {
            const label = document.createElement("label");
            label.textContent = this.label + (this.required ? "*" : "");
            container.appendChild(label);
        }
        const input = document.createElement("input");
        input.type = "text";
        input.value = this.value ?? "";

        input.readOnly= this.readonly;
        input.placeholder = "...";
        const suggestions = document.createElement("div");
        suggestions.className = "f-suggestions";
        suggestions.style.display = "none";
        input.oninput = async e => {
            await this.search(e.target.value, this.value);
            setupBoundingRect(input, suggestions)
            suggestions.style.display = "block";
        };
        const me = this;
        input.addEventListener("blur", function () {
            if(this.readOnly)return;

            setTimeout(function () {
                if(me.options){
                for(let i=0;i<me.options.length;i++){
                    let o = me.options[i];

                    if(me.equalsIgnoreCase(o.value,me.inputEl.value)){
                        me.setValue(me.inputEl.value);
                        me.inputEl.value = o.label;
                        if(me.suggestionsEl) {
                            me.suggestionsEl.style.display = "none";
                        }
                        return;
                    }
                }
                }
                if(me.clickedItem){
                    me.clickedItem=false;
                    return;
                }
                if(me.prevInputValue === me.inputEl.value){
                    return;
                }
                if(me.allowNewItems){
                    me.setValue(me.inputEl.value);
                }else if(me.allowEmpty && me.inputEl.value===''){
                    me.setValue(me.inputEl.value);
                    me.previous.label='';
                    me.previous.value=null;
                }else{
                    me.setValue(me.previous.value);
                    me.inputEl.value=me.previous.label;
                }
                if(me.suggestionsEl) {
                    me.suggestionsEl.style.display = "none";
                }
            }, 500);

        });
        container.appendChild(input);
        document.body.appendChild(suggestions);
        this.inputEl = input;
        this.suggestionsEl = suggestions;
        this._setContainer(container);
        this._setMainElement(input);
        return container;
    }

    reset() {
        super.reset();
        this.inputEl.value='';
    }

    close(){
        if (!this.suggestionsEl) return;

        this.suggestionsEl.style.display = "none";
        this.suggestionsEl.innerHTML = "";
    }

    refreshSuggestions() {
        if (!this.suggestionsEl) return;
        this.suggestionsEl.innerHTML = "";
        this.positionAtBottomLeft(this.inputEl,this.suggestionsEl);
        const me = this;
        this.options.forEach(o => {
            const div = document.createElement("div");
            div.className = "f-suggestion";
            div.textContent = o.label;
            div.onclick = () => {
                me.clickedItemContent = o;
                me.clickedItem=true;
                me.setValue(o.value, me.value);
                me.inputEl.value = o.label;
                me.suggestionsEl.style.display = "none";
                me.suggestionsEl.innerHTML = "";
            };
            this.suggestionsEl.appendChild(div);
        });

    }
}

// 9️⃣ Combo Fields (ENTITY)
class ComboField extends OptionField {
    
    bindValueToUi(load=false){
        Array.from(this.select.options).forEach((option, index) => {
            option.selected = false;
            if(this.compareOptionWithValue(option,this.value)){
                this.select.selectedIndex=index;
                option.selected=true;
            }
        });
    }


    enable(){
        super.enable();
        this.mainElement.disabled = false;
    }

    disable(){
        super.disable();
        this.mainElement.disabled = true;
    }
    render(passedContainer=null) {
        if(passedContainer!=null)this.container=passedContainer;
        if(!this.shouldRender())return null;
        let container = this.container;
        if(container==null){
            container = document.createElement("div");
            container.className = "f-field";
        }
        if(this.label) {
            const label = document.createElement("label");
            label.textContent = this.label + (this.required ? "*" : "");
            container.appendChild(label);
        }
        const select = document.createElement("select");

        this.select = select;
        if (this.api) {
            this.loadOptions(this.query).then(() => this.renderOptions());
        }
        const renderOptions = () => {
            select.innerHTML = "";
            let selectedIndex = (this.options && this.options.length>0)?0:-1;
            this.options.forEach((o,index) => {
                const option = document.createElement("option");
                option.value = o.value;
                option.textContent = o.label;
                if(o.selected){
                    selectedIndex=index;
                }
                select.appendChild(option);
            });
            if(selectedIndex>=0) {
                select.selectedIndex = selectedIndex;
                this.setValueFromJSON(select.selectedOptions[0].value,{});
            }
        };
        renderOptions();

        select.disabled= this.readonly;
        select.onchange = e => {
            this.setValue(e.target.value, this.value);
        }
        container.appendChild(select);
        this.renderOptions = renderOptions;
        this._setContainer(container);
        this._setMainElement(select);
        return container;
    }
}

class ComboMultiField extends OptionFieldMulti {
    constructor({debounce = 300, ...rest}={}) {
        super(rest);
        this.value = [];
        this.search = debounceAsync(async (query) => {
            await this.loadOptions(query);
            this.refreshSuggestions();
            return this.options;
        }, debounce);
        this.inputEl = null;
        this.suggestionsEl = null;
        this.tagsEl = null;
    }


    close(){
        if (!this.suggestionsEl) return;

        this.suggestionsEl.style.display = "none";
        this.suggestionsEl.innerHTML = "";
    }

    bindValueToUi(load=false) {
        super.bindValueToUi(load);
        this.refreshTags();
    }

    render(passedContainer=null) {
        if(passedContainer!=null)this.container=passedContainer;
        if(!this.shouldRender())return null;
        let containerMain = this.container;
        if(containerMain==null){
            containerMain = document.createElement("div");
            containerMain.className = "f-field";
        }
        const container = document.createElement("div");
        container.className = "f-combo-multi";
        if(this.label) {
            const label = document.createElement("label");
            label.textContent = this.label + (this.required ? "*" : "");
            containerMain.appendChild(label);
        }
        const tags = document.createElement("div");
        tags.className = "f-tags";
        const input = document.createElement("input");
        input.type = "text";

        input.readOnly= this.readonly;
        input.placeholder = "...";
        const suggestions = document.createElement("div");
        suggestions.className = "f-suggestions";
        suggestions.style.display = "none";

        const me = this;
        this.refreshTags = () => {
            tags.innerHTML = "";
            this.value.forEach(v => {
                const span = document.createElement("span");
                span.className = "f-tag";
                span.textContent = v.label;
                span.dataset.key = v.value;
                span.innerHTML = `
        ${v.label}
        <button type="button">&times;</button>
      `;
                span.querySelector("button").onclick = () => {
                    this.value = this.value.filter(x => !this.equalsIgnoreCase(x.value,v.value));
                    me.refreshTags();
                };
                tags.appendChild(span);
            });
        };

        input.oninput = async e => {
            await this.search(e.target.value);
            setupBoundingRect(input, suggestions)
            suggestions.style.display = "block";
        };
        container.appendChild(tags);
        container.appendChild(input);
        document.body.appendChild(suggestions);
        containerMain.appendChild(container);
        this.inputEl = input;
        this.suggestionsEl = suggestions;
        this.tagsEl = tags;
        //this.refreshTags = refreshTags;
        this._setContainer(containerMain);
        this._setMainElement(input);
        if(this.defaultValue){
            this.setValueFromJSON(this.defaultValue);
        }
        return containerMain;
    }



    refreshSuggestions() {
        if (!this.suggestionsEl) return;
        this.suggestionsEl.innerHTML = "";
        this.positionAtBottomLeft(this.inputEl,this.suggestionsEl);
        this.options.forEach(o => {
            const div = document.createElement("div");
            div.className = "f-suggestion";
            div.textContent = o.label;
            div.onclick = () => {
                if (this.allowDuplicates) {
                    if (!this.value.filter(x => this.equalsIgnoreCase(x.value,o.value))) {
                        this.inputEl.value = "";
                        this.suggestionsEl.innerHTML = "";
                        this.suggestionsEl.style.display = "none";
                        return;
                    }
                }
                this.value.push(o);
                this.refreshTags();
                this.inputEl.value = "";
                this.suggestionsEl.innerHTML = "";
                this.suggestionsEl.style.display = "none";
            };
            this.suggestionsEl.appendChild(div);
        });
    }

    toObject() {
        return this.value.map(v => v.value);
    }

    reset() {
        if(this.defaultValue){
            this.value = this.defaultValue;
        }else {
            this.value = [];
        }
        this.options = [];
        this.refreshTags();
    }
}

class Button extends Node {
    constructor({readonly=false,...rest}={}) {
        super(rest);
        this.readonly = readonly;
    }
}

// 10️⃣ Buttons
class TextButton extends Button {
    constructor({label, onClick,readonly=false,offset=null,...rest}={}) {
        super(rest);
        this.label = label;
        this.onClick = onClick;
        this.offset= offset;
    }

    render(passedContainer=null) {
        if(passedContainer!=null)this.container=passedContainer;
        if(!this.shouldRender())return null;

        const btn = document.createElement("button");
        btn.textContent = this.label+(this.required?"*":"");
        btn.type = "button";
        btn.disabled = this.readonly;
        btn.className = "f-btn-text";
        btn.onclick = ()=>this.onClick(this);
        this._setContainer(btn);
        this._setMainElement(btn);
        return btn;
    }
}

class IconButton extends Button {
    constructor({
                    buttonClasses=[],
                    title='',
                    onClick,
                    readonly=false,...rest}={}) {
        super({readonly,...rest});
        this.onClick = onClick;
        this.title = title;
        this.buttonClasses = buttonClasses
    }

    render(passedContainer=null) {
        if(passedContainer!=null)this.container=passedContainer;
        if(!this.shouldRender())return null;
        const btn = document.createElement("button");
        btn.type = "button";//
        // btn.className = "f-btn-icon";
        btn.classList.add(...this.buttonClasses)
        btn.classList.add("icon-btn");
        btn.title = this.title;
        btn.setAttribute('data-tooltip',btn.title);
        btn.onclick = ()=>this.onClick(this);
        btn.disabled = this.readonly;

        this._setContainer(btn);
        this._setMainElement(btn);
        return btn;
    }
}


// 11️⃣ Form
class Form {
    constructor(...layout) {
        this.data = {};
        this.itemWithIds = [];
        this.itemWithNames = [];
        this.layout = [];
        this.fields = new Map();
        this.postRendered=false;

        this.conditionToRender =null;
        this.detached=false;
        this.name = '';
        for (const l of layout) {
            if (l && typeof l.attachForm === "function") {
                this.layout.push(l);
                l.attachForm(this);
            }else{
                if(l.detached){
                    this.detached=l.detached;
                }
                if(l.name){
                    this.name=l.name;
                }
            }
        }

    }


    withRenderCondition(conditionToRenderCallbackOrValue){
        this.conditionToRender = conditionToRenderCallbackOrValue;
        return this;
    }

    shouldRender() {
        if(this.conditionToRender===null)return true;
        if (typeof this.conditionToRender === 'function') {
            return !!this.conditionToRender(); // call the function and coerce to boolean
        }
        return !!this.conditionToRender; // coerce value to boolean
    }
    close(){
        for (const f of this.fields.values()) {
            f.close()
        }
    }

    getById(id){
        return this.itemWithIds[id];
    }

    registerId(item){
        if(item.id){
            this.itemWithIds[item.id]=item;
        }
    }



    getByName(id){
        return this.itemWithNames[id];
    }
    registerName(item){
        if(item.name){
            this.itemWithNames[item.name]=item;
        }
    }
    attachForm(form) {
        if(this.detached===true){
            for (const l of this.layout) {
                l.attachForm(this);
            }
            return;
        }
        for (const l of this.layout) {
            l.attachForm(form);
        }
    }

    registerField(field) {
        this.fields.set(field.name, field);
    }

    handleCascade(changed, value) {
        for (const f of this.fields.values()) {
            if (f.dependsOn === changed) {
                if (typeof f.loadOptions === "function") {
                    f.reset();
                    f.loadOptions(value).then(() => f.renderOptions());
                }

            }
        }
    }

    getFieldValue(name) {
        return this.fields.get(name)?.value;
    }

    getField(name) {
        return this.fields.get(name);
    }

    load(json) {
        this.data = json;
        for (const k of this.fields.keys()) {
            const value = getValueByPath(json, k);

            if (value !== undefined) {
                this.fields.get(k)?.setValueFromJSON(value,json);
            }
        }
    }

    async loadOptions() {
        for (const f of this.fields.values()) if (f.loadOptions && !f.dependsOn) await f.loadOptions("");
    }

    submit(callback) {
        if (this.validate()) {
            callback(this.toObject(true));
        }
    }

    validate() {
        let valid = true;
        for (const f of this.fields.values()) {
            valid = f.validate() && valid;
        }
        if (!valid) {
            showError(this.getErrorsString());
        }
        return valid;
    }

    getErrors() {
        let errors = [];
        for (const f of this.fields.values()) {
            let errs = f.getErrors();
            if (!errs) continue;
            errors.push(errs);
        }
        return errors;
    }

    getErrorsString() {
        let errors = [];
        errors.push("<ul class='f-form-errors'>");
        for (const e of this.getErrors()) {
            errors.push("<li>" + e.item.getErrorsString() + "</li>");
        }
        errors.push("</ul>");

        return errors.join("\n<br>");
    }

    toObject(sendableOnly = false) {
        if(this.data==null || typeof this.data ==="undefined")this.data = {};
        let toSend = this.data;
        for (const fkey of this.fields.keys()) {
            let f = this.fields.get(fkey);
            if (sendableOnly && !f.isSendable()) continue;
            setValueByPath(toSend,fkey,f.toObject());
            //toSend.set(f.name, f);
        }
        //return Object.fromEntries(toSend.entries().map(([k, f]) => [k, f.toObject()]));
        return toSend;
    }

    reset() {
        for (const fkey of this.fields.keys()) {
            let f = this.fields.get(fkey);
            f.reset();
        }
    }

    postRender(){

        if(!this.postRendered){
            for (const l of this.layout) {
                l.postRender();
            }
        }
    }

    render(passedContainer=null) {
        if(passedContainer!=null)this.container=passedContainer;
        if(!this.shouldRender())return null;
        let container = this.container;
        if(container==null){
            container = document.createElement("div");
            container.className = "f-form";
        }
        this.container = container;
        this.layout.forEach(c => {
            let cx = c.render();
            if(cx)container.appendChild(cx);
        });
        this.postRendered =true;
        const observer = new MutationObserver(() => {
            if (container.isConnected) {
                if (document.body.contains(container)) {
                    observer.disconnect(); // stop observing if you only care about first attach
                    this.layout.forEach(c => c.postRender());
                }
            }
        });
        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
        return container;
    }
}

function setValueByPath(obj, path, value) {
    return path.split(".").reduce((acc, key, i, arr) => {
        if (i === arr.length - 1) {
            acc[key] = value; // set final value
        } else {
            acc[key] ??= {}; // create object if missing
        }
        return acc[key];
    }, obj);
}

function getValueByPath(obj, path){
    if(typeof path === "undefined")return null;
    return path
        .split(".")
        .reduce((acc, key) => acc?.[key], obj);
}

class RadioButtonGroup extends Field {
    constructor({options = [], ...rest}={}) {
        super(rest);
        this.options = options;
    }

    render(passedContainer=null) {
        if(passedContainer!=null)this.container=passedContainer;
        if(!this.shouldRender())return null;
        let container = this.container;
        if(container==null){
            container = document.createElement("div");
            container.className = "f-field f-radio-group";
        }
        const label = document.createElement("label");
        label.textContent = this.label+(this.required?"*":"");
        container.appendChild(label);
        this.options.forEach(opt => {
            const wrapper = document.createElement("label");
            wrapper.className = "f-radio";
            const input = document.createElement("input");
            input.type = "radio";
            input.name = this.name;
            input.value = opt.value;
            input.readOnly = this.readonly;
            input.checked = this.value === opt.value;
            input.onchange = () => {
                this.setValue(opt.value, this.value);
                this.validate();
            }
            wrapper.appendChild(input);
            wrapper.appendChild(document.createTextNode(opt.label));
            container.appendChild(wrapper);
        });
        this._setContainer(container);
        return container;
    }
}

class CheckboxGroup extends OptionFieldMulti {
    constructor({...rest}={}) {
        rest.allowEmpty = false;
        rest.allowDuplicates = false;
        rest.maxItems = Infinity;
        super(rest);
        this.value = [];
    }

    toObject() {
        return this.value ?? [];
    }

    bindValueToUi(load=false) {
        super.bindValueToUi(load);
        this.refreshOptions();
    }

    refreshOptions(){
        this.options.forEach(opt => {
            let input = opt.input;
        });
    }
    render(passedContainer=null) {
        if(passedContainer!=null)this.container=passedContainer;
        if(!this.shouldRender())return null;
        let container = this.container;
        if(container==null){
            container = document.createElement("div");
            container.className = "f-field f-checkbox-group";
        }
        const label = document.createElement("label");
        label.textContent = this.label+(this.required?"*":"");
        container.appendChild(label);
        this.options.forEach(opt => {
            const wrapper = document.createElement("label");
            wrapper.className = "f-checkbox";
            const input = document.createElement("input");
            opt.input = input;
            input.type = "checkbox";
            input.value = opt.value;
            input.readOnly = this.readonly;
            input.checked = this.value.includes(opt.value);
            input.onchange = () => {
                let oldValues = [...this.value];
                if (input.checked) {
                    this.value.push(opt.value);
                } else {
                    this.value = this.value.filter(v => v !== opt.value);
                }
                this.setValue(this.value, oldValues);
                this.validate();
            };
            wrapper.appendChild(input);
            wrapper.appendChild(document.createTextNode(opt.label));
            container.appendChild(wrapper);
        });
        this._setMainElement(container);
        this._setContainer(container);
        return container;
    }
}

class Tabbed extends Node {
    constructor({onchange = null,...rest} = {}, ...tabs) {
        super(rest);
        this.onchange = onchange;
        this.withChildren(...tabs);
        this.activeIndex = 0;
        this.tabsContent = [];
    }

    attachForm(form) {
        super.attachForm(form);
        this.tabs.forEach(c => {
            c.content.parent = this;
            c.content.attachForm(form);
        });
    }

    /**
     *
     * @param children
     * @returns {Tabbed}
     */
    withChildren(...children) {
        if (!children) return this;
        const realChildrenCount = children.length;
        if (realChildrenCount === 0) return this;
        this.tabs = children;
        return this;
    }

    postRender() {
        if(!this.shouldRender())return;
        super.postRender();
        this.tabs.forEach((tab, idx) => {
            tab.content.postRender();
        });
    }

    render(passedContainer=null) {
        if(passedContainer!=null)this.container=passedContainer;
        if(!this.shouldRender())return null;
        if(this.id!==null){
            let possibleSelected = localStorage.getItem(window.location.href+"#"+this.id);
            if(possibleSelected!=null){
                this.activeIndex = parseInt(possibleSelected);
            }
        }
        let container = this.container;
        if(container==null){
            container = document.createElement("div");
            container.className = "f-tabs";
        }
        const tabHeaders = document.createElement("div");
        tabHeaders.className = "f-tab-headers";
        const tabContents = document.createElement("div");
        tabContents.className = "f-tab-contents";
        const me = this;
        this.tabs.forEach((tab, idx) => {
            let rendered = tab.content.render()
            if(!rendered)return;
            const btn = document.createElement("button");
            btn.className = "f-tab-header" + (idx === this.activeIndex ? " active" : "");
            btn.textContent = tab.title;
            btn.onclick = () => {
                const oldindex = me.activeIndex;
                const oldcontent = tabContents.children[oldindex];
                this.activeIndex = idx;
                localStorage.setItem(window.location.href+"#"+me.id,me.activeIndex);
                if (this.onchange) {
                    this.onchange({
                        index: this.activeIndex,
                        content: tabContents.children[this.activeIndex]
                    }, {index: oldindex, content: oldcontent}, this);
                }
                renderTabs();
            };
            tabHeaders.appendChild(btn);

            rendered.style.display = idx === this.activeIndex ? "block" : "none";
            tabContents.appendChild(rendered);
            this.tabsContent.push(rendered);
        });
        const renderTabs = () => {
            Array.from(tabHeaders.children).forEach((btn, idx) => {
                btn.classList.toggle("active", idx === this.activeIndex);
            });
            for (let i = 0; i < this.tabs.length; i++) {
                if (i === this.activeIndex) {
                    this.tabsContent[i].style.display = "block";
                } else {
                    this.tabsContent[i].style.display = "none";
                }
            }
        };

        renderTabs();
        container.appendChild(tabHeaders);
        container.appendChild(tabContents);
        this._setMainElement(container);
        this._setContainer(container);
        return container;
    }
}


class Collapsible extends Node {
    constructor({title, open = false,
                    onchange = null,...rest} = {}, ...children) {
        super(rest);
        this.onchange = onchange;
        this.title = title;
        this.open = open;
        this.withChildren(...children);
    }

    attachForm(form) {
        super.attachForm(form);
        if(this.children)this.children.forEach(c => {
            c.parent = this;
            c.attachForm(form);
        });
    }

    withChildren(...children) {
        if (!children) return this;
        const realChildrenCount = children.length;
        if (realChildrenCount === 0) return this;
        this.children = children;
        return this;
    }


    render(passedContainer=null) {
        if(passedContainer!=null)this.container=passedContainer;
        if(!this.shouldRender())return null;
        let container = this.container;
        if(container==null){
            container = document.createElement("div");
            container.className = "f-collapsible";
        }
        const header = document.createElement("div");
        header.className = "f-collapsible-header";
        header.textContent = this.title;
        const me = this;
        header.onclick = () => {
            let previous = me.open;
            me.open = !me.open;
            contentDiv.style.display = me.open ? "block" : "none";
            container.classList.toggle("open", me.open);
            if (me.onchange) {
                me.onchange(me.open, previous, me);
            }
        };
        const contentDiv = document.createElement("div");
        contentDiv.className = "f-collapsible-content";
        contentDiv.style.display = this.open ? "block" : "none";
        if(this.children)this.children.forEach(c => {
            let cx = c.render();
            if(cx)contentDiv.appendChild(cx);
        });
        container.appendChild(header);
        container.appendChild(contentDiv);
        if (this.open) container.classList.add("open");
        this._setMainElement(container);
        this._setContainer(container);
        return container;
    }
}


class Forms {
    constructor(...forms) {
        this.data = {};
        this.forms = forms;
        this.itemWithNames =[];
        this.itemWithIds = [];
        this.fields = new Map();
        this.conditionToRender = null;
        this.detached=false;

        this.name = '';
        for (const l of forms) {
            if (l && typeof l.attachForm === "function") {
                l.attachForm(this);
            }else{
                if(l.detached){
                    this.detached=l.detached;
                }
                if(l.name){
                    this.name=l.name;
                }
            }
        }
    }

    reset() {
        for (const fkey of this.fields.keys()) {
            let f = this.fields.get(fkey);
            f.reset();
        }
    }


    withRenderCondition(conditionToRenderCallbackOrValue){
        this.conditionToRender = conditionToRenderCallbackOrValue;
        return this;
    }

    shouldRender() {
        if(this.conditionToRender===null)return true;
        if (typeof this.conditionToRender === 'function') {
            return !!this.conditionToRender(); // call the function and coerce to boolean
        }
        return !!this.conditionToRender; // coerce value to boolean
    }


    close(){
        for (const f of this.fields.values()) {
            f.close()
        }
    }

    registerField(field) {
        this.fields.set(field.name, field);
        this.registerId(field);
        this.registerName(field);
    }



    getById(id){
        return this.itemWithIds[id];
    }

    registerId(item){
        if(item.id){
            this.itemWithIds[item.id]=item;
        }
    }



    getByName(id){
        return this.itemWithNames[id];
    }
    registerName(item){
        if(item.name){
            this.itemWithNames[item.name]=item;
        }
    }

    handleCascade(changed, value) {
        for (const f of this.fields.values()) {
            if (f.dependsOn === changed) {
                if (typeof f.loadOptions === "function") {
                    f.reset();
                    f.loadOptions(value).then(() => f.renderOptions());
                }
            }
        }
    }

    getFieldValue(name) {
        return this.fields.get(name)?.value;
    }

    load(json) {
        this.data = json;
        for (const k of this.fields.keys()) {
            const value = getValueByPath(json, k);

            if (value !== undefined) {
                this.fields.get(k)?.setValueFromJSON(value,json);
            }
        }
    }

    async loadOptions() {
        for (const f of this.fields.values()) if (f.loadOptions && !f.dependsOn) await f.loadOptions("");
    }

    submit(callback) {
        if (this.validate()) {
            callback(this.toObject(true));
        }
    }

    validate() {
        let valid = true;
        for (const f of this.fields.values()) {
            valid = f.validate() && valid;
        }
        if (!valid) {
            showError(this.getErrorsString());
        }
        return valid;
    }

    getErrors() {
        let errors = [];
        for (const f of this.fields.values()) {
            let errs = f.getErrors();
            if (!errs) continue;
            errors.push(errs);
        }
        return errors;
    }

    getErrorsString() {
        let errors = [];
        errors.push("<ul class='f-form-errors'>");
        for (const e of this.getErrors()) {
            errors.push("<li>" + e.item.getErrorsString() + "</li>");
        }
        errors.push("</ul>");

        return errors.join("\n<br>");
    }

    toObject(sendableOnly = false) {
        if(this.data==null || typeof this.data ==="undefined")this.data = {};
        let toSend = this.data;
        for (const fkey of this.fields.keys()) {
            let f = this.fields.get(fkey);
            if (sendableOnly && !f.isSendable()) continue;
            setValueByPath(toSend,fkey,f.toObject());
            //toSend.set(f.name, f);
        }
        //return Object.fromEntries(toSend.entries().map(([k, f]) => [k, f.toObject()]));
        return toSend;
    }

    render(where = null) {
        let torender = [];


        const observer = new MutationObserver(() => {
            if (where && where.isConnected) {
                if (document.body.contains(where)) {
                    observer.disconnect(); // stop observing if you only care about first attach
                    for (const l of this.forms) {
                        l.postRender();
                    }
                }
            }
        });
        observer.observe(document.body, {
            childList: true,
            subtree: true
        });

        for (const l of this.forms) {
            const rendered = l.render();
            if(rendered) {
                if (where) {
                    where.appendChild(rendered);
                }
                torender.push(rendered);
            }
        }

        return torender;
    }
}



// 12️⃣ Dialog
/**
 * On-the-fly modal dialog that wraps a Form/Forms/Node and blocks the background.
 *
 * Usage:
 *   new Dialog({
 *       title: "Edit Record",
 *       content: myForm,                  // Form | Forms | Node instance
 *       size: "md",                       // "sm" | "md" | "lg" | "xl" (optional, default "md")
 *       onConfirm: (data) => { ... },     // called with form.toObject() on confirm
 *       onCancel: () => { ... },          // called on cancel / close
 *       confirmLabel: "Save",             // optional, default "OK"
 *       cancelLabel: "Cancel",            // optional, default "Cancel"
 *       showCancel: true,                 // optional, default true
 *       closeOnOverlay: true,             // optional, default false
 *   }).open();
 *
 * The dialog validates the form before calling onConfirm.
 * Call dialog.close() to close it programmatically.
 */
class Dialog {
    constructor({
                    title = "",
                    content,                        // Form | Forms | Node | HTMLElement
                    size = "md",
                    onConfirm = null,
                    onConfirmed = null,
                    onCancel = null,
                    confirmLabel = null,
                    cancelLabel = null,
                    showCancel = true,
                    closeOnOverlay = false,
                } = {}) {
        this.title = title;
        this.content = content;
        this.size = size;
        this.onConfirm = onConfirm;
        this.onConfirmed= onConfirmed;
        this.onCancel = onCancel;
        this.confirmLabel = confirmLabel ?? translate("OK");
        this.cancelLabel = cancelLabel ?? translate("CANCEL");
        this.showCancel = showCancel;
        this.closeOnOverlay = closeOnOverlay;

        this._overlay = null;
        this._keydownHandler = null;
    }

    /** Open and mount the dialog. Returns this for chaining. */
    open(data) {
        if (this._overlay) return this; // already open

        // --- overlay ---
        const overlay = document.createElement("div");
        overlay.className = "dialog-overlay";
        this._overlay = overlay;

        // --- dialog box ---
        const box = document.createElement("div");
        box.className = `dialog-box dialog-${this.size}`;

        // header
        const header = document.createElement("div");
        header.className = "dialog-header";

        const titleEl = document.createElement("div");
        titleEl.className = "dialog-title";
        titleEl.textContent = this.title;

        const closeBtn = document.createElement("button");
        closeBtn.className = "dialog-close-btn";
        closeBtn.type = "button";
        closeBtn.innerHTML = "&times;";
        closeBtn.title = translate("CLOSE");
        closeBtn.onclick = () => this._cancel();

        header.appendChild(titleEl);
        header.appendChild(closeBtn);

        // body
        const body = document.createElement("div");
        body.className = "dialog-body";

        if (this.content) {
            if (this.content instanceof Form) {
                let cx = this.content.render();
                if(cx)body.appendChild(cx);
            } else if (this.content instanceof Forms) {
                this.content.render(body);
            }
            if(data){
                this.content.load(data);
            }
        }

        // footer
        const footer = document.createElement("div");
        footer.className = "dialog-footer";

        if (this.showCancel) {
            const cancelBtn = document.createElement("button");
            cancelBtn.className = "notification-dialog-button secondary";
            cancelBtn.type = "button";
            cancelBtn.textContent = this.cancelLabel;
            cancelBtn.onclick = () => this._cancel();
            footer.appendChild(cancelBtn);
        }

        const confirmBtn = document.createElement("button");
        confirmBtn.className = "notification-dialog-button primary";
        confirmBtn.type = "button";
        confirmBtn.textContent = this.confirmLabel;
        confirmBtn.onclick = () => this._confirm();
        footer.appendChild(confirmBtn);

        box.appendChild(header);
        box.appendChild(body);
        box.appendChild(footer);
        overlay.appendChild(box);
        document.body.appendChild(overlay);

        // close on overlay click (optional)
        if (this.closeOnOverlay) {
            overlay.addEventListener("click", (e) => {
                if (e.target === overlay) this._cancel();
            });
        }

        // keyboard: Enter → confirm, Escape → cancel
        this._keydownHandler = (e) => {
            if (e.key === "Escape") { e.preventDefault(); this._cancel(); }
            if (e.key === "Enter" && e.target.tagName !== "TEXTAREA" && e.target.tagName !== "SELECT") {
                e.preventDefault();
                this._confirm();
            }
        };
        document.addEventListener("keydown", this._keydownHandler);

        // animate in
        requestAnimationFrame(() => overlay.classList.add("dialog-open"));

        return this;
    }

    /** Close and remove the dialog from the DOM. */
    close() {
        if (!this._overlay) return;
        const overlay = this._overlay;
        overlay.classList.remove("dialog-open");
        overlay.addEventListener("transitionend", () => {
            if (overlay.parentNode) overlay.parentNode.removeChild(overlay);
        }, {once: true});
        if (this._keydownHandler) {
            document.removeEventListener("keydown", this._keydownHandler);
            this._keydownHandler = null;
        }
        if(this.content) this.content.close();
        this._overlay = null;
    }

    _confirm() {
        if (this.content && typeof this.content.validate === "function") {
            if (!this.content.validate()) return;
        }
        const data = (this.content && typeof this.content.toObject === "function")
            ? this.content.toObject(true)
            : null;
        if (this.onConfirm) {
            if(this.onConfirm(data,this)){
                this.close();
                if(this.onConfirmed) {
                    this.onConfirmed(data, this);
                }
            }
        }else{
            this.close();
            if(this.onConfirmed) {
                this.onConfirmed(data, this);
            }
        }
    }

    _cancel() {
        this.close();
        if (this.onCancel) this.onCancel(this);
    }
}

/**
 * Convenience factory — creates and immediately opens a Dialog.
 *
 * window.showDialog({
 *   title: "...",
 *   content: form,
 *   onConfirm: (data) => { ... }
 * });
 */
window.showDialog = function (options) {
    return new Dialog(options).open();
};

class NativeDatePicker {
    constructor(container, options = {}) {
        this.container = typeof container === 'string'
            ? document.querySelector(container)
            : container;

        if (!this.container) {
            throw new Error('Invalid container element');
        }

        this.options = {
            showText: true,                 // Show formatted text
            locale: navigator.language || 'en-US',
            initialDate: new Date(),
            snapToMonday: true,             // Snap to Monday
            dateFormat: 'yyyy-mm-dd',       // Default locale format
            onDaySelect: null,              // Callback: date selected
            onWeekSelect: null,             // Callback: week (Monday) selected
            ...options
        };

        this._build();
        this._init();
        this._attachEvents();
    }

    // -----------------------------
    // Build DOM
    // -----------------------------
    _build() {

        this.container.picker=this;
        this.container.innerHTML = '';



        if (this.options.showText) {
            this.dateText = document.createElement('div');
            this.dateText.style.cursor = 'pointer';
            this.dateText.style.padding = '6px 10px';
            this.dateText.style.border = '1px solid #ccc';
            this.dateText.style.display = 'inline-block';
            this.dateText.style.borderRadius = '4px';
            this.container.appendChild(this.dateText);
        }

        this.dateInput = document.createElement('input');
        this.dateInput.type = 'date';

        if (this.options.showText) {
            if(isMobileDevice()){
                // Keep input accessible for Android but visually hidden
                this.dateInput.style.position = 'absolute';
                this.dateInput.style.opacity = '0';
                this.dateInput.style.width = '1px';
                this.dateInput.style.height = '1px';
                this.dateInput.style.left = '-9999px';
                this.dateInput.style.top = '0';
            }else{
                this.dateInput.style.display = 'none';
            }
        }

        this.container.appendChild(this.dateInput);
    }

    // -----------------------------
    // Initialization
    // -----------------------------
    _init() {
        const date = this.options.snapToMonday
            ? this._getMonday(this.options.initialDate)
            : new Date(this.options.initialDate);
        this._setDate(date);
        //this._triggerCallbacks(date);
    }

    // -----------------------------
    // Event Handling
    // -----------------------------
    _attachEvents() {
        const me = this;
        if (this.options.showText) {
            this.dateText.addEventListener('click', () => {
                me.dateInput.showPicker?.();
                me.dateInput.focus();
            });
        }

        this.dateInput.addEventListener('change', () => {
            let selectedDate = new Date(me.dateInput.value);

            if (me.options.snapToMonday) {
                selectedDate = me._getMonday(selectedDate);
            }

            me._setDate(selectedDate);
            me._triggerCallbacks(selectedDate);
        });
    }

    // -----------------------------
    // Core Logic
    // -----------------------------
    _getMonday(date) {
        const day = date.getDay();
        const diff = (day === 0 ? -6 : 1 - day);
        const monday = new Date(date);
        monday.setDate(date.getDate() + diff);
        return monday;
    }

    _formatDate(date) {
        return date.toLocaleDateString(this.options.locale, {
            year: 'numeric',
            month: 'long',
            weekday: 'long',
            day: 'numeric'
        });
    }

    _setDate(date) {
        const iso = date.toISOString().split('T')[0];
        this.dateInput.value = iso;

        if (this.options.showText) {
            this.dateText.textContent = this._formatDate(date);
        }

        this.currentDate = date;
    }

    // -----------------------------
    // Callbacks
    // -----------------------------
    _triggerCallbacks(date) {
        if (typeof this.options.onDaySelect === 'function') {
            this.options.onDaySelect(new Date(date),this._formatLocaleDate(new Date(date)));
        }

        if (typeof this.options.onWeekSelect === 'function') {
            const monday = this._getMonday(date);
            this.options.onWeekSelect(new Date(monday),this._formatLocaleDate(new Date(monday)));
        }
    }

    // -----------------------------
    // Public API
    // -----------------------------
    getDate() {
        return new Date(this.currentDate);
    }

    setDate(date) {
        let newDate = new Date(date);
        if (this.options.snapToMonday) {
            newDate = this._getMonday(newDate);
        }
        this._setDate(newDate);
        this._triggerCallbacks(newDate);
    }

    // -----------------------------
    // Locale-formatted getters/setters
    // -----------------------------
    getLocaleDate() {
        return this._formatLocaleDate(this.currentDate);
    }

    setLocaleDate(dateString) {
        const date = this._parseLocaleDate(dateString);
        this.setDate(date);
    }

    _formatLocaleDate(date) {
        const yyyy = date.getFullYear();
        const mm = String(date.getMonth() + 1).padStart(2, '0');
        const dd = String(date.getDate()).padStart(2, '0');

        // Replace tokens in format string
        return this.options.dateFormat
            .replace('yyyy', yyyy)
            .replace('mm', mm)
            .replace('dd', dd);
    }

    _parseLocaleDate(dateString) {
        const fmt = this.options.dateFormat;
        let year = 1970, month = 0, day = 1;

        if (fmt === 'yyyy-mm-dd') {
            [year, month, day] = dateString.split('-').map(Number);
            month -= 1;
        } else if (fmt === 'yyyy/mm/dd') {
            [year, month, day] = dateString.split('/').map(Number);
            month -= 1;
        } else if (fmt === 'yyyy/dd/mm') {
            [year, day, month] = dateString.split('/').map(Number);
            month -= 1;
        } else if (fmt === 'dd/mm/yyyy') {
            [day, month, year] = dateString.split('/').map(Number);
            month -= 1;
        } else if (fmt === 'mm/dd/yyyy') {
            [month, day, year] = dateString.split('/').map(Number);
            month -= 1;
        }

        return new Date(year, month, day);
    }

    destroy() {

        this.container.picker=null;
        this.container.innerHTML = '';
    }
}




function joinNonEmpty(separator, ...strings) {
    return strings
        .filter(s => s != null && s !== '') // remove null, undefined, and empty strings
        .join(separator);
}

function trimIfLong(str, maxLength) {
    if (typeof str !== 'string') return str; // handle non-string input safely
    return str.length > maxLength ? str.slice(0, maxLength) : str;
}

class UploadField extends TextField {

    isBinaryFile(filename) {
        const textExtensions = new Set([
            "txt", "md", "js", "ts", "json", "html", "css",
            "xml", "csv", "yml", "yaml"
        ]);

        const ext = filename.split('.').pop().toLowerCase();
        return !textExtensions.has(ext);
    }
    constructor({accept=[],onFileUpload=null,multiple=false,...rest}={}) {
        rest.type="text";
        super(rest);
        this.fileInput = null;
        this.input = null;
        this.dropZone = null;
        this.tagsEl = null;
        this.multiple = multiple;
        if(accept.length===0){
            this.accept="*/*";
        }else{
            this.accept=accept.join(",");
        }
        this.onFileUpload = onFileUpload;
    }

    openFileBrowser(){
        if(this.readonly)return;
        this.fileInput.value = '';
        this.fileInput.dispatchEvent(new MouseEvent('click', { bubbles: true }));
    }

    matchesAccept(file){
        if(this.accept==="*/*")return true;
        const name = file.name.toLowerCase();
        const type = (file.type||"").toLowerCase();
        return this.accept.split(",")
            .map(p => p.trim().toLowerCase())
            .filter(p => p.length>0)
            .some(p => {
                if(p.startsWith("."))return name.endsWith(p);
                if(p.endsWith("/*"))return type.startsWith(p.slice(0,-1));
                return type===p;
            });
    }

    enable(){
        this.readonly = false;
        if(this.dropZone)this.dropZone.classList.remove("f-dropzone-disabled");
        this.refreshTags();
    }

    disable(){
        this.readonly = true;
        if(this.dropZone)this.dropZone.classList.add("f-dropzone-disabled");
        this.refreshTags();
    }

    bindValueToUi(load=false) {
        if(!Array.isArray(this.value))this.value=[];
        this.refreshTags();
    }

    refreshTags(){
        if(!this.tagsEl)return;
        const me = this;
        this.tagsEl.innerHTML = "";
        const files = Array.isArray(this.value)?this.value:[];
        files.forEach(f => {
            const span = document.createElement("span");
            span.className = "f-tag f-file-tag";
            const name = document.createElement("span");
            name.textContent = f.name;
            span.appendChild(name);
            if(!me.readonly){
                const btn = document.createElement("button");
                btn.type = "button";
                btn.innerHTML = "&times;";
                btn.onclick = e => {
                    e.stopPropagation();
                    me.removeFile(f);
                };
                span.appendChild(btn);
            }
            me.tagsEl.appendChild(span);
        });
        if(this.dropZone){
            this.dropZone.classList.toggle("f-dropzone-empty", files.length===0);
        }
    }

    removeFile(file){
        this.value = (Array.isArray(this.value)?this.value:[]).filter(x => x!==file);
        this.refreshTags();
        this.validate();
        if(this.onFileUpload){
            this.onFileUpload(this,this.value);
        }
    }

    postRender() {
        if(!this.shouldRender())return;
        super.postRender();
        const me = this;
        this.fileInput.addEventListener('change', () => {
            me.handleUploads(Array.from(me.fileInput.files)).catch(err => {
                console.error(err);
            });
        });
    }

    render(passedContainer=null) {
        if(passedContainer!=null)this.container=passedContainer;
        if(!this.shouldRender())return null;
        let container = this.container;
        if(container==null){
            container = document.createElement("div");
            container.className = "f-field";
        }
        const label = document.createElement("label");
        label.textContent = this.label+(this.required?"*":"");
        const fileInput = document.createElement("input");
        fileInput.type = "file";
        fileInput.multiple = this.multiple;
        fileInput.accept = this.accept;
        fileInput.style = "display:none;";
        this.fileInput = fileInput;

        const dropZone = document.createElement("div");
        dropZone.className = "f-dropzone f-dropzone-empty";
        if(this.readonly)dropZone.classList.add("f-dropzone-disabled");
        const hint = document.createElement("span");
        hint.className = "f-dropzone-hint";
        hint.textContent = translate('DROP_FILES_HERE') ||
            (this.multiple ? "Drag files here or click to browse" : "Drag a file here or click to browse");
        const tags = document.createElement("div");
        tags.className = "f-tags";
        dropZone.appendChild(hint);
        dropZone.appendChild(tags);
        this.dropZone = dropZone;
        this.tagsEl = tags;
        this.input = dropZone;

        const me = this;
        dropZone.onclick = () => me.openFileBrowser();
        dropZone.ondragover = e => {
            e.preventDefault();
            if(!me.readonly)dropZone.classList.add("f-dropzone-over");
        };
        dropZone.ondragleave = () => dropZone.classList.remove("f-dropzone-over");
        dropZone.ondrop = e => {
            e.preventDefault();
            dropZone.classList.remove("f-dropzone-over");
            if(me.readonly)return;
            me.handleUploads(Array.from(e.dataTransfer.files)).catch(err => {
                console.error(err);
            });
        };

        container.appendChild(label);
        container.appendChild(fileInput);
        container.appendChild(dropZone);
        this._setContainer(container);
        this._setMainElement(dropZone);
        this.refreshTags();

        return container;
    }
    clear(){
        this.value=[];
        this.fileInput.value="";
        this.refreshTags();
    }

    async handleUploads(files) {
        if(!files || files.length===0)return;
        files = files.filter(f => this.matchesAccept(f));
        if(files.length===0)return;
        startWaitingWheel();
        if(!this.multiple){
            this.value=[];
            files = files.slice(0,1);
        }else if(!Array.isArray(this.value)){
            this.value=[];
        }
        try {
            for (let i = 0; i < files.length; i++) {
                const file = files[i];
                // replace a previously selected file with the same name
                this.value = this.value.filter(x => x.name!==file.name);
                await this.handleUpload(file);
            }
            this.refreshTags();
            this.validate();
            if(this.onFileUpload){
                this.onFileUpload(this,this.value);
            }
        }catch (e){
            console.error(e);
        }
        stopWaitingWheel();
    }

    async handleUpload(file) {
        const me=this;
        return new Promise((resolve, reject) => {
            const reader = new FileReader();

            reader.onload = async (e) => {
                try {
                    const content = e.target.result;
                    me.value.push({
                        name:file.name,
                        extension: file.name.includes('.') ? file.name.split('.').pop().toLowerCase() : '',
                        content:content,
                        binary:me.isBinaryFile(file.name)
                    })
                    resolve("OK");
                } catch (err) {
                    reject(err);
                }
            };

            reader.onerror = reject;
            if(me.isBinaryFile(file.name)){
                reader.readAsDataURL(file);
            }else {
                reader.readAsText(file); // or readAsDataURL / readAsArrayBuffer
            }
        });
    }
}

class StructurlessForm extends Form {
    constructor(container=null,...layout) {
        super(...layout);
        this.container = container;
    }

    reset() {
        for (const fkey of this.fields.keys()) {
            let f = this.fields.get(fkey);
            f.reset();
        }
    }
    render(passedContainer=null) {
        if(passedContainer!=null)this.container=passedContainer;
        if(!this.shouldRender())return null;
        if(this.container==null){
            throw Exception("MISSING CONTAINER")
        }
        this.layout.forEach(c => {
            let cx = c.render(this.container);

            if(cx && cx!==this.container)this.container.appendChild(cx);
        });
        this.postRendered =true;
        const me = this;
        const observer = new MutationObserver(() => {
            if (me.container.isConnected) {
                if (document.body.contains(me.container)) {
                    observer.disconnect(); // stop observing if you only care about first attach
                    this.layout.forEach(c => c.postRender());
                }
            }
        });
        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
        return this.container;
    }
}



class RolesSelect extends SelectField{

    adaptResult(newOptions) {
        let result = [];
        if(newOptions){
            for (const item of newOptions) {
                result.push({
                    'label':item.name,
                    'value':item.id,
                })
            }
        }
        return result;
    }
    async loadRoles( exact) {
        var fetcher = new Fetcher({
            url: translate('API_URL')+'/roles.php',
            withWaitingWheel:false
        })
            .withMethod("GET")
            .onError((data, headers, status, statusText) => {
                showError(data.message||translate("ERROR"));
            });
        if(exact){
            fetcher.withQuery("exact","true")
        }
        const result = await fetcher.fetch();
        if(result){
            return result.items;
        }
        return [];
    }
    constructor({...rest}={}) {
        rest.api = async ({query,exact}) => {
            return await this.loadRoles(exact);
        };
        super(rest);


    }
}


// Same structure as WorkersAutoComplete in the PHP app's sap.js, minus the
// worker-role mechanics: debounced query against users?action=workers, the
// server resolves ids exactly (exact=true) when binding a stored value back.
class UsersAutoComplete extends AutocompleteField {
    adaptResult(newOptions) {
        let result = [];
        if (newOptions) {
            for (const item of newOptions) {
                result.push({
                    'label': trimIfLong(item.ragioneSociale || item.username, 50),
                    'value': item.id
                });
            }
        }
        return result;
    }

    constructor({...rest}) {
        rest.api = async ({query, exact}) => {
            return await this.loadUsers(query, exact);
        };
        rest.allowNewItems = false;
        if (rest.label === null || typeof rest.label === 'undefined') rest.label = translate('username');
        super(rest);
        this.search = debounceAsync(async (query) => {
            await this.loadOptions(query);
            this.refreshSuggestions();
            return this.options;
        }, rest.debounce);
        this.inputEl = null;
        this.suggestionsEl = null;
    }

    async loadUsers(query, exact) {
        if (query == null) query = '';
        var fetcher = new Fetcher({
            url: translate('API_URL') + '/users.php',
            withWaitingWheel: false
        }).withQuery('action', 'combo')
            .withMethod('GET')
            .withQuery('query', query)
            .onError((data) => {
                showError((data && data.message) || translate('ERROR'));
            });
        if (exact) {
            fetcher.withQuery('exact', 'true');
        }
        const result = await fetcher.fetch();
        if (result) return result.items;
        return [];
    }

    postRender() {
        super.postRender();
        if (this.defaultValue) {
            this.setValueFromJSON(this.defaultValue);
        }
    }
}