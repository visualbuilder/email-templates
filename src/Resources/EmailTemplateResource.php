<?php

namespace Visualbuilder\EmailTemplates\Resources;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Unique;
use Illuminate\View\View;
use Visualbuilder\EmailTemplates\Contracts\CreateMailableInterface;
use Visualbuilder\EmailTemplates\Contracts\FormHelperInterface;
use Visualbuilder\EmailTemplates\EmailTemplatesPlugin;
use Visualbuilder\EmailTemplates\Models\EmailTemplate;
use Visualbuilder\EmailTemplates\Resources\EmailTemplateResource\Pages;
use Visualbuilder\FilamentTinyEditor\TinyEditor;

class EmailTemplateResource extends Resource
{
    protected static ?string $model = EmailTemplate::class;

    public static function shouldRegisterNavigation(): bool
    {
        return EmailTemplatesPlugin::get()->shouldRegisterNavigation();
    }

    public static function getNavigationIcon(): ?string
    {
        return config('filament-email-templates.navigation.templates.icon');
    }

    public static function getNavigationGroup(): ?string
    {
        return EmailTemplatesPlugin::get()->getNavigationGroup();
    }

    public static function getNavigationSort(): ?int
    {
        return config('filament-email-templates.navigation.templates.sort');
    }

    public static function getModelLabel(): string
    {
        return __(config('filament-email-templates.navigation.templates.label'));
    }

    public static function getPluralModelLabel(): string
    {
        return __(config('filament-email-templates.navigation.templates.label'));
    }

    public static function getCluster(): string
    {
        return config('filament-email-templates.navigation.templates.cluster');
    }

    public static function getSubNavigationPosition(): SubNavigationPosition
    {
        return config('filament-email-templates.navigation.templates.position');
    }

    public static function table(Table $table): Table
    {
        return $table
                ->query(EmailTemplate::query())
                ->contentGrid([
                        'md' => 2,
                        'lg' => 3,
                        'xl' => 4,
                ])
                ->columns(
                        [
                                Stack::make([
                                        SpatieMediaLibraryImageColumn::make('screenshot')
                                                ->collection('screenshot')
                                                ->conversion('thumb')
                                                ->circular(false)
                                                ->width('100%')
                                                ->height(180)
                                                ->extraImgAttributes(['style' => 'object-fit: cover; object-position: top; border-radius: 8px; margin: 0 auto; margin-bottom: 0.5rem;'])
                                                ->defaultImageUrl(fn () => 'data:image/svg+xml,' . rawurlencode('<svg xmlns="http://www.w3.org/2000/svg" width="400" height="250" fill="none"><rect width="400" height="250" rx="8" fill="#1f2937"/><text x="200" y="130" text-anchor="middle" fill="#6b7280" font-size="14">No preview</text></svg>'))
                                                ->label(__('Preview')),
                                        TextColumn::make('name')
                                                ->weight('bold')
                                                ->sortable()
                                                ->searchable()
                                                ->alignment('center'),
                                        TextColumn::make('subject')
                                                ->color('gray')
                                                ->searchable()
                                                ->limit(60)
                                                ->alignment('center'),
                                ]),
                        ]
                )
                ->filters(
                        [
                                Tables\Filters\TrashedFilter::make(),
                        ]
                )
                ->actionsAlignment('end')
                ->actions(
                        [
                                ActionGroup::make([
                                        Action::make('create-mail-class')
                                                ->label(__('Build Class'))
                                                ->visible(fn (EmailTemplate $record) => ! $record->mailable_exists)
                                                ->icon('heroicon-o-document-text')
                                                ->action(function (EmailTemplate $record) {
                                                    $notify = app(CreateMailableInterface::class)->createMailable($record);
                                                    Notification::make()
                                                            ->title($notify->title)
                                                            ->icon($notify->icon)
                                                            ->iconColor($notify->icon_color)
                                                            ->duration(10000)
                                                            ->body("<span style='overflow-wrap: anywhere;'>".$notify->body."</span>")
                                                            ->send();
                                                }),
                                        Action::make('captureScreenshot')
                                                ->label(__('Capture Screenshot'))
                                                ->icon('heroicon-o-camera')
                                                ->color('info')
                                                ->visible(fn () => EmailTemplatesPlugin::get()->hasScreenshotCapture())
                                                ->action(function (EmailTemplate $record): void {
                                                    $data = $record->getEmailPreviewData();
                                                    $html = view($record->view_path, ['data' => $data])->render();

                                                    $callback = EmailTemplatesPlugin::get()->getScreenshotCaptureCallback();
                                                    $result = $callback($html);

                                                    if (! $result || ! isset($result['image'])) {
                                                        Notification::make()
                                                                ->title(__('Screenshot capture failed'))
                                                                ->danger()
                                                                ->send();
                                                        return;
                                                    }

                                                    $extension = str_contains($result['contentType'] ?? '', 'jpeg') ? 'jpg' : 'png';
                                                    $tempPath = tempnam(sys_get_temp_dir(), 'email_screenshot_') . '.' . $extension;
                                                    file_put_contents($tempPath, $result['image']);

                                                    $record->addMedia($tempPath)
                                                            ->toMediaCollection('screenshot');

                                                    Notification::make()
                                                            ->title(__('Screenshot captured'))
                                                            ->success()
                                                            ->send();
                                                }),
                                        ViewAction::make('Preview')
                                                ->icon('heroicon-o-magnifying-glass')
                                                ->modalContent(fn (EmailTemplate $record): View => view(
                                                        'vb-email-templates::forms.components.iframe',
                                                        ['record' => $record],
                                                ))
                                                ->modalHeading(fn (EmailTemplate $record): string => 'Preview Email: ' . $record->name)
                                                ->modalSubmitAction(false)
                                                ->modalCancelAction(false)
                                                ->slideOver(),
                                        EditAction::make(),
                                        DeleteAction::make(),
                                        ForceDeleteAction::make()
                                                ->before(function (EmailTemplate $record, EmailTemplateResource $emailTemplateResource) {
                                                    $emailTemplateResource->handleLogoDelete($record->logo);
                                                }),
                                        RestoreAction::make(),
                                ]),
                        ]
                )
                ->bulkActions(
                        [
                                \Filament\Actions\BulkAction::make('captureScreenshots')
                                        ->label(__('Capture Screenshots'))
                                        ->icon('heroicon-o-camera')
                                        ->color('info')
                                        ->visible(fn () => EmailTemplatesPlugin::get()->hasScreenshotCapture())
                                        ->deselectRecordsAfterCompletion()
                                        ->action(function (\Illuminate\Database\Eloquent\Collection $records): void {
                                            $dispatched = 0;

                                            foreach ($records as $record) {
                                                try {
                                                    $data = $record->getEmailPreviewData();
                                                    $html = view($record->view_path, ['data' => $data])->render();

                                                    \Visualbuilder\EmailTemplates\Jobs\CaptureEmailScreenshot::dispatch($record, $html);
                                                    $dispatched++;
                                                } catch (\Throwable $e) {
                                                    // Skip templates that fail to render
                                                }
                                            }

                                            Notification::make()
                                                    ->title(__(':count screenshot jobs queued', ['count' => $dispatched]))
                                                    ->body(__('Screenshots will appear as they complete.'))
                                                    ->success()
                                                    ->send();
                                        }),
                                DeleteBulkAction::make(),
                                ForceDeleteBulkAction::make(),
                                RestoreBulkAction::make(),
                        ]
                )
                ->defaultPaginationPageOption(12)
                ->paginationPageOptions([12, 24, 48]);
    }

