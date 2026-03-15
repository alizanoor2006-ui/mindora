# Mindora – Student Mental Health System 🌿

A comprehensive, fully functional AI-Based Stress Monitoring System for Students.

## Tech Stack
- **Frontend:** HTML, CSS, PHP, JS, Chart.js
- **Backend API:** Python (Flask, Waitress)
- **Database:** MySQL
- **Styling:** Custom Glassmorphism UI (Pastel Green, Lavender, Soft Blue)

## Features
- **Student Dashboard:** Track screen time, sleep time, mood, and stress quizzes with interactive graphs.
- **AI Journaling:** Automatically scores your emotions using NLP sentiment analysis.
- **AI Chatbot:** Intent-based emotional support chatbot available 24/7.
- **Admin Dashboard:** Campus-wide analytics, stress trends, and user management.
- **Relaxing UI:** Accessible settings for auditory relief and colorful themes.

## 🚀 Setup Instructions

### 1. Database Setup (MySQL/XAMPP)
1. Open XAMPP Control Panel and start **Apache** and **MySQL**.
2. Assuming `root` user with no password. Start a terminal and run the provided SQL script:
   ```cmd
   c:\xampp\mysql\bin\mysql.exe -u root < c:\xampp\htdocs\Clone_Strees_system\database\mindora_schema.sql
   ```

### 2. Frontend Setup (PHP)
1. Ensure the project is downloaded directly into your XAMPP `htdocs\Clone_Strees_system` directory.
2. Ensure Apache is running in XAMPP.

### 3. AI Backend Setup (Python)
1. Navigate to the `ai-backend` directory via command prompt or PowerShell.
2. Let the environment install dependencies (a `venv` was set up). Use your terminal:
   ```powershell
   cd ai-backend
   python -m venv venv
   .\venv\Scripts\activate
   pip install -r requirements.txt
   ```
3. Run the AI server locally via Waitress on port 5000:
   ```powershell
   python app.py
   ```

### 4. Running the Complete System
1. Keep the Python Flask backend running in the background.
2. In your browser (Chrome/Edge), navigate to: `http://localhost/Clone_Strees_system/`
3. Click "Get Started" and go through the onboarding.
4. From the login page, you can choose to "Sign Up". During registration, toggle the Student/Admin dropdown to create separate accounts.
5. Explore the various tabs (Activity, Insights, Chatbot) as a student, then sign in as Admin to see aggregated analytics graphs!
