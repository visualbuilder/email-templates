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
use Illuminate\View\View;
use Visualbuilder\EmailTemplates\Contracts\CreateMailableInterface;
use Visualbuilder\EmailTemplates\Contracts\FormHelperInterface;
use Visualbuilder\EmailTemplates\Models\EmailTemplate;
use Visualbuilder\EmailTemplates\Resources\EmailTemplateResource\Pages;

class EmailTemplateResource extends Resource
{
    protected static ?string $model = EmailTemplate::class;

    protected static ?string $navigationIcon = 'heroicon-o-envelope';

    public static function getNavigationGroup(): ?string
    {
        return config('email-templates.navigation.group');
    }

    public static function getNavigationSort(): ?int
    {
        return config('email-templates.navigation.sort');
    }

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

        $formHelper = app(FormHelperInterface::class);
        $templates = $formHelper->getTemplateViewOptions();
        $recipients = $formHelper->getRecipientOptions();


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
                                            ->options($formHelper->getLanguageOptions())
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
                        ->label("Build Class")
                        //Only show the button if the file does not exist
                        ->visible(function (EmailTemplate $record) {return ! $record->mailable_exists;})
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
                    Tables\Actions\ViewAction::make('Preview')
                        ->icon('heroicon-o-magnifying-glass')
                        ->modalContent(fn (EmailTemplate $record): View => view(
                            'vb-email-templates::forms.components.iframe',
                            ['record' => $record],
                        ))
                        ->modalHeading(fn (EmailTemplate $record): string => 'Preview Email: '.$record->name)
                        ->modalSubmitAction(false)
                        ->modalCancelAction(false)
                        ->slideOver(),

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
