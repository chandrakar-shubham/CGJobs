package com.example.data

import android.content.Context
import android.util.Log
import com.example.data.api.ServerConfig
import com.example.data.api.model.toDomain
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
        try {
            val api = ServerConfig.getApiService(context)
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
                Log.d("ServerBackedNewsRepo", "Successfully fetched ${updated.size} news from server")
                _isSyncing.value = false
                return@withContext true
            }
        } catch (e: Exception) {
            Log.w("ServerBackedNewsRepo", "Server sync notice: ${e.message}. Using offline fallback repository.")
            _serverAvailable.value = false
        }
        _isSyncing.value = false
        return@withContext false
    }

    override fun getNewsStream(): Flow<List<JobUpdate>> = _newsStream.asStateFlow()

    override fun getNewsByCategory(category: String): Flow<List<JobUpdate>> {
        return _newsStream.map { list ->
            if (category == "सभी" || category.equals("All", ignoreCase = true)) {
                list
            } else {
                list.filter {
                    it.category.equals(category, ignoreCase = true) ||
                    (category == "व्यापम" && it.category == "CG Vyapam") ||
                    (category == "समसामयिकी" && it.category == "Current Affairs") ||
                    (category == "प्रवेश पत्र" && it.category == "Admit Card") ||
                    (category == "परिणाम" && it.category == "Result") ||
                    (category == "शिक्षक" && (it.category == "Teaching" || it.category == "CGSSB")) ||
                    (category == "पुलिस" && (it.category == "Police & Defence" || it.category == "CG Police"))
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
