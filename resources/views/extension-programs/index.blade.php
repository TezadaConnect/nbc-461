<x-app-layout>
    @section('title', 'Extension Programs/Projects/Activities |')
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h2 class="font-weight-bold mb-2">Extension Programs/Projects/Activities</h2>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <div class="form-group mb-3 ml-1">
                            <div class="d-inline mr-2">
                                <a href="{{ route('extension-programs.create') }}" class="btn btn-success"><i class="bi bi-plus"></i> Add Extension Program/Project/Activity</a>
                            </div>
                            <button class="btn btn-primary mr-1" data-toggle="modal" data-target="#tagsModal">
                                Extensions to Add (Tagged by your Partner) @if (count($tags) != 0)
                                            <span class="badge badge-secondary">{{ count($tags) }}</span>
                                        @else
                                            <span class="badge badge-secondary">0</span>
                                        @endif
                            </button>
                        </div>
                        <hr>
                        <div class="alert alert-info" role="alert">
                            <i class="bi bi-lightbulb-fill"></i> <strong>Instructions & Reminders: </strong> <br>
                            <div class="ml-3">
                                &#8226; You can add your partners in the extension program/project/activity to share them the extension you encode. <br>
                                &#8226; Tag your extension partners before submitting. <br>
                                <span class="ml-3"><i class="bi bi-arrow-right ml-1"></i> Click "Tag Extension Partners" button after viewing the extension.</span><br>
                                &#8226; Submit your accomplishments for the Quarter {{ $currentQuarterYear->current_quarter }} on or before
                                    <?php
                                        $deadline = strtotime( $currentQuarterYear->deadline );
                                        $deadline = date( 'F d, Y', $deadline);
                                        ?>
                                        <strong>{{ $deadline }}</strong>. <br>
                                &#8226; Once you <strong>submit</strong> an accomplishment, you are <strong>not allowed to edit</strong> until the quarter period ends.
                            </div>
                        </div>
                        <div class="table-responsive" style="overflow-x:auto;">
                            <table class="table" id="eservice_table">
                                <thead>
                                    <tr>
                                        <th></th>
                                        <th>Title</th>
                                        <th>Status</th>
                                        <th>College/Branch/Campus/Office</th>
                                        <th>Quarter</th>
                                        <th>Year</th>
                                        <th>Date Modified</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($extensionServices as $extensionService)
                                    <tr class="tr-hover" role="button">
                                        <td onclick="window.location.href = '{{ route('extension-programs.show', $extensionService->id) }}' ">{{ $loop->iteration }}</td>
                                        <td onclick="window.location.href = '{{ route('extension-programs.show', $extensionService->id) }}' ">{{ ($extensionService->title_of_extension_program != null ? $extensionService->title_of_extension_program : ($extensionService->title_of_extension_project != null ? $extensionService->title_of_extension_project : ($extensionService->title_of_extension_activity != null ? $extensionService->title_of_extension_activity : ''))) }}</td>
                                        <td onclick="window.location.href = '{{ route('extension-programs.show', $extensionService->id) }}' ">{{ $extensionService->status }}</td>
                                        <td onclick="window.location.href = '{{ route('extension-programs.show', $extensionService->id) }}' ">{{ $extensionService->college_name }}</td>
                                        <td class="{{ ($extensionService->report_quarter == $currentQuarterYear->current_quarter && $extensionService->report_year == $currentQuarterYear->current_year) ? 'to-submit' : '' }}" onclick="window.location.href = '{{ route('extension-programs.show', $extensionService->id) }}' ">
                                            {{ $extensionService->report_quarter }}
                                        </td>
                                        <td onclick="window.location.href = '{{ route('extension-programs.show', $extensionService->id) }}' ">
                                            {{ $extensionService->report_year }}
                                        </td>
                                        <td onclick="window.location.href = '{{ route('extension-programs.show', $extensionService->id) }}' ">
                                        <?php
                                            $updated_at = strtotime( $extensionService->updated_at );
                                            $updated_at = date( 'M d, Y h:i A', $updated_at );
                                            ?>
                                            {{ $updated_at }}
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group" aria-label="button-group">
                                                <a href="{{ route('extension-programs.show', $extensionService) }}" class="btn btn-sm btn-primary d-inline-flex align-items-center">View</a>
                                                <a href="{{ route('extension-programs.edit', $extensionService) }}" class="btn btn-sm btn-warning d-inline-flex align-items-center">Edit</a>
                                                <button type="button" value="{{ $extensionService->id }}" class="btn btn-sm btn-danger d-inline-flex align-items-center" data-bs-toggle="modal" data-bs-target="#deleteModal" data-bs-eservice="{{ ($extensionService->title_of_extension_program != null ? $extensionService->title_of_extension_program : ($extensionService->title_of_extension_project != null ? $extensionService->title_of_extension_project : ($extensionService->title_of_extension_activity != null ? $extensionService->title_of_extension_activity : ''))) }}">Delete</button>
                                                @if ($submissionStatus[12][$extensionService->id] == 0)
                                                    <a href="{{ url('submissions/check/12/'.$extensionService->id) }}" class="btn btn-sm btn-primary d-inline-flex align-items-center">Submit</a>
                                                @elseif ($submissionStatus[12][$extensionService->id] == 1)
                                                    <a href="{{ url('submissions/check/12/'.$extensionService->id) }}" class="btn btn-sm btn-success d-inline-flex align-items-center">Submitted {{ $submitRole[$extensionService->id] == 'f' ? 'as Faculty' : 'as Admin' }}</a>
                                                @elseif ($submissionStatus[12][$extensionService->id] == 2)
                                                    <a href="{{ route('extension-programs.edit', $extensionService->id) }}#upload-document" class="btn btn-sm btn-warning d-inline-flex align-items-center"><i class="bi bi-exclamation-circle-fill text-danger mr-1"></i> No Document</a>
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
    @include('extension-programs.invite.modal', compact('tags'))
>>>>>>>> qars-tickets-ken:resources/views/extension-programs/index.blade.php

    {{-- Delete Modal --}}
    @include('delete')


    @push('scripts')
    <script type="text/javascript" src="https://cdn.datatables.net/1.11.1/js/jquery.dataTables.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/1.11.1/js/dataTables.bootstrap4.min.js"></script>
     <script>
        window.setTimeout(function() {
            $(".alert-index").fadeTo(500, 0).slideUp(500, function(){
                $(this).remove();
            });
        }, 4000);

             $('#eservice_table').DataTable();

         //Item to delete to display in delete modal
        var deleteModal = document.getElementById('deleteModal')
        deleteModal.addEventListener('show.bs.modal', function (event) {
          var button = event.relatedTarget
          var id = button.getAttribute('value')
          var eServiceTitle = button.getAttribute('data-bs-eservice')
          var itemToDelete = deleteModal.querySelector('#itemToDelete')
          itemToDelete.textContent = eServiceTitle

          var url = '{{ route("extension-programs.destroy", ":id") }}';
          url = url.replace(':id', id);
          document.getElementById('delete_item').action = url;

        });
     </script>
     @endpush
</x-app-layout>
