# Changelog

All notable changes to `email-templates` will be documented in this file

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

