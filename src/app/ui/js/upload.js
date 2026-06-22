// Nuget upload endpoint, relative to src/app/ui/  ->  src/upload/
const UPLOAD_URL = '../../upload/';

export function initPage(registry) {
    const perms = registry.get('permissions') || {};
    const isAdmin = !!perms.can_create;
    const content = document.getElementById('content');

    let apiKey = '';
    let feed = '';

    // pull the current user's API key + feed url so we can show CLI snippets and push from the browser.
    new Fetcher({url: translate('API_URL') + '/profile.php'})
        .withMethod('GET')
        .onSuccess((data) => {
            apiKey = (data.item && data.item.apiKeyFull) || '';
            feed = data.feed || '';
            renderSnippets();
        })
        .onError(() => showError(translate('ERROR')))
        .fetch();

    // --- Upload panel ---
    const panel = card(translate('upload_package'));
    content.appendChild(panel);

    const fileInput = document.createElement('input');
    fileInput.type = 'file';
    fileInput.accept = '.nupkg,.snupkg';
    panel.appendChild(fileInput);

    const btn = document.createElement('button');
    btn.className = 'btn save-btn';
    btn.textContent = translate('UPLOAD');
    btn.style.marginLeft = '0.5rem';
    btn.onclick = () => doUpload(fileInput.files[0]);
    panel.appendChild(btn);

    const snippets = document.createElement('pre');
    snippets.style.marginTop = '1rem';
    panel.appendChild(snippets);

    function renderSnippets() {
        const src = (feed || '') + 'upload';
        snippets.textContent =
            `NuGet SetApiKey ${apiKey} -Source ${src}\n` +
            `NuGet Push mypackage.nupkg -Source ${src}\n` +
            `dotnet nuget push mypackage.nupkg --api-key ${apiKey} --source ${src}`;
    }

    function doUpload(file) {
        if (!file) { showError(translate('ERROR')); return; }
        const fd = new FormData();
        fd.append('package', file);
        fetch(UPLOAD_URL, {method: 'POST', headers: {'X-NuGet-ApiKey': apiKey}, body: fd})
            .then((r) => r.text().then((t) => ({ok: r.ok, t})))
            .then(({ok, t}) => {
                if (ok) showInfo(translate('SUCCESS') + ': ' + t);
                else showError(t || translate('ERROR'));
            })
            .catch(() => showError(translate('ERROR')));
    }

    // --- Admin: load from another repository ---
    if (isAdmin) {
        const pullPanel = card(translate('load_from_repo'));
        content.appendChild(pullPanel);
        const pullForm = new Form(
            new Row(new Column({span: 12}, new TextField({name: 'Url', label: translate('repo_url_template')}))),
            new Row(
                new Column({span: 6}, new TextField({name: 'Id', label: translate('id')})),
                new Column({span: 6}, new TextField({name: 'Version', label: translate('version')})),
            ),
        );
        pullPanel.appendChild(pullForm.render());
        const pullBtn = document.createElement('button');
        pullBtn.className = 'btn save-btn';
        pullBtn.textContent = translate('UPLOAD');
        pullBtn.onclick = () => {
            new Fetcher({url: translate('API_URL') + '/packages.php'})
                .withMethod('POST').withQuery('action', 'pull')
                .withBody(pullForm.toObject(true))
                .onSuccess(() => showInfo(translate('SUCCESS')))
                .onError(() => showError(translate('ERROR')))
                .fetch();
        };
        pullPanel.appendChild(pullBtn);

        const refreshPanel = card(translate('refresh_packages'));
        content.appendChild(refreshPanel);
        const refreshBtn = document.createElement('button');
        refreshBtn.className = 'btn save-btn';
        refreshBtn.textContent = translate('GO');
        refreshBtn.onclick = () => {
            new Fetcher({url: translate('API_URL') + '/packages.php'})
                .withMethod('POST').withQuery('action', 'refresh')
                .onSuccess((d) => showInfo(translate('SUCCESS') + ': ' + (d.refreshed || 0)))
                .onError(() => showError(translate('ERROR')))
                .fetch();
        };
        refreshPanel.appendChild(refreshBtn);
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
