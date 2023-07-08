<?php

namespace Visualbuilder\EmailTemplates\Resources\EmailTemplateResource\Pages;

use Filament\Forms\Components\Card;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Pages\Actions\EditAction;
use Filament\Resources\Form;
use Filament\Resources\Pages\EditRecord;
use Filament\Resources\Pages\Page;

use FilamentTiptapEditor\TiptapEditor;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Str;
use Visualbuilder\EmailTemplates\Components\SelectLanguage;
use Visualbuilder\EmailTemplates\Resources\EmailTemplateResource;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Components\TextInput;

class PreviewEmailTemplate extends ViewRecord
{

    protected static string $resource = EmailTemplateResource::class;

    public function getTitle(): string
    {
        return __('vb-email-templates::email-templates.general-labels.preview-email', ['label'=>$this->record->name]);
    }

    protected $tokenHelper;

    public $iframe;
    public $src;

    public function __construct()
    {
        parent::__construct();
        $this->tokenHelper = app(\Visualbuilder\EmailTemplates\Contracts\TokenHelperInterface::class);
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        foreach ($data as $key=>$value){
           $data[$key] = $this->tokenHelper->replaceTokens($value,$this);
        }

        return $data;
    }



    protected function getForms(): array
    {
        return [
            'form' => $this->makeForm()
                           ->context('view')
                           ->model($this->getRecord())
                           ->schema($this->getFormSchema())
                           ->statePath('data')
                           ->inlineLabel(config('filament.layout.forms.have_inline_labels')),
        ];
    }
}
