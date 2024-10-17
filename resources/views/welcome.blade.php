<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rawuh-Go</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <style>
        /* Reset CSS */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Roboto', sans-serif;
            background-color: #1e1e1e; /* Dark Background */
            color: #f5f5f5; /* Light Text */
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background-color 0.3s, color 0.3s;
            overflow: hidden;
        }

        .container {
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            width: 100%;
            max-width: 600px; /* Memperlebar ukuran kontainer */
            padding: 40px; /* Menambahkan padding untuk ruang yang lebih baik */
            border-radius: 10px;
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.5);
            background-color: #2c2c2c; /* Slightly lighter background for the container */
        }

        .logo {
            width: 150px;
            height: 150px;
            margin-bottom: 10px; /* Mengurangi jarak antara logo dan teks */
        }

        .logo img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        h3 {
            margin: 10px 0; /* Mengurangi margin untuk mendekatkan teks ke logo */
            font-size: 2rem;
            line-height: 1.2; /* Mengatur jarak baris untuk teks */
            opacity: 0; /* Initially hidden for animation */
            transform: translateY(-20px); /* Move up for animation */
            animation: fadeInUp 1s forwards; /* Animation applied */
        }

        @keyframes fadeInUp {
            0% {
                opacity: 0;
                transform: translateY(-20px);
            }
            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .btn {
            display: inline-block;
            padding: 15px 30px;
            font-size: 18px;
            color: #ffffff; /* White Text */
            background-color: #2A5867; /* Bright Button Color */
            text-decoration: none;
            border-radius: 10px;
            transition: background-color 0.3s, transform 0.3s;
            margin-top: 20px; /* Spacing for the button */
            font-weight: 700; /* Bold Button Text */
            box-shadow: 0 2px 10px rgba(0, 123, 255, 0.3); /* Button Shadow */
        }

        .btn:hover {
            background-color: #132f38; /* Darker Hover Color */
            transform: scale(1.05); /* Slight scale effect on hover */
        }

        /* Light Theme Styles */
        body.light-mode {
            background-color: #f5f5f5; /* Light Mode Background */
            color: #1e1e1e; /* Dark Text */
        }

        body.light-mode .container {
            background-color: #ffffff; /* Light Mode Container Background */
        }

        body.light-mode .btn {
            background-color: #2A5867; /* Light Mode Button Color */
            color: #ffffff; /* Light Button Text */
        }

        body.light-mode .btn:hover {
            background-color: #132f38; /* Light Mode Hover Color */
        }

        /* Responsif untuk layar lebih kecil (mobile) */
        @media (max-width: 768px) {
            h3 {
                font-size: 1.5rem;
            }

            .btn {
                font-size: 16px;
                padding: 12px 20px;
            }

            .logo {
                width: 120px;
                height: 120px;
            }

            .container {
                max-width: 90%; /* Full width for mobile */
                padding: 20px; /* Reduce padding */
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">
            <!-- Tempatkan logo di sini -->
            <img src="{{ asset('images/logo.png') }}" alt="Logo">
        </div>
        <h3>Selamat Datang di Rawuh Go</h3> <!-- Teks berada dalam satu baris -->
        <a href="/admin/login" class="btn">Masuk ke Admin</a>
    </div>

    <script>
        // Deteksi tema pengguna
        const prefersDarkScheme = window.matchMedia("(prefers-color-scheme: dark)");

        // Fungsi untuk menerapkan tema
        const applyTheme = (darkMode) => {
            if (darkMode) {
                document.body.classList.remove('light-mode');
            } else {
                document.body.classList.add('light-mode');
            }
        }

        // Mengaplikasikan tema berdasarkan preferensi pengguna
        applyTheme(prefersDarkScheme.matches);

        // Mendeteksi perubahan tema
        prefersDarkScheme.addEventListener("change", (e) => {
            applyTheme(e.matches);
        });
    </script>
</body>
</html>
