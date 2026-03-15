from google import genai
import sys

GEMINI_API_KEY = "AIzaSyCCIvhqvkzVnC26HGql-cQsWkn0ssNwQJ8"
client = genai.Client(api_key=GEMINI_API_KEY)
MODELS = [
    "models/gemini-flash-latest",
    "models/gemini-flash-lite-latest", 
    "models/gemini-2.5-flash-lite",
    "models/gemini-3-flash-preview"
]

print("--- Gemini Fallback List Diagnostic ---")
for m in MODELS:
    try:
        print(f"Testing {m}...", end=" ", flush=True)
        response = client.models.generate_content(model=m, contents="Hi")
        print(f"SUCCESS: {response.text.strip()[:20]}...")
    except Exception as e:
        print(f"FAILED: {str(e)[:100]}")

print("---------------------------------------")
