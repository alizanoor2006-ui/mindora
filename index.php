<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mindora | Welcome</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #e8f5e9, #ede7f6, #e3f2fd);
            min-height: 100vh;
        }
        .landing-container {
            height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            padding: 2rem;
        }
        .hero-title {
            font-size: 3.5rem;
            color: #4a4a8a;
            margin-bottom: 2rem;
            max-width: 800px;
            text-shadow: 0 4px 10px rgba(0,0,0,0.05);
            line-height: 1.3;
        }
        .flower-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
            animation: float 3s ease-in-out infinite;
            display: inline-block;
        }
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-15px); }
            100% { transform: translateY(0px); }
        }
        .btn-primary {
            display: inline-block;
            background: linear-gradient(135deg, #a8d5a2, #b39ddb);
            color: white;
            text-decoration: none;
            font-size: 1.25rem;
            padding: 1rem 3rem;
            border-radius: 50px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.12);
            transition: transform 0.2s, box-shadow 0.2s;
            font-weight: 600;
        }
        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.18);
        }
    </style>
</head>
<body>
    <div class="landing-container">
        <span class="flower-icon">🌸</span>
        <h1 class="hero-title">Welcome to Mindora<br>Your mental health journey starts here</h1>
        <a href="onboarding.php" class="btn btn-primary">Get Started 🌿</a>
    </div>
</body>
</html>
