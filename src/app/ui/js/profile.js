export function initPage(registry) {
    const content = document.getElementById('content');
    let feed = '';

    // --- Profile form ---
    const form = new Form(
        new Row(new IconButtonColumn({span: 1},
            new IconButton({buttonClasses: ['btn', 'save-btn'], title: translate('SAVE'), onClick: () => save()}),
        )),
        new Row(
            new Column({span: 6}, new TextField({name: 'username', readonly: true, label: translate('username')})),
            new Column({span: 6}, new TextField({name: 'ragioneSociale', label: translate('COMPANY_NAME')})),
        ),
        new Row(
            new Column({span: 6}, new TextField({name: 'email', label: translate('email')})),
            new Column({span: 6}, new TextField({name: 'telefono', label: translate('phone')})),
        ),
        new Row(
            new Column({span: 6}, new TextField({name: 'password', type: 'password', sendable: false, label: translate('NEW_PASSWORD')})),
            new Column({span: 6}, new TextField({name: 'password_confirm', type: 'password', sendable: false, label: translate('CONFIRM_PASSWORD')})),
        ),
        new Row(new Column({span: 12},
            new TextButton({label: translate('CHANGE_PASSWORD'), onClick: () => changePassword()}),
        )),
    );
    const profilePanel = card(translate('profile'));
    profilePanel.appendChild(form.render());
    content.appendChild(profilePanel);

    // --- API key panel ---
    const keyPanel = card(translate('my_api_key'));
    content.appendChild(keyPanel);
    const keyField = document.createElement('input');
    keyField.type = 'text';
    keyField.readOnly = true;
    keyField.style.width = '100%';
    keyPanel.appendChild(keyField);
    const snippet = document.createElement('pre');
    keyPanel.appendChild(snippet);
    const regen = document.createElement('button');
    regen.className = 'btn';
    regen.textContent = translate('regenerate');
    regen.onclick = () => regenerate();
    keyPanel.appendChild(regen);

    // --- My packages grid ---
    const gridForm = new Form(
        new Row(new Column({span: 12}, new TextField({name: 'search', label: translate('search')}))),
        new Row(new Grid({
            span: 12, canAdd: false, canRemove: false, canEdit: false,
            rows: [
                {id: 'Id', label: translate('id')},
                {id: 'Version', label: translate('version')},
                {id: 'Title', label: translate('title')},
                {id: 'DownloadCount', label: translate('downloads')},
            ],
        }).withId('mypackages').withStandardSearchApi(translate('API_URL') + '/profile.php')),
    );
    const gridPanel = card(translate('my_packages'));
    gridPanel.appendChild(gridForm.render());
    content.appendChild(gridPanel);
    gridForm.getById('mypackages').withEventHandler('detail-show', (g, data) => {
        window.location.href = `package_detail.html?id=${encodeURIComponent(data.Id)}&version=${encodeURIComponent(data.Version)}`;
    });

    load();

    function load() {
        new Fetcher({url: translate('API_URL') + '/profile.php'})
            .withMethod('GET')
            .onSuccess((data) => {
                form.load(data.item);
                feed = data.feed || '';
                keyField.value = data.item.apiKeyFull || '';
                renderSnippet();
            })
            .onError(() => showError(translate('ERROR')))
            .fetch();
    }

    function renderSnippet() {
        snippet.textContent = `NuGet SetApiKey ${keyField.value} -Source ${feed}upload`;
    }

    function save() {
        new Fetcher({url: translate('API_URL') + '/profile.php'})
            .withMethod('POST').withQuery('action', 'save')
            .withBody(form.toObject(true))
            .onSuccess(() => showInfo(translate('SUCCESS')))
            .onError(() => showError(translate('ERROR')))
            .fetch();
    }

    function changePassword() {
        const p = form.getByName('password');
        const c = form.getByName('password_confirm');
        if (!p.value || p.value !== c.value) {
            p.setInvalid(); c.setInvalid();
            showError(translate('PASSWORDS_NOT_MATCHING'));
            return;
        }
        p.setValid(); c.setValid();
        new Fetcher({url: translate('API_URL') + '/profile.php'})
            .withMethod('POST').withQuery('action', 'password')
            .withBody({password: p.value})
            .onSuccess(() => showInfo(translate('SUCCESS')))
            .onError(() => showError(translate('ERROR')))
            .fetch();
    }

    function regenerate() {
        if (!confirm(translate('regenerate_confirm'))) return;
        new Fetcher({url: translate('API_URL') + '/profile.php'})
            .withMethod('POST').withQuery('action', 'regenerateApiKey')
            .onSuccess((d) => { keyField.value = d.apiKey || ''; renderSnippet(); showInfo(translate('SUCCESS')); })
            .onError(() => showError(translate('ERROR')))
            .fetch();
    }

    function card(title) {
        const c = document.createElement('div');
        c.className = 'card';
        c.style.padding = '1rem';
        c.style.marginBottom = '1rem';
        const h = document.createElement('h4');
        h.textContent = title;
        c.appendChild(h);
        return c;
    }
}
