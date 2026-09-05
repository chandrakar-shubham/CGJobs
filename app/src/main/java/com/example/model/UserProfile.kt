package com.example.model

data class UserProfile(
    val name: String = "Shubham Chandrakar",
    val email: String = "shubham@gmail.com",
    val selectedInterests: Set<String> = setOf("CGSSB", "CG Vyapam", "CGPSC", "Teacher", "Patwari"),
    val notificationsEnabled: Boolean = true,
    val examAlertsEnabled: Boolean = true,
    val resultAlertsEnabled: Boolean = true,
    val language: String = "हिन्दी",
    val isDarkMode: Boolean = false
)
