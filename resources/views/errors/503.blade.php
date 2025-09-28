@php
use App\Helpers\Helpers;
    $settings = Helpers::getSettings();

@endphp

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $settings['maintenance']['title'] }}</title>

    <!-- Font link -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@100..900&display=swap" rel="stylesheet">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" id="rtl-link" type="text/css" href="{{ asset('front/css/vendors/bootstrap.css') }}">

    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background-image: url("{{ asset('front/images/background/bg.jpg') }}");
            background-position: center center;
            background-repeat: no-repeat;
            background-size: cover;
            height: 100vh;
            display: flex;
            place-items: center;
            text-align: center;
            color: #fff;
        }

        .maintenance-container {
            background: rgba(0, 0, 0, 0.7);
            padding: 30px;
            border-radius: 10px;
            max-width: 600px;
        }

        .maintenance-title {
            font-size: 36px;
            font-weight: 700;
            margin-bottom: 15px;
        }

        .maintenance-text {
            font-size: 18px;
            line-height: 1.5;
        }

        .maintenance-image {
            max-width: 100%;
            margin: 20px 0;
        }
    </style>
</head>

<body>
    <div class="maintenance-container">
        <h1 class="maintenance-title">{{ $settings['maintenance']['title'] }}</h1>
        <p class="maintenance-text">
        {{ $settings['maintenance']['description'] }}
        </p>
        <img src="{{ asset($settings['maintenance']['image']) }}" alt="Maintenance" class="maintenance-image">

    </div>
</body>

</html>

