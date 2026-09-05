/**
 * NewsAPI.org Integration Service
 * Documentation: https://newsapi.org/docs
 */
const https = require('https');
const store = require('../data/store');

class NewsApiService {
  constructor() {}

  /**
   * Fetch latest news from NewsAPI.org
   * @param {Object} options
   * @param {string} [options.query] - Custom search query
   * @param {number} [options.pageSize] - Number of articles (default: 15)
   * @param {boolean} [options.autoImport] - Automatically save to news database
   */
  async fetchNews(options = {}) {
    const settings = store.getSettings();
    const apiKey = options.apiKey || settings.newsApiKey;

    if (!apiKey) {
      throw new Error("NewsAPI Key is not configured. Please add your key in Settings.");
    }

    const query = options.query || "Chhattisgarh OR CGPSC OR Vyapam OR 'Sarkari Naukri'";
    const pageSize = options.pageSize || 15;
    const encodedQuery = encodeURIComponent(query);
    const path = `/v2/everything?q=${encodedQuery}&language=hi&sortBy=publishedAt&pageSize=${pageSize}&apiKey=${apiKey}`;

    const rawArticles = await this._httpGet('newsapi.org', path);

    if (!rawArticles || !rawArticles.articles) {
      throw new Error(rawArticles.message || "Failed to parse NewsAPI response");
    }

    const normalizedItems = rawArticles.articles.map((art, idx) => {
      // Determine probable category
      let category = "Current Affairs";
      const titleLower = (art.title || "").toLowerCase();
      if (titleLower.includes("psc") || titleLower.includes("लोक सेवा")) category = "CGPSC";
      else if (titleLower.includes("vyapam") || titleLower.includes("व्यापम")) category = "CG Vyapam";
      else if (titleLower.includes("police") || titleLower.includes("पुलिस") || titleLower.includes("defence")) category = "Police & Defence";
      else if (titleLower.includes("teacher") || titleLower.includes("शिक्षक")) category = "Teaching";
      else if (titleLower.includes("result") || titleLower.includes("परिणाम")) category = "Result";
      else if (titleLower.includes("admit") || titleLower.includes("प्रवेश पत्र")) category = "Admit Card";

      const publishedDate = art.publishedAt ? new Date(art.publishedAt) : new Date();
      const dateStr = publishedDate.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });

      return {
        id: "newsapi_" + Date.now() + "_" + idx,
        title: art.title || "छत्तीसगढ़ समसामयिक समाचार",
        summary: art.description || art.content || "समाचार विवरण उपलब्ध नहीं है।",
        detailedContent: (art.content || art.description || "") + `\n\nस्रोत: ${art.source?.name || 'NewsAPI'}\nमूल लिंक: ${art.url}`,
        category,
        source: art.source?.name || "NewsAPI",
        sourceUrl: art.url || "https://newsapi.org",
        imageUrl: art.urlToImage || null,
        publishedAt: dateStr,
        relativeTime: "हाल ही में",
        isBreaking: false,
        isNew: true,
        vacancies: "नवीनतम अपडेट",
        eligibility: "विस्तृत विवरण हेतु मूल लिंक देखें",
        officialNotificationUrl: art.url,
        applyUrl: null,
        importantDates: {
          applicationStart: dateStr,
          lastDate: "जारी",
          examDate: null
        }
      };
    });

    store.updateSettings({ lastSyncNewsApi: new Date().toISOString() });

    if (options.autoImport) {
      let importedCount = 0;
      for (const item of normalizedItems) {
        // Simple deduplication check on title similarity
        const existing = store.news.find(n => n.title.trim().toLowerCase() === item.title.trim().toLowerCase());
        if (!existing) {
          store.addNews(item);
          importedCount++;
        }
      }
      store.logActivity(`Imported ${importedCount} articles from NewsAPI.org`);
      return {
        success: true,
        totalFetched: normalizedItems.length,
        importedCount,
        articles: normalizedItems
      };
    }

    return {
      success: true,
      totalFetched: normalizedItems.length,
      articles: normalizedItems
    };
  }

  _httpGet(hostname, path) {
    return new Promise((resolve, reject) => {
      const options = {
        hostname,
        port: 443,
        path,
        method: 'GET',
        headers: {
          'User-Agent': 'CGJobsApp/1.0'
        }
      };

      const req = https.request(options, (res) => {
        let body = '';
        res.on('data', chunk => body += chunk);
        res.on('end', () => {
          try {
            const parsed = JSON.parse(body);
            if (res.statusCode >= 200 && res.statusCode < 300) {
              resolve(parsed);
            } else {
              reject(new Error(parsed.message || `HTTP ${res.statusCode}: ${body}`));
            }
          } catch (e) {
            reject(new Error(`Failed to parse JSON response: ${body.substring(0, 100)}`));
          }
        });
      });

      req.on('error', (err) => reject(err));
      req.end();
    });
  }
}

module.exports = new NewsApiService();
