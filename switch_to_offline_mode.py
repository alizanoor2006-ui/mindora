import os
import sys

# This script "reverts" the project to a non-AI state by replacing app.py
# with a version that only uses static fallback messages.

OFFLINE_APP_CODE = """
from flask import Flask, request, jsonify
from flask_cors import CORS
import random

app = Flask(__name__)
CORS(app)

@app.route('/api/sentiment', methods=['POST'])
def analyze_sentiment():
    return jsonify({
        "score": 0,
        "label": "Neutral",
        "suggestion": "Keep journaling to track your feelings! 🌿 (Offline Mode)"
    })

@app.route('/api/quiz', methods=['POST'])
def calculate_stress():
    return jsonify({
        "score": 0,
        "percentage": 0,
        "stress_level": "Low",
        "insight": "Take a deep breath. You're doing great. 🌿 (Offline Mode)"
    })

@app.route('/api/suggestions', methods=['POST'])
def get_ai_suggestions():
    return jsonify({ 
        "title": "Stay Mindful 🌿", 
        "desc": "Take a moment to breathe deeply and reflect on your day. (Offline Mode)" 
    })

@app.route('/api/chatbot', methods=['POST'])
def chatbot_response():
    responses = [
        "I'm here for you, even in offline mode! 🌿",
        "How can I help you today? 🌸",
        "Remember to take some time for yourself. ✨",
        "You're doing a great job! 🍃"
    ]
    return jsonify({"response": random.choice(responses)})

@app.route('/api/quick_tip', methods=['GET'])
def get_quick_tip():
    return jsonify({"tip": "Take a deep breath. You're doing great. 🌿"})

@app.route('/api/health', methods=['GET'])
def health_check():
    return jsonify({"status": "online", "mode": "OFFLINE"})

if __name__ == '__main__':
    print("Mindora Offline Backend active on http://localhost:5000")
    app.run(host='0.0.0.0', port=5000, debug=True)
"""

def main():
    print("--- Mindora Revert to Offline Mode ---")
    confirm = input("This will disable Gemini AI and use static messages. Continue? (y/n): ")
    if confirm.lower() != 'y':
        print("Aborted.")
        return

    try:
        with open('app.py', 'w', encoding='utf-8') as f:
            f.write(OFFLINE_APP_CODE)
        print("[SUCCESS] app.py has been converted to Offline Mode.")
        print("Please restart your backend (python app.py) to apply changes.")
    except Exception as e:
        print(f"[ERROR] Could not revert: {e}")

if __name__ == "__main__":
    main()
