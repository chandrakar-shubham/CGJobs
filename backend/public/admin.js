/**
 * CGJobs Admin Dashboard Client Script
 */

const API_BASE = window.location.origin + '/api';
let adminToken = localStorage.getItem('cgjobs_admin_token') || 'dev_admin_token_default';
let currentNewsList = [];
let currentCategories = [];

// DOM Ready
document.addEventListener('DOMContentLoaded', () => {
  setupTabs();
  checkAuthAndLoad();
  setupEventListeners();
  updateApiEndpointLabel();
});

function updateApiEndpointLabel() {
  const lbl = document.getElementById('labelApiEndpoint');
  if (lbl) {
    lbl.textContent = window.location.origin + '/api';
  }
}

function showToast(message, isError = false) {
  const toast = document.getElementById('toast');
  const msg = document.getElementById('toastMsg');
  const icon = document.getElementById('toastIcon');

  msg.textContent = message;
  icon.innerHTML = isError ? '⚠️' : '✅';
  toast.classList.remove('translate-y-20', 'opacity-0');
  toast.classList.add('translate-y-0', 'opacity-100');

  setTimeout(() => {
    toast.classList.remove('translate-y-0', 'opacity-100');
    toast.classList.add('translate-y-20', 'opacity-0');
  }, 3500);
}

// Authentication Headers
function getHeaders() {
  return {
    'Content-Type': 'application/json',
    'x-admin-token': adminToken
  };
}

// Check session
async function checkAuthAndLoad() {
  try {
    const res = await fetch(`${API_BASE}/admin/stats`, { headers: getHeaders() });
    if (res.status === 401) {
      showLoginModal();
      return;
    }
    hideLoginModal();
    loadDashboard();
    loadNews();
    loadCategories();
    loadSettings();
    loadAlertsHistory();
    loadDevices();
  } catch (e) {
    console.error("Auth check failed:", e);
    // Fallback load public data
    loadNews();
    loadCategories();
  }
}

function showLoginModal() {
  document.getElementById('modalLogin').classList.remove('hidden');
}

function hideLoginModal() {
  document.getElementById('modalLogin').classList.add('hidden');
}

// Tab Switching
function setupTabs() {
  const tabs = document.querySelectorAll('.nav-tab');
  tabs.forEach(tab => {
    tab.addEventListener('click', () => {
      const target = tab.getAttribute('data-tab');
      switchTab(target);
    });
  });
}

function switchTab(tabName) {
  document.querySelectorAll('.nav-tab').forEach(t => {
    if (t.getAttribute('data-tab') === tabName) {
      t.classList.add('border-brand-600', 'text-brand-700', 'font-bold');
      t.classList.remove('border-transparent', 'text-slate-600');
    } else {
      t.classList.remove('border-brand-600', 'text-brand-700', 'font-bold');
      t.classList.add('border-transparent', 'text-slate-600');
    }
  });

  document.querySelectorAll('.tab-content').forEach(c => {
    c.classList.remove('active');
  });

  const activeContent = document.getElementById(`tab-${tabName}`);
  if (activeContent) {
    activeContent.classList.add('active');
  }

  // Refresh tab specific data
  if (tabName === 'dashboard') loadDashboard();
  if (tabName === 'news') loadNews();
  if (tabName === 'notifications') { loadAlertsHistory(); loadSettings(); }
  if (tabName === 'categories') loadCategories();
  if (tabName === 'users') loadDevices();
}

// ==========================================
// DATA LOADERS
// ==========================================

