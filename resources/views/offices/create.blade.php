<x-app-layout>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <h3>Set Up Your Account</h3>
                <p>
                    <a class="back_link" href="{{ route('account') }}"><i class="bi bi-chevron-double-left"></i>Back to my account.</a>
                </p>
                <div class="card">
                    <div class="card-body">
                        <form action="{{ route('offices.store') }}" method="post">
                            @csrf
                            <div class="row">
                                <div class="col-md-12">
                                    *Assign your role and designated college/branch/campus/office based on your reporting on IPCR/OPCR.
                                    <hr>
                                </div>
                                <div class="col-md-12">
                                    <label for="">I am a/an <strong>{{ $role }}*</strong> of:</label>
                                    <select name="cbco[]" id="cbco" required>
                                        <option value="">Choose...</option>
                                    </select>
                                    <span class="text-danger">This is required</span>
                                    <hr>
                                </div>
                                <div class="col-md-12">
                                    Please check below if this definition applies to you: <br>
                                    @if ($role == "Admin")
                                        <em>Admin with Teaching Load</em> refers to an admin personnel who also works as a faculty member in the university. They are reporting Admin QAR to their office AND Academic QAR to their college/branch/campus.
                                    @else
                                        <em>Faculty with Admin Designation</em> refers to a faculty member who holds a position as admin in an office. They are reporting Academic QAR to their college/branch/campus AND Admin QAR to their office. It also applies to the faculty assistants to office.
                                    @endif
                                    <br><br>
                                    <div class="form-group input-group-md">
                                        <input type="checkbox" name="yes" id="yes" {{ $existingCol2 != null ? 'checked' : '' }}>
                                        <label for="yes">I am a/an <strong>{{ $role == "Admin" ? 'Admin with Teaching Load' : 'Faculty with Admin Designation' }}*</strong></label>
                                        @if ($role == "Admin")
                                            <!-- If the current role is admin, then the designee type is faculty -->
                                            <!-- Role in seeder -->
                                            <input type="hidden" name="role" value="3"> 
                                            <!-- Role as type -->
                                            <input type="hidden" name="role_type" value="A">
                                            <input type="hidden" name="designee_type" value="F">
                                        @elseif ($role == "Faculty")
                                            <input type="hidden" name="role" value="1">
                                            <input type="hidden" name="role_type" value="F">
                                            <input type="hidden" name="designee_type" value="A">
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-12 designation-div">
                                    <label for="designee_cbco">If yes, please select the College/Branch/Campus <strong>{{ $role == "Admin" ? 'you work as Faculty' : '/Office you work as Admin' }}*</strong>:</label>
                                    <select name="designee_cbco[]" id="designee_cbco">
                                        <option value="">Choose...</option>
                                    </select>
                                    <span class="text-danger" id="designation_error">This is required</span>
                                    <x-jet-input-error for="designee_cbco"></x-jet-input-error>
                                </div>
                                <div class="col-md-12 mt-3">
                                    <div class="mb-0">
                                        <div class="d-flex justify-content-end align-items-baseline">
                                            <a href="{{ session('url') ? url(session('url')) : route('account') }}" class="btn btn-secondary mr-2">Cancel</a>
                                            <button type="submit" id="submit" class="btn btn-success">Save</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="{{ asset('dist/selectize.min.js') }}"></script>
    <script>
        $(function() {
            $('.designation-div').hide();
            $("#cbco").selectize({
                maxItems: null,
                valueField: 'id',
                labelField: 'name',
                sortField: "name",
                searchField: "name",
                options: @json($cbco),
                items: @json($existingCol),
            });
            $("#designee_cbco").selectize({
                maxItems: null,
                valueField: 'id',
                labelField: 'name',
                sortField: "name",
                searchField: "name",
                options: @json($cbco),
                items: @json($existingCol2),
            });

            if($('#yes').is(':checked')){
                $('.designation-div').show();
                $('#designee_cbco').removeAttr('disabled');
                $('#designee_cbco').attr('required', true);
                $('#designation_error').show();
            } else {
                $('.designation-div').hide();
                $('#designee_cbco').removeAttr('required');
                $('#designee_cbco').attr('disabled', true);
                $('#designation_error').hide();
            }
        });
    </script>
    <script>
       $('#yes').on('change', function() {
            if( $('#yes').is(':checked')){
                $('.designation-div').show();
                $('#designee_cbco').removeAttr('disabled');
                $('#designee_cbco').attr('required', true);
                $('#designation_error').show();
            } else {
                $('.designation-div').hide();
                $('#designee_cbco').removeAttr('required');
                $('#designee_cbco').attr('disabled', true);
                $('#designation_error').hide();
            }
        });

    </script>
    @endpush
</x-app-layout>
