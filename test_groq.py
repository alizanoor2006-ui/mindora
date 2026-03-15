import requests
import json

url = "http://localhost:5000/api/chatbot"
data = {"message": "Hi, I'm feeling a bit stressed."}

try:
    print("Testing Groq Chatbot API...")
    resp = requests.post(url, json=data)
    print(f"Status Code: {resp.status_code}")
    print(f"Response: {json.dumps(resp.json(), indent=2)}")
except Exception as e:
    print(f"Error: {e}")
