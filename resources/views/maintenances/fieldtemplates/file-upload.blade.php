<div class="{{ $fieldInfo->size }} {{ $fieldInfo->name }} mb-2" id="upload-document">
    <div class="form-group">
        <label class="font-weight-bold">{{ $fieldInfo->label }}</label><span style='color: red'>{{ ($fieldInfo->required == 1) ? " *" : '' }}</span>
        @if (isset($fieldInfo->h_r_i_s_form_id))
            @if ($fieldInfo->name == 'document' || $fieldInfo->name == 'documentSO' || $fieldInfo->name == 'documentCert' || $fieldInfo->name == 'documentPic')
                <br>
                <span role="alert">
                    Note: Attachments should be in <strong>JPEG/JPG, PNG, or PDF</strong> format and less than <strong>500kb</strong> in file size.
                </span>
                <br>
            @endif
        @endif
        <input type="file"
            class="{{ $errors->has($fieldInfo->name) ? 'is-invalid' : '' }} mt-2"
            name="{{ $fieldInfo->name }}"
            id="{{ $fieldInfo->name }}"
            {{ ($fieldInfo->required == 1) ? 'required' : '' }}
            accept="image/jpeg, application/pdf, image/png, image/jpg, image/pjpeg, image/jfif, image/pjp, image/x-png"
        >

        @error($fieldInfo->name)
            <span class='invalid-feedback' role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>
</div>

<script>
    $('#documentSO').on('change', function(event){
        var files = event.target.files
        var extension = files[0].type
        if(extension == "text/html" || extension == "application/xhtml+xml" || extension == "application/xml"){
            alert("Invalid file type! Only allows JPG/JPEG, PNG, and PDF file formats.");
        }
    });
    $('#documentCert').on('change', function(event){
        var files = event.target.files
        var extension = files[0].type
        if(extension == "text/html" || extension == "application/xhtml+xml" || extension == "application/xml"){
            alert("Invalid file type! Only allows JPG/JPEG, PNG, and PDF file formats.");
        }
    });
    $('#documentPic').on('change', function(event){
        var files = event.target.files
        var extension = files[0].type
        if(extension == "text/html" || extension == "application/xhtml+xml" || extension == "application/xml"){
            alert("Invalid file type! Only allows JPG/JPEG, PNG, and PDF file formats.");
        }
    });
</script>
