<x-dynamic-component
    :component="$getFieldWrapperView()"
    :id="$getId()"
    :label="$getLabel()"
    :label-sr-only="$isLabelHidden()"
    :helper-text="$getHelperText()"
    :hint="$getHint()"
    :hint-action="$getHintAction()"
    :hint-color="$getHintColor()"
    :hint-icon="$getHintIcon()"
    :required="$isRequired()"
    :state-path="$getStatePath()"
>
    <div x-data="{ state: $wire.entangle('{{ $getStatePath() }}').defer }">
        <span class="fi fi-gb"></span>
        <select class="selectpicker" data-width="fit">
            <option value=""> Select an option</option>

            <option value="en_GB" data-content='<span class="fi fi-gb"></span> British'>
                <span x-text="fi fi-gb"></span> 
                British
            </option>
                        
        </select>
    </div>
</x-dynamic-component>
