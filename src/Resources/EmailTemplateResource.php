<?php

namespace Visualbuilder\EmailTemplates\Resources;

use Filament\Actions\Action;
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
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
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
                ->columns(
                        [
                                TextColumn::make('id')
                                        ->sortable()
                                        ->searchable()
                                        ->toggleable(),
                                TextColumn::make('key')
                                        ->limit(50)
                                        ->sortable()
                                        ->searchable()
                                        ->toggleable(isToggledHiddenByDefault: true),
                                TextColumn::make('name')
                                        ->limit(50)
                                        ->sortable()
                                        ->searchable()
                                        ->toggleable(),
                                TextColumn::make('title')
                                        ->limit(50)
                                        ->searchable()
                                        ->toggleable(isToggledHiddenByDefault: true),
                                TextColumn::make('language')
                                        ->limit(50)
                                        ->toggleable(isToggledHiddenByDefault: true),
                                TextColumn::make('subject')
                                        ->searchable()
                                        ->limit(50)
                                        ->toggleable(),
                                TextColumn::make('content')
                                        ->limit(200)
                                        ->wrap()
                                        ->searchable()
                                        ->toggleable(),
                        ]
                )
                ->filters(
                        [
                                Tables\Filters\TrashedFilter::make(),
                        ]
                )
                ->actions(
                        [
                                Action::make('create-mail-class')
                                        ->label("Build Class")
                                        //Only show the button if the file does not exist
                                        ->visible(function (EmailTemplate $record) {
                                            return !$record->mailable_exists;
                                        })
                                        ->icon('heroicon-o-document-text')
                                        // ->action('createMailClass'),
                                        ->action(function (EmailTemplate $record) {
                                            $notify = app(CreateMailableInterface::class)->createMailable($record);
                                            Notification::make()
                                                    ->title($notify->title)
                                                    ->icon($notify->icon)
                                                    ->iconColor($notify->icon_color)
                                                    ->duration(10000)
                                                    //Fix for bug where body hides the icon
                                                    ->body("<span style='overflow-wrap: anywhere;'>".$notify->body."</span>")
                                                    ->send();
                                        }),
                                ViewAction::make('Preview')
                                        ->icon('heroicon-o-magnifying-glass')
                                        ->modalContent(fn(EmailTemplate $record): View => view(
                                                'vb-email-templates::forms.components.iframe',
                                                ['record' => $record],
                                        ))
                                        ->modalHeading(fn(EmailTemplate $record): string => 'Preview Email: '.$record->name)
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
                        ]
                )
                ->bulkActions(
                        [
                                DeleteBulkAction::make(),
                                ForceDeleteBulkAction::make(),
                                RestoreBulkAction::make(),
                        ]
                );
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
                                                                                            return $rule->where('language', $get('language'));
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

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
                ->withoutGlobalScopes(
                        [
                                SoftDeletingScope::class,
                        ]
                );
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
