A Project Report 
On

**Mindora: AI-Powered Student Wellness Platform**

Submitted To
SAVITRIBAI PHULE PUNE UNIVERSITY
For Fulfilment
Of
TY BSc (Computer Science), Semester VI
ASHOKA CENTER FOR BUSINESS & COMPUTER STUDIES, NASHIK

Guided by: Asst.prof. [Insert Guide Name]

Submitted by:
[Your Name]
[Partner Name 1]
[Partner Name 2]

---

### ACKNOWLEDGEMENT

We would like to express our sincere gratitude to Dr. P.A. Ghosh, Principal of Ashoka Center for Business and Computer Studies, for his unwavering support and encouragement throughout the duration of this project. His visionary leadership has provided us with the necessary resources and guidance to pursue our academic endeavours. We extend our heartfelt thanks to Dr. Harsha Patil, Vice Principal, for her valuable insights and assistance in coordinating various aspects of this project. Her dedication to fostering academic excellence has been instrumental in shaping our journey. Our gratitude also goes to Mrs. Sonali Ingle, Head of the Department of Computer Science, for her expert guidance and mentorship. Her expertise and commitment to our academic growth have been invaluable in shaping the direction of this project. We would also like to thank our Project Guide, Asst.prof. [Insert Guide Name], for their invaluable support and guidance at every step of this project. Their expertise, patience, and encouragement have been instrumental in helping us navigate through challenges and achieve our goals. Finally, we extend our thanks to all faculty members and staff who have contributed to this project directly or indirectly. Their support and encouragement have been crucial in making this project a success.  
Thank you all for your unwavering support and belief in our abilities.

---

### INDEX

| Sr. No. | Content |
| :--- | :--- |
| 1. | Abstract |
| 2. | Introduction<br>○ Motivation<br>○ Problem Statement<br>○ Objective<br>○ Literature Survey<br>○ Project Scope and Limits |
| 3. | System Analysis<br>○ Existing System<br>○ Scope And Limits of Existing System<br>○ Requirement Analysis<br>○ System Features |
| 4. | System Design<br>○ Database Design |
| 5. | Implementation Details<br>○ Software / Hardware Specifications |
| 6. | Outputs And Reports Testing<br>○ Test Plan<br>○ Black Box Testing Test Cases<br>○ White Box Testing Test Cases<br>○ Results |
| 7. | Conclusion |
| 8. | Future Scope |
| 9. | Bibliography And References |

---

### PROJECT ABSTRACT

The Mindora Student Wellness Platform is a comprehensive web-based and AI-integrated system designed to promote, track, and educate students about their mental and emotional well-being. This system serves as a centralized hub for managing daily stress, offering detailed emotional tracking (mood, sleep, screen time), and providing diverse therapeutic interventions. It features a user-friendly, calming interface with category-wise curated insights (videos and articles), an interactive AI-powered journal with sentiment analysis, and a secure empathy-driven chatbot accessible only to registered students. The administrative backend provides efficient content management for wellness resources. Developed using HTML, CSS, JavaScript, PHP, MySQL, and a Python (Flask) backend integrating the Groq Cloud AI API (hosting Llama 3 & Gemma models), Mindora aims to address the growing demand for accessible digital mental health support while laying the groundwork for robust, fail-safe AI integration in student environments.

---

### INTRODUCTION

 **Motivation**
In an era of rapid digital academic pressure and evolving student lifestyles, mental health issues such as anxiety and burnout have gained severe prominence. However, many students lack knowledge about therapeutic exercises or immediate emotional support. Mindora aims to bridge this gap by creating a comprehensive digital platform that tracks emotional metrics alongside an AI-driven empathetic companion, promoting awareness and emotional engagement.

 **Problem Statement**
Students often struggle to find immediate, non-judgmental emotional support, detailed information on stress management, and a unified place to track their habits (sleep, screen time). Existing platforms lack a seamless, engaging user experience that integrates habit tracking with real-time, personalized AI empathy and curated resources. 

 **Objective**
The project aims to provide an extensive toolkit for student wellness, complete with detailed mood tracking and health metrics, to educate and inform users about their emotional patterns. Additionally, it will feature a diverse AI-driven repository that showcases personalized therapeutic articles and videos, encouraging users to explore creative ways to manage stress. A key focus is promoting mental health awareness by highlighting actionable insights generated through Natural Language Processing (NLP). The platform will be designed with a visually appealing (glassmorphism) and intuitive user interface to enhance user experience. Furthermore, the system will be structured to accommodate a robust Multi-Model AI Failover mechanism, allowing seamless uptime even when underlying AI APIs face rate limits.

 **Literature Survey**
