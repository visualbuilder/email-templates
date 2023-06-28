<?php

namespace Visualbuilder\EmailTemplates\Resources;

use Closure;
use Filament\Forms;
use Filament\Tables;
use Illuminate\Support\Str;
use Filament\Resources\Form;
use Filament\Resources\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\RichEditor;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Visualbuilder\EmailTemplates\Models\EmailTemplate;
use Visualbuilder\EmailTemplates\Resources\EmailTemplateResource\Pages;
use Visualbuilder\EmailTemplates\Resources\EmailTemplateResource\RelationManagers;

class EmailTemplateResource extends Resource
{
    protected static ?string $model = EmailTemplate::class;

    protected static ?string $navigationIcon = 'heroicon-o-collection';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Card::make()->schema([
                    Grid::make(['default' => 1])->schema([
                        TextInput::make('name')->reactive()
                            ->afterStateUpdated(function (Closure $set, $state) {
                                $set('key', Str::slug($state));
                            })
                            ->label(__(config('email-templates.field-labels.template-name')))
                            ->hint(__(config('email-templates.field-labels.template-name-hint')))
                            ->required(),                    
                    ]),
                    
                    Grid::make(['default' => 1, 'sm' => 1, 'md' => 2])->schema([
                        TextInput::make('key')
                            ->label(__(config('email-templates.field-labels.key')))
                            ->hint(__(config('email-templates.field-labels.key-hint')))
                            ->required()->unique(ignorable: fn ($record) => $record),
                        Select::make('language')
                            ->label(__(config('email-templates.field-labels.lang')))
                            ->options([
                                'British' => 'British',
                            ])
                            ->required(),
                        
                        TextInput::make('from')
                            ->label(__(config('email-templates.field-labels.email-from')))
                            ->required(),
                        TextInput::make('send_to')
                            ->label(__(config('email-templates.field-labels.email-to'))),
                    ]),
                    
                    Grid::make(['default' => 1])->schema([
                        TextInput::make('subject')
                            ->label(__(config('email-templates.field-labels.subject'))),

                        TextInput::make('preheader')
                            ->label(__(config('email-templates.field-labels.header')))
                            ->hint(__(config('email-templates.field-labels.header-hint'))),

                        TextInput::make('title')
                            ->label(__(config('email-templates.field-labels.title')))
                            ->hint(__(config('email-templates.field-labels.title-hint'))),

                        RichEditor::make('content')
                            ->label(__(config('email-templates.field-labels.content'))),
                    ]),
                    
                ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id'),
                TextColumn::make('name')->limit(50)->sortable()->searchable(),
                TextColumn::make('language')->limit(50),
                TextColumn::make('subject')->limit(50),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->label("Preview"),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }
    
    public static function getRelations(): array
    {
        return [
            //
        ];
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEmailTemplates::route('/'),
            'create' => Pages\CreateEmailTemplate::route('/create'),
            'edit' => Pages\EditEmailTemplate::route('/{record}/edit'),
            'view' => Pages\PreviewEmailTemplate::route('/{record}'),
        ];
    }    
}
