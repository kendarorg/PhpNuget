class SearchQuery {
    constructor(src) {
        this.from = 0;
        this.count = -1;
        this.format = "json";
        this.orderBy = [];
        this.extraConditions =[];
        this.fields = [];
        this.searchTerms = {};
        if (src != null && src !== 'undefined') {
            for (let key in src) {
                if (src.hasOwnProperty(key)) { // filter only own properties
                    this.searchTerms[key] = src[key];
                }
            }
        }
    }
}

class OrderBy {
    constructor() {
        this.field = null;
        this.direction = 0;
    }

    toggle(field) {
        if (this.field === null || field !== this.field) {
            this.field = field;
            this.direction = 1;
        } else {
            if (this.direction === 1) {
                this.direction = -1;
            } else {
                this.field = null;
                this.direction = 0;
            }
        }
    }

    setFromOrderBy(queryArray) {
        if(!queryArray)return;
        if (queryArray.length > 0) {
            const query = queryArray[0];
            const splitted = query.trim().split(/\s+/);
            if (splitted.length == 2) {
                this.field = splitted[0];
                if (splitted[1] === "ASC") {
                    this.direction = 1;
                } else {
                    this.direction = -1;
                }
            } else {
                this.field = null;
                this.direction = 0;
            }
        }
    }

    buildSearchQueryOrderBy() {
        if (this.field === null) return [];
        if (this.direction === 1) return [this.field + " ASC"];
        if (this.direction === -1) return [this.field + " DESC"];
        return [];
    }

    applyLabels(rows) {
        for (let i = 0; i < rows.length; i++) {
            const row = rows[i];
            if (row.id !== null && row.id === this.field) {
                row.content.innerHTML = this.buildLabel(row.label)
            } else {
                row.content.innerHTML = row.label;
            }
        }
    }

    buildLabel(label) {
        if (this.direction === 1) {
            return label + " &#9652;";
        } else if (this.direction === -1) {
            return label + " &#9662;";
        }
        return label;
    }
}

class GridItemNavigation {

    parseUrl(urlString) {
        const url = new URL(urlString);

        // Collect query parameters into an object, handling multiple values
        const params = {};
        url.searchParams.forEach((value, key) => {
            if (params[key]) {
                // Already exists, push to array
                params[key] = Array.isArray(params[key]) ? [...params[key], value] : [params[key], value];
            } else {
                params[key] = value;
            }
        });

        // Build object
        const result = {
            host: url.hostname,
            params,
            path: url.pathname,
            port: url.port || null,
            protocol: url.protocol.replace(":", "")
        };

        return result;
    }

    constructor({gridName = null, grid = null, buildLinkCallback = null, containerElement = null}) {
        if (typeof containerElement === "string") {
            containerElement = document.getElementById(containerElement);
        }
        this.containerElement = containerElement;
        if (gridName == null) {
            this.items = [];
            this.buildLinkCallback = buildLinkCallback
            if (grid) {
                this.grid.withEventHandler('loaded', (grid, data) => {
                    this.loadData(grid, data);
                });
            }
        } else {
            this.loadNavigation(gridName);
        }
    }

    postRender() {

    }

    loadData(grid, data) {
        let gridData = {
            position: 0,
            data: []
        }
        if (Array.isArray(data)) {
            let links = [];
            for (let i = 0; i < data.length; i++) {
                links.push(this.parseUrl(this.buildLinkCallback(data[i])));
            }
            gridData = {
                position: 0,
                data: links
            }
        }
        localStorage.setItem((grid.name || grid.id) + ".get_search_results", JSON.stringify(gridData));
    }

