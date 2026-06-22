
function permissionsGrid(viewing,canRead,canCreate,canDelete){
    if(!canCreate && !canRead){
        return new Column({span:12});
    }
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
            {id: 'id',label:translate("USER_LEVEL_PERMISSIONS")},
            {id: 'permissions',label:translate("permissions")}
        ]})
    grid.withEventHandler("detail-show",(grid,data,index)=>{
        showPermissionsDialog(translate("DO_SHOW"),true,grid,data,index);
    });
    grid.withEventHandler("detail-edit",(grid,data,index)=>{
        showPermissionsDialog(translate("DO_EDIT"),viewing,grid,data,index);
    });
    grid.withEventHandler("detail-delete",(grid,data,index)=>{
        showConfirm(translate("ARE_YOU_SURE_DELETE"),()=>{
            grid.removeRow(index);
        })
    });
    grid.withEventHandler("add-new",(grid,data,index)=>{
        showPermissionsDialog(translate("DO_ADD"),false,grid,{},-1);
    });

    return grid;
}

function showPermissionsDialog(action,viewOnly,grid,data,index){
    let form = new Form(
        new Row(
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