async function loadDashboard() {
  try {
    const res = await fetch(`${API_BASE}/admin/stats`, { headers: getHeaders() });
    const data = await res.json();
    if (data.success && data.stats) {
      const s = data.stats;
      document.getElementById('statTotalNews').textContent = s.totalNews || 0;
      document.getElementById('statBreakingNews').textContent = s.breakingCount || 0;
      document.getElementById('statCategories').textContent = s.totalCategories || 0;
      document.getElementById('statSubscribers').textContent = s.registeredDevices || 0;
      document.getElementById('statAlerts').textContent = s.totalAlerts || 0;

      // Status badges
      const badgeFcm = document.getElementById('badgeFcm');
      const textFcm = document.getElementById('textFcm');
      if (s.fcmConfigured) {
        badgeFcm.className = "w-2.5 h-2.5 rounded-full bg-emerald-500";
        textFcm.textContent = "FCM Key Active";
      } else {
        badgeFcm.className = "w-2.5 h-2.5 rounded-full bg-amber-400";
        textFcm.textContent = "Key needed (Simulated mode)";
      }

      const badgeNewsApi = document.getElementById('badgeNewsApi');
      const textNewsApi = document.getElementById('textNewsApi');
      if (s.newsApiConfigured) {
        badgeNewsApi.className = "w-2.5 h-2.5 rounded-full bg-emerald-500";
        textNewsApi.textContent = s.lastSyncNewsApi ? `Synced ${new Date(s.lastSyncNewsApi).toLocaleTimeString()}` : "Key Active";
      } else {
        badgeNewsApi.className = "w-2.5 h-2.5 rounded-full bg-slate-300";
        textNewsApi.textContent = "Key optional";
      }

      const badgeNewsData = document.getElementById('badgeNewsData');
      const textNewsData = document.getElementById('textNewsData');
      if (s.newsDataConfigured) {
        badgeNewsData.className = "w-2.5 h-2.5 rounded-full bg-emerald-500";
        textNewsData.textContent = s.lastSyncNewsData ? `Synced ${new Date(s.lastSyncNewsData).toLocaleTimeString()}` : "Key Active";
      } else {
        badgeNewsData.className = "w-2.5 h-2.5 rounded-full bg-slate-300";
        textNewsData.textContent = "Key optional";
      }
    }

    loadLogs();
  } catch (e) {
    console.error("Dashboard stats error:", e);
  }
}

async function loadLogs() {
  try {
    const res = await fetch(`${API_BASE}/admin/logs`, { headers: getHeaders() });
    const data = await res.json();
    const container = document.getElementById('logsContainer');
    if (!data.logs || data.logs.length === 0) {
      container.innerHTML = '<div class="py-2 text-xs text-slate-400">No activity logged yet.</div>';
      return;
    }

    container.innerHTML = data.logs.slice(0, 15).map(log => `
      <div class="py-2.5 flex items-center justify-between text-xs">
        <div class="flex items-center space-x-2">
          <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
          <span class="text-slate-800 font-medium">${escapeHtml(log.message)}</span>
        </div>
        <span class="text-slate-400 text-[11px] whitespace-nowrap">${new Date(log.timestamp).toLocaleTimeString()}</span>
      </div>
    `).join('');
  } catch (e) {
    console.error("Logs error:", e);
  }
}

// ==========================================
// NEWS CRUD
// ==========================================

async function loadNews() {
  try {
    const category = document.getElementById('newsCategoryFilter')?.value || 'all';
    const query = document.getElementById('newsSearchInput')?.value || '';
    const res = await fetch(`${API_BASE}/news?category=${encodeURIComponent(category)}&query=${encodeURIComponent(query)}&limit=100`);
    const data = await res.json();
    currentNewsList = data.news || [];
    renderNewsTable(currentNewsList);
  } catch (e) {
    console.error("Load news error:", e);
  }
}