    loadNavigation(gridName) {
        let gridData = localStorage.getItem(gridName + ".get_search_results");
        if (!gridData) return;
        let currentUrl = this.parseUrl(window.location.href);
        gridData = JSON.parse(gridData);
        if (!gridData || !gridData.data) return;
        let foundedIndex = this.loadFoundedIndex(currentUrl, gridData, gridName);
        let hasPrev = foundedIndex > 0;
        let hasNext = foundedIndex < (gridData.data.length - 1) && foundedIndex >= 0;
        let nav = this.containerElement;
        if (!nav) return;
        if (hasPrev) {
            const btn = document.createElement("button");
            btn.textContent = translate('PREVIOUS');
            btn.className = "action-btn";
            const target = this.mergeUrlAndBuild(gridData.data[foundedIndex - 1], currentUrl);
            btn.addEventListener("click", () => {
                window.location.href = target;
            });
            this.containerElement.appendChild(btn);
        }
        if (hasNext) {
            const btn = document.createElement("button");
            btn.textContent = translate('NEXT');
            btn.className = "action-btn";
            const target = this.mergeUrlAndBuild(gridData.data[foundedIndex + 1], currentUrl);
            btn.addEventListener("click", () => {
                window.location.href = target;
            });
            this.containerElement.appendChild(btn);
        }
    }


    loadFoundedIndex(sample, gridData, gridName) {
        try {
            let foundedIndex = gridData.data.findIndex(objUrl => {
                // Check if all params in the sample exist and match in the object
                for (const key in objUrl.params) {
                    if (!(key in sample.params)) return false;

                    // Convert both to arrays for comparison
                    const sampleVals = Array.isArray(sample.params[key]) ? sample.params[key] : [sample.params[key]];
                    const objVals = Array.isArray(objUrl.params[key]) ? objUrl.params[key] : [objUrl.params[key]];

                    // Check if all sample values are included in the object url values
                    if (!objVals.every(v => sampleVals.includes(v))) return false;
                }
                return true;
            });
            if (foundedIndex < 0) foundedIndex = 0;
            return foundedIndex;
        } catch (e) {
            localStorage.removeItem(gridName + ".get_search_results");
            return -1;
        }
    }

    mergeUrlAndBuild(datum, currentUrl) {
        let newUrl = {}
        for (const key in currentUrl.params) {
            if (key in datum.params) {
                currentUrl.params[key] = datum.params[key];
            }
        }
        let url = currentUrl.protocol + "://" + currentUrl.host + (currentUrl.port ? (":" + currentUrl.port) : "");
        // Add path
        url += currentUrl.path || "/";

        // Add query parameters
        const params = currentUrl.params || {};
        const query = Object.entries(params)
            .flatMap(([key, value]) => {
                const values = Array.isArray(value) ? value : [value];
                return values.map(v => `${encodeURIComponent(key)}=${encodeURIComponent(v)}`);
            })
            .join("&");

        if (query) {
            url += `?${query}`;
        }

        return url;
    }
}

class Grid extends Column {
    constructor({
                    shownRows = ["10", "20", "50", "100"],
                    span = 12,
                    name = null,
                    rows = [],
                    data = [],
                    fixed = false,
                    xls= true,
                    navigable = true,
                    sendable = false,
                    canAdd = true,
                    canRemove = true,
                    canEditInline = false,
                    canBrowse = true,
                    canEdit = true,
                    showHeader = true,
                    withBorder = 0,
                    onFormat = [],
                    storeSearch = true,
                    extraTopButtons = [],// Array of {enabled,apply} functions
                    extraButtons = [],// Array of {enabled,apply} functions
                    adaptContent = null,
                    searchable = true,
                    onSaveRow = null,
                    onSearch = () => {
                        console.log("Implement onSearch")
                    },
                    eventHandlers = new Map(),
                    ...rest
                } = {}) {
        super({
            span,
            rows,
            fixed,
            navigable,
            sendable,
            canAdd,
            canRemove,
            canBrowse,
            canEdit,
            adaptContent,
            onSearch,
            eventHandlers,
            ...rest // pass anything extra
        }, []);
        if(!showHeader){
            navigable = false;
        }
        this.shownRows = shownRows;
        this.withBorder =withBorder;
        this.xls = xls;
        this.onSaveRow = onSaveRow;
        this.canEditInline = canEditInline;
        this.onFormat  =onFormat;
        this.storeSearch = storeSearch;
        this.showHeader = showHeader;
        this.order = new OrderBy();
        this.adaptContent = adaptContent;
        this.fixed = fixed;
        if (fixed) navigable = false;
        this.navigable = navigable;
        this.searchable = searchable;
        this.sendable = sendable;
        this.rows = rows;
        this.name = name;
        this.canAdd = canAdd;
        this.canRemove = canRemove;
        this.canBrowse = canBrowse;
        this.canEdit = canEdit;
        this.extraButtons = extraButtons;
        this.extraTopButtons = extraTopButtons;
        this.thead = null;
        this.xls = xls;
        this.topButtons = new Map();
        this.from = 0;
        this.onSearch = onSearch;
        this.eventHandlers = eventHandlers;
        this.data = data;
        if (this.fixed) {
            this.onSearch = () => {
                this.load(this.data);
            }
        }
    }

