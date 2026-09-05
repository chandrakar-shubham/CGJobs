/**
 * CGJobs Backend Server & Admin Portal
 * Express REST API + Management Dashboard
 */
require('dotenv').config();
const express = require('express');
const cors = require('cors');
const path = require('path');

const store = require('./data/store');
const fcmService = require('./services/fcmService');
const newsApiService = require('./services/newsApiService');
const newsDataService = require('./services/newsDataService');

const app = express();
const PORT = process.env.SERVER_PORT || (process.env.PORT && process.env.PORT !== '8080' ? process.env.PORT : 5000);

// Enable CORS for Android client & web dashboard
app.use(cors({
  origin: '*',
  methods: ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
  allowedHeaders: ['Content-Type', 'Authorization', 'x-admin-token']
}));

app.use(express.json({ limit: '10mb' }));
app.use(express.urlencoded({ extended: true }));

// Serve static assets for the Admin Dashboard Website
app.use(express.static(path.join(__dirname, 'public')));

// Simple in-memory token session tracker for admin dashboard
const activeAdminTokens = new Set(['dev_admin_token_default']);

const adminAuthMiddleware = (req, res, next) => {
  const token = req.headers['x-admin-token'] || req.query.token;
  if (token && activeAdminTokens.has(token)) {
    return next();
  }
  return res.status(401).json({ error: "Unauthorized: Invalid or expired admin session" });
};

// ==========================================
// 1. PUBLIC REST API (FOR ANDROID CLIENT)
// ==========================================

// Health / Server Ping
app.get('/api/health', (req, res) => {
  res.json({
    status: 'ok',
    appName: 'CGJobs Backend API',
    version: '1.0.0',
    serverTime: new Date().toISOString(),
    stats: {
      totalNews: store.news.length,
      categoriesCount: store.categories.length,
      alertsCount: store.alerts.length
    }
  });
});

// Fetch all news with optional filtering
app.get('/api/news', (req, res) => {
  const { category, query, limit = 50 } = req.query;
  const list = store.getNews({ category, query });
  res.json({
    success: true,
    count: list.length,
    news: list.slice(0, parseInt(limit, 10))
  });
});

// Fetch single news detail
app.get('/api/news/:id', (req, res) => {
  const item = store.getNewsById(req.params.id);
  if (!item) {
    return res.status(404).json({ error: "Article not found" });
  }
  res.json({ success: true, item });
});

// Fetch all categories
app.get('/api/categories', (req, res) => {
  res.json({
    success: true,
    categories: store.getCategories()
  });
});

// Fetch alerts / notifications feed
app.get('/api/alerts', (req, res) => {
  res.json({
    success: true,
    alerts: store.getAlerts()
  });
});

// Register Android Device Token for Push Notifications
app.post('/api/alerts/register-token', (req, res) => {
  try {
    const { token, deviceModel, platform } = req.body;
    if (!token) {
      return res.status(400).json({ error: "Token is required" });
    }
    const result = store.registerDeviceToken({ token, deviceModel, platform });
    res.json(result);
  } catch (err) {
    res.status(500).json({ error: err.message });
  }
});

// ==========================================
// 2. ADMIN AUTHENTICATION & DASHBOARD APIS
// ==========================================

// Admin Login
app.post('/api/admin/login', (req, res) => {
  const { username, password } = req.body;
  if (username === store.adminUser.username && password === store.adminUser.password) {
    const token = "admin_" + Date.now() + "_" + Math.random().toString(36).substring(2, 9);
    activeAdminTokens.add(token);
    store.logActivity(`Admin login successful (${username})`);
    return res.json({
      success: true,
      token,
      user: {
        username: store.adminUser.username,
        email: store.adminUser.email
      }
    });
  }
  return res.status(401).json({ error: "Invalid username or password" });
});

// Admin Logout
app.post('/api/admin/logout', adminAuthMiddleware, (req, res) => {
  const token = req.headers['x-admin-token'];
  if (token) activeAdminTokens.delete(token);
  res.json({ success: true });
});

