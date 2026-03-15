<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mindora | Onboarding</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #e8f5e9, #ede7f6, #e3f2fd);
            min-height: 100vh;
        }
        .glass-card {
            background: rgba(255,255,255,0.7);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            padding: 2.5rem;
            box-shadow: 0 8px 32px rgba(0,0,0,0.1);
        }
        .btn {
            display: inline-block;
            padding: 0.7rem 1.8rem;
            border-radius: 50px;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            border: none;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .btn-primary {
            background: linear-gradient(135deg, #a8d5a2, #b39ddb);
            color: white;
        }
        .btn-secondary {
            background: rgba(255,255,255,0.6);
            color: #7c6ab5;
            border: 2px solid #e0d7f5;
        }
        .btn:hover { transform: translateY(-2px); box-shadow: 0 6px 18px rgba(0,0,0,0.12); }
        h2 { color: #4a4a8a; margin-bottom: 0.75rem; }
        p  { color: #777; font-size: 1rem; line-height: 1.6; }
    </style>
    <style>
        .onboarding-container {
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 1rem;
        }
        .onboarding-card {
            max-width: 500px;
            width: 100%;
            text-align: center;
        }
        .slide {
            display: none;
            animation: fadeIn 0.5s ease;
        }
        .slide.active {
            display: block;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateX(20px); }
            to { opacity: 1; transform: translateX(0); }
        }
        .slide-img {
            font-size: 5rem; /* Placeholder icon */
            margin-bottom: 1.5rem;
            display: inline-block;
        }
        .controls {
            display: flex;
            justify-content: space-between;
            margin-top: 2rem;
        }
        .dots {
            display: flex;
            justify-content: center;
            gap: 0.5rem;
            margin-top: 1.5rem;
        }
        .dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: rgba(0,0,0,0.1);
        }
        .dot.active {
            background: var(--clr-lavender-dark);
        }
    </style>
</head>
<body>
    <div class="onboarding-container">
        <div class="glass-card onboarding-card">
            
            <div class="slide active" id="slide-1">
                <div class="slide-img">📊</div>
                <h2>Track Your Mental Well-being</h2>
                <p>Monitor your mood and identify patterns to better understand yourself.</p>
            </div>

            <div class="slide" id="slide-2">
                <div class="slide-img">📚</div>
                <h2>Access Resources & Support</h2>
                <p>Find tips, articles, and hotlines when you need them most.</p>
            </div>

            <div class="slide" id="slide-3">
                <div class="slide-img">🧘‍♀️</div>
                <h2>Meditate & Relax</h2>
                <p>When stressed, come and relax with guided support and soothing sounds.</p>
            </div>

            <div class="dots">
                <span class="dot active" id="dot-1"></span>
                <span class="dot" id="dot-2"></span>
                <span class="dot" id="dot-3"></span>
            </div>

            <div class="controls">
                <a href="login.php" class="btn btn-secondary">Skip</a>
                <button type="button" class="btn btn-primary" id="btn-next">Next</button>
            </div>
            
        </div>
    </div>

    <script>
        let currentSlide = 1;
        const totalSlides = 3;

        document.getElementById('btn-next').addEventListener('click', () => {
            if (currentSlide < totalSlides) {
                document.getElementById(`slide-${currentSlide}`).classList.remove('active');
                document.getElementById(`dot-${currentSlide}`).classList.remove('active');
                currentSlide++;
                document.getElementById(`slide-${currentSlide}`).classList.add('active');
                document.getElementById(`dot-${currentSlide}`).classList.add('active');
                
                if (currentSlide === totalSlides) {
                    document.getElementById('btn-next').textContent = 'Get Started';
                }
            } else {
                window.location.href = 'login.php';
            }
        });
    </script>
</body>
</html>