    withFormatHandler(format,callback){
        this.onFormat[format]=callback;
        return this;
    }

    setOrderBy(field) {
    }

    toggleOrderBy(field) {
        this.order.toggle(field);
        this.order.applyLabels(this.rows);
        this.reload(true);
    }


    removeRow(index) {
        this.data.splice(index, 1);
        this.load(this.data);
    }

    withOnSearch(cb) {
        this.onSearch = cb;
        return this;
    }

    isSendable() {
        return this.sendable;
    }

    withEventHandler(event, cb) {
        event = event.toLowerCase();
        if (!this.eventHandlers.has(event)) {
            this.eventHandlers.set(event, []);
        }
        this.eventHandlers.get(event).push(cb);
        return this;
    }

    load(data,searchQuery) {
        if(searchQuery!=null && searchQuery.format && searchQuery.format!=="json"){
            return;
        }
        if (this.thead) {
            while (this.thead.nextElementSibling) {
                this.thead.nextElementSibling.remove();
            }
        }else{
            this.container.getElementsByTagName("table")[0].innerHTML='';
        }

        const grid = this;
        let maxCount = Number.MAX_VALUE;
        if (this.topButtons.has("count")) {
            maxCount = parseInt(this.topButtons.get("count").value);
            this.topButtons.get("current").textContent = this.from;
        }
        this.data = data;
        for (var i = 0; i < data.length && i < maxCount; i++) {
            this.addRow(data, i, grid);
        }
        if (this.eventHandlers.has("loaded")) {
            const eh = this.eventHandlers.get("loaded");
            eh.forEach(function (element, index, array) {
                element(grid, data);
            });
        }

        this.setupButtons(data, maxCount);
    }

    withLoaded(callback) {
        return this.withEventHandler("loaded", callback);
    }

    setupButtons(data, maxCount) {
        if (this.fixed) return;
        if (data.length > 0) {
            if(this.xls)this.topButtons.get("xls").style.display = "inline-block";

            if (this.navigable) {
                this.topButtons.get("first").style.display = "inline-block";
                this.topButtons.get("current").style.display = "inline-block";
            }
        } else {
            if(this.xls)this.topButtons.get("xls").style.display = "none";
            if (this.navigable) {
                if (this.from > 0) {
                    this.topButtons.get("first").style.display = "inline-block";
                    this.topButtons.get("current").style.display = "inline-block";
                } else {
                    this.topButtons.get("first").style.display = "none";
                    this.topButtons.get("current").style.display = "none";
                }
            }
        }
        if (this.navigable) {
            if (data.length > maxCount) {
                this.topButtons.get("next").style.display = "inline-block";
            }
            if (data.length < maxCount) {
                this.topButtons.get("next").style.display = "none";
            }
        }
        if (this.from > 0) {
            if(this.xls)this.topButtons.get("xls").style.display = "inline-block";

            if (this.navigable) {
                this.topButtons.get("first").style.display = "inline-block";
                if (this.from > maxCount) {
                    this.topButtons.get("back").style.display = "inline-block";
                } else {
                    this.topButtons.get("back").style.display = "none";
                }
            }

        } else {

            if (this.navigable) {
                this.topButtons.get("back").style.display = "none";
                this.topButtons.get("first").style.display = "none";
                this.topButtons.get("current").style.display = "none";
            }
        }
    }

    validate() {
        return true;
    }

    getErrors() {
        return null;
    }

    withShow(callback) {
        return this.withEventHandler("detail-show", callback);
    }

    withEdit(callback) {
        return this.withEventHandler("detail-edit", callback);
    }


    withDelete(callback) {
        return this.withEventHandler("detail-delete", callback);
    }

