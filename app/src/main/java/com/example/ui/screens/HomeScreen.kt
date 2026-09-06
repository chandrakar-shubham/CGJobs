package com.example.ui.screens

import android.content.Context
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
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.lazy.itemsIndexed
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
import androidx.compose.material.icons.outlined.List
import androidx.compose.material.icons.outlined.OpenInNew
import androidx.compose.material.icons.outlined.SearchOff
import androidx.compose.material.icons.outlined.Share
import androidx.compose.material.icons.outlined.SwapVert
import androidx.compose.material.icons.outlined.ViewDay
import androidx.compose.material.icons.outlined.Work
import androidx.compose.material3.Button
import androidx.compose.material3.ButtonDefaults
import androidx.compose.material3.Card
import androidx.compose.material3.CardDefaults
import androidx.compose.material3.CircularProgressIndicator
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
import com.example.ui.components.CategorySelector
import com.example.ui.components.InshortsSwipeableCard
import com.example.ui.components.JobNewsCard
import com.example.ui.components.SecondaryJobCard
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

@Composable
fun HomeScreen(
    newsList: List<JobUpdate>,
    selectedCategory: String,
    isRefreshing: Boolean,
    onCategorySelected: (String) -> Unit,
    onArticleClick: (JobUpdate) -> Unit,
    onToggleSave: (String) -> Unit,
    onRefresh: () -> Unit,
    modifier: Modifier = Modifier,
    categories: List<AppCategory> = emptyList()
) {
    val context = LocalContext.current
    val coroutineScope = rememberCoroutineScope()
    // View mode: Inshorts vertical swipe mode (default) vs traditional list feed
    var isSwipeMode by remember { mutableStateOf(true) }

    val pagerState = rememberPagerState(pageCount = { newsList.size })

    Column(
        modifier = modifier
            .fillMaxSize()
            .background(MaterialTheme.colorScheme.background)
            .testTag("home_screen_root")
    ) {
        // Category Selector Chips & View Toggle Header
        Column(
            modifier = Modifier
                .fillMaxWidth()
                .background(MaterialTheme.colorScheme.surface)
                .padding(top = 2.dp, bottom = 4.dp)
        ) {
            // View mode switch row
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
                            .size(26.dp)
                            .clip(CircleShape)
                            .background(BrandGreenLight),
                        contentAlignment = Alignment.Center
                    ) {
                        Icon(
                            imageVector = Icons.Outlined.Work,
                            contentDescription = null,
                            tint = BrandGreen,
                            modifier = Modifier.size(15.dp)
                        )
                    }
                    Spacer(modifier = Modifier.width(8.dp))
                    Text(
                        text = if (isSwipeMode) "सरकारी नौकरी (Inshorts Swipe)" else "सरकारी नौकरी (सूची)",
                        fontSize = 14.sp,
                        fontWeight = FontWeight.Bold,
                        color = TextPrimary
                    )
                }

                Row(
                    verticalAlignment = Alignment.CenterVertically,
                    horizontalArrangement = Arrangement.spacedBy(6.dp)
                ) {
                    if (isSwipeMode && newsList.isNotEmpty()) {
                        Box(
                            modifier = Modifier
                                .clip(RoundedCornerShape(12.dp))
                                .background(Slate50)
                                .border(1.dp, BorderLight.copy(alpha = 0.5f), RoundedCornerShape(12.dp))
                                .padding(horizontal = 7.dp, vertical = 3.dp)
                        ) {
                            Text(
                                text = "${pagerState.currentPage + 1} / ${newsList.size}",
                                fontSize = 11.sp,
                                fontWeight = FontWeight.SemiBold,
                                color = TextSecondary
                            )
                        }
                    }

                    // Mode Toggle Button (Inshorts View vs List View)
                    Box(
                        modifier = Modifier
                            .clip(RoundedCornerShape(12.dp))
                            .background(Slate50)
                            .border(1.dp, BorderLight, RoundedCornerShape(12.dp))
                            .clickable { isSwipeMode = !isSwipeMode }
                            .padding(horizontal = 8.dp, vertical = 4.dp)
                    ) {
                        Row(verticalAlignment = Alignment.CenterVertically) {
                            Icon(
                                imageVector = if (isSwipeMode) Icons.Outlined.List else Icons.Outlined.ViewDay,
                                contentDescription = "Toggle View",
                                tint = BrandGreen,
                                modifier = Modifier.size(15.dp)
                            )
                            Spacer(modifier = Modifier.width(4.dp))
                            Text(
                                text = if (isSwipeMode) "List" else "Inshorts",
                                fontSize = 11.5.sp,
                                fontWeight = FontWeight.Bold,
                                color = BrandGreen
                            )
                        }
                    }
                }
            }

            // Category Chips
            CategorySelector(
                selectedCategory = selectedCategory,
                onCategorySelected = { category ->
                    onCategorySelected(category)
                    if (isSwipeMode) {
                        coroutineScope.launch { pagerState.scrollToPage(0) }
                    }
                },
                categories = categories,
                modifier = Modifier.fillMaxWidth()
            )
        }

        if (isRefreshing) {
            Box(
                modifier = Modifier
                    .fillMaxWidth()
                    .padding(6.dp),
                contentAlignment = Alignment.Center
            ) {
                CircularProgressIndicator(
                    modifier = Modifier.size(22.dp),
                    color = BrandGreen,
                    strokeWidth = 2.dp
                )
            }
        }

        if (newsList.isEmpty()) {
            // Empty State
            Box(
                modifier = Modifier
                    .fillMaxSize()
                    .padding(32.dp),
                contentAlignment = Alignment.Center
            ) {
                Column(horizontalAlignment = Alignment.CenterHorizontally) {
                    Icon(
                        imageVector = Icons.Outlined.SearchOff,
                        contentDescription = "No updates",
                        tint = Slate600,
                        modifier = Modifier.size(64.dp)
                    )
                    Spacer(modifier = Modifier.height(16.dp))
                    Text(
                        text = "इस श्रेणी में अभी कोई नई सूचना उपलब्ध नहीं है",
                        fontSize = 15.sp,
                        fontWeight = FontWeight.Bold,
                        color = TextPrimary,
                        textAlign = TextAlign.Center
                    )
                    Spacer(modifier = Modifier.height(8.dp))
                    Text(
                        text = "कृपया 'सभी' श्रेणी देखें या बाद में दोबारा जांचें।",
                        fontSize = 13.sp,
                        color = TextSecondary,
                        textAlign = TextAlign.Center
                    )
                    Spacer(modifier = Modifier.height(18.dp))
                    Button(
                        onClick = { onCategorySelected("सभी") },
                        colors = ButtonDefaults.buttonColors(containerColor = BrandGreen),
                        shape = RoundedCornerShape(16.dp),
                        modifier = Modifier.height(48.dp)
                    ) {
                        Text("सभी अपडेट्स देखें", color = Color.White, fontWeight = FontWeight.Bold)
                    }
                }
            }
        } else if (isSwipeMode) {
            // Inshorts-style Vertical Pager for Jobs
            Box(modifier = Modifier.weight(1f)) {
                VerticalPager(
                    state = pagerState,
                    modifier = Modifier
                        .fillMaxSize()
                        .testTag("jobs_vertical_pager")
                ) { page ->
                    val job = newsList[page]
                    InshortsSwipeableCard(
                        item = job.toInshortsPostItem(isJob = true),
                        pageIndex = page,
                        totalPages = newsList.size,
                        onArticleClick = { onArticleClick(job) },
                        onToggleSave = { onToggleSave(job.id) },
                        onDeepLinkClick = { onArticleClick(job) },
                        modifier = Modifier.fillMaxSize()
                    )
                }

                // Up / Down quick scroller buttons
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
                                    contentDescription = "Previous",
                                    tint = TextPrimary,
                                    modifier = Modifier.size(22.dp)
                                )
                            }
                        }
                    }

                    if (pagerState.currentPage < newsList.size - 1) {
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
                                    contentDescription = "Next",
                                    tint = TextPrimary,
                                    modifier = Modifier.size(22.dp)
                                )
                            }
                        }
                    }
                }
            }
        } else {
            // Traditional List Feed
            LazyColumn(
                modifier = Modifier
                    .fillMaxSize()
                    .testTag("home_feed_lazy_column"),
                contentPadding = PaddingValues(start = 16.dp, end = 16.dp, top = 4.dp, bottom = 84.dp),
                verticalArrangement = Arrangement.spacedBy(14.dp)
            ) {
                item(key = "featured_${newsList.first().id}") {
                    JobNewsCard(
                        job = newsList.first(),
                        onReadMore = { onArticleClick(newsList.first()) },
                        onOfficialNotification = { onArticleClick(newsList.first()) },
                        onToggleSave = { onToggleSave(newsList.first().id) }
                    )
                }

                itemsIndexed(
                    items = newsList.drop(1),
                    key = { _, item -> item.id }
                ) { index, item ->
                    if (index % 3 == 0 && item.isBreaking) {
                        JobNewsCard(
                            job = item,
                            onReadMore = { onArticleClick(item) },
                            onOfficialNotification = { onArticleClick(item) },
                            onToggleSave = { onToggleSave(item.id) }
                        )
                    } else {
                        SecondaryJobCard(
                            job = item,
                            onClick = { onArticleClick(item) },
                            onToggleSave = { onToggleSave(item.id) }
                        )
                    }
                }
            }
        }
    }
}

