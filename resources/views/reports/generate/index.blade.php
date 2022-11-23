<!-- Generate Report Modal -->
<div class="modal fade" id="GenerateReport" tabindex="-1" aria-labelledby="GenerateReportLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="GenerateReportLabel">Export Report</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                @if ($generatePerson != "individual")
                <div class="alert alert-success" id="exportInstruction" role="alert">
                    Select the <strong>format</strong> to be exported.
                </div>
                @endif
                <form action="{{ route('report.generate.index', $data->id ?? '') }}" method="post" id="generate_form">
                    @csrf
                    <input type="hidden" name="generatePerson" value="{{ $generatePerson }}">
                    @if ($generatePerson == "individual")
                        @if (in_array(1, $roles) && in_array(3, $roles))
                        <input type="hidden" name="level" value="individual">
                        <div class="form-group">
                            <label for="type">Format</label>
                            <select name="type" id="type" class="form-control" required>
                                <option value="" selected disabled>Choose...</option>
                                <option value="academic" {{ in_array(1, $roles) && !in_array(3, $roles) ? 'selected' : '' }}>Academic</option>
                                <option value="admin" {{ in_array(3, $roles) && !in_array(1, $roles) ? 'selected' : '' }}>Admin</option>
                            </select>
                        </div>
                        @else
                        <input type="hidden" name="level" value="individual">
                        <select hidden name="type" id="type" class="form-control" required>
                            <option value="" selected disabled>Choose...</option>
                            <option value="academic" {{ in_array(1, $roles) && !in_array(3, $roles) ? 'selected' : '' }}>Academic</option>
                            <option value="admin" {{ in_array(3, $roles) && !in_array(1, $roles) ? 'selected' : '' }}>Admin</option>
                        </select>
                        @endif
                    @elseif ($generatePerson == "chair/chief")
                    <div class="form-group">
                        <label for="level">Level</label>
                        <select name="level" id="level" class="form-control" required>
                            <option value="individual" selected>Individual</option>
                            <option value="department" selected>Department/Section</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="type">Format</label>
                        <select name="type" id="type" class="form-control" required>
                            <option value="" selected disabled>Choose...</option>
                            <option value="academic">Academic</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="employee">Employee</label>
                        <span class="d-flex" tabindex="0" data-container="body" data-bs-placement="left" data-toggle="tooltip" title="Selection required for Individual-level report.">
                            <select name="employee" id="employee" class="form-control" required>
                                <option value="" selected disabled>Choose...</option>
                                @foreach ($employees as $employee)
                                    <option value="{{ $employee->id }}">{{ $employee->last_name.', '.$employee->first_name.' '.$employee->middle_name }}</option>
                                @endforeach
                            </select>
                        </span>
                    </div>
                    @elseif ($generatePerson == "dean/director")
                    <input type="hidden" name="cbco" value="{{ $data->id }}">
                    <div class="form-group">
                        <label for="level">Level</label>
                        <select name="level" id="level" class="form-control" required>
                            <option value="individual" selected>Individual</option>
                            <option value="department" selected>Department/Section</option>
                            <option value="college" selected>College/Branch/Campus/Office</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="type">Format</label>
                        <select name="type" id="type" class="form-control" required>
                            <option value="" selected disabled>Choose...</option>
                            <option value="academic">Academic</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="employee">Employee</label>
                        <span class="d-flex" tabindex="0" data-container="body" data-bs-placement="left" data-toggle="tooltip" title="Selection required for Individual-level report.">
                            <select name="employee" id="employee" class="form-control" required>
                                <option value="" selected disabled>Choose...</option>
                                @foreach ($employees as $employee)
                                    <option value="{{ $employee->id }}">{{ $employee->last_name.', '.$employee->first_name.' '.$employee->middle_name }}</option>
                                @endforeach
                            </select>
                        </span>
                    </div>
                    <div class="form-group">
                        <label for="department">Department/Section</label>
                        <span class="d-flex" tabindex="0" data-container="body" data-bs-placement="left" data-toggle="tooltip" title="Selection required for Department-level report.">
                            <select name="department" id="department" class="form-control" required>
                                <option value="" selected disabled>Choose...</option>
                                @foreach ($departments as $department)
                                    <option value="{{ $department->id }}">{{ $department->name }}</option>
                                @endforeach
                            </select>
                        </span>
                    </div>
                    @endif
                    @if (in_array($generatePerson, ['individual', 'chair/chief']))
                    <div class="form-group">
                        <label for="cbco">College/Branch/Campus/Office</label>
                        <span class="d-flex" tabindex="0" data-container="body" data-bs-placement="left" data-toggle="tooltip" title="Selection required for College-level report.">
                            <select name="cbco" id="cbco" class="form-control" required>
                                <option value="" selected disabled>Choose...</option>
                            </select>
                        </span>
                    </div>
                    @endif
                    <select hidden name="quarterGenerate" id="quarterGenerate" class="form-control">
                        <option value="1" {{$quarter == 1 ? 'selected' : ''}} class="quarter">1</option>
                        <option value="2" {{$quarter == 2 ? 'selected' : ''}} class="quarter">2</option>
                        <option value="3" {{$quarter == 3 ? 'selected' : ''}} class="quarter">3</option>
                        <option value="4" {{$quarter == 4 ? 'selected' : ''}} class="quarter">4</option>
                    </select>
                    <select hidden name="quarterGenerate2" id="quarterGenerate2" class="form-control">
                        <option value="1" {{$quarter2 == 1 ? 'selected' : ''}} class="quarter">1</option>
                        <option value="2" {{$quarter2 == 2 ? 'selected' : ''}} class="quarter">2</option>
                        <option value="3" {{$quarter2 == 3 ? 'selected' : ''}} class="quarter">3</option>
                        <option value="4" {{$quarter2 == 4 ? 'selected' : ''}} class="quarter">4</option>
                    </select>
                    <select hidden name="yearGenerate" id="yearGenerate" class="form-control" value="">
                    </select>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary">Export</button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        var max = new Date().getFullYear();
        var min = 0;
        var diff = max-2022;
        min = max-diff;
        select = document.getElementById('yearGenerate');

        var year = {!! json_encode($year) !!};
        for (var i = max; i >= min; i--) {
            select.append(new Option(i, i));
            if (year == i) {
                document.getElementById("yearGenerate").value = i;
            }
        }
    </script>
    <script>
        $('#department').removeAttr('required');
        $('#department').attr('disabled', true);
        $('#employee').removeAttr('required');
        $('#employee').attr('disabled', true);
        if ("{{ $generatePerson }}" == "individual"){
            $('#cbco').removeAttr('disabled');
            $('#cbco').attr('required', true);
        } else{
            $('#cbco').removeAttr('required');
            $('#cbco').attr('disabled', true);
        }
        var alert = document.getElementById('exportInstruction');

        $('#level').on('change', function (){
            if($(this).val() == 'department'){
                $('#department').removeAttr('disabled');
                $('#department').attr('required', true);
                $('#employee').removeAttr('required');
                $('#employee').attr('disabled', true);
                $('#cbco').removeAttr('required');
                $('#cbco').attr('disabled', true);
                $('#employee').val('');
                $('#cbco').val('');
                if ("{{ $generatePerson }}" != "chair/chief")
                    alert.innerHTML = 'Select the <strong>format and department/section</strong> to be exported.';
            }
            else if($(this).val() == 'individual'){
                $('#department').removeAttr('required');
                $('#department').attr('disabled', true);
                $('#department').val('');
                $('#employee').removeAttr('disabled');
                $('#employee').attr('required', true);
                $('#cbco').removeAttr('disabled');
                $('#cbco').attr('required', true);
                if ("{{ $generatePerson }}" == "chair/chief")
                    alert.innerHTML = 'Select the <strong>format, employee, and college/branch/campus/office</strong> to be exported.';
                else
                    alert.innerHTML = 'Select the <strong>format, and employee</strong> to be exported.';
            } else if ($(this).val() == 'college') {
                $('#department').removeAttr('required');
                $('#department').attr('disabled', true);
                $('#department').val('');
                $('#employee').removeAttr('required');
                $('#employee').attr('disabled', true);
                $('#employee').val('');
                alert.innerHTML = 'Select the <strong>format</strong> to be exported.';
            }
        });
    </script>
@endpush