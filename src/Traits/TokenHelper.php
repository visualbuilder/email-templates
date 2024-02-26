<?php

namespace Visualbuilder\EmailTemplates\Traits;

use App\Models\EndUser;
use Illuminate\Support\Facades\View;

trait TokenHelper
{
    public function replaceTokens($content, $models)
    {
        // Replace singular tokens.
        // These are for password reset and email verification

        if (isset($models->tokenUrl)) {
            $content = str_replace('##tokenURL##', $models->tokenUrl, $content);
        }

        if (isset($models->verificationUrl)) {
            $content = str_replace('##verificationUrl##', $models->verificationUrl, $content);
        }

        // Replace model-attribute tokens.
        // Will look for pattern ##model.attribute## and replace the value if found.
        // Eg ##user.name##
        preg_match_all('/##(.*?)\.(.*?)##/', $content, $matches);

        if (count($matches) > 0 && count($matches[0]) > 0) {
            for ($i = 0; $i < count($matches[0]); $i++) {
                $modelKey = $matches[1][$i];
                $attributeKey = $matches[2][$i];

                if (isset($models->$modelKey) && isset($models->$modelKey->$attributeKey)) {
                    $content = str_replace($matches[0][$i], $models->$modelKey->$attributeKey, $content);
                }
            }
        }

        //Insert user-consent
        if(isset($models->consentOptions)){
            $content =  str_replace('##consent-options##',view('vendor.user-consent.mails.accept-notification', ['consentOptions' => $models->consentOptions])->render(),$content);


            $coachingContract = $models->consentOptions->filter(function ($option) {
                return $option->key == 'coaching-contract';
            });
            if($coachingContract && $models->user instanceof EndUser)
            {
                if($order = $models->user->latestOrderWithCategory('Contract'))
                {
                    $totalCoachingHours = $order->total_coaching_duration;
                    $expectedWeeks = estimateCoachingWeeks($totalCoachingHours);
                    $content  = str_replace('{{ total_hours_coaching }}', $totalCoachingHours,$content);
                    $content  = str_replace('{{ expected_weeks }}', $expectedMonths,$content);
                }


            }

        }



        // Replace config tokens.
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

        $button = $this->buildEmailButton($content);
        $content = self::replaceButtonToken($content, $button);

        return $content;
    }

    public function buildEmailButton($content)
    {
        $title = $url = '';
        if (preg_match('/(?<=##button).*?(?=#)/', $content, $matches)) {
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
                    'data' => ['theme' => $this->theme->colours],
                ])
                           ->render();
            }
        };

        return '';
    }

    public static function replaceButtonToken($content, $button)
    {
        $search = "/(?<=##button).*?(?=##)/";
        $replace = "";
        $content = preg_replace($search, $replace, $content);
        $content = str_replace('##button##', $button, $content);

        return $content;
    }
}
