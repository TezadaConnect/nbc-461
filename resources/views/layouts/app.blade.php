<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>@yield('title') PUP eQAR</title>

        <link rel="icon" href="{{ url('favicon.ico') }}">
        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css?family=Nunito:400,600,700" rel="stylesheet">

        <!-- Styles -->
        <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.2/css/bootstrap.css"/>
        <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.1/css/dataTables.bootstrap4.min.css"/>
        <link rel="stylesheet" href="{{ asset('dist/markdown-toolbar.css') }}" type="text/css" />
        <link href="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.min.css" rel="stylesheet" />
        <link href="https://unpkg.com/filepond-plugin-file-poster/dist/filepond-plugin-file-poster.css" rel="stylesheet" />
        <link href="https://unpkg.com/filepond/dist/filepond.min.css" rel="stylesheet" />
        <link href="{{ asset('lightbox2/dist/css/lightbox.css') }}" rel="stylesheet" />
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.9.1/font/bootstrap-icons.css"> <!--added-->
        <link rel="stylesheet" href="{{ asset('dist/selectize.bootstrap4.css') }}">
        <link rel="stylesheet" href="{{ asset('css/app.css') }}">

        <!-- Scripts -->
        <script src="https://kit.fontawesome.com/b22b0c1d67.js" crossorigin="anonymous"></script>
        <script src="https://code.jquery.com/jquery-3.5.1.js"></script>
        <script src="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.min.js"></script>
        <script src="https://unpkg.com/filepond-plugin-file-validate-size/dist/filepond-plugin-file-validate-size.min.js"></script>
        <script src="https://unpkg.com/filepond-plugin-file-poster/dist/filepond-plugin-file-poster.js"></script>
        <script src="https://unpkg.com/filepond-plugin-file-validate-type/dist/filepond-plugin-file-validate-type.js"></script>
        <script src="https://unpkg.com/filepond-plugin-image-exif-orientation/dist/filepond-plugin-image-exif-orientation.min.js"></script>
        <script src="https://unpkg.com/filepond-plugin-file-encode/dist/filepond-plugin-file-encode.min.js"></script>
        <script src="{{ asset('lightbox2/dist/js/lightbox.js') }}"></script>
        <script src="https://unpkg.com/filepond/dist/filepond.min.js"></script>
        <!-- JavaScript Bundle with Popper -->
        <script src="{{ asset('js/app.js') }}"></script>
        <script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js"></script>

        <!-- Bootstrap Datepicker Resources -->
        <!-- <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script> -->
        <!-- <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.0/js/bootstrap.min.js"></script> -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.3.0/js/bootstrap-datepicker.js"></script>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.3.0/css/datepicker.css"/>

        {{-- LOCAL DEPENDENCIES CSS--}}
        <link rel="stylesheet" href="{{ asset('css/sweetalert2.min.css') }}">
    
        {{-- LOCAL DEPENDENCIES SCRIPTS--}}
        <script src="{{ asset('js/sweetalert2.all.min.js') }}"></script>
    </head>
    <body class="font-sans antialiased bg-light" style="background-image: {{ URL('storage/cover2.png') }}">
        <div id="loading">
            <div class="d-flex justify-content-center">
                <div class="spinner-border text-danger font-weight-bold page-spinner" style="width: 3rem; height: 3rem;" role="status">
                    <span class="visually-hidden"></span>
                </div>
            </div>
        </div>
        @include('navigation-menu')

        <!-- Page Heading -->
        @if(request()->routeIs('reports.*', 'chairperson.index', 'researcher.index', 'extensionist.index', 'director.index', 'sector.index', 'ipo.index', 'profile.*'))
        <!-- Page Heading -->
        <header class="d-flex py-3" style="background-color: #212529; border-color: #212529; color: whitesmoke;">
            <div class="container">
                <div>{{ $header }}</div>
            </div>
        </header>
        @endif
        <!-- Page Content -->
        <main class="container my-5">

            {{ $slot }}
        </main>

        @stack('modals')
        @stack('scripts')

        <div id="fb-root"></div><!-- Messenger Chat Plugin Code -->
        <div id="fb-customer-chat" class="fb-customerchat"></div><!-- Your Chat Plugin code -->

        <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.2.2/js/bootstrap.bundle.min.js" integrity="sha512-BOsvKbLb0dB1IVplOL9ptU1EYA+LuCKEluZWRUYG73hxqNBU85JBIBhPGwhQl7O633KtkjMv8lvxZcWP+N3V3w==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script>
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-toggle="tooltip"]'))
var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
  return new bootstrap.Tooltip(tooltipTriggerEl)
})
</script>
    </body>
