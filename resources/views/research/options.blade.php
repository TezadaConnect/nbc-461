<!--  -->
<div class="dropdown">
    <button class="btn btn-primary btn-sm dropdown-toggle py-3" type="button" id="viewDropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        View
    </button>
    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="viewDropdownMenuButton">
        <a href="{{ route('research.show', $research_id) }}" class="dropdown-item"><i class="bi bi-eye"></i> View Registration Record</a>
        @if ($research_status >= 28)
        <a href="{{ route('research.completed.index', $research_id) }}" class="dropdown-item"><i class="bi bi-eye"></i> View Completion Record</a>
        @endif
        @if ($researchRecords['presentation'][$research_id])
        <a href="{{ route('research.presentation.index', $research_id) }}" class="dropdown-item"><i class="bi bi-eye"></i> View Presentation Record</a>
        @endif
        @if ($researchRecords['publication'][$research_id])
        <a href="{{ route('research.publication.index', $research_id) }}" class="dropdown-item"><i class="bi bi-eye"></i> View Publication Record</a>
        @endif
        @if ($researchRecords['copyright'][$research_id])
        <a href="{{ route('research.copyrighted.index', $research_id) }}" class="dropdown-item"><i class="bi bi-eye"></i> View Copyright Record</a>
        @endif
        @if ($research_status >= '30')
            @if ($researchRecords['citation'][$research_id])
            <a href="{{ route('research.citation.index', $research_id) }}" class="dropdown-item"><i class="bi bi-eye"></i> View Citation Records</a>
            @endif
        @endif
        @if ($researchRecords['utilization'][$research_id])
        <a href="{{ route('research.utilization.index', $research_id) }}" class="dropdown-item"><i class="bi bi-eye"></i> View Utilization Records</a>
        @endif
    </div>
</div>
<div class="dropdown">
    <button class="btn btn-warning btn-sm dropdown-toggle py-3" type="button" id="editDropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        Edit
    </button>
    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="editDropdownMenuButton">
        @php
            $submissions = 1;
        @endphp
        @if ($researchRecords['regi'][$research_id] && ($isSubmitted['regi'][$research_id] == false))
            <a href="{{ route('research.edit', $research_id) }}" class="dropdown-item"><i class="bi bi-pencil-square"></i> Edit Registration Record</a>
            @php
                $submissions = 0;
            @endphp
        @endif
        @if ($researchRecords['completion'][$research_id] && ($isSubmitted['completion'][$research_id] == false))
            <a href="{{ route('research.completed.index', $research_id) }}" class="dropdown-item"><i class="bi bi-pencil-square"></i> Edit Completion Record</a>
            @php
                $submissions = 0;
            @endphp
        @endif
        @if ($researchRecords['presentation'][$research_id] && ($isSubmitted['presentation'][$research_id] == false))
            <a href="{{ route('research.presentation.index', $research_id) }}" class="dropdown-item"><i class="bi bi-pencil-square"></i> Edit Presentation Record</a>
            @php
                $submissions = 0;
            @endphp
        @endif
        @if ($researchRecords['publication'][$research_id] && ($isSubmitted['publication'][$research_id] == false))
            <a href="{{ route('research.publication.index', $research_id) }}" class="dropdown-item"><i class="bi bi-pencil-square"></i> Edit Publication Record</a>
            @php
                $submissions = 0;
            @endphp
        @endif
        @if ($researchRecords['copyright'][$research_id] && ($isSubmitted['copyright'][$research_id] == false))
            <a href="{{ route('research.copyrighted.index', $research_id) }}" class="dropdown-item"><i class="bi bi-pencil-square"></i> Edit Copyright Record</a>
            @php
                $submissions = 0;
            @endphp
        @endif
        @if ($research_status >= '30')
            @if ($researchRecords['citation'][$research_id])
            <a href="{{ route('research.citation.showAll', [$research_id, 'for-updates']) }}" class="dropdown-item"><i class="bi bi-pencil-square"></i> Edit Citation Records</a>
            @php
                $submissions = 0;
            @endphp
            @endif
        @endif
        @if ($researchRecords['utilization'][$research_id])
        <a href="{{ route('research.utilization.showAll', [$research_id, 'for-updates']) }}" class="dropdown-item"><i class="bi bi-pencil-square"></i> Edit Utilization Records</a>
            @php
                $submissions = 0;
            @endphp
        @endif

        @if ($submissions == 1)
        <a href="#" class="dropdown-item" style="pointer-events: none;">No edits can be done after submission.</a>
        @endif
    </div>