- **Digital Mental Health Trends**: Studies show increasing demand for accessible wellness tools due to academic convenience and reduced clinical stigma.
- **AI in Therapy**: Research highlights conversational agents' (chatbots) role in engagement and immediate anxiety reduction.
- **Digital Journal Repositories**: Successful platforms use personalized recommendations and sentiment analysis to attract users.
- **Consumer Behaviour in Wellness**: Studies indicate that educating students about screen-time effects enhances healthier lifestyle adoption rates.
- **System Resilience Research**: Fail-over mechanisms in remote API calls are critical for increasing user trust and engagement.

 **Project Scope and Limits**

**Scope:**
1. **Centralized Wellness Management**
   Organizes user emotional data (mood, journal, sleep) with detailed timestamps and sentiment scores. Provides a comprehensive dashboard for rapid status review.
2. **Enhanced Student Engagement**
   Offers an empathetic AI Chatbot to educate and calm users during peak stress. Includes a comprehensive "Insights" library to provide actionable therapeutic resources.
3. **Advanced AI Integration**
   Python-based NLP evaluates journals to categorize sentiment (Positive, Negative, Neutral). Generates automated insight reports based on combined metrics.
4. **Efficient Data Tracking**
   Health metrics (screen/sleep) are monitored to prevent burnout. Generates automated stress quizzes for emotional analysis.

**Limitations:**
1. **Not a Clinical Replacement**
   Unlike professional medical platforms, Mindora cannot diagnose psychiatric conditions. It is purely a companion and tracking tool.
2. **Dependent on External APIs**
   The deep AI logic relies on the Groq Cloud API, requiring an internet connection (though a basic offline fallback is provided).
3. **No Integration with Wearables**
   The platform currently does not support automated data fetching from smartwatches (Apple Watch, Fitbit) for sleep/screen data; it requires manual or simulated input.
4. **No Direct Inter-Student Communication**
   The system is an individualized platform with no dedicated social forum, limiting peer-to-peer interactions.

---

### SYSTEM ANALYSIS

**EXISTING SYSTEMS**
Current student wellness platforms primarily rely on manual journaling or disconnected apps, leading to fragmented insights. Sleep and screen time are tracked via separate physical device apps, causing analysis errors. Emotional engagement is limited, with no proper access to tailored resources or an empathetic chatbot. Habit tracking is manual, making reporting tedious. Additionally, the absence of a centralized, AI-driven online presence restricts holistic mental health awareness.

**SCOPE AND LIMITATIONS OF EXISTING SYSTEMS**

**Scope:**
1. Manual tracking systems provide flexibility but at the cost of data synthesis efficiency.
2. Traditional offline journals allow total privacy, but lack actionable feedback.
3. Simple apps avoid technical complexity, but they lack advanced AI automation.

**Limitations:**
1. Time-Consuming – Switching between sleep trackers, journals, and resource sites is slow.
2. Human Errors – Risk of misinterpreting one's own emotional trends over time.
3. Lack of Empathic Interaction – No real-time feedback system for immediate emotional support.
4. No Data Insights – Existing apps lack automated cross-referencing (e.g., showing how screen time affects mood).
5. Limited Scalability – Cannot handle complex NLP text analysis efficiently on-device.

**Requirement Analysis**
1. **Admin Panel:**
   - Add, update, and manage wellness resources (articles, videos) and categories.
   - View overarching system metrics (if implemented).
2. **User Panel:**
   - Browse curated insights, personal dashboard, and historical data.
   - Interact with the AI Chatbot and AI Journal.
   - Record daily sleep, screen time, and mood.
3. **Security Features:**
   - User authentication (registration, login, secure sessioning).
   - Data validation for secure journaling.
4. **Automated AI Reports:**
   - Generate real-time sentiment analysis and therapeutic suggestions.

**System Features**
1. **User Features:**
   - **Dashboard & Tracking** – Log daily mood, sleep, and screen time easily.
   - **Mindful Journal & AI Sentiment** – Write entries and receive immediate analytical feedback.
   - **Mindora Chatbot** – Communicate with an empathetic AI entity.
   - **AI Curated Insights** – Find therapeutic resources uniquely matched to the student's current emotional state.
2. **Admin Features:**
   - **Resource Management** – Add and categorize helpful videos and articles for the AI to recommend.
