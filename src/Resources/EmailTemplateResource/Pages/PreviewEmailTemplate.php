<?php

namespace Visualbuilder\EmailTemplates\Resources\EmailTemplateResource\Pages;

use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;

use Filament\Forms\Components\TextInput;

use Filament\Forms\Components\View;
use Filament\Forms\Form;
use Filament\Resources\Pages\ViewRecord;
use Visualbuilder\EmailTemplates\Components\Iframe;
use Visualbuilder\EmailTemplates\Models\EmailTemplate;
use Visualbuilder\EmailTemplates\Resources\EmailTemplateResource;

class PreviewEmailTemplate extends ViewRecord
{
    protected static string $resource = EmailTemplateResource::class;

    // protected static string $view = 'filament-panels::resources.pages.view-record';

    public function getTitle(): string
    {
        return __('vb-email-templates::email-templates.general-labels.preview-email', ['label' => $this->record->name]);
    }

    protected $tokenHelper;

    public $iframe;
    public $src;

    public function __construct()
    {
        $this->tokenHelper = app(\Visualbuilder\EmailTemplates\Contracts\TokenHelperInterface::class);
    }

    public function form(Form $form): Form
    {


        return  $form
                //->operation('view')
                // ->disabled()
                ->model($this->getRecord())
                ->statePath($this->getFormStatePath())
                ->columns($this->hasInlineLabels() ? 1 : 2)
                ->inlineLabel($this->hasInlineLabels())
             ->schema(
                 [
                     Section::make()
                         ->schema(
                             [
                                 Grid::make(['default' => 1, 'sm' => 1, 'md' => 2])
                                     ->schema(
                                         [
                                             Select::make('id')
                                                   ->options(EmailTemplate::all()->pluck('name', 'id'))
                                                   ->searchable()
                                                   ->label(__('vb-email-templates::email-templates.general-labels.template-name'))
                                                   ->reactive()
                                                   ->afterStateUpdated(function ($state) {
                                                       $this->redirectRoute('filament.resources.email-templates.view', $state);
                                                   }),

                                             TextInput::make('from')
                                                      ->label(__('vb-email-templates::email-templates.form-fields-labels.email-from'))
                                                      ->disabled(),
                                         ]
                                     ),
                                 Grid::make(['default' => 1])
                                     ->schema(
                                         [
                                             TextInput::make('subject')
                                                      ->label(__('vb-email-templates::email-templates.form-fields-labels.subject'))
                                                      ->disabled(),
                                             TextInput::make('preheader')
                                                      ->label(__('vb-email-templates::email-templates.form-fields-labels.header'))
                                                      ->hint(__('vb-email-templates::email-templates.form-fields-labels.header-hint'))
                                                      ->disabled(),
                                         ]
                                     ),
                                 Grid::make(['default' => 1])
                                     ->schema(
                                         [
                                           Iframe::make('preview', route('email-template.preview', $this->getRecord())),
                                         ]
                                     ),

                             ]
                         ),
                 ]
             );

    }

    public function getFormStatePath(): ?string
    {
        return 'data';
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        foreach ($data as $key => $value) {
            $data[$key] = $this->tokenHelper->replaceTokens($value, $this);
        }

        return $data;
    }
}
