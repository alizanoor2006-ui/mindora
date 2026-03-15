import os
import sys
from groq import Groq
from dotenv import load_dotenv

load_dotenv()
key = os.environ.get("GROQ_API_KEY")
print(f"Testing key: {key[:10]}...")

try:
    client = Groq(api_key=key)
    chat_completion = client.chat.completions.create(
        messages=[{"role": "user", "content": "Say hello"}],
        model="llama-3.3-70b-versatile",
    )
    print("Success!")
    print(chat_completion.choices[0].message.content)
except Exception as e:
    print(f"Error: {e}")
