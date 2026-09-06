/**
 * In-memory & Persistent JSON store for CGJobs Backend
 */
const fs = require('fs');
const path = require('path');
const { SEED_SECTIONS, SEED_CATEGORIES, SEED_NEWS, SEED_ALERTS, SEED_STATIC_GK } = require('./seedData');

const DB_FILE = path.join(__dirname, 'database.json');

class DataStore {
  constructor() {
    this.sections = [];
    this.news = [];
    this.categories = [];
    this.staticGk = [];
    this.alerts = [];
    this.deviceTokens = [];
    this.settings = {
      fcmServerKey: process.env.FCM_SERVER_KEY || "",
      firebaseProjectId: process.env.FIREBASE_PROJECT_ID || "",
      newsApiKey: process.env.NEWS_API_KEY || "",
      newsDataApiKey: process.env.NEWSDATA_API_KEY || "",
      autoSyncMinutes: parseInt(process.env.AUTO_SYNC_INTERVAL_MINUTES || "60", 10),
      autoPushOnSync: true,
      lastSyncNewsApi: null,
      lastSyncNewsData: null
    };
    this.adminUser = {
      username: process.env.ADMIN_USERNAME || "admin",
      password: process.env.ADMIN_PASSWORD || "admin123",
      email: "admin@cgjobs.in"
    };
    this.activityLogs = [];
    this.load();
  }

  load() {
    try {
      if (fs.existsSync(DB_FILE)) {
        const raw = fs.readFileSync(DB_FILE, 'utf8');
        const data = JSON.parse(raw);
        this.sections = data.sections && data.sections.length > 0 ? data.sections : [...SEED_SECTIONS];
        this.news = data.news || [];
        this.staticGk = data.staticGk && data.staticGk.length > 0 ? data.staticGk : [...SEED_STATIC_GK];
        
        // Ensure categories have section tags and include seed categories
        const existingCats = data.categories || [];
        const existingIds = new Set(existingCats.map(c => c.id));
        
        // Merge seed categories if missing
        const mergedCats = existingCats.map(cat => {
          if (!cat.section) {
            if (cat.id.includes('gk') || ['history', 'geography'].includes(cat.id)) cat.section = 'static_gk';
            else if (cat.id.includes('ca') || cat.id === 'current_affairs' || ['national', 'international', 'economy', 'sports'].includes(cat.id)) cat.section = 'news';
            else cat.section = 'jobs';
          }
          return cat;
        });

        SEED_CATEGORIES.forEach(sc => {
          if (!existingIds.has(sc.id)) {
            mergedCats.push(sc);
          }
        });

        this.categories = mergedCats;
        this.alerts = data.alerts || [];
        this.deviceTokens = data.deviceTokens || [];
        if (data.settings) {
          this.settings = { ...this.settings, ...data.settings };
        }
        if (data.adminUser) {
          this.adminUser = { ...this.adminUser, ...data.adminUser };
        }
        this.activityLogs = data.activityLogs || [];
      } else {
        // Seed default initial records
        this.sections = [...SEED_SECTIONS];
        this.news = [...SEED_NEWS];
        this.categories = [...SEED_CATEGORIES];
        this.staticGk = [...SEED_STATIC_GK];
        this.alerts = [...SEED_ALERTS];
        this.deviceTokens = [
          {
            token: "demo_fcm_token_samsung_galaxy_s24",
            deviceModel: "Samsung Galaxy S24",
            platform: "Android 14",
            registeredAt: new Date().toISOString(),
            lastActive: new Date().toISOString()
          },
          {
            token: "demo_fcm_token_redmi_note_13",
            deviceModel: "Redmi Note 13 Pro",
            platform: "Android 13",
            registeredAt: new Date(Date.now() - 86400000).toISOString(),
            lastActive: new Date().toISOString()
          }
        ];
        this.logActivity("System initialized with default seed data");
        this.save();
      }
    } catch (e) {
      console.error("Error reading database.json, initializing from memory:", e.message);
      this.sections = [...SEED_SECTIONS];
      this.news = [...SEED_NEWS];
      this.categories = [...SEED_CATEGORIES];
      this.staticGk = [...SEED_STATIC_GK];
      this.alerts = [...SEED_ALERTS];
    }
  }

  save() {
    try {
      const data = {
        sections: this.sections,
        categories: this.categories,
        news: this.news,
        staticGk: this.staticGk,
        alerts: this.alerts,
        deviceTokens: this.deviceTokens,
        settings: this.settings,
        adminUser: this.adminUser,
        activityLogs: this.activityLogs.slice(-100) // keep last 100 logs
      };
      fs.writeFileSync(DB_FILE, JSON.stringify(data, null, 2), 'utf8');
    } catch (e) {
      console.error("Error persisting database.json:", e.message);
    }
  }