function renderNewsTable(items) {
  const tbody = document.getElementById('newsTableBody');
  if (!items || items.length === 0) {
    tbody.innerHTML = `<tr><td colspan="5" class="px-4 py-8 text-center text-slate-400">No news articles found matching filter.</td></tr>`;
    return;
  }

  tbody.innerHTML = items.map(item => `
    <tr class="hover:bg-slate-50 transition border-b border-slate-100">
      <td class="px-4 py-3 align-top whitespace-nowrap">
        <span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold ${getCategoryBadgeColor(item.category)}">
          ${escapeHtml(item.category)}
        </span>
        ${item.isBreaking ? '<span class="ml-1 inline-block px-1.5 py-0.2 rounded text-[10px] font-bold bg-red-100 text-red-700">BREAKING</span>' : ''}
      </td>
      <td class="px-4 py-3 align-top max-w-sm">
        <div class="font-bold text-slate-900 text-xs">${escapeHtml(item.title)}</div>
        <div class="text-slate-500 text-[11px] line-clamp-2 mt-0.5">${escapeHtml(item.summary)}</div>
        <div class="text-[10px] text-slate-400 mt-1">Source: ${escapeHtml(item.source)}</div>
      </td>
      <td class="px-4 py-3 align-top whitespace-nowrap text-[11px]">
        <div class="font-semibold text-slate-800">${item.vacancies ? escapeHtml(item.vacancies) : '—'}</div>
        <div class="text-slate-500 text-[10px]">Last: ${item.importantDates?.lastDate ? escapeHtml(item.importantDates.lastDate) : '—'}</div>
      </td>
      <td class="px-4 py-3 align-top whitespace-nowrap text-[11px] text-slate-500">
        <div>${escapeHtml(item.publishedAt || '')}</div>
        <div class="text-[10px] text-slate-400">${escapeHtml(item.relativeTime || '')}</div>
      </td>
      <td class="px-4 py-3 align-top text-right whitespace-nowrap space-x-1">
        <button onclick="quickPushArticle('${item.id}')" title="Push to Android" class="px-2 py-1 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 rounded font-semibold text-[11px]">
          📢 Push
        </button>
        <button onclick="editArticle('${item.id}')" title="Edit" class="px-2 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded font-semibold text-[11px]">
          ✏️ Edit
        </button>
        <button onclick="deleteArticle('${item.id}')" title="Delete" class="px-2 py-1 bg-red-50 hover:bg-red-100 text-red-600 rounded font-semibold text-[11px]">
          🗑️
        </button>
      </td>
    </tr>
  `).join('');
}

function getCategoryBadgeColor(cat) {
  if (cat === 'CGPSC') return 'bg-purple-100 text-purple-800';
  if (cat === 'CG Vyapam') return 'bg-blue-100 text-blue-800';
  if (cat === 'Current Affairs') return 'bg-amber-100 text-amber-800';
  if (cat === 'Police & Defence') return 'bg-orange-100 text-orange-800';
  if (cat === 'Teaching') return 'bg-emerald-100 text-emerald-800';
  if (cat === 'Admit Card') return 'bg-sky-100 text-sky-800';
  if (cat === 'Result') return 'bg-rose-100 text-rose-800';
  return 'bg-slate-100 text-slate-800';
}

function openAddNewsModal() {
  document.getElementById('editNewsId').value = '';
  document.getElementById('modalNewsTitle').textContent = 'Add New Job / News Post';
  document.getElementById('formNewsItem').reset();
  populateCategorySelect('modalCategory');
  document.getElementById('modalNews').classList.remove('hidden');
}

function closeNewsModal() {
  document.getElementById('modalNews').classList.add('hidden');
}

function editArticle(id) {
  const item = currentNewsList.find(n => n.id === id);
  if (!item) return;

  document.getElementById('editNewsId').value = item.id;
  document.getElementById('modalNewsTitle').textContent = 'Edit News Post';
  populateCategorySelect('modalCategory', item.category);

  document.getElementById('modalTitle').value = item.title || '';
  document.getElementById('modalSummary').value = item.summary || '';
  document.getElementById('modalDetailedContent').value = item.detailedContent || '';
  document.getElementById('modalSource').value = item.source || '';
  document.getElementById('modalVacancies').value = item.vacancies || '';
  document.getElementById('modalEligibility').value = item.eligibility || '';
  document.getElementById('modalAgeLimit').value = item.ageLimit || '';
  document.getElementById('modalApplyUrl').value = item.applyUrl || '';
  document.getElementById('modalOfficialPdfUrl').value = item.officialNotificationUrl || '';
  document.getElementById('modalDateStart').value = item.importantDates?.applicationStart || '';
  document.getElementById('modalDateLast').value = item.importantDates?.lastDate || '';
  document.getElementById('modalDateExam').value = item.importantDates?.examDate || '';
  document.getElementById('modalIsBreaking').checked = Boolean(item.isBreaking);

  document.getElementById('modalNews').classList.remove('hidden');
}

