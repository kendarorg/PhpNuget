export function initPage(registry) {
    const id            = getQueryParam('id') || null;
    const functionality = getQueryParam('functionality') || null;
    const editing = hasQueryParam('edit') && getQueryParam('edit') === 'true';
    const viewing = !editing;
    const creating = !id;

    if (id) new GridItemNavigation({gridName: 'functionalities', containerElement: 'content'});

    const form = new Form(
        new Row(new IconButtonColumn({span: 1},
            new IconButton({buttonClasses: ['btn', 'back-btn'], title: translate('BACK'), onClick: () => { window.location.href = 'functionalities.html'; }}),
            new IconButton({buttonClasses: ['btn', 'save-btn'], title: translate('SAVE'), onClick: () => save(form), readonly: viewing}),
        )),
        new Row(
            new Column({span: 6}, new AutocompleteField({
                name: 'functionality', label: translate('FUNCTIONALITY'), required: true, readonly: viewing, allowNewItems: true,
                api: async ({query}) => loadFunctionalities(query),
            })),
            new Column({span: 6}, new TextField({name: 'id', label: translate('ID'), required: true, readonly: viewing})),
        ),
        new Row(
            new Column({span: 6}, new TextField({name: 'name', label: translate('NAME'), required: true, readonly: viewing})),
            new Column({span: 6}, new TextField({name: 'description', label: translate('DESCRIPTION'), readonly: viewing})),
        ),
        new Row(
            new Column({span: 6}, new TextField({name: 'value', label: translate('VALUE'), readonly: viewing})),
            new Column({span: 6}),
        ),
        new Row(new IconButtonColumn({span: 1},
            new IconButton({buttonClasses: ['btn', 'back-btn'], title: translate('BACK'), onClick: () => { window.location.href = 'functionalities.html'; }}),
            new IconButton({buttonClasses: ['btn', 'save-btn'], title: translate('SAVE'), onClick: () => save(form), readonly: viewing}),
        )),
    );

    document.getElementById('content').appendChild(form.render());
    if (!creating) load(functionality, id);

    async function loadFunctionalities(query) {
        if (!query) query = '';
        const result = await new Fetcher({url: translate('API_URL') + '/functionalities.php', withWaitingWheel: false})
            .withQuery('action', 'functionalities').withMethod('GET').withQuery('functionality', query)
            .onError(() => showError(translate('ERROR')))
            .fetch();
        return result ? result.items : [];
    }

    function load(functionality, id) {
        new Fetcher({url: translate('API_URL') + '/functionalities.php'})
            .withMethod('GET').withQuery('functionality', functionality).withQuery('id', id)
            .onSuccess((data) => form.load(data.item))
            .onError(() => showError(translate('ERROR')))
            .fetch();
    }

    function save(form) {
        if (form.validate()) {
            new Fetcher({url: translate('API_URL') + '/functionalities.php'})
                .withMethod(creating ? 'POST' : 'PUT')
                .withBody(form.toObject(true))
                .onSuccess(() => showInfo(translate('SUCCESS')))
                .onError(() => showError(translate('ERROR')))
                .fetch();
        }
    }
}
