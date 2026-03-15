<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mindora | Welcome</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
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
            color: var(--clr-text-main);
            margin-bottom: 2rem;
            max-width: 800px;
            text-shadow: 0 4px 10px rgba(0,0,0,0.05);
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
    </style>
</head>
<body>
    <div class="landing-container">
        <span class="flower-icon">🌸</span>
        <h1 class="hero-title">Welcome to Mindora<br>Your mental health journey starts here</h1>
        <a href="onboarding.php" class="btn btn-primary" style="font-size: 1.25rem; padding: 1rem 3rem;">Get Started</a>
    </div>
</body>
</html>