</div>
<div class="dropdown">
    <button class="btn btn-purple btn-sm dropdown-toggle py-3" type="button" id="optionDropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        Other Options
    </button>
    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="optionDropdownMenuButton">
    @switch($research_status)
        @case('26')
            @if ($isSubmitted['regi'][$research_id] == false)
            <a class="dropdown-item" href="{{ route('research.mark-as-ongoing', $research_id) }}"><i class="bi bi-check2-circle"></i> Mark as Ongoing</a>
            @endif
            @break
        @case('27')
            <a class="dropdown-item" href="{{ route('research.completed.create', $research_id) }}"><i class="bi bi-check2-circle"></i> Mark as Completed</a>
            @break
        @case('28')
            <a class="dropdown-item" href="{{ route('research.presentation.create', $research_id ) }}"><i class="bi bi-laptop"></i> Mark as Presented</a>
            <a class="dropdown-item" href="{{ route('research.publication.create', $research_id ) }}"><i class="bi bi-paperclip"></i> Mark as Published</a>
            @if ($researchRecords['copyright'][$research->id] == null)
                <a class="dropdown-item" href="{{ route('research.copyrighted.index', $research_id ) }}"><i class="bi bi-c-circle"></i> Add Copyright Details</a>
            @endif
            @break
        @case('29')
            <a class="dropdown-item" href="{{ route('research.publication.create', $research_id ) }}"><i class="bi bi-paperclip"></i> Mark as Published</a>
            <a class="dropdown-item" href="{{ route('research.copyrighted.index', $research_id ) }}"><i class="bi bi-c-circle"></i> Add Copyright Details</a>
            @break
        @case('30')
            <a class="dropdown-item" href="{{ route('research.presentation.create', $research_id ) }}"><i class="bi bi-laptop"></i> Mark as Presented</a>
            @if ($researchRecords['copyright'][$research->id] == null)
                <a class="dropdown-item" href="{{ route('research.copyrighted.index', $research_id ) }}"><i class="bi bi-c-circle"></i> Add Copyright Details</a>
            @endif
            <a class="dropdown-item" href="{{ route('research.citation.create', $research_id) }}"><i class="bi bi-blockquote-left"></i> Add Citation</a>
            @break
            @case('31')
            {{-- Presented.Published --}}
            @if ($researchRecords['copyright'][$research->id] == null)
                <a class="dropdown-item" href="{{ route('research.copyrighted.index', $research_id ) }}"><i class="bi bi-c-circle"></i> Add Copyright Details</a>
            @endif
            <a class="dropdown-item" href="{{ route('research.citation.create', $research_id) }}"><i class="bi bi-blockquote-left"></i> Add Citation</a>
            @break
            @default
        @endswitch
        <a class="dropdown-item" href="{{ route('research.utilization.create', $research_id) }}"><i class="bi bi-gear"></i> Add Utilization</a>
    </div>
