<?php

namespace Visualbuilder\EmailTemplates\Resources;

use Closure;
use Filament\Tables;
use Illuminate\Support\Str;
use Filament\Resources\Form;
use Filament\Resources\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Grid;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\Select;
use FilamentTiptapEditor\TiptapEditor;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Visualbuilder\EmailTemplates\Models\EmailTemplate;
use Visualbuilder\EmailTemplates\Components\SelectLanguage;
use Visualbuilder\EmailTemplates\Resources\EmailTemplateResource\Pages;

class EmailTemplateResource extends Resource
{
    protected static ?string $model = EmailTemplate::class;
    protected static ?string $navigationIcon = 'heroicon-o-mail';

    public static function getModelLabel(): string
    {
        return __('vb-email-templates::email-templates.resource_name.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('vb-email-templates::email-templates.resource_name.plural');
    }

    public static function form(Form $form): Form
    {
        $languages = self::prepareLang();
        $templates = self::getTemplateViewList();

        return $form->schema(
            [
                Card::make()
                    ->schema(
                        [
                            Grid::make(['default' => 1])
                                ->schema(
                                    [
                                        TextInput::make('name')
                                                 ->reactive()
                                                 ->afterStateUpdated(
                                                     function (Closure $set, $state) {
                                                         $set('key', Str::slug($state));
                                                     }
                                                 )
                                                 ->label(__('vb-email-templates::email-templates.form-fields-labels.template-name'))
                                                 ->hint(__('vb-email-templates::email-templates.form-fields-labels.template-name-hint'))
                                                 ->required(),
                                    ]
                                ),

                            Grid::make(['default' => 1, 'sm' => 1, 'md' => 2])
                                ->schema(
                                    [
                                        TextInput::make('key')
                                                 ->label(__('vb-email-templates::email-templates.form-fields-labels.key'))
                                                 ->hint(__('vb-email-templates::email-templates.form-fields-labels.key-hint'))
                                                 ->required()
                                                 ->unique(ignorable: fn ($record) => $record),
                                        SelectLanguage::make('language')
                                                      ->options($languages),
                                        Select::make('view')
                                              ->label(__('vb-email-templates::email-templates.form-fields-labels.template-view'))
                                              ->options($templates)
                                              ->required(),
                                        TextInput::make('from')
                                                 ->label(__('vb-email-templates::email-templates.form-fields-labels.email-from'))
                                                 ->required(),
                                        TextInput::make('send_to')
                                                 ->label(__('vb-email-templates::email-templates.form-fields-labels.email-to')),
                                    ]
                                ),

                            Grid::make(['default' => 1])
                                ->schema(
                                    [
                                        TextInput::make('subject')
                                                 ->label(__('vb-email-templates::email-templates.form-fields-labels.subject')),

                                        TextInput::make('preheader')
                                                 ->label(__('vb-email-templates::email-templates.form-fields-labels.header'))
                                                 ->hint(__('vb-email-templates::email-templates.form-fields-labels.header-hint')),

                                        TextInput::make('title')
                                                 ->label(__('vb-email-templates::email-templates.form-fields-labels.title'))
                                                 ->hint(__('vb-email-templates::email-templates.form-fields-labels.title-hint')),

                                        TiptapEditor::make('content')
                                                    ->label(__('vb-email-templates::email-templates.form-fields-labels.content'))
                                                    ->profile('default'),
                                    ]
                                ),

                        ]
                    ),
            ]
        );
    }

    public static function prepareLang()
    {
        $languages = config('email-templates.languages');

        $preparedLang = [];
        foreach ($languages as $langKey => $langVal) {
            $preparedLang[ $langKey ] =
                '<span class="fi fi-'.$langVal[ "flag-icon" ].'"></span> '.$langVal[ "display" ];
        }

        return $preparedLang;
    }

    public static function getTemplateViewList()
    {
        $overrideDirectory = resource_path('views/vendor/vb-email-templates/email');
        $packageDirectory = dirname(view(config('email-templates.template_view_path').'.default')->getPath());

        $directories = [$overrideDirectory, $packageDirectory];

        $filenamesArray = [];

        foreach ($directories as $directory) {
            if(file_exists($directory)) {
                $filenamesArray = array_merge($filenamesArray, self::getFiles($directory, $directory));
            }
        }

        // Remove duplicates
        $filenamesArray = array_unique($filenamesArray);

        return array_combine($filenamesArray, $filenamesArray);
    }

    /**
     * Recursively get all files in a directory and children
     */
    private static function getFiles($dir, $basepath)
    {
        $files = $subdirs = $subFiles = [];

        if($handle = opendir($dir)) {
            while (false !== ($entry = readdir($handle))) {
                if($entry == "." || $entry == "..") {
                    continue;
                }
                if(substr($entry, 0, 1) == '_') {
                    continue;
                }
                $entryPath = $dir.'/'.$entry;
                if(is_dir($entryPath)) {
                    $subdirs[] = $entryPath;
                } else {
                    $subFiles[] = str_replace(
                        '/',
                        '.',
                        str_replace(
                            '.blade.php',
                            '',
                            str_replace(
                                $basepath.'/',
                                '',
                                $entryPath
                            )
                        )
                    );
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

    public static function table(Table $table): Table
    {
        return $table->columns(
            [
                TextColumn::make('id'),
                TextColumn::make('name')
                          ->limit(50)
                          ->sortable()
                          ->searchable(),
                TextColumn::make('language')
                          ->limit(50),
                TextColumn::make('subject')
                          ->limit(50),
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
                                ->label("Create Mail Class")
                                ->icon('heroicon-o-document-text') 
                                ->url(fn (EmailTemplate $record): string => route('email-template.generateMailable', $record)),
                             Tables\Actions\ViewAction::make()
                                                      ->label("Preview")
                                                      ->hidden(fn ($record) => $record->trashed()),
                             Tables\Actions\EditAction::make(),
                             Tables\Actions\DeleteAction::make(),
                             Tables\Actions\ForceDeleteAction::make(),
                             Tables\Actions\RestoreAction::make(),
                         ]
                     )
                     ->bulkActions(
                         [
                             Tables\Actions\DeleteBulkAction::make(),
                             Tables\Actions\ForceDeleteBulkAction::make(),
                             Tables\Actions\RestoreBulkAction::make(),
                         ]
                     );
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
                     ->withoutGlobalScopes(
                         [
                             SoftDeletingScope::class,
                         ]
                     );
    }
}
