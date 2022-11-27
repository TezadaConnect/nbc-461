<!-- Generate Report Modal -->
<div class="modal fade" id="GenerateReport" tabindex="-1" aria-labelledby="GenerateReportLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="GenerateReportLabel">Export Report</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="{{ route('report.generate.index', $data->id ?? '') }}" method="post" id="generate_form">
                    @csrf
                    @if ($level == 'individual')
                        @if (in_array(1, $roles) && in_array(3, $roles))
                        <input type="hidden" name="level" value="{{ $level }}">
                        <div class="form-group">
                            <label for="type">Format</label>
                            <select name="type" id="type" class="form-control" required>
                                <option value="" selected disabled>Choose...</option>
                                <option value="academic" {{ in_array(1, $roles) && !in_array(3, $roles) ? 'selected' : '' }}>Academic</option>
                                <option value="admin" {{ in_array(3, $roles) && !in_array(1, $roles) ? 'selected' : '' }}>Admin</option>
                            </select>
                        </div>
                        @else
                        <input type="hidden" name="level" value="{{ $level }}">
                        <select hidden name="type" id="type" class="form-control" required>
                            <option value="" selected disabled>Choose...</option>
                            <option value="academic" {{ in_array(1, $roles) && !in_array(3, $roles) ? 'selected' : '' }}>Academic</option>
                            <option value="admin" {{ in_array(3, $roles) && !in_array(1, $roles) ? 'selected' : '' }}>Admin</option>
                        </select>
                        @endif
                    @else
                    <input type="hidden" name="level" value="{{ $level }}">
                    <div class="form-group">
                        <label for="type">Format</label>
                        <select name="type" id="type" class="form-control" required>
                            <option value="" selected disabled>Choose...</option>
                            <option value="academic">Academic</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    @endif
                    <!-- CBCO (College/Branch/Campus/Office) -->
                    @if($level == "individual" || $special_type == 'sector' || $special_type == 'ipqmso')
                    <div class="form-group">
                        <label for="cbco">College/Branch/Campus/Office</label>
                        <select name="cbco" id="cbco" class="form-control" required>
                            <option value="" selected disabled>Choose...</option>
                            @foreach ($colleges as $college)
                                <option value="{{ $college->id }}">{{$college->name}}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                    <div class="form-group">
                        <input type="hidden" name="quarter_generate" id="quarter_generate" class="form-control">
                    </div>
                    <div class="form-group">
                        <input type="hidden" name="year_generate" id="year_generate" class="form-control" >
                    </div>
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
        var max = {!! json_encode($year) !!};
        var min = 0;
        var diff = max-2022;
        min = max-diff;
        select = document.getElementById('year_generate');

        var year = {!! json_encode($year) !!};
        for (var i = max; i >= min; i--) {
            select.append(new Option(i, i));
            if (year == i) {
                document.getElementById("year_generate").value = i;
            }
        }
        
        var special = '{{ $special_type ?? "" }}';
        if(special != ''){
            $(document).on('change', '#cbco', function() {
                var collegeID = $('#cbco option:selected').val();
                var url = "{{ route('report.generate.index', ':id') }}";
                var replace = url.replace(':id', collegeID);
                $('#generate_form').attr('action', replace);
            });
        }
    </script>
@endpush