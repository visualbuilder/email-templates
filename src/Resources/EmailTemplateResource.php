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
use FilamentTiptapEditor\TiptapEditor;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Visualbuilder\EmailTemplates\Models\EmailTemplate;
use Visualbuilder\EmailTemplates\Components\SelectLanguage;
use Visualbuilder\EmailTemplates\Resources\EmailTemplateResource\Pages;
use Visualbuilder\EmailTemplates\Resources\EmailTemplateResource\RelationManagers;

class EmailTemplateResource extends Resource
{
    protected static ?string $model = EmailTemplate::class;

    protected static ?string $navigationIcon = 'heroicon-o-collection';

    public static function form(Form $form): Form
    {
        // $languages = config('email-templates.languages');
        // dd($languages->en_GB);
        $languages = self::prepareLang();
        $templates = self::getTemplateViewList();
        // dd($templates);
        return $form
            ->schema([
                Card::make()->schema([
                    Grid::make(['default' => 1])->schema([
                        TextInput::make('name')->reactive()
                            ->afterStateUpdated(function (Closure $set, $state) {
                                $set('key', Str::slug($state));
                            })
                            ->label(__('vb-email-templates::email-template-form-fields.labels.template-name'))
                            ->hint(__(config('vb-email-templates::email-template-form-fields.labels.template-name-hint')))
                            ->required(),                    
                    ]),
                    
                    Grid::make(['default' => 1, 'sm' => 1, 'md' => 2])->schema([
                        TextInput::make('key')
                            ->label(__(config('vb-email-templates::email-template-form-fields.labels.key')))
                            ->hint(__(config('vb-email-templates::email-template-form-fields.labels.key-hint')))
                            ->required()->unique(ignorable: fn ($record) => $record),
                        SelectLanguage::make('language')
                            ->options($languages),
                        Select::make('view')
                            ->label(__(config('vb-email-templates::email-template-form-fields.labels.template-view')))
                            ->options($templates)
                            ->required(),
                        TextInput::make('from')
                            ->label(__(config('vb-email-templates::email-template-form-fields.labels.email-from')))
                            ->required(),
                        TextInput::make('send_to')
                            ->label(__(config('vb-email-templates::email-template-form-fields.labels.email-to'))),
                    ]),
                    
                    Grid::make(['default' => 1])->schema([
                        TextInput::make('subject')
                            ->label(__(config('vb-email-templates::email-template-form-fields.labels.subject'))),

                        TextInput::make('preheader')
                            ->label(__(config('vb-email-templates::email-template-form-fields.labels.header')))
                            ->hint(__(config('vb-email-templates::email-template-form-fields.labels.header-hint'))),

                        TextInput::make('title')
                            ->label(__(config('vb-email-templates::email-template-form-fields.labels.title')))
                            ->hint(__(config('vb-email-templates::email-template-form-fields.labels.title-hint'))),

                        TiptapEditor::make('content')
                            ->label(__(config('vb-email-templates::email-template-form-fields.labels.content')))
                            ->profile('default'),
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
            $preparedLang[$langKey] = '<span class="fi fi-'.$langVal["flag-icon"].'"></span> '.$langVal["display"];
        }
        return $preparedLang;
    }

    public static function getTemplateViewList() {
        // create an array to store the filenames
        $filenamesArray = [];
        $templates = [];
    
        $templateDirectory = dirname(view(config('email-templates.template_view_path'). '.default')->getPath());
        $filenamesArray = self::getFiles($templateDirectory, $templateDirectory);

        // formatting array
        foreach ($filenamesArray as $item) {
            $templates[config('email-templates.template_view_path').'.'.$item] = 'vb-'.$item;
        }
        return $templates;
    }

    /**
     * Recursively get all files in a directory and children
     */
	private static function getFiles($dir, $basepath) {
		$files = $subdirs = $subFiles = [];

		if($handle = opendir($dir)) {
			while (false !== ($entry = readdir($handle))) {
				if($entry == "." || $entry == "..") continue;
				$entryPath = $dir.'/'.$entry;
				if(is_dir($entryPath)) {
					$subdirs[] = $entryPath;
				}
				else {
					$subFiles[] = str_replace('/', '.', str_replace('.blade.php', '', str_replace($basepath.'/', '', $entryPath)));
				}
			}
			closedir($handle);
			sort($subFiles);
			$files = array_merge($files, $subFiles);
			foreach ($subdirs as $subdir) {
				$files = array_merge($files, self::getFiles($subdir, $basepath));
			}
		}
		return $files;
	}
    
}
