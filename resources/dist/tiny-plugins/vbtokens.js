/**
 * vbtokens - TinyMCE plugin for visualbuilder/email-templates.
 *
 * Adds:
 * - An "Insert token" toolbar menu listing every token from the
 *   TokenRegistry, grouped by source and sorted alphabetically.
 * - An inline autocompleter: type ## to search and insert tokens.
 * - Badge display: inserted tokens become non-editable chips that delete
 *   as a single unit; DefaultTokenHelper strips the wrapper at render
 *   time so sent emails contain plain replaced text.
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

    const flat = groups.flatMap((group) =>
        group.items.map((item) => ({
            token: item.token,
            label: item.label,
            group: group.label,
        }))
    );

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

    editor.on('init', () => {
        editor.dom.addStyle(
            '.vb-token{display:inline-block;background:#eef2ff;color:#3730a3;border:1px solid #c7d2fe;' +
                'border-radius:9999px;padding:0 .5em;font-size:.85em;font-weight:600;line-height:1.7;' +
                'white-space:nowrap;cursor:default;user-select:all;}'
        );
    });

    return {
        getMetadata: () => ({
            name: 'vbtokens',
            url: 'https://github.com/visualbuilder/email-templates',
        }),
    };
});
