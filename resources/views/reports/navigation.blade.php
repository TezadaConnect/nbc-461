<div class="container">

    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="row">
                <div class="col">
                    {{-- My Accomplishments --}}
                    <h6 style="font-weight: bold; color: #eeb510">MY ACCOMPLISHMENTS</h6>
                    <a href="{{ route('reports.consolidate.myaccomplishments') }}" class="submission-menu {{ request()->routeIs('reports.consolidate.myaccomplishments') || request()->routeIs('reports.consolidate.myaccomplishments.*') ? 'active' : '' }} ">My Accomplishments</a>
                </div>
                <div class="col">
                    @isset ($assignments)
                    <h6 style="font-weight: bold; color: #eeb510">REVIEW ACCOMPLISHMENTS</h6>
                    @endisset
                    @if ($assignments[5] != null)
                    <a href="{{ route('chairperson.index') }}" class="submission-menu {{ request()->routeIs('chairperson.index') ? 'active' : ''}}">Department/Section Level</a><br>
                    @endif
                    @if ($assignments[10] != null)
                    <a href="{{ route('researcher.index') }}" class="submission-menu {{ request()->routeIs('researcher.index') ? 'active' : ''}}">Research</a><br>
                    @endif
                    @if ($assignments[11] != null)
                    <a href="{{ route('extensionist.index') }}" class="submission-menu {{ request()->routeIs('extensionist.index') ? 'active' : ''}}">Extensions</a><br>
                    @endif
                    @if ($assignments[6] != null || $assignments[12] != null)
                    <a href="{{ route('director.index') }}" class="submission-menu {{ request()->routeIs('director.index') ? 'active' : ''}}">College/Office Level</a><br>
                    @endif
                    @if ($assignments[7] != null || $assignments[13] != null)
                    <a href="{{ route('sector.index') }}" class="submission-menu {{ request()->routeIs('sector.index') ? 'active' : ''}}">Sector Level</a><br>
                    @endif
                    @if (in_array(8, $roles))
                    <a href="{{ route('ipo.index') }}" class="submission-menu {{ request()->routeIs('ipo.index') ? 'active' : ''}}">IPO Level</a><br>
                    @endif
                </div>
                <div class="col">
                    @isset ($assignments)
                    <h6 style="font-weight: bold; color: #eeb510">GENERATE QAR</h6>
                    @endisset
                    @if ($assignments[5] != null)
                    {{-- Departments' --}}
                        @forelse ( $assignments[5] as $row)
                            <a href="{{ route('reports.consolidate.department', $row->department_id) }}" class="submission-menu  {{ isset($id) ? ($row->department_id == $id && (request()->routeIs('reports.consolidate.department') || request()->routeIs('reports.consolidate.department.*')) ? 'active' : '') : '' }}">  
                                Chair/Chief - {{ $row->code }}
                            </a><br>
                        @empty
    
                        @endforelse 
                    @endif
    
                    {{-- Researchers --}}
                    @if ($assignments[10] != null)
                        @forelse ( $assignments[10] as $row)
                            <a href="{{ route('reports.consolidate.research', $row->college_id) }}" class="submission-menu {{ isset($id) ? ($row->college_id == $id && request()->routeIs('reports.consolidate.research') ? 'active' : '') : '' }}">
                            Research Coord. - {{ $row->name }}
                            </a><br>
                        @empty
                        @endforelse
                    @endif
    
                    {{-- Extensionist --}}
                    @if ($assignments[11] != null)
                        @forelse ( $assignments[11] as $row)
                            <a href="{{ route('reports.consolidate.extension', $row->college_id) }}" class="submission-menu {{ isset($id) ? ($row->college_id == $id && (request()->routeIs('reports.consolidate.extension') || request()->routeIs('reports.consolidate.extension.*')) ? 'active' : '') : '' }}">
                                Extension Coord. - {{ $row->code }}
                            </a><br>
                        @empty
                        @endforelse
                    @endif
    
                    {{-- Colleges/Branches/Offices --}}
                    @if ($assignments[6] != null)
                        @forelse ( $assignments[6] as $row)
                            <a href="{{ route('reports.consolidate.college', $row->college_id) }}" class="submission-menu  {{ isset($id) ? ($row->college_id == $id && (request()->routeIs('reports.consolidate.college') || request()->routeIs('reports.consolidate.college.*')) ? 'active' : '') : '' }} ">
                                Dean/Director - {{ $row->code }}
                            </a><br>
                        @empty
                        @endforelse
                    @endif

                    {{-- Colleges/Branches/Offices --}}
                    @if ($assignments[12] != null)
                        @forelse ( $assignments[12] as $row)
                            <a href="{{ route('reports.consolidate.college', $row->college_id) }}" class="submission-menu  {{ isset($id) ? ($row->college_id == $id && (request()->routeIs('reports.consolidate.college') || request()->routeIs('reports.consolidate.college.*')) ? 'active' : '') : '' }} ">
                                Assistant/Associate to Dean/Director - {{ $row->code }}
                            </a><br>
                        @empty
                        @endforelse
                    @endif
    
                    {{-- Sectors/VPs --}}
                    @if ($assignments[7] != null)
                        @forelse ( $assignments[7] as $row)
                            <a href="{{ route('reports.consolidate.sector', $row->sector_id) }}" class="submission-menu {{ isset($sector->id) ? ($row->sector_id == $sector->id && (request()->routeIs('reports.consolidate.sector') || request()->routeIs('reports.consolidate.sector.*')) ? 'active' : '') : '' }}">
                                VP - {{ $row->code }}
                            </a><br>
                        @empty
                        @endforelse
                    @endif

                    {{-- Sectors/VPs --}}
                    @if ($assignments[13] != null)
                        @forelse ( $assignments[13] as $row)
                            <a href="{{ route('reports.consolidate.sector', $row->sector_id) }}" class="submission-menu {{ isset($sector->id) ? ($row->sector_id == $sector->id && (request()->routeIs('reports.consolidate.sector') || request()->routeIs('reports.consolidate.sector.*')) ? 'active' : '') : '' }}">
                                Assistant to VP - {{ $row->code }}
                            </a><br>
                        @empty
                        @endforelse
                    @endif
    
                    {{-- IPQMSOs --}}
                    @if (in_array(8, $roles))
                        <a href="{{ route('reports.consolidate.ipqmso') }}" class="submission-menu {{ request()->routeIs('reports.consolidate.ipqmso') || request()->routeIs('reports.consolidate.ipo.*') ? 'active' : ''}}">
                            IPO Level
                        </a>  
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>