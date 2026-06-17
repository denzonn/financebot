<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />

    <title>@yield('title')</title>

    @include('includes.style')

    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
</head>

<body class="font-poppins text-[#0d0d0e] overflow-x-hidden" data-page="{{ trim($__env->yieldContent('page')) }}"
    data-toast-success="{{ session('toast_success') }}" data-toast-error="{{ session('error') }}">
    <section id="content" class="flex flex-col items-center">
        <!-- Halaman Konten -->
        <div class="w-full">
            @yield('content')
        </div>

    </section>

    @include('includes.script')

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @if ($errors->any())
        <script>
            document.addEventListener('DOMContentLoaded', function() {

                Swal.fire({
                    icon: 'error',
                    title: 'Oops!',
                    text: '{{ $errors->first() }}',
                    confirmButtonColor: '#0284c7'
                });

            });
        </script>
    @endif
</body>

</html>
