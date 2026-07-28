import Alpine from 'alpinejs';
import Chart from 'chart.js/auto';

window.Alpine = Alpine;
window.Chart = Chart;

Alpine.start();

const TABLE_ACTION_ICONS = {
    view: `
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M2.75 12s3.25-6 9.25-6 9.25 6 9.25 6-3.25 6-9.25 6S2.75 12 2.75 12Z" />
            <circle cx="12" cy="12" r="2.75" />
        </svg>`,
    edit: `
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M14.75 5.25 18.75 9.25M4.5 19.5l4.75-1 9.5-9.5a2.83 2.83 0 0 0-4-4l-9.5 9.5-1 4.75Z" />
        </svg>`,
    delete: `
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 7.25h15M9.25 10.25v6.5M14.75 10.25v6.5M6.75 7.25l.75 12h9l.75-12M9 7.25V4.75h6v2.5" />
        </svg>`,
    print: `
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M7 8V3.75h10V8M7 16.25H5.25A2.25 2.25 0 0 1 3 14v-3.75A2.25 2.25 0 0 1 5.25 8h13.5A2.25 2.25 0 0 1 21 10.25V14a2.25 2.25 0 0 1-2.25 2.25H17M7 13.25h10v7H7z" />
            <path stroke-linecap="round" d="M17.5 11h.01" />
        </svg>`,
    download: `
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3.5v11M7.75 10.5 12 14.75l4.25-4.25M4.5 19.5h15" />
        </svg>`,
    upload: `
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 20.5v-11M7.75 13.5 12 9.25l4.25 4.25M4.5 4.5h15" />
        </svg>`,
    approve: `
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="m5 12.5 4.25 4.25L19 7" />
        </svg>`,
    reject: `
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" d="m6 6 12 12M18 6 6 18" />
        </svg>`,
    add: `
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" d="M12 5v14M5 12h14" />
        </svg>`,
    receive: `
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4 5.5h16v13H4zM8 9.25h8M12 5.5v8.25M8.75 11.5 12 14.75l3.25-3.25" />
        </svg>`,
    distribute: `
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3.75 4.5 7.5 12 11.25l7.5-3.75L12 3.75ZM4.5 12l7.5 3.75L19.5 12M4.5 16.5 12 20.25l7.5-3.75" />
        </svg>`,
    history: `
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4.75 8.25H1.5v-3.5M2 8a10 10 0 1 1-.1 7.75M12 6.75V12l3.5 2" />
        </svg>`,
    refresh: `
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19.25 8.25V4.75h-3.5M18.75 5.25A8.25 8.25 0 1 0 20 14" />
        </svg>`,
    settings: `
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" aria-hidden="true">
            <circle cx="12" cy="12" r="3" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M19.25 13.25v-2.5l-2.05-.55a7.05 7.05 0 0 0-.65-1.55l1.06-1.84-1.77-1.77L14 6.1a7.05 7.05 0 0 0-1.55-.65L11.9 3.4H9.4l-.55 2.05a7.05 7.05 0 0 0-1.55.65L5.46 5.04 3.69 6.81l1.06 1.84a7.05 7.05 0 0 0-.65 1.55l-2.05.55v2.5l2.05.55c.15.55.37 1.07.65 1.55l-1.06 1.84 1.77 1.77L7.3 17.9c.48.28 1 .5 1.55.65l.55 2.05h2.5l.55-2.05a7.05 7.05 0 0 0 1.55-.65l1.84 1.06 1.77-1.77-1.06-1.84c.28-.48.5-1 .65-1.55l2.05-.55Z" />
        </svg>`,
    generic: `
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" aria-hidden="true">
            <circle cx="12" cy="12" r="1" fill="currentColor" stroke="none" />
            <circle cx="5.5" cy="12" r="1" fill="currentColor" stroke="none" />
            <circle cx="18.5" cy="12" r="1" fill="currentColor" stroke="none" />
        </svg>`,
};

