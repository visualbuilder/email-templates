<?php

namespace Visualbuilder\EmailTemplates\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Visualbuilder\EmailTemplates\Models\EmailTemplate;
use Visualbuilder\EmailTemplates\Models\EmailTemplateTheme;
use Visualbuilder\EmailTemplates\Resources\EmailTemplateThemeResource\Pages;

class EmailTemplateThemeResource extends Resource
{
    protected static ?string $model = EmailTemplateTheme::class;

    protected static ?string $navigationIcon = 'heroicon-o-paint-brush';

    public static function getNavigationGroup(): ?string
    {
        return config('filament-email-templates.navigation.group');
    }

    public static function getNavigationSort(): ?int
    {
        return config('filament-email-templates.navigation.sort');
    }

    public static function getModelLabel(): string
    {
        return __('vb-email-templates::email-templates.theme_resource_name.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('vb-email-templates::email-templates.theme_resource_name.plural');
    }

    public static function getPreviewData()
    {
        $emailTemplate = EmailTemplate::findEmailByKey('user-verify-email');

        return $emailTemplate->getEmailPreviewData();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make(__('vb-email-templates::email-templates.theme-form-fields-labels.template-preview'))
                            ->schema([
                                Forms\Components\ViewField::make('preview')->view('vb-email-templates::email.default_preview', ['data' => self::getPreviewData()]),
                            ])
                            ->columnSpan(['lg' => 2]),
                    ])
                    ->columnSpan(['lg' => 2]),

                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make()
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label(__('vb-email-templates::email-templates.theme-form-fields-labels.theme-name'))
                                    ->columnSpan(3),

                                Forms\Components\Toggle::make('is_default')
                                    ->label(__('vb-email-templates::email-templates.theme-form-fields-labels.is-default'))
                                    ->inline(false)
                                    ->onColor('success')
                                    ->offColor('danger'),
                            ]),

                        Forms\Components\Section::make(__('vb-email-templates::email-templates.theme-form-fields-labels.set-colors'))
                            ->schema([
                                Forms\Components\ColorPicker::make('colours.header_bg_color')
                                    ->label(__('vb-email-templates::email-templates.theme-form-fields-labels.header-bg'))
                                    ->live(),

                                Forms\Components\ColorPicker::make('colours.body_bg_color')
                                    ->label(__('vb-email-templates::email-templates.theme-form-fields-labels.body-bg'))
                                    ->live(),

                                Forms\Components\ColorPicker::make('colours.content_bg_color')
                                    ->label(__('vb-email-templates::email-templates.theme-form-fields-labels.content-bg'))
                                    ->live(),

                                Forms\Components\ColorPicker::make('colours.footer_bg_color')
                                    ->label(__('vb-email-templates::email-templates.theme-form-fields-labels.footer-bg')),

                                Forms\Components\ColorPicker::make('colours.callout_bg_color')
                                    ->label(__('vb-email-templates::email-templates.theme-form-fields-labels.callout-bg'))
                                    ->live(),

                                Forms\Components\ColorPicker::make('colours.button_bg_color')
                                    ->label(__('vb-email-templates::email-templates.theme-form-fields-labels.button-bg'))
                                    ->live(),

                                Forms\Components\ColorPicker::make('colours.body_color')
                                    ->label(__('vb-email-templates::email-templates.theme-form-fields-labels.body-color'))
                                    ->live(),

                                Forms\Components\ColorPicker::make('colours.callout_color')
                                    ->label(__('vb-email-templates::email-templates.theme-form-fields-labels.callout-color'))
                                    ->live(),

                                Forms\Components\ColorPicker::make('colours.button_color')
                                    ->label(__('vb-email-templates::email-templates.theme-form-fields-labels.button-color'))
                                    ->live(),

                                Forms\Components\ColorPicker::make('colours.anchor_color')
                                    ->label(__('vb-email-templates::email-templates.theme-form-fields-labels.anchor-color'))
                                    ->live(),
                            ]),

                    ])
                    ->columnSpan(['lg' => 1]),
            ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\IconColumn::make('is_default')->boolean(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEmailTemplateThemes::route('/'),
            'create' => Pages\CreateEmailTemplateTheme::route('/create'),
            'edit' => Pages\EditEmailTemplateTheme::route('/{record}/edit'),
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
