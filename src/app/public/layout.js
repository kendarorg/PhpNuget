import GlobalRegistry from './registry.js';

async function loadIncludes() {
    const elements = document.querySelectorAll('[data-include]');
    for (const el of elements) {
        const url = el.getAttribute('data-include');
        const html = await fetch(url).then(r => r.text());
        el.outerHTML = html;
    }
}

function populateHeader(registry) {
    const logo = document.getElementById('company-logo');
    if (logo) logo.src = registry.get('company_logo') || '';

    const title = document.getElementById('page-title');
    if (title) title.textContent = registry.get('page_title') || '';

    const welcome = document.getElementById('user-welcome');
    if (welcome) {
        const user = registry.get('user') || {};
        welcome.textContent = translate('WELCOME', user.display_name, user.role);
    }

    const logoutBtn = document.getElementById('logout-btn');
    if (logoutBtn) {
        logoutBtn.textContent = translate('LOGOUT');
        logoutBtn.onclick = () => {
            new Fetcher({ url: translate('API_URL') + '/login.php', method: 'POST' })
                .withQuery('action', 'logout')
                .onSuccess(() => { window.location.href = 'login.html'; })
                .fetch();
        };
    }
}

function populateMenu(registry) {
    const ul = document.getElementById('menu-list');
    if (!ul) return;
    const menu = registry.get('menu') || [];
    ul.innerHTML = '';
    for (const item of menu) {
        const label = translate(item.key.toUpperCase());
        const li = document.createElement('li');
        li.innerHTML =
            `<div class="menuitemLarge"><a href="${item.page}">${label}</a></div>` +
            `<button title="${label}" class="menuitemSmall btn ${item.class}" onclick="window.location.href='${item.page}'"></button>`;
        ul.appendChild(li);
    }
}

export async function bootstrap(data) {
    const permModule  = data['LOAD-PERMISSIONS']  || null;
    const permModule2 = data['LOAD-PERMISSIONS2'] || null;

    await GlobalRegistry.load(data, permModule, permModule2);

    if (!GlobalRegistry.get('logged_in') && !data['NO-AUTH']) {
        window.location.href = 'login.html';
        return;
    }

    const titleArgs = data['PAGE-TITLE'];
    if (titleArgs) {
        document.title = Array.isArray(titleArgs)
            ? translate(...titleArgs)
            : translate(titleArgs);
        GlobalRegistry.set('page_title', document.title);
    }

    await loadIncludes();

    populateHeader(GlobalRegistry);
    populateMenu(GlobalRegistry);

    if (data['PAGE-MODULE']) {
        const moduleUrl = new URL(data['PAGE-MODULE'], document.baseURI);
        const mod = await import(moduleUrl.href);
        mod.initPage(GlobalRegistry);
    }
}
