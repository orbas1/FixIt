<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="description" content="{{ config('app.name') }}" />
    <meta name="keywords" content="{{ config('app.name') }}" />
    <meta name="author" content="{{ config('app.name') }}" />
    <link rel="icon" href="{{ asset('frontend/images/favicon/1.png') }}" type="image/x-icon" />
    <title>Not Found Page</title>

    <!-- Google font -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100;0,9..40,200;0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;0,9..40,800;0,9..40,900;0,9..40,1000;1,9..40,100;1,9..40,200;1,9..40,300;1,9..40,400;1,9..40,500;1,9..40,600;1,9..40,700;1,9..40,800;1,9..40,900;1,9..40,1000&display=swap" rel="stylesheet" />

    <!-- Swiper Slider css -->
    <link rel="stylesheet" type="text/css" href="{{ asset('frontend/css/vendors/swiper-bundle.min.css') }}" />

    <!-- Bootstrap css -->
    <link rel="stylesheet" type="text/css" href="{{ asset('frontend/css/vendors/bootstrap/bootstrap.css') }}" />

    <!-- Iconsax css -->
    <link rel="stylesheet" type="text/css" href="{{ asset('frontend/css/vendors/iconsax/iconsax.css') }}" />

    <!-- Template css -->
    <link rel="stylesheet" type="text/css" href="{{ asset('frontend/css/style.css') }}" />
</head>

<body class="notLoaded">
    <!-- Loader Section Start -->
    <div class="page-loader" id="loader">
        <div class="page-loader-wrapper">
            <img src="{{ asset('frontend/images/gif/loader.gif') }}" alt="" />
        </div>
    </div>
    <!-- Loader Section End -->

    <!-- Not Found Section Start -->
    <div class="error-wrapper">
        <div class="no-data-content">
            <img src="{{ asset('frontend/images/no-data.png') }}" alt="no-data" class="img-fluid" />

            <h2>Page Not Found</h2>
            <p>The page you’re trying to find either doesn’t exist or has been relocated.</p>
            <a href="{{ route('frontend.home') }}" class="btn btn-solid">
                <i class="iconsax arrow" icon-name="arrow-left"></i>
                Back To Home
            </a>
        </div>
    </div>
    <!-- Not Found Section End -->

    <!-- latest jquery-->



    <!-- jquery ui-->
    <script src="{{ asset('frontend/js/jquery-ui.min.js') }}"></script>

    <!-- Bootstrap js-->
    <script src="{{ asset('frontend/js/bootstrap/bootstrap.bundle.min.js') }}"></script>

    <script src="{{ asset('frontend/js/bootstrap/bootstrap-notify.min.js') }}"></script>

    <script src="{{ asset('frontend/js/bootstrap/popper.min.js') }}"></script>

    <!-- Iconsax js -->
    <script src="{{ asset('frontend/js/iconsax/iconsax.js') }}"></script>

    <!-- Favourite js -->
    <script src="{{ asset('frontend/js/add-favourite.js') }}"></script>

    <!-- script js -->
    <script src="{{ asset('frontend/js/script.js') }}"></script>

</body>
</html>