// Change Admin Password
app.post('/api/admin/change-password', adminAuthMiddleware, (req, res) => {
  const { oldPassword, newPassword, email } = req.body;
  if (oldPassword !== store.adminUser.password) {
    return res.status(400).json({ error: "Current password is incorrect" });
  }
  if (!newPassword || newPassword.length < 5) {
    return res.status(400).json({ error: "New password must be at least 5 characters" });
  }

  store.adminUser.password = newPassword;
  if (email) store.adminUser.email = email;
  store.logActivity("Admin password updated");
  store.save();

  res.json({ success: true, message: "Password updated successfully" });
});

// Admin Dashboard Summary Metrics
app.get('/api/admin/stats', adminAuthMiddleware, (req, res) => {
  const news = store.news;
  const breakingCount = news.filter(n => n.isBreaking).length;
  const currentAffairsCount = news.filter(n => n.category === 'Current Affairs').length;

  res.json({
    success: true,
    stats: {
      totalNews: news.length,
      breakingCount,
      currentAffairsCount,
      totalCategories: store.categories.length,
      registeredDevices: store.deviceTokens.length,
      totalAlerts: store.alerts.length,
      lastSyncNewsApi: store.settings.lastSyncNewsApi,
      lastSyncNewsData: store.settings.lastSyncNewsData,
      fcmConfigured: Boolean(store.settings.fcmServerKey),
      newsApiConfigured: Boolean(store.settings.newsApiKey),
      newsDataConfigured: Boolean(store.settings.newsDataApiKey)
    }
  });
});

// Admin Activity Logs
app.get('/api/admin/logs', adminAuthMiddleware, (req, res) => {
  res.json({
    success: true,
    logs: store.activityLogs
  });
});

// ==========================================
// 3. NEWS MANAGEMENT (CRUD)
// ==========================================

app.post('/api/news', adminAuthMiddleware, async (req, res) => {
  try {
    const { title, category, summary } = req.body;
    if (!title || !category || !summary) {
      return res.status(400).json({ error: "Title, Category and Summary are required" });
    }

    const newItem = store.addNews(req.body);

    // If "pushNotification" flag is sent, trigger instant push
    if (req.body.pushNotification) {
      try {
        await fcmService.sendNotification({
          title: `[${category}] ${title}`,
          body: summary.substring(0, 120),
          category,
          articleId: newItem.id,
          targetType: "topic",
          targetValue: "all_users"
        });
      } catch (err) {
        console.error("Auto push on news create failed:", err.message);
      }
    }

    res.json({ success: true, item: newItem });
  } catch (err) {
    res.status(500).json({ error: err.message });
  }
});

app.put('/api/news/:id', adminAuthMiddleware, (req, res) => {
  const updated = store.updateNews(req.params.id, req.body);
  if (!updated) {
    return res.status(404).json({ error: "Item not found" });
  }
  res.json({ success: true, item: updated });
});

app.delete('/api/news/:id', adminAuthMiddleware, (req, res) => {
  const ok = store.deleteNews(req.params.id);
  if (!ok) {
    return res.status(404).json({ error: "Item not found" });
  }
  res.json({ success: true });
});

// ==========================================
// 4. CATEGORY MANAGEMENT
// ==========================================

app.post('/api/categories', adminAuthMiddleware, (req, res) => {
  try {
    const { name, hindiName } = req.body;
    if (!name) return res.status(400).json({ error: "Category name is required" });
    const cat = store.addCategory({ name, hindiName });
    res.json({ success: true, category: cat });
  } catch (err) {
    res.status(400).json({ error: err.message });
  }
});

app.delete('/api/categories/:id', adminAuthMiddleware, (req, res) => {
  const ok = store.deleteCategory(req.params.id);
  if (!ok) return res.status(404).json({ error: "Category not found" });
  res.json({ success: true });
});

// ==========================================
// 5. PUSH NOTIFICATIONS & SUBSCRIBERS
// ==========================================

