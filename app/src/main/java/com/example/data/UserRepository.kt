package com.example.data

import com.example.model.UserProfile
import kotlinx.coroutines.flow.Flow

interface UserRepository {
    fun getUserProfile(): Flow<UserProfile>
    suspend fun updateInterests(interests: Set<String>)
    suspend fun updateNotificationSettings(enabled: Boolean, examAlerts: Boolean, resultAlerts: Boolean)
    suspend fun updateLanguage(language: String)
    suspend fun updateDarkMode(isDark: Boolean)
    suspend fun updateProfileInfo(name: String, email: String)
}
