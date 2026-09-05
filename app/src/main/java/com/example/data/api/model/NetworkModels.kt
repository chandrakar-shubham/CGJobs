package com.example.data.api.model

import com.example.model.AlertItem
import com.example.model.AlertType
import com.example.model.ImportantDates
import com.example.model.JobUpdate
import com.squareup.moshi.Json
import com.squareup.moshi.JsonClass

@JsonClass(generateAdapter = true)
data class HealthResponse(
    val status: String,
    val appName: String? = null,
    val version: String? = null,
    val serverTime: String? = null
)

@JsonClass(generateAdapter = true)
data class ImportantDatesDto(
    val applicationStart: String = "जारी",
    val lastDate: String = "शीघ्र",
    val examDate: String? = null,
    val admitCardDate: String? = null,
    val resultDate: String? = null
)

@JsonClass(generateAdapter = true)
data class NewsDto(
    val id: String,
    val title: String,
    val summary: String,
    val detailedContent: String? = null,
    val category: String,
    val source: String? = null,
    val sourceUrl: String? = null,
    val imageUrl: String? = null,
    val publishedAt: String? = null,
    val relativeTime: String? = null,
    val isBreaking: Boolean? = false,
    val isNew: Boolean? = false,
    val vacancies: String? = null,
    val eligibility: String? = null,
    val ageLimit: String? = null,
    val selectionProcess: String? = null,
    val officialNotificationUrl: String? = null,
    val applyUrl: String? = null,
    val importantDates: ImportantDatesDto? = null
)

@JsonClass(generateAdapter = true)
data class NewsApiResponse(
    val success: Boolean,
    val count: Int? = null,
    val news: List<NewsDto>? = null
)

@JsonClass(generateAdapter = true)
data class SingleNewsResponse(
    val success: Boolean,
    val item: NewsDto? = null
)

@JsonClass(generateAdapter = true)
data class CategoryDto(
    val id: String,
    val name: String,
    val hindiName: String? = null
)

@JsonClass(generateAdapter = true)
data class CategoriesApiResponse(
    val success: Boolean,
    val categories: List<CategoryDto>? = null
)

@JsonClass(generateAdapter = true)
data class AlertDto(
    val id: String,
    val category: String? = null,
    val title: String,
    val shortDescription: String? = null,
    val time: String? = null,
    val type: String? = null,
    val isRead: Boolean? = false,
    val articleId: String? = null
)

@JsonClass(generateAdapter = true)
data class AlertsApiResponse(
    val success: Boolean,
    val alerts: List<AlertDto>? = null
)

@JsonClass(generateAdapter = true)
data class RegisterTokenRequest(
    val token: String,
    val deviceModel: String? = null,
    val platform: String? = null
)

// Extension mappers to map DTOs to Domain models
fun NewsDto.toDomain(): JobUpdate {
    return JobUpdate(
        id = id,
        title = title,
        summary = summary,
        detailedContent = detailedContent ?: summary,
        category = category,
        source = source ?: "CG Jobs Portal",
        sourceUrl = sourceUrl ?: "https://cgstate.gov.in",
        imageUrl = imageUrl,
        publishedAt = publishedAt ?: "Recent",
        relativeTime = relativeTime ?: "हाल ही में",
        isBreaking = isBreaking ?: false,
        isNew = isNew ?: false,
        importantDates = ImportantDates(
            applicationStart = importantDates?.applicationStart ?: "जारी",
            lastDate = importantDates?.lastDate ?: "शीघ्र",
            examDate = importantDates?.examDate,
            admitCardDate = importantDates?.admitCardDate,
            resultDate = importantDates?.resultDate
        ),
        vacancies = vacancies,
        eligibility = eligibility,
        ageLimit = ageLimit,
        selectionProcess = selectionProcess,
        officialNotificationUrl = officialNotificationUrl,
        applyUrl = applyUrl,
        isSaved = false
    )
}

fun AlertDto.toDomain(): AlertItem {
    val alertType = when (type?.uppercase()) {
        "BREAKING" -> AlertType.BREAKING
        "RECRUITMENT" -> AlertType.RECRUITMENT
        "EXAM_DATE" -> AlertType.EXAM_DATE
        "ADMIT_CARD" -> AlertType.ADMIT_CARD
        "RESULT" -> AlertType.RESULT
        "DEADLINE" -> AlertType.DEADLINE
        else -> AlertType.BREAKING
    }

    return AlertItem(
        id = id,
        category = category ?: "सूचना",
        title = title,
        shortDescription = shortDescription ?: "",
        time = time ?: "हाल ही में",
        type = alertType,
        isRead = isRead ?: false,
        articleId = articleId
    )
}
