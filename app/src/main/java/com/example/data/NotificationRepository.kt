package com.example.data

import com.example.model.AlertItem
import kotlinx.coroutines.flow.Flow

interface NotificationRepository {
    fun getAlertsStream(): Flow<List<AlertItem>>
    fun getUnreadCountStream(): Flow<Int>
    suspend fun markAsRead(alertId: String)
    suspend fun markAllAsRead()
}
