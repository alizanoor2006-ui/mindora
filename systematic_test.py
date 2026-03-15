from google import genai
import sys

GEMINI_API_KEY = "AIzaSyCCIvhqvkzVnC26HGql-cQsWkn0ssNwQJ8"
client = genai.Client(api_key=GEMINI_API_KEY)

test_models = [
    "models/gemini-1.5-flash", # Just in case
    "models/gemini-flash-latest",
    "models/gemini-flash-lite-latest",
    "models/gemini-pro-latest",
    "models/gemini-2.0-flash-lite-preview-09-2025" # From the list (Wait, I'll use ones from the list)
]

# Get top 5 from actual list
actual_list = []
try:
    for model in client.models.list():
        actual_list.append(model.name)
except:
    pass

models_to_try = actual_list[:10]

for model_id in models_to_try:
    print(f"\n--- Testing model: {model_id} ---")
    try:
        response = client.models.generate_content(
            model=model_id,
            contents="Say 'Hi'"
        )
        print(f"SUCCESS with {model_id}!")
        print(f"Response: {response.text}")
        break # Found one!
    except Exception as e:
        print(f"FAILED {model_id}: {type(e).__name__}")
        if "429" in str(e):
            print("  Reason: Quota Exhausted")
        elif "404" in str(e):
            print("  Reason: Not Found")
        else:
            print(f"  Reason: {str(e)[:100]}")
