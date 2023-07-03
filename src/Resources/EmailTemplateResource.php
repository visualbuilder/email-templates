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
        $languages = self::prepareLang();
        return $form
            ->schema([
                Card::make()->schema([
                    Grid::make(['default' => 1])->schema([
                        TextInput::make('name')->reactive()
                            ->afterStateUpdated(function (Closure $set, $state) {
                                $set('key', Str::slug($state));
                            })
                            ->label(__(config('email-template-form-fields.labels.template-name')))
                            ->hint(__(config('email-template-form-fields.labels.template-name-hint')))
                            ->required(),                    
                    ]),
                    
                    Grid::make(['default' => 1, 'sm' => 1, 'md' => 2])->schema([
                        TextInput::make('key')
                            ->label(__(config('email-template-form-fields.labels.key')))
                            ->hint(__(config('email-template-form-fields.labels.key-hint')))
                            ->required()->unique(ignorable: fn ($record) => $record),
                        Select::make('language')
                            ->label(__(config('email-template-form-fields.labels.lang')))
                            ->allowHtml(true)
                            ->options($languages)
                            // ->getOptionLabelsUsing(function ($languages) {

                            // })
                            ->required(),
                        
                        TextInput::make('from')
                            ->label(__(config('email-template-form-fields.labels.email-from')))
                            ->required(),
                        TextInput::make('send_to')
                            ->label(__(config('email-template-form-fields.labels.email-to'))),
                    ]),
                    
                    Grid::make(['default' => 1])->schema([
                        TextInput::make('subject')
                            ->label(__(config('email-template-form-fields.labels.subject'))),

                        TextInput::make('preheader')
                            ->label(__(config('email-template-form-fields.labels.header')))
                            ->hint(__(config('email-template-form-fields.labels.header-hint'))),

                        TextInput::make('title')
                            ->label(__(config('email-template-form-fields.labels.title')))
                            ->hint(__(config('email-template-form-fields.labels.title-hint'))),

                        RichEditor::make('content')
                            ->label(__(config('email-template-form-fields.labels.content'))),
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
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->label("Preview"),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\ForceDeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
                Tables\Actions\ForceDeleteBulkAction::make(),
                Tables\Actions\RestoreBulkAction::make(),
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
    
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function prepareLang() {
        $languages = config('email-templates.languages');
        
        $preparedLang = [];
        foreach($languages as $langKey => $langVal)
        {
            $preparedLang[$langKey] = '<span class="fi fi-gr"></span> '.$langVal['display'];

            // $preparedLang[] = [
            //     ['value' => $langKey, 'label' => $langVal['display'], 'icon' => 'heroicon-o-plus'],
            // ];
        }
        return $preparedLang;
    }
    
}