async function deleteArticle(id) {
  if (!confirm("Are you sure you want to delete this news item?")) return;
  try {
    const res = await fetch(`${API_BASE}/news/${id}`, {
      method: 'DELETE',
      headers: getHeaders()
    });
    if (res.ok) {
      showToast("News article deleted successfully");
      loadNews();
      loadDashboard();
    } else {
      showToast("Failed to delete article", true);
    }
  } catch (e) {
    showToast("Error deleting article", true);
  }
}

async function quickPushArticle(id) {
  const item = currentNewsList.find(n => n.id === id);
  if (!item) return;

  try {
    const res = await fetch(`${API_BASE}/notifications/send`, {
      method: 'POST',
      headers: getHeaders(),
      body: JSON.stringify({
        title: `[${item.category}] ${item.title}`,
        body: item.summary.substring(0, 100) + '...',
        category: item.category,
        articleId: item.id,
        targetType: 'topic',
        targetValue: 'all_users'
      })
    });
    const data = await res.json();
    if (data.success) {
      showToast("Push notification dispatched to Android devices!");
      loadDashboard();
      loadAlertsHistory();
    } else {
      showToast(data.error || "Failed to push notification", true);
    }
  } catch (e) {
    showToast("Network error sending push", true);
  }
}

// ==========================================
// CATEGORIES & SECTIONS
// ==========================================

let currentCategorySectionFilter = 'all';

function filterCategorySection(section) {
  currentCategorySectionFilter = section;
  document.querySelectorAll('.cat-sec-btn').forEach(btn => {
    if (btn.getAttribute('data-sec') === section) {
      btn.className = 'cat-sec-btn px-3 py-1 text-xs font-bold rounded-lg bg-slate-900 text-white';
    } else {
      btn.className = 'cat-sec-btn px-3 py-1 text-xs font-bold rounded-lg bg-slate-100 text-slate-600 hover:bg-slate-200';
    }
  });
  renderCategoriesList();
}

async function loadCategories() {
  try {
    const res = await fetch(`${API_BASE}/categories`);
    const data = await res.json();
    currentCategories = data.categories || [];

    // Update section counters
    const jobsCount = currentCategories.filter(c => c.section === 'jobs').length;
    const newsCount = currentCategories.filter(c => c.section === 'news').length;
    const gkCount = currentCategories.filter(c => c.section === 'static_gk').length;
    
    if (document.getElementById('secCountJobs')) document.getElementById('secCountJobs').textContent = `${jobsCount} Categories`;
    if (document.getElementById('secCountNews')) document.getElementById('secCountNews').textContent = `${newsCount} Categories`;
    if (document.getElementById('secCountGk')) document.getElementById('secCountGk').textContent = `${gkCount} Categories`;

    // Update news filter dropdown
    const filter = document.getElementById('newsCategoryFilter');
    if (filter) {
      filter.innerHTML = `<option value="all">All Categories</option>` +
        currentCategories.map(c => `<option value="${c.name}">${c.name} [${c.section || 'jobs'}]</option>`).join('');
    }

    renderCategoriesList();
  } catch (e) {
    console.error("Load categories error:", e);
  }
}

function renderCategoriesList() {
  const list = document.getElementById('categoriesList');
  if (!list) return;

  const filtered = currentCategories.filter(c => {
    if (currentCategorySectionFilter === 'all') return true;
    return (c.section || 'jobs') === currentCategorySectionFilter;
  });

  if (filtered.length === 0) {
    list.innerHTML = `<div class="py-6 text-center text-xs text-slate-400">No categories found in this section.</div>`;
    return;
  }

  const sectionBadges = {
    jobs: '<span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">💼 Jobs</span>',
    news: '<span class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-800">📰 News</span>',
    static_gk: '<span class="px-2 py-0.5 rounded text-[10px] font-bold bg-purple-100 text-purple-800">📖 Static GK</span>'
  };

  list.innerHTML = filtered.map(c => {
    const sec = c.section || 'jobs';
    const badge = sectionBadges[sec] || `<span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-700">${sec}</span>`;
    return `
      <div class="py-3 flex items-center justify-between hover:bg-slate-50/50 px-2 rounded-xl transition">
        <div class="flex items-center space-x-3">
          ${badge}
          <div>
            <div class="font-bold text-xs text-slate-800">${escapeHtml(c.name)}</div>
            <div class="text-[11px] text-slate-500">${escapeHtml(c.hindiName || '')}</div>
          </div>
        </div>
        <button onclick="deleteCategory('${c.id}')" class="text-xs text-red-600 hover:text-red-700 font-semibold px-2.5 py-1 bg-red-50 hover:bg-red-100 rounded-lg transition">
          Delete
        </button>
      </div>
    `;
  }).join('');
}

