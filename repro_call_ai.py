import os
import sys
from groq import Groq
from dotenv import load_dotenv

load_dotenv()
GROQ_API_KEY = os.environ.get("GROQ_API_KEY")
if GROQ_API_KEY:
    GROQ_API_KEY = GROQ_API_KEY.strip()

client = Groq(api_key=GROQ_API_KEY)
MODELS = [
    "llama-3.3-70b-versatile",
    "llama-3.1-8b-instant",
    "gemma2-9b-it",
    "mixtral-8x7b-32768"
]

def call_ai(prompt, is_json=False, temperature=0.7):
    for model_id in MODELS:
        try:
            print(f"Testing {model_id}...")
            kwargs = {
                "model": model_id,
                "messages": [{"role": "user", "content": prompt}],
                "temperature": temperature
            }
            if is_json:
                kwargs["response_format"] = {"type": "json_object"}
                
            response = client.chat.completions.create(**kwargs)
            res_text = response.choices[0].message.content.strip()
            return res_text
        except Exception as e:
            print(f"Failed {model_id}: {e}")
            continue
    return None

res = call_ai("Hi Mindora!")
print(f"Final Result: {res}")
