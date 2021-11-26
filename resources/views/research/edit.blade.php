<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 font-weight-bold">
            {{ __($research->research_code.' > Update Research Information') }}
        </h2>
    </x-slot>

    <div class="container">
        <div class="row">
            <div class="col-md-12">
                @include('research.navigation-bar', ['research_code' => $research->id, 'research_status' => $research->status])
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <form action="{{ route('research.update', $research->id) }}" method="post">
                            @csrf
                            @method('put')
                            @include('research.form', ['formFields' => $researchFields1, 'value' => $values])
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>College/Campus/Branch/Office where you commit the research</label>
    
                                        <select name="college_id" id="college" class="form-control custom-select"  required>
                                            <option value="" selected disabled>Choose...</option>
                                            @foreach ($colleges as $college)
                                            <option value="{{ $college->id }}" {{ ($values['college_id'] == $college->id) ? 'selected' : '' }}>{{ $college->name }}</option>
                                            @endforeach
                                           
                                        </select>
                                        
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label>Department where you commit the research</label>

                                    <select name="department_id" id="department" class="form-control custom-select" required>
                                        <option value="" selected disabled>Choose...</option>
                                    </select>
                                </div>
                            </div>
                            @include('research.form', ['formFields' => $researchFields2, 'value' => $values])
                            
                            <div class="col-md-12">
                                <div class="mb-0">
                                    <div class="d-flex justify-content-end align-items-baseline">
                                        <button type="submit" id="submit" class="btn btn-success">Save</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mt-3">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <h5 id="textHome" style="color:maroon">Supporting Documents</h5>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <h6 style="color:maroon"><i class="far fa-file-alt mr-2"></i>Documents</h6>
                                <div class="row">
                                    @if (count($researchDocuments) > 0)
                                        @foreach ($researchDocuments as $document)
                                            @if(preg_match_all('/application\/\w+/', \Storage::mimeType('documents/'.$document['filename'])))
                                                <div class="col-md-12 mb-3" id="doc-{{ $document['id'] }}">
                                                    <div class="card bg-light border border-maroon rounded-lg">
                                                        <div class="card-body">
                                                            <div class="row mb-3">
                                                                <div class="col-md-12">
                                                                    <div class="embed-responsive embed-responsive-1by1">
                                                                        <iframe  src="{{ route('document.view', $document['filename']) }}" width="100%" height="500px"></iframe>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-md-12">
                                                                    <button class="btn btn-danger remove-doc" data-id="doc-{{ $document['id'] }}" data-link="{{ route('research.removedoc', $document['filename']) }}" data-toggle="modal" data-target="#deleteModal">Delete</button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        @endforeach
                                    @else
                                        <div class="col-md-4 offset-md-4">
                                            <h6 class="text-center">No Documents Attached</h6>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6 style="color:maroon"><i class="far fa-image mr-2"></i>Images</h6>
                                <div class="row">
                                    @if(count($researchDocuments) > 0)
                                        @foreach ($researchDocuments as $document)
                                            @if(preg_match_all('/image\/\w+/', \Storage::mimeType('documents/'.$document['filename'])))
                                                <div class="col-md-6 mb-3" id="doc-{{ $document['id'] }}">
                                                    <div class="card bg-light border border-maroon rounded-lg">
                                                        <a href="{{ route('document.display', $document['filename']) }}" data-lightbox="gallery" data-title="{{ $document['filename'] }}">
                                                            <img src="{{ route('document.display', $document['filename']) }}" class="card-img-top img-resize"/>
                                                        </a>
                                                        <div class="card-body">
                                                            <table class="table table-sm my-n3 text-center">
                                                                <tr>
                                                                    <th>
                                                                        <button class="btn btn-danger remove-doc" data-id="doc-{{ $document['id'] }}" data-link="{{ route('research.removedoc', $document['filename']) }}" data-toggle="modal" data-target="#deleteModal">Delete</button>
                                                                    </th>
                                                                </tr>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        @endforeach
                                    @else
                                        <div class="col-md-4 offset-md-4">
                                            <h6 class="text-center">No Documents Attached</h6>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                </div>
            </div>
        </div>
    </div>
    {{-- Delete doc Modal --}}
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Delete Form</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <h5 class="text-center">Are you sure you want to delete this document?</h5>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary mb-2" data-dismiss="modal">Cancel</button>
                    <button class="btn btn-danger mb-2 mr-2" id="deletedoc">Delete</button>
                </form>
                </div>
            </div>
        </div>
    </div>
    @push('scripts')
        <script>
            var url = '';
            var docId = '';
            $('.remove-doc').on('click', function(){
                url = $(this).data('link');   
                docId = $(this).data('id');
                console.log(docId);
            });
            $('#deletedoc').on('click', function(){
                $.get(url, function (data){
                    console.log(data);
                    $('#deleteModal .close').click();
                    $('#'+docId).remove();
                });
            });

            $('#college').on('input', function(){
                var collegeId = $('#college').val();
                $('#department').empty().append('<option selected="selected" disabled="disabled" value="">Choose...</option>');
                $.get('/departments/options/'+collegeId, function (data){

                    data.forEach(function (item){
                        $("#department").append(new Option(item.name, item.id));
                        
                    });

                });
            });
        
        </script>
        <script>
            function hide_dates() {
                $('.start_date').hide();
                $('.target_date').hide();
                $('#start_date').attr('disabled', true);
                $('#target_date').attr('disabled', true);
            }

            $(function() {
                if ({{$research->status}} == 26) {
                    hide_dates();
                    
                }
                else if ({{$research->status}} == 27) {
                    $('.start_date').show();
                    $('.target_date').show();
                    $('#status').attr('disabled', true);
                }
                var collegeId = $('#college').val();
                $('#department').empty().append('<option selected="selected" disabled="disabled" value="">Choose...</option>');
                $.get('/departments/options/'+collegeId, function (data){

                    data.forEach(function (item){
                        $("#department").append(new Option(item.name, item.id));
                        
                    });
                    document.getElementById("department").value = "{{ $values['department_id'] }}";
                });
                $('#status').empty().append('<option selected="selected" value="{{ $researchStatus->id }}">{{ $researchStatus->name }}</option>');
                $('#status').attr('disabled', true);
                
            });

            $('#nature_of_involvement').on('change', function (){
                $('#nature_of_involvement option[value=11]').attr('selected','selected');
                // console.log(11);
                $('#nature_of_involvement').attr('disabled', true); 
            });

            $('#funding_type').on('change', function (){
                var type = $(this).val();
                if(type == 23){
                    
                    $('.funding_agency').show();
                    $('#funding_agency').val('Polytechnic University of the Philippines');
                    $('#funding_agency').removeAttr('disabled');
                    $('#funding_agency').attr('readonly', true);
                }
                else if(type == 24){
                    $('.funding_agency').hide();
                    $('#funding_agency').attr('disabled', true);
                }
                else if(type == 25){
                    $('#funding_agency').removeAttr('readonly');
                    $('#funding_agency').removeAttr('disabled');
                    $('.funding_agency').show();
                    $('#funding_agency').val('');
                }
            });

            $('#status').on('change', function(){
                var statusId = $('#status').val();
                if (statusId == 26) {
                    hide_dates();
                    $('#start_date').removeAttr('required');
                    $('#target_date').removeAttr('required');
                }
                else if (statusId == 27) {
                    $('.start_date').show();
                    $('.target_date').show();
                    $('#start_date').attr("required", true);
                    $('#target_date').attr("required", true);;
                }
            });

            
            $('#keywords').on('keyup', function(){
                var value = $(this).val();
                if (value != null){
                    var count = value.match(/(\w+)/g).length;
                    if(count < 5)
                        $("#validation-keywords").text('The number of keywords is still less than five (5)');
                    else{
                        $("#validation-keywords").text('');
                    }
                }
                if (value == null)
                    $("#validation-keywords").text('The number of keywords must be five (5)');
            });

        </script>
        <script>
             $('#start_date').on('input', function(){
                var date = new Date($('#start_date').val());
                var day = date.getDate();
                var month = date.getMonth() + 1;
                var year = date.getFullYear();
                // alert([day, month, year].join('-'));
                // document.getElementById("target_date").setAttribute("min", [day, month, year].join('-'));
                document.getElementById('target_date').setAttribute('min', [year, month, day.toLocaleString(undefined, {minimumIntegerDigits: 2})].join('-'));
                $('#target_date').val([year, month, day.toLocaleString(undefined, {minimumIntegerDigits: 2})].join('-'));
            });
        </script>
    @endpush
</x-app-layout>