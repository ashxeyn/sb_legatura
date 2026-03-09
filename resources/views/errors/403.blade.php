<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - Access Denied | Legatura</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/3.5.0/remixicon.min.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 20px;
        }

        .error-container {
            text-align: center;
            color: white;
            max-width: 600px;
            animation: fadeIn 0.6s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .error-code {
            font-size: 150px;
            font-weight: 800;
            line-height: 1;
            margin: 0;
            text-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }

        .error-icon {
            font-size: 120px;
            margin-bottom: 20px;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.1);
            }
        }

        .error-title {
            font-size: 32px;
            font-weight: 700;
            margin: 20px 0 10px;
        }

        .error-message {
            font-size: 18px;
            opacity: 0.9;
            margin-bottom: 30px;
            line-height: 1.6;
        }

        .btn-home {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: white;
            color: #fa709a;
            padding: 14px 32px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 16px;
            text-decoration: none;
            transition: all 0.3s ease;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        .btn-home:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.3);
            background: #f8f9fa;
        }

        .btn-home i {
            font-size: 20px;
        }

        @media (max-width: 640px) {
            .error-code {
                font-size: 100px;
            }

            .error-icon {
                font-size: 80px;
            }

            .error-title {
                font-size: 24px;
            }

            .error-message {
                font-size: 16px;
            }
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-icon">
            <i class="ri-lock-line"></i>
        </div>
        <h1 class="error-code">403</h1>
        <h2 class="error-title">Access Denied</h2>
        <p class="error-message">
            You don't have permission to access this page. 
            If you believe this is an error, please contact support.
        </p>
        <a href="/" class="btn-home">
            <i class="ri-home-4-line"></i>
            <span>Back to Home</span>
        </a>
    </div>
</body>
</html>
