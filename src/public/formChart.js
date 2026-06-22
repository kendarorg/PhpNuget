

class FormChart extends Column {

    load(data,searchQuery) {
        if(searchQuery!=null && searchQuery.format && searchQuery.format!=="json"){
            return;
        }
        const me = this;
        let prevChar = Singleton.getValue(this.name);
        if(prevChar!==null){
            prevChar.destroy();
            Singleton.setValue(this.name)
            prevChar = null;
        }

        prevChar=new Chart(
            document.getElementById(this.name), {
                type: "line",
                data: data,
                options: {
                    responsive: true,
                    interaction: {
                        mode: "index",
                        intersect: false
                    },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: ctx => `${ctx.dataset.label}: ${ctx.raw.toFixed(2)}`
                            }
                        }
                    },
                    scales: {
                        y: {
                            stacked: true,
                            beginAtZero: true,
                            ticks: {
                                callback: value => `${value.toFixed(2)}`
                            }
                        },
                        x:{
                            stacked: true,
                        }
                    }
                }
            });

        Singleton.setValue(this.name,prevChar);

        if (this.eventHandlers.has("loaded")) {
            const eh = this.eventHandlers.get("loaded");
            eh.forEach(function (element, index, array) {
                element(me, data);
            });
        }

        this.setupButtons(data);
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
        searchContainer.appendChild(navigationContainer);
        container.appendChild(searchContainer);

        const canvas = document.createElement("canvas");
        canvas.id=this.name;
        canvas.classList.add("canvas-chart");


        //this.renderHeader(grid, thead, table);
        container.appendChild(document.createElement("br"));
        const subDiv = document.createElement("div");


        subDiv.appendChild(canvas);
        container.appendChild(subDiv);
        container.appendChild(document.createElement("br"));
        this.canvas = canvas;
        if (this.fixed && this.data != null && this.data.length > 0) {
            this.load(this.data);
        }
        this.reload();
        return container;
    }

    constructor({
                    span = 12,
                    name = null,
                    data = [],
                    fixed = false,
                    xls = true,
                    withBorder = 0,
                    searchable = true,
                    onFormat = [],
                    storeSearch = true,
                    extraTopButtons = [],// Array of {enabled,apply} functions
                    adaptContent = null,
                    onSearch = () => {
                        console.log("Implement onSearch")
                    },
                    eventHandlers = new Map(),
                    ...rest
                } = {}) {
        if(rest.name===null){
            rest.name="chart_id"+Singleton.setValue("counter",Singleton.getValue(counter)+1);
        }
        super({
            ...rest // pass anything extra
        }, []);
        this.onFormat  =onFormat;
        this.storeSearch = storeSearch;
        this.adaptContent = adaptContent;
        this.name = name;
        this.fixed = fixed;
        this.xls = xls;
        this.extraTopButtons = extraTopButtons;
        this.thead = null;
        this.searchable = searchable;
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

    withOnSearch(cb) {
        this.onSearch = cb;
        return this;
    }
    isSendable() {
        return false;
    }

    withEventHandler(event, cb) {
        event = event.toLowerCase();
        if (!this.eventHandlers.has(event)) {
            this.eventHandlers.set(event, []);
        }
        this.eventHandlers.get(event).push(cb);
        return this;
    }

    withLoaded(callback) {
        return this.withEventHandler("loaded", callback);
    }
    withReloaded(callback) {
        return this.withEventHandler("reloaded", callback);
    }

    setupButtons(data) {
        if (this.fixed) return;
        if (this.xls && this.topButtons.has("xls")) {
            const hasData = data && data.datasets && data.datasets.length > 0;
            this.topButtons.get("xls").style.display = hasData ? "inline-block" : "none";
        }
    }

    validate() {
        return true;
    }

    getErrors() {
        return null;
    }

    withEventHandler(name, callback) {
        if (!this.eventHandlers.has(name)) {
            this.eventHandlers.set(name, [])
        }
        this.eventHandlers.get(name).push(callback);
        return this;
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
        if (result ) grid.load(result.chart, searchQuery);
    }

    withStandardSearchApi(targetUrl){
        this.targetUrl = targetUrl;
        this.onSearch = this.standardSearchApi
        return this;
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
            let max = new SearchQuery().count;
            searchQuery = new SearchQuery(this.form.toObject());
            if(this.navigable) {
                searchQuery.count = max;
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
            searchQuery.count = count;
            searchQuery.from = this.from;
            if (searchQuery.from < 0) searchQuery.from = 0;
            if (searchQuery.count <= 0) searchQuery.count = 20;
            searchQuery.orderBy = this.order.buildSearchQueryOrderBy();
            localStorage.setItem(this.id + ".do_search", JSON.stringify(searchQuery));
            searchQuery.count += 1;
        }
        if(searchQuery==null){
            let max = new SearchQuery().count;
            searchQuery = new SearchQuery(this.form.toObject());
            if(this.navigable) {
                searchQuery.count = max;
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
}