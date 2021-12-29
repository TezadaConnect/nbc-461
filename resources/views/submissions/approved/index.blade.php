<x-app-layout>   
    <x-slot name="header">
            @include('submissions.navigation', compact('roles'))
    </x-slot>

    @if (in_array(5, $roles))
        <div class="card mb-3">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        <h5>Approved By Me</h5>
                        <hr>
                    </div>
                    <div class="col-md-12">
                        <div class="table-responive">
                            <table class="table table-hover table-sm table-bordered text-center" id="report_approved_by_me">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Report Category</th>
                                        <th>Faculty</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($approved_by_me as $row)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $row->report_category }}</td>
                                        <td>{{ $row->last_name.', '.$row->first_name.' '.$row->middle_name.(($row->suffix == null) ? '' : ', '.$row->suffix) }}</td>
                                        <td>
                                            <button class="btn btn-sm btn-primary button-view" id="viewButton" data-toggle="modal" data-target="#viewReport"  data-url="{{ route('document.view', ':filename') }}" data-id="{{ $row->id }}">Details</button>
                                        </td>
                                    </tr>
                                    @empty
                                        
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>   
    @endif
    @if (in_array(6, $roles))
    <div class="card mb-3">
        <div class="card-body">
            <div class="row">
                <div class="col-md-12">
                    <h5>Approved By Me</h5>
                    <hr>
                </div>
                <div class="col-md-12">
                    <div class="table-responive">
                        <table class="table table-hover table-sm table-bordered text-center" id="report_approved_by_me">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Department</th>
                                    <th>Report Category</th>
                                    <th>Faculty</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($approved_by_me as $row)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $row->department_name }}</td>
                                    <td>{{ $row->report_category }}</td>
                                    <td>{{ $row->last_name.', '.$row->first_name.' '.$row->middle_name.(($row->suffix == null) ? '' : ', '.$row->suffix) }}</td>
                                    <td>
                                        <button class="btn btn-sm btn-primary button-view" id="viewButton" data-toggle="modal" data-target="#viewReport"  data-url="{{ route('document.view', ':filename') }}" data-id="{{ $row->id }}">Details</button>
                                    </td>
                                </tr>
                                @empty
                                    
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>   
    @endif
    @if (in_array(7, $roles))
    <div class="card mb-3">
        <div class="card-body">
            <div class="row">
                <div class="col-md-12">
                    <h5>Approved By Me</h5>
                    <hr>
                </div>
                <div class="col-md-12">
                    <div class="table-responive">
                        <table class="table table-hover table-sm table-bordered text-center" id="report_approved_by_me">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>College</th>
                                    <th>Report Category</th>
                                    <th>Faculty</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($approved_by_me as $row)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $row->college_name }}</td>
                                    <td>{{ $row->report_category }}</td>
                                    <td>{{ $row->last_name.', '.$row->first_name.' '.$row->middle_name.(($row->suffix == null) ? '' : ', '.$row->suffix) }}</td>
                                    <td>
                                        <button class="btn btn-sm btn-primary button-view" id="viewButton" data-toggle="modal" data-target="#viewReport"  data-url="{{ route('document.view', ':filename') }}" data-id="{{ $row->id }}">Details</button>
                                    </td>
                                </tr>
                                @empty
                                    
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>     
    @endif
    @if (in_array(8, $roles))
    <div class="card mb-3">
        <div class="card-body">
            <div class="row">
                <div class="col-md-12">
                    <h5>Approved By Me</h5>
                    <hr>
                </div>
                <div class="col-md-12">
                    <div class="table-responive">
                        <table class="table table-hover table-sm table-bordered text-center" id="report_approved_by_me">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>College</th>
                                    <th>Report Category</th>
                                    <th>Faculty</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($approved_by_me as $row)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $row->college_name }}</td>
                                    <td>{{ $row->report_category }}</td>
                                    <td>{{ $row->last_name.', '.$row->first_name.' '.$row->middle_name.(($row->suffix == null) ? '' : ', '.$row->suffix) }}</td>
                                    <td>
                                        <button class="btn btn-sm btn-primary button-view" id="viewButton" data-toggle="modal" data-target="#viewReport"  data-url="{{ route('document.view', ':filename') }}" data-id="{{ $row->id }}">Details</button>
                                    </td>
                                </tr>
                                @empty
                                    
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>     
    @endif

    <div class="modal fade" id="viewReport" tabindex="-1" aria-labelledby="viewReportLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewReportLabel">View Submission</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12 h4 font-weight-bold text-center">Accomplishment Details:</div>
                    <div class="col-md-12">
                        <table class="table table-sm table-borderless" id="columns_value_table">
                        </table>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12 h5 font-weight-bold text-center">Documents:</div>
                    <div class="col-md-12 text-center" id="data_documents">
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-12 text-center" id="review_btn_undo">
                    </div>
                    <div class="col-md-12 text-center" id="review_btn_relay">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>


    @push('scripts')
        <script type="text/javascript" src="https://cdn.datatables.net/1.11.1/js/jquery.dataTables.min.js"></script>
        <script type="text/javascript" src="https://cdn.datatables.net/1.11.1/js/dataTables.bootstrap4.min.js"></script>
        <script>
            $('.button-view').on('click', function(){
                var catID = $(this).data('id');
                var link = $(this).data('url');
                
                var countColumns = 0;
                $.get('/reports/data/'+catID, function (data){
                    Object.keys(data).forEach(function(k){
                        countColumns = countColumns + 1;
                        $('#columns_value_table').append('<tr id="row-'+countColumns+'" class="report-content"></tr>')
                        $('#row-'+countColumns).append('<td class="report-content font-weight-bold h5 text-right">'+k+':</td>');
                        $('#row-'+countColumns).append('<td class="report-content h5 text-left">'+data[k]+'</td>');
                    });
                });
                $.get('/reports/docs/'+catID, function (data) {
                    data.forEach(function (item){
                        var newlink = link.replace(':filename', item)
                        $('#data_documents').append('<a href="'+newlink+'" class="report-content h5 m-1 btn btn-primary">'+item+'<a/>');
                    });
                });
                
            });

            $('#viewReport').on('hidden.bs.modal', function(event) {
                $('.report-content').remove();
            });
            $(function(){
                $('#report_approved_by_me').DataTable();
            });
            // auto hide alert
            window.setTimeout(function() {
                $(".alert").fadeTo(500, 0).slideUp(500, function(){
                    $(this).remove(); 
                });
            }, 4000);
        </script>
    @endpush
</x-app-layout>