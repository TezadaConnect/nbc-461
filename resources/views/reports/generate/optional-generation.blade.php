<!-- Generate Report Modal -->
<div class="modal fade" id="GenerateIndiv" tabindex="-1" aria-labelledby="GenerateIndivLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="GenerateIndivLabel">Export Individual QAR</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="{{ route('report.generate.optional') }}" method="post" id="optional_form">
                    @csrf
                    <input type="hidden" name="level" value="individual">
                    <input type="hidden" name="type" value="optional">
                    <input type="hidden" name="delivery_unit" value="{{ $data->id }}">
                    <div class="form-group">
                        <label for="type">Employee</label>
                        <select name="user_id" id="user_id" class="form-control" required>
                            <option value="" selected disabled>Choose...</option>
                            @foreach($employees as $row)
                            <option value="{{ $row->user_id }}">{{ $row->last_name.', '.$row->first_name.' '.$row->middle_name }}</option>
                            @endforeach
                        </select>
                    </div>
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
        
    </script>
@endpush