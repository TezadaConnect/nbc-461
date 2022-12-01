<!--  -->
<div class="dropdown">
    <button class="btn btn-primary btn-sm dropdown-toggle py-3" type="button" id="viewDropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        View
    </button>
    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="viewDropdownMenuButton">
        <a href="{{ route('research.show', $research->id) }}" class="dropdown-item"><i class="bi bi-eye"></i> View Registration Record</a>
        @if ($research_status >= 28)
        <a href="{{ route('research.completed.index', $research->id) }}" class="dropdown-item"><i class="bi bi-eye"></i> View Completion Record</a>
        @endif
        @if ($researchRecords['presentation'][$research->id])
        <a href="{{ route('research.presentation.index', $research->id) }}" class="dropdown-item"><i class="bi bi-eye"></i> View Presentation Record</a>
        @endif
        @if ($researchRecords['publication'][$research->id])
        <a href="{{ route('research.publication.index', $research->id) }}" class="dropdown-item"><i class="bi bi-eye"></i> View Publication Record</a>
        @endif
        @if ($researchRecords['copyright'][$research->id])
        <a href="{{ route('research.copyrighted.index', $research->id) }}" class="dropdown-item"><i class="bi bi-eye"></i> View Copyright Record</a>
        @endif
        @if ($research_status >= '30')
            @if ($researchRecords['citation'][$research->id])
            <a href="{{ route('research.citation.index', $research->id) }}" class="dropdown-item"><i class="bi bi-eye"></i> View Citation Records</a>
            @endif
        @endif
        @if ($researchRecords['utilization'][$research->id])
        <a href="{{ route('research.utilization.index', $research->id) }}" class="dropdown-item"><i class="bi bi-eye"></i> View Utilization Records</a>
        @endif
    </div>
</div>
<div class="dropdown">
    <button class="btn btn-warning btn-sm dropdown-toggle py-3" type="button" id="editDropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        Edit
    </button>
    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="editDropdownMenuButton">
        @if ($researchRecords['regi'][$research->id] && ($isSubmitted[1][$research->id] == false))
            <a href="{{ route('research.edit', $research->id) }}" class="dropdown-item"><i class="bi bi-pencil-square"></i> Edit Registration Record</a>
        @elseif ($isSubmitted[1][$research->id] == true)
            <span class="d-flex" tabindex="0" data-toggle="tooltip" data-bs-placement="top" title="Not allowed to edit after submission.">
                <a href="{{ route('research.edit', $research->id) }}" class="dropdown-item text-muted btn-disabled"><i class="bi bi-pencil-square"></i> Edit Registration Record</a>
            </span>
        @endif
        @if ($researchRecords['completion'][$research->id] && ($isSubmitted[2][$research->id] == false))
            <a href="{{ route('research.completed.edit', [$research->id, $researchRecords['completion'][$research->id]['id'] ]) }}" class="dropdown-item"><i class="bi bi-pencil-square"></i> Edit Completion Record</a>
        @elseif ($isSubmitted[2][$research->id] == true)
            <span class="d-flex" tabindex="0" data-toggle="tooltip" data-bs-placement="top" title="Not allowed to edit after submission.">
                <a href="" class="dropdown-item text-muted btn-disabled"><i class="bi bi-pencil-square"></i> Edit Completion Record</a>
            </span>
        @endif
        @if ($researchRecords['presentation'][$research->id] && ($isSubmitted[4][$research->id] == false))
            <a href="{{ route('research.presentation.edit', [$research->id, $researchRecords['presentation'][$research->id]['id'] ]) }}" class="dropdown-item"><i class="bi bi-pencil-square"></i> Edit Presentation Record</a>
        @elseif ($isSubmitted[4][$research->id] == true)
        <span class="d-flex" tabindex="0" data-toggle="tooltip" data-bs-placement="top" title="Not allowed to edit after submission.">
            <a href="" class="dropdown-item text-muted btn-disabled"><i class="bi bi-pencil-square"></i> Edit Presentation Record</a>
        </span>
        @endif
        @if ($researchRecords['publication'][$research->id] && ($isSubmitted[3][$research->id] == false))
            <a href="{{ route('research.publication.edit', [$research->id, $researchRecords['publication'][$research->id]['id'] ]) }}" class="dropdown-item"><i class="bi bi-pencil-square"></i> Edit Publication Record</a>
        @elseif ($isSubmitted[3][$research->id] == true)
        <span class="d-flex" tabindex="0" data-toggle="tooltip" data-bs-placement="top" title="Not allowed to edit after submission.">
            <a href="" class="dropdown-item text-muted btn-disabled"><i class="bi bi-pencil-square"></i> Edit Publication Record</a>
        </span>    
        @endif
        @if ($researchRecords['copyright'][$research->id] && ($isSubmitted[7][$research->id] == false))
            <a href="{{ route('research.copyrighted.edit', [$research->id, $researchRecords['copyright'][$research->id]['id'] ]) }}" class="dropdown-item"><i class="bi bi-pencil-square"></i> Edit Copyright Record</a>
        @elseif ($isSubmitted[7][$research->id] == true)    
        <span class="d-flex" tabindex="0" data-toggle="tooltip" data-bs-placement="top" title="Not allowed to edit after submission.">
            <a href="" class="dropdown-item text-muted btn-disabled"><i class="bi bi-pencil-square"></i> Edit Copyright Record</a>
        </span>
        @endif
        @if ($research_status >= '30')
            @if ($researchRecords['citation'][$research->id])
            <a href="{{ route('research.citation.showAll', [$research->id, 'for-updates']) }}" class="dropdown-item"><i class="bi bi-pencil-square"></i> Edit Citation Records</a>
            @endif
        @endif
        @if ($researchRecords['utilization'][$research->id])
        <a href="{{ route('research.utilization.showAll', [$research->id, 'for-updates']) }}" class="dropdown-item"><i class="bi bi-pencil-square"></i> Edit Utilization Records</a>
        @endif
    </div>