  logActivity(message, meta = {}) {
    const entry = {
      id: "log_" + Date.now() + "_" + Math.random().toString(36).substring(2, 7),
      timestamp: new Date().toISOString(),
      message,
      meta
    };
    this.activityLogs.unshift(entry);
    if (this.activityLogs.length > 200) {
      this.activityLogs.pop();
    }
  }

  // --- News Operations ---
  getNews(filter = {}) {
    let result = [...this.news];
    if (filter.category && filter.category !== 'all' && filter.category !== 'All Jobs') {
      const catLower = filter.category.toLowerCase().trim();
      result = result.filter(n => 
        (n.category && n.category.toLowerCase().includes(catLower)) ||
        (n.category && catLower.includes(n.category.toLowerCase()))
      );
    }
    if (filter.query) {
      const q = filter.query.toLowerCase().trim();
      result = result.filter(n => 
        (n.title && n.title.toLowerCase().includes(q)) ||
        (n.summary && n.summary.toLowerCase().includes(q)) ||
        (n.category && n.category.toLowerCase().includes(q)) ||
        (n.source && n.source.toLowerCase().includes(q))
      );
    }
    return result;
  }

  getNewsById(id) {
    return this.news.find(n => n.id === id);
  }

  addNews(item) {
    const id = item.id || ("cg_" + Date.now() + "_" + Math.random().toString(36).substring(2, 6));
    const newItem = {
      id,
      title: item.title || "Untitled Update",
      summary: item.summary || "",
      detailedContent: item.detailedContent || item.summary || "",
      category: item.category || "All Jobs",
      source: item.source || "CG Jobs Portal",
      sourceUrl: item.sourceUrl || "https://cgstate.gov.in",
      imageUrl: item.imageUrl || null,
      publishedAt: item.publishedAt || new Date().toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }),
      relativeTime: "Just now",
      isBreaking: Boolean(item.isBreaking),
      isNew: true,
      vacancies: item.vacancies || null,
      eligibility: item.eligibility || null,
      ageLimit: item.ageLimit || null,
      selectionProcess: item.selectionProcess || null,
      officialNotificationUrl: item.officialNotificationUrl || null,
      applyUrl: item.applyUrl || null,
      importantDates: item.importantDates || {
        applicationStart: "जारी है",
        lastDate: "शीघ्र ही",
        examDate: null
      },
      createdAt: new Date().toISOString()
    };

    // Prepend to list
    this.news.unshift(newItem);
    this.logActivity(`Added news: "${newItem.title}" in ${newItem.category}`);
    this.save();
    return newItem;
  }

  updateNews(id, updates) {
    const index = this.news.findIndex(n => n.id === id);
    if (index === -1) return null;

    this.news[index] = {
      ...this.news[index],
      ...updates,
      updatedAt: new Date().toISOString()
    };
    this.logActivity(`Updated news [${id}]: "${this.news[index].title}"`);
    this.save();
    return this.news[index];
  }

  deleteNews(id) {
    const index = this.news.findIndex(n => n.id === id);
    if (index === -1) return false;
    const removed = this.news.splice(index, 1)[0];
    this.logActivity(`Deleted news [${id}]: "${removed.title}"`);
    this.save();
    return true;
  }

  // --- Section & Category Operations ---
  getSections() {
    return this.sections.map(sec => {
      const cats = this.getCategories(sec.id);
      let count = 0;
      if (sec.id === 'jobs') {
        count = this.news.filter(n => !n.category.toLowerCase().includes('current affairs')).length;
      } else if (sec.id === 'news') {
        count = this.news.filter(n => n.category.toLowerCase().includes('current affairs') || n.title.includes('समसामयिकी')).length;
      } else if (sec.id === 'static_gk') {
        count = this.staticGk.length;
      }
      return {
        ...sec,
        categories: cats,
        itemsCount: count
      };
    });
  }

  getCategories(section = null) {
    if (!section || section === 'all') {
      return this.categories;
    }
    const secLower = section.toLowerCase();
    return this.categories.filter(c => (c.section && c.section.toLowerCase() === secLower));
  }

  addCategory(category) {
    const section = category.section || 'jobs';
    const rawId = category.id || (category.name.toLowerCase().replace(/[^a-z0-9]/g, '_') + '_' + section);
    const id = rawId.toLowerCase();
    
    if (this.categories.some(c => c.id === id || (c.name.toLowerCase() === category.name.toLowerCase() && c.section === section))) {
      throw new Error(`Category already exists in section "${section}"`);
    }

    const newCat = {
      id,
      name: category.name,
      hindiName: category.hindiName || category.name,
      section: section
    };
    this.categories.push(newCat);
    this.logActivity(`Created category: "${newCat.name}" in [${section}]`);
    this.save();
    return newCat;
  }

  deleteCategory(id) {
    const index = this.categories.findIndex(c => c.id === id);
    if (index === -1) return false;
    const removed = this.categories.splice(index, 1)[0];
    this.logActivity(`Deleted category [${id}]: "${removed.name}" (${removed.section || 'general'})`);
    this.save();
    return true;
  }

  // --- Static GK Operations ---
  getStaticGk(filter = {}) {
    let result = [...this.staticGk];
    if (filter.category && filter.category !== 'all' && filter.category !== 'सभी (All)') {
      const catLower = filter.category.toLowerCase().trim();
      result = result.filter(gk => 
        (gk.category && gk.category.toLowerCase().includes(catLower)) ||
        (catLower.includes(gk.category ? gk.category.toLowerCase() : ''))
      );
    }
    if (filter.query) {
      const q = filter.query.toLowerCase().trim();
      result = result.filter(gk =>
        (gk.title && gk.title.toLowerCase().includes(q)) ||
        (gk.summary && gk.summary.toLowerCase().includes(q)) ||
        (gk.category && gk.category.toLowerCase().includes(q)) ||
        (gk.examTip && gk.examTip.toLowerCase().includes(q)) ||
        (gk.facts && gk.facts.some(f => f.toLowerCase().includes(q)))
      );
    }
    return result;
  }

  getStaticGkById(id) {
    return this.staticGk.find(gk => gk.id === id);
  }

  addStaticGk(item) {
    const id = item.id || ("gk_" + Date.now() + "_" + Math.random().toString(36).substring(2, 6));
    const newGk = {
      id,
      title: item.title,
      category: item.category || "सामान्य ज्ञान",
      summary: item.summary || "",
      facts: Array.isArray(item.facts) ? item.facts : (item.facts ? [item.facts] : []),
      examTip: item.examTip || null,
      relatedExam: item.relatedExam || "CGPSC व व्यापम",
      createdAt: new Date().toISOString()
    };
    this.staticGk.unshift(newGk);
    this.logActivity(`Added Static GK capsule: "${newGk.title}"`);
    this.save();
    return newGk;
  }

  updateStaticGk(id, updates) {
    const index = this.staticGk.findIndex(g => g.id === id);
    if (index === -1) return null;
    this.staticGk[index] = {
      ...this.staticGk[index],
      ...updates,
      updatedAt: new Date().toISOString()
    };
    this.logActivity(`Updated Static GK capsule [${id}]: "${this.staticGk[index].title}"`);
    this.save();
    return this.staticGk[index];
  }

  deleteStaticGk(id) {
    const index = this.staticGk.findIndex(g => g.id === id);
    if (index === -1) return false;
    const removed = this.staticGk.splice(index, 1)[0];
    this.logActivity(`Deleted Static GK [${id}]: "${removed.title}"`);
    this.save();
    return true;
  }

  // --- Alerts Operations ---
  getAlerts() {
    return this.alerts;
  }

  addAlert(alert) {
    const newAlert = {
      id: alert.id || ("alert_" + Date.now()),
      category: alert.category || "सूचना",
      title: alert.title,
      shortDescription: alert.shortDescription || alert.body || "",
      time: "Just now",
      type: alert.type || "BREAKING",
      isRead: false,
      articleId: alert.articleId || null,
      createdAt: new Date().toISOString()
    };
    this.alerts.unshift(newAlert);
    this.logActivity(`Dispatched alert: "${newAlert.title}"`);
    this.save();
    return newAlert;
  }

  // --- Device Tokens (Push Subscribers) ---
  registerDeviceToken(deviceInfo) {
    const { token, deviceModel, platform } = deviceInfo;
    if (!token) throw new Error("FCM Token is required");

    const existingIndex = this.deviceTokens.findIndex(d => d.token === token);
    const now = new Date().toISOString();

    if (existingIndex >= 0) {
      this.deviceTokens[existingIndex].lastActive = now;
      if (deviceModel) this.deviceTokens[existingIndex].deviceModel = deviceModel;
      if (platform) this.deviceTokens[existingIndex].platform = platform;
    } else {
      this.deviceTokens.push({
        token,
        deviceModel: deviceModel || "Android Device",
        platform: platform || "Android",
        registeredAt: now,
        lastActive: now
      });
      this.logActivity(`Registered new Android device for push notifications (${deviceModel || 'Android'})`);
    }
    this.save();
    return { success: true, count: this.deviceTokens.length };
  }

  getDeviceTokens() {
    return this.deviceTokens;
  }

  // --- Settings ---
  getSettings() {
    return { ...this.settings };
  }

  updateSettings(newSettings) {
    this.settings = { ...this.settings, ...newSettings };
    this.logActivity("Updated backend settings and API keys");
    this.save();
    return this.settings;
  }
}

module.exports = new DataStore();
