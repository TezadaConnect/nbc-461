<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 font-weight-bold">
            {{ __('Not Available') }}
        </h2>
    </x-slot>

    <div class="row justify-content-center">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <h4>Page is under maintenance mode.</h4>
                    <a href="{{ url()->previous() }}" class="text-link">Return to Previous Page</a>
                </div>
            </div>
        </div>
    </div>
    
</x-app-layout>