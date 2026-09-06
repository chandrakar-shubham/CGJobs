package com.example.ui.screens

import android.content.Context
import android.content.Intent
import androidx.compose.foundation.BorderStroke
import androidx.compose.foundation.background
import androidx.compose.foundation.border
import androidx.compose.foundation.clickable
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.PaddingValues
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.layout.size
import androidx.compose.foundation.layout.width
import androidx.compose.foundation.lazy.LazyRow
import androidx.compose.foundation.lazy.items
import androidx.compose.foundation.pager.VerticalPager
import androidx.compose.foundation.pager.rememberPagerState
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.shape.CircleShape
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.foundation.verticalScroll
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.Bookmark
import androidx.compose.material.icons.filled.KeyboardArrowDown
import androidx.compose.material.icons.filled.KeyboardArrowUp
import androidx.compose.material.icons.outlined.AutoStories
import androidx.compose.material.icons.outlined.BookmarkBorder
import androidx.compose.material.icons.outlined.CheckCircle
import androidx.compose.material.icons.outlined.Lightbulb
import androidx.compose.material.icons.outlined.School
import androidx.compose.material.icons.outlined.Share
import androidx.compose.material.icons.outlined.SwapVert
import androidx.compose.material3.Card
import androidx.compose.material3.CardDefaults
import androidx.compose.material3.HorizontalDivider
import androidx.compose.material3.Icon
import androidx.compose.material3.IconButton
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Surface
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.runtime.getValue
import androidx.compose.runtime.mutableStateOf
import androidx.compose.runtime.remember
import androidx.compose.runtime.rememberCoroutineScope
import androidx.compose.runtime.setValue
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.clip
import androidx.compose.ui.graphics.Brush
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.platform.LocalContext
import androidx.compose.ui.platform.testTag
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.style.TextAlign
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import com.example.model.AppCategory
import com.example.model.StaticGkCard
import com.example.ui.theme.BorderLight
import com.example.ui.theme.BrandGold
import com.example.ui.theme.BrandGreen
import com.example.ui.theme.BrandGreenDark
import com.example.ui.theme.BrandGreenLight
import com.example.ui.theme.Slate50
import com.example.ui.theme.Slate600
import com.example.ui.theme.TextPrimary
import com.example.ui.theme.TextSecondary
import kotlinx.coroutines.launch

val STATIC_GK_CATEGORIES = listOf(
    "सभी (All)",
    "इतिहास",
    "भूगोल",
    "संस्कृति व जनजाति",
    "राजव्यवस्था व अर्थव्यवस्था",
    "छत्तीसगढ़ विशेष"
)

