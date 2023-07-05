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
    <div class="filament-forms-select-component group flex items-center space-x-1 rtl:space-x-reverse">
        <div 
            x-data="{
                state: $wire.entangle('{{ $getStatePath() }}').defer,
                options: {{json_encode($getOptions())}},
                open: false,
                toggle() {
                    if (this.open) {
                        return this.close()
                    }
    
                    this.$refs.button.focus()
    
                    this.open = true
                },
                close(focusAfter) {
                    if (! this.open) return
    
                    this.open = false
    
                    focusAfter && focusAfter.focus()
                }
            }"
                    x-on:keydown.escape.prevent.stop="close($refs.button)"
                    x-on:focusin.window="! $refs.panel.contains($event.target) && close()"
                    x-id="['dropdown-button']"
                    class="relative min-w-0 flex-1"
            >
                <!-- Button -->
                <button
                        x-ref="button"
                        x-on:click="toggle()"
                        :aria-expanded="open"
                        :aria-controls="$id('dropdown-button')"
                        type="button"
                        class="flex items-center gap-x-2.5 filament-forms-input w-full rounded-lg text-gray-900 shadow-sm outline-none transition duration-75 border focus:border-primary-500 focus:ring-1 focus:ring-inset focus:ring-primary-500 disabled:opacity-70 border-gray-300 px-2 py-2"
                >
                    <template x-if="state">
                        <span x-html="options[state]" class="w-full text-left"></span>
                    </template>

                    <template x-if="!state">
                        <span class="w-full text-left">Select an option</span>
                    </template>
    
                    <!-- Heroicon: chevron-down -->
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </button>
    
                <!-- Panel -->
                <div
                        x-ref="panel"
                        x-show="open"
                        x-transition.origin.top.left
                        x-on:click.outside="close($refs.button)"
                        :id="$id('dropdown-button')"
                        style="display: none;"
                        class="absolute left-0 mt-2 w-full rounded-md bg-white shadow-md"
                >
                    @foreach($getOptions() as $value => $label)
                        <a href="#" x-on:click="state = '{{$value}}'; open = false" class="flex items-center gap-2 w-full first-of-type:rounded-t-md last-of-type:rounded-b-md px-4 py-2.5 text-left text-md hover:bg-gray-50 disabled:text-gray-500">
                            {!! $label !!} 
                        </a>
                    @endforeach
                    
{{--                    @dump($options);--}}
                </div>
        </div>
    </div>
</x-dynamic-component>