</div>
<div class="dropdown">
    @if ($research->is_registrant == 0)
    <span class="d-flex" tabindex="0" data-toggle="tooltip" title="The registrant can only access the other options.">
        <button class="btn btn-purple btn-disabled btn-sm dropdown-toggle py-3" type="button" disabled>Other Options</button>
    </span>
    @else
    <button class="btn btn-purple btn-sm dropdown-toggle py-3" type="button" id="optionDropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        Other Options
    </button>
    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="optionDropdownMenuButton">
    @switch($research_status)
        @case('26')
            <a class="dropdown-item" href="{{ route('research.mark-as-ongoing', $research->id) }}"><i class="bi bi-check2-circle"></i> Mark as Ongoing</a>
            @break
        @case('27')
            <a class="dropdown-item" href="{{ route('research.completed.create', $research->id) }}"><i class="bi bi-check2-circle"></i> Mark as Completed</a>
            @break
        @case('28')
            <a class="dropdown-item" href="{{ route('research.presentation.create', $research->id ) }}"><i class="bi bi-laptop"></i> Mark as Presented</a>
            <a class="dropdown-item" href="{{ route('research.publication.create', $research->id ) }}"><i class="bi bi-paperclip"></i> Mark as Published</a>
            @if ($researchRecords['copyright'][$research->id] == null)
                <a class="dropdown-item" href="{{ route('research.copyrighted.index', $research->id ) }}"><i class="bi bi-c-circle"></i> Add Copyright Details</a>
            @endif
            @break
        @case('29')
            <a class="dropdown-item" href="{{ route('research.publication.create', $research->id ) }}"><i class="bi bi-paperclip"></i> Mark as Published</a>
            <a class="dropdown-item" href="{{ route('research.copyrighted.index', $research->id ) }}"><i class="bi bi-c-circle"></i> Add Copyright Details</a>
            @break
        @case('30')
            <a class="dropdown-item" href="{{ route('research.presentation.create', $research->id ) }}"><i class="bi bi-laptop"></i> Mark as Presented</a>
            @if ($researchRecords['copyright'][$research->id] == null)
                <a class="dropdown-item" href="{{ route('research.copyrighted.index', $research->id ) }}"><i class="bi bi-c-circle"></i> Add Copyright Details</a>
            @endif
            <a class="dropdown-item" href="{{ route('research.citation.create', $research->id) }}"><i class="bi bi-blockquote-left"></i> Add Citation</a>
            @break
            @case('31')
            {{-- Presented.Published --}}
            @if ($researchRecords['copyright'][$research->id] == null)
                <a class="dropdown-item" href="{{ route('research.copyrighted.index', $research->id ) }}"><i class="bi bi-c-circle"></i> Add Copyright Details</a>
            @endif
            <a class="dropdown-item" href="{{ route('research.citation.create', $research->id) }}"><i class="bi bi-blockquote-left"></i> Add Citation</a>
            @break
            @default
        @endswitch
        <a class="dropdown-item" href="{{ route('research.utilization.create', $research->id) }}"><i class="bi bi-gear"></i> Add Utilization</a>
    </div>
    @endif