@Composable
fun StaticGkScreen(
    gkList: List<StaticGkCard>,
    onToggleSave: (String) -> Unit,
    modifier: Modifier = Modifier,
    categories: List<AppCategory> = emptyList()
) {
    val context = LocalContext.current
    val coroutineScope = rememberCoroutineScope()
    var selectedCategory by remember { mutableStateOf("सभी (All)") }

    val filteredList = remember(gkList, selectedCategory) {
        val clean = selectedCategory.trim()
        if (clean == "सभी (All)" || clean == "सभी" || clean.equals("All", ignoreCase = true) || clean.equals("all_gk", ignoreCase = true) || clean.startsWith("सभी") || clean.startsWith("All")) {
            gkList
        } else {
            gkList.filter {
                it.category.equals(clean, ignoreCase = true) ||
                clean.contains(it.category, ignoreCase = true) ||
                it.category.contains(clean, ignoreCase = true) ||
                (clean.contains("भूगोल") && it.category.contains("भूगोल")) ||
                (clean.contains("इतिहास") && it.category.contains("इतिहास")) ||
                (clean.contains("जनजाति") && it.category.contains("जनजाति")) ||
                (clean.contains("अर्थव्यवस्था") && it.category.contains("अर्थव्यवस्था"))
            }
        }
    }

    val displayList = if (filteredList.isNotEmpty()) filteredList else gkList
    val pagerState = rememberPagerState(pageCount = { displayList.size })

    Column(
        modifier = modifier
            .fillMaxSize()
            .background(MaterialTheme.colorScheme.background)
            .testTag("static_gk_screen_root")
    ) {
        // Header & Category Chips Bar
        Column(
            modifier = Modifier
                .fillMaxWidth()
                .background(MaterialTheme.colorScheme.surface)
                .padding(top = 8.dp, bottom = 4.dp)
        ) {
            Row(
                modifier = Modifier
                    .fillMaxWidth()
                    .padding(horizontal = 16.dp, vertical = 4.dp),
                verticalAlignment = Alignment.CenterVertically,
                horizontalArrangement = Arrangement.SpaceBetween
            ) {
                Row(verticalAlignment = Alignment.CenterVertically) {
                    Box(
                        modifier = Modifier
                            .size(28.dp)
                            .clip(CircleShape)
                            .background(BrandGreenLight),
                        contentAlignment = Alignment.Center
                    ) {
                        Icon(
                            imageVector = Icons.Outlined.AutoStories,
                            contentDescription = null,
                            tint = BrandGreen,
                            modifier = Modifier.size(16.dp)
                        )
                    }
                    Spacer(modifier = Modifier.width(8.dp))
                    Text(
                        text = "स्टैटिक GK (Inshorts Swipe)",
                        fontSize = 15.sp,
                        fontWeight = FontWeight.Bold,
                        color = TextPrimary
                    )
                }

                if (displayList.isNotEmpty()) {
                    Box(
                        modifier = Modifier
                            .clip(RoundedCornerShape(12.dp))
                            .background(Slate50)
                            .border(1.dp, BorderLight.copy(alpha = 0.5f), RoundedCornerShape(12.dp))
                            .padding(horizontal = 8.dp, vertical = 3.dp)
                    ) {
                        Row(verticalAlignment = Alignment.CenterVertically) {
                            Icon(
                                imageVector = Icons.Outlined.SwapVert,
                                contentDescription = null,
                                tint = Slate600,
                                modifier = Modifier.size(14.dp)
                            )
                            Spacer(modifier = Modifier.width(4.dp))
                            Text(
                                text = "${pagerState.currentPage + 1} / ${displayList.size}",
                                fontSize = 11.5.sp,
                                fontWeight = FontWeight.SemiBold,
                                color = TextSecondary
                            )
                        }
                    }
                }
            }

            // Categories horizontal scroller
            LazyRow(
                modifier = Modifier
                    .fillMaxWidth()
                    .testTag("static_gk_category_row"),
                contentPadding = PaddingValues(horizontal = 14.dp, vertical = 6.dp),
                horizontalArrangement = Arrangement.spacedBy(8.dp)
            ) {
                if (categories.isNotEmpty()) {
                    items(categories, key = { it.id }) { cat ->
                        val isSelected = selectedCategory.equals(cat.name, ignoreCase = true) ||
                                selectedCategory.equals(cat.id, ignoreCase = true) ||
                                (cat.hindiName?.equals(selectedCategory, ignoreCase = true) == true) ||
                                (cat.id == "all_gk" && (selectedCategory.contains("All") || selectedCategory.contains("सभी")))

                        val label = when {
                            !cat.hindiName.isNullOrBlank() && cat.name.contains("All") -> "सभी (All)"
                            !cat.hindiName.isNullOrBlank() -> cat.hindiName
                            else -> cat.name
                        }

                        Box(
                            modifier = Modifier
                                .clip(RoundedCornerShape(16.dp))
                                .background(if (isSelected) BrandGreen else Slate50)
                                .border(
                                    width = 1.dp,
                                    color = if (isSelected) BrandGreen else BorderLight,
                                    shape = RoundedCornerShape(16.dp)
                                )
                                .clickable {
                                    selectedCategory = cat.hindiName ?: cat.name
                                    coroutineScope.launch { pagerState.scrollToPage(0) }
                                }
                                .padding(horizontal = 12.dp, vertical = 6.dp)
                                .testTag("static_gk_chip_${cat.id}")
                        ) {
                            Text(
                                text = label,
                                fontSize = 12.sp,
                                fontWeight = if (isSelected) FontWeight.Bold else FontWeight.Medium,
                                color = if (isSelected) Color.White else TextPrimary
                            )
                        }
                    }
                } else {
                    items(STATIC_GK_CATEGORIES) { cat ->
                        val isSelected = cat == selectedCategory
                        Box(
                            modifier = Modifier
                                .clip(RoundedCornerShape(16.dp))
                                .background(if (isSelected) BrandGreen else Slate50)
                            .border(
                                width = 1.dp,
                                color = if (isSelected) BrandGreen else BorderLight,
                                shape = RoundedCornerShape(16.dp)
                            )
                            .clickable {
                                selectedCategory = cat
                                coroutineScope.launch { pagerState.scrollToPage(0) }
                            }
                            .padding(horizontal = 12.dp, vertical = 6.dp)
                        ) {
                            Text(
                                text = cat,
                                fontSize = 12.sp,
                                fontWeight = if (isSelected) FontWeight.Bold else FontWeight.Medium,
                                color = if (isSelected) Color.White else TextPrimary
                            )
                        }
                    }
                }
            }
        }

        // Vertical Pager (Inshorts full-card swipe)
        if (displayList.isEmpty()) {
            Box(
                modifier = Modifier
                    .fillMaxSize()
                    .padding(32.dp),
                contentAlignment = Alignment.Center
            ) {
                Text(
                    text = "इस श्रेणी में कोई GK तथ्य उपलब्ध नहीं है।",
                    fontSize = 14.sp,
                    color = TextSecondary,
                    textAlign = TextAlign.Center
                )
            }
        } else {
            Box(modifier = Modifier.weight(1f)) {
                VerticalPager(
                    state = pagerState,
                    modifier = Modifier
                        .fillMaxSize()
                        .testTag("static_gk_pager")
                ) { page ->
                    val item = displayList[page]
                    StaticGkInshortCard(
                        gk = item,
                        pageIndex = page,
                        totalPages = displayList.size,
                        onToggleSave = { onToggleSave(item.id) },
                        onShare = { shareGkCard(context, item) },
                        modifier = Modifier.fillMaxSize()
                    )
                }

                // Up / Down quick scrollers
                Column(
                    modifier = Modifier
                        .align(Alignment.CenterEnd)
                        .padding(end = 8.dp),
                    verticalArrangement = Arrangement.spacedBy(8.dp)
                ) {
                    if (pagerState.currentPage > 0) {
                        Surface(
                            modifier = Modifier
                                .size(36.dp)
                                .clip(CircleShape)
                                .clickable {
                                    coroutineScope.launch {
                                        pagerState.animateScrollToPage(pagerState.currentPage - 1)
                                    }
                                },
                            color = MaterialTheme.colorScheme.surface.copy(alpha = 0.9f),
                            shadowElevation = 3.dp,
                            shape = CircleShape
                        ) {
                            Box(contentAlignment = Alignment.Center) {
                                Icon(
                                    imageVector = Icons.Default.KeyboardArrowUp,
                                    contentDescription = "Previous Card",
                                    tint = TextPrimary,
                                    modifier = Modifier.size(22.dp)
                                )
                            }
                        }
                    }

                    if (pagerState.currentPage < displayList.size - 1) {
                        Surface(
                            modifier = Modifier
                                .size(36.dp)
                                .clip(CircleShape)
                                .clickable {
                                    coroutineScope.launch {
                                        pagerState.animateScrollToPage(pagerState.currentPage + 1)
                                    }
                                },
                            color = MaterialTheme.colorScheme.surface.copy(alpha = 0.9f),
                            shadowElevation = 3.dp,
                            shape = CircleShape
                        ) {
                            Box(contentAlignment = Alignment.Center) {
                                Icon(
                                    imageVector = Icons.Default.KeyboardArrowDown,
                                    contentDescription = "Next Card",
                                    tint = TextPrimary,
                                    modifier = Modifier.size(22.dp)
                                )
                            }
                        }
                    }
                }
            }
        }
    }
}