</div>
<div class="dropdown">
    <button class="btn btn-primary btn-sm dropdown-toggle py-3" type="button" id="submitDropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        Submit
    </button>
    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="submitDropdownMenuButton">
        <!-- Submit buttons -->
        @if ($submissionStatus[1][$research->id] == 0)
            <a href="{{ url('submissions/check/1/'.$research->id) }}" class="dropdown-item"><i class="bi bi-check-square"></i> Submit Registration Record</a>
        @elseif ($submissionStatus[1][$research->id] == 1)
            <a href="#" style="pointer-events: none;" class="dropdown-item bg-success">Registration Submitted {{ $submitRole[$research->id] == 'f' ? 'as Faculty' : 'as Admin' }}</a>
        @elseif ($submissionStatus[1][$research->id] == 2)
            <a href="{{ route('research.edit', $research->id) }}#upload-document" class="dropdown-item"><i class="bi bi-exclamation-circle-fill text-danger"></i> Registration - No Document</a>
        @else
            -
        @endif 
        @if ($isSubmitted['regi'][$research->id])
            <!-- Completion -->
            @if ($researchRecords['completion'][$research->id])
                @if ($submissionStatus[2][$researchRecords['completion'][$research->id]['id']] == 0)
                    <a href="{{ url('submissions/check/2/'.$researchRecords['completion'][$research->id]['id']) }}" class="dropdown-item"><i class="bi bi-check-square"></i> Submit Completion Record</a>
                @elseif ($submissionStatus[2][$researchRecords['completion'][$research->id]['id']] == 1)
                    <a href="#" style="pointer-events: none;" class="dropdown-item bg-success">Completion Submitted {{ $submitRole[$researchRecords['completion'][$research->id]['id']] == 'f' ? 'as Faculty' : 'as Admin' }}</a>
                @elseif ($submissionStatus[2][$researchRecords['completion'][$research->id]['id']] == 2)
                    <a href="{{ route('research.complete', $researchRecords['completion'][$research->id]['id']) }}#upload-document" class="dropdown-item"><i class="bi bi-exclamation-circle-fill text-danger"></i> Completion - No Document</a>
                @else
                    -
                @endif
            @endif
        @endif
        @if ($isSubmitted['completion'][$research->id])
            <!-- Presentation -->
            @if ($researchRecords['presentation'][$research->id] != null)
                @if ($submissionStatus[4][$researchRecords['presentation'][$research->id]['id']] == 0)
                    <a href="{{ url('submissions/check/4/'.$researchRecords['presentation'][$research->id]['id']) }}" class="dropdown-item"><i class="bi bi-check-square"></i> Submit Presentation Record</a>
                @elseif ($submissionStatus[4][$researchRecords['presentation'][$research->id]['id']] == 1)
                    <a href="#" style="pointer-events: none;" class="dropdown-item bg-success">Presentation Submitted {{ $submitRole[$researchRecords['presentation'][$research->id]['id']] == 'f' ? 'as Faculty' : 'as Admin' }}</a>
                @elseif ($submissionStatus[4][$researchRecords['presentation'][$research->id]['id']] == 2)
                    <a href="{{ route('research.complete', $researchRecords['presentation'][$research->id]['id']) }}#upload-document" class="dropdown-item"><i class="bi bi-exclamation-circle-fill text-danger"></i> Presentation - No Document</a>
                @else
                    -
                @endif
            @endif
            <!-- Publication -->
            @if ($researchRecords['publication'][$research->id] != null)
                @if ($submissionStatus[3][$researchRecords['publication'][$research->id]['id']] == 0)
                    <a href="{{ url('submissions/check/3/'.$researchRecords['publication'][$research->id]['id']) }}" class="dropdown-item"><i class="bi bi-check-square"></i> Submit Publication Record</a>
                @elseif ($submissionStatus[3][$researchRecords['publication'][$research->id]['id']] == 1)
                    <a href="#" style="pointer-events: none;" class="dropdown-item bg-success">Publication Submitted {{ $submitRole[$researchRecords['publication'][$research->id]['id']] == 'f' ? 'as Faculty' : 'as Admin' }}</a>
                @elseif ($submissionStatus[3][$researchRecords['publication'][$research->id]['id']] == 2)
                    <a href="{{ route('research.complete', $researchRecords['publication'][$research->id]['id']) }}#upload-document" class="dropdown-item"><i class="bi bi-exclamation-circle-fill text-danger"></i> Publication - No Document</a>
                @else
                    -
                @endif
            @endif
            <!-- Copyright -->
            @if ($researchRecords['copyright'][$research->id] != null)
                @if ($submissionStatus[7][$researchRecords['copyright'][$research->id]['id']] == 0)
                    <a href="{{ url('submissions/check/7/'.$researchRecords['copyright'][$research->id]['id']) }}" class="dropdown-item"><i class="bi bi-check-square"></i> Submit Copyright Record</a>
                @elseif ($submissionStatus[7][$researchRecords['copyright'][$research->id]['id']] == 1)
                    <a href="#" style="pointer-events: none;" class="dropdown-item bg-success"> Copyright Submitted {{ $submitRole[$researchRecords['copyright'][$research->id]['id']] == 'f' ? 'as Faculty' : 'as Admin' }}</a>
                @elseif ($submissionStatus[7][$researchRecords['copyright'][$research->id]['id']] == 2)
                    <a href="{{ route('research.copyrighted.edit', [$research->id, $researchRecords['copyright'][$research->id]['id']]) }}#upload-document" class="dropdown-item"><i class="bi bi-exclamation-circle-fill text-danger"></i> Copyright - No Document</a>
                @else
                    - 
                @endif  
            @endif
        @endif

        @if ($researchRecords['citation'][$research->id] != null)
            <a class="dropdown-item" href="{{ route('research.citation.index', $research->id) }}"><i class="bi bi-check-square"></i> Submit Citations</a>
        @endif
        @if ($isSubmitted['regi'][$research->id])
            @if ($researchRecords['utilization'][$research->id] != null)
                <a class="dropdown-item" href="{{ route('research.utilization.showAll', [$research->id, 'for-submission']) }}"><i class="bi bi-check-square"></i> Submit Utilizations</a>
            @endif
        @endif
    </div>
</div>
@if($research_status <= 27)
<button class="btn btn-danger" data-toggle="modal" data-target="#deleteModal">Defer</button>
@endif
{{-- Remove Form Modal --}}
<!-- <div class="modal fade" id="removeModal" data-backdrop="static" tabindex="-1" aria-labelledby="removeModalLabel" aria-hidden="true">
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
</div> -->

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