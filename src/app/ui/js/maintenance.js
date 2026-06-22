const API_URL = () => translate('API_URL') + '/maintenance.php';

class Container extends Node {
    constructor({tag = 'div', html = null, style = null, ...rest} = {}) {
        super(rest);
        this.tag = tag;
        this.html = html;
        this.style = style;
    }
    render(passedContainer = null) {
        if (passedContainer != null) this.container = passedContainer;
        if (!this.shouldRender()) return null;
        const el = document.createElement(this.tag);
        if (this.style) el.setAttribute('style', this.style);
        if (this.html != null) el.innerHTML = this.html;
        this._setContainer(el);
        this._setMainElement(el);
        return el;
    }
}

function daysAgo(n) {
    const d = new Date();
    d.setDate(d.getDate() - n);
    return d.toISOString().slice(0, 10);
}

export function initPage(registry) {
    let errorDetailsData = {};

    const logsGrid = new Grid({
        span: 12,
        canBrowse: true,
        canAdd: false,
        canEdit: false,
        canRemove: false,
        xls: false,
        rows: [
            {id: 'id', label: 'ID'},
            {id: 'username', label: 'User'},
            {id: 'user_id', label: 'User ID', customRender: (td, v) => td.textContent = v===-1?'':v},
            {id: 'type', label: 'Tipo'},
            {id: 'operation', label: 'Operazione'},
            {id: 'operationId', label: 'Operation ID'},
            {id: 'data', label: 'Data', maxLength: 60},
            {id: 'created_at', label: 'Timestamp'},
        ],
    }).withId('maintenance_logs');

    logsGrid.withOnSearch(async (grid, searchQuery) => {
        const result = await new Fetcher({url: API_URL()})
            .withQuery('action', 'search_logs')
            .withMethod('POST')
            .withBody(searchQuery)
            .onError(() => showError(translate('ERROR')))
            .fetch();
        if (result && result.items) grid.load(result.items, searchQuery);
    });

    logsGrid.withEventHandler('detail-show', (g, row) => showLogDetail(row.id));

    const form = new Form(
        new Collapsible({title: 'Contatori interni'},
            new Row(new Column({span: 12},
                new TextButton({label: 'Aggiorna Contatori', onClick: () => updateCounters()}),
                new Container({id: 'counters-help', tag: 'p', html: 'Aggiorna i contatori del database per assicurare la consistenza dei dati.'}),
            )),
        ),


    );

    const logsForm = new Form(
        new Collapsible({title: 'Log Operazioni'},
            new Row(
                new Column({span: 3}, new TextField({name: 'type', label: 'Tipo'})),
                new Column({span: 3}, new TextField({name: 'operation', label: 'Operazione'})),
                new Column({span: 2}, new TextField({name: 'user_id', label: 'User ID'})),
                new Column({span: 2}, new TextField({name: 'operationId', label: 'Operation ID'})),
                new Column({span: 2}, new TextField({name: 'data', label: 'Data'})),
            ),
            new Row(logsGrid),
        ),
    );

    const content = document.getElementById('content');
    content.appendChild(form.render());
    content.appendChild(logsForm.render());

    async function updateCounters() {
        const data = await new Fetcher({url: API_URL()})
            .withQuery('action', 'update_counters')
            .withMethod('GET')
            .onError(() => showError(translate('ERROR')))
            .fetch();
        if (data) showInfo('Operazione eseguita con successo.');
    }

    function syncJobsWorkers() {
        const simulate = !!form.getByName('simulate_jobs_workers').value;
        if (simulate) {
            performJobWorkersSync(true);
        } else {
            showConfirm('Vuoi sincronizzare i lavoratori con le commesse?<br>Non stai simulando',
                () => performJobWorkersSync(false));
        }
    }

    async function performJobWorkersSync(simulate) {
        const data = await new Fetcher({url: API_URL()})
            .withQuery('action', 'jobs_to_workers')
            .withQuery('simulate', simulate ? 'true' : 'false')
            .withMethod('GET')
            .onError(() => showError('Errore nella sincronizzazione.'))
            .fetch();
        if (data) renderJobsWorkersResults(data.items || []);
    }

    function renderJobsWorkersResults(results) {
        const target = form.getById('jobs-workers-results').container;
        if (!results || results.length === 0) {
            target.innerHTML = '<p>Nessun elemento da sincronizzare.</p>';
            return;
        }
        let html = '<table class="data-table"><thead><tr>' +
            '<th>Commessa</th><th>User ID</th><th>Username</th><th>Tariffa</th><th>Tipo</th>' +
            '</tr></thead><tbody>';
        results.forEach(item => {
            html += `<tr>
                <td>${item.commessa ?? ''}</td>
                <td>${item.user_id ?? ''}</td>
                <td>${item.username ?? ''}</td>
                <td>${item.tariffa ?? ''}</td>
                <td>${item.type ?? ''}</td>
            </tr>`;
        });
        html += '</tbody></table>';
        target.innerHTML = html;
    }

    async function showPossibleErrors() {
        const target = form.getById('possible-errors-results').container;
        target.innerHTML = '';
        const data = await new Fetcher({url: API_URL()})
            .withQuery('action', 'show_possible_errors')
            .withMethod('GET')
            .onError(() => showError('Errore nel caricamento.'))
            .fetch();
        if (data) renderPossibleErrors(data);
    }

    function renderPossibleErrors(payload) {
        errorDetailsData = {};
        const target = form.getById('possible-errors-results').container;
        const zero = payload.tariffaOrariaZero || {};
        const jobErrors = payload.erroreCommessa || {};

        let html = '';
        const zeroKeys = Object.getOwnPropertyNames(zero);
        if (zeroKeys.length > 0) {
            html += '<table class="data-table"><thead><tr><th>ID</th><th>Utente a costo zero</th></tr></thead><tbody>';
            zeroKeys.forEach(key => {
                const value = zero[key];
                if (!value.user_id) return;
                errorDetailsData['tariffaOrariaZero.' + key] = value;
                html += `<tr>
                    <td><a href="users_edit.html?id=${value.user_id}">${value.user_id}</a></td>
                    <td>${value.username ?? ''}</td>
                </tr>`;
            });
            html += '</tbody></table>';
        }
        html += '<br>============================<br>';

        const jobIds = [];
        const jobKeys = Object.getOwnPropertyNames(jobErrors).sort((a, b) => b.localeCompare(a));
        if (jobKeys.length > 0) {
            html += '<table class="data-table"><thead><tr><th>Commessa</th><th>Errori</th></tr></thead><tbody>';
            for (const key of jobKeys) {
                const value = jobErrors[key];
                if (!value.errors) continue;
                errorDetailsData['erroreCommessa.' + key] = JSON.stringify(value, null, 2);
                jobIds.push(key);
                html += `<tr>
                    <td><a target="_blank" href="jobs_edit.html?id=${key}">${key}</a></td>
                    <td><ul>`;
                Object.getOwnPropertyNames(value.errors).forEach(skey => {
                    const subErrors = value.errors[skey] || {};
                    html += `<li data-error-key="erroreCommessa.${key}" data-commessa="${key}" class="maintenance-error-item"><u>${skey}</u><ul>`;
                    Object.getOwnPropertyNames(subErrors).forEach(svkey => {
                        if (svkey !== 'errors') html += `<li>${svkey}</li>`;
                    });
                    html += `</ul></li>`;
                });
                html += `</ul></td></tr>`;
            }
            html += '</tbody></table><br>';
        }

        if (html === '<br>============================<br>') {
            target.innerHTML = '<p>Nessun errore trovato. Preoccupati.</p>';
            return;
        }
        target.innerHTML = html;
        localStorage.setItem('jobs.search_results', JSON.stringify(jobIds));

        target.querySelectorAll('.maintenance-error-item').forEach(li => {
            li.addEventListener('click', (e) => {
                e.stopPropagation();
                showErrorDialog(li.getAttribute('data-error-key'), li.getAttribute('data-commessa'));
            });
        });
    }

    function showErrorDialog(id, commessa) {
        const log = errorDetailsData[id];
        const body = document.createElement('div');
        body.innerHTML =
            `<p><strong>Commessa:</strong> ${commessa}</p>` +
            `<p><strong>Data:</strong></p><textarea rows="10" cols="60">${log || 'N/A'}</textarea>`;
        const overlay = document.createElement('div');
        overlay.className = 'dialog-overlay dialog-open';
        const box = document.createElement('div');
        box.className = 'dialog-box dialog-md';
        const header = document.createElement('div');
        header.className = 'dialog-header';
        const title = document.createElement('div');
        title.className = 'dialog-title';
        title.textContent = 'Dettaglio Errore';
        const closeX = document.createElement('button');
        closeX.className = 'dialog-close-btn';
        closeX.innerHTML = '&times;';
        closeX.onclick = () => overlay.remove();
        header.appendChild(title); header.appendChild(closeX);
        const bodyWrap = document.createElement('div');
        bodyWrap.className = 'dialog-body';
        bodyWrap.appendChild(body);
        const footer = document.createElement('div');
        footer.className = 'dialog-footer';
        const okBtn = document.createElement('button');
        okBtn.className = 'notification-dialog-button primary';
        okBtn.textContent = translate('CLOSE') || 'Chiudi';
        okBtn.onclick = () => overlay.remove();
        footer.appendChild(okBtn);
        box.appendChild(header); box.appendChild(bodyWrap); box.appendChild(footer);
        overlay.appendChild(box);
        document.body.appendChild(overlay);
    }

    function confirmTimeSheets() {
        const dateField = form.getByName('confirm_time_sheets_up_to');
        const date = dateField.value;
        if (!date) {
            showError('Selezionare una data.');
            return;
        }
        showConfirm('Vuoi confermare i fogli ore sino al ' + date + '?', async () => {
            const data = await new Fetcher({url: API_URL()})
                .withQuery('action', 'confirm_time_sheets')
                .withQuery('to_date', date)
                .withMethod('GET')
                .onError(() => showError(translate('ERROR')))
                .fetch();
            if (data) showInfo('Operazione eseguita con successo.');
        });
    }

    async function showLogDetail(logId) {
        const data = await new Fetcher({url: API_URL()})
            .withQuery('action', 'show_log_data')
            .withQuery('id', logId)
            .withMethod('GET')
            .onError(() => showError(translate('ERROR')))
            .fetch();
        if (!data) return;
        const log = data.item || {};
        const content = document.createElement('div');
        content.innerHTML =
            `<p><strong>ID:</strong> ${log.id ?? ''}</p>` +
            `<p><strong>User:</strong> ${log.username || 'N/A'}</p>` +
            `<p><strong>User ID:</strong> ${log.user_id || 'N/A'}</p>` +
            `<p><strong>Tipo:</strong> ${log.type || 'N/A'}</p>` +
            `<p><strong>Operazione:</strong> ${log.operation || 'N/A'}</p>` +
            `<p><strong>Operation ID:</strong> ${log.operationId || 'N/A'}</p>` +
            `<p><strong>Timestamp:</strong> ${log.timestamp || log.created_at || 'N/A'}</p>` +
            `<p><strong>Data:</strong></p><textarea rows="10" cols="60">${log.data || 'N/A'}</textarea>`;

        const overlay = document.createElement('div');
        overlay.className = 'dialog-overlay dialog-open';
        const box = document.createElement('div');
        box.className = 'dialog-box dialog-md';
        const header = document.createElement('div');
        header.className = 'dialog-header';
        const title = document.createElement('div');
        title.className = 'dialog-title';
        title.textContent = 'Dettaglio Log';
        const closeX = document.createElement('button');
        closeX.className = 'dialog-close-btn';
        closeX.innerHTML = '&times;';
        closeX.onclick = () => overlay.remove();
        header.appendChild(title); header.appendChild(closeX);
        const bodyWrap = document.createElement('div');
        bodyWrap.className = 'dialog-body';
        bodyWrap.appendChild(content);
        const footer = document.createElement('div');
        footer.className = 'dialog-footer';
        const okBtn = document.createElement('button');
        okBtn.className = 'notification-dialog-button primary';
        okBtn.textContent = translate('CLOSE') || 'Chiudi';
        okBtn.onclick = () => overlay.remove();
        footer.appendChild(okBtn);
        box.appendChild(header); box.appendChild(bodyWrap); box.appendChild(footer);
        overlay.appendChild(box);
        document.body.appendChild(overlay);
    }
}
