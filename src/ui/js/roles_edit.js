export function initPage(registry) {
    const id      = getQueryParam('id') || null;
    const editing = hasQueryParam('edit') && getQueryParam('edit') === 'true';
    const viewing = !editing;
    const creating = !id;
    const perms = registry.get('permissions') || {};

    if (id) new GridItemNavigation({gridName: 'roles', containerElement: 'content'});

    const form = new Form(
        new Row(new IconButtonColumn({span: 1},
            new IconButton({buttonClasses: ['btn', 'back-btn'], title: translate('BACK'), onClick: () => { window.location.href = 'roles.html'; }}),
            new IconButton({buttonClasses: ['btn', 'save-btn'], title: translate('SAVE'), onClick: () => save(form), readonly: viewing}),
        )),
        new Row(
            new Column({span: 6}, new TextField({name: 'id', label: translate('id'), readonly: true})),
            new Column({span: 6}, new TextField({name: 'name', label: translate('name'), readonly: true})),
        ),
        new Row(new Column({span: 12}, new TextArea({name: 'description', label: translate('description'), readonly: viewing}))),
        new Row(permissionsGrid(viewing, perms.can_read, perms.can_create, perms.can_delete)),
        new Row(new IconButtonColumn({span: 1},
            new IconButton({buttonClasses: ['btn', 'back-btn'], title: translate('BACK'), onClick: () => { window.location.href = 'roles.html'; }}),
            new IconButton({buttonClasses: ['btn', 'save-btn'], title: translate('SAVE'), onClick: () => save(form), readonly: viewing}),
        )),
    );

    document.getElementById('content').appendChild(form.render());
    if (!creating) load(id);

    function load(id) {
        new Fetcher({url: translate('API_URL') + '/roles.php'})
            .withMethod('GET').withQuery('id', id)
            .onSuccess((data) => form.load(data.item))
            .onError(() => showError(translate('ERROR')))
            .fetch();
    }

    function save(form) {
        if (form.validate()) {
            new Fetcher({url: translate('API_URL') + '/roles.php'})
                .withMethod(creating ? 'POST' : 'PUT')
                .withBody(form.toObject(true))
                .onSuccess(() => showInfo(translate('SUCCESS')))
                .onError(() => showError(translate('ERROR')))
                .fetch();
        }
    }
}


function permissionsGrid(viewing,canRead,canCreate,canDelete){
    if(viewing){
        canCreate=false;
        canDelete=false;
    }
    let grid = new Grid({span:12,
        name:"permissions",
        fixed:true,
        sendable:true,
        canBrowse:canRead,
        canEdit: canCreate && !viewing,
        canAdd:canCreate && !viewing,
        canRemove:canDelete && !viewing,
        rows:[
            {id: 'id',label:translate("id")},
            {id: 'permissions',label:translate("permissions")}
        ]})
    grid.withEventHandler("detail-show",(grid,data,index)=>{
        let role = grid.form.getByName("id").value;
        showPermissionsDialog(role,translate("DO_SHOW"),true,grid,data,index);
    });
    grid.withEventHandler("detail-edit",(grid,data,index)=>{
        let role = grid.form.getByName("id").value;
        showPermissionsDialog(role,translate("DO_EDIT"),viewing,grid,data,index);
    });
    grid.withEventHandler("detail-delete",(grid,data,index)=>{
        showConfirm(translate("ARE_YOU_SURE_DELETE"),()=>{
            grid.removeRow(index);
        })
    });
    grid.withEventHandler("add-new",(grid,data,index)=>{
        let role = grid.form.getByName("id").value;
        showPermissionsDialog(role,translate("DO_ADD"),false,grid,{},-1);
    });

    return grid;
}

function showPermissionsDialog(role,action,viewOnly,grid,data,index){
    if(role===null || typeof role === "undefined" || role===""){
        showError(translate("CREATE_ITEM_BEFORE_ADDING_CHILDREN"))
    }
    data.role=role;
    let form = new Form(
        new Row(
            new Column({span:4},
                new TextField({
                    name:"role",
                    label:translate("role"),
                    readonly:true
                })
            ),
            new Column({span:4},
                new TextField({
                    name:"id",
                    label:translate("id"),
                    readonly:index!=-1
                })
            ),
            new Column({span:4},
                new TextField({
                    name:"permissions",
                    label:translate("permission"),
                    required:true,
                    readonly:viewOnly
                })
            )
        )
    )
    new Dialog({
        title: action,
        content: form,
        onConfirm: (receivedData) => {
            data.permissions =receivedData.permissions;
            data.id = receivedData.id;
            if(index>=0)grid.data[index]=data;
            else {

                if(grid.data.findIndex(obj => obj.id === data.id)){
                    showError(translate("DUPLICATED_ITEM"));
                    return false;
                }
                grid.data.push(data);
            }
            grid.load(grid.data);
            return true;
        },
        onCancel: () => console.log("cancelled")
    }).open(data);
}