@Composable
fun StaticGkInshortCard(
    gk: StaticGkCard,
    pageIndex: Int,
    totalPages: Int,
    onToggleSave: () -> Unit,
    onShare: () -> Unit,
    modifier: Modifier = Modifier
) {
    val scrollState = rememberScrollState()

    Card(
        modifier = modifier
            .padding(horizontal = 14.dp, vertical = 10.dp)
            .testTag("static_gk_card_${gk.id}"),
        shape = RoundedCornerShape(20.dp),
        colors = CardDefaults.cardColors(containerColor = MaterialTheme.colorScheme.surface),
        border = BorderStroke(1.dp, BorderLight.copy(alpha = 0.7f)),
        elevation = CardDefaults.cardElevation(defaultElevation = 1.dp)
    ) {
        Column(
            modifier = Modifier
                .fillMaxSize()
                .padding(16.dp)
        ) {
            Column(
                modifier = Modifier
                    .weight(1f)
                    .verticalScroll(scrollState)
            ) {
                // Header Meta Row
                Row(
                    modifier = Modifier.fillMaxWidth(),
                    verticalAlignment = Alignment.CenterVertically
                ) {
                    Box(
                        modifier = Modifier
                            .clip(RoundedCornerShape(8.dp))
                            .background(BrandGreenLight)
                            .padding(horizontal = 8.dp, vertical = 4.dp)
                    ) {
                        Text(
                            text = gk.category,
                            fontSize = 11.sp,
                            fontWeight = FontWeight.Bold,
                            color = BrandGreen
                        )
                    }

                    Spacer(modifier = Modifier.width(8.dp))

                    Text(
                        text = "•  उपयोगी: ${gk.relatedExam}",
                        fontSize = 11.sp,
                        color = TextSecondary,
                        maxLines = 1
                    )

                    Spacer(modifier = Modifier.weight(1f))

                    IconButton(
                        onClick = onToggleSave,
                        modifier = Modifier
                            .size(36.dp)
                            .testTag("static_gk_save_btn_${gk.id}")
                    ) {
                        Icon(
                            imageVector = if (gk.isSaved) Icons.Filled.Bookmark else Icons.Outlined.BookmarkBorder,
                            contentDescription = if (gk.isSaved) "Saved" else "Save",
                            tint = if (gk.isSaved) BrandGreen else Slate600,
                            modifier = Modifier.size(20.dp)
                        )
                    }

                    IconButton(
                        onClick = onShare,
                        modifier = Modifier
                            .size(36.dp)
                            .testTag("static_gk_share_btn_${gk.id}")
                    ) {
                        Icon(
                            imageVector = Icons.Outlined.Share,
                            contentDescription = "Share",
                            tint = Slate600,
                            modifier = Modifier.size(20.dp)
                        )
                    }
                }

                Spacer(modifier = Modifier.height(10.dp))

                // Thematic Banner
                GkHeroBanner(gk = gk)

                Spacer(modifier = Modifier.height(14.dp))

                // Title
                Text(
                    text = gk.title,
                    fontSize = 18.sp,
                    fontWeight = FontWeight.Bold,
                    color = TextPrimary,
                    lineHeight = 24.sp
                )

                Spacer(modifier = Modifier.height(10.dp))

                // 60-Word Inshorts Summary
                Text(
                    text = gk.summary,
                    fontSize = 13.5.sp,
                    color = TextSecondary,
                    lineHeight = 21.sp
                )

                Spacer(modifier = Modifier.height(12.dp))

                // Key Facts Box (महत्वपूर्ण तथ्य)
                Box(
                    modifier = Modifier
                        .fillMaxWidth()
                        .clip(RoundedCornerShape(12.dp))
                        .background(Slate50)
                        .border(1.dp, BorderLight, RoundedCornerShape(12.dp))
                        .padding(12.dp)
                ) {
                    Column(verticalArrangement = Arrangement.spacedBy(6.dp)) {
                        Text(
                            text = "📌 महत्वपूर्ण तथ्य (Key Facts):",
                            fontSize = 12.sp,
                            fontWeight = FontWeight.Bold,
                            color = TextPrimary
                        )

                        gk.facts.forEach { fact ->
                            Row(
                                modifier = Modifier.fillMaxWidth(),
                                verticalAlignment = Alignment.Top
                            ) {
                                Icon(
                                    imageVector = Icons.Outlined.CheckCircle,
                                    contentDescription = null,
                                    tint = BrandGreen,
                                    modifier = Modifier
                                        .size(15.dp)
                                        .padding(top = 2.dp)
                                )
                                Spacer(modifier = Modifier.width(6.dp))
                                Text(
                                    text = fact,
                                    fontSize = 12.sp,
                                    color = TextPrimary,
                                    lineHeight = 17.sp
                                )
                            }
                        }
                    }
                }

                Spacer(modifier = Modifier.height(12.dp))

                // Exam Tip Callout
                Box(
                    modifier = Modifier
                        .fillMaxWidth()
                        .clip(RoundedCornerShape(12.dp))
                        .background(BrandGreenLight.copy(alpha = 0.6f))
                        .border(1.dp, BrandGreen.copy(alpha = 0.25f), RoundedCornerShape(12.dp))
                        .padding(12.dp)
                ) {
                    Row(verticalAlignment = Alignment.Top) {
                        Icon(
                            imageVector = Icons.Outlined.Lightbulb,
                            contentDescription = null,
                            tint = BrandGold,
                            modifier = Modifier
                                .size(18.dp)
                                .padding(top = 1.dp)
                        )
                        Spacer(modifier = Modifier.width(8.dp))
                        Column {
                            Text(
                                text = "परीक्षा दृष्टिकोण (Exam Insight):",
                                fontSize = 12.sp,
                                fontWeight = FontWeight.Bold,
                                color = BrandGreenDark
                            )
                            Spacer(modifier = Modifier.height(2.dp))
                            Text(
                                text = gk.examTip,
                                fontSize = 11.5.sp,
                                color = TextPrimary,
                                lineHeight = 16.5.sp
                            )
                        }
                    }
                }

                Spacer(modifier = Modifier.height(12.dp))
            }

            // Bottom Navigation Footer
            Column(
                modifier = Modifier
                    .fillMaxWidth()
                    .padding(top = 8.dp)
            ) {
                HorizontalDivider(
                    color = BorderLight.copy(alpha = 0.6f),
                    thickness = 0.8.dp,
                    modifier = Modifier.padding(bottom = 8.dp)
                )

                Row(
                    modifier = Modifier.fillMaxWidth(),
                    verticalAlignment = Alignment.CenterVertically,
                    horizontalArrangement = Arrangement.SpaceBetween
                ) {
                    Row(verticalAlignment = Alignment.CenterVertically) {
                        Icon(
                            imageVector = Icons.Outlined.School,
                            contentDescription = null,
                            tint = BrandGreen,
                            modifier = Modifier.size(15.dp)
                        )
                        Spacer(modifier = Modifier.width(6.dp))
                        Text(
                            text = "CGPSC & व्यापम सामान्य अध्ययन",
                            fontSize = 11.sp,
                            fontWeight = FontWeight.Medium,
                            color = BrandGreen
                        )
                    }

                    if (pageIndex < totalPages - 1) {
                        Text(
                            text = "अगला तथ्य स्वाइप करें ↑",
                            fontSize = 10.5.sp,
                            fontWeight = FontWeight.SemiBold,
                            color = BrandGreen
                        )
                    } else {
                        Text(
                            text = "सभी तथ्य पढ़े जा चुके हैं",
                            fontSize = 10.sp,
                            color = TextSecondary
                        )
                    }
                }
            }
        }
    }
}