</html>

<script>
    $(window).on('load',function(){
        const delayMs = 500; // delay in milliseconds
        setTimeout(function(){
            $('#modal').modal('show');
        }, delayMs);

        $("#error-modal-button-1").click(function() {
            $('#modal').modal('hide');
        }); 
        $("#error-modal-button-2").click(function() {
            $('#modal').modal('hide');
        });
    });
</script>

<!-- Your SDK code -->
<script>
    var chatbox = document.getElementById('fb-customer-chat');
    chatbox.setAttribute("page_id", "101461845962090");
    chatbox.setAttribute("attribution", "biz_inbox");
</script>

<script>
    window.fbAsyncInit = function() {
        FB.init({
        xfbml            : true,
        version          : 'v14.0'
        });
    };

    (function(d, s, id) {
        var js, fjs = d.getElementsByTagName(s)[0];
        if (d.getElementById(id)) return;
        js = d.createElement(s); js.id = id;
        js.src = 'https://connect.facebook.net/en_US/sdk/xfbml.customerchat.js';
        fjs.parentNode.insertBefore(js, fjs);
    }(document, 'script', 'facebook-jssdk'));
</script>

<script>
    window.addEventListener('load', function () {
        $('#loading').fadeOut();
    });
</script>

{{-- Sweet Alerts --}}
<script>

    // STANDARD POPUP ALERTS

    if("{{ Session::has('success'); }}"){
        Swal.fire({
            title: "Success!",
            text: "{{ Session::get('success'); }}",
            confirmButtonColor: '#38c172',
            icon: "success"
        });
    }

    if("{{ Session::has('error'); }}"){
        Swal.fire({
            title: "Failed!",
            text: "{{ Session::get('error'); }}",
            confirmButton: false,
            confirmButtonColor: '#38c172',
            icon: "error"
        });
    }

    if("{{ Session::has('warning'); }}"){
        Swal.fire({
            title: "Warning!",
            text: "{{ Session::get('warning'); }}",
            confirmButtonColor: '#38c172',
            icon: "warning"
        });
    }

    if("{{ Session::has('info'); }}"){
        Swal.fire({
            title: "Information!",
            text: "{{ Session::get('info'); }}",
            confirmButtonColor: '#38c172',
            icon: "info"
        });
    }

    // PUT YOUR CUSTOM POPUP ALERT BELLOW

    if("{{ Session::has('success_switch'); }}"){
        Swal.fire({
            title: "Switched Successful",
            text: "{{ Session::get('success_switch'); }}",
            confirmButtonColor: '#38c172',
            icon: "success"
        });
    }

    if("{{ Session::has('submit_success'); }}"){
        Swal.fire({
            title: "Submitted Successfully",
            text: "{{ Session::get('submit_success'); }}",
            confirmButtonColor: '#38c172',
            icon: "success"
        });
    }
    
    if("{{ Session::has('save_success'); }}"){
        Swal.fire({
            title: "Successfully Saved!",
            text: "Accomplishment is ready for submission!",
            confirmButtonColor: '#38c172',
            icon: "success"
        });
    }

    if("{{ Session::has('incomplete_account'); }}"){
        Swal.fire({
            title: "Welcome to PUP eQAR!",
            text: "Before you explore, PUP eQAR needs to know your college, branch, campus, or office of designation.",
            confirmButtonColor: '#38c172',
            icon: "success"
        }).then(function() {
        // Redirect the user
        window.location.href = "{{ route('offices.create') }}";
        });
    }
  
    if("{{ Session::has('cannot_access'); }}"){
        Swal.fire({
            title: "Oops!",
            text: "{{ Session::get('cannot_access'); }}",
            confirmButtonColor: '#38c172',
            icon: "warning"
        });
    }
</script>

{{-- SUCCESS POPUP --}}
{{-- <div class="row">
    <div class="col-md-12">
        @if ($message = Session::get('success_switch'))
            <div class="alert alert-success alert-index">
                <i class="bi bi-check-circle"></i> {{ $message }}
            </div>
        @endif
    </div>
</div> --}}