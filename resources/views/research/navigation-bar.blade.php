<h3 class="font-weight-bold mb-2">Research/Book Chapter 
    @if(request()->routeIs('research.completed.index'))
        Completion
    @elseif(request()->routeIs('research.presentation.index'))
        Presentation
    @elseif(request()->routeIs('research.publication.index'))
        Publication
    @elseif(request()->routeIs('research.copyrighted.index'))
        Copyright
    @endif
    - {{ $research['title'] }}</h3>
<p>
    <a class="back_link" href="{{ route('research.index') }}"><i class="bi bi-chevron-double-left"></i>Return to Research Main Page</a>
</p>