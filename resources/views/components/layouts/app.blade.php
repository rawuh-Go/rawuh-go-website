<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rawuh-Go</title>

    <link rel="icon" href="{{ asset('images/favicon.png') }}" type="image/png">

    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="http://unpkg.com/leaflet/dist/leaflet.css">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Add before closing -->
    <script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>

    <style>
        #map {
            height: 400px;
        }

        <style>body {
            font-family: Arial, sans-serif;
            background-color: #1c1f2b;
            color: white;
            margin: 0;
            padding: 0;
        }

        .container {
            padding: 20px;
        }

        .header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
        }

        .header img {
            border-radius: 50%;
            width: 50px;
            height: 50px;
        }

        .profile-info {
            display: flex;
            flex-direction: column;
        }

        .profile-info .name {
            font-size: 18px;
            font-weight: bold;
        }

        .profile-info .role {
            font-size: 14px;
            color: #cccccc;
        }

        .company-tag {
            background-color: #2f495e;
            color: #a2bddb;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
        }

        .date-box {
            background-color: #2f495e;
            border-radius: 12px;
            padding: 15px;
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 20px;
        }

        .check-buttons {
            display: flex;
            justify-content: space-between;
            gap: 10px;
        }

        .check-button {
            flex: 1;
            background-color: #e8f4fd;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            font-weight: bold;
            color: #2f495e;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 5px;
            font-size: 14px;
        }

        .check-button.check-out {
            background-color: #fbe6a4;
            color: #7f6515;
        }

        .icon {
            width: 24px;
            height: 24px;
        }
    </style>
    </style>
</head>

<body>
    {{ $slot }}
</body>

</html>