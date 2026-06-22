async function renderApiUi(map) {
    const jsPath = translate('API_URL') + '/reports/' + map.report + '.js';
    const code = await fetch(jsPath).then(r => r.text());
    (new Function('params', code))(map);
}
