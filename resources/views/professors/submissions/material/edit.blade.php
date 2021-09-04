<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 font-weight-bold">
            {{ __('F.1. Instructional Material, Reference/Text Book, Module, Monographs > Edit') }}
        </h2>
    </x-slot>
    <div class="container">
        <div class="row mb-3">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        @if ($message = Session::get('success'))
                            <div class="alert alert-success">
                                {{ $message }}
                            </div>
                        @endif
                        <div class="row">
                            <div class="col-md-12">
                                <a href="{{ route('professor.submissions.material.show', $material->id) }}" class="btn btn-secondary mb-2 mr-2"><i class="fas fa-arrow-left mr-2"></i> Back</a>
                            </div>
                        </div>
                        <hr>
                        <form action="{{ route('professor.submissions.material.update', $material->id) }}" method="POST">
                            @csrf
                            @method('put')
                            <div class="row">
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <x-jet-label value="{{ __('Department') }}" />

                                        <select name="department" id="department" class="form-control custom-select {{ $errors->has('department') ? 'is-invalid' : '' }}" autofocus autocomplete="department">
                                            <option value="" selected disabled>Choose...</option>
                                            @foreach($departments as $department)
                                            <option value="{{ $department->id }}" {{ ((old('department', $material->department_id) == $department->id) ? 'selected' : '' )}}>{{ $department->name }}</option>    
                                            @endforeach
                                        </select>

                                        <x-jet-input-error for="department"></x-jet-input-error>
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <x-jet-label value="{{ __('Category') }}" />

                                        <x-jet-input :value="old('category', $material->category)" class="{{ $errors->has('category') ? 'is-invalid' : '' }}" type="text" name="category" autofocus autocomplete="category" />

                                        <x-jet-input-error for="category"></x-jet-input-error>
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <x-jet-label value="{{ __('Level') }}" />

                                        <select name="level" id="level" class="form-control custom-select {{ $errors->has('level') ? 'is-invalid' : '' }}" autofocus autocomplete="level">
                                            <option value="" selected disabled>Choose...</option>
                                            @foreach($levels as $level)
                                            <option value="{{ $level->id }}" {{ ((old('level', $material->level_id) == $level->id) ? 'selected' : '' )}}>{{ $level->name }}</option>    
                                            @endforeach
                                        </select>

                                        <x-jet-input-error for="level"></x-jet-input-error>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-lg-8">
                                    <div class="form-group">
                                        <x-jet-label value="{{ __('Title') }}" />

                                        <x-jet-input :value="old('title', $material->title)" class="{{ $errors->has('title') ? 'is-invalid' : '' }}" type="text" name="title" autofocus autocomplete="title" />

                                        <x-jet-input-error for="title"></x-jet-input-error>
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <x-jet-label value="{{ __('Author/s/Compiler/s') }}" />

                                        <x-jet-input :value="old('author', $material->author)" class="{{ $errors->has('author') ? 'is-invalid' : '' }}" type="text" name="author" autofocus autocomplete="author" />

                                        <x-jet-input-error for="author"></x-jet-input-error>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-lg-12">
                                    <h5 id=textHome style="color: maroon"><b>Editors/Referees </b></h5>
                                </div>
                                <div class="col-lg-8">
                                    <div class="form-group">
                                        <x-jet-label value="{{ __('Name') }}" />

                                        <x-jet-input :value="old('editorname', $material->editor_name)" class="{{ $errors->has('editorname') ? 'is-invalid' : '' }}" type="text" name="editorname" autofocus autocomplete="editorname" />

                                        <x-jet-input-error for="editorname"></x-jet-input-error>
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <x-jet-label value="{{ __('Profession') }}" />

                                        <x-jet-input :value="old('editorprofession', $material->editor_profession)" class="{{ $errors->has('editorprofession') ? 'is-invalid' : '' }}" type="text" name="editorprofession" autofocus autocomplete="editorprofession" />

                                        <x-jet-input-error for="editorprofession"></x-jet-input-error>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-lg-3">
                                    <div class="form-group">
                                        <x-jet-label value="{{ __('Volume No') }}" />

                                        <x-jet-input :value="old('volume', $material->volume)" class="{{ $errors->has('volume') ? 'is-invalid' : '' }}" type="text" name="volume" autofocus autocomplete="volume" />

                                        <x-jet-input-error for="volume"></x-jet-input-error>
                                    </div>
                                </div>
                                <div class="col-lg-3">
                                    <div class="form-group">
                                        <x-jet-label value="{{ __('Issue No') }}" />

                                        <x-jet-input :value="old('issue', $material->issue)" class="{{ $errors->has('issue') ? 'is-invalid' : '' }}" type="text" name="issue" autofocus autocomplete="issue" />

                                        <x-jet-input-error for="issue"></x-jet-input-error>
                                    </div>
                                </div>
                                <div class="col-lg-3">
                                    <div class="form-group">
                                        <x-jet-label value="{{ __('Date of Publication') }}" />

                                        <x-jet-input :value="old('datepublication', $material->date_publication)" class="{{ $errors->has('datepublication') ? 'is-invalid' : '' }}" type="text" id="date-publication" name="datepublication" autofocus autocomplete="datepublication" />

                                        <x-jet-input-error for="datepublication"></x-jet-input-error>
                                    </div>
                                </div>
                                <div class="col-lg-3">
                                    <div class="form-group">
                                        <x-jet-label value="{{ __('Copyright Registration No ') }}" />

                                        <x-jet-input :value="old('copyright', $material->copyright)" class="{{ $errors->has('copyright') ? 'is-invalid' : '' }}" type="text" name="copyright" autofocus autocomplete="copyright" />

                                        <x-jet-input-error for="copyright"></x-jet-input-error>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <x-jet-label value="{{ __('Date Started') }}" />

                                        <x-jet-input :value="old('datestarted', $material->date_started)" class="{{ $errors->has('datestarted') ? 'is-invalid' : '' }}" type="text" id="date-start" name="datestarted" autofocus autocomplete="datestarted" />

                                        <x-jet-input-error for="datestarted"></x-jet-input-error>
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <x-jet-label value="{{ __('Date Completed') }}" />

                                        <x-jet-input :value="old('datecompleted', $material->date_completed)" class="{{ $errors->has('datecompleted') ? 'is-invalid' : '' }}" type="text" id="date-completed" name="datecompleted" autofocus autocomplete="datecompleted" />

                                        <x-jet-input-error for="datecompleted"></x-jet-input-error>

                                    </div>
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Upload Images or Documents</label>
                                        <input type="file" 
                                            class="filepond mb-n1"
                                            name="document[]"
                                            multiple
                                            data-max-file-size="5MB"
                                            data-max-files="10"/>
                                            @error('document')
                                                <small class="text-danger" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </small>
                                            @enderror
                                        <p class="mt-1"><small>Maximum size per file: 5MB. Maximum number of files: 10.</small></p>
                                        <p class="mt-n4"><small>Accepts PDF, JPEG, and PNG file formats.</small></p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <x-jet-label value="{{ __('Description of Supporting Documents') }}" />

                                        <textarea class="form-control {{ $errors->has('documentdescription') ? 'is-invalid' : '' }}" name="documentdescription" cols="30" rows="5" autofocus autocomplete="documentdescription">{{ old('documentdescription', $material->document_description) }}</textarea>

                                        <x-jet-input-error for="documentdescription"></x-jet-input-error>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <div class="mb-0">
                                <div class="d-flex justify-content-end align-items-baseline">
                                    <a href="{{ route('professor.submissions.invention.show', $invention->id) }}" class="btn btn-outline-danger mr-2">
                                        CANCEL
                                    </a>
                                    <x-jet-button>
                                        {{ __('Submit') }}
                                    </x-jet-button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <h5 id="textHome" style="color:maroon">Supporting Documents</h5>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <h6 style="color:maroon"><i class="far fa-file-alt mr-2"></i>Documents</h6>
                                <div class="row">
                                    @if (count($documents) > 0)
                                        @foreach ($documents as $document)
                                            @if(preg_match_all('/application\/\w+/', \Storage::mimeType('documents/'.$document->filename)))
                                                <div class="col-md-12 mb-3">
                                                    <div class="card bg-light border border-maroon rounded-lg">
                                                        <div class="card-body">
                                                            <div class="row mb-3">
                                                                <div class="col-md-12">
                                                                    <div class="embed-responsive embed-responsive-1by1">
                                                                        <iframe  src="{{ route('document.view', $document->filename) }}" width="100%" height="500px"></iframe>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-md-12">
                                                                    <form action="{{   route('professor.material.file.delete', $document->submission_id)  }}" method="POST" onsubmit="return confirm('Are you sure?');">
                                                                        @csrf
                                                                        <input type="hidden" name="filename" value="{{ $document->filename }}">
                                                                        <button type="submit" class="btn btn-outline-danger btn-sm"><i class="far fa-trash-alt"></i> Delete</button>
                                                                    </form>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        @endforeach
                                    @else
                                        <div class="col-md-4 offset-md-4">
                                            <h6 class="text-center">No Documents Attached</h6>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6 style="color:maroon"><i class="far fa-image mr-2"></i>Images</h6>
                                <div class="row">
                                    @if(count($documents) > 0)
                                        @foreach ($documents as $document)
                                            @if(preg_match_all('/image\/\w+/', \Storage::mimeType('documents/'.$document->filename)))
                                                <div class="col-md-6 mb-3">
                                                    <div class="card bg-light border border-maroon rounded-lg">
                                                        <a href="{{ route('document.display', $document->filename) }}" data-lightbox="gallery" data-title="{{ $document->filename }}">
                                                            <img src="{{ route('document.display', $document->filename) }}" class="card-img-top img-resize"/>
                                                        </a>
                                                        <div class="card-body">
                                                            <table class="table table-sm my-n3 text-center">
                                                                <tr>
                                                                    <th>
                                                                        <form action="{{  route('professor.material.file.delete', $document->submission_id) }}" method="POST" onsubmit="return confirm('Are you sure?');">
                                                                            @csrf
                                                                            <input type="hidden" name="filename" value="{{ $document->filename }}">
                                                                            <button type="submit" class="btn btn-outline-danger btn-sm"><i class="far fa-trash-alt mr-2"></i> Remove</button>
                                                                        </form>
                                                                    </th>
                                                                </tr>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        @endforeach
                                    @else
                                        <div class="col-md-4 offset-md-4">
                                            <h6 class="text-center">No Documents Attached</h6>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @push('scripts')
        <script src="{{ asset('js/litepicker.js') }}"></script>
        <script src="https://cdn.jsdelivr.net/npm/litepicker/dist/plugins/mobilefriendly.js"></script>
        <script>
          
            const today = new Date();

            const picker = new Litepicker ({
                element: document.getElementById('date-start'),
                elementEnd: document.getElementById('date-completed'),
                singleMode: false,
                // allowRepick: true,
                resetButton: true,
                // numberOfColumns: 2,
                // numberOfMonths: 2,
                dropdowns: {
                    "minYear":2020,
                    "maxYear":null,
                    "months":true,
                    "years":true,
                },
                // firstDay : 0,
                plugins: ['mobilefriendly'],
                mobilefriendly: {
                  breakpoint: 480,
                },
            });

            const picker2 = new Litepicker ({
                element: document.getElementById('date-publication'),
                singleMode: true,
                // allowRepick: true,
                resetButton: true,
                // numberOfColumns: 2,
                // numberOfMonths: 2,
                dropdowns: {
                    "minYear":2020,
                    "maxYear":null,
                    "months":true,
                    "years":true,
                },
                // firstDay : 0,
                plugins: ['mobilefriendly'],
                mobilefriendly: {
                  breakpoint: 480,
                },
            });

            // picker.setDateRange(today, today, false);
        </script>
        <script>
            /*
            We want to preview images, so we need to register the Image Preview plugin
            */
            FilePond.registerPlugin(
	
                // encodes the file as base64 data
                FilePondPluginFileEncode,
                
                // validates the size of the file
                FilePondPluginFileValidateSize,
                
                // corrects mobile image orientation
                FilePondPluginImageExifOrientation,
                
                // previews dropped images
                FilePondPluginImagePreview,

                FilePondPluginFileValidateType,
                
            );

            // Create a FilePond instance
            const pondDocument = FilePond.create(document.querySelector('input[name="document[]"]'));
            // const pondImage = FilePond.create(document.querySelector('input[name="image[]"]'));
            pondDocument.setOptions({
                acceptedFileTypes: ['application/pdf', 'image/jpeg', 'image/png'],
                // stylePanelLayout: 'integrated',
                // styleItemPanelAspectRatio: '1',
                // imagePreviewHeight: 250,
                server: {
                    process: {
                        url: "/upload",
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        }
                    },
                    revert:{
                        url: "/remove",
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        }
                    },
                }
            });
            // pondImage.setOptions({
            //     acceptedFileTypes: ['image/*'],
            //     imagePreviewHeight: 50,
            //     server: {
            //         process: "/upload",
            //         revert: "/removeimage",
            //         headers: {
            //             'X-CSRF-TOKEN': '{{ csrf_token() }}',
            //         }
            //     }
            // });


        </script>
    @endpush
</x-app-layout>