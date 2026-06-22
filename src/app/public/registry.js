class GlobalRegistry {
    static data = {};

    static normalizeKey(key) {
        return String(key).toLowerCase();
    }

    static normalizeObject(obj) {
        const normalized = {};
        for (const [key, value] of Object.entries(obj || {})) {
            normalized[this.normalizeKey(key)] = value;
        }
        return normalized;
    }

    static async load(pageData = {}, permissionsModule = null, permissions2Module = null) {
        const params = new URLSearchParams();
        if (permissionsModule)  params.set('permissions',  permissionsModule);
        if (permissions2Module) params.set('permissions2', permissions2Module);

        const url = translate('API_URL') + '/globalRegistry.php' + (params.size > 0 ? '?' + params.toString() : '');
        const result = await fetch(url).then(r => r.json());

        this.data = {
            ...this.normalizeObject(result),
            ...this.normalizeObject(pageData),
        };
    }

    static get(key) {
        return this.data[this.normalizeKey(key)];
    }

    static set(key, value) {
        this.data[this.normalizeKey(key)] = value;
    }
}

export default GlobalRegistry;
