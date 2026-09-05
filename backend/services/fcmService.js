/**
 * Firebase Cloud Messaging (FCM) Push Notification Service
 */
const https = require('https');
const store = require('../data/store');

class FCMService {
  constructor() {}

  /**
   * Send a push notification to registered Android devices or a topic
   * @param {Object} options
   * @param {string} options.title - Notification title
   * @param {string} options.body - Notification text body
   * @param {string} [options.category] - Category name
   * @param {string} [options.articleId] - Deep link article ID
   * @param {string} [options.imageUrl] - Big picture image URL
   * @param {string} [options.targetType] - 'topic' | 'all_tokens' | 'single_token'
   * @param {string} [options.targetValue] - Specific token or topic name
   */
  async sendNotification(options) {
    const {
      title,
      body,
      category = "CG Jobs Alert",
      articleId = null,
      imageUrl = null,
      targetType = "topic",
      targetValue = "all_users"
    } = options;

    if (!title || !body) {
      throw new Error("Title and body are required for push notification");
    }

    // 1. Also record into internal in-app alerts store
    const createdAlert = store.addAlert({
      category,
      title,
      shortDescription: body,
      type: "BREAKING",
      articleId
    });

    const settings = store.getSettings();
    const serverKey = settings.fcmServerKey;

    // Build standard FCM payload
    const payload = {
      notification: {
        title,
        body,
        sound: "default",
        icon: "ic_notification",
        color: "#059669",
        image: imageUrl || undefined
      },
      data: {
        title,
        body,
        category,
        articleId: articleId || "",
        click_action: "FLUTTER_NOTIFICATION_CLICK", // compatibility
        open_article: articleId || ""
      }
    };

    if (!serverKey) {
      // In development / before server key is entered, log simulation
      store.logActivity(`Simulated Push Notification [No FCM Server Key configured yet]: "${title}"`, {
        alertId: createdAlert.id,
        target: `${targetType}:${targetValue}`
      });
      return {
        success: true,
        simulated: true,
        message: "Notification recorded in alerts feed. To broadcast over device radios, please paste your FCM Server Key in Push Settings.",
        alert: createdAlert
      };
    }

    // 2. Dispatch via FCM HTTP legacy endpoint (supported widely across Firebase projects)
    try {
      const results = [];

      if (targetType === 'topic' || targetValue === 'all_users') {
        const topicPayload = {
          to: `/topics/${targetValue || 'all_users'}`,
          ...payload
        };
        const res = await this._postToFCM(serverKey, topicPayload);
        results.push(res);
      } else if (targetType === 'single_token') {
        const tokenPayload = {
          to: targetValue,
          ...payload
        };
        const res = await this._postToFCM(serverKey, tokenPayload);
        results.push(res);
      } else {
        // Broadcast to all registered tokens
        const tokens = store.getDeviceTokens().map(d => d.token);
        if (tokens.length === 0) {
          return {
            success: true,
            simulated: true,
            message: "No devices registered yet. Alert saved to in-app notification feed.",
            alert: createdAlert
          };
        }
        for (const token of tokens) {
          try {
            const tokenPayload = { to: token, ...payload };
            const res = await this._postToFCM(serverKey, tokenPayload);
            results.push(res);
          } catch (err) {
            console.error(`Failed to send to token ${token.substring(0, 10)}...:`, err.message);
          }
        }
      }

      store.logActivity(`Dispatched FCM Push Notification: "${title}" to ${targetType}`, { resultsCount: results.length });
      return {
        success: true,
        simulated: false,
        results,
        alert: createdAlert
      };
    } catch (error) {
      store.logActivity(`FCM dispatch error: ${error.message}`);
      throw error;
    }
  }

  _postToFCM(serverKey, payload) {
    return new Promise((resolve, reject) => {
      const dataString = JSON.stringify(payload);
      const options = {
        hostname: 'fcm.googleapis.com',
        port: 443,
        path: '/fcm/send',
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `key=${serverKey}`,
          'Content-Length': Buffer.byteLength(dataString)
        }
      };

      const req = https.request(options, (res) => {
        let responseBody = '';
        res.on('data', chunk => responseBody += chunk);
        res.on('end', () => {
          if (res.statusCode >= 200 && res.statusCode < 300) {
            try {
              resolve(JSON.parse(responseBody));
            } catch (e) {
              resolve({ raw: responseBody });
            }
          } else {
            reject(new Error(`FCM Error [${res.statusCode}]: ${responseBody}`));
          }
        });
      });

      req.on('error', (err) => reject(err));
      req.write(dataString);
      req.end();
    });
  }
}

module.exports = new FCMService();
