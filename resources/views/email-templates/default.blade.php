@inject('tokenHelper','Visualbuilder\EmailTemplates\Contracts\TokenHelperInterface')

<h2 class="filament-header-heading text-2xl font-bold tracking-tight">{{__('vb-email-templates::email-template-labels.general-labels.preview-email')}}</h2>

<table class="table-auto border border-slate-400 p-3 mb-6 block">
    <tbody>
    <tr>
        <th>{{__('vb-email-templates::email-template-labels.general-labels.send-from')}}</th>
        <td class="pl-4">{{$this->record->from}} </td>
    </tr>
    <tr>
        <th>{{__('vb-email-templates::email-template-labels.general-labels.subject')}}</th>
        <td class="pl-4">{{$tokenHelper->replaceTokens($this->record->subject,$this)}}</td>
    </tr>
    <tr>
        <th>{{__('vb-email-templates::email-template-labels.general-labels.pre-header')}}</th>
        <td class="pl-4">{{$tokenHelper->replaceTokens($this->record->preheader,$this)}}</td>
    </tr>
    </tbody>
</table>
<hr>
<iframe style="width: 100%; height: 800px" src="{{ route('email-template.preview',$this->record) }}">{{__('vb-email-templates::email-template-labels.general-labels.browser-not-compatible')}}</iframe>
