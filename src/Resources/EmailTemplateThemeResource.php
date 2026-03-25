<?php

namespace Visualbuilder\EmailTemplates\Resources;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Visualbuilder\EmailTemplates\EmailTemplatesPlugin;
use Visualbuilder\EmailTemplates\Models\EmailTemplate;
use Visualbuilder\EmailTemplates\Models\EmailTemplateTheme;
use Visualbuilder\EmailTemplates\Resources\EmailTemplateThemeResource\Pages;

class EmailTemplateThemeResource extends Resource
{
    protected static ?string $model = EmailTemplateTheme::class;


    public static function shouldRegisterNavigation(): bool
    {
        return EmailTemplatesPlugin::get()->shouldRegisterNavigation();
    }

    public static function getNavigationIcon(): ?string
    {
        return config('filament-email-templates.navigation.themes.icon');
    }

    public static function getNavigationGroup(): ?string
    {
        return EmailTemplatesPlugin::get()->getNavigationGroup();
    }

    public static function getNavigationSort(): ?int
    {
        return config('filament-email-templates.navigation.themes.sort');
    }

    public static function getModelLabel(): string
    {
        return __(config('filament-email-templates.navigation.themes.label'));
    }

    public static function getPluralModelLabel(): string
    {
        return __(config('filament-email-templates.navigation.themes.label'));
    }

    public static function getCluster(): string
    {
        return config('filament-email-templates.navigation.themes.cluster');
    }

    public static function getSubNavigationPosition(): SubNavigationPosition
    {
        return config('filament-email-templates.navigation.templates.position');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
                ->schema([
                        Group::make()
                                ->schema([
                                        Section::make(__('vb-email-templates::email-templates.theme-form-fields-labels.template-preview'))
                                                ->schema([
                                                        View::make('preview')
                                                                ->view('vb-email-templates::email.default_preview',
                                                                        ['data' => self::getPreviewData()])
                                                                ->dehydrated(false),
                                                ])
                                                ->columnSpan(['lg' => 2]),
                                ])
                                ->columnSpan(['lg' => 2]),
                        Group::make()
                                ->schema([
                                        Section::make()
                                                ->schema([
                                                        TextInput::make('name')
                                                                ->label(__('vb-email-templates::email-templates.theme-form-fields-labels.theme-name'))
                                                                ->columnSpan(3),

                                                        Toggle::make('is_default')
                                                                ->label(__('vb-email-templates::email-templates.theme-form-fields-labels.is-default'))
                                                                ->inline(false)
                                                                ->onColor('success')
                                                                ->offColor('danger'),
                                                ]),

                                        Section::make(__('Screenshot'))
                                                ->schema([
                                                        SpatieMediaLibraryFileUpload::make('screenshot')
                                                                ->collection('screenshot')
                                                                ->image()
                                                                ->imageEditor()
                                                                ->label(__('Theme Screenshot'))
                                                                ->helperText(__('Upload or auto-capture a preview of this theme')),
                                                ])
                                                ->collapsed(),

                                        Section::make(__('vb-email-templates::email-templates.theme-form-fields-labels.set-colors'))
                                                ->schema([
                                                        ColorPicker::make('colours.header_bg_color')
                                                                ->label(__('vb-email-templates::email-templates.theme-form-fields-labels.header-bg'))
                                                                ->live(),

                                                        ColorPicker::make('colours.body_bg_color')
                                                                ->label(__('vb-email-templates::email-templates.theme-form-fields-labels.body-bg'))
                                                                ->live(),

                                                        ColorPicker::make('colours.content_bg_color')
                                                                ->label(__('vb-email-templates::email-templates.theme-form-fields-labels.content-bg'))
                                                                ->live(),

                                                        ColorPicker::make('colours.footer_bg_color')
                                                                ->label(__('vb-email-templates::email-templates.theme-form-fields-labels.footer-bg')),

                                                        ColorPicker::make('colours.callout_bg_color')
                                                                ->label(__('vb-email-templates::email-templates.theme-form-fields-labels.callout-bg'))
                                                                ->live(),

                                                        ColorPicker::make('colours.button_bg_color')
                                                                ->label(__('vb-email-templates::email-templates.theme-form-fields-labels.button-bg'))
                                                                ->live(),

                                                        ColorPicker::make('colours.body_color')
                                                                ->label(__('vb-email-templates::email-templates.theme-form-fields-labels.body-color'))
                                                                ->live(),

                                                        ColorPicker::make('colours.callout_color')
                                                                ->label(__('vb-email-templates::email-templates.theme-form-fields-labels.callout-color'))
                                                                ->live(),

                                                        ColorPicker::make('colours.button_color')
                                                                ->label(__('vb-email-templates::email-templates.theme-form-fields-labels.button-color'))
                                                                ->live(),

                                                        ColorPicker::make('colours.anchor_color')
                                                                ->label(__('vb-email-templates::email-templates.theme-form-fields-labels.anchor-color'))
                                                                ->live(),
                                                ]),

                                ])
                                ->columnSpan(['lg' => 1]),
                ])->columns(3);
    }

