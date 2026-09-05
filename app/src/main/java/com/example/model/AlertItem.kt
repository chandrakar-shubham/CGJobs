package com.example.model

enum class AlertType {
    BREAKING,
    RECRUITMENT,
    EXAM_DATE,
    ADMIT_CARD,
    RESULT,
    DEADLINE
}

data class AlertItem(
    val id: String,
    val category: String,
    val title: String,
    val shortDescription: String,
    val time: String,
    val type: AlertType,
    val isRead: Boolean = false,
    val articleId: String? = null
)