</div>
<div class="dropdown">
    <button class="btn btn-primary btn-sm dropdown-toggle py-3" type="button" id="submitDropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        Submit
    </button>
    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="submitDropdownMenuButton">
        <!-- Submit buttons -->
        @if ($submissionStatus[1][$research->id] == 0)
            <a href="{{ url('submissions/check/1/'.$research->id) }}" class="dropdown-item"><i class="bi bi-check-square"></i> Submit New Commit/Ongoing Record</a>
        @elseif ($submissionStatus[1][$research->id] == 1)
            <a href="#" style="pointer-events: none;" class="dropdown-item bg-success">New Commit/Ongoing Submitted {{ $submitRole[1][$research->id] == 'f' ? 'as Faculty' : 'as Admin' }}</a>
        @elseif ($submissionStatus[1][$research->id] == 2)
            <a href="{{ route('research.edit', $research->id) }}#upload-document" class="dropdown-item"><i class="bi bi-exclamation-circle-fill text-danger"></i> Registration - No Document</a>
        @else
            -
        @endif 
        @if ($isSubmitted[1][$research->id])
            <!-- Completion -->
            @if ($researchRecords['completion'][$research->id])
                @if ($submissionStatus[2][$research->id] == 0)
                    <a href="{{ url('submissions/check/2/'.$research->id) }}" class="dropdown-item"><i class="bi bi-check-square"></i> Submit Completion Record</a>
                @elseif ($submissionStatus[2][$research->id] == 1)
                    <a href="#" style="pointer-events: none;" class="dropdown-item bg-success">Completion Submitted {{ $submitRole[2][$research->id] == 'f' ? 'as Faculty' : 'as Admin' }}</a>
                @elseif ($submissionStatus[2][$research->id] == 2)
                    <a href="{{ route('research.completed.edit', [$research->id, $researchRecords['completion'][$research->id]['id']]) }}#upload-document" class="dropdown-item"><i class="bi bi-exclamation-circle-fill text-danger"></i> Completion - No Document</a>
                @else
                    -
                @endif
            @endif
        @else
            @if ($research->status >= 28 )
            <span class="d-flex" tabindex="0" data-toggle="tooltip" data-bs-placement="top" title="Must submit first the preceding research info.">
                <a href="" class="dropdown-item text-muted btn-disabled"><i class="bi bi-check-square"></i> Submit Completion Record</a>
            </span>    
            @endif
        @endif
        @if ($isSubmitted[2][$research->id])
            <!-- Presentation -->
            @if ($researchRecords['presentation'][$research->id] != null)
                @if ($submissionStatus[4][$research->id] == 0)
                    <a href="{{ url('submissions/check/4/'.$research->id) }}" class="dropdown-item"><i class="bi bi-check-square"></i> Submit Presentation Record</a>
                @elseif ($submissionStatus[4][$research->id] == 1)
                    <a href="#" style="pointer-events: none;" class="dropdown-item bg-success">Presentation Submitted {{ $submitRole[4][$research->id] == 'f' ? 'as Faculty' : 'as Admin' }}</a>
                @elseif ($submissionStatus[4][$research->id] == 2)
                    <a href="{{ route('research.presentation.edit', [$research->id, $researchRecords['presentation'][$research->id]['id'] ]) }}#upload-document" class="dropdown-item"><i class="bi bi-exclamation-circle-fill text-danger"></i> Presentation - No Document</a>
                @else
                    -
                @endif
            @endif
            <!-- Publication -->
            @if ($researchRecords['publication'][$research->id] != null)
                @if ($submissionStatus[3][$research->id] == 0)
                    <a href="{{ url('submissions/check/3/'.$research->id) }}" class="dropdown-item"><i class="bi bi-check-square"></i> Submit Publication Record</a>
                @elseif ($submissionStatus[3][$research->id] == 1)
                    <a href="#" style="pointer-events: none;" class="dropdown-item bg-success">Publication Submitted {{ $submitRole[3][$research->id] == 'f' ? 'as Faculty' : 'as Admin' }}</a>
                @elseif ($submissionStatus[3][$research->id] == 2)
                    <a href="{{ route('research.publication.edit', [$research->id, $researchRecords['publication'][$research->id]['id'] ]) }}#upload-document" class="dropdown-item"><i class="bi bi-exclamation-circle-fill text-danger"></i> Publication - No Document</a>
                @else
                    -
                @endif
            @endif
            <!-- Copyright -->
            @if ($researchRecords['copyright'][$research->id] != null)
                @if ($submissionStatus[7][$research->id] == 0)
                    <a href="{{ url('submissions/check/7/'.$research->id) }}" class="dropdown-item"><i class="bi bi-check-square"></i> Submit Copyright Record</a>
                @elseif ($submissionStatus[7][$research->id] == 1)
                    <a href="#" style="pointer-events: none;" class="dropdown-item bg-success"> Copyright Submitted {{ $submitRole[7][$research->id] == 'f' ? 'as Faculty' : 'as Admin' }}</a>
                @elseif ($submissionStatus[7][$research->id] == 2)
                    <a href="{{ route('research.copyrighted.edit', [$research->id, $researchRecords['copyright'][$research->id]['id']]) }}#upload-document" class="dropdown-item"><i class="bi bi-exclamation-circle-fill text-danger"></i> Copyright - No Document</a>
                @else
                    - 
                @endif  
            @endif
        @endif
        @if($isSubmitted[3][$research->id])
            @if ($researchRecords['citation'][$research->id] != null)
                <a class="dropdown-item" href="{{ route('research.citation.showAll', [$research->id, 'for-submission']) }}"><i class="bi bi-check-square"></i> Submit Citations</a>
            @endif
        @endif
        @if ($isSubmitted[1][$research->id])
            @if ($researchRecords['utilization'][$research->id] != null)
                <a class="dropdown-item" href="{{ route('research.utilization.showAll', [$research->id, 'for-submission']) }}"><i class="bi bi-check-square"></i> Submit Utilizations</a>
            @endif
        @endif
    </div>
