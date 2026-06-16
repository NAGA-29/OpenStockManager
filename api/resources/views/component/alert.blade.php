{{-- <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script> --}}
@if(Session::has('success_message'))
    @php $message = json_encode(Session::get('success_message')) @endphp
    <script>
        const Toast = Swal.mixin({
            toast: true,
            position: 'bottom-end',
            showConfirmButton: false,
            timer: 4000,
            timerProgressBar: true,
                })

        Toast.fire({
            icon: 'success',
            title: <?= $message; ?>,
        })
    </script>
@elseif (Session::has('error_message'))
    @php $message = json_encode(Session::get('error_message')) @endphp
    <script>
        const Toast = Swal.mixin({
            toast: true,
            position: 'bottom-end',
            showConfirmButton: false,
            timer: 4000,
            timerProgressBar: true,
                })

        Toast.fire({
            icon: 'error',
            title: <?= $message; ?>,
        })
    </script>
@endif
