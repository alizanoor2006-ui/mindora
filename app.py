import sys
from flask import Flask, request, jsonify
from flask_cors import CORS
from textblob import TextBlob
import re
import random
from groq import Groq
import os
import json
from dotenv import load_dotenv

# Load environment variables
load_dotenv(override=True)

# Groq (Llama 3) Configuration
GROQ_API_KEY = os.environ.get("GROQ_API_KEY")
if GROQ_API_KEY:
    GROQ_API_KEY = GROQ_API_KEY.strip()

client = Groq(api_key=GROQ_API_KEY) if GROQ_API_KEY else None
# Robust Model Configuration for Groq
MODELS = [
    "llama-3.3-70b-versatile",
    "llama-3.1-8b-instant",
    "gemma2-9b-it",
    "mixtral-8x7b-32768"
]

app = Flask(__name__)
CORS(app)

def call_ai(messages_or_prompt, is_json=False, temperature=0.7):
    """Calls Groq API with automatic fallback between models. 
       Accepts either a string prompt or a list of messages."""
    
    # Standardize input to message list
    if isinstance(messages_or_prompt, str):
        messages = [{"role": "user", "content": messages_or_prompt}]
    else:
        messages = messages_or_prompt

    for model_id in MODELS:
        try:
            print(f"DEBUG - Attempting {model_id}...", file=sys.stderr)
            kwargs = {
                "model": model_id,
                "messages": messages,
                "temperature": temperature
            }
            if is_json:
                kwargs["response_format"] = {"type": "json_object"}
                
            response = client.chat.completions.create(**kwargs)
            res_text = response.choices[0].message.content.strip()
            
            print(f"DEBUG - SUCCESS with {model_id}", file=sys.stderr)
            return res_text
        except Exception as e:
            err_msg = str(e)
            pid = os.getpid()
            print(f"DEBUG - PID {pid} - Failed {model_id}: {err_msg[:100]}", file=sys.stderr)
            
            # Stop for auth errors
            if "401" in err_msg or "403" in err_msg:
                break
            continue
            
    return None

@app.route('/api/sentiment', methods=['POST'])
def analyze_sentiment():
    data = request.json
    text = data.get('text', '')
    if not text:
        return jsonify({"error": "No text provided"}), 400
    
    analysis = TextBlob(text)
    score = analysis.sentiment.polarity
    
    prompt = f"The user wrote this journal entry: '{text}'. Provide a short, empathetic reflection (max 2 sentences) and a supportive suggestion for their well-being."
    reflection = call_ai(prompt)
    if not reflection:
        pid = os.getpid()
        reflection = f"It seems you're feeling down. Try listening to calming audio (Ref: {pid})." if score < -0.1 else f"Keep journaling to track these feelings! (Ref: {pid})"

    label = "Neutral"
    if score > 0.1:
        label = "Positive"
    elif score < -0.1:
        label = "Negative"
        
    return jsonify({
        "score": score,
        "label": label,
        "suggestion": reflection
    })

@app.route('/api/quiz', methods=['POST'])
def calculate_stress():
    data = request.json
    answers = data.get('answers', [])
    
    if not answers:
        return jsonify({"error": "No answers provided"}), 400
        
    total_score = sum(answers)
    max_score = len(answers) * 5
    percentage = total_score / max_score
    
    level = "High" if percentage > 0.7 else ("Medium" if percentage > 0.4 else "Low")

    prompt = f"A student scored {total_score}/{max_score} on a stress quiz (Level: {level}). Provide one actionable tip in 1 short sentence."
    insight = call_ai(prompt)
    if not insight:
        pid = os.getpid()
        insight = f"Please visit the Insights tab for grounding exercises (Ref: {pid})." if level == "High" else f"You are managing well. Keep up the good work! (Ref: {pid})"
        
    return jsonify({
        "score": total_score,
        "percentage": percentage,
        "stress_level": level,
        "insight": insight
    })

@app.route('/api/suggestions', methods=['POST'])
def get_ai_suggestions():
    data = request.json
    screen_time = data.get('screen_time', 0)
    sleep_time = data.get('sleep_time', 0)
    mood = data.get('mood', 'Neutral')
    
    prompt = (
        f"A student has {screen_time}h screen time, {sleep_time}h sleep, and feels {mood}. "
        "Provide a personalized mental health tip in JSON format like this: { \"title\": \"...\", \"desc\": \"...\" }."
    )

    res_text = call_ai(prompt, is_json=True)
    if res_text:
        try:
            suggestion = json.loads(res_text)
        except:
            pid = os.getpid()
            suggestion = { "title": "Stay Mindful 🌿", "desc": f"Take a moment to breathe deeply and reflect on your day (Ref: {pid})." }
    else:
        fallback_suggestions = [
            { "title": "Nature Timeout 🌿", "desc": "Step outside for 2 minutes and notice 3 things you see in nature." },
            { "title": "The 4-7-8 Breath 🧘", "desc": "Inhale for 4s, hold for 7s, exhale for 8s to calm your nervous system." },
            { "title": "Hydration Check 💧", "desc": "Drink a full glass of water. Your brain needs it for focus!" },
            { "title": "Digital Detox 📱", "desc": "Set your phone aside for 15 minutes and enjoy the 'now' around you." }
        ]
        suggestion = random.choice(fallback_suggestions)

    return jsonify(suggestion)