    withAdd(callback) {
        return this.withEventHandler("add-new", callback);
    }

    withEventHandler(name, callback) {
        if (!this.eventHandlers.has(name)) {
            this.eventHandlers.set(name, [])
        }
        this.eventHandlers.get(name).push(callback);
        return this;
    }

    addRow(data, i, grid) {
        const tr = document.createElement("tr");
        let colContent = data[i];
        if (isString(colContent)) {
            if(colContent === "===") {
                this.buildTableSeparator(tr);
                return;
            }else if(colContent.startsWith("th=")){
                this.buildSubHeader(colContent,tr);
                return;
            }
        }
        if (this.adaptContent) {
            colContent = this.adaptContent(colContent);
        }
        for (let j = 0; j < this.rows.length; j++) {
            const row = this.rows[j];
            const td = document.createElement("td");
            this.addColum(row, td, colContent,i);
            tr.appendChild(td);
        }
        if (this.canBrowse) {
            //<button data-href="job_edit.html?id=${job.id}&view=true" title="Visualizza" class="btn show-btn"></button>
            const btn = document.createElement("button");
            btn.title = translate("SHOW");

            btn.classList.add("icon-btn");
            btn.setAttribute('data-tooltip', btn.title);
            btn.classList.add("btn", "show-btn");
            btn.addEventListener("click", (e) => {
                e.preventDefault();
                if (this.eventHandlers.has("detail-show")) {
                    const eh = this.eventHandlers.get("detail-show");
                    eh.forEach(function (element, index, array) {
                        element(grid, colContent, i);
                    });
                }
            });
            const td = document.createElement("td");
            td.appendChild(btn);
            tr.appendChild(td);

        }
        if (this.canEdit) {
            //<button data-href="job_edit.html?id=${job.id}&view=true" title="Visualizza" class="btn show-btn"></button>
            const btn = document.createElement("button");
            btn.title = translate("EDIT");

            btn.classList.add("icon-btn");
            btn.setAttribute('data-tooltip', btn.title);
            btn.classList.add("btn", "edit-btn");
            btn.addEventListener("click", (e) => {
                e.preventDefault();
                if (this.eventHandlers.has("detail-edit")) {
                    const eh = this.eventHandlers.get("detail-edit");
                    eh.forEach(function (element, index, array) {
                        element(grid, colContent, i);
                    });
                }
            });
            const td = document.createElement("td");
            td.appendChild(btn);
            tr.appendChild(td);
        }

        if (this.canRemove) {
            const btn = document.createElement("button");
            btn.title = translate("DELETE");
            btn.classList.add("btn", "delete-btn");

            btn.classList.add("icon-btn");
            btn.setAttribute('data-tooltip', btn.title);
            btn.addEventListener("click", (e) => {
                e.preventDefault();
                if (this.eventHandlers.has("detail-delete")) {
                    const eh = this.eventHandlers.get("detail-delete");
                    eh.forEach(function (element, index, array) {
                        element(grid, colContent, i);
                    });
                }
            });
            const td = document.createElement("td");
            td.appendChild(btn);
            tr.appendChild(td);
        }
        for (let k = 0; k < this.extraButtons.length; k++) {
            const td = document.createElement("td");
            const kExtraButton = this.extraButtons[k];
            if (kExtraButton.enabled(grid, i, colContent, td)) {
                kExtraButton.apply(grid, i, colContent, td);
            }
            tr.appendChild(td);

        }
        this.canvas.appendChild(tr);
    }

    buildTableSeparator(tr) {
        let colspan = this.rows.length;
        if (this.canBrowse) colspan++;
        if (this.canEdit) colspan++;
        if (this.canRemove) colspan++;
        colspan += this.extraButtons.length;

        const td = document.createElement("td");
        td.classList.add("table-separator")
        td.colSpan = colspan;
        tr.appendChild(td);
        this.canvas.appendChild(tr);
    }

    parseWithStyle(element,input,maxLength=null) {
        if(input) {
            const regex = /<style\s*'([^']*)'>([\s\S]*?)<\/style>/i;
            let match = null;
            try {
                match = input.match(regex);
            }catch (e){
                element.textContent = input;
                return;
            }

