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
                        Forms\Components\Section::make('Template Preview')
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
                                    ->columnSpan(3),

                                Forms\Components\Toggle::make('is_default')
                                    ->label('Is Active')
                                    ->inline(false)
                                    ->onColor('success')
                                    ->offColor('danger'),
                            ]),

                        Forms\Components\Section::make('Set Colors')
                            ->schema([
                                Forms\Components\ColorPicker::make('colours.header_bg_color')
                                    ->label('Header Background')
                                    ->live(),

                                Forms\Components\ColorPicker::make('colours.body_bg_color')
                                    ->label('Body Background')
                                    ->live(),

                                Forms\Components\ColorPicker::make('colours.content_bg_color')
                                    ->label('Content Background')
                                    ->live(),

                                Forms\Components\ColorPicker::make('colours.footer_bg_color')
                                    ->label('Footer Background'),

                                Forms\Components\ColorPicker::make('colours.callout_bg_color')
                                    ->label('Callout Background')->live(),

                                Forms\Components\ColorPicker::make('colours.button_bg_color')
                                    ->label('Button Background')->live(),

                                Forms\Components\ColorPicker::make('colours.body_color')
                                    ->label('Body Color')->live(),

                                Forms\Components\ColorPicker::make('colours.callout_color')
                                    ->label('Callout Color'),

                                Forms\Components\ColorPicker::make('colours.button_color')
                                    ->label('Button Color')->live(),

                                Forms\Components\ColorPicker::make('colours.anchor_color')
                                    ->label('Anchor Color')->live(),
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