function populateCategorySelect(selectId, selectedValue = '') {
  const sel = document.getElementById(selectId);
  if (!sel) return;
  sel.innerHTML = currentCategories.map(c => `
    <option value="${c.name}" ${c.name === selectedValue ? 'selected' : ''}>[${c.section || 'jobs'}] ${c.name} (${c.hindiName || ''})</option>
  `).join('');
}

async function deleteCategory(id) {
  if (!confirm("Are you sure you want to delete this category?")) return;
  try {
    const res = await fetch(`${API_BASE}/categories/${id}`, {
      method: 'DELETE',
      headers: getHeaders()
    });
    if (res.ok) {
      showToast("Category deleted");
      loadCategories();
      loadDashboard();
    } else {
      showToast("Cannot delete category", true);
    }
  } catch (e) {
    showToast("Error deleting category", true);
  }
}

// ==========================================
// PUSH NOTIFICATIONS
// ==========================================

async function loadAlertsHistory() {
  try {
    const res = await fetch(`${API_BASE}/alerts`);
    const data = await res.json();
    const list = document.getElementById('alertsHistoryList');
    if (!list) return;

    if (!data.alerts || data.alerts.length === 0) {
      list.innerHTML = `<div class="py-4 text-xs text-slate-400">No alerts broadcasted yet.</div>`;
      return;
    }

    list.innerHTML = data.alerts.map(a => `
      <div class="py-3 flex items-start justify-between">
        <div>
          <div class="flex items-center space-x-2">
            <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800">${escapeHtml(a.category || 'Alert')}</span>
            <span class="font-bold text-xs text-slate-900">${escapeHtml(a.title)}</span>
          </div>
          <p class="text-xs text-slate-600 mt-1">${escapeHtml(a.shortDescription || '')}</p>
        </div>
        <span class="text-[11px] text-slate-400 whitespace-nowrap ml-4">${escapeHtml(a.time || 'Recent')}</span>
      </div>
    `).join('');
  } catch (e) {
    console.error("Load alerts error:", e);
  }
}

async function sendTestAlert() {
  document.getElementById('pushTitle').value = "CGJobs Live Test: आरक्षक भर्ती प्रवेश पत्र जारी";
  document.getElementById('pushBody').value = "यह एक परीक्षण अलर्ट है। छात्र तुरंत परीक्षा तिथि व केंद्र की जानकारी चेक करें।";
  document.getElementById('pushCategory').value = "Test Alert";
  showToast("Filled test notification fields! Click Send Push to broadcast.");
}

async function loadDevices() {
  try {
    const res = await fetch(`${API_BASE}/notifications/devices`, { headers: getHeaders() });
    const data = await res.json();
    const list = document.getElementById('devicesList');
    const badge = document.getElementById('badgeSubscribersCount');
    const devices = data.devices || [];

    if (badge) badge.textContent = `${devices.length} Devices`;

    if (!list) return;
    if (devices.length === 0) {
      list.innerHTML = `<div class="py-4 text-xs text-slate-400">No mobile devices registered yet. Open the Android app to register.</div>`;
      return;
    }

    list.innerHTML = devices.map(d => `
      <div class="py-3 flex items-center justify-between">
        <div>
          <div class="font-bold text-xs text-slate-800">${escapeHtml(d.deviceModel || 'Android Device')} (${escapeHtml(d.platform || 'Android')})</div>
          <div class="font-mono text-[10px] text-slate-400 truncate max-w-xs sm:max-w-md">Token: ${d.token ? d.token.substring(0, 30) + '...' : '—'}</div>
        </div>
        <span class="text-[11px] text-slate-400">${new Date(d.lastActive).toLocaleDateString()}</span>
      </div>
    `).join('');
  } catch (e) {
    console.error("Load devices error:", e);
  }
}

