
    @php
      $table_columns_json = json_encode($tableColumns, JSON_FORCE_OBJECT);
      $table_contents_json = json_encode($tableContents, JSON_FORCE_OBJECT);
      $table_format_json = json_encode($tableFormat, JSON_FORCE_OBJECT);
    @endphp
    
    <!-- These breaks give spaces for the heading formed in the AccomplishmentReportExport -->
    <!-- HTML & CSS not working with aligning multiple elements in one cell row -->
    <br>
    <br>
    <br>
    <br>
    <br>
    <br>
    {{-- foreach through the format --}}
    @foreach ($tableFormat as $format)
    {{-- if its not table(0) output only the name else output name and table--}}
    @if ($format->is_table == "0")
    <h2 class="mt-2">{{ $format->name }}</h2>
    @else
            <h2>{{ $format->name }}</h2>
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-sm">
                    <thead> 
                        <tr>
                            @if ($format->is_individual == "1" && $level != "individual")
                                @if ($level == "college")
                                    @if ($type == "academic")
                                        <th>Department</th>
                                    @elseif ($type == "admin")
                                        <th>Section</th>    
                                    @endif
                                @endif
                            <th>Name of the Employee</th>
                            @endif
                            @if ($level == "individual")
                                @if ($type == "academic")
                                    <th>Department</th>
                                @elseif ($type == "admin")
                                    <th>Section</th>
                                @else
                                @endif
                            @endif
                            {{-- load the addtl columns --}}
                            @foreach ($tableColumns[$format->id] as $column)
                                <th>{{ $column['name'] }}</th>
                            @endforeach
                            <th>Supporting Documents</th>
                            <th>Date Submitted</th>
                            <th>Date Received by IPO</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($tableContents[$format->id] as $content)
                            @php
                                $data = json_decode($content['report_details'], true);
                                $documents =  json_decode($content['report_documents'], true);
                            @endphp
                            <tr>
                                @if ($format->is_individual == "1" && $level != "individual")
                                    @if ($level == "college")
                                    <td>
                                        @if ($level != "department")
                                            {{ $data['department_id'] }}
                                        @else
                                        @endif
                                    </td>
                                    @endif
                                    <td>
                                        {{ $content['faculty_name'] }}
                                    </td>
                                @endif
                                @if ($level == "individual")
                                    @isset ($type)
                                    <td>{{ $data['department_id'] ?? ''}}</td>
                                    @else
                                    @endisset
                                @endif
                                @foreach ($tableColumns[$format->id] as $column )
                                    @if (isset($data[$column['report_column']]))
                                        <td>{{ $data[$column['report_column']] }}</td>
                                    @else
                                        @if ($column == 'fund_source' && $data[$column['report_column']] == 0)
                                            <td>Not Paid</td>
                                        @else
                                            <td>-</td>
                                        @endif
                                    @endif
                                @endforeach
                                <td><a href="{{ route('report.generate.document-view', $content['id']) }}" target="_blank">View Documents</a></td>
                                <td>{{ date( "F d, Y", strtotime($content['report_date'])) }}</td>
                                <td>{{ date( "F d, Y", strtotime($content['updated_at'])) }}</td>
                            </tr>
                                
                        @empty
                        <tr>
                            @if ($format->is_individual == "1" && $level != "individual")
                                    <td>
                                        -
                                    </td>
                                    <td>
                                        -
                                    </td>
                                @endif
                                @if ($level == "individual")
                                    <td>-</td>
                                    <td>-</td>
                                @endif
                                @foreach ($tableColumns[$format->id] as $column )
                                    <td>-</td>
                                @endforeach
                        </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                    @php
                        $footers = json_decode($format->footers);
                    @endphp
                    @if ($footers != null)
                        @foreach ($footers as $footer)
                        <tr>
                            <td><small>{{ $footer }}</small></td>
                        </tr>
                        <br>
                        @endforeach
                    @else
                        <tr>
                            <td><small></small></td>
                        </tr>
                    @endif
                    </tfoot>
                </table>
            </div>
            
        @endif

        
    @endforeach

    <p>This report was generated using the PUP eQAR system on {{ date(' m/d/Y h:i:s a') }} </p>