    public static function form(Schema $schema): Schema
    {
        $formHelper = app(FormHelperInterface::class);
        $templates = $formHelper->getTemplateViewOptions();

        return $schema->schema(
                [
                        Section::make()
                                ->columnSpanFull()
                                ->schema(
                                        [
                                                Grid::make(['default' => 1])
                                                        ->schema(
                                                                [
                                                                        TextInput::make('name')
                                                                                ->live()
                                                                                ->label(__('vb-email-templates::email-templates.form-fields-labels.template-name'))
                                                                                ->hint(__('vb-email-templates::email-templates.form-fields-labels.template-name-hint'))
                                                                                ->required(),
                                                                ]
                                                        ),

                                                Grid::make(['default' => 1, 'sm' => 1, 'md' => 2])
                                                        ->schema(
                                                                [
                                                                        TextInput::make('key')
                                                                                ->afterStateUpdated(
                                                                                        fn(Set $set, ?string $state) => $set('key', Str::slug($state))
                                                                                )
                                                                                ->label(__('vb-email-templates::email-templates.form-fields-labels.key'))
                                                                                ->hint(__('vb-email-templates::email-templates.form-fields-labels.key-hint'))
                                                                                ->required()
                                                                                ->unique(table: EmailTemplate::class,
                                                                                        column: 'key',
                                                                                        ignoreRecord: true,
                                                                                        modifyRuleUsing: function (Unique $rule, $get) {
                                                                                            $rule->where('language', $get('language'));
                                                                                            if (EmailTemplate::isMultitenancyEnabled() && Filament::getTenant()) {
                                                                                                $rule->where(EmailTemplate::getTenantForeignKeyName(), Filament::getTenant()->getKey());
                                                                                            }
                                                                                            return $rule;
                                                                                        })
                                                                                ->maxLength(191),
                                                                        Select::make('language')
                                                                                ->options($formHelper->getLanguageOptions())
                                                                                ->default(config('filament-email-templates.default_locale'))
                                                                                ->searchable()
                                                                                ->allowHtml(),
                                                                        TextInput::make('from.email')->default(config('mail.from.address'))
                                                                                ->label(__('vb-email-templates::email-templates.form-fields-labels.email-from'))
                                                                                ->email(),
                                                                        TextInput::make('from.name')->default(config('mail.from.name'))
                                                                                ->label(__('vb-email-templates::email-templates.form-fields-labels.email-from-name'))
                                                                                ->string()
                                                                                ->maxLength(191),

                                                                        Select::make('view')
                                                                                ->label(__('vb-email-templates::email-templates.form-fields-labels.template-view'))
                                                                                ->options($templates)
                                                                                ->default(current($templates))
                                                                                ->searchable()
                                                                                ->required(),

                                                                        Select::make(config('filament-email-templates.theme_table_name').'_id')
                                                                                ->label(__('vb-email-templates::email-templates.form-fields-labels.theme'))
                                                                                ->relationship(name: 'theme', titleAttribute: 'name')
                                                                                ->native(false)
                                                                ]
                                                        ),

                                                Grid::make(['default' => 1])
                                                        ->schema(
                                                                [
                                                                        TextInput::make('subject')
                                                                                ->label(__('vb-email-templates::email-templates.form-fields-labels.subject'))
                                                                                ->maxLength(191),

                                                                        TextInput::make('preheader')
                                                                                ->label(__('vb-email-templates::email-templates.form-fields-labels.header'))
                                                                                ->hint(__('vb-email-templates::email-templates.form-fields-labels.header-hint'))
                                                                                ->maxLength(191),

                                                                        TextInput::make('title')
                                                                                ->label(__('vb-email-templates::email-templates.form-fields-labels.title'))
                                                                                ->hint(__('vb-email-templates::email-templates.form-fields-labels.title-hint'))
                                                                                ->maxLength(191),

                                                                        TinyEditor::make('content')
                                                                                ->label(__('vb-email-templates::email-templates.form-fields-labels.content'))
                                                                                ->profile('default')
                                                                                ->default("<p>Dear ##user.first_name##, </p>"),

                                                                        Radio::make('logo_type')
                                                                                ->label(__('vb-email-templates::email-templates.form-fields-labels.logo-type'))
                                                                                ->options([
                                                                                        'browse_another' => __('vb-email-templates::email-templates.form-fields-labels.browse-another'),
                                                                                        'paste_url'      => __('vb-email-templates::email-templates.form-fields-labels.paste-url'),
                                                                                ])
                                                                                ->default('browse_another')
                                                                                ->inline()
                                                                                ->live(),

                                                                        FileUpload::make('logo')
                                                                                ->label(__('vb-email-templates::email-templates.form-fields-labels.logo'))
                                                                                ->hint(__('vb-email-templates::email-templates.form-fields-labels.logo-hint'))
                                                                                ->hidden(fn(Get $get) => $get('logo_type') !== 'browse_another')
                                                                                ->directory(config('filament-email-templates.browsed_logo'))
                                                                                ->image(),

                                                                        TextInput::make('logo_url')
                                                                                ->label(__('vb-email-templates::email-templates.form-fields-labels.logo-url'))
                                                                                ->hint(__('vb-email-templates::email-templates.form-fields-labels.logo-url-hint'))
                                                                                ->placeholder('https://www.example.com/media/test.png')
                                                                                ->hidden(fn(Get $get) => $get('logo_type') !== 'paste_url')
                                                                                ->activeUrl()
                                                                                ->maxLength(191),
                                                                ]
                                                        ),

                                        ]
                                ),
                ]
        );
    }