// ==========================================
// SETTINGS & KEYS
// ==========================================

async function loadSettings() {
  try {
    const res = await fetch(`${API_BASE}/settings`, { headers: getHeaders() });
    const data = await res.json();
    if (data.settings) {
      const s = data.settings;
      if (document.getElementById('inputFcmServerKey')) {
        document.getElementById('inputFcmServerKey').value = s.fcmServerKey || '';
      }
      if (document.getElementById('inputFirebaseProjectId')) {
        document.getElementById('inputFirebaseProjectId').value = s.firebaseProjectId || '';
      }
      if (document.getElementById('inputNewsApiKey')) {
        document.getElementById('inputNewsApiKey').value = s.newsApiKey || '';
      }
      if (document.getElementById('inputNewsDataKey')) {
        document.getElementById('inputNewsDataKey').value = s.newsDataApiKey || '';
      }
      if (document.getElementById('selectAutoSyncInterval')) {
        document.getElementById('selectAutoSyncInterval').value = s.autoSyncMinutes ?? 60;
      }
      if (document.getElementById('checkAutoPushOnSync')) {
        document.getElementById('checkAutoPushOnSync').checked = Boolean(s.autoPushOnSync);
      }
    }
  } catch (e) {
    console.error("Load settings error:", e);
  }
}

async function saveApiKeys() {
  const newsApiKey = document.getElementById('inputNewsApiKey')?.value;
  const newsDataApiKey = document.getElementById('inputNewsDataKey')?.value;

  try {
    const res = await fetch(`${API_BASE}/settings`, {
      method: 'POST',
      headers: getHeaders(),
      body: JSON.stringify({ newsApiKey, newsDataApiKey })
    });
    if (res.ok) {
      showToast("API Keys saved successfully!");
      loadDashboard();
    } else {
      showToast("Failed to save keys", true);
    }
  } catch (e) {
    showToast("Network error saving keys", true);
  }
}

async function saveSyncPreferences() {
  const autoSyncMinutes = parseInt(document.getElementById('selectAutoSyncInterval').value, 10);
  const autoPushOnSync = document.getElementById('checkAutoPushOnSync').checked;

  try {
    const res = await fetch(`${API_BASE}/settings`, {
      method: 'POST',
      headers: getHeaders(),
      body: JSON.stringify({ autoSyncMinutes, autoPushOnSync })
    });
    if (res.ok) {
      showToast("Sync preferences saved!");
    } else {
      showToast("Failed to update preferences", true);
    }
  } catch (e) {
    showToast("Error updating sync preferences", true);
  }
}

// ==========================================
// SYNC ACTIONS (NewsAPI & NewsData.io)
// ==========================================

async function triggerNewsApiSync() {
  const btn = document.getElementById('btnSyncNewsApi');
  if (btn) btn.innerHTML = 'Pulling from NewsAPI...';

  const query = document.getElementById('inputNewsApiQuery')?.value;
  const autoImport = document.getElementById('checkNewsApiAutoImport')?.checked !== false;

  try {
    const res = await fetch(`${API_BASE}/sync/newsapi`, {
      method: 'POST',
      headers: getHeaders(),
      body: JSON.stringify({ query, autoImport })
    });
    const data = await res.json();
    if (data.success) {
      showToast(`NewsAPI: Pulled ${data.totalFetched} articles (${data.importedCount || 0} imported)`);
      loadNews();
      loadDashboard();
    } else {
      showToast(data.error || "NewsAPI pull failed", true);
    }
  } catch (e) {
    showToast("Error contacting NewsAPI. Verify your key in Settings.", true);
  } finally {
    if (btn) btn.innerHTML = 'Pull from NewsAPI Now';
  }
}

