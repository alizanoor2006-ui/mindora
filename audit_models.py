from google import genai
import sys

GEMINI_API_KEY = "AIzaSyCCIvhqvkzVnC26HGql-cQsWkn0ssNwQJ8"
client = genai.Client(api_key=GEMINI_API_KEY)

try:
    print("Detailed Model Audit:")
    for model in client.models.list():
        name = model.name
        # Check if it supports generation
        supports_gen = "generateContent" in getattr(model, 'supported_generation_methods', [])
        
        # Test generation if it seems likely
        if "flash" in name.lower() or "pro" in name.lower() or supports_gen:
            print(f"\nTesting {name}...")
            try:
                # Use a very tiny prompt to test quota
                res = client.models.generate_content(model=name, contents=".")
                print(f"  [SUCCESS] {name}")
            except Exception as e:
                err_str = str(e)
                if "429" in err_str:
                    print(f"  [QUOTA EXHAUSTED] {name}")
                elif "404" in err_str:
                    print(f"  [NOT FOUND/API VERSION] {name}")
                else:
                    print(f"  [ERROR] {name}: {err_str[:80]}")
except Exception as e:
    print(f"Fatal audit error: {e}")
