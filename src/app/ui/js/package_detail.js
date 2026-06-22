export function initPage(registry) {
    const id      = getQueryParam('id') || null;
    const version = getQueryParam('version') || null;
    const perms   = registry.get('permissions') || {};
    const viewing = !(perms.can_create || perms.can_update);

    const form = new Form(
        new Row(new IconButtonColumn({span: 1},
            new IconButton({buttonClasses: ['btn', 'back-btn'], title: translate('BACK'), onClick: () => { window.location.href = 'packages.html'; }}),
            new IconButton({buttonClasses: ['btn', 'save-btn'], title: translate('SAVE'), onClick: () => save(), readonly: viewing}),
        )),
        new Row(
            new Column({span: 6}, new TextField({name: 'Id', readonly: true, label: translate('id')})),
            new Column({span: 6}, new TextField({name: 'Version', readonly: true, label: translate('version')})),
        ),
        new Row(
            new Column({span: 6}, new TextField({name: 'Title', readonly: viewing, label: translate('title')})),
            new Column({span: 6}, new TextField({name: 'Author', readonly: viewing, label: translate('authors')})),
        ),
        new Row(new Column({span: 12}, new TextArea({name: 'Summary', readonly: viewing, label: translate('summary')}))),
        new Row(new Column({span: 12}, new TextArea({name: 'Description', readonly: viewing, label: translate('DESCRIPTION')}))),
        new Row(
            new Column({span: 6}, new TextField({name: 'Copyright', readonly: viewing, label: translate('copyright')})),
            new Column({span: 6}, new TextField({name: 'Tags', readonly: viewing, label: translate('tags')})),
        ),
        new Row(
            new Column({span: 6}, new TextField({name: 'IconUrl', readonly: viewing, label: translate('icon_url')})),
            new Column({span: 6}, new TextField({name: 'ProjectUrl', readonly: viewing, label: translate('project_url')})),
        ),
        new Row(
            new Column({span: 6}, new TextField({name: 'LicenseUrl', readonly: viewing, label: translate('license_url')})),
            new Column({span: 6}, new TextField({name: 'DownloadCount', readonly: true, label: translate('downloads')})),
        ),
        new Row(new Column({span: 12}, new TextArea({name: 'ReleaseNotes', readonly: viewing, label: translate('release_notes')}))),
        new Row(
            new Column({span: 4}, new CheckboxField({name: 'Listed', readonly: viewing, label: translate('is_listed')})),
            new Column({span: 4}, new CheckboxField({name: 'RequireLicenseAcceptance', readonly: viewing, label: translate('require_license_acceptance')})),
            new Column({span: 4}, new CheckboxField({name: 'IsPreRelease', readonly: true, label: translate('is_pre_release')})),
        ),
        new Row(new IconButtonColumn({span: 1},
            new IconButton({buttonClasses: ['btn', 'delete-btn'], title: translate('DELETE'), onClick: () => remove(), readonly: !perms.can_delete}),
        )),
    );

    const content = document.getElementById('content');
    content.appendChild(form.render());

    // side panels: versions + install snippet
    const aside = document.createElement('div');
    aside.className = 'card';
    aside.style.padding = '1rem';
    aside.style.marginTop = '1rem';
    content.appendChild(aside);

    load();

    function load() {
        const f = new Fetcher({url: translate('API_URL') + '/packages.php'})
            .withMethod('GET').withQuery('action', 'detail').withQuery('id', id);
        if (version) f.withQuery('version', version);
        f.onSuccess((data) => {
            form.load(data.item);
            renderAside(data.item, data.versions || [], data.feed || '');
        }).onError(() => showError(translate('ERROR'))).fetch();
    }

    function renderAside(item, versions, feed) {
        aside.innerHTML = '';

        const vh = document.createElement('h4');
        vh.textContent = translate('versions');
        aside.appendChild(vh);
        versions.forEach((v) => {
            const a = document.createElement('a');
            a.href = `package_detail.html?id=${encodeURIComponent(item.Id)}&version=${encodeURIComponent(v.Version)}`;
            a.textContent = `${v.Version} (${v.VersionDownloadCount || 0})`;
            a.style.display = 'block';
            aside.appendChild(a);
        });

        const sh = document.createElement('h4');
        sh.textContent = translate('install');
        sh.style.marginTop = '1rem';
        aside.appendChild(sh);
        const pre = document.createElement('pre');
        const feedV2 = (feed || '').replace(/\/app\/?$/, '/') + 'api/v2';
        pre.textContent =
            `dotnet add package ${item.Id} --version ${item.Version}\n` +
            `Install-Package ${item.Id} -Version ${item.Version}\n` +
            `Source: ${feedV2}`;
        aside.appendChild(pre);
    }

    function save() {
        if (form.validate()) {
            new Fetcher({url: translate('API_URL') + '/packages.php'})
                .withMethod('POST').withQuery('action', 'save')
                .withBody(form.toObject(true))
                .onSuccess(() => showInfo(translate('SUCCESS')))
                .onError(() => showError(translate('ERROR')))
                .fetch();
        }
    }

    function remove() {
        const item = form.toObject(true);
        new Fetcher({url: translate('API_URL') + '/packages.php'})
            .withMethod('POST').withQuery('action', 'delete')
            .withQuery('id', item.Id).withQuery('version', item.Version)
            .onSuccess(() => { showInfo(translate('SUCCESS')); window.location.href = 'packages.html'; })
            .onError(() => showError(translate('ERROR')))
            .fetch();
    }
}
