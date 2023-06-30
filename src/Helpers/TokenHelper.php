<?php

namespace Visualbuilder\EmailTemplates\Helpers;

use Visualbuilder\EmailTemplates\Contracts\TokenHelperInterface;

class TokenHelper implements TokenHelperInterface
{
    public function replaceTokens($content, $model)
    {
        // Replace singular tokens.
        // These are for password reset and email verification
        if (isset($model->tokenUrl)) {
            $content = str_replace('##tokenURL##', $model->tokenUrl, $content);
        }
        
        if (isset($model->verificationUrl)) {
            $content = str_replace('##verificationUrl##', $model->verificationUrl, $content);
        }
        
        // Replace model-attribute tokens.
        // Will look for pattern ##model.attribute## and replace the value if found.
        // Eg ##user.firstname##
        preg_match_all('/##(.*?)\.(.*?)##/', $content, $matches);
        
        if (count($matches) > 0 && count($matches[0]) > 0) {
            for ($i = 0; $i < count($matches[0]); $i++) {
                $modelKey = $matches[1][$i];
                $attributeKey = $matches[2][$i];
                
                if (isset($model->$modelKey) && isset($model->$modelKey->$attributeKey)) {
                    $content = str_replace($matches[0][$i], $model->$modelKey->$attributeKey, $content);
                }
            }
        }
        
        // Replace config tokens.
        $allowedConfigKeys = config('email-templates.config-keys');
        
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
}