import urllib.request
import urllib.error

try:
    req = urllib.request.Request('http://127.0.0.1/project/login.php')
    response = urllib.request.urlopen(req, timeout=5)
    print("SUCCESS: ", response.status)
    print(response.read().decode('utf-8')[:300])
except urllib.error.URLError as e:
    print("ERROR: ", e)
