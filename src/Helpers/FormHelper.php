<?php

namespace Visualbuilder\EmailTemplates\Helpers;

use Visualbuilder\EmailTemplates\Contracts\FormHelperInterface;

class FormHelper implements FormHelperInterface
{
    public function getLanguageOptions()
    {
        return collect(config('email-templates.languages'))->mapWithKeys(function ($langVal, $langKey) {
            return [
                $langKey => '<span class="flag-icon flag-icon-'.$langVal["flag-icon"].'"></span> '.$langVal["display"],
            ];
        })->toArray();
    }

    public function getTemplateViewOptions()
    {
        $overrideDirectory = resource_path('views/vendor/vb-email-templates/email');
        $packageDirectory = dirname(view(config('email-templates.template_view_path').'.default')->getPath());

        $directories = [$overrideDirectory, $packageDirectory];

        $filenamesArray = collect($directories)
            ->filter(function ($directory) {
                return file_exists($directory);
            })
            ->flatMap(function ($directory) {
                return self::getFiles($directory, $directory);
            })
            ->unique()
            ->values()
            ->toArray();

        return array_combine($filenamesArray, $filenamesArray);
    }

    /**
     * Recursively get all files in a directory and children
     */
    private static function getFiles($dir, $basepath)
    {
        $files = $subdirs = $subFiles = [];

        if ($handle = opendir($dir)) {
            while (false !== ($entry = readdir($handle))) {
                if ($entry == "." || $entry == "..") {
                    continue;
                }
                if (substr($entry, 0, 1) == '_') {
                    continue;
                }
                $entryPath = $dir.'/'.$entry;
                if (is_dir($entryPath)) {
                    $subdirs[] = $entryPath;
                } else {
                    $subFiles[] = str_replace(
                        '/',
                        '.',
                        str_replace(
                            '.blade.php',
                            '',
                            str_replace(
                                $basepath.'/',
                                '',
                                $entryPath
                            )
                        )
                    );
                }
            }
            closedir($handle);
            sort($subFiles);
            $files = array_merge($files, $subFiles);
            foreach ($subdirs as $subdir) {
                $files = array_merge($files, self::getFiles($subdir, $basepath));
            }
        }

        return $files;
    }

    public function getRecipientOptions()
    {
        return collect(config('email-templates.recipients'))->mapWithKeys(function ($recipient) {
            $splitNamespace = explode('\\', $recipient);
            $className = end($splitNamespace); // Get the class name without namespace

            return [$className => $recipient]; // Use class name as key and full class name as value
        })->toArray();
    }
}
