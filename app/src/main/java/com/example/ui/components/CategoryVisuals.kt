package com.example.ui.components

import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.automirrored.outlined.Article
import androidx.compose.material.icons.outlined.AccountBalance
import androidx.compose.material.icons.outlined.Badge
import androidx.compose.material.icons.outlined.BarChart
import androidx.compose.material.icons.outlined.Book
import androidx.compose.material.icons.outlined.DateRange
import androidx.compose.material.icons.outlined.LocalPolice
import androidx.compose.material.icons.outlined.MenuBook
import androidx.compose.material.icons.outlined.Public
import androidx.compose.material.icons.outlined.School
import androidx.compose.material.icons.outlined.Work
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.graphics.vector.ImageVector
import com.example.ui.theme.CategoryAdmitCard
import com.example.ui.theme.CategoryAdmitCardBg
import com.example.ui.theme.CategoryCgpsc
import com.example.ui.theme.CategoryCgpscBg
import com.example.ui.theme.CategoryCgssb
import com.example.ui.theme.CategoryCgssbBg
import com.example.ui.theme.CategoryCurrentAffairs
import com.example.ui.theme.CategoryCurrentAffairsBg
import com.example.ui.theme.CategoryEducation
import com.example.ui.theme.CategoryEducationBg
import com.example.ui.theme.CategoryResult
import com.example.ui.theme.CategoryResultBg
import com.example.ui.theme.CategorySyllabus
import com.example.ui.theme.CategorySyllabusBg
import com.example.ui.theme.CategoryVyapam
import com.example.ui.theme.CategoryVyapamBg

data class CategoryTheme(
    val color: Color,
    val backgroundColor: Color,
    val icon: ImageVector,
    val hindiSubtitle: String
)

fun getCategoryTheme(category: String): CategoryTheme {
    return when {
        category.contains("CGSSB", ignoreCase = true) -> CategoryTheme(
            color = CategoryCgssb,
            backgroundColor = CategoryCgssbBg,
            icon = Icons.Outlined.Badge,
            hindiSubtitle = "शिक्षक, कर्मचारी भर्ती"
        )
        category.contains("Vyapam", ignoreCase = true) || category.contains("Patwari", ignoreCase = true) -> CategoryTheme(
            color = CategoryVyapam,
            backgroundColor = CategoryVyapamBg,
            icon = Icons.Outlined.Work,
            hindiSubtitle = "परीक्षा, भर्ती, परिणाम"
        )
        category.contains("CGPSC", ignoreCase = true) -> CategoryTheme(
            color = CategoryCgpsc,
            backgroundColor = CategoryCgpscBg,
            icon = Icons.Outlined.AccountBalance,
            hindiSubtitle = "राज्य सेवा परीक्षा"
        )
        category.contains("Education", ignoreCase = true) || category.contains("Teacher", ignoreCase = true) || category.contains("शिक्षा", ignoreCase = true) -> CategoryTheme(
            color = CategoryEducation,
            backgroundColor = CategoryEducationBg,
            icon = Icons.Outlined.School,
            hindiSubtitle = "स्कूल, कॉलेज, अन्य"
        )
        category.contains("Admit", ignoreCase = true) || category.contains("प्रवेश", ignoreCase = true) -> CategoryTheme(
            color = CategoryAdmitCard,
            backgroundColor = CategoryAdmitCardBg,
            icon = Icons.Outlined.DateRange,
            hindiSubtitle = "प्रवेश पत्र"
        )
        category.contains("Result", ignoreCase = true) || category.contains("परिणाम", ignoreCase = true) -> CategoryTheme(
            color = CategoryResult,
            backgroundColor = CategoryResultBg,
            icon = Icons.Outlined.BarChart,
            hindiSubtitle = "परीक्षा परिणाम"
        )
        category.contains("Syllabus", ignoreCase = true) || category.contains("पाठ्यक्रम", ignoreCase = true) -> CategoryTheme(
            color = CategorySyllabus,
            backgroundColor = CategorySyllabusBg,
            icon = Icons.Outlined.Book,
            hindiSubtitle = "पाठ्यक्रम"
        )
        category.contains("Current", ignoreCase = true) || category.contains("समसामयिकी", ignoreCase = true) -> CategoryTheme(
            color = CategoryCurrentAffairs,
            backgroundColor = CategoryCurrentAffairsBg,
            icon = Icons.Outlined.Public,
            hindiSubtitle = "छत्तीसगढ़ व अन्य"
        )
        category.contains("Police", ignoreCase = true) || category.contains("आरक्षक", ignoreCase = true) -> CategoryTheme(
            color = CategoryCgpsc,
            backgroundColor = CategoryCgpscBg,
            icon = Icons.Outlined.LocalPolice,
            hindiSubtitle = "पुलिस आरक्षक व उप-निरीक्षक"
        )
        else -> CategoryTheme(
            color = CategoryCgssb,
            backgroundColor = CategoryCgssbBg,
            icon = Icons.AutoMirrored.Outlined.Article,
            hindiSubtitle = "सरकारी नौकरी व अपडेट्स"
        )
    }
}
