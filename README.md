# CGJobs Laravel 11 Admin Panel & REST API

छत्तीसगढ़ रोजगार, भर्ती (CGPSC, CG Vyapam, Police, Education) एवं करंट अफेयर्स मोबाइल ऐप के लिए **Laravel 11 Admin Panel**।

---

## 🌟 विशेषताएं (Features)

- **Admin Dashboard**: कुल भर्तियां, ब्रेकिंग न्यूज़, पुश अलर्ट्स और कनेक्टेड मोबाइल डिवाइसेस का रीयल-टाइम अवलोकन।
- **Job Recruitment Management (CRUD)**:
  - छत्तीसगढ़ के सभी विभागों (व्यापम, CGPSC, पुलिस, शिक्षक भर्ती आदि) की नई वैकेंसी पोस्ट करें।
  - पद संख्या, शैक्षणिक योग्यता, आयु सीमा, चयन प्रक्रिया एवं अंतिम तिथि दर्ज करें।
  - आधिकारिक अधिसूचना PDF एवं ऑनलाइन आवेदन लिंक जोड़ें।
  - **HOT/Breaking Badge** सेट करें।
- **Push Notification Center (Firebase Cloud Messaging - FCM)**:
  - एक क्लिक में सभी Android यूज़र्स (`/topics/all_users`) या पंजीकृत टोकन्स पर प्रवेश पत्र, रिजल्ट व ब्रेकिंग अलर्ट प्रसारित करें।
- **Automated News Fetcher**:
  - NewsData.io एवं NewsAPI.org से सरकारी नौकरी व करंट अफेयर्स की खबरों को स्वतः आयात व श्रेणीबद्ध करें।
- **100% Android REST API Compatible**:
  - Android Jetpack Compose ऐप के Retrofit क्लाइंट के साथ पूरी तरह से सिंक।

---

## 🚀 इंस्टॉलेशन व सेटअप (Quick Start)

### Option A: बिना PHP इंस्टॉल किए (Docker से 1-क्लिक)

```bash
cd laravel-backend
docker-compose up -d
```
एडमिन पैनल खुल जाएगा: **http://localhost:8000/admin**

---

### Option B: सामान्य PHP 8.2+ व Composer द्वारा

1. **डायरेक्टरी में जाएं व डिपेंडेंसी इंस्टॉल करें**:
   ```bash
   cd laravel-backend
   composer install
   ```

2. **Environment फाइल बनाएं**:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

3. **डेटाबेस माइग्रेट व डिफ़ॉल्ट डेटा सीड करें**:
   ```bash
   # SQLite डेटाबेस फाइल बनाएं (यदि मौजूद न हो)
   touch database/database.sqlite

   php artisan migrate --seed
   ```

4. **डेवलपमेंट सर्वर शुरू करें**:
   ```bash
   php artisan serve --port=8000
   ```
   अब ब्राउज़र में खोलें:
   - **Admin Portal**: [http://localhost:8000/admin](http://localhost:8000/admin)
   - **API Health Check**: [http://localhost:8000/api/health](http://localhost:8000/api/health)

---

## 🔐 डिफ़ॉल्ट एडमिन लॉगिन (Default Credentials)

- **URL**: `http://localhost:8000/admin`
- **Username**: `admin`
- **Password**: `admin123`

*(नोट: आप इसे `.env` फाइल में `ADMIN_USERNAME` व `ADMIN_PASSWORD` द्वारा बदल सकते हैं)*

---

## 📱 Android मोबाइल ऐप को Laravel बैकएंड से कनेक्ट करना

1. Android ऐप खोलें।
2. टॉप-लेफ्ट हैमबर्गर मेनू (**☰**) खोलें और **"सर्वर व एडमिन (Server & Admin)"** चुनें  
   *(अथवा **Profile** स्क्रीन में **"सर्वर व बैकएंड सेटिंग्स"** पर टैप करें)*।
3. सर्वर URL दर्ज करें:
   - **Android Emulator में टेस्ट करते समय**:
     ```
     http://10.0.2.2:8000/
     ```
   - **असली स्मार्टफोन (Physical Phone) पर टेस्ट करते समय** (कंप्यूटर और फोन एक ही वाई-फाई पर हों):
     ```
     http://192.168.x.x:8000/
     ```
   - **क्लाउड पर डिप्लॉय होने के बाद**:
     ```
     https://your-domain.com/
     ```
4. **"Test Connection"** पर टैप करें। हरा टिक दिखने पर **"Save & Connect"** दबाएं।
5. अब ऐप में दिखाई देने वाला समस्त डेटा आपके Laravel Admin Panel से रीयल-टाइम लोड होगा!

---

## 🔔 Firebase Push Notifications कॉन्फ़िगरेशन (वैकल्पिक)

यदि आप एडमिन पैनल से सीधे मोबाइल फोन पर अलर्ट भेजना चाहते हैं:

1. [Firebase Console](https://console.firebase.google.com/) में जाएं।
2. Project Settings → Cloud Messaging में जाकर **Server Key** कॉपी करें।
3. `laravel-backend/.env` में जोड़ें:
   ```env
   FCM_SERVER_KEY=your_firebase_cloud_messaging_server_key
   ```
4. एडमिन पैनल में **"पुश नोटिफिकेशन भेजें"** पर जाएं और नया अलर्ट पोस्ट करें।

---

## 📡 REST API एंडपॉइंट्स (Android Client Mapping)

| Method | Endpoint | Description |
|---|---|---|
| `GET` | `/api/health` | सर्वर स्टेटस व कनेक्टिविटी जांच |
| `GET` | `/api/news` | भर्ती व रोजगार अधिसूचनाओं की सूची (कैटेगरी व सर्च फिल्टर सहित) |
| `GET` | `/api/news/{id}` | एकल भर्ती का संपूर्ण विवरण |
| `GET` | `/api/categories` | सभी सक्रिय विभाग व श्रेणियां |
| `GET` | `/api/alerts` | प्रवेश पत्र, परीक्षा तिथि व ब्रेकिंग अलर्ट्स |
| `POST`| `/api/alerts/register-token` | मोबाइल ऐप FCM टोकन रजिस्ट्रेशन |
