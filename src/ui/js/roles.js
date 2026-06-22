export function initPage(registry) {
    const perms = registry.get('permissions') || {};

    let itemNavigation = new GridItemNavigation({buildLinkCallback: (row) => 'http://test/?id=' + row.id});
    const form = new Form(
        new Row(
            new Column({span: 6}, new TextField({name: 'id', label: translate('id')})),
            new Column({span: 6}, new TextField({name: 'name_desc', label: translate('NAME')})),
        ),
        new Row(
            new Grid({
                span: 12, xls: false,
                adaptContent: data => { if (!data.name) data.name = data.ragioneSociale; return data; },
                canBrowse: perms.can_read,
                canAdd: perms.can_create,
                canRemove: perms.can_delete,
                rows: [
                    {id: 'id', label: translate('id')},
                    {id: 'name', label: translate('name')},
                    {id: 'description', label: translate('description')},
                ],
            })
                .withLoaded((grid, data) => itemNavigation.loadData(grid, data))
                .withId('roles')
                .withStandardSearchApi(translate('API_URL') + '/roles.php')
        ),
    );

    document.getElementById('content').appendChild(form.render());

    const grid = form.getById('roles');
    grid.withEventHandler('detail-show', (g, data) => { window.location.href = `roles_edit.html?id=${data.id}`; });
    grid.withEventHandler('detail-edit', (g, data) => { window.location.href = `roles_edit.html?id=${data.id}&edit=true`; });
    grid.withEventHandler('detail-delete', (g, data) => {
        showConfirm(translate('ARE_YOU_SURE_DELETE'), () => {
            new Fetcher({url: translate('API_URL') + '/roles.php'})
                .withMethod('DELETE').withQuery('id', data.id)
                .onSuccess(() => grid.refreshSearch())
                .onError(() => showInfo(translate('ERROR')))
                .fetch();
        });
    });
    grid.withEventHandler('add-new', () => { window.location.href = 'roles_edit.html?edit=true'; });
}
