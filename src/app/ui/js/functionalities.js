export function initPage(registry) {
    const perms = registry.get('permissions') || {};

    let itemNavigation = new GridItemNavigation({buildLinkCallback: (row) => 'http://test/?id=' + row.id});
    const form = new Form(
        new Row(
            new Column({span: 6}, new TextField({name: 'functionality_like', label: translate('FUNCTIONALITY')})),
            new Column({span: 6}, new TextField({name: 'name_like', label: translate('NAME')})),
        ),
        new Row(
            new Column({span: 6}, new TextField({name: 'value_like', label: translate('VALUE')})),
            new Column({span: 6}),
        ),
        new Row(
            new Grid({
                span: 12, xls: false,
                canBrowse: perms.can_read,
                canAdd: perms.can_create,
                canRemove: perms.can_delete,
                rows: [
                    {id: 'functionality', label: translate('functionality')},
                    {id: 'id', label: translate('id')},
                    {id: 'name', label: translate('name')},
                    {id: 'description', label: translate('description')},
                    {id: 'value', label: translate('value')},
                ],
            })
                .withLoaded((grid, data) => itemNavigation.loadData(grid, data))
                .withId('functionalities')
                .withStandardSearchApi(translate('API_URL') + '/functionalities.php')
        ),
    );

    document.getElementById('content').appendChild(form.render());

    const grid = form.getById('functionalities');
    grid.withEventHandler('detail-show', (g, data) => { window.location.href = `functionalities_edit.html?functionality=${data.functionality}&id=${data.id}`; });
    grid.withEventHandler('detail-edit', (g, data) => { window.location.href = `functionalities_edit.html?functionality=${data.functionality}&id=${data.id}&edit=true`; });
    grid.withEventHandler('detail-delete', (g, data) => {
        showConfirm(translate('ARE_YOU_SURE_DELETE'), () => {
            new Fetcher({url: translate('API_URL') + '/functionalities.php'})
                .withMethod('DELETE')
                .withQuery('functionality', data.functionality)
                .withQuery('id', data.id)
                .onSuccess(() => grid.refreshSearch())
                .onError(() => showInfo(translate('ERROR')))
                .fetch();
        });
    });
    grid.withEventHandler('add-new', () => { window.location.href = 'functionalities_edit.html?edit=true'; });
}
