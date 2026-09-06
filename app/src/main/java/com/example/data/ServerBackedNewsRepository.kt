package com.example.data

import android.content.Context
import android.util.Log
import com.example.data.api.ServerConfig
import com.example.data.api.model.toDomain
import com.example.model.AppCategory
import com.example.model.AppSection
import com.example.model.JobUpdate
import kotlinx.coroutines.CoroutineScope
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.flow.Flow
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.flow.first
import kotlinx.coroutines.flow.map
import kotlinx.coroutines.launch
import kotlinx.coroutines.withContext

/**
 * ServerBackedNewsRepository fetches live data from the hosted CGJobs REST API backend,
 * while automatically falling back to MockNewsRepository if offline or server is unreachable.
 */
class ServerBackedNewsRepository(
    private val context: Context? = null,
    private val fallbackRepository: NewsRepository = MockNewsRepository()
) : NewsRepository {

    private val scope = CoroutineScope(Dispatchers.IO)
    private val _newsStream = MutableStateFlow<List<JobUpdate>>(
        runCatching {
            kotlinx.coroutines.runBlocking {
                fallbackRepository.getNewsStream().first()
            }
        }.getOrDefault(emptyList())
    )

    private val _sectionsStream = MutableStateFlow<List<AppSection>>(
        runCatching {
            kotlinx.coroutines.runBlocking {
                fallbackRepository.getSectionsStream().first()
            }
        }.getOrDefault(emptyList())
    )

    private val _categoriesStream = MutableStateFlow<List<AppCategory>>(
        runCatching {
            kotlinx.coroutines.runBlocking {
                fallbackRepository.getCategoriesStream().first()
            }
        }.getOrDefault(emptyList())
    )

    private val _isSyncing = MutableStateFlow(false)
    val isSyncing: Flow<Boolean> = _isSyncing.asStateFlow()

    private val _serverAvailable = MutableStateFlow(false)
    val serverAvailable: Flow<Boolean> = _serverAvailable.asStateFlow()

    init {
        scope.launch {
            // Try fetching from server in background if configured
            tryFetchFromServer()
        }
    }

    suspend fun tryFetchFromServer(): Boolean = withContext(Dispatchers.IO) {
        _isSyncing.value = true
        var newsSuccess = false
        try {
            val api = ServerConfig.getApiService(context)

            // 1. Fetch live sections and categorized lists
            try {
                val sectionsResponse = api.getSections()
                if (sectionsResponse.success && !sectionsResponse.sections.isNullOrEmpty()) {
                    val mappedSections = sectionsResponse.sections.map { it.toDomain() }
                    _sectionsStream.value = mappedSections
                    Log.d("ServerBackedNewsRepo", "Successfully fetched ${mappedSections.size} sections")
                }
            } catch (e: Exception) {
                Log.w("ServerBackedNewsRepo", "Failed fetching sections from server: ${e.message}")
            }

            // 2. Fetch categories
            try {
                val categoriesResponse = api.getCategories()
                if (categoriesResponse.success && !categoriesResponse.categories.isNullOrEmpty()) {
                    val mappedCategories = categoriesResponse.categories.map { it.toDomain() }
                    _categoriesStream.value = mappedCategories
                    Log.d("ServerBackedNewsRepo", "Successfully fetched ${mappedCategories.size} categories")
                }
            } catch (e: Exception) {
                Log.w("ServerBackedNewsRepo", "Failed fetching categories from server: ${e.message}")
            }

            // 3. Fetch news / jobs
            val response = api.getNews(limit = 100)
            if (response.success && !response.news.isNullOrEmpty()) {
                val mapped = response.news.map { it.toDomain() }

                // Merge saved states from current list
                val currentSavedIds = _newsStream.value.filter { it.isSaved }.map { it.id }.toSet()
                val updated = mapped.map { item ->
                    if (currentSavedIds.contains(item.id)) item.copy(isSaved = true) else item
                }

                _newsStream.value = updated
                _serverAvailable.value = true
                newsSuccess = true
                Log.d("ServerBackedNewsRepo", "Successfully fetched ${updated.size} news from server")
            }
        } catch (e: Exception) {
            Log.w("ServerBackedNewsRepo", "Server sync notice: ${e.message}. Using offline fallback repository.")
            _serverAvailable.value = false
        }
        _isSyncing.value = false
        return@withContext newsSuccess
    }

    override fun getNewsStream(): Flow<List<JobUpdate>> = _newsStream.asStateFlow()

    override fun getSectionsStream(): Flow<List<AppSection>> = _sectionsStream.asStateFlow()

    override fun getCategoriesStream(section: String?): Flow<List<AppCategory>> {
        return _categoriesStream.map { list ->
            if (section.isNullOrBlank()) list
            else list.filter { it.section.equals(section, ignoreCase = true) }
        }
    }

    override fun getNewsByCategory(category: String): Flow<List<JobUpdate>> {
        return _newsStream.map { list ->
            val clean = category.trim()
            if (clean == "सभी" || clean.equals("All", ignoreCase = true) || clean.startsWith("सभी") || clean.startsWith("All")) {
                list
            } else {
                list.filter { item ->
                    item.category.equals(clean, ignoreCase = true) ||
                    item.category.contains(clean, ignoreCase = true) ||
                    clean.contains(item.category, ignoreCase = true) ||
                    (clean.contains("व्यापम") && item.category.contains("Vyapam", ignoreCase = true)) ||
                    (clean.contains("समसामयिकी") && item.category.contains("Current Affairs", ignoreCase = true)) ||
                    (clean.contains("प्रवेश पत्र") && item.category.contains("Admit Card", ignoreCase = true)) ||
                    (clean.contains("परिणाम") && item.category.contains("Result", ignoreCase = true)) ||
                    (clean.contains("शिक्षक") && (item.category.contains("Teaching", ignoreCase = true) || item.category.contains("CGSSB", ignoreCase = true))) ||
                    (clean.contains("पुलिस") && (item.category.contains("Police", ignoreCase = true) || item.category.contains("Defence", ignoreCase = true))) ||
                    (clean.contains("पटवारी") && item.category.contains("Patwari", ignoreCase = true)) ||
                    (clean.contains("इंजीनियरिंग") && item.category.contains("Engineering", ignoreCase = true)) ||
                    (clean.contains("चिकित्सा") && item.category.contains("Medical", ignoreCase = true))
                }
            }
        }
    }

    override fun getNewsById(id: String): Flow<JobUpdate?> {
        return _newsStream.map { list -> list.find { it.id == id } }
    }

    override fun searchNews(query: String): Flow<List<JobUpdate>> {
        return _newsStream.map { list ->
            if (query.isBlank()) list
            else {
                val q = query.trim().lowercase()
                list.filter {
                    it.title.lowercase().contains(q) ||
                    it.summary.lowercase().contains(q) ||
                    it.category.lowercase().contains(q) ||
                    it.source.lowercase().contains(q) ||
                    (it.vacancies?.lowercase()?.contains(q) == true)
                }
            }
        }
    }

    override fun getSavedNews(): Flow<List<JobUpdate>> {
        return _newsStream.map { list -> list.filter { it.isSaved } }
    }

    override suspend fun toggleSave(id: String) {
        val current = _newsStream.value
        _newsStream.value = current.map { item ->
            if (item.id == id) item.copy(isSaved = !item.isSaved) else item
        }
        fallbackRepository.toggleSave(id)
    }

    override suspend fun refreshNews() {
        val fetched = tryFetchFromServer()
        if (!fetched) {
            fallbackRepository.refreshNews()
            val fallbackItems = fallbackRepository.getNewsStream().first()
            if (_newsStream.value.isEmpty()) {
                _newsStream.value = fallbackItems
            }
        }
    }
}
