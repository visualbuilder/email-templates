<?php

namespace Visualbuilder\EmailTemplates\Resources;

use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use FilamentTiptapEditor\TiptapEditor;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;
use Visualbuilder\EmailTemplates\Models\EmailTemplate;
use Visualbuilder\EmailTemplates\Resources\EmailTemplateResource\Pages;

class EmailTemplateResource extends Resource
{
    protected static ?string $model = EmailTemplate::class;
    protected static ?string $navigationIcon = 'heroicon-o-envelope';

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
        $recipients = self::getRecipients();

        return $form->schema(
            [
                Section::make()
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
                                                fn (Set $set, ?string $state) => $set('key', Str::slug($state))
                                            )
                                            ->label(__('vb-email-templates::email-templates.form-fields-labels.key'))
                                            ->hint(__('vb-email-templates::email-templates.form-fields-labels.key-hint'))
                                            ->required()
                                            ->unique(ignorable: fn ($record) => $record),
                                        Select::make('language')
                                            ->options($languages)
                                            ->default(config('email-templates.default_locale'))
                                            ->searchable()
                                            ->allowHtml(),
                                        Select::make('view')
                                            ->label(__('vb-email-templates::email-templates.form-fields-labels.template-view'))
                                            ->options($templates)
                                            ->default(current($templates))
                                            ->searchable()
                                            ->required(),
                                        TextInput::make('from')->default(config('mail.from.address'))
                                            ->label(__('vb-email-templates::email-templates.form-fields-labels.email-from'))
                                            ->required(),
                                        Select::make('send_to')
                                            ->label(__('vb-email-templates::email-templates.form-fields-labels.email-to'))
                                            ->options($recipients)
                                            ->default(current($recipients))
                                            ->searchable()
                                            ->required(),
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
                                            ->profile('default')
                                        ->default("<p>Dear ##user.firstname##, </p>"),
                                    ]
                                ),

                        ]
                    ),
            ]
        );
    }

    public static function prepareLang()
    {
        return collect(config('email-templates.languages'))->mapWithKeys(function ($langVal, $langKey) {
            return [
                $langKey => '<span class="flag-icon flag-icon-'.$langVal["flag-icon"].'"></span> '.$langVal["display"],
            ];
        })->toArray();
    }

    public static function getTemplateViewList()
    {
        $overrideDirectory = resource_path('views/vendor/vb-email-templates/email');
        $packageDirectory = dirname(view(config('email-templates.template_view_path').'.default')->getPath());

        $directories = [$overrideDirectory, $packageDirectory];

        $filenamesArray = collect($directories)
            ->filter(function ($directory) {
                return file_exists($directory);
            })
            ->flatMap(function ($directory) {
                return self::getFiles($directory, $directory);
            })
            ->unique()
            ->values()
            ->toArray();

        return array_combine($filenamesArray, $filenamesArray);
    }

    /**
     * Recursively get all files in a directory and children
     */
    private static function getFiles($dir, $basepath)
    {
        $files = $subdirs = $subFiles = [];

        if ($handle = opendir($dir)) {
            while (false !== ($entry = readdir($handle))) {
                if ($entry == "." || $entry == "..") {
                    continue;
                }
                if (substr($entry, 0, 1) == '_') {
                    continue;
                }
                $entryPath = $dir.'/'.$entry;
                if (is_dir($entryPath)) {
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

    public static function getRecipients()
    {
        return collect(config('email-templates.recipients'))->mapWithKeys(function ($recipient) {
            $splitNamespace = explode('\\', $recipient);
            $className = end($splitNamespace); // Get the class name without namespace

            return [$className => $recipient]; // Use class name as key and full class name as value
        })->toArray();
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
                        // ->action('createMailClass'),
                        ->action(function ($record) {
                            $createMailableHelper = app(\Visualbuilder\EmailTemplates\Contracts\CreateMailableInterface::class);
                            $notify = $createMailableHelper->createMailable($record);
                            // dd($record);
                            Notification::make()
                                ->title($notify->title)
                                ->icon($notify->icon)
                                ->iconColor($notify->icon_color)
                                ->send();
                        }),
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
