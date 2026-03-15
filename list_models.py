from google import genai
import sys

GEMINI_API_KEY = "AIzaSyCCIvhqvkzVnC26HGql-cQsWkn0ssNwQJ8"
client = genai.Client(api_key=GEMINI_API_KEY)

try:
    print("Listing available models:")
    # The new SDK might use client.models.list()
    for model in client.models.list():
        name = getattr(model, 'name', 'Unknown')
        displayName = getattr(model, 'display_name', 'Unknown')
        methods = getattr(model, 'supported_generation_methods', [])
        print(f"- {name} ({displayName}) | Methods: {methods}")
except Exception as e:
    print(f"Error listing models: {type(e).__name__}: {str(e)}")