3. **Security & Stability Features:**
   - **Secure Login & Authentication** – Ensures sensitive journal data protection.
   - **Multi-Model AI Failover** – A resilient Python Flask backend that switches between Groq-hosted AI models (Llama 3.3, Llama 3.1, Gemma 2, Mixtral) automatically if rate limits are exceeded, ensuring zero downtime.

---

### SYSTEM DESIGN

**DATABASE DESIGN**

- **users**: Stores `user_id`, `username`, `email`, `password_hash`.
- **mood_data**: Tracks daily emotions (`id`, `user_id`, `mood`, `date`).
- **screen_sleep_data**: Stores health metrics (`id`, `user_id`, `screen_time`, `sleep_time`, `date`).
- **journals**: Stores textual entries (`id`, `user_id`, `content`, `sentiment_score`, `sentiment_label`, `ai_insight`, `date`).
- **chatbot_logs**: Retains conversation history (`id`, `user_id`, `message`, `response`, `created_at`).
- **insights_content**: Admin library (`id`, `title`, `category`, `topic`, `url`).
- **quiz_results**: Records stress assessments (`id`, `user_id`, `score`, `stress_level`, `date`).

*(Note to student: Insert ER Diagrams and Table Structures here in your final Word Doc)*

---

### IMPLEMENTATION DETAILS

**Software / Hardware Specifications**

**Software:**
- Frontend: HTML5, CSS3, JavaScript (Vanilla), PHP (v8+)
- Backend Logic & AI: Python (v3.10+), Flask Web Framework, `groq` library, TextBlob
- Database: MySQL
- Local Environment: XAMPP for Apache & MySQL hosting
- API: Groq Cloud AI API (Llama 3.3 70B, Llama 3.1 8B, Gemma 2 9B, Mixtral 8x7B)

**Hardware:**
- Processor: Intel Core i3 / AMD Ryzen 3 or higher.
- RAM: Minimum 4GB (8GB recommended for running Python and XAMPP simultaneously).
- Storage: Minimum 500MB free space for project files and database.

---

### OUTPUTS AND REPORTS TESTING

**Test Plan**
Testing was conducted using a modular approach: 
1. **Unit Testing**: Verifying database queries in PHP and API key authentication in Python.
2. **Integration Testing**: Ensuring the PHP frontend successfully POSTs data to the Python Flask backend and parses the JSON response.
3. **System Testing**: End-to-end evaluation mimicking a highly stressed user interacting with multiple features rapidly.

**Black Box Testing Test Cases**
- *Test Case 1*: User inputs 12 hours of screen time and 2 hours of sleep, then opens the Insights page.
  *Expected*: System successfully passes data to AI, which returns an article regarding "Digital Detox" or "Sleep Hygiene."
  *Result*: Pass.

**White Box Testing Test Cases**
- *Test Case 1*: Provide a faulty/rate-limited API key to the `call_ai` Python function.
  *Expected*: The code catches the Exception, analyzes the API error (e.g., rate limits), loops to the next model in the `MODELS` array, and returns a valid response without crashing.
  *Result*: Pass.

**Results**
The platform met all functional requirements. The unique integration of an AI Failover system successfully mitigated external API unreliability, creating a robust, uninterrupted user experience for student wellness tracking.

---

### CONCLUSION
The Mindora project successfully demonstrates how modern AI, specifically Large Language Models like Meta's Llama 3 via Groq, can be securely and effectively integrated into student wellness applications. By thoroughly addressing the technical challenges of remote API limitations via an intelligent Python-PHP hybrid architecture, and providing a calming, cohesive UI, the platform proves to be an engaging, practical, and highly beneficial tool for managing academic and emotional stress.

---

### FUTURE SCOPE
- **PDF/Document Analysis**: Allowing students to upload study materials (PDFs) for the AI to summarize, directly reducing academic workload stress.
- **Push Notifications**: Mobile alerts to remind students to step away from screens or log their daily journals.
- **Wearable Integration**: Automatically pulling accurate sleep metrics from health devices via Bluetooth APIs.

---

### BIBLIOGRAPHY AND REFERENCES
1. Groq API Documentation: https://console.groq.com/docs
2. Flask Documentation (Python Web Framework): https://flask.palletsprojects.com/
3. PHP Official Documentation: https://www.php.net/docs.php
4. TextBlob: Simplified NLP Processing: https://textblob.readthedocs.io/
