package com.example.ui.screens

import android.content.Intent
import android.net.Uri
import androidx.compose.animation.AnimatedVisibility
import androidx.compose.animation.fadeIn
import androidx.compose.animation.fadeOut
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
import androidx.compose.material.icons.automirrored.filled.ArrowForward
import androidx.compose.material.icons.automirrored.outlined.Article
import androidx.compose.material.icons.filled.Bookmark
import androidx.compose.material.icons.filled.KeyboardArrowDown
import androidx.compose.material.icons.filled.KeyboardArrowUp
import androidx.compose.material.icons.outlined.BookmarkBorder
import androidx.compose.material.icons.outlined.DateRange
import androidx.compose.material.icons.outlined.OpenInNew
import androidx.compose.material.icons.outlined.Public
import androidx.compose.material.icons.outlined.School
import androidx.compose.material.icons.outlined.Share
import androidx.compose.material.icons.outlined.SwapVert
import androidx.compose.material.icons.outlined.Whatshot
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
import androidx.compose.ui.text.style.TextOverflow
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import com.example.model.AppCategory
import com.example.model.JobUpdate
import com.example.ui.components.InshortsSwipeableCard
import com.example.ui.components.getCategoryTheme
import com.example.ui.components.shareJobUpdate
import com.example.ui.components.toInshortsPostItem
import com.example.ui.theme.BorderLight
import com.example.ui.theme.BrandGold
import com.example.ui.theme.BrandGoldLight
import com.example.ui.theme.BrandGreen
import com.example.ui.theme.BrandGreenDark
import com.example.ui.theme.BrandGreenLight
import com.example.ui.theme.BreakingRed
import com.example.ui.theme.Slate50
import com.example.ui.theme.Slate100
import com.example.ui.theme.Slate600
import com.example.ui.theme.TextPrimary
import com.example.ui.theme.TextSecondary
import kotlinx.coroutines.launch

val CURRENT_AFFAIRS_FILTERS = listOf(
    "सभी (All)",
    "राष्ट्रीय (National)",
    "अंतर्राष्ट्रीय (International)",
    "छत्तीसगढ़ (State)",
    "अर्थव्यवस्था (Economy)",
    "खेलकूद (Sports)",
    "सरकारी योजनाएं"
)

