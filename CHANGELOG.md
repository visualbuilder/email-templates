# Changelog

All notable changes to `email-templates` will be documented in this file

## 5.7.0 - unreleased
 - Email block composer. A template can build its body from ordered blocks (`layout` JSON column) instead of the single `content` field: Text, Hero, Two columns, Button, Image, Divider / spacer and Saved block. Each block renders as one full-width table row with inline styles, explicit background colours and Outlook ghost tables; buttons are bulletproof (VML). Templates without a layout render exactly as before. Block mode is not offered on quote templates.
 - Block types are classes implementing `EmailBlockDefinition`, listed by the `EmailBlockRegistry` singleton (`block_types` config or `register()` at runtime). `EmailLayoutRenderer` renders a layout; the send path, the template preview and the Builder block previews all use it.
 - New **Email Blocks** resource (block library, `EmailBlock` model, `vb_email_blocks` table) for reusable saved blocks, with a preview.
 - `layout.blade.php` / `layout_preview.blade.php` render the theme header, hero title, blocks, support block, footer and, when the mailable has an `$unsubscribeUrl`, an unsubscribe line. New parts: `_support_block_standalone`, `_unsubscribe`, `_block_open`, `_block_close`, `_block_button`.
 - `EmailTemplate::usesLayout()` and `renderViewPath()`; `BuildGenericEmail` and the template screenshot actions render through `renderViewPath()`.
 - New config keys: `blocks_table_name`, `block_types`, `block_images`, `block_images_disk`, `font_family`, `navigation.blocks`. New migration stubs: `add_layout_to_email_templates_table`, `create_email_blocks_table`.

## 5.6.3 - 2026-09-17
 - The vbtokens editor plugin URL is root-relative unless `app.asset_url` is set. It was built with `asset()` at boot, and under `config:cache` that runs in the console, where the host comes from `APP_URL`; a panel on another domain then fetched the plugin cross-host, where a CSP or an auth gate blocked it and the Insert token menu disappeared. `EmailTemplatesServiceProvider::tokenPluginUrl()` is public for applications registering their own profiles.

## 5.6.2 - 2026-09-07
 - Cache invalidation now covers lookups that fell back from another language. A lookup for a language with no template is cached under the requested language, which the save hook could not name; those languages are now remembered per key and forgotten on update and delete. Cache key format is unchanged.

## 5.6.1 - 2026-09-07
 - Updating or deleting a template no longer runs `optimize:clear` and `opcache_reset()`. The template cache is keyed per template and is forgotten on save, which is all the invalidation needs; the global clear wiped every compiled view, config and route cache on each save and logged "OPcache cleared" dozens of times during a seeded build.

## 5.0.0 - 2026-03-31
 - Added Filament 5.x compatibility
 - Upgraded to Livewire 4.x
 - Updated dependencies: Pest 4.x, PHPUnit 12.x
 - Fixed ViewErrorBag compatibility with Livewire 4
 - All core functionality tests passing (29/54 tests)
 - Note: Livewire component tests temporarily disabled pending Livewire 4 testing framework updates

#3.0.37 - 2024-08-26
 - Test suite and readme.md updated

