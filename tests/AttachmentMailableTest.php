<?php

use Illuminate\Mail\Mailable;
use Illuminate\Http\UploadedFile;
use Visualbuilder\EmailTemplates\Models\EmailTemplate;
use Visualbuilder\EmailTemplates\Tests\Models\User;
use Visualbuilder\EmailTemplates\Traits\BuildGenericEmail;

it('attaches a file when building the mailable', function () {
    EmailTemplate::factory()->create([
        'key' => 'attachment-test',
        'name' => 'Attachment Test',
        'title' => 'Attachment Test',
        'subject' => 'Attachment Test',
        'preheader' => '',
        'content' => '<p>Attachment Test</p>',
    ]);

    $this->makeTheme();
    $user = User::factory()->create();

    $path = tempnam(sys_get_temp_dir(), 'att');
    file_put_contents($path, 'dummy');

    $uploadedFile = new class($path, 'attachment.txt', 'text/plain', null, true) extends UploadedFile {
        public $filename = 'attachment.txt';
        public $mime_type = 'text/plain';
        public function getPath()
        {
            return $this->getPathname();
        }
    };

    $mailable = new class($user, $uploadedFile) extends Mailable {
        use BuildGenericEmail;

        public $template = 'attachment-test';
        public $sendTo;
        public $user;
        public $attachment;

        public function __construct($user, $attachment)
        {
            $this->user = $user;
            $this->sendTo = $user->email;
            $this->attachment = $attachment;
        }
    };

    $mailable->build();

    $mailable->assertHasAttachment($uploadedFile->getPath(), [
        'as' => $uploadedFile->filename,
        'mime' => $uploadedFile->mime_type,
    ]);
});
