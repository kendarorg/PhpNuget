export function initPage(registry) {
    const id      = getQueryParam('id') || null;
    const editing = hasQueryParam('edit') && getQueryParam('edit') === 'true';
    const viewing = !editing;
    const creating = !id;
    const perms = registry.get('permissions') || {};
    const canSeeFinancial = perms.can_read || perms.can_create;

    if (id) new GridItemNavigation({gridName: 'users', containerElement: 'content'});

    const extraEditRows = creating ? [] : [
        new Row(new Column({span: 6},
            new TextField({type: 'password', label: translate('CONFIRM_PASSWORD'), readonly: viewing, sendable: false, name: 'password_confirm'}),
        )),
        new Row(new Column({span: 12},
            new TextButton({label: translate('CHANGE_PASSWORD'), readonly: viewing, onClick: (tb) => changePassword(tb.form)}),
        )),
    ];

    // nuget upload token: admins can view and regenerate it (never free-typed).
    const apiKeyRows = (!creating && perms.can_read) ? [
        new Row(
            new Column({span: 8}, new TextField({name: 'apiKey', readonly: true, label: translate('api_key')})),
            new Column({span: 4}, new TextButton({label: translate('regenerate'), readonly: viewing, onClick: () => regenerateApiKey(form)})),
        ),
    ] : [];

    const financialRows = canSeeFinancial ? [
        new Row(
            new Column({span: 6}, new TextField({name: 'tariffaOraria', type: 'number', required: true, label: translate('HOURLY_COST'), readonly: viewing})),
            new Column({span: 6}, new TextField({name: 'livello', label: translate('level'), readonly: viewing})),
        ),
        new Row(new Column({span: 12}, new TextArea({name: 'notes', label: translate('notes'), readonly: viewing}))),
        new Row(permissionsGrid(viewing, perms.can_read, perms.can_create, perms.can_delete)),
    ] : [];

    const form = new Form(
        new Row(new IconButtonColumn({span: 1},
            new IconButton({buttonClasses: ['btn', 'back-btn'], title: translate('BACK'), onClick: () => { window.location.href = 'users.html'; }}),
            new IconButton({buttonClasses: ['btn', 'save-btn'], title: translate('SAVE'), onClick: () => save(form), readonly: viewing}),
        )),
        new Row(
            new Column({span: 4},
                new HiddenField({name: 'id'}),
                new TextField({name: 'username', readonly: viewing, required: true, label: translate('username')}),
            ),
            new Column({span: 4}, new TextField({label: translate('COMPANY_NAME'), name: 'ragioneSociale', required: true, readonly: viewing})),
        ),
        new Row(
            new Column({span: 6}, new TextField({name: 'password', readonly: viewing, type: 'password', sendable: false, label: translate('NEW_PASSWORD')})),
            new Column({span: 6}),
        ),
        ...extraEditRows,
        new Row(
            new Column({span: 6}, new RolesSelect({defaultValue: 'Ospite', name: 'role', allowEmpty: false, label: translate('ROLE'), readonly: viewing})),
            new Column({span: 6}, new TextField({name: 'code', label: translate('code'), required: true, readonly: viewing})),
        ),
        new Row(
            new Column({span: 6}, new TextField({name: 'codiceFiscale', label: translate('TAX_CODE'), readonly: viewing})),
            new Column({span: 6}, new TextField({name: 'partitaIva', label: translate('VAT_NUMBER'), readonly: viewing})),
        ),
        new Row(
            new Column({span: 6}, new TextField({name: 'indirizzo', label: translate('ADDRESS'), readonly: viewing})),
            new Column({span: 6}, new TextField({name: 'citta', label: translate('TOWN'), readonly: viewing})),
        ),
        new Row(
            new Column({span: 4}, new TextField({name: 'provincia', label: translate('province'), readonly: viewing})),
            new Column({span: 4}, new TextField({name: 'cap', label: translate('POSTAL_CODE'), readonly: viewing})),
            new Column({span: 4}, new TextField({name: 'country', label: translate('COUNTRY'), readonly: viewing})),
        ),
        new Row(
            new Column({span: 4}, new TextField({name: 'email', label: translate('email'), required: true, readonly: viewing})),
            new Column({span: 4}, new TextField({name: 'telefono', label: translate('phone'), readonly: viewing})),
            new Column({span: 4}, new TextField({name: 'fax', label: translate('fax'), readonly: viewing})),
        ),
        ...apiKeyRows,
        ...financialRows,
        new Row(new IconButtonColumn({span: 1},
            new IconButton({buttonClasses: ['btn', 'back-btn'], title: translate('BACK'), onClick: () => { window.location.href = 'users.html'; }}),
            new IconButton({buttonClasses: ['btn', 'save-btn'], title: translate('SAVE'), onClick: () => save(form), readonly: viewing}),
        )),
    );

    document.getElementById('content').appendChild(form.render());
    if (!creating) load(id);

    function load(id) {
        new Fetcher({url: translate('API_URL') + '/users.php'})
            .withMethod('GET').withQuery('id', id)
            .onSuccess((data) => form.load(data.item))
            .onError(() => showError(translate('ERROR')))
            .fetch();
    }

    function save(form) {
        if (form.validate()) {
            new Fetcher({url: translate('API_URL') + '/users.php'})
                .withMethod(creating ? 'POST' : 'PUT')
                .withBody(form.toObject(true))
                .onSuccess(() => showInfo(translate('SUCCESS')))
                .onError(() => showError(translate('ERROR')))
                .fetch();
        }
    }

    function regenerateApiKey(form) {
        const uid = form.getByName('id').value;
        if (!uid) return;
        if (!confirm(translate('regenerate_confirm'))) return;
        new Fetcher({url: translate('API_URL') + '/users.php'})
            .withMethod('POST').withQuery('action', 'regenerateApiKey').withQuery('id', uid)
            .onSuccess((d) => { form.getByName('apiKey').setValue(d.apiKey || ''); showInfo(translate('SUCCESS')); })
            .onError(() => showError(translate('ERROR')))
            .fetch();
    }

    function changePassword(form) {
        const prevp = form.getByName('password');
        const newp  = form.getByName('password_confirm');
        const id    = form.getByName('id').value;
        if (prevp.value && newp.value) {
            if (prevp.value !== newp.value) {
                prevp.setInvalid(); newp.setInvalid();
                showError(translate('PASSWORDS_NOT_MATCHING'));
            } else {
                prevp.setValid(); newp.setValid();
                new Fetcher({url: translate('API_URL') + '/users.php'})
                    .withMethod('PUT').withQuery('action', 'changepassword').withQuery('id', id)
                    .withBody({password: newp.value})
                    .onSuccess(() => showInfo(translate('SUCCESS')))
                    .onError(() => showError(translate('ERROR')))
                    .fetch();
            }
        } else {
            prevp.setInvalid(); newp.setInvalid();
            showError(translate('PASSWORDS_NOT_MATCHING'));
        }
    }
}
