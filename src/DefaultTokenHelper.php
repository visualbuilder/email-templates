<?php

namespace Visualbuilder\EmailTemplates;


use Illuminate\Support\Facades\View;
use Visualbuilder\EmailTemplates\Contracts\TokenReplacementInterface;
use Visualbuilder\EmailTemplates\Models\EmailTemplate;


class DefaultTokenHelper implements TokenReplacementInterface
{
    /**
     * Replace tokens in the content with actual values from the models.
     *
     * @param  string  $content  The content with tokens to be replaced
     * @param  array  $models  The models containing the values for the tokens
     *
     * @return string The content with replaced tokens
     */
    public function replaceTokens(string $content, $models): string
    {
        $content = $this->stripTokenBadges($content);

        $content = $this->replaceSingularTokens($models, $content);

        $content = $this->replaceConfigTokens($models,$content);

        $content = $this->replaceModelTokens($models, $content);

        return $this->replaceButtonTokens($models, $content);
    }



    /**
     * The editor's Insert Token control wraps tokens in a non-editable
     * badge span for display. Unwrap them before replacement so sent
     * emails contain plain replaced text, never the badge markup.
     */
    protected function stripTokenBadges(string $content): string
    {
        return preg_replace(
            '/<span[^>]*\bvb-token\b[^>]*>(.*?)<\/span>/is',
            '$1',
            $content
        ) ?? $content;
    }

    /**
     *
     * @return string
     */
    protected function replaceSingularTokens( $models, string $content): string
    {
        /**
         * Replace singular tokens for password reset and validations
         * Add custom tokens in the config
         */
        foreach (config('filament-email-templates.known_tokens') as $key) {
            if (isset($models->{$key})) {
                $content = str_replace("##$key##", $models->{$key}, $content);
            }
        }
        return $content;
    }

    protected function replaceConfigTokens( $models, string $content): string
    {
        /**
         * Replace config tokens.
         *
         * Define which tokens are allowed in this config setting
         */
        $allowedConfigKeys = config('filament-email-templates.config_keys');

        preg_match_all('/##config\.(.*?)##/', $content, $matches);
        if (count($matches) > 0 && count($matches[0]) > 0) {
            for ($i = 0; $i < count($matches[0]); $i++) {
                $configKey = $matches[1][$i];
                if (in_array($configKey, $allowedConfigKeys)) {
                    $configValue = config($configKey);
                    if ($configValue !== null) {
                        $content = str_replace($matches[0][$i], $configValue, $content);
                    }
                }
            }
        }

        return $content;
    }

    protected function replaceModelTokens( $models, string $content): string
    {
        /**
         * Replace model-attribute tokens.
         * Will look for pattern ##model.attribute## and replace the value if found.
         * Eg ##user.name## or create your own accessors in a model
         */
        preg_match_all('/##(?!config\.)([^.#]+)\.(.*?)##/', $content, $matches);

        if (count($matches) > 0 && count($matches[0]) > 0) {
            for ($i = 0; $i < count($matches[0]); $i++) {
                $modelKey = $matches[1][$i];
                $attributeKey = $matches[2][$i];
                $replacement = (isset($models->$modelKey) && isset($models->$modelKey->$attributeKey))?$models->$modelKey->$attributeKey:"";
                $content = str_replace($matches[0][$i], $replacement, $content);

            }
        }
        return $content;
    }

    private function buildEmailButton($content, $emailTemplate): string
    {
        $content = str_replace('&#039;', "'", $content);

        $title = $url = '';
        if (preg_match('/\{\{button.*?\}\}/', $content, $matches)) {
            if ($check1 = preg_match("/(?<=url=').*?(?='\s)/", $matches[ 0 ], $url)) {
                $url = $url[ 0 ];
            }
            if ($check2 = preg_match("/(?<=title=').*?(?=')/", $matches[ 0 ], $title)) {
                $title = $title[ 0 ];
            }
            if ($check1 && $check2) {

                return View::make('vb-email-templates::email.parts._button', [
                        'url' => $url,
                        'title' => $title,
                        'data' => ['theme' => $emailTemplate->theme->colours],
                ])
                        ->render();
            }
        };

        return '';
    }

    /**
     * @param  array|string  $content
     *
     * @return string
     */
    protected function replaceButtonTokens( $models, string $content): string
    {
        /**
         *Replace {{button url='xxx' title='xxx'}}
         */

        if (isset($models->emailTemplate)) {
            $button = $this->buildEmailButton($content, $models->emailTemplate);
            $content = self::replaceButtonToken($content, $button);
        }

        return $content;
    }


    private static function replaceButtonToken($content, $button)
    {
        // Search pattern to find the new button format
        $search = "/\{\{button.*?\}\}/";
        // Replace the found button token with the actual button HTML
        $content = preg_replace($search, $button, $content);

        return $content;
    }

}
