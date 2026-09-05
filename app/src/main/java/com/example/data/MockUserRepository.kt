package com.example.data

import com.example.model.UserProfile
import kotlinx.coroutines.flow.Flow
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.asStateFlow

class MockUserRepository : UserRepository {

    private val _userProfile = MutableStateFlow(UserProfile())
    val userProfile = _userProfile.asStateFlow()

    override fun getUserProfile(): Flow<UserProfile> = userProfile

    override suspend fun updateInterests(interests: Set<String>) {
        _userProfile.value = _userProfile.value.copy(selectedInterests = interests)
    }

    override suspend fun updateNotificationSettings(
        enabled: Boolean,
        examAlerts: Boolean,
        resultAlerts: Boolean
    ) {
        _userProfile.value = _userProfile.value.copy(
            notificationsEnabled = enabled,
            examAlertsEnabled = examAlerts,
            resultAlertsEnabled = resultAlerts
        )
    }

    override suspend fun updateLanguage(language: String) {
        _userProfile.value = _userProfile.value.copy(language = language)
    }

    override suspend fun updateDarkMode(isDark: Boolean) {
        _userProfile.value = _userProfile.value.copy(isDarkMode = isDark)
    }

    override suspend fun updateProfileInfo(name: String, email: String) {
        _userProfile.value = _userProfile.value.copy(name = name, email = email)
    }
}
