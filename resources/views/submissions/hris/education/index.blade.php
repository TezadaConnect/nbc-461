<x-app-layout>
    @section('title', 'Ongoing/Advanced Professional Studies |')
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h2 class="font-weight-bold mb-2">Ongoing/Advanced Professional Studies</h2>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">

                {{-- ========= ALERT DETAILS ========= --}}
                @if ($message = Session::get('success'))
                    <div class="alert alert-success alert-index">
                        {{ $message }}
                    </div>
                @endif

                <div class="card">
                    <div class="card-header">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3 ml-1">
                                    <div class="d-inline mr-2">
                                        <a href="{{ route('submissions.educ.create') }}" class="btn btn-success"><i class="bi bi-plus"></i> Add Ongoing/Advanced Professional Study</a>
                                    </div>
                                </div>
                                <hr>
                                <div class="alert alert-info" role="alert">
                                    <i class="bi bi-lightbulb-fill"></i> <strong>Reminders: </strong> <br>
                                    <div class="ml-3">
                                        &#8226; Once you <strong>submit</strong> an accomplishment, you are <strong>not allowed to edit</strong> until the 
                                        quarter ends, except that it was returned to you by the Chairperson, Researcher, or Extensionist.
                                        You may request them to return your accomplishment if revision is necessary. <br>
                                        &#8226; All the added/updated records will be reflected in your <strong>Personnel Portal account</strong> and vice versa. <br>
                                        &#8226; Submit your accomplishments for the <strong>Quarter {{ $currentQuarterYear->current_quarter }}</strong> on or before 
                                        <?php
                                                $deadline = strtotime( $currentQuarterYear->deadline );
                                                $deadline = date( 'F d, Y', $deadline);
                                                ?>
                                                <strong>{{ $deadline }}</strong>. <br>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-hover" id="education_table">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>School Name</th>
                                                <th>Level</th>
                                                <th>Inclusive Dates of Attendance</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($educationFinal as $education)
                                                <tr>
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td>{{ $education->SchoolName }}</td>
                                                    <td>
                                                       {{ $education->EducationLevel}}
                                                    </td>
                                                    <td>
                                                        {{ $education->IncDate }}
                                                    </td>
                                                    <td>
                                                        <div class="btn-group" role="group" aria-label="button-group">
                                                            @if(in_array($education->EmployeeEducationBackgroundID, $savedReports))
                                                                <a href="{{ route('submissions.educ.show', $education->EmployeeEducationBackgroundID) }}" class="btn btn-sm btn-primary d-inline-flex align-items-center">View</a>
                                                                @if(isset($submissionStatus[24]))
                                                                    @if(isset($submissionStatus[24][$education->EmployeeEducationBackgroundID]))
                                                                        @if(in_array($submissionStatus[24][$education->EmployeeEducationBackgroundID], array(0,2)))
                                                                            <a href="{{ route('submissions.educ.edit', $education->EmployeeEducationBackgroundID) }}" class="btn btn-sm btn-warning d-inline-flex align-items-center">Edit</a>
                                                                        @elseif ($submissionStatus[24][$education->EmployeeEducationBackgroundID] == 1 )
                                                                            <button class="btn btn-sm btn-warning d-inline-flex align-items-center" onclick="cantedit()">Edit</button>
                                                                        @endif
                                                                        @if ($submissionStatus[24][$education->EmployeeEducationBackgroundID] == 0 )
                                                                            <a href="{{ route('submissions.educ.check', $education->EmployeeEducationBackgroundID) }}" class="btn btn-sm btn-primary d-inline-flex align-items-center">Submit</a>
                                                                        @elseif ($submissionStatus[24][$education->EmployeeEducationBackgroundID] == 1 )
                                                                            <a href="{{ route('submissions.educ.check', $education->EmployeeEducationBackgroundID) }}" class="btn btn-sm btn-success d-inline-flex align-items-center">Submitted {{ $submitRole[$education->EmployeeEducationBackgroundID] == 'f' ? 'as Faculty' : 'as Admin' }}</a>
                                                                        @elseif ($submissionStatus[24][$education->EmployeeEducationBackgroundID] == 2 )
                                                                            <a href="{{ route('submissions.educ.edit', $education->EmployeeEducationBackgroundID ) }}#upload-document" class="btn btn-sm btn-warning d-inline-flex align-items-center"><i class="bi bi-exclamation-circle-fill text-danger mr-1"></i> No Document</a>
                                                                        @endif
                                                                        @if(in_array($submissionStatus[24][$education->EmployeeEducationBackgroundID], array(0,2)))
                                                                            <button type="button" value="{{ $education->EmployeeEducationBackgroundID }}" class="btn btn-sm btn-danger d-inline-flex align-items-center" data-bs-toggle="modal" data-bs-target="#deleteModal" data-bs-educ="{{ $education->SchoolName }}">Delete</button>
                                                                        @elseif ($submissionStatus[24][$education->EmployeeEducationBackgroundID] == 1 )
                                                                            <button type="button" class="btn btn-sm btn-danger d-inline-flex align-items-center" onclick="cantdelete()">Delete</button>
                                                                            @if(isset($isReturnRequested[$education->EmployeeEducationBackgroundID]))
                                                                                <button type="button" class="btn btn-sm btn-primary d-inline-flex align-items-center" data-reportref = "{{ $education->EmployeeEducationBackgroundID }}" data-reqres="{{$isReturnRequested[$education->EmployeeEducationBackgroundID]}}" onclick="retrequested(this)">Return Status</button>
                                                                            @else
                                                                                <button type="button" class="btn btn-sm btn-warning d-inline-flex align-items-center" onclick="returnrequest({{ $education->EmployeeEducationBackgroundID }})">Request Return</button>
                                                                            @endif
                                                                        @endif
                                                                    @endif
                                                                @endif
                                                            @else
                                                                <a href="{{ route('submissions.educ.add', $education->EmployeeEducationBackgroundID) }}" class="btn btn-sm btn-success d-inline-flex align-items-center">Add</a>
                                                                <button type="button" value="{{ $education->EmployeeEducationBackgroundID }}" class="btn btn-sm btn-danger d-inline-flex align-items-center" data-bs-toggle="modal" data-bs-target="#deleteModal" data-bs-educ="{{ $education->SchoolName }}">Delete</button>
                                                            @endif
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h5 class="text-center">Are you sure you want to delete this accomplishment?</h5>
                <p id="itemToDelete" class="text-center h4"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary mb-2" data-bs-dismiss="modal">Cancel</button>
                <a href="" type="button" class="btn btn-danger mb-2 mr-2" id="delete_modal">Delete</a>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script type="text/javascript" src="https://cdn.datatables.net/1.11.1/js/jquery.dataTables.min.js"></script>
        <script type="text/javascript" src="https://cdn.datatables.net/1.11.1/js/dataTables.bootstrap4.min.js"></script>
        <script>

            $(document).ready( function () {
                $('#education_table').DataTable({
                });
            } );
            
            function cantedit() {
                // alert("Cannot be edited once submitted. Please request to return the accomplishment to be edited.");
                Swal.fire({
                    icon: 'error',
                    title: 'Cannot be edited once submitted.',
                    text: 'Please request to return the accomplishment to be edited.'
                });
            };

            function cantdelete() {
                // alert("Cannot be deleted once submitted. Please request to return the accomplishment if you wish to delete it.");
                Swal.fire({
                    icon: 'error',
                    title: 'Cannot be deleted once submitted.',
                    text: 'Please request to return the accomplishment if you wish to delete it.'
                });
            };

            function returnrequest(refid){
                Swal.fire({
                    title: 'Request To Return',
                    html: `<input type="textarea" id="returnrequestreason" class="swal2-input" placeholder="Reason for Request">`,
                    confirmButtonColor: '#4CAF50',
                    confirmButtonText: 'Submit Request',
                    showCancelButton: true,
                    focusConfirm: false,
                    preConfirm: () => {
                        let reason = document.getElementById('returnrequestreason').value;
                        if (reason) {
                            // let reason = document.getElementById('returnrequestreason').value;
                            /* $.ajaxSetup({
                                headers: {
                                    'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content');
                                }
                            }); */
                            $.ajax({
                                type: 'POST',
                                url: "{{route('submissions.development.returnrequest')}}",
                                data: {"_token": "{{csrf_token()}}",
                                    "reason": jQuery('#returnrequestreason').val(),
                                    "reprefid": refid,
                                },
                                success: function (resp) {
                                    if (resp.success) {
                                        swal.fire("Return Requested!", "", "success");
                                        location.reload();
                                    } else {
                                        swal.fire("Error!", 'Something went wrong.', "error");
                                    }
                                },
                                error: function (resp) {
                                    swal.fire("Error!", resp.message, "error");
                                }
                            });
                        } else {
                                    Swal.showValidationMessage('Please specify reason');
                        }
                    }
                });
            }; 

            function retrequested(element){
                let reasonreq = element.dataset.reqres; 
                let reportrefid = element.dataset.reportref; 
                if(reasonreq.includes("Request Denied:")){
                    //call return request
                    Swal.fire({
                        icon: 'error',
                        title: 'Return Request Denied',
                        text: reasonreq,
                        confirmButtonText: 'Request Again',
                        showCancelButton: true,
                        preConfirm: () => {
                            returnrequest(reportrefid);
                        },
                    });
                }else{
                    Swal.fire({
                        icon: 'info',
                        title: 'Return Already Requested',
                        text: element.dataset.reqres,
                    });
                }
            };

            // auto hide alert
            window.setTimeout(function() {
                $(".alert-index").fadeTo(500, 0).slideUp(500, function(){
                    $(this).remove();
                });
            }, 4000);

            //Item to delete to display in delete modal
            var deleteModal = document.getElementById('deleteModal')
            deleteModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget
            var id = button.getAttribute('value')
            var officershipTitle = button.getAttribute('data-bs-educ')
            var itemToDelete = deleteModal.querySelector('#itemToDelete')
            itemToDelete.textContent = officershipTitle

            var url = '{{ route("submissions.educ.destroy", ":id") }}';
            url = url.replace(':id', id);
            $('#delete_modal').attr('href', url);
            });
        </script>
    @endpush

</x-app-layout>