package com.example.model

data class StaticGkCard(
    val id: String,
    val title: String,
    val category: String, // "इतिहास", "भूगोल", "संस्कृति व जनजाति", "राजव्यवस्था व अर्थव्यवस्था", "साहित्य व पुरस्कार", "छत्तीसगढ़ विशेष"
    val summary: String,
    val facts: List<String>,
    val examTip: String,
    val relatedExam: String = "CGPSC, CG व्यापम, पुलिस, शिक्षक",
    val imageUrl: String? = null,
    val isSaved: Boolean = false
)