@Composable
fun JobInshortCard(
    job: JobUpdate,
    pageIndex: Int,
    totalPages: Int,
    onArticleClick: () -> Unit,
    onToggleSave: () -> Unit,
    onShare: () -> Unit,
    onOpenUrl: (String) -> Unit,
    modifier: Modifier = Modifier
) {
    val scrollState = rememberScrollState()
    val categoryTheme = getCategoryTheme(job.category)

    Card(
        modifier = modifier
            .padding(horizontal = 14.dp, vertical = 10.dp)
            .testTag("job_inshort_card_${job.id}"),
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
                            .background(categoryTheme.backgroundColor)
                            .padding(horizontal = 8.dp, vertical = 4.dp)
                    ) {
                        Row(verticalAlignment = Alignment.CenterVertically) {
                            Icon(
                                imageVector = categoryTheme.icon,
                                contentDescription = null,
                                tint = categoryTheme.color,
                                modifier = Modifier.size(13.dp)
                            )
                            Spacer(modifier = Modifier.width(4.dp))
                            Text(
                                text = job.category,
                                fontSize = 11.sp,
                                fontWeight = FontWeight.Bold,
                                color = categoryTheme.color
                            )
                        }
                    }

                    if (job.isBreaking || job.isNew) {
                        Spacer(modifier = Modifier.width(6.dp))
                        Box(
                            modifier = Modifier
                                .clip(RoundedCornerShape(8.dp))
                                .background(BreakingRed)
                                .padding(horizontal = 7.dp, vertical = 3.dp)
                        ) {
                            Text(
                                text = if (job.isBreaking) "BREAKING" else "NEW",
                                fontSize = 9.sp,
                                fontWeight = FontWeight.Bold,
                                color = Color.White
                            )
                        }
                    }

                    Spacer(modifier = Modifier.weight(1f))

                    Text(
                        text = job.relativeTime,
                        fontSize = 11.sp,
                        color = TextSecondary
                    )

                    Spacer(modifier = Modifier.width(4.dp))

                    IconButton(
                        onClick = onToggleSave,
                        modifier = Modifier
                            .size(36.dp)
                            .testTag("job_inshort_save_btn_${job.id}")
                    ) {
                        Icon(
                            imageVector = if (job.isSaved) Icons.Filled.Bookmark else Icons.Outlined.BookmarkBorder,
                            contentDescription = if (job.isSaved) "Saved" else "Save",
                            tint = if (job.isSaved) BrandGreen else Slate600,
                            modifier = Modifier.size(20.dp)
                        )
                    }

                    IconButton(
                        onClick = onShare,
                        modifier = Modifier
                            .size(36.dp)
                            .testTag("job_inshort_share_btn_${job.id}")
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

                // Thematic Hero Banner for Job
                JobHeroBanner(job = job)

                Spacer(modifier = Modifier.height(14.dp))

                // Title
                Text(
                    text = job.title,
                    fontSize = 18.sp,
                    fontWeight = FontWeight.Bold,
                    color = TextPrimary,
                    lineHeight = 24.sp
                )

                Spacer(modifier = Modifier.height(10.dp))

                // 60-word Inshorts Summary
                Text(
                    text = job.summary,
                    fontSize = 13.5.sp,
                    color = TextSecondary,
                    lineHeight = 21.sp
                )

                Spacer(modifier = Modifier.height(12.dp))

                // Key Job Info Box (पदों की संख्या, योग्यता, अंतिम तिथि)
                Box(
                    modifier = Modifier
                        .fillMaxWidth()
                        .clip(RoundedCornerShape(12.dp))
                        .background(Slate50)
                        .border(1.dp, BorderLight, RoundedCornerShape(12.dp))
                        .padding(12.dp)
                ) {
                    Column(verticalArrangement = Arrangement.spacedBy(6.dp)) {
                        if (!job.vacancies.isNullOrBlank()) {
                            Row(verticalAlignment = Alignment.CenterVertically) {
                                Text(
                                    text = "कुल पद (Vacancies):",
                                    fontSize = 12.sp,
                                    fontWeight = FontWeight.Bold,
                                    color = TextPrimary
                                )
                                Spacer(modifier = Modifier.width(6.dp))
                                Text(
                                    text = job.vacancies,
                                    fontSize = 12.sp,
                                    fontWeight = FontWeight.SemiBold,
                                    color = BrandGreen
                                )
                            }
                        }

                        if (!job.eligibility.isNullOrBlank()) {
                            Row(verticalAlignment = Alignment.Top) {
                                Text(
                                    text = "योग्यता (Eligibility):",
                                    fontSize = 12.sp,
                                    fontWeight = FontWeight.Bold,
                                    color = TextPrimary
                                )
                                Spacer(modifier = Modifier.width(6.dp))
                                Text(
                                    text = job.eligibility,
                                    fontSize = 12.sp,
                                    color = TextSecondary
                                )
                            }
                        }

                        if (job.importantDates.lastDate.isNotBlank()) {
                            Row(verticalAlignment = Alignment.CenterVertically) {
                                Text(
                                    text = "अंतिम तिथि (Last Date):",
                                    fontSize = 12.sp,
                                    fontWeight = FontWeight.Bold,
                                    color = BreakingRed
                                )
                                Spacer(modifier = Modifier.width(6.dp))
                                Text(
                                    text = job.importantDates.lastDate,
                                    fontSize = 12.sp,
                                    fontWeight = FontWeight.Bold,
                                    color = BreakingRed
                                )
                            }
                        }
                    }
                }

                Spacer(modifier = Modifier.height(14.dp))
            }

            // Bottom CTA Footer with Link to Full Details and Apply
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

                // Main CTA: Read full details
                Box(
                    modifier = Modifier
                        .fillMaxWidth()
                        .clip(RoundedCornerShape(12.dp))
                        .background(BrandGreenLight)
                        .border(1.dp, BrandGreen.copy(alpha = 0.3f), RoundedCornerShape(12.dp))
                        .clickable { onArticleClick() }
                        .padding(horizontal = 14.dp, vertical = 10.dp)
                ) {
                    Row(
                        modifier = Modifier.fillMaxWidth(),
                        verticalAlignment = Alignment.CenterVertically,
                        horizontalArrangement = Arrangement.SpaceBetween
                    ) {
                        Row(verticalAlignment = Alignment.CenterVertically) {
                            Box(
                                modifier = Modifier
                                    .size(28.dp)
                                    .clip(CircleShape)
                                    .background(BrandGreen),
                                contentAlignment = Alignment.Center
                            ) {
                                Icon(
                                    imageVector = Icons.AutoMirrored.Outlined.Article,
                                    contentDescription = null,
                                    tint = Color.White,
                                    modifier = Modifier.size(16.dp)
                                )
                            }
                            Spacer(modifier = Modifier.width(10.dp))
                            Column {
                                Text(
                                    text = "पूरी भर्ती विवरण पढ़ें (Full Details)",
                                    fontSize = 12.5.sp,
                                    fontWeight = FontWeight.Bold,
                                    color = BrandGreenDark
                                )
                                Text(
                                    text = "सिलेबस, अधिसूचना PDF व ऑनलाइन आवेदन लिंक",
                                    fontSize = 10.sp,
                                    color = BrandGreen
                                )
                            }
                        }

                        Icon(
                            imageVector = Icons.AutoMirrored.Filled.ArrowForward,
                            contentDescription = null,
                            tint = BrandGreen,
                            modifier = Modifier.size(18.dp)
                        )
                    }
                }

                Spacer(modifier = Modifier.height(6.dp))

                // Bottom Links Row (Apply Now + Official Link + Swipe Cue)
                Row(
                    modifier = Modifier.fillMaxWidth(),
                    verticalAlignment = Alignment.CenterVertically,
                    horizontalArrangement = Arrangement.SpaceBetween
                ) {
                    val targetUrl = job.applyUrl ?: job.officialNotificationUrl ?: job.sourceUrl
                    Row(
                        verticalAlignment = Alignment.CenterVertically,
                        modifier = Modifier
                            .clip(RoundedCornerShape(8.dp))
                            .clickable { onOpenUrl(targetUrl) }
                            .padding(horizontal = 4.dp, vertical = 2.dp)
                    ) {
                        Text(
                            text = if (!job.applyUrl.isNullOrBlank()) "ऑनलाइन आवेदन लिंक" else "आधिकारिक पोर्टल",
                            fontSize = 11.sp,
                            fontWeight = FontWeight.Bold,
                            color = BrandGreen
                        )
                        Spacer(modifier = Modifier.width(3.dp))
                        Icon(
                            imageVector = Icons.Outlined.OpenInNew,
                            contentDescription = "Open Link",
                            tint = BrandGreen,
                            modifier = Modifier.size(12.dp)
                        )
                    }

                    if (pageIndex < totalPages - 1) {
                        Text(
                            text = "स्वाइप करें ↑",
                            fontSize = 10.5.sp,
                            fontWeight = FontWeight.SemiBold,
                            color = BrandGreen
                        )
                    } else {
                        Text(
                            text = "सभी भर्तियां समाप्त",
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
fun JobHeroBanner(
    job: JobUpdate,
    modifier: Modifier = Modifier
) {
    val categoryTheme = getCategoryTheme(job.category)
    val gradientColors = when {
        job.category.contains("Police", ignoreCase = true) -> listOf(Color(0xFF1E3A8A), Color(0xFF1E40AF))
        job.category.contains("Teacher", ignoreCase = true) || job.category.contains("Education", ignoreCase = true) -> listOf(Color(0xFF9A3412), Color(0xFFC2410C))
        job.category.contains("CGPSC", ignoreCase = true) -> listOf(Color(0xFF1E40AF), Color(0xFF3B82F6))
        job.category.contains("Vyapam", ignoreCase = true) -> listOf(Color(0xFF831843), Color(0xFF9D174D))
        else -> listOf(Color(0xFF0F5132), Color(0xFF198754))
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
                        text = "🏛️ ${job.category} भर्ती बुलेटिन",
                        fontSize = 10.sp,
                        fontWeight = FontWeight.Bold,
                        color = Color.White
                    )
                }

                Text(
                    text = "स्रोत: ${job.source}",
                    fontSize = 10.sp,
                    color = Color.White.copy(alpha = 0.85f)
                )
            }

            Column {
                Text(
                    text = "निशुल्क आवेदन व विस्तृत जानकारी",
                    fontSize = 12.sp,
                    fontWeight = FontWeight.SemiBold,
                    color = Color.White
                )
                Text(
                    text = "प्रकाशित: ${job.publishedAt}",
                    fontSize = 10.sp,
                    color = Color.White.copy(alpha = 0.8f)
                )
            }
        }
    }
}
