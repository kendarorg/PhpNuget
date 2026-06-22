export function initPage(registry) {
    const logo = document.getElementById('company-logo');
    if (logo) logo.src = registry.get('company_logo') || '';

    const token = getQueryParam('token');
    const login = getQueryParam('login');

    if (!token || !login) {
        showError(translate('TOKEN_INVALID'));
        return;
    }

    const form = new Form(
        new Row(new Column({span: 12},
            new TextField({type: 'password', name: 'password',        label: translate('NEW_PASSWORD'),     required: true}),
        )),
        new Row(new Column({span: 12},
            new TextField({type: 'password', name: 'confirmPassword', label: translate('CONFIRM_PASSWORD'), required: true}),
        )),
        new Row(new Column({span: 12},
            new TextButton({label: translate('CHANGE_PASSWORD'), onClick: () => doConfirm(form)}),
        )),
    );
    document.getElementById('login-container-int').appendChild(form.render());

    function passwordRulesOk(pwd) {
        return pwd.length >= 8
            && /[A-Z]/.test(pwd)
            && /[a-z]/.test(pwd)
            && /[0-9]/.test(pwd)
            && /[!?@_\-]/.test(pwd);
    }

    function doConfirm(form) {
        if (!form.validate()) return;

        const pwd     = form.getFieldValue('password');
        const confirm = form.getFieldValue('confirmPassword');

        if (pwd !== confirm) {
            showError(translate('PASSWORDS_NOT_MATCHING'));
            return;
        }
        if (!passwordRulesOk(pwd)) {
            showError(translate('PASSWORD_RULES'));
            return;
        }

        new Fetcher({url: translate('API_URL') + '/login.php'})
            .withQuery('action', 'resetPasswordConfirm')
            .withMethod('POST')
            .withBody({ login: login, token: token, password: pwd })
            .onSuccess(() => {
                showInfo(translate('OPERATION_SUCCESSFUL'));
                setTimeout(() => { window.location.href = 'login.html'; }, 1500);
            })
            .onError(() => showError(translate('ADMIN_ERROR')))
            .fetch();
    }
}
