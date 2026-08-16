<?php

use Filament\Pages\Enums\SubNavigationPosition;
use Visualbuilder\EmailTemplates\DefaultTokenHelper;

return [
    /**
     * If you wish to customise the table name change this before migration
     */
    'table_name' => 'vb_email_templates',
    'theme_table_name' => 'vb_email_templates_themes',

    /**
     * Flag-icon stylesheet
     *
     * The language picker uses `.flag-icon` classes (flag-icon-css). By default
     * the package loads the stylesheet from a CDN. If you self-host flag icons,
     * or have a strict Content-Security-Policy that disallows the CDN, set this
     * to null/false to stop the package injecting it (and load your own), or
     * point it at your own URL.
     */
    'flag_icon_stylesheet' => 'https://cdnjs.cloudflare.com/ajax/libs/flag-icon-css/3.5.0/css/flag-icon.min.css',

    /**
     * Multitenancy Support
     *
     * Enable this to allow tenants to have their own email templates
     * while falling back to global (system) templates.
     *
     * tenant_model:              The tenant model class (e.g. App\Models\Team::class)
     *                            If null, uses Filament::getTenantModel()
     * tenant_foreign_key:        Column name on the templates/themes tables (e.g. 'team_id')
     *                            If null, derived from the tenant model class name
     * ownership_relationship:    Relationship name on EmailTemplate/Theme models (e.g. 'team')
     *                            If null, derived from the tenant model class name
     */
    'multitenancy' => [
        'enabled' => false,
        'tenant_model' => null,
        'tenant_foreign_key' => null,
        'ownership_relationship' => null,
    ],


    /**
     * Mail Classes will be generated into this directory
     */
    "mailable_directory" => 'Mail/Visualbuilder/EmailTemplates',

    /**
     * If you want to use your own token helper replace this class
     *  Eg create a file like this:-
     *
     *  namespace App\Helpers
     *
     *  use Visualbuilder\EmailTemplates\Contracts\TokenReplacementInterface;
     *
     *  class MyTokenHelper implements TokenReplacementInterface
     *  {
     *      public function replaceTokens($content, $models)
     *          {
     *           // First, call the parent method if you want to retain and build upon its functionality
     *              $content = parent::replaceTokens($content, $models);
     *      }
     *  }
     */

    'tokenHelperClass' => DefaultTokenHelper::class,


    /**
     * Some tokens don't belong to a model.  These $models->token will be checked
     */
    'known_tokens' => [
        'tokenUrl',
        'verificationUrl',
        'message'
    ],

    /**
     * Admin panel navigation options
     */
    'navigation' => [
        'enabled' => true,
        'templates' => [
            'sort' => 10,
            'label' => 'Email Templates',
            'icon' => 'heroicon-o-envelope',
            'group' => 'Content',
            'cluster' => false,
            'position' => SubNavigationPosition::Top
        ],
        'themes' => [
            'sort' => 20,
            'label' => 'Email Template Themes',
            'icon' => 'heroicon-o-paint-brush',
            'group' => 'Content',
            'cluster' => false,
            'position' => SubNavigationPosition::Top
        ],
    ],

    //Email templates will be copied to resources/views/vendor/vb-email-templates/email
    //default.blade.php is base view that can be customised below
    'default_view' => 'default',

    'template_view_path' => 'vb-email-templates::email',

    'template_keys' => [
        'user-welcome' => 'User Welcome Email',
        'user-request-reset' => 'User Request Password Reset',
        'user-password-reset-success' => 'User Password Reset',
        'user-locked-out' => 'User Account Locked Out',
        'user-verify-email' => 'User Verify Email',
        'user-verified' => 'User Verified',
        'user-login' => 'User Logged In',
    ],

    //Default Logo
    'logo' => 'media/email-templates/logo.png',

    //Browsed Logo
    'browsed_logo' => 'media/email-templates/logos',

    //Logo size in pixels -> 200 pixels high is plenty big enough.
    'logo_width' => '500',
    'logo_height' => '126',

    //Content Width in Pixels
    'content_width' => '600',

    //Contact details included in default email templates
    'customer-services' => [
        'email' => 'support@yourcompany.com',
        'phone' => '+441273 455702'
    ],

    //Footer Links
    'links' => [
        ['name' => 'Website', 'url' => 'https://yourwebsite.com', 'title' => 'Goto website'],
        [
            'name' => 'Privacy Policy', 'url' => 'https://yourwebsite.com/privacy-policy',
            'title' => 'View Privacy Policy'
        ],
    ],

    //Options for alternative languages
    //Note that Laravel default locale is just 'en' you can use this but
    //we are being more specific to cater for English vs USA languages
    'default_locale' => 'en_GB',

    //These will be included in the language picker when editing an email template
    'languages' => [
        'en_GB' => ['display' => 'British', 'flag-icon' => 'gb'],
        'en_US' => ['display' => 'USA', 'flag-icon' => 'us'],
        'es' => ['display' => 'Español', 'flag-icon' => 'es'],
        'fr' => ['display' => 'Français', 'flag-icon' => 'fr'],
        'pt' => ['display' => 'Brasileiro', 'flag-icon' => 'br'],
        'in' => ['display' => 'Hindi', 'flag-icon' => 'in'],
    ],

    //Notifiable Models who can receive emails
    'recipients' => [
        App\Models\User::class,
    ],

    /**
     * Allowed config keys which can be inserted into email templates
     * eg use ##config.app.name## in the email template for automatic replacement.
     */
    'config_keys' => [
        'app.name',
        'app.url',
        'email-templates.customer-services'
        // Add other safe config keys here.
        // We don't want to allow all config keys they may contain secret keys or credentials
    ],

    //Most built-in emails can be automatically sent with minimal setup,
    //except "request password reset" requires a function in the User's model.  See readme.md for details
    /**
     * Map token prefixes to model classes for email preview.
     * The first record of each model will be loaded for token replacement.
     * e.g. 'endUser' => \App\Models\EndUser::class,
     */
    'preview_models' => [],

    /**
     * Extra models to expose in the token catalogue (TokenRegistry) that
     * powers the editor's Insert Token control. Recipients and
     * preview_models are included automatically; use this for anything else.
     * Attributes default to the model's fillable + appended attributes
     * (minus hidden) — pass an explicit list to override.
     *
     * e.g. 'order' => \App\Models\Order::class,
     *      'subscriber' => ['class' => \App\Models\Subscriber::class, 'attributes' => ['first_name', 'last_name'], 'label' => 'Subscriber'],
     */
    'token_models' => [],

    /**
     * Attributes never exposed in the token catalogue when deriving a
     * model's attribute list, even if a model forgets to $hidden them.
     * Shell wildcards are supported, e.g. '*_id' hides every foreign key.
     * Explicit attribute lists (above) bypass this safety net.
     */
    'token_excluded_attributes' => [
        'password',
        'password_confirmation',
        'remember_token',
        'api_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ],

    /**
     * Static overrides for computed/URL tokens that can't come from a model.
     * Applied before model-based token replacement in previews.
     * e.g. 'order.edit_url' => 'https://example.com/orders/1/edit',
     */
    'preview_data' => [],

    'send_emails' => [
        'new_user_registered' => true,
        'verification' => true,
        'user_verified' => true,
        'login' => true,
        'password_reset_success' => true,
        'locked_out' => true,
    ],

];