</div>
@if($research_status <= 27)
<button class="btn btn-danger" type="button" data-toggle="modal" data-target="#deleteModal">Defer</button>
@else
<span class="d-flex" tabindex="0" data-toggle="tooltip" title="Cannot defer after completing the research.">
  <button class="btn btn-danger btn-disabled" type="button" disabled>Defer</button>
</span>
@endif
{{-- Remove Form Modal --}}
{{-- <div class="modal fade" id="removeModal" data-backdrop="static" tabindex="-1" aria-labelledby="removeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="removeModalLabel">Are you sure you want to change the status of the research into deferred?</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary mb-2" data-dismiss="modal">No</button>
                <a href="{{ route('research.remove-self', $research_code) }}" class="btn btn-danger mb-2 mr-2">YES</a>
            </div>
        </div>
    </div>
</div> --}}

 {{-- Delete Form Modal --}}
 <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Defer Research</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <h5 class="text-center">Are you sure you want to change the status of the research into deferred?</h5>
                <p class="text-center h4">{{ $research->title }}</p>
                <form action="{{ route('research.destroy', $research->id) }}" method="POST">
                    @csrf
                    @method('delete')
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary mb-2" data-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-danger mb-2 mr-2">Defer</button>
            </form>
            </div>
        </div>
    </div>
</div>