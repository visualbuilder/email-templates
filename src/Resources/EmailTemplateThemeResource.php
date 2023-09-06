<?php

namespace Visualbuilder\EmailTemplates\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Visualbuilder\EmailTemplates\Models\EmailTemplateTheme;
use Visualbuilder\EmailTemplates\Resources\EmailTemplateThemeResource\Pages;
use Visualbuilder\EmailTemplates\Resources\EmailTemplateThemeResource\RelationManagers;

class EmailTemplateThemeResource extends Resource
{
    protected static ?string $model = EmailTemplateTheme::class;

    protected static ?string $navigationIcon = 'heroicon-o-paint-brush';

    public static function getNavigationGroup(): ?string
    {
        return config('email-templates.navigation.group');
    }

    public static function getNavigationSort(): ?int
    {
        return config('email-templates.navigation.sort');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make()
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->columnSpan(3),

                                Forms\Components\Toggle::make('is_active')
                                    ->label('Is Active')
                                    ->inline(false)
                                    ->onColor('success')
                                    ->offColor('danger'),
                            ])->columns(4),

                        Forms\Components\Section::make('Set Colors')
                            ->schema([
                                Forms\Components\ColorPicker::make('colours.header_bg_color')
                                ->label('Header Background'),

                                Forms\Components\ColorPicker::make('colours.body_bg_color')
                                    ->label('Body Background'),

                                Forms\Components\ColorPicker::make('colours.content_bg_color')
                                    ->label('Content Background'),

                                Forms\Components\ColorPicker::make('colours.footer_bg_color')
                                    ->label('Footer Background'),
                                    
                                Forms\Components\ColorPicker::make('colours.callout_bg_color')
                                    ->label('Callout Background'),

                                Forms\Components\ColorPicker::make('colours.button_bg_color')
                                    ->label('Button Background'),

                                Forms\Components\ColorPicker::make('colours.body_color')
                                    ->label('Body Color'),

                                Forms\Components\ColorPicker::make('colours.callout_color')
                                    ->label('Callout Color'),

                                Forms\Components\ColorPicker::make('colours.button_color')
                                    ->label('Button Color'),

                                Forms\Components\ColorPicker::make('colours.anchor_color')
                                    ->label('Anchor Color'),
                            ])->columns(3)
                    ])->columnSpanFull()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
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