async function triggerNewsDataSync() {
  const btn = document.getElementById('btnSyncNewsData');
  if (btn) btn.innerHTML = 'Pulling from NewsData.io...';

  const query = document.getElementById('inputNewsDataQuery')?.value;
  const autoImport = document.getElementById('checkNewsDataAutoImport')?.checked !== false;

  try {
    const res = await fetch(`${API_BASE}/sync/newsdata`, {
      method: 'POST',
      headers: getHeaders(),
      body: JSON.stringify({ query, autoImport })
    });
    const data = await res.json();
    if (data.success) {
      showToast(`NewsData.io: Pulled ${data.totalFetched} articles (${data.importedCount || 0} imported)`);
      loadNews();
      loadDashboard();
    } else {
      showToast(data.error || "NewsData.io pull failed", true);
    }
  } catch (e) {
    showToast("Error contacting NewsData.io. Verify your key in Settings.", true);
  } finally {
    if (btn) btn.innerHTML = 'Pull from NewsData.io Now';
  }
}

// ==========================================
// EVENT LISTENERS
// ==========================================

function setupEventListeners() {
  // Quick Sync in Header
  document.getElementById('btnQuickSync')?.addEventListener('click', () => {
    loadDashboard();
    loadNews();
    loadAlertsHistory();
    showToast("Refreshed data from server");
  });

  // Login Form
  document.getElementById('formLogin')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const username = document.getElementById('loginUsername').value;
    const password = document.getElementById('loginPassword').value;

    try {
      const res = await fetch(`${API_BASE}/admin/login`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ username, password })
      });
      const data = await res.json();
      if (data.success && data.token) {
        adminToken = data.token;
        localStorage.setItem('cgjobs_admin_token', adminToken);
        hideLoginModal();
        showToast("Signed in successfully!");
        checkAuthAndLoad();
      } else {
        showToast(data.error || "Invalid username or password", true);
      }
    } catch (err) {
      showToast("Login connection error", true);
    }
  });

  // Logout
  document.getElementById('btnLogout')?.addEventListener('click', () => {
    localStorage.removeItem('cgjobs_admin_token');
    adminToken = '';
    showLoginModal();
    showToast("Logged out");
  });

  // News Category / Search filter
  document.getElementById('newsCategoryFilter')?.addEventListener('change', loadNews);
  document.getElementById('newsSearchInput')?.addEventListener('input', debounce(loadNews, 300));

  // Add/Edit News Form Submit
  document.getElementById('formNewsItem')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const id = document.getElementById('editNewsId').value;
    const isEdit = Boolean(id);

    const payload = {
      title: document.getElementById('modalTitle').value,
      category: document.getElementById('modalCategory').value,
      summary: document.getElementById('modalSummary').value,
      detailedContent: document.getElementById('modalDetailedContent').value,
      source: document.getElementById('modalSource').value || 'CG Jobs Raipur',
      vacancies: document.getElementById('modalVacancies').value,
      eligibility: document.getElementById('modalEligibility').value,
      ageLimit: document.getElementById('modalAgeLimit').value,
      applyUrl: document.getElementById('modalApplyUrl').value,
      officialNotificationUrl: document.getElementById('modalOfficialPdfUrl').value,
      isBreaking: document.getElementById('modalIsBreaking').checked,
      pushNotification: document.getElementById('modalPushNotification').checked,
      importantDates: {
        applicationStart: document.getElementById('modalDateStart').value,
        lastDate: document.getElementById('modalDateLast').value,
        examDate: document.getElementById('modalDateExam').value
      }
    };

    try {
      const url = isEdit ? `${API_BASE}/news/${id}` : `${API_BASE}/news`;
      const method = isEdit ? 'PUT' : 'POST';

      const res = await fetch(url, {
        method,
        headers: getHeaders(),
        body: JSON.stringify(payload)
      });
      const data = await res.json();

      if (data.success) {
        showToast(isEdit ? "News updated successfully!" : "New post published!");
        closeNewsModal();
        loadNews();
        loadDashboard();
      } else {
        showToast(data.error || "Failed to save post", true);
      }
    } catch (err) {
      showToast("Error submitting news post", true);
    }
  });

  // Push Target Selector change
  document.getElementById('pushTargetType')?.addEventListener('change', (e) => {
    const box = document.getElementById('specificTokenBox');
    if (e.target.value === 'single_token') {
      box.classList.remove('hidden');
    } else {
      box.classList.add('hidden');
    }
  });

  // Send Push Form Submit
  document.getElementById('formSendPush')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const title = document.getElementById('pushTitle').value;
    const body = document.getElementById('pushBody').value;
    const category = document.getElementById('pushCategory').value;
    const articleId = document.getElementById('pushArticleId').value;
    const imageUrl = document.getElementById('pushImageUrl').value;
    const targetType = document.getElementById('pushTargetType').value;
    const targetValue = document.getElementById('pushTargetValue').value;

    try {
      const res = await fetch(`${API_BASE}/notifications/send`, {
        method: 'POST',
        headers: getHeaders(),
        body: JSON.stringify({
          title,
          body,
          category,
          articleId,
          imageUrl,
          targetType,
          targetValue: targetType === 'single_token' ? targetValue : (targetType === 'topic' ? 'all_users' : '')
        })
      });
      const data = await res.json();
      if (data.success) {
        showToast(data.simulated ? "Alert saved! " + data.message : "Broadcast pushed to Android devices!");
        document.getElementById('formSendPush').reset();
        loadDashboard();
        loadAlertsHistory();
      } else {
        showToast(data.error || "Failed to send push", true);
      }
    } catch (err) {
      showToast("Error sending notification", true);
    }
  });

  // FCM Settings Form Submit
  document.getElementById('formFcmSettings')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const fcmServerKey = document.getElementById('inputFcmServerKey').value;
    const firebaseProjectId = document.getElementById('inputFirebaseProjectId').value;

    try {
      const res = await fetch(`${API_BASE}/settings`, {
        method: 'POST',
        headers: getHeaders(),
        body: JSON.stringify({ fcmServerKey, firebaseProjectId })
      });
      if (res.ok) {
        showToast("FCM settings updated!");
        loadDashboard();
      } else {
        showToast("Failed to update FCM settings", true);
      }
    } catch (err) {
      showToast("Error updating FCM settings", true);
    }
  });

  // Add Category Form Submit
  document.getElementById('formAddCategory')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const section = document.getElementById('catSection')?.value || 'jobs';
    const name = document.getElementById('catName').value;
    const hindiName = document.getElementById('catHindiName').value;

    try {
      const res = await fetch(`${API_BASE}/categories`, {
        method: 'POST',
        headers: getHeaders(),
        body: JSON.stringify({ name, hindiName, section })
      });
      const data = await res.json();
      if (data.success) {
        showToast(`Category "${name}" created in [${section}]!`);
        document.getElementById('formAddCategory').reset();
        loadCategories();
        loadDashboard();
      } else {
        showToast(data.error || "Failed to create category", true);
      }
    } catch (err) {
      showToast("Error adding category", true);
    }
  });

  // Admin Password Form
  document.getElementById('formAdminPassword')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const oldPassword = document.getElementById('inputOldPassword').value;
    const newPassword = document.getElementById('inputNewPassword').value;

    try {
      const res = await fetch(`${API_BASE}/admin/change-password`, {
        method: 'POST',
        headers: getHeaders(),
        body: JSON.stringify({ oldPassword, newPassword })
      });
      const data = await res.json();
      if (data.success) {
        showToast("Admin password changed!");
        document.getElementById('formAdminPassword').reset();
      } else {
        showToast(data.error || "Password change failed", true);
      }
    } catch (err) {
      showToast("Network error updating password", true);
    }
  });
}

function debounce(func, wait) {
  let timeout;
  return function executedFunction(...args) {
    const later = () => {
      clearTimeout(timeout);
      func(...args);
    };
    clearTimeout(timeout);
    timeout = setTimeout(later, wait);
  };
}

function escapeHtml(str) {
  if (!str) return '';
  return str.replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
}
