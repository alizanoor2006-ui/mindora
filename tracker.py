import time
import requests
import json
import os
import sys
from datetime import datetime

# Configuration
CONFIG_FILE = 'tracker_config.json'
API_URL = 'http://localhost/project/api_handler.php'
SYNC_INTERVAL = 60  # Sync ever 60 seconds

def get_active_window_title():
    try:
        import pygetwindow as gw
        win = gw.getActiveWindow()
        if win and hasattr(win, 'title'):
            return win.title
        return "Idle"
    except ImportError:
        print("Error: Missing 'pygetwindow' library. Run: pip install pygetwindow pywin32")
        return None
    except Exception as e:
        return "Unknown"

def load_config():
    if os.path.exists(CONFIG_FILE):
        try:
            with open(CONFIG_FILE, 'r') as f:
                return json.load(f)
        except:
            return None
    return None

def save_config(user_id):
    with open(CONFIG_FILE, 'w') as f:
        json.dump({'user_id': user_id}, f)

def sync_data(user_id, seconds):
    try:
        data = {
            'action': 'auto_sync_screen',
            'seconds': seconds,
            'user_id': user_id
        }
        response = requests.post(API_URL, data=data, timeout=5)
        return response.json()
    except Exception as e:
        print(f"Sync failed: {e}")
        return None

def main():
    print("="*50)
    print("   Mindora Desktop Tracker 🌿")
    print("   Automatic Screen Time Monitoring")
    print("="*50)

    # Dependency Check
    try:
        import pygetwindow
        import requests
    except ImportError:
        print("\n[!] Setup Required: Missing Python libraries.")
        print("Please run: pip install pygetwindow pywin32 requests")
        sys.exit(1)

    config = load_config()
    if not config:
        print("\nWelcome! We need your Mindora User ID (find it in Settings).")
        uid = input("Enter User ID: ").strip()
        if not uid.isdigit():
            print("Error: User ID must be a number.")
            sys.exit(1)
        save_config(uid)
        config = {'user_id': uid}

    user_id = config['user_id']
    print(f"\n[✔] Tracking active for User #{user_id}")
    print("   Interval: 10s check | 60s sync")
    print("   Press CTRL+C to stop.\n")

    last_sync_time = time.time()
    accumulated_seconds = 0

    try:
        while True:
            time.sleep(10)
            title = get_active_window_title()
            
            if title and title != "Idle" and title != "":
                accumulated_seconds = accumulated_seconds + 10
            
            if time.time() - last_sync_time >= SYNC_INTERVAL:
                if accumulated_seconds > 0:
                    print(f"[{datetime.now().strftime('%H:%M:%S')}] Syncing {accumulated_seconds}s...")
                    res = sync_data(user_id, accumulated_seconds)
                    if res and res.get('status') == 'success':
                        print(f"   Success! Today's Total: {res.get('current_total')} hrs")
                    else:
                        print(f"   Error: {res.get('message') if res else 'Connection error'}")
                
                accumulated_seconds = 0
                last_sync_time = time.time()

    except KeyboardInterrupt:
        print("\nTracker paused. See you later! 🌸")

if __name__ == "__main__":
    main()