@app.route('/api/chatbot', methods=['POST'])
def chatbot_response():
    data = request.json
    message = data.get('message', '')
    history = data.get('history', []) # Expecting [{role: 'user', content: '...'}, {role: 'assistant', content: '...'}]
    
    if not message:
        return jsonify({"response": "I'm here for you. Tell me what's on your mind."})

    system_msg = {
        "role": "system",
        "content": (
            "You are Mindora, a warm, supportive, and solution-focused best friend for students. "
            "Your personality is friendly, empathetic, and uses moderate emojis. "
            "Strict Rules:\n"
            "1. If the user asks for a PLAN (diet plan, timetable, study schedule, routine), you MUST generate a clear step-by-step structure using bullet points or numbered lists.\n"
            "2. If a plan requires information like age, weight, goals, or study hours that hasn't been provided yet, politely ask for it BEFORE creating the plan.\n"
            "3. If the user asks for ADVICE, give practical, actionable suggestions that a student can easily follow.\n"
            "4. Always stay relevant to the user's specific question or information.\n"
            "5. Keep responses supportive and positive, while remaining practical and useful.\n"
            "6. Avoid long, overwhelming blocks of text; use spacing and formatting for clarity."
        )
    }

    # Construct message sequence
    all_messages = [system_msg] + history + [{"role": "user", "content": message}]

    reply = call_ai(all_messages, temperature=0.7)
    if not reply:
        fallback_replies = [
            "I'm here for you! Tell me more about how you're feeling today? 🌿",
            "That sounds like a lot to handle. Remember to be kind to yourself. 🌸",
            "I might be a bit slow right now, but I'm always listening. ✨",
            "You're doing great. What's one small thing that made you smile today? 🍃",
            "I'm always here to chat. Take a deep breath with me. 🧘"
        ]
        reply = random.choice(fallback_replies)

    return jsonify({"response": reply})

@app.route('/api/curated_insights', methods=['POST'])
def get_curated_insights():
    data = request.json
    mood = data.get('mood', 'Neutral')
    journals = " | ".join(data.get('journals', []))
    chats = " | ".join(data.get('chats', []))
    screen = data.get('screen_time', 0)
    sleep = data.get('sleep_time', 0)
    available = data.get('available_resources', [])

    # Format available resources for the prompt
    resource_list = "\n".join([f"- ID: {r['id']}, Title: {r['title']}, Topic: {r['topic']}, Type: {r['category']}" for r in available])

    prompt = (
        f"User Data: Mood={mood}, Screen={screen}h, Sleep={sleep}h. "
        f"Thoughts: {journals}. Chat Context: {chats}.\n\n"
        "Available Admin Resources:\n"
        f"{resource_list}\n\n"
        "Pick the top 3 MOST relevant resources from the 'Available Admin Resources' list above. "
        "Respond ONLY with a JSON array of the IDs you chose format like this: [1, 2, 3]. "
        "Strictly pick from the list. If the list is empty, return []."
    )

    res_text = call_ai(prompt) 
    print(f"DEBUG - Recommendations raw AI response: {res_text}", file=sys.stderr)

    if res_text:
        try:
            # Clean up potential markdown code blocks
            res_text = res_text.replace('```json', '').replace('```', '').strip()
            
            import re
            match = re.search(r'\[.*\]', res_text, re.DOTALL)
            if match:
                selected_ids = json.loads(match.group())
                print(f"DEBUG - Parsed IDs: {selected_ids}", file=sys.stderr)
                
                # Robust ID matching (handling string vs int)
                id_strings = [str(sid) for sid in selected_ids]
                suggestions = [r for r in available if str(r.get('id')) in id_strings]
                
                print(f"DEBUG - Total matched resources: {len(suggestions)}", file=sys.stderr)
                if suggestions:
                    return jsonify(suggestions)
        except Exception as e:
            print(f"DEBUG - Recommendations Error: {e}", file=sys.stderr)
    
    # Final Fallback: If AI fails but we have resources, show the first 3
    if available:
        print("DEBUG - AI failed, falling back to top 3 library items", file=sys.stderr)
        return jsonify(available[:3])

    return jsonify([])

@app.route('/api/quick_tip', methods=['GET'])
def get_quick_tip():
    fallback_tips = [
        "Take a deep breath. You're doing great. 🌿",
        "Small steps lead to big progress. 🌸",
        "Your mental health is a priority. 🧘",
        "Stay hydrated and take short breaks. 💧",
        "You've got this, one moment at a time. ✨"
    ]
    
    # Try AI first, but with a fallback if it fails/rate limits
    tip = call_ai("Short inspiring student tip (max 10 words).")
    if not tip:
        tip = random.choice(fallback_tips)
    return jsonify({"tip": tip})

@app.route('/api/todo', methods=['POST'])
def generate_todo_list():
    data = request.json
    mood = data.get('mood', 'Neutral')
    journals = " | ".join(data.get('journals', []))
    screen_time = data.get('screen_time', 0)
    sleep_time = data.get('sleep_time', 0)

    prompt = (
        f"Context: Mood is {mood}, Screen Time is {screen_time}h, Sleep is {sleep_time}h. "
        f"Recent Thoughts: {journals}. "
        "Based on this, generate exactly 3 short, actionable wellness tasks for today. "
        "Respond ONLY with a JSON object in this format: { \"todos\": [\"task 1\", \"task 2\", \"task 3\"] }"
    )

    res_text = call_ai(prompt, is_json=True)
    if res_text:
        try:
            return jsonify(json.loads(res_text))
        except:
            pass

    # Fallback
    return jsonify({
        "todos": [
            "Take a 5-minute deep breathing break 🧘",
            "Stretch for 2 minutes to release tension 🌸",
            "Drink a glass of water to stay hydrated! 💧"
        ]
    })

@app.route('/api/health', methods=['GET'])
def health_check():
    return jsonify({
        "status": "online", 
        "models_available": MODELS,
        "note": "Multi-model failover active via Groq"
    })

if __name__ == '__main__':
    print("Mindora AI Backend active on http://localhost:5000", file=sys.stderr)
    app.run(host="0.0.0.0", port=5000, debug=True)
