export function initPage(registry) {
    const user = registry.get('user') || {};
    const content = document.getElementById('content');

    const wrap = document.createElement('div');
    wrap.className = 'card';
    wrap.style.padding = '1.5rem';

    const h = document.createElement('h2');
    h.textContent = translate('welcome') + (user.display_name ? ', ' + user.display_name : '');
    wrap.appendChild(h);

    const link = document.createElement('a');
    link.href = 'packages.html';
    link.className = 'btn';
    link.textContent = translate('packages');
    wrap.appendChild(link);

    content.appendChild(wrap);
}
