package com.example.model

data class AppCategory(
    val id: String,
    val name: String,
    val hindiName: String? = null,
    val section: String = "jobs"
) {
    /**
     * Human-friendly display label: e.g. "CGPSC (सीजीपीएससी)" or "सभी भर्तियां"
     */
    val displayName: String
        get() = when {
            hindiName.isNullOrBlank() -> name
            name.equals("सभी", ignoreCase = true) || name.equals("All", ignoreCase = true) -> "$name ($hindiName)"
            else -> "$name ($hindiName)"
        }

    val chipLabel: String
        get() = if (!hindiName.isNullOrBlank()) "$name ($hindiName)" else name
}

data class AppSection(
    val id: String, // "jobs", "news", "static_gk"
    val name: String,
    val hindiName: String? = null,
    val description: String? = null,
    val icon: String? = null,
    val categories: List<AppCategory> = emptyList(),
    val itemCount: Int = 0
)
