export function initPage(registry) {
    const perms = registry.get('permissions') || {};
    const simulatePerms = registry.get('permissions2') || {};
    const canSimulate = !!simulatePerms.can_create;

    const simulateButton = {
        enabled: () => canSimulate,
        apply: (grid, i, colContent, td) => {
            const bt = document.createElement('button');
            bt.classList.add('btn', 'simulate-btn', 'icon-btn');
            bt.title = translate('SIMULATE');
            bt.setAttribute('data-tooltip', bt.title);
            bt.setAttribute('data-id', colContent.id);
            bt.addEventListener('click', (e) => {
                e.preventDefault();
                showConfirm(translate('ARE_YOU_SURE_SIMULATE'), () => {
                    new Fetcher({url: translate('API_URL') + '/users.php'})
                        .withMethod('POST').withQuery('action', 'simulate')
                        .withBody({id: colContent.id})
                        .onSuccess(() => { window.location.href = 'dashboard.html'; })
                        .onError((data) => showError((data && data.message) || translate('ERROR')))
                        .fetch();
                });
            });
            td.appendChild(bt);
        },
    };

    let itemNavigation = new GridItemNavigation({buildLinkCallback: (row) => 'http://test/?id=' + row.id});
    const form = new Form(
        new Row(
            new Column({span: 6},
                new HiddenField({name: 'id'}),
                new TextField({name: 'taxcode', label: translate('TAX_CODES')}),
            ),
            new Column({span: 6}, new TextField({name: 'names', label: translate('NAME')})),
        ),
        new Row(
            new Column({span: 6}, new RolesSelect({name: 'role', allowEmpty: true, label: translate('ROLE')})),
            new Column({span: 6}, new ComboField({
                name: 'locked', label: translate('STATUS'),
                options: [
                    {value: 'false', label: translate('ACTIVE'), selected: true},
                    {value: 'true', label: translate('INACTIVE')},
                    {value: '', label: translate('ALL')},
                ],
            })),
        ),
        new Row(
            new Grid({
                span: 12,
                adaptContent: data => {
                    data.status = translate('active');
                    if (data.locked === true) data.status = translate('locked');
                    return data;
                },
                canBrowse: perms.can_read,
                canAdd: perms.can_create,
                canRemove: perms.can_delete,
                rows: [
                    {id: 'code', label: translate('code')},
                    {id: 'username', label: translate('username')},
                    {id: 'ragioneSociale', label: translate('business_name')},
                    {id: 'email', label: translate('email')},
                    {id: 'role', label: translate('role')},
                    {id: 'status', label: translate('status')},
                ],
                extraButtons: [simulateButton],
            })
                .withLoaded((grid, data) => itemNavigation.loadData(grid, data))
                .withId('users')
                .withStandardSearchApi(translate('API_URL') + '/users.php')
        ),
    );

    document.getElementById('content').appendChild(form.render());

    const grid = form.getById('users');
    grid.withEventHandler('detail-show', (g, data) => { window.location.href = `users_edit.html?id=${data.id}`; });
    grid.withEventHandler('detail-edit', (g, data) => { window.location.href = `users_edit.html?id=${data.id}&edit=true`; });
    grid.withEventHandler('detail-delete', (g, data) => {
        showConfirm(translate('ARE_YOU_SURE_DELETE'), () => {
            new Fetcher({url: translate('API_URL') + '/users.php'})
                .withMethod('DELETE').withQuery('id', data.id)
                .onSuccess(() => grid.refreshSearch())
                .onError(() => showInfo(translate('ERROR')))
                .fetch();
        });
    });
    grid.withEventHandler('add-new', () => { window.location.href = 'users_edit.html?edit=true'; });
}