@Composable
fun CurrentAffairsScreen(
    newsList: List<JobUpdate>,
    onArticleClick: (JobUpdate) -> Unit,
    onToggleSave: (String) -> Unit,
    modifier: Modifier = Modifier,
    newsCategories: List<AppCategory> = emptyList()
) {
    val context = LocalContext.current
    val coroutineScope = rememberCoroutineScope()
    var selectedFilter by remember { mutableStateOf("सभी (All)") }

    // Filter news list based on selected tab chip
    val filteredList = remember(newsList, selectedFilter) {
        val baseList = if (newsList.any { it.section.equals("news", ignoreCase = true) || it.category.equals("Current Affairs", ignoreCase = true) }) {
            newsList.filter {
                it.section.equals("news", ignoreCase = true) ||
                it.category.equals("Current Affairs", ignoreCase = true) ||
                it.title.contains("समसामयिकी") ||
                it.title.contains("करेंट अफेयर्स") ||
                it.title.contains("बजट")
            }
        } else {
            newsList
        }

        val cleanFilter = selectedFilter.trim()
        when {
            cleanFilter == "सभी (All)" || cleanFilter == "सभी" || cleanFilter.equals("All", ignoreCase = true) || cleanFilter.equals("all_news", ignoreCase = true) || cleanFilter.startsWith("सभी") || cleanFilter.startsWith("All") -> baseList
            cleanFilter.contains("राष्ट्रीय") || cleanFilter.contains("National") -> baseList.filter {
                it.title.contains("भारत") || it.title.contains("राष्ट्रीय") || it.title.contains("केंद्र") || it.title.contains("देश") || it.summary.contains("भारत") || it.summary.contains("राष्ट्रीय")
            }.ifEmpty { baseList.filter { !it.title.contains("अंतर्राष्ट्रीय") } }
            cleanFilter.contains("अंतर्राष्ट्रीय") || cleanFilter.contains("International") -> baseList.filter {
                it.title.contains("विश्व") || it.title.contains("अंतर्राष्ट्रीय") || it.title.contains("ग्लोबल") || it.title.contains("विदेशी") || it.summary.contains("अंतर्राष्ट्रीय") || it.summary.contains("विश्व")
            }
            cleanFilter.contains("छत्तीसगढ़") || cleanFilter.contains("State") -> baseList.filter {
                it.title.contains("छत्तीसगढ़") || it.title.contains("बस्तर") || it.title.contains("रायपुर") || it.title.contains("टाइगर") || it.title.contains("राज्य")
            }
            cleanFilter.contains("अर्थव्यवस्था") || cleanFilter.contains("Economy") || cleanFilter.contains("बजट") -> baseList.filter {
                it.title.contains("बजट") || it.title.contains("रोजगार") || it.title.contains("अर्थ") || it.title.contains("तेंदूपत्ता") || it.title.contains("धान") || it.title.contains("आईटी") || it.summary.contains("बजट") || it.summary.contains("करोड़")
            }
            cleanFilter.contains("खेलकूद") || cleanFilter.contains("Sports") -> baseList.filter {
                it.title.contains("खेल") || it.title.contains("पुरस्कार") || it.title.contains("पद्म") || it.title.contains("ओलंपिक") || it.title.contains("पदक") || it.title.contains("क्रिकेट") || it.summary.contains("खेल")
            }
            cleanFilter.contains("योजना") || cleanFilter.contains("Schemes") -> baseList.filter {
                it.title.contains("योजना") || it.summary.contains("योजना") || it.detailedContent.contains("योजना")
            }
            cleanFilter.contains("पर्यावरण") || cleanFilter.contains("Environment") || cleanFilter.contains("वन") -> baseList.filter {
                it.title.contains("पर्यावरण") || it.title.contains("वन") || it.title.contains("टाइगर") || it.summary.contains("वन")
            }
            else -> baseList.filter {
                it.category.contains(cleanFilter, ignoreCase = true) ||
                it.title.contains(cleanFilter, ignoreCase = true) ||
                it.summary.contains(cleanFilter, ignoreCase = true)
            }.ifEmpty { baseList }
        }
    }

    val displayList = if (filteredList.isNotEmpty()) filteredList else newsList
    val pagerState = rememberPagerState(pageCount = { displayList.size })

    Column(
        modifier = modifier
            .fillMaxSize()
            .background(MaterialTheme.colorScheme.background)
            .testTag("current_affairs_screen_root")
    ) {
        // Top Filter and Status Row
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
                            imageVector = Icons.Outlined.Whatshot,
                            contentDescription = null,
                            tint = BrandGreen,
                            modifier = Modifier.size(16.dp)
                        )
                    }
                    Spacer(modifier = Modifier.width(8.dp))
                    Text(
                        text = "करेंट अफेयर्स (Inshorts)",
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

            // Category Filter Chips
            LazyRow(
                modifier = Modifier
                    .fillMaxWidth()
                    .testTag("current_affairs_filter_row"),
                contentPadding = PaddingValues(horizontal = 14.dp, vertical = 6.dp),
                horizontalArrangement = Arrangement.spacedBy(8.dp)
            ) {
                if (newsCategories.isNotEmpty()) {
                    items(newsCategories, key = { it.id }) { cat ->
                        val isSelected = selectedFilter.equals(cat.name, ignoreCase = true) ||
                                selectedFilter.equals(cat.id, ignoreCase = true) ||
                                (cat.hindiName?.equals(selectedFilter, ignoreCase = true) == true) ||
                                (cat.id == "all_news" && (selectedFilter.contains("All") || selectedFilter.contains("सभी")))

                        val label = when {
                            !cat.hindiName.isNullOrBlank() && cat.name.contains("All") -> "सभी (All)"
                            !cat.hindiName.isNullOrBlank() -> "${cat.hindiName} (${cat.name})"
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
                                    selectedFilter = cat.name
                                    coroutineScope.launch {
                                        pagerState.scrollToPage(0)
                                    }
                                }
                                .padding(horizontal = 12.dp, vertical = 6.dp)
                                .testTag("current_affairs_chip_${cat.id}")
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
                    items(CURRENT_AFFAIRS_FILTERS) { filter ->
                        val isSelected = filter == selectedFilter
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
                                    selectedFilter = filter
                                    coroutineScope.launch {
                                        pagerState.scrollToPage(0)
                                    }
                                }
                                .padding(horizontal = 12.dp, vertical = 6.dp)
                        ) {
                            Text(
                                text = filter,
                                fontSize = 12.sp,
                                fontWeight = if (isSelected) FontWeight.Bold else FontWeight.Medium,
                                color = if (isSelected) Color.White else TextPrimary
                            )
                        }
                    }
                }
            }
        }

        // Vertical Pager (Inshorts scrollable format)
        if (displayList.isEmpty()) {
            Box(
                modifier = Modifier
                    .fillMaxSize()
                    .padding(32.dp),
                contentAlignment = Alignment.Center
            ) {
                Text(
                    text = "इस श्रेणी में कोई करेंट अफेयर्स उपलब्ध नहीं है।",
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
                        .testTag("current_affairs_pager")
                ) { page ->
                    val currentNews = displayList[page]
                    InshortsSwipeableCard(
                        item = currentNews.toInshortsPostItem(isJob = false),
                        pageIndex = page,
                        totalPages = displayList.size,
                        onArticleClick = { onArticleClick(currentNews) },
                        onToggleSave = { onToggleSave(currentNews.id) },
                        onDeepLinkClick = { onArticleClick(currentNews) },
                        modifier = Modifier.fillMaxSize()
                    )
                }

                // Floating Next/Previous Navigation Buttons
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
fun InshortNewsCard(
    news: JobUpdate,
    pageIndex: Int,
    totalPages: Int,
    onArticleClick: () -> Unit,
    onToggleSave: () -> Unit,
    onShare: () -> Unit,
    onOpenSourceUrl: (String) -> Unit,
    modifier: Modifier = Modifier
) {
    val scrollState = rememberScrollState()
    val categoryTheme = getCategoryTheme(news.category)

    Card(
        modifier = modifier
            .padding(horizontal = 14.dp, vertical = 10.dp)
            .testTag("inshort_card_${news.id}"),
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
            // Card Content (Scrollable if screen is small)
            Column(
                modifier = Modifier
                    .weight(1f)
                    .verticalScroll(scrollState)
            ) {
                // Top Meta Header: Category Tag, Source, Actions
                Row(
                    modifier = Modifier.fillMaxWidth(),
                    verticalAlignment = Alignment.CenterVertically
                ) {
                    // Category Tag Badge
                    Box(
                        modifier = Modifier
                            .clip(RoundedCornerShape(8.dp))
                            .background(categoryTheme.backgroundColor)
                            .padding(horizontal = 8.dp, vertical = 4.dp)
                    ) {
                        Text(
                            text = if (news.category == "Current Affairs") "समसामयिकी" else news.category,
                            fontSize = 11.sp,
                            fontWeight = FontWeight.Bold,
                            color = categoryTheme.color
                        )
                    }

                    Spacer(modifier = Modifier.width(8.dp))

                    Text(
                        text = "•  ${news.relativeTime}",
                        fontSize = 11.sp,
                        color = TextSecondary
                    )

                    Spacer(modifier = Modifier.weight(1f))

                    // Bookmark Button
                    IconButton(
                        onClick = onToggleSave,
                        modifier = Modifier
                            .size(36.dp)
                            .testTag("inshort_save_btn_${news.id}")
                    ) {
                        Icon(
                            imageVector = if (news.isSaved) Icons.Filled.Bookmark else Icons.Outlined.BookmarkBorder,
                            contentDescription = if (news.isSaved) "Saved" else "Save",
                            tint = if (news.isSaved) BrandGreen else Slate600,
                            modifier = Modifier.size(20.dp)
                        )
                    }

                    // Share Button
                    IconButton(
                        onClick = onShare,
                        modifier = Modifier
                            .size(36.dp)
                            .testTag("inshort_share_btn_${news.id}")
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

                // Hero Thematic Visual Graphic Banner
                InshortHeroBanner(
                    news = news,
                    modifier = Modifier
                        .fillMaxWidth()
                        .height(130.dp)
                )

                Spacer(modifier = Modifier.height(14.dp))

                // Headline (शीर्षक)
                Text(
                    text = news.title,
                    fontSize = 17.5.sp,
                    fontWeight = FontWeight.Bold,
                    color = TextPrimary,
                    lineHeight = 23.sp
                )

                Spacer(modifier = Modifier.height(10.dp))

                // Inshorts 60-Word Concise Summary (शॉर्ट खबर)
                Text(
                    text = news.summary,
                    fontSize = 13.5.sp,
                    color = TextSecondary,
                    lineHeight = 21.sp,
                    letterSpacing = 0.15.sp
                )

                Spacer(modifier = Modifier.height(12.dp))

                // Exam Takeaways Box (परीक्षा विशेष)
                Box(
                    modifier = Modifier
                        .fillMaxWidth()
                        .clip(RoundedCornerShape(12.dp))
                        .background(BrandGreenLight.copy(alpha = 0.5f))
                        .border(1.dp, BrandGreen.copy(alpha = 0.2f), RoundedCornerShape(12.dp))
                        .padding(12.dp)
                ) {
                    Column {
                        Row(verticalAlignment = Alignment.CenterVertically) {
                            Icon(
                                imageVector = Icons.Outlined.School,
                                contentDescription = null,
                                tint = BrandGreen,
                                modifier = Modifier.size(16.dp)
                            )
                            Spacer(modifier = Modifier.width(6.dp))
                            Text(
                                text = "परीक्षा उपयोगी तथ्य (Key Takeaway):",
                                fontSize = 12.sp,
                                fontWeight = FontWeight.Bold,
                                color = BrandGreenDark
                            )
                        }
                        Spacer(modifier = Modifier.height(4.dp))
                        Text(
                            text = news.examTakeaway ?: if (news.eligibility != null) {
                                "${news.eligibility} • विभाग: ${news.source}"
                            } else {
                                "CGPSC, व्यापम, शिक्षक व राज्य स्तरीय प्रतियोगी परीक्षाओं हेतु महत्वपूर्ण।"
                            },
                            fontSize = 11.5.sp,
                            color = TextPrimary,
                            lineHeight = 17.sp
                        )
                    }
                }

                Spacer(modifier = Modifier.height(12.dp))
            }

            // ==========================================
            // ANCHORED BOTTOM SECTION: LINK TO NEWSPAGE
            // ==========================================
            Column(
                modifier = Modifier
                    .fillMaxWidth()
                    .padding(top = 8.dp)
            ) {
                HorizontalDivider(
                    color = BorderLight.copy(alpha = 0.6f),
                    thickness = 0.8.dp,
                    modifier = Modifier.padding(bottom = 10.dp)
                )

                // Primary Prominent Link: Open Full News Page
                Card(
                    modifier = Modifier
                        .fillMaxWidth()
                        .clip(RoundedCornerShape(14.dp))
                        .clickable { onArticleClick() }
                        .testTag("inshort_open_newspage_link_${news.id}"),
                    colors = CardDefaults.cardColors(containerColor = BrandGreenLight),
                    border = BorderStroke(1.2.dp, BrandGreen.copy(alpha = 0.35f)),
                    shape = RoundedCornerShape(14.dp)
                ) {
                    Row(
                        modifier = Modifier
                            .fillMaxWidth()
                            .padding(horizontal = 14.dp, vertical = 11.dp),
                        verticalAlignment = Alignment.CenterVertically,
                        horizontalArrangement = Arrangement.SpaceBetween
                    ) {
                        Row(
                            verticalAlignment = Alignment.CenterVertically,
                            modifier = Modifier.weight(1f)
                        ) {
                            Box(
                                modifier = Modifier
                                    .size(32.dp)
                                    .clip(CircleShape)
                                    .background(BrandGreen),
                                contentAlignment = Alignment.Center
                            ) {
                                Icon(
                                    imageVector = Icons.AutoMirrored.Outlined.Article,
                                    contentDescription = "News Page Link",
                                    tint = Color.White,
                                    modifier = Modifier.size(18.dp)
                                )
                            }

                            Spacer(modifier = Modifier.width(10.dp))

                            Column {
                                Text(
                                    text = "पूरी खबर पढ़ें (View Full News Page)",
                                    fontSize = 13.sp,
                                    fontWeight = FontWeight.Bold,
                                    color = BrandGreenDark
                                )
                                Text(
                                    text = "विस्तृत विवरण, पाठ्यक्रम व आधिकारिक सूचना",
                                    fontSize = 10.5.sp,
                                    color = BrandGreen
                                )
                            }
                        }

                        Icon(
                            imageVector = Icons.AutoMirrored.Filled.ArrowForward,
                            contentDescription = "Go to news page",
                            tint = BrandGreen,
                            modifier = Modifier.size(18.dp)
                        )
                    }
                }

                Spacer(modifier = Modifier.height(8.dp))

                // Secondary Row: Source link, Website link & Swipe cue
                Row(
                    modifier = Modifier.fillMaxWidth(),
                    verticalAlignment = Alignment.CenterVertically,
                    horizontalArrangement = Arrangement.SpaceBetween
                ) {
                    Row(verticalAlignment = Alignment.CenterVertically) {
                        // Source web link (Real news portal)
                        Row(
                            verticalAlignment = Alignment.CenterVertically,
                            modifier = Modifier
                                .clip(RoundedCornerShape(8.dp))
                                .clickable { onOpenSourceUrl(news.sourceUrl) }
                                .padding(horizontal = 4.dp, vertical = 2.dp)
                        ) {
                            Text(
                                text = "स्रोत: ${news.source}",
                                fontSize = 10.5.sp,
                                fontWeight = FontWeight.Medium,
                                color = Slate600
                            )
                            Spacer(modifier = Modifier.width(4.dp))
                            Icon(
                                imageVector = Icons.Outlined.OpenInNew,
                                contentDescription = "Open Source",
                                tint = Slate600,
                                modifier = Modifier.size(12.dp)
                            )
                        }

                        if (!news.webArticleUrl.isNullOrBlank()) {
                            Spacer(modifier = Modifier.width(6.dp))
                            Row(
                                verticalAlignment = Alignment.CenterVertically,
                                modifier = Modifier
                                    .clip(RoundedCornerShape(8.dp))
                                    .clickable { onOpenSourceUrl(news.webArticleUrl!!) }
                                    .padding(horizontal = 4.dp, vertical = 2.dp)
                            ) {
                                Text(
                                    text = "वेबसाइट लेख",
                                    fontSize = 10.5.sp,
                                    fontWeight = FontWeight.SemiBold,
                                    color = BrandGreenDark
                                )
                                Spacer(modifier = Modifier.width(3.dp))
                                Icon(
                                    imageVector = Icons.Outlined.OpenInNew,
                                    contentDescription = "Open Web Article",
                                    tint = BrandGreenDark,
                                    modifier = Modifier.size(11.dp)
                                )
                            }
                        }
                    }

                    // Swipe next cue
                    if (pageIndex < totalPages - 1) {
                        Row(verticalAlignment = Alignment.CenterVertically) {
                            Text(
                                text = "स्वाइप करें ↑",
                                fontSize = 10.5.sp,
                                fontWeight = FontWeight.SemiBold,
                                color = BrandGreen
                            )
                        }
                    } else {
                        Text(
                            text = "नवीनतम अपडेट समाप्त",
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
fun InshortHeroBanner(
    news: JobUpdate,
    modifier: Modifier = Modifier
) {
    val categoryTheme = getCategoryTheme(news.category)
    val gradientColors = when {
        news.title.contains("टाइगर") || news.title.contains("वन") -> listOf(
            Color(0xFF1B5E20),
            Color(0xFF2E7D32)
        )
        news.title.contains("बजट") || news.title.contains("आईटी") -> listOf(
            Color(0xFF0F172A),
            Color(0xFF1E3A8A)
        )
        news.title.contains("खेल") || news.title.contains("ओलंपिक") -> listOf(
            Color(0xFF831843),
            Color(0xFFBE185D)
        )
        news.title.contains("तेंदूपत्ता") || news.title.contains("धान") -> listOf(
            Color(0xFF78350F),
            Color(0xFFD97706)
        )
        news.title.contains("रामलला") || news.title.contains("संस्कृति") -> listOf(
            Color(0xFF7C2D12),
            Color(0xFFEA580C)
        )
        else -> listOf(
            Color(0xFF0B6B46),
            Color(0xFF15803D)
        )
    }

    Box(
        modifier = modifier
            .clip(RoundedCornerShape(14.dp))
            .background(Brush.linearGradient(gradientColors))
            .padding(14.dp)
    ) {
        // Decorative background icon
        Icon(
            imageVector = Icons.Outlined.Public,
            contentDescription = null,
            tint = Color.White.copy(alpha = 0.12f),
            modifier = Modifier
                .size(90.dp)
                .align(Alignment.BottomEnd)
        )

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
                        text = "⚡ छत्तीसगढ़ समसामयिक बुलेटिन",
                        fontSize = 10.sp,
                        fontWeight = FontWeight.Bold,
                        color = Color.White
                    )
                }

                if (news.isBreaking || news.isNew) {
                    Box(
                        modifier = Modifier
                            .clip(RoundedCornerShape(6.dp))
                            .background(BreakingRed)
                            .padding(horizontal = 6.dp, vertical = 2.dp)
                    ) {
                        Text(
                            text = if (news.isBreaking) "BREAKING" else "NEW",
                            fontSize = 9.sp,
                            fontWeight = FontWeight.Bold,
                            color = Color.White
                        )
                    }
                }
            }

            Column {
                Text(
                    text = news.source,
                    fontSize = 12.sp,
                    fontWeight = FontWeight.SemiBold,
                    color = Color.White.copy(alpha = 0.9f)
                )
                Text(
                    text = "प्रकाशित: ${news.publishedAt}",
                    fontSize = 10.sp,
                    color = Color.White.copy(alpha = 0.75f)
                )
            }
        }
    }
}
