package com.example.data

import android.content.Context
import android.util.Log
import com.example.data.api.ServerConfig
import com.example.data.api.model.RegisterTokenRequest
import com.example.data.api.model.toDomain
import com.example.model.AlertItem
import kotlinx.coroutines.CoroutineScope
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.flow.Flow
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.flow.first
import kotlinx.coroutines.flow.map
import kotlinx.coroutines.launch
import kotlinx.coroutines.withContext

class ServerBackedNotificationRepository(
    private val context: Context? = null,
    private val fallbackRepo: NotificationRepository = MockNotificationRepository()
) : NotificationRepository {

    private val scope = CoroutineScope(Dispatchers.IO)
    private val _alertsStream = MutableStateFlow<List<AlertItem>>(
        runCatching {
            kotlinx.coroutines.runBlocking {
                fallbackRepo.getAlertsStream().first()
            }
        }.getOrDefault(emptyList())
    )

    init {
        scope.launch {
            tryFetchAlerts()
        }
    }

    suspend fun tryFetchAlerts(): Boolean = withContext(Dispatchers.IO) {
        try {
            val api = ServerConfig.getApiService(context)
            val response = api.getAlerts()
            if (response.success && !response.alerts.isNullOrEmpty()) {
                val mapped = response.alerts.map { it.toDomain() }
                _alertsStream.value = mapped
                return@withContext true
            }
        } catch (e: Exception) {
            Log.w("ServerBackedNotifRepo", "Could not fetch server alerts: ${e.message}")
        }
        return@withContext false
    }

    suspend fun registerDeviceToken(token: String, model: String? = null): Boolean = withContext(Dispatchers.IO) {
        try {
            val api = ServerConfig.getApiService(context)
            api.registerDeviceToken(RegisterTokenRequest(token = token, deviceName = model, platform = "android"))
            return@withContext true
        } catch (e: Exception) {
            Log.e("ServerBackedNotifRepo", "Token registration error: ${e.message}")
            return@withContext false
        }
    }

    override fun getAlertsStream(): Flow<List<AlertItem>> = _alertsStream.asStateFlow()

    override fun getUnreadCountStream(): Flow<Int> {
        return _alertsStream.map { list -> list.count { !it.isRead } }
    }

    override suspend fun markAsRead(alertId: String) {
        val current = _alertsStream.value
        _alertsStream.value = current.map {
            if (it.id == alertId) it.copy(isRead = true) else it
        }
        fallbackRepo.markAsRead(alertId)
    }

    override suspend fun markAllAsRead() {
        val current = _alertsStream.value
        _alertsStream.value = current.map { it.copy(isRead = true) }
        fallbackRepo.markAllAsRead()
    }
}
