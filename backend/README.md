# CGJobs Backend & Admin Management Portal

Production-ready backend website and REST API service for **CGJobs (Chhattisgarh Recruitment & Current Affairs)** Android application.

## Features
- **Modern Web Admin Dashboard**: Beautiful responsive web interface to manage jobs, current affairs, exams, categories, push notifications, and settings.
- **Push Notification Center**: Powered by Firebase Cloud Messaging (FCM). Broadcast instant alerts to all app users (`/topics/all_users`) or specific device tokens.
- **NewsAPI.org Integration**: Automated & manual pulling of national & Chhattisgarh job news with deduplication.
- **NewsData.io Integration**: Hindi & regional news API integration with auto-categorization into CGPSC, Vyapam, Police, Teaching, and Current Affairs.
- **Full CRUD Management**: Create, edit, delete news articles, vacancies, eligibility, important dates, admit card links, and results.
- **Device & Subscriber Management**: Track installed mobile apps and registered device tokens.
- **Zero-Setup Persistence**: Works out of the box with file-backed JSON database (`data/database.json`) pre-populated with realistic Chhattisgarh job updates.

---

## Quick Start (Local Run)

```bash
cd backend
npm install
npm start
```
The server starts on `http://localhost:3000`:
- **Web Admin Portal**: `http://localhost:3000`
- **Default Username**: `admin`
- **Default Password**: `admin123`
- **REST API Endpoint**: `http://localhost:3000/api/news`

---

## How to Host on Free / Cloud Hosting Services

### 1. Render.com (Recommended Free Hosting)
1. Push your repository to GitHub or GitLab.
2. Sign up on [Render.com](https://render.com).
3. Click **New +** → **Web Service**.
4. Connect your repo and set:
   - **Root Directory**: `backend`
   - **Environment**: `Node`
   - **Build Command**: `npm install`
   - **Start Command**: `npm start`
5. Under **Environment Variables**, add:
   - `ADMIN_PASSWORD`: Your custom secure password
   - `FCM_SERVER_KEY`: (Optional) Your Firebase Cloud Messaging server key
   - `NEWS_API_KEY`: (Optional) Your NewsAPI.org key
   - `NEWSDATA_API_KEY`: (Optional) Your NewsData.io key
6. Click **Deploy Web Service**. You will receive an instant HTTPS URL (e.g., `https://cgjobs-backend.onrender.com`).

---

### 2. Railway.app
1. Go to [Railway.app](https://railway.app).
2. Click **New Project** → **Deploy from GitHub repo**.
3. Set the root directory to `backend`.
4. Add environment variables and deploy. Click **Settings** → **Generate Domain** to get your public URL.

---

### 3. VPS / cPanel / DigitalOcean / Hostinger
1. Upload the `backend/` folder to your server.
2. In terminal:
   ```bash
   cd backend
   npm install --production
   npm install -g pm2
   pm2 start server.js --name "cgjobs-backend"
   pm2 save
   pm2 startup
   ```
3. Configure Nginx reverse proxy to forward traffic on port 80/443 to `http://127.0.0.1:3000`.

---

## Connecting the Android App to Your Hosted Backend

1. In the Android App, open the navigation drawer or profile settings.
2. Tap on **"Server & Backend Settings"**.
3. Enter your hosted backend URL (e.g. `https://your-app.onrender.com`).
4. Tap **"Test Connection"**. Once verified with a green checkmark, the app will automatically fetch live updates from your backend server!