@Composable
fun GkHeroBanner(
    gk: StaticGkCard,
    modifier: Modifier = Modifier
) {
    val gradientColors = when (gk.category) {
        "भूगोल" -> listOf(Color(0xFF0F766E), Color(0xFF115E59))
        "इतिहास" -> listOf(Color(0xFF9A3412), Color(0xFF7C2D12))
        "संस्कृति व जनजाति" -> listOf(Color(0xFF7E22CE), Color(0xFF581C87))
        "राजव्यवस्था व अर्थव्यवस्था" -> listOf(Color(0xFF1E3A8A), Color(0xFF172554))
        else -> listOf(Color(0xFF15803D), Color(0xFF14532D))
    }

    Box(
        modifier = modifier
            .fillMaxWidth()
            .height(115.dp)
            .clip(RoundedCornerShape(14.dp))
            .background(Brush.linearGradient(gradientColors))
            .padding(14.dp)
    ) {
        Column(
            modifier = Modifier.fillMaxSize(),
            verticalArrangement = Arrangement.SpaceBetween
        ) {
            Row(
                modifier = Modifier.fillMaxWidth(),
                verticalAlignment = Alignment.CenterVertically,
                horizontalArrangement = Arrangement.SpaceBetween
            ) {
                Box(
                    modifier = Modifier
                        .clip(RoundedCornerShape(6.dp))
                        .background(Color.White.copy(alpha = 0.2f))
                        .padding(horizontal = 8.dp, vertical = 3.dp)
                ) {
                    Text(
                        text = "📖 छत्तीसगढ़ सामान्य ज्ञान कैप्सूल",
                        fontSize = 10.sp,
                        fontWeight = FontWeight.Bold,
                        color = Color.White
                    )
                }

                Text(
                    text = gk.category,
                    fontSize = 10.sp,
                    fontWeight = FontWeight.Bold,
                    color = Color.White.copy(alpha = 0.85f)
                )
            }

            Column {
                Text(
                    text = "स्टडी कैप्सूल • 60 शब्दों में तैयारी",
                    fontSize = 12.sp,
                    fontWeight = FontWeight.SemiBold,
                    color = Color.White
                )
                Text(
                    text = "सभी प्रतियोगी परीक्षाओं हेतु संकलित तथ्य",
                    fontSize = 10.sp,
                    color = Color.White.copy(alpha = 0.8f)
                )
            }
        }
    }
}

fun shareGkCard(context: Context, gk: StaticGkCard) {
    val shareBody = """
📚 *${gk.title}* [${gk.category}]
    
${gk.summary}

📌 *प्रमुख बिंदु:*
${gk.facts.joinToString("\n") { "• $it" }}

💡 *परीक्षा दृष्टिकोण:*
${gk.examTip}

📲 CGJobs ऐप पर छत्तीसगढ़ सामान्य ज्ञान व भर्तियों के सभी अपडेट्स पढ़ें!
    """.trimIndent()

    val intent = Intent(Intent.ACTION_SEND).apply {
        type = "text/plain"
        putExtra(Intent.EXTRA_SUBJECT, gk.title)
        putExtra(Intent.EXTRA_TEXT, shareBody)
    }
    context.startActivity(Intent.createChooser(intent, "GK तथ्य शेयर करें"))
}