    public function handleLogoDelete($logo)
    {
        if ($logo) {
            $defaultLogoPath = config('filament-email-templates.logo');
            $parsedLogoPath = str_replace(asset('/'), storage_path('app/public/'), $logo);

            if (!str_contains($parsedLogoPath, $defaultLogoPath) && File::exists($parsedLogoPath)) {
                File::delete($parsedLogoPath);
            }
        }
    }

    public static function getPages(): array
    {
        return [
                'index'  => Pages\ListEmailTemplates::route('/'),
                'create' => Pages\CreateEmailTemplate::route('/create'),
                'edit'   => Pages\EditEmailTemplate::route('/{record}/edit'),
        ];
    }

    public static function isScopedToTenant(): bool
    {
        // We handle tenant scoping ourselves to support global+tenant template visibility
        return false;
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
                ->withoutGlobalScopes([SoftDeletingScope::class]);

        if (EmailTemplate::isMultitenancyEnabled()) {
            $tenant = Filament::getTenant();
            if ($tenant) {
                $fk = EmailTemplate::getTenantForeignKeyName();
                $query->where(function ($q) use ($fk, $tenant) {
                    $q->where($fk, $tenant->getKey())->orWhereNull($fk);
                });
            }
        }

        return $query;
    }

    public function handleLogo(array $data): array
    {
        if ($data['logo_type'] == "paste_url" && $data['logo_url']) {
            $data['logo'] = $data['logo_url'];
        }
        unset($data['logo_type'], $data['logo_url']);
        return $data;
    }
}
