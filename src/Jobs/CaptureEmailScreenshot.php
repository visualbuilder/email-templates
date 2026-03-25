<?php

declare(strict_types=1);

namespace Visualbuilder\EmailTemplates\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Spatie\MediaLibrary\HasMedia;
use Visualbuilder\EmailTemplates\EmailTemplatesPlugin;

class CaptureEmailScreenshot implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 2;

    public int $timeout = 120;

    public function __construct(
        public HasMedia $record,
        public string $html,
    ) {}

    public function handle(): void
    {
        try {
            $callback = EmailTemplatesPlugin::get()->getScreenshotCaptureCallback();

            if (! $callback) {
                Log::warning('CaptureEmailScreenshot: No screenshot capture callback configured');
                return;
            }

            $result = $callback($this->html);

            if (! $result || ! isset($result['image'])) {
                Log::warning('CaptureEmailScreenshot: Capture failed', [
                    'record_id' => $this->record->id,
                    'record_type' => get_class($this->record),
                ]);
                return;
            }

            $extension = str_contains($result['contentType'] ?? '', 'jpeg') ? 'jpg' : 'png';
            $tempPath = tempnam(sys_get_temp_dir(), 'email_screenshot_') . '.' . $extension;
            file_put_contents($tempPath, $result['image']);

            $this->record->addMedia($tempPath)
                ->toMediaCollection('screenshot');

        } catch (\Throwable $e) {
            Log::error('CaptureEmailScreenshot: Exception', [
                'record_id' => $this->record->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
