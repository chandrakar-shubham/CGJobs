/**
 * NewsData.io Integration Service
 * Documentation: https://newsdata.io/docs
 */
const https = require('https');
const store = require('../data/store');

class NewsDataService {
  constructor() {}

  /**
   * Fetch latest news from NewsData.io
   * @param {Object} options
   * @param {string} [options.query] - Keywords
   * @param {string} [options.country] - Default: 'in'
   * @param {string} [options.language] - Default: 'hi,en'
   * @param {boolean} [options.autoImport] - Automatically save to news database
   */
  async fetchNews(options = {}) {
    const settings = store.getSettings();
    const apiKey = options.apiKey || settings.newsDataApiKey;

    if (!apiKey) {
      throw new Error("NewsData.io API Key is not configured. Please add your key in Settings.");
    }

    const query = options.query || "Chhattisgarh OR CGPSC OR 'छत्तीसगढ़ भर्ती'";
    const country = options.country || "in";
    const language = options.language || "hi,en";
    const encodedQuery = encodeURIComponent(query);
    const path = `/api/1/news?apikey=${apiKey}&q=${encodedQuery}&country=${country}&language=${encodeURIComponent(language)}`;

    const rawResponse = await this._httpGet('newsdata.io', path);

    if (!rawResponse || rawResponse.status !== 'success' || !rawResponse.results) {
      throw new Error(rawResponse.results?.message || rawResponse.message || "Failed to fetch from NewsData.io");
    }

    const normalizedItems = rawResponse.results.map((art, idx) => {
      // Determine category
      let category = "Current Affairs";
      const titleLower = (art.title || "").toLowerCase();
      if (titleLower.includes("psc") || titleLower.includes("लोक सेवा")) category = "CGPSC";
      else if (titleLower.includes("vyapam") || titleLower.includes("व्यापम")) category = "CG Vyapam";
      else if (titleLower.includes("police") || titleLower.includes("पुलिस")) category = "Police & Defence";
      else if (titleLower.includes("teacher") || titleLower.includes("शिक्षक") || titleLower.includes("shikshak")) category = "Teaching";
      else if (titleLower.includes("result") || titleLower.includes("परिणाम")) category = "Result";
      else if (titleLower.includes("admit") || titleLower.includes("प्रवेश पत्र")) category = "Admit Card";

      const pubDate = art.pubDate ? new Date(art.pubDate) : new Date();
      const dateStr = pubDate.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });

      return {
        id: "newsdata_" + Date.now() + "_" + idx,
        title: art.title || "छत्तीसगढ़ नवीनतम समाचार",
        summary: art.description || "संक्षिप्त विवरण उपलब्ध नहीं है।",
        detailedContent: (art.content || art.description || "") + `\n\nस्रोत: ${art.source_id || 'NewsData.io'}\nमूल लिंक: ${art.link}`,
        category,
        source: art.source_id || "NewsData.io",
        sourceUrl: art.link || "https://newsdata.io",
        imageUrl: art.image_url || null,
        publishedAt: dateStr,
        relativeTime: "हाल ही में",
        isBreaking: Boolean(art.keywords && art.keywords.includes("breaking")),
        isNew: true,
        vacancies: "नवीनतम अधिसूचना",
        eligibility: "विस्तृत विवरण हेतु मूल लिंक देखें",
        officialNotificationUrl: art.link,
        applyUrl: null,
        importantDates: {
          applicationStart: dateStr,
          lastDate: "जारी",
          examDate: null
        }
      };
    });

    store.updateSettings({ lastSyncNewsData: new Date().toISOString() });

    if (options.autoImport) {
      let importedCount = 0;
      for (const item of normalizedItems) {
        const existing = store.news.find(n => n.title.trim().toLowerCase() === item.title.trim().toLowerCase());
        if (!existing) {
          store.addNews(item);
          importedCount++;
        }
      }
      store.logActivity(`Imported ${importedCount} articles from NewsData.io`);
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
              reject(new Error(parsed.results?.message || parsed.message || `HTTP ${res.statusCode}: ${body}`));
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

module.exports = new NewsDataService();
