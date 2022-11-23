<!-- Generate Report Modal -->
<div class="modal fade" id="GenerateIPOLevel" tabindex="-1" aria-labelledby="GenerateIPOLevelLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="GenerateIPOLevelLabel">Export QAR</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="alert alert-success" id="exportInstruction" role="alert">
                    Select the <strong>format</strong> to be exported.
                </div>
                <form action="{{ route('report.generate.index', auth()->id()) }}" method="post" id="generate_ipo_form">
                    @csrf
                    <input type="hidden" name="generatePerson" value="ipo">
                    <div class="form-group">
                        <label for="level">Level</label>
                        <select name="level" id="level" class="form-control" required>
                            <option value="individual">Individual</option>
                            <option value="department">Department/Section</option>
                            <option value="college">College/Branch/Campus/Office</option>
                            <option value="sector">Sector</option>
                            <option value="ipo" selected>IPO</option>
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
                    <div class="form-group">
                        <label for="cbco">College/Branch/Campus/Office</label>
                        <span class="d-flex" tabindex="0" data-container="body" data-bs-placement="left" data-toggle="tooltip" title="Selection required for College-level report.">
                            <select name="cbco" id="cbco" class="form-control" required>
                                <option value="" selected disabled>Choose...</option>
                                
                            </select>
                        </span>
                    </div>
                    <div class="form-group">
                        <label for="sector">Sector</label>
                        <span class="d-flex" tabindex="0" data-container="body" data-bs-placement="left" data-toggle="tooltip" title="Selection required for Sector-level report.">
                            <select name="sector" id="sector" class="form-control" required>
                                <option value="" selected disabled>Choose...</option>
                                @foreach ($sectors as $sector)
                                    <option value="{{ $sector->id }}">{{ $sector->name }}</option>
                                @endforeach
                            </select>
                        </span>
                    </div>
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
                    <select hidden name="yearGenerate" id="yearGenerate" class="form-control" >
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
        
        $('#sector').removeAttr('required');
        $('#sector').attr('disabled', true);
        $('#cbco').removeAttr('required');
        $('#cbco').attr('disabled', true);
        $('#department').removeAttr('required');
        $('#department').attr('disabled', true);
        $('#employee').removeAttr('required');
        $('#employee').attr('disabled', true);
        var alert = document.getElementById('exportInstruction');

        $('#level').on('change', function (){
            if ($(this).val() == 'ipo') {
                //Univ. Funded
                $('#sector').removeAttr('required');
                $('#sector').attr('disabled', true);
                $('#sector').val('');
                $('#cbco').removeAttr('required');
                $('#cbco').attr('disabled', true);
                $('#cbco').val('');
                $('#department').removeAttr('required');
                $('#department').attr('disabled', true);
                $('#department').val('');
                $('#employee').removeAttr('required');
                $('#employee').attr('disabled', true);
                $('#employee').val('');
                alert.innerHTML = 'Select the <strong>format</strong> to be exported.';
            }
            else if($(this).val() == 'sector'){
                $('#sector').removeAttr('disabled');
                $('#sector').attr('required', true);
                $('#cbco').removeAttr('required');
                $('#cbco').attr('disabled', true);
                $('#cbco').val('');
                $('#department').removeAttr('required');
                $('#department').attr('disabled', true);
                $('#department').val('');
                $('#employee').removeAttr('required');
                $('#employee').attr('disabled', true);
                $('#employee').val('');
                alert.innerHTML = 'Select the <strong>format and sector</strong> to be exported.';
            }
            else if($(this).val() == 'department'){
                $('#sector').removeAttr('required');
                $('#sector').attr('disabled', true);
                $('#sector').val('');
                $('#cbco').removeAttr('required');
                $('#cbco').attr('disabled', true);
                $('#cbco').val('');
                $('#department').removeAttr('disabled');
                $('#department').attr('required', true);
                $('#employee').removeAttr('required');
                $('#employee').attr('disabled', true);
                $('#employee').val('');
                alert.innerHTML = 'Select the <strong>format and department/section</strong> to be exported.';
            }
            else if($(this).val() == 'individual'){
                $('#sector').removeAttr('required');
                $('#sector').attr('disabled', true);
                $('#sector').val('');
                $('#cbco').removeAttr('disabled');
                $('#cbco').attr('required', true);
                $('#department').removeAttr('required');
                $('#department').attr('disabled', true);
                $('#department').val('');
                $('#employee').removeAttr('disabled');
                $('#employee').attr('required', true);
                alert.innerHTML = 'Select the <strong>format, employee, and college/branch/campus/office</strong> to be exported.';
            } else if ($(this).val() == 'college') {
                //Univ. Funded
                $('#sector').removeAttr('required');
                $('#sector').attr('disabled', true);
                $('#sector').val('');
                $('#cbco').removeAttr('disabled');
                $('#cbco').attr('required', true);
                $('#cbco').val('');
                $('#department').removeAttr('required');
                $('#department').attr('disabled', true);
                $('#department').val('');
                $('#employee').removeAttr('required');
                $('#employee').attr('disabled', true);
                $('#employee').val('');
                alert.innerHTML = 'Select the <strong>format and college/branch/campus/office</strong> to be exported.';
            }
        });
    </script>
    <script>
        $('#level').on('change', function(){
            if ($('#level').val() == "individual"){
                $('#employee').on('change', function(){
                    var type = $('#type').val();
                    var employee = $('#employee').val();
                    $('#cbco').empty().append('<option selected="selected" disabled="disabled" value="">Choose...</option>');
                    var url = "{{ url('maintenances/colleges/name/:userTypeInitial/:userID') }}";
                    var api = url.replace(':userTypeInitial', type).replace(':userID', employee);
                    $.get(api, function (data){
                        if (data != '') {
                            data.forEach(function (item){
                                $("#cbco").append(new Option(item.name, item.id));
                            });
                        } else
                            $("#cbco").append('<option disabled="disabled" value="">No college/branch/campus/office has been tagged by the employee.</option>');
                    });
                });

                $('#type').on('change', function(){
                    var type = $('#type').val();
                    var employee = $('#employee').val();
                    $('#cbco').empty().append('<option selected="selected" disabled="disabled" value="">Choose...</option>');
                    var url = "{{ url('maintenances/colleges/name/:userTypeInitial/:userID') }}";
                    var api = url.replace(':userTypeInitial', type).replace(':userID', employee);
                    $.get(api, function (data){
                        if (data != '') {
                            data.forEach(function (item){
                                $("#cbco").append(new Option(item.name, item.id));
                            });
                        } else
                            $("#cbco").append('<option disabled="disabled" value="">No college/branch/campus/office has been tagged by the employee.</option>');
                    });
                });
            } else{
                var data = <?php echo json_encode($colleges); ?>;
                if (data != '') {
                    data.forEach(function (item){
                        $("#cbco").append(new Option(item.name, item.id));
                    });
                } else
                    $("#cbco").append('<option disabled="disabled" value="">No college/branch/campus/office has been tagged by the employee.</option>');
            }
        });
    </script>
@endpush