// Send push notification
app.post('/api/notifications/send', adminAuthMiddleware, async (req, res) => {
  try {
    const { title, body, category, articleId, imageUrl, targetType, targetValue } = req.body;
    if (!title || !body) {
      return res.status(400).json({ error: "Title and body are required" });
    }

    const result = await fcmService.sendNotification({
      title,
      body,
      category: category || "सूचना",
      articleId,
      imageUrl,
      targetType: targetType || "topic",
      targetValue: targetValue || "all_users"
    });

    res.json(result);
  } catch (err) {
    res.status(500).json({ error: err.message });
  }
});

// List registered devices
app.get('/api/notifications/devices', adminAuthMiddleware, (req, res) => {
  res.json({
    success: true,
    devices: store.getDeviceTokens()
  });
});

// ==========================================
// 6. NEWSAPI & NEWSDATA.IO INTEGRATION
// ==========================================

// Pull from NewsAPI
app.post('/api/sync/newsapi', adminAuthMiddleware, async (req, res) => {
  try {
    const { query, pageSize, autoImport } = req.body;
    const result = await newsApiService.fetchNews({
      query,
      pageSize,
      autoImport: autoImport !== false
    });

    if (result.importedCount > 0 && store.settings.autoPushOnSync) {
      try {
        await fcmService.sendNotification({
          title: "नवीनतम अपडेट्स उपलब्ध!",
          body: `${result.importedCount} नए भर्ती समाचार एवं समसामयिकी जोड़े गए हैं।`,
          category: "Live Sync",
          targetType: "topic",
          targetValue: "all_users"
        });
      } catch (e) {
        console.error("Auto push on sync failed:", e.message);
      }
    }

    res.json(result);
  } catch (err) {
    res.status(500).json({ error: err.message });
  }
});

// Pull from NewsData.io
app.post('/api/sync/newsdata', adminAuthMiddleware, async (req, res) => {
  try {
    const { query, autoImport } = req.body;
    const result = await newsDataService.fetchNews({
      query,
      autoImport: autoImport !== false
    });

    if (result.importedCount > 0 && store.settings.autoPushOnSync) {
      try {
        await fcmService.sendNotification({
          title: "नवीनतम भर्ती अलर्ट!",
          body: `${result.importedCount} नए रोजगार समाचार लोड किए गए।`,
          category: "Live Sync",
          targetType: "topic",
          targetValue: "all_users"
        });
      } catch (e) {
        console.error("Auto push on sync failed:", e.message);
      }
    }

    res.json(result);
  } catch (err) {
    res.status(500).json({ error: err.message });
  }
});

// ==========================================
// 7. SETTINGS & CONFIGURATION
// ==========================================

app.get('/api/settings', adminAuthMiddleware, (req, res) => {
  const s = store.getSettings();
  // Mask keys partially for safe display
  res.json({
    success: true,
    settings: {
      ...s,
      fcmServerKeyMasked: s.fcmServerKey ? (s.fcmServerKey.substring(0, 8) + '...' + s.fcmServerKey.slice(-4)) : '',
      newsApiKeyMasked: s.newsApiKey ? (s.newsApiKey.substring(0, 6) + '...' + s.newsApiKey.slice(-4)) : '',
      newsDataApiKeyMasked: s.newsDataApiKey ? (s.newsDataApiKey.substring(0, 6) + '...' + s.newsDataApiKey.slice(-4)) : ''
    }
  });
});

app.post('/api/settings', adminAuthMiddleware, (req, res) => {
  try {
    const updated = store.updateSettings(req.body);
    res.json({ success: true, settings: updated });
  } catch (err) {
    res.status(500).json({ error: err.message });
  }
});

// Fallback route for Admin Dashboard SPA
app.get('*', (req, res) => {
  res.sendFile(path.join(__dirname, 'public', 'index.html'));
});

// Start Express Server
app.listen(PORT, '0.0.0.0', () => {
  console.log(`====================================================`);
  console.log(` CGJobs Backend & Admin Website running on port ${PORT}`);
  console.log(` Dashboard URL: http://localhost:${PORT}`);
  console.log(` REST API URL:  http://localhost:${PORT}/api/news`);
  console.log(` Health Check:  http://localhost:${PORT}/api/health`);
  console.log(`====================================================`);
});
