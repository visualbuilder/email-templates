<?php

use Illuminate\Support\Facades\File;
use Visualbuilder\EmailTemplates\Helpers\FormHelper;

it('recursively collects blade views and ignores underscore directories', function () {
    $baseDir = base_path('tests/tmp/views');

    File::deleteDirectory($baseDir);

    // create nested directories
    File::ensureDirectoryExists($baseDir.'/sub/nested', 0755, true);
    File::ensureDirectoryExists($baseDir.'/_ignored');
    File::ensureDirectoryExists($baseDir.'/sub/_private');

    // create files
    File::put($baseDir.'/welcome.blade.php', '');
    File::put($baseDir.'/_hidden.blade.php', '');
    File::put($baseDir.'/sub/alpha.blade.php', '');
    File::put($baseDir.'/sub/nested/detail.blade.php', '');
    File::put($baseDir.'/_ignored/hidden.blade.php', '');
    File::put($baseDir.'/sub/_private/secret.blade.php', '');

    // access private method
    $method = new ReflectionMethod(FormHelper::class, 'getFiles');
    $method->setAccessible(true);
    $files = $method->invoke(null, $baseDir, $baseDir);

    expect($files)->toEqualCanonicalizing([
        'welcome',
        'sub.alpha',
        'sub.nested.detail',
    ]);

    File::deleteDirectory($baseDir);
});

