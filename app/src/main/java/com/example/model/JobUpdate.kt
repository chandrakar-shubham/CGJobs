package com.example.model

data class ImportantDates(
    val applicationStart: String,
    val lastDate: String,
    val examDate: String? = null,
    val admitCardDate: String? = null,
    val resultDate: String? = null
)

data class JobUpdate(
    val id: String,
    val slug: String? = null,
    val title: String,
    val summary: String,
    val detailedContent: String,
    val examTakeaway: String? = null,
    val category: String,
    val section: String = "jobs",
    val postType: String = "job",
    val source: String,
    val sourceUrl: String,
    val webArticleUrl: String? = null,
    val imageUrl: String? = null,
    val publishedAt: String,
    val relativeTime: String,
    val isBreaking: Boolean = false,
    val isNew: Boolean = false,
    val importantDates: ImportantDates,
    val vacancies: String? = null,
    val eligibility: String? = null,
    val ageLimit: String? = null,
    val selectionProcess: String? = null,
    val officialNotificationUrl: String? = null,
    val applyUrl: String? = null,
    val isSaved: Boolean = false
)
