<h3 class="font-weight-bold mb-2">Research/Book Chapter - {{ $research['title'] }}</h3>
<p>
    <a class="back_link" href="{{ route('research.index') }}"><i class="bi bi-chevron-double-left"></i>Return to Research Main Page</a>
</p>
<div class="card mb-3 research-tabs">
    <div class="card-body">
        <div class="row">
            <div class="col-md-12">
                <ul class="nav justify-content-center m-n3">
                    @canany(['viewAny','create', 'update', 'delete'], App\Models\Research::class)
                    <li class="nav-sub-menu">
                        <a href="{{ route('research.show', $research_code) }}" class="text-dark {{ request()->routeIs('research.show') ? 'active' : '' }}">
                            {{ __('Registration') }}
                        </a >
                    </li>
                    @endcanany
                    @if ($research_status >= 28)
                        @canany(['viewAny','create', 'update'], App\Models\ResearchComplete::class)
                        <li class="nav-sub-menu">
                            <a href="{{ route('research.completed.index', $research_code) }}" class="text-dark {{ request()->routeIs('research.completed.*') ? 'active' : '' }}">
                                {{ __('Completion') }}
                            </a >
                        </li>
                        @endcanany
                    @endif

                    @if ($noRequisiteRecords[1] == true)
                    @canany(['viewAny','create', 'update'], App\Models\ResearchPresentation::class)
                    <li class="nav-sub-menu">
                        <a href="{{ route('research.presentation.index', $research_code) }}" class="text-dark {{ request()->routeIs('research.presentation.*') ? 'active' : '' }}">
                            {{ __('Presentation') }}
                        </a >
                    </li>
                    @endcanany
                    @endif

                    @if ($noRequisiteRecords[2] == true)
                    @canany(['viewAny','create', 'update'], App\Models\ResearchPublication::class)
                    <li class="nav-sub-menu">
                        <a href="{{ route('research.publication.index', $research_code) }}" class="text-dark {{ request()->routeIs('research.publication.*') ? 'active' : '' }}">
                            {{ __('Publication') }}
                        </a >
                    </li>
                    @endcanany
                    @endif

                    @if ($noRequisiteRecords[3] == true)
                    @canany(['viewAny','create', 'update', 'delete'], App\Models\ResearchCopyright::class)
                    <li class="nav-sub-menu">
                        <a href="{{ route('research.copyrighted.index', $research_code) }}" class="text-dark {{ request()->routeIs('research.copyrighted.*') ? 'active' : '' }}">
                            {{ __('Copyright') }}
                        </a >
                    </li>
                    @endcanany
                    @endif

                    @if ($research_status >= '30')
                        @canany(['viewAny','create', 'update', 'delete'], App\Models\ResearchCitation::class)
                        <li class="nav-sub-menu">
                            <a href="{{ route('research.citation.index', $research_code) }}" class="text-dark {{ request()->routeIs('research.ciation.*') ? 'active' : '' }}">
                                {{ __('Citation') }}
                            </a >
                        </li>
                        @endcanany
                    @endif

                    @canany(['viewAny','create', 'update', 'delete'], App\Models\ResearchUtilization::class)
                    <li class="nav-sub-menu">
                        <a href="{{ route('research.utilization.index', $research_code) }}" class="text-dark {{ request()->routeIs('research.utilization.*') ? 'active' : '' }}">
                            {{ __('Utilization') }}
                        </a >
                    </li>
                    @endcanany
                </ul>
            </div>
        </div>
    </div>
</div>
