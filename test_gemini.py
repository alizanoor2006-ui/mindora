from google import genai
import sys

GEMINI_API_KEY = "AIzaSyCCIvhqvkzVnC26HGql-cQsWkn0ssNwQJ8"
client = genai.Client(api_key=GEMINI_API_KEY)
MODEL_ID = "models/gemini-1.5-flash"

try:
    print(f"Testing Gemini with model {MODEL_ID}...")
    response = client.models.generate_content(
        model=MODEL_ID,
        contents="Hello, are you online?"
    )
    print("Success!")
    print(f"Response: {response.text}")
except Exception as e:
    print(f"Error occurred: {type(e).__name__}: {str(e)}")
    import traceback
    traceback.print_exc()