const TABLE_ACTION_RULES = [
    { type: 'delete', pattern: /\b(delete|remove|archive|void)\b/i },
    { type: 'reject', pattern: /\b(reject|decline|cancel|return|unavailable|deactivate|disable)\b/i },
    { type: 'approve', pattern: /\b(approve|assign|grant|confirm|complete|submit|save|available|activate|enable)\b/i },
    { type: 'edit', pattern: /\b(edit|update|revise|modify)\b/i },
    { type: 'print', pattern: /\b(print)\b/i },
    { type: 'download', pattern: /\b(download|export|pdf)\b/i },
    { type: 'upload', pattern: /\b(upload|attach)\b/i },
    { type: 'receive', pattern: /\b(receive|accept delivery)\b/i },
    { type: 'distribute', pattern: /\b(designate|distribute|allocate|issue)\b/i },
    { type: 'history', pattern: /\b(history|ledger|audit)\b/i },
    { type: 'refresh', pattern: /\b(resubmit|retry|restore|reopen|clear filters?)\b/i },
    { type: 'settings', pattern: /\b(settings?|configure|configuration)\b/i },
    { type: 'add', pattern: /\b(add|create|new)\b/i },
    { type: 'view', pattern: /\b(view|show|open|details|preview|inspect|review)\b/i },
];

function cleanActionLabel(value) {
    return String(value ?? '')
        .replace(/\s+/g, ' ')
        .replace(/[→›»]+$/g, '')
        .trim();
}

function resolveActionType(label, element) {
    const searchable = [
        label,
        element.getAttribute('title'),
        element.getAttribute('aria-label'),
        element.getAttribute('href'),
        element.getAttribute('data-action'),
    ]
        .filter(Boolean)
        .join(' ');

    return TABLE_ACTION_RULES.find((rule) => rule.pattern.test(searchable))?.type ?? null;
}


function decorateTableAction(element) {
    if (!(element instanceof HTMLElement)) {
        return;
    }

    if (
        element.dataset.tableActionReady === 'true' ||
        element.matches('[data-table-action-ignore]') ||
        element.closest('[data-table-action-ignore]')
    ) {
        return;
    }

    const explicitLabel = cleanActionLabel(element.getAttribute('data-action-label'));
    const accessibleLabel = cleanActionLabel(element.getAttribute('aria-label'));
    const titleLabel = cleanActionLabel(element.getAttribute('title'));
    const visibleLabel = cleanActionLabel(element.textContent);
    const label = explicitLabel || accessibleLabel || titleLabel || visibleLabel;

    if (!label) {
        return;
    }

    const actionType = resolveActionType(label, element);

    // Only convert controls that clearly represent an action. This avoids
    // changing ordinary data links inside table cells.
    if (!actionType) {
        return;
    }

    element.dataset.tableActionReady = 'true';
    element.dataset.tableActionType = actionType;
    element.classList.add('table-action-control');
    element.setAttribute('aria-label', label);
    element.setAttribute('title', label);

    element.innerHTML = `
        <span class="table-action-icon" aria-hidden="true">
            ${TABLE_ACTION_ICONS[actionType] ?? TABLE_ACTION_ICONS.generic}
        </span>
    `;
}

function decorateTableActions(root = document) {
    if (!(root instanceof Document || root instanceof DocumentFragment || root instanceof HTMLElement)) {
        return;
    }

    if (root instanceof HTMLElement && root.matches('table td a, table td button')) {
        decorateTableAction(root);
    }

    root.querySelectorAll?.('table td a, table td button').forEach(decorateTableAction);
}

function initializeTableActions() {
    decorateTableActions(document);

    const observer = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
            mutation.addedNodes.forEach((node) => {
                if (node instanceof HTMLElement || node instanceof DocumentFragment) {
                    decorateTableActions(node);
                }
            });
        });
    });

    observer.observe(document.body, {
        childList: true,
        subtree: true,
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeTableActions, { once: true });
} else {
    initializeTableActions();
}