    public static function getPreviewData()
    {
        $emailTemplate = EmailTemplate::first();

        return $emailTemplate->getEmailPreviewData();
    }

    public static function table(Table $table): Table
    {
        return $table
                ->contentGrid([
                        'md' => 2,
                        'lg' => 3,
                        'xl' => 4,
                ])
                ->columns([
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
                                Tables\Columns\TextColumn::make('name')
                                        ->weight('bold')
                                        ->sortable()
                                        ->searchable()
                                        ->alignment('center'),
                                Tables\Columns\TextColumn::make('is_default')
                                        ->label('')
                                        ->badge()
                                        ->state(fn ($record) => $record->is_default ? __('Default') : null)
                                        ->icon(fn ($state) => $state ? 'heroicon-o-check-circle' : null)
                                        ->color('success')
                                        ->alignment('center'),
                        ]),
                ])
                ->actionsAlignment('end')
                ->filters([
                    //
                ])
                ->actions([
                        Action::make('captureScreenshot')
                                ->label(__('Capture'))
                                ->icon('heroicon-o-camera')
                                ->color('info')
                                ->visible(fn () => EmailTemplatesPlugin::get()->hasScreenshotCapture())
                                ->action(function ($record): void {
                                    $emailTemplate = EmailTemplate::first();

                                    if (! $emailTemplate) {
                                        Notification::make()
                                                ->title(__('No email template found'))
                                                ->body(__('Create at least one email template to generate a preview.'))
                                                ->danger()
                                                ->send();
                                        return;
                                    }

                                    // Render the email HTML with this theme's colours
                                    $data = $emailTemplate->getEmailPreviewData();
                                    $data['theme'] = $record->colours;
                                    $html = view($emailTemplate->view_path, ['data' => $data])->render();

                                    $callback = EmailTemplatesPlugin::get()->getScreenshotCaptureCallback();
                                    $result = $callback($html);

                                    if (! $result || ! isset($result['image'])) {
                                        Notification::make()
                                                ->title(__('Screenshot capture failed'))
                                                ->body(__('The screenshot service did not return an image.'))
                                                ->danger()
                                                ->persistent()
                                                ->send();
                                        return;
                                    }

                                    $extension = str_contains($result['contentType'] ?? '', 'jpeg') ? 'jpg' : 'png';
                                    $tempPath = tempnam(sys_get_temp_dir(), 'theme_screenshot_') . '.' . $extension;
                                    file_put_contents($tempPath, $result['image']);

                                    $record->addMedia($tempPath)
                                            ->toMediaCollection('screenshot');

                                    Notification::make()
                                            ->title(__('Screenshot captured successfully'))
                                            ->success()
                                            ->send();
                                }),
                        EditAction::make(),
                ])
                ->bulkActions([
                        BulkActionGroup::make([
                                \Filament\Actions\BulkAction::make('captureScreenshots')
                                        ->label(__('Capture Screenshots'))
                                        ->icon('heroicon-o-camera')
                                        ->color('info')
                                        ->visible(fn () => EmailTemplatesPlugin::get()->hasScreenshotCapture())
                                        ->deselectRecordsAfterCompletion()
                                        ->action(function (\Illuminate\Database\Eloquent\Collection $records): void {
                                            $emailTemplate = EmailTemplate::first();

                                            if (! $emailTemplate) {
                                                Notification::make()
                                                        ->title(__('No email template found'))
                                                        ->danger()
                                                        ->send();
                                                return;
                                            }

                                            $dispatched = 0;

                                            foreach ($records as $record) {
                                                try {
                                                    $data = $emailTemplate->getEmailPreviewData();
                                                    $data['theme'] = $record->colours;
                                                    $html = view($emailTemplate->view_path, ['data' => $data])->render();

                                                    \Visualbuilder\EmailTemplates\Jobs\CaptureEmailScreenshot::dispatch($record, $html);
                                                    $dispatched++;
                                                } catch (\Throwable $e) {
                                                    // Skip themes that fail to render
                                                }
                                            }

                                            Notification::make()
                                                    ->title(__(':count screenshot jobs queued', ['count' => $dispatched]))
                                                    ->body(__('Screenshots will appear as they complete.'))
                                                    ->success()
                                                    ->send();
                                        }),
                                DeleteBulkAction::make(),
                        ]),
                ])
                ->emptyStateActions([
                        CreateAction::make(),
                ]);
    }

    public static function getPages(): array
    {
        return [
                'index'  => Pages\ListEmailTemplateThemes::route('/'),
                'create' => Pages\CreateEmailTemplateTheme::route('/create'),
                'edit'   => Pages\EditEmailTemplateTheme::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
                ->withoutGlobalScopes(
                        [
                                SoftDeletingScope::class,
                        ]
                );
    }
}
