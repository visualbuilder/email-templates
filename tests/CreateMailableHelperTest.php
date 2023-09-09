<?php

// tests/Unit/CreateMailableHelperTest.php

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Visualbuilder\EmailTemplates\Helpers\CreateMailableHelper;
use Visualbuilder\EmailTemplates\Models\EmailTemplate;

it('creates a mailable class if it does not exist', function () {
    // Given: An email template record
    $record = EmailTemplate::factory()->create(['key' => 'test-email']);

    // And: The mailable class does not exist
    $className = Str::studly($record->key);
    $filePath = app_path(config('email-templates.mailable_directory')."/{$className}.php");
    if (file_exists($filePath)) {
        unlink($filePath);
    }

    // When: We run the createMailable method
    $helper = new CreateMailableHelper();
    $response = $helper->createMailable($record);

    // Then: The mailable class should be created
    expect(file_exists($filePath))->toBeTrue();
    expect($response->title)->toBe("Class generated successfully");
    expect($response->icon)->toBe("heroicon-o-check-circle");
    expect($response->icon_color)->toBe("success");
});

it('returns an error if the mailable class already exists', function () {
    // Given: An email template record
    $record = EmailTemplate::factory()->create(['key' => 'test-email']);

    // And: The mailable class already exists
    $className = Str::studly($record->key);
    $filePath = app_path(config('email-templates.mailable_directory')."/{$className}.php");
    File::put($filePath, '');

    // When: We run the createMailable method
    $helper = new CreateMailableHelper();
    $response = $helper->createMailable($record);

    // Then: It should return an error response
    expect($response->icon)->toBe("heroicon-o-exclamation-circle");
    expect($response->icon_color)->toBe("danger");
});
