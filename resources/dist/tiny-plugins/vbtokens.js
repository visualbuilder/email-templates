/**
 * vbtokens - TinyMCE plugin for visualbuilder/email-templates.
 *
 * Adds:
 * - An "Insert token" toolbar menu listing every token from the
 *   TokenRegistry, grouped by source and sorted alphabetically.
 * - An inline autocompleter: type ## to search and insert tokens.
 * - A "Button" control: dialog for the {{button url='' title=''}} pseudo
 *   token (renders as a table-based, email-client-safe button at send
 *   time). URL can be typed or picked from the token catalogue.
 *   Double-click an existing button chip to edit it.
 * - Badge display: tokens and buttons become non-editable chips that
 *   delete as a single unit; DefaultTokenHelper strips the wrapper at
 *   render time so sent emails contain the plain replaced output. Bare
 *   tokens already in the content (or pasted) are wrapped on load.
 *
 * The token list arrives via the `vbtokens_list` editor option, populated
 * from PHP through the field's custom configs.
 */
tinymce.PluginManager.add('vbtokens', (editor) => {
    editor.options.register('vbtokens_list', {
        processor: 'array',
        default: [],
    });

    const groups = editor.options.get('vbtokens_list') || [];

    const flat = groups.flatMap((group) =>
        group.items.map((item) => ({
            token: item.token,
            label: item.label,
            group: group.label,
        }))
    );

    // ── Plain tokens ────────────────────────────────────────────────

    const badgeHtml = (token) =>
        '<span class="vb-token" contenteditable="false">' + editor.dom.encode(token) + '</span>&nbsp;';

    const insertToken = (token) => {
        editor.insertContent(badgeHtml(token));
        editor.focus();
    };

    editor.ui.registry.addMenuButton('vbtokens', {
        text: 'Insert token',
        tooltip: 'Insert a personalisation token',
        fetch: (callback) => {
            callback(
                groups.map((group) => ({
                    type: 'nestedmenuitem',
                    text: group.label,
                    getSubmenuItems: () =>
                        group.items.map((item) => ({
                            type: 'menuitem',
                            text: item.label,
                            onAction: () => insertToken(item.token),
                        })),
                }))
            );
        },
    });

    editor.ui.registry.addAutocompleter('vbtokens', {
        trigger: '##',
        minChars: 0,
        columns: 1,
        fetch: (pattern) => {
            const needle = pattern.toLowerCase();

            return Promise.resolve(
                flat
                    .filter(
                        (item) =>
                            item.token.toLowerCase().includes(needle) ||
                            item.label.toLowerCase().includes(needle) ||
                            item.group.toLowerCase().includes(needle)
                    )
                    .slice(0, 25)
                    .map((item) => ({
                        type: 'autocompleteitem',
                        value: item.token,
                        text: item.group + ': ' + item.label,
                    }))
            );
        },
        onAction: (api, rng, value) => {
            editor.selection.setRng(rng);
            editor.insertContent(badgeHtml(value));
            api.hide();
            editor.focus();
        },
    });

    // ── Email button pseudo token ───────────────────────────────────

    const buttonPattern = /\{\{button\s+url='([^']*)'\s+title='([^']*)'\s*\}\}/i;

    const buttonChipHtml = (url, title) =>
        '<span class="vb-token vb-button-token" contenteditable="false">' +
        editor.dom.encode("{{button url='" + url + "' title='" + title + "'}}") +
        '</span>&nbsp;';

    const tokenSelectItems = [{ text: 'Type a URL above', value: '' }].concat(
        flat.map((item) => ({ text: item.group + ': ' + item.label, value: item.token }))
    );

    const openButtonDialog = (targetNode = null) => {
        let initial = { title: '', url: '', urlToken: '' };

        if (targetNode) {
            const match = (targetNode.textContent || '').match(buttonPattern);

            if (match) {
                const isToken = flat.some((item) => item.token === match[1]);
                initial = {
                    title: match[2],
                    url: isToken ? '' : match[1],
                    urlToken: isToken ? match[1] : '',
                };
            }
        }

        editor.windowManager.open({
            title: 'Email button',
            body: {
                type: 'panel',
                items: [
                    { type: 'input', name: 'title', label: 'Button text' },
                    { type: 'input', name: 'url', label: 'URL' },
                    {
                        type: 'selectbox',
                        name: 'urlToken',
                        label: 'or use a token as the URL',
                        items: tokenSelectItems,
                    },
                ],
            },
            initialData: initial,
            buttons: [
                { type: 'cancel', text: 'Cancel' },
                { type: 'submit', text: targetNode ? 'Update' : 'Insert', primary: true },
            ],
            onSubmit: (api) => {
                const data = api.getData();
                const url = data.urlToken || data.url;

                if (!url || !data.title) {
                    return;
                }

                const html = buttonChipHtml(url, data.title);

                if (targetNode) {
                    editor.dom.setOuterHTML(targetNode, html);
                } else {
                    editor.insertContent(html);
                }

                api.close();
                editor.focus();
            },
        });
    };

    editor.ui.registry.addButton('vbbutton', {
        text: 'Button',
        tooltip: 'Insert an email-safe button (double-click a button chip to edit it)',
        onAction: () => openButtonDialog(),
    });

    editor.on('dblclick', (event) => {
        const node = event.target.closest && event.target.closest('.vb-button-token');

        if (node) {
            openButtonDialog(node);
        }
    });

    // ── Badges for tokens already in the content ────────────────────
    //
    // Content saved before the badges existed, or typed / pasted as plain
    // text, holds bare ##tokens## and {{button}} tokens. Wrap them in the
    // same chips when content loads or is pasted. Only text nodes are
    // touched, so a token in a link's href stays as it is, and existing
    // chips are skipped, so this is safe to run repeatedly.

    const bareTokenPattern = /##[a-z0-9_.]+##|\{\{button\s+url='[^']*'\s+title='[^']*'\s*\}\}/gi;

    const wrapBareTokens = (root) => {
        const doc = root.ownerDocument;
        const walker = doc.createTreeWalker(root, NodeFilter.SHOW_TEXT, {
            acceptNode: (node) =>
                node.parentElement && node.parentElement.closest('.vb-token, script, style, [contenteditable="false"]')
                    ? NodeFilter.FILTER_REJECT
                    : NodeFilter.FILTER_ACCEPT,
        });
        const targets = [];

        while (walker.nextNode()) {
            bareTokenPattern.lastIndex = 0;

            if (bareTokenPattern.test(walker.currentNode.nodeValue)) {
                targets.push(walker.currentNode);
            }
        }

        targets.forEach((node) => {
            const text = node.nodeValue;
            const fragment = doc.createDocumentFragment();
            let last = 0;

            text.replace(bareTokenPattern, (match, offset) => {
                if (offset > last) {
                    fragment.appendChild(doc.createTextNode(text.slice(last, offset)));
                }

                const chip = doc.createElement('span');
                chip.className = match.startsWith('{{') ? 'vb-token vb-button-token' : 'vb-token';
                chip.setAttribute('contenteditable', 'false');
                chip.textContent = match;
                fragment.appendChild(chip);
                last = offset + match.length;

                return match;
            });

            if (last < text.length) {
                fragment.appendChild(doc.createTextNode(text.slice(last)));
            }

            node.parentNode.replaceChild(fragment, node);
        });
    };

    // Initial load and setContent() calls; insertContent() (selection) is
    // already a chip or goes through PastePostProcess.
    editor.on('SetContent', (event) => {
        if (!event.selection) {
            wrapBareTokens(editor.getBody());
        }
    });

    editor.on('PastePostProcess', (event) => {
        wrapBareTokens(event.node);
    });

    // ── Chip styling inside the editor ──────────────────────────────

    editor.on('init', () => {
        editor.dom.addStyle(
            '.vb-token{display:inline-block;background:#eef2ff;color:#3730a3;border:1px solid #c7d2fe;' +
                'border-radius:9999px;padding:0 .5em;font-size:.85em;font-weight:600;line-height:1.7;' +
                'white-space:nowrap;cursor:default;user-select:all;}' +
                '.vb-button-token{background:#3730a3;color:#eef2ff;border-color:#312e81;cursor:pointer;}'
        );
    });

    return {
        getMetadata: () => ({
            name: 'vbtokens',
            url: 'https://github.com/visualbuilder/email-templates',
        }),
    };
});
