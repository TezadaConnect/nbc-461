<x-app-layout>
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h2 class="font-weight-bold mb-2">User Account</h2>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group input-group-sm">
                                    <label for="">Employee No.</label>
                                    <input type="text" readonly class="form-control" value="{{ $accountDetail[0]->EmpNo }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group input-group-sm">
                                    <label for="">Plantilla Position</label>
                                    <input type="text" readonly class="form-control" value="{{ $accountDetail[0]->PlantillaPosition }}">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                        <div class="col-md-3">
                                <div class="form-group input-group-sm">
                                    <label for="">Surname</label>
                                    <input type="text" readonly class="form-control" value="{{ $employeeDetail[0]->LName }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group input-group-sm">
                                    <label for="">First Name</label>
                                    <input type="text" readonly class="form-control" value="{{ $employeeDetail[0]->FName }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group input-group-sm">
                                    <label for="">Middle Name</label>
                                    <input type="text" readonly class="form-control" value="{{ $employeeDetail[0]->MName }}">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group input-group-sm">
                                    <label for="">Suffix</label>
                                    <input type="text" readonly class="form-control" value="{{ $employeeDetail[0]->EName }}">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="">Role</label>
                                    <input type="text" id="role" readonly class="form-control" value="{{ $roles }}">
                                </div>
                            </div>
                        </div>
                        @php $isEmployeeRecordEmpty = 0; @endphp
                        <div class="row">
                            <div class="col-md-12">
                                <table class="table table-hover table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Reporting Role/Designee</th>
                                            <th>College/Branch/Campus/Office of Designation</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($employeeTypeOfUser as $employee)
                                        <tr>
                                            <td>{{ $employee->type == 'A' ? 'Administrative' : 'Faculty' }}</td>
                                            <td>
                                                @forelse ($designations[$employee->type] as $college)
                                                    @if ($loop->last)
                                                        {{ $college->name }}
                                                    @else
                                                        {{ $college->name }},
                                                    @endif
                                                @empty
                                                    -
                                                @endforelse
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group" aria-label="button-group">
                                                    <a href="{{ route('offices.create') }}" type="button" class="btn btn-warning btn-sm">Edit College/Office</a>
                                                    <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteModal" data-bs-designation="{{ $employee->type }}">Delete</button>
                                                </div>
                                            </td>
                                        </tr>
                                        @empty
                                        @php $isEmployeeRecordEmpty = 1; @endphp
                                        <tr>
                                            <td>
                                                <a href="{{ route('offices.create') }}" type="button" class="btn btn-success mr-2">Add College/Office Designation</a>
                                            </td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        @if ($isEmployeeRecordEmpty == 0)
                        @php $isDeptRecordEmpty = 0; @endphp
                        <div class="row">
                            <div class="col-md-12">
                                <div class="table table-hovered table-bordered">
                                    <table>
                                        <thead>
                                            <tr>
                                                <th>Departments/Sections where to commit QAR</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>@forelse ($departmentNames as $dept)
                                                        @if ($loop->last)
                                                            {{ $dept->name }}
                                                        @else
                                                            {{ $dept->name }},
                                                        @endif
                                                    @empty
                                                        @php $isDeptRecordEmpty = 1; @endphp
                                                        <a href="{{ route('offices.addDepartment') }}" type="button" class="btn btn-success">Add Department/Section</a>
                                                    @endforelse</td>
                                                <td>
                                                    @if ($isDeptRecordEmpty == 0)
                                                    <a href="{{ route('offices.addDepartment') }}" type="button" class="btn btn-warning btn-sm">Edit Department/Section</a>
                                                    @endif
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        @endif
                        <hr>
                        <form action="{{ route('account.signature.save') }}" method="post">
                            @csrf
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="">Signature <small class="ml-2">Upload your digital signature to be attached in consolidated reports. </small></label>
                                        <input type="file"
                                        class="{{ $errors->has('document') ? 'is-invalid' : '' }} filepond mb-n1"
                                        name="document[]"
                                        id="document"
                                        data-max-file-size="10MB"
                                        data-max-files="10"
                                        required/>
                                        <p class="mt-1"><small>Accepts JPEG, and PNG file formats.</small></p>
                                        <button type="submit" id="submit" class="btn btn-success float-right">Save Signature</button>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    @if (!$user->signature == '')
                                        @if(preg_match_all('/image\/\w+/', \Storage::mimeType('documents/'.$user->signature)))

                                        <div class="card bg-light border border-maroon rounded-lg">
                                            <a href="{{ route('document.display', $user->signature) }}" data-lightbox="gallery" data-title="{{ $user->signature }}" target="_blank">
                                                <img src="{{ route('document.display', $user->signature) }}" class="card-img-top img-resize"/>
                                            </a>
                                        </div>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Remove Designation</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                        <h5 class="text-center">Are you sure you want to remove your designation record? This action cannot be undone.</h5>
                        <h5 id="designation" class="text-center font-weight-bold"></h5>
                        <form action="" id="delete_item" method="POST">
                            @csrf
                            @method('delete')
                    </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary mb-2" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger mb-2 mr-2">Delete</button>
                </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="{{ asset('dist/selectize.min.js') }}"></script>
    <script>
        $("#role").selectize({
        delimiter: ",",
        persist: false,
        create: function (input) {
            return {
            value: input,
            text: input,
            };
        },
        });
        $("#role")[0].selectize.lock();
    </script>
    <script>
        //Item to delete to display in delete modal
        var deleteModal = document.getElementById('deleteModal')
        deleteModal.addEventListener('show.bs.modal', function (event) {
          var button = event.relatedTarget
          var designee_type = button.getAttribute('data-bs-designation')
          var designation = deleteModal.querySelector('#designation')
          if (designee_type == 'A') {
              designation.textContent = 'Admin Designee'
          } else {
              designation.textContent = 'Faculty Designee'
          }

          var url = '{{ route("offices.destroy", ":designee_type") }}';
          url = url.replace(':designee_type', designee_type);
          document.getElementById('delete_item').action = url;
        });
    </script>
    <script>
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
        pondDocument.setOptions({
            acceptedFileTypes: ['image/jpeg', 'image/png'],

            server: {
                process: {
                    url: "{{ url('/upload') }}",
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    }
                },
            }
        });
    </script>
    @if (Session::has('incomplete_account'))
        <script>
            $(function(){
                $(window).on('load', function(){
                    $('#accountModal').modal('show');
                });
            });
        </script>
    @endif
    @endpush
</x-app-layout>