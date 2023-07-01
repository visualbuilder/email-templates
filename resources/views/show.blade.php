<h2 class="filament-header-heading text-2xl font-bold tracking-tight">Preview Email</h2>

<table class="table-auto border border-slate-400 p-3 mb-6 block">
    <tbody>
    <tr>
        <th>Send From</th>
        <td class="pl-4">{{$this->record->from}} </td>
    </tr>
    <tr>
        <th>Subject</th>
        <td class="pl-4">{{$this->record->subject}}</td>
    </tr>
    <tr>
        <th>PreHeader</th>
        <td class="pl-4">{{$this->record->preheader}}</td>
    </tr>
    </tbody>
</table>
<hr>
<iframe style="width: 100%; height: 800px" src="{{ route('email-template.preview',$this->record) }}">Your browser isn't compatible</iframe>
