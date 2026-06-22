export function initPage(registry) {
    const perms = registry.get('permissions') || {};

    const form = new Form(
        new Row(
            new Column({span: 12},
                new TextField({name: 'search', label: translate('search')}),
            ),
        ),
        new Row(
            new Grid({
                span: 12,
                canBrowse: perms.can_read,
                canAdd: false,
                canRemove: perms.can_delete,
                rows: [
                    {id: 'Id', label: translate('id')},
                    {id: 'Version', label: translate('version')},
                    {id: 'Title', label: translate('title')},
                    {id: 'DownloadCount', label: translate('downloads')},
                    {id: 'Tags', label: translate('tags')},
                ],
            })
                .withId('packages')
                .withStandardSearchApi(translate('API_URL') + '/packages.php')
        ),
    );

    document.getElementById('content').appendChild(form.render());

    const grid = form.getById('packages');
    grid.withEventHandler('detail-show', (g, data) => {
        window.location.href = `package_detail.html?id=${encodeURIComponent(data.Id)}&version=${encodeURIComponent(data.Version)}`;
    });
}