            if (match) {
                element.style = match[1].trim();
                input = match[2];

            }
            if (maxLength && maxLength > 0) {
                input = trimIfLong(input, maxLength)
            }
        }
        element.textContent = input;
    }

    addColum(row, td, colContent,rowNumberParam) {
        const rowNumber = rowNumberParam;
        var rowData = colContent[row.id];
        const me = this;
        if (row.customRender) {
            row.customRender(td, rowData, colContent);
        } else if (row.maxLength && row.maxLength > 0) {
            this.parseWithStyle(td,rowData,row.maxLength)
        } else {
            this.parseWithStyle(td,rowData)
        }
        if (row.onEdit && this.canEditInline) {
            td.addEventListener("dblclick", function (e) {
                e.preventDefault();
                let originalInnerHtml = td.innerHTML;
                let originalStyle = td.style;
                td.innerHTML = '';
                td.style = "white-space: nowrap;";
                let toPass = row.onEdit(me,rowNumber,row,colContent);
                if (!Array.isArray(toPass)) {
                    let tp = [];
                    tp.push(toPass);
                    toPass = tp;
                }
                toPass.push(new IconButton({
                    buttonClasses: ['btn', 'cancel-btn'],
                    title: translate("CANCEL"),
                    onClick: (item) => {
                        td.innerHTML = originalInnerHtml;
                        td.style = originalStyle;
                    }
                }));
                toPass.push(
                    new IconButton({
                        buttonClasses: ['btn', 'save-btn'],
                        title: translate("SAVE"),
                        onClick: (item) => {

                            colContent = item.form.toObject();
                            if(me.onSaveRow){
                                me.onSaveRow(me,rowNumber,row,colContent);
                            }
                            console.log("changing row");
                            td.innerHTML='';
                            me.data[rowNumber]=colContent;
                            me.addColum(row,td,colContent);
                        }
                    })
                );
                let forms = new StructurlessForm(
                    td,
                    ...toPass
                )
                forms.render(td);
                forms.load(colContent);
            });
        }
    }

    render(passedContainer=null) {
        if(passedContainer!=null)this.container=passedContainer;
        if(!this.shouldRender())return null;
        const grid = this;
        let container = this.container;

        if(container==null){
            container = document.createElement("div");
            container.className = `f-col f-col-${this.span}`;
        }
        //container.style = "    background-color: #f4f4f4;";
        this._setContainer(container);
        const navigationContainer = document.createElement("div");
        const searchContainer = document.createElement("div");




        for (let k = 0; k < this.extraTopButtons.length; k++) {
            const kExtraButton = this.extraTopButtons[k];
            if (kExtraButton.enabled(grid)) {
                kExtraButton.apply(grid, navigationContainer);
            }
        }

        if(!this.fixed && this.searchable){
            this.topButtons.set("search", document.createElement("button"));
            this.topButtons.get("search").classList.add("btn", "search-btn");
            this.topButtons.get("search").title = translate("SEARCH");
            this.topButtons.get("search").classList.add("icon-btn");
            this.topButtons.get("search").setAttribute('data-tooltip', this.topButtons.get("search").title);

            this.topButtons.get("search").addEventListener("click", (e) => {
                e.preventDefault();
                this.refreshSearch();
                if (this.eventHandlers.has("search")) {
                    const eh = this.eventHandlers.get("search");
                    eh.forEach(function (element) {
                        element(grid);
                    });
                }
            });
            navigationContainer.appendChild(this.topButtons.get("search"));

            if(this.storeSearch || this.searchable) {

                this.topButtons.set("clean", document.createElement("button"));
                this.topButtons.get("clean").classList.add("btn", "eraser-btn");
                this.topButtons.get("clean").title = translate("clean_search");
                this.topButtons.get("clean").classList.add("icon-btn");
                this.topButtons.get("clean").setAttribute('data-tooltip', this.topButtons.get("clean").title);

                this.topButtons.get("clean").addEventListener("click", (e) => {
                    e.preventDefault();
                    this.cleanSearch();
                    if (this.eventHandlers.has("clean")) {
                        const eh = this.eventHandlers.get("clean");
                        eh.forEach(function (element) {
                            element(grid);
                        });
                    }
                });
                navigationContainer.appendChild(this.topButtons.get("clean"));
            }
        }

        if (this.xls && !this.fixed) {
            this.topButtons.set("xls", document.createElement("button"));
            this.topButtons.get("xls").classList.add("btn", "xls-btn");
            this.topButtons.get("xls").title = translate("EXPORT_XLS");
            this.topButtons.get("xls").classList.add("icon-btn");
            this.topButtons.get("xls").setAttribute('data-tooltip', this.topButtons.get("xls").title);
            this.topButtons.get("xls").style.display = "none";
            this.topButtons.get("xls").addEventListener("click", () => {
                this.reload(false, "xls");
            });
            navigationContainer.appendChild(this.topButtons.get("xls"));

        }
        if (!this.fixed && this.navigable) {




            this.topButtons.set("first", document.createElement("button"));
            this.topButtons.get("first").classList.add("action-btn");
            this.topButtons.get("first").textContent = translate("START");
            this.topButtons.get("first").title = translate("START");
            this.topButtons.get("first").classList.add("icon-btn");
            this.topButtons.get("first").setAttribute('data-tooltip', this.topButtons.get("first").title);
            this.topButtons.get("first").style.display = "none";

            this.topButtons.get("first").addEventListener("click", () => {
                this.from = 0;
                this.refreshSearch();
            });
            navigationContainer.appendChild(this.topButtons.get("first"));

            this.topButtons.set("back", document.createElement("button"));
            this.topButtons.get("back").classList.add("action-btn");
            this.topButtons.get("back").textContent = translate("BACK");
            this.topButtons.get("back").title = translate("BACK");
            this.topButtons.get("back").classList.add("icon-btn");
            this.topButtons.get("back").setAttribute('data-tooltip', this.topButtons.get("back").title);
            this.topButtons.get("back").style.display = "none";
            this.topButtons.get("back").addEventListener("click", () => {
                let count = 20;
                if(this.topButtons.get("count").selectedOptions.length>0) {
                    count = parseInt(this.topButtons.get("count").selectedOptions[0].value);
                }
                this.from = this.from - count;
                if (this.from < 0) this.from = 0;
                this.refreshSearch();
            });
            navigationContainer.appendChild(this.topButtons.get("back"));


            this.topButtons.set("current", document.createElement("button"));
            this.topButtons.get("current").classList.add("inaction-btn");
            this.topButtons.get("current").textContent = "0";
            this.topButtons.get("current").title = "0";
            this.topButtons.get("current").style.display = "none";
            navigationContainer.appendChild(this.topButtons.get("current"));

            this.topButtons.set("next", document.createElement("button"));
            this.topButtons.get("next").classList.add("action-btn");
            this.topButtons.get("next").textContent = translate("NEXT");
            this.topButtons.get("next").title = translate("NEXT");
            this.topButtons.get("next").classList.add("icon-btn");
            this.topButtons.get("next").setAttribute('data-tooltip', this.topButtons.get("next").title);
            this.topButtons.get("next").style.display = "none";
            this.topButtons.get("next").addEventListener("click", () => {
                let count = 20;
                if(this.topButtons.get("count").selectedOptions.length>0) {
                    count = parseInt(this.topButtons.get("count").selectedOptions[0].value);
                }
                this.from = this.from + count;
                this.refreshSearch();
            });
            navigationContainer.appendChild(this.topButtons.get("next"));

            this.topButtons.set("count", document.createElement("select"));
            this.topButtons.get("count").classList.add("navigation-select");
            this.topButtons.get("count").addEventListener("change", function () {
                grid.refreshSearch();
            });

            const values = this.shownRows;

            values.forEach(v => {
                const option = document.createElement("option");
                option.value = v;
                option.textContent = v;
                this.topButtons.get("count").appendChild(option);
            });
            navigationContainer.appendChild(this.topButtons.get("count"));
        }

        if (this.canAdd) {
            let extra = document.createElement("span");
            extra.innerHTML = "&nbsp;&nbsp;&nbsp;&nbsp;";
            navigationContainer.appendChild(extra);
            this.topButtons.set("add", document.createElement("button"));
            this.topButtons.get("add").classList.add("btn", "add-btn");
            this.topButtons.get("add").title = translate("ADD");
            this.topButtons.get("add").classList.add("icon-btn");
            this.topButtons.get("add").setAttribute('data-tooltip', this.topButtons.get("add").title);

            this.topButtons.get("add").addEventListener("click", (e) => {
                e.preventDefault();
                if (this.eventHandlers.has("add-new")) {
                    const eh = this.eventHandlers.get("add-new");
                    eh.forEach(function (element) {
                        element(grid);
                    });
                }
            });
            navigationContainer.appendChild(this.topButtons.get("add"));
        }
        searchContainer.appendChild(navigationContainer);
        container.appendChild(searchContainer);

        const table = document.createElement("table");
        table.style = " box-shadow: 0 1px 2px rgba(4, 4, 4, 4);";
        if(this.withBorder && this.withBorder>0){
            table.setAttribute("border",this.withBorder+"");
        }

        const thead = document.createElement("thead");
        this.renderHeader(grid, thead, table);
        container.appendChild(document.createElement("br"));
        const subDiv = document.createElement("div");
        subDiv.classList.add("table-viewport");
        subDiv.appendChild(table);
        container.appendChild(subDiv);
        container.appendChild(document.createElement("br"));
        this.canvas = table;
        if (this.showHeader) {
            this.thead = thead;
        }
        if (this.fixed && this.data != null && this.data.length > 0) {
            this.load(this.data);
        }
        this.reload();
        return container;
    }


    renderHeader(grid, thead, table) {
        if (this.showHeader) {
            const trh = document.createElement("tr");

            for (let i = 0; i < this.rows.length; i++) {
                const row = this.rows[i];
                const th = document.createElement("th");
                th.innerHTML = row.label;
                if (this.navigable) {
                    if (!row.hasOwnProperty('orderable') || row.orderable) {
                        th.addEventListener("click", () => grid.toggleOrderBy(row.id));
                    }
                }
                trh.appendChild(th);
                row.content = th;
            }
            if (this.canAdd || this.canBrowse || this.canRemove) {
                const th = document.createElement("th");
                th.innerHTML = "&nbsp";
                th.colSpan = 0;
                if (this.canBrowse) th.colSpan += 1;
                if (this.canRemove) th.colSpan += 1;
                if (this.canEdit) th.colSpan += 1;
                th.colSpan += this.extraButtons.length;
                trh.appendChild(th);
            }
            thead.appendChild(trh);
            table.appendChild(thead);
        }
    }

    setValueFromJSON(value,json) {
        this.load(value);
    }

    async standardSearchApi(grid,searchQuery){
        let fetcher = new Fetcher({
            url: this.targetUrl
        }).withQuery("action", "search")
            .withMethod("POST")
            .withBody(searchQuery)
            .onError((data, headers, status, statusText) => {
                showInfo(translate("ERROR"));
            });
        if(searchQuery.format==="xls" && this.xls){
            fetcher.withQuery("format", "xls")
            fetcher.withJsonResponse(false).withDownload().fetch();
            return;
        }
        var result = await fetcher.fetch();
        if (result ) grid.load(result.items, searchQuery);
    }

    withStandardSearchApi(targetUrl){
        this.targetUrl = targetUrl;
        this.onSearch = this.standardSearchApi
        return this;
    }

    applyPagination(searchQuery) {
        let count = 20;
        if (this.topButtons && this.topButtons.has("count") && this.topButtons.get("count").selectedOptions.length > 0) {
            count = parseInt(this.topButtons.get("count").selectedOptions[0].value);
        }
        searchQuery.from = this.from > 0 ? this.from : 0;
        searchQuery.orderBy = this.order.buildSearchQueryOrderBy();
        // ask one extra row so load() can detect whether a next page exists
        searchQuery.count = count + 1;
    }

    reload(applyOrderBy = false,format="json") {
        if (this.fixed) return;
        let searchQuery = null;
        if(this.storeSearch) {
            searchQuery = JSON.parse(localStorage.getItem(this.id + ".do_search"));


            try {
                if (!searchQuery) {
                    searchQuery = new SearchQuery(this.form.toObject());
                } else {
                    this.form.load(searchQuery.searchTerms);

                }
            } catch (e) {
                searchQuery = new SearchQuery(this.form.toObject());
                this.form.load(searchQuery.searchTerms);
            }
            if (this.navigable) {
                if (applyOrderBy) {
                    searchQuery.orderBy = this.order.buildSearchQueryOrderBy();
                } else {
                    this.order.setFromOrderBy(searchQuery.orderBy);
                    this.order.applyLabels(this.rows);
                }
            }
            if (!this.navigable) searchQuery.count = -1;
            if (searchQuery.count <= 0) searchQuery.count = 20;
            if (this.topButtons && this.topButtons.has("count")) {
                this.topButtons.get("count").value = searchQuery.count + "";
            }
            this.from = searchQuery.from;
            localStorage.setItem(this.id + ".do_search", JSON.stringify(searchQuery));
            searchQuery.count += 1;
        }
        if(format!=="json"){
            if(searchQuery==null){
                let max = new SearchQuery().count;
                searchQuery = new SearchQuery(this.form.toObject());
                if(this.navigable) {
                    searchQuery.count = max;
                }
            }
            searchQuery.format = format;
            if(this.onFormat && this.onFormat[format]){
                this.onFormat[format](this, searchQuery);
                return;
            }
        }
        if(searchQuery==null){
            searchQuery = new SearchQuery(this.form.toObject());
            if(this.navigable) {
                this.applyPagination(searchQuery);
            }
        }
        this.onSearch(this, searchQuery);
    }

    reset(){

    }

    cleanSearch(){
        let searchQuery = new SearchQuery();
        searchQuery.count = 20;
        searchQuery.from = 0;
        this.form.reset();
        if(this.storeSearch){
            localStorage.setItem(this.id + ".do_search", JSON.stringify(searchQuery));
            this.onSearch(this, searchQuery);
        }else{
            searchQuery = new SearchQuery(this.form.toObject());
        }
        this.onSearch(this, searchQuery);
    }
    refreshSearch() {
        let searchQuery = null;
        if(this.storeSearch) {
            searchQuery = new SearchQuery(this.form.toObject());

            if (searchQuery.count < 0) searchQuery.count = 20;
            let count = 20;
            if(this.topButtons.get("count").selectedOptions.length>0) {
                count = parseInt(this.topButtons.get("count").selectedOptions[0].value);
            }
            searchQuery.count = count;
            searchQuery.from = this.from;
            if (searchQuery.from < 0) searchQuery.from = 0;
            if (searchQuery.count <= 0) searchQuery.count = 20;
            searchQuery.orderBy = this.order.buildSearchQueryOrderBy();
            localStorage.setItem(this.id + ".do_search", JSON.stringify(searchQuery));
            searchQuery.count += 1;
        }
        if(searchQuery==null){
            searchQuery = new SearchQuery(this.form.toObject());
            if(this.navigable) {
                this.applyPagination(searchQuery);
            }
        }
        this.onSearch(this, searchQuery);
    }

    toObject() {
        return this.data;
    }


    attachForm(form) {
        this.form = form;
        if (this.id != null) {
            this.form.registerId(this);
        }
        if (this.name != null) {
            this.form.registerField(this);
        }
    }

    buildSubHeader(colContent,tr) {
        const [key, valuePart] = colContent.split(/=(.+)/);
        const valueArray = valuePart.split("|");

        let i=0;
        for(i=0;i<this.rows.length;i++) {
            console.log(valueArray)
            if(i<valueArray.length) {
                let label = valueArray[i];
                const th = document.createElement("th");
                th.textContent = translate(label);
                tr.appendChild(th);
            }else{
                const th = document.createElement("th");
                tr.appendChild(th);
            }
        }
        if(this.canEdit){
            tr.appendChild(document.createElement("th"));
        }

        if(this.canRemove){
            tr.appendChild(document.createElement("th"));
        }

        if(this.canBrowse){
            tr.appendChild(document.createElement("th"));
        }
        if(this.extraButtons) {
            for (let k = 0; k < this.extraButtons.length; k++) {
                tr.appendChild(document.createElement("th"));
            }
        }

        this.canvas.appendChild(tr);
    }
}