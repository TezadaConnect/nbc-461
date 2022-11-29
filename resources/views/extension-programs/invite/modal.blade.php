<div class="modal fade" id="tagsModal" data-backdrop="static" tabindex="-1" aria-labelledby="tagsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="tagsModalLabel">Extension Tagged by Partner</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-bordered text-center" id="invitations_table">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Extension Partner</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($tags as $row)
                            <tr>
                                <td>{{ ($row->title_of_extension_program != null ? $row->title_of_extension_program : ($row->title_of_extension_project != null ? $row->title_of_extension_project : ($row->title_of_extension_activity != null ? $row->title_of_extension_activity : ''))) }}</td>
                                <td>{{ $row->last_name.', '.$row->first_name.' '.$row->middle_name.' '.$row->suffix }}</td>
                                <td>
                                    <a href="{{ route('extension.invite.confirm', [ "id" => $row->extension_program_id]) }}" class="btn btn-sm btn-primary mr-1">Confirm</a>
                                    <a href="{{ route('extension.invite.cancel', [ "id" => $row->extension_program_id]) }}" class="btn btn-sm btn-secondary ">Remove</a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary mb-2" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script type="text/javascript" src="https://cdn.datatables.net/1.11.1/js/jquery.dataTables.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/1.11.1/js/dataTables.bootstrap4.min.js"></script>
    <script>
         $("#invitations_table").dataTable();
    </script>
@endpush