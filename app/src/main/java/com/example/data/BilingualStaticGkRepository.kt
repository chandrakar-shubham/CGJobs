package com.example.data

import android.content.Context
import android.util.Log
import com.example.data.api.ServerConfig
import com.example.data.api.model.toDomain
import com.example.model.StaticGkCard
import kotlinx.coroutines.CoroutineScope
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.flow.Flow
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.flow.map
import kotlinx.coroutines.flow.first
import kotlinx.coroutines.launch
import kotlinx.coroutines.withContext

/** Server-backed Static GK repository that respects the app's persisted language. */
class BilingualStaticGkRepository(private val context: Context? = null) {
    private val fallback = StaticGkRepository(context)
    private val scope = CoroutineScope(Dispatchers.IO)
    private val _gkStream = MutableStateFlow<List<StaticGkCard>>(fallback.gkStream.value)
    val gkStream = _gkStream.asStateFlow()

    init { scope.launch { tryFetchFromServer() } }

    fun getGkByCategory(category: String): Flow<List<StaticGkCard>> = _gkStream.map { list ->
        val clean = category.trim()
        if (clean == "सभी" || clean.equals("All", true) || clean.startsWith("सभी") || clean.startsWith("All")) list
        else list.filter { it.category.equals(clean, true) || it.category.contains(clean, true) || clean.contains(it.category, true) }
    }

    fun toggleSave(id: String) { _gkStream.value = _gkStream.value.map { if (it.id == id) it.copy(isSaved = !it.isSaved) else it } }

    suspend fun tryFetchFromServer(): Boolean = withContext(Dispatchers.IO) {
        try {
            val lang = ServerConfig.getLanguage(context)
            val response = ServerConfig.getApiService(context).getStaticGk(language = lang)
            if (response.success && !response.items.isNullOrEmpty()) {
                val saved = _gkStream.value.filter { it.isSaved }.map { it.id }.toSet()
                _gkStream.value = response.items.map { it.toDomain() }.map { if (it.id in saved) it.copy(isSaved = true) else it }
                Log.d("BilingualGkRepo", "Fetched ${_gkStream.value.size} GK items in $lang")
                return@withContext true
            }
        } catch (e: Exception) { Log.w("BilingualGkRepo", "sync: ${e.message}") }
        false
    }

    suspend fun refreshGk() { tryFetchFromServer() }
}
