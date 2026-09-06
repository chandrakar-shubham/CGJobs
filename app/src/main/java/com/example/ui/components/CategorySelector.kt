package com.example.ui.components

import androidx.compose.foundation.background
import androidx.compose.foundation.clickable
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.PaddingValues
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.lazy.LazyRow
import androidx.compose.foundation.lazy.items
import androidx.compose.foundation.shape.CircleShape
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.clip
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.platform.testTag
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import com.example.model.AppCategory
import com.example.ui.theme.BorderLight
import com.example.ui.theme.BrandGreen
import com.example.ui.theme.Slate100
import com.example.ui.theme.Slate600

val HOME_CATEGORIES = listOf(
    "सभी",
    "CGSSB",
    "CG Vyapam",
    "CGPSC",
    "Teacher",
    "Police",
    "Patwari",
    "TET",
    "Education",
    "Admit Card",
    "Result",
    "Answer Key",
    "Current Affairs"
)

@Composable
fun CategorySelector(
    selectedCategory: String,
    onCategorySelected: (String) -> Unit,
    modifier: Modifier = Modifier,
    categories: List<AppCategory> = emptyList()
) {
    Box(
        modifier = modifier
            .fillMaxWidth()
            .background(MaterialTheme.colorScheme.surface)
            .padding(vertical = 4.dp)
    ) {
        LazyRow(
            modifier = Modifier.testTag("category_selector_row"),
            contentPadding = PaddingValues(horizontal = 16.dp, vertical = 6.dp),
            horizontalArrangement = Arrangement.spacedBy(8.dp)
        ) {
            if (categories.isNotEmpty()) {
                items(categories, key = { it.id }) { cat ->
                    val isSelected = cat.name.equals(selectedCategory, ignoreCase = true) ||
                            cat.id.equals(selectedCategory, ignoreCase = true) ||
                            (cat.hindiName?.equals(selectedCategory, ignoreCase = true) == true) ||
                            (cat.id == "all_jobs" && (selectedCategory == "सभी" || selectedCategory.equals("All", ignoreCase = true)))

                    val label = when {
                        !cat.hindiName.isNullOrBlank() && cat.name.equals("All Jobs", ignoreCase = true) -> "सभी भर्तियां"
                        !cat.hindiName.isNullOrBlank() && cat.name.contains("(") -> cat.name
                        !cat.hindiName.isNullOrBlank() -> "${cat.name} (${cat.hindiName})"
                        else -> cat.name
                    }

                    Box(
                        modifier = Modifier
                            .clip(CircleShape)
                            .background(
                                if (isSelected) BrandGreen else Slate100
                            )
                            .clickable { onCategorySelected(cat.name) }
                            .padding(horizontal = 16.dp, vertical = 7.dp)
                            .testTag("category_chip_${cat.id}"),
                        contentAlignment = Alignment.Center
                    ) {
                        Text(
                            text = label,
                            color = if (isSelected) Color.White else Slate600,
                            fontSize = 13.5.sp,
                            fontWeight = if (isSelected) FontWeight.SemiBold else FontWeight.Medium
                        )
                    }
                }
            } else {
                items(HOME_CATEGORIES) { category ->
                    val isSelected = category.equals(selectedCategory, ignoreCase = true) ||
                            (category == "सभी" && selectedCategory.equals("All", ignoreCase = true))

                    Box(
                        modifier = Modifier
                            .clip(CircleShape)
                            .background(
                                if (isSelected) BrandGreen else Slate100
                            )
                            .clickable { onCategorySelected(category) }
                            .padding(horizontal = 16.dp, vertical = 7.dp)
                            .testTag("category_chip_$category"),
                        contentAlignment = Alignment.Center
                    ) {
                        Text(
                            text = category,
                            color = if (isSelected) Color.White else Slate600,
                            fontSize = 13.5.sp,
                            fontWeight = if (isSelected) FontWeight.SemiBold else FontWeight.Medium
                        )
                    }
                }
            }
        }
    }
}

