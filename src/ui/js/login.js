export function initPage(registry) {
    const logo = document.getElementById('company-logo');
    if (logo) logo.src = registry.get('company_logo') || '';

    const form = new Form(
        new Row(new Column({span: 12},
            new TextField({name: 'login', label: translate('LOGIN'), required: true}),
        )),
        new Row(new Column({span: 12},
            new TextField({type: 'password', name: 'password', label: translate('PASSWORD'), required: true}),
        )),
        new Row(new Column({span: 12},
            new TextButton({label: translate('LOGIN'), onClick: () => doLogin(form)}),
        )),
        new Row(new Column({span: 12},
            new TextButton({label: translate('RESET_PASSWORD'), onClick: () => doResetPassword(form)}),
        )),
    );
    document.getElementById('login-container-int').appendChild(form.render());

    function doLogin(form) {
        if (form.validate()) {
            new Fetcher({url: translate('API_URL') + '/login.php'})
                .withQuery('action', 'login')
                .withMethod('POST')
                .withBody(form.toObject())
                .onSuccess(() => {
                    localStorage.clear();
                    if (hasQueryParam('back')) {
                        window.location.href = atob(getQueryParam('back'));
                    } else {
                        window.location.href = 'dashboard.html';
                    }
                })
                .onError(() => showError(translate('ADMIN_ERROR')))
                .fetch();
        }
    }

    function doResetPassword(form) {
        if (form.getField('login').validate()) {
            new Fetcher({url: translate('API_URL') + '/login.php'})
                .withQuery('action', 'resetPassword')
                .withQuery('login', form.getFieldValue('login'))
                .onSuccess(() => showInfo(translate('SUCCESS')))
                .onError(() => showError(translate('ADMIN_ERROR')))
                .fetch();
        } else {
            showError(translate('MISSING_FIELD', 'LOGIN'));
        }
    }
}
