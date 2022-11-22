<x-app-layout>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <h3>Set Up Your Account - Add departm</h3>
                <p>
                    <a class="back_link" href="{{ session('url') ? url(session('url')) : route('account') }}"><i class="bi bi-chevron-double-left"></i>Back to my account.</a>
                </p>
                <div class="card">
                    <div class="card-body">
                        <form action="{{ route('offices.storeDepartment') }}" method="post">
                            @csrf
                            <div class="row">
                                <div class="col-md-12">
                                    *Assign your department/section based on your reporting on IPCR/OPCR.
                                    <hr>
                                </div>
                                <div class="col-md-12">
                                    <label for="">Departments/Sections where I commit accomplishments</label>
                                    <select name="department[]" id="department" required>
                                        <option value="">Choose...</option>
                                    </select>
                                    <span class="text-danger">This is required</span>
                                    <hr>
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
            $("#department").selectize({
                maxItems: null,
                valueField: 'id',
                labelField: 'name',
                sortField: "name",
                searchField: "name",
                options: @json($departments),
                items: @json($departmentRecordIDs),
            });
        });
    </script>
    @endpush
</x-app-layout>
