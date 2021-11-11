
<div class="{{ $fieldInfo->size }}">
    <div class="form-group">
        <label>{{ $fieldInfo->label }}</label><?php if ($fieldInfo->required == 1) { echo "<span style='color: red'> *</span>"; } ?>

        <textarea name="{{ $fieldInfo->name }}" id="{{ $fieldInfo->name }}"  class="form-control" 
                placeholder="{{ $fieldInfo->placeholder }}" {{ ($fieldInfo->required == 1) ? 'required' : '' }}
                @switch($fieldInfo->visibility)
                    @case(2)
                        {{ 'readonly' }}
                        @break
                    @case(3)
                        {{ 'disabled' }}
                        @break
                    @case(2)
                        {{ 'hidden' }}
                        @break
                    @default
                        
                @endswitch>{{ $value }}</textarea>

    </div>
</div>