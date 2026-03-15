import requests
import json

url = "http://localhost:5000/api/curated_insights"
test_data = {
    "mood": "Stressed",
    "screen_time": 8,
    "sleep_time": 4,
    "journals": ["Too much work", "Feeling overwhelmed"],
    "chats": ["I need help", "I am tired"],
    "available_resources": [
        {"id": 1, "title": "Mindfulness for Students", "topic": "Grounding", "category": "Video"},
        {"id": 2, "title": "Managing Exam Anxiety", "topic": "Anxiety", "category": "Article"},
        {"id": 3, "title": "The Science of Sleep", "topic": "Healing", "category": "Video"}
    ]
}

try:
    print("Calling /api/curated_insights...")
    response = requests.post(url, json=test_data)
    print(f"Status: {response.status_code}")
    data = response.json()
    print(f"Response: {json.dumps(data, indent=2)}")
except Exception as e:
    print(f"Error: {e}")
