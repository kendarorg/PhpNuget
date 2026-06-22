
class CompanyRelation extends SelectField{
    constructor({...rest}) {
        rest.options =[
            {value: 'Cliente', label: translate("CUSTOMER")},
            {value: 'Fornitore', label: translate("SUPPLIER")},
            {value: 'Altro', label: translate("OTHER")},
        ];
        if(rest.label===null || typeof rest.label === "undefined")rest.label = translate('relationship');else rest.label=null;
        super(rest);
    }
}

class CompanyType extends SelectField{
    constructor({...rest}) {
        rest.options =[
            {value: 'Societa', label: translate("COMPANY")},
            {value: 'Persona', label: translate("PERSON")},
        ];
        if(rest.label===null || typeof rest.label === "undefined")rest.label = translate('kind');else rest.label=null;
        super(rest);
    }
}



class FunctionalitiesSelect extends SelectField{
    constructor({functionality="",...rest}={}) {
        rest.api = async ({query,exact,dependsOn}) => {
            return await this.loadFunctionalities(functionality,exact,dependsOn);
        };
        if(rest.dependsOn) {
            rest.alternateSelect = (value, dep) => {
                if(this.equalsIgnoreCase(dep,"Assenza")){
                    return "Permesso";
                }else if(this.equalsIgnoreCase(dep,"Gestione")){
                    return "Amministrazione";
                }else if(this.equalsIgnoreCase(dep,"Produzione")){
                    return "Scavo";
                }else if(this.equalsIgnoreCase(dep,"Nessuna")){
                    return "Nessuna";
                }
                return value;
            }
        }
        super(rest);
        this.maxItems=1000;
        this.functionality = functionality;

    }

    compareOptionWithValue(o,v){
        let spl = o.value.split("/");
        if(spl.length===2){
            if(this.equalsIgnoreCase(spl[1],v)){
                return true;
            }
        }
        return this.equalsIgnoreCase(o.value,v)||this.equalsIgnoreCase(o.label,v);
    }


    async loadFunctionalities(query, exact,dependsOn) {
        var fetcher = new Fetcher({
            url: translate('API_URL')+'/functionalities.php',
            withWaitingWheel:false
        }).withQuery("action", "functionalityItems")
            .withMethod("GET")
            .withQuery("functionality",query)
            .onError((data, headers, status, statusText) => {
                showError(data.message||translate("ERROR"));
            });
        if(this.dependsOn && (!dependsOn||dependsOn==="")){
            dependsOn="UNKNOWN";
        }
        if(dependsOn){
            fetcher.withQuery("prefix",dependsOn)
        }
        if(exact){
            fetcher.withQuery("exact","true")
        }
        const result = await fetcher.fetch();
        if(result){
            if(this.dependsOn) {
                result.items.forEach((item, index) => {
                    item.value = item.label;
                })
            }
            return result.items;
        }
        return [];
    }
}

class ContactsAutoComplete extends AutocompleteField{
    adaptResult(newOptions) {
        let result = [];
        if(newOptions){
            for (const item of newOptions) {
                result.push({
                    'label':item.label,
                    'value':item.value
                })
            }
        }
        return result;
    }

    constructor({...rest}) {
        rest.api = async ({query,exact}) => {
            return await this.loadContacts(query,exact);
        };
        super(rest);
        this.search = debounceAsync(async (query) => {
            await this.loadOptions(query);
            this.refreshSuggestions();
            return this.options;
        }, rest.debounce);
        this.allowNewItems = false;
        this.inputEl = null;
        this.suggestionsEl = null;
    }


    async loadContacts(query,exact,role) {
        if(query==null) query='';
        var fetcher = new Fetcher({
            url: translate('API_URL')+'/contacts.php',
            withWaitingWheel:false
        }).withQuery("action", "contacts")
            .withMethod("GET")
            .withQuery("query",query)
            .onError((data, headers, status, statusText) => {
                showError(data.message||translate("ERROR"));
            });
        if(exact){
            fetcher.withQuery("exact","true")
        }
        const result = await fetcher.fetch();
        if(result)return result.items;
        return [];
    }
}

class JobsAutoComplete extends  AutocompleteField{
    adaptResult(newOptions) {
        let result = [];
        if(newOptions){
            for (const item of newOptions) {
                result.push({
                    'label':"("+item.id+") "+trimIfLong(joinNonEmpty(",",
                        item.toponimo,item.location,item.description),50),
                    'value':item.value,
                    'id':item.id
                })
            }
        }
        return result;
    }

    constructor(rest) {
        rest.api = async ({query,exact}) => {
            
            return await this.loadJobs(query,exact);
        };
        super(rest);
        this.search = debounceAsync(async (query) => {
            await this.loadOptions(query);
            this.refreshSuggestions();
            return this.options;
        }, rest.debounce);
        this.allowNewItems = false;
        this.inputEl = null;
        this.suggestionsEl = null;
    }

    async loadJobs(query,exact) {
        if(query==null) query='';
        var fetcher = new Fetcher({
            url: translate('API_URL')+'/jobs.php',
            withWaitingWheel:false
        }).withQuery("action", "jobs")
            .withMethod("GET")
            .withQuery("query",query)
            .onError((data, headers, status, statusText) => {
                showError(data.message||translate("ERROR"));
            });
        if(exact){
            fetcher.withQuery("exact","true")
        }
        const result = await fetcher.fetch();
        if(result)return result.items;
        return [];
    }
}



class WorkersAutoComplete extends  AutocompleteField{
    adaptResult(newOptions) {
        let result = [];
        if(newOptions){
            for (const item of newOptions) {
                result.push({
                    'label':trimIfLong(item.ragioneSociale,50),
                    'value':item.id,
                    'tariffaOraria':item.tariffaOraria
                })
            }
        }
        return result;
    }

    constructor({role=null,...rest}) {
        rest.api = async ({query,exact}) => {
            return await this.loadWorkers(query,exact);
        };
        rest.allowNewItems = false;
        super(rest);
        this.role = role;
        this.search = debounceAsync(async (query) => {
            await this.loadOptions(query);
            this.refreshSuggestions();
            return this.options;
        }, rest.debounce);
        this.inputEl = null;
        this.suggestionsEl = null;
    }

    async loadWorkers(query,exact,role) {
        if(query==null) query='';
        var fetcher = new Fetcher({
            url: translate('API_URL')+'/users.php',
            withWaitingWheel:false
        }).withQuery("action", "workers")
            .withMethod("GET")
            .withQuery("query",query)
            .onError((data, headers, status, statusText) => {
                showError(data.message||translate("ERROR"));
            });
        if(exact){
            fetcher.withQuery("exact","true")
        }
        if(this.role==="TD"){
            fetcher.withQuery("permission","technical_director")
        }
        const result = await fetcher.fetch();
        if(result)return result.items;
        return [];
    }

    postRender() {
        super.postRender();
        if(this.defaultValue){
            this.setValueFromJSON(this.defaultValue);
        }
    }
}




