{{-- fieldInfo --}}

<div class="{{ $fieldInfo->size }} mb-3">
    <div class="form-group">
        <label class="font-weight-bold" for="{{ $fieldInfo->name }}">{{ $fieldInfo->label }}</label><span style='color: red'>{{ ($fieldInfo->required == 1) ? " *" : '' }}</span>

        <input type="number" name="{{ $fieldInfo->name }}" id="{{ $fieldInfo->name }}" value="{{ (old($fieldInfo->name) == '') ?  (is_null($value) ? '0': str_replace ( ',', '', $value)) : old($fieldInfo->name) }}" class="{{ $errors->has($fieldInfo->name) ? 'is-invalid' : '' }} form-control form-validation"
                placeholder="{{ $fieldInfo->placeholder }}" {{ ($fieldInfo->required == 1) ? 'required' : '' }}
                @switch($fieldInfo->visibility)
                    @case(2)
                        {{ 'readonly' }}
                        @break
                    @case(3)
                        {{ 'disabled' }}
                        @break
                    @case(4)
                        {{ 'hidden' }}
                        @break
                    @default

                @endswitch step=".01">

                @error($fieldInfo->name)
                    <span class='invalid-feedback' role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror

    </div>
</div>
