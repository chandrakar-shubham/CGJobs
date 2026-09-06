package com.example.ui.components

import android.content.ClipData
import android.content.ClipboardManager
import android.content.Context
import android.content.Intent
import android.net.Uri
import android.widget.Toast
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
import androidx.compose.material.icons.outlined.CheckCircle
import androidx.compose.material.icons.outlined.ContentCopy
import androidx.compose.material.icons.outlined.DateRange
import androidx.compose.material.icons.outlined.Image
import androidx.compose.material.icons.outlined.Link
import androidx.compose.material.icons.outlined.OpenInNew
import androidx.compose.material.icons.outlined.School
import androidx.compose.material.icons.outlined.Share
import androidx.compose.material.icons.outlined.SwapVert
import androidx.compose.material.icons.outlined.Work
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
import androidx.compose.ui.layout.ContentScale
import androidx.compose.ui.platform.LocalContext
import androidx.compose.ui.platform.testTag
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.style.TextOverflow
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import coil.compose.SubcomposeAsyncImage
import com.example.model.JobUpdate
import com.example.ui.theme.BorderLight
import com.example.ui.theme.BrandGold
import com.example.ui.theme.BrandGoldLight
import com.example.ui.theme.BrandGreen
import com.example.ui.theme.BrandGreenDark
import com.example.ui.theme.BrandGreenLight
import com.example.ui.theme.BreakingRed
import com.example.ui.theme.Slate100
import com.example.ui.theme.Slate50
import com.example.ui.theme.Slate600
import com.example.ui.theme.TextPrimary
import com.example.ui.theme.TextSecondary
import kotlinx.coroutines.launch

/**
 * Universal data model for Inshorts-style items (supports both News articles and Job postings).
 */
data class InshortsPostItem(
    val id: String,
    val title: String,
    val summary: String,
    val detailedContent: String = "",
    val category: String,
    val source: String,
    val sourceUrl: String? = null,
    val imageUrl: String? = null,
    val publishedAt: String = "",
    val relativeTime: String = "",
    val isJob: Boolean = false,
    val isBreaking: Boolean = false,
    val isNew: Boolean = false,
    val vacancies: String? = null,
    val eligibility: String? = null,
    val lastDate: String? = null,
    val applyUrl: String? = null,
    val officialNotificationUrl: String? = null,
    val examTakeaway: String? = null,
    val isSaved: Boolean = false,
    val deepLinkUri: String = "cgjobs://post/$id"
)

/**
 * Extension mapper from JobUpdate domain entity to InshortsPostItem.
 */
fun JobUpdate.toInshortsPostItem(isJob: Boolean = true): InshortsPostItem {
    return InshortsPostItem(
        id = id,
        title = title,
        summary = summary,
        detailedContent = detailedContent,
        category = category,
        source = source,
        sourceUrl = sourceUrl,
        imageUrl = imageUrl,
        publishedAt = publishedAt,
        relativeTime = relativeTime,
        isJob = isJob,
        isBreaking = isBreaking,
        isNew = isNew,
        vacancies = vacancies,
        eligibility = eligibility,
        lastDate = importantDates.lastDate,
        applyUrl = applyUrl,
        officialNotificationUrl = officialNotificationUrl,
        examTakeaway = if (!isJob) eligibility ?: "CGPSC, व्यापम व राज्य स्तरीय प्रतियोगी परीक्षाओं हेतु महत्वपूर्ण।" else null,
        isSaved = isSaved,
        deepLinkUri = "cgjobs://post/$id"
    )
}

/**
 * Reusable Inshorts-style Swipeable Card Component in Jetpack Compose.
 * Displays news or job posts with image support, concise 60-word summary,
 * metadata badges, and comprehensive deep-linking capabilities.
 */
@Composable
fun InshortsSwipeableCard(
    item: InshortsPostItem,
    pageIndex: Int,
    totalPages: Int,
    onArticleClick: () -> Unit,
    onToggleSave: () -> Unit,
    onDeepLinkClick: ((String) -> Unit)? = null,
    modifier: Modifier = Modifier
) {
    val context = LocalContext.current
    val scrollState = rememberScrollState()
    val categoryTheme = getCategoryTheme(item.category)

    Card(
        modifier = modifier
            .padding(horizontal = 14.dp, vertical = 8.dp)
            .testTag("inshorts_card_${item.id}"),
        shape = RoundedCornerShape(20.dp),
        colors = CardDefaults.cardColors(containerColor = MaterialTheme.colorScheme.surface),
        border = BorderStroke(1.dp, BorderLight.copy(alpha = 0.8f)),
        elevation = CardDefaults.cardElevation(defaultElevation = 1.dp)
    ) {
        Column(
            modifier = Modifier
                .fillMaxSize()
                .padding(16.dp)
        ) {
            // Scrollable Content Area (for long descriptions on small screens)
            Column(
                modifier = Modifier
                    .weight(1f)
                    .verticalScroll(scrollState)
            ) {
                // Top Meta Header: Category Tag, Source/Time, Deep link action & Save
                Row(
                    modifier = Modifier.fillMaxWidth(),
                    verticalAlignment = Alignment.CenterVertically
                ) {
                    // Category Badge
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
                                text = item.category,
                                fontSize = 11.sp,
                                fontWeight = FontWeight.Bold,
                                color = categoryTheme.color
                            )
                        }
                    }

                    if (item.isBreaking || item.isNew) {
                        Spacer(modifier = Modifier.width(6.dp))
                        Box(
                            modifier = Modifier
                                .clip(RoundedCornerShape(8.dp))
                                .background(BreakingRed)
                                .padding(horizontal = 7.dp, vertical = 3.dp)
                        ) {
                            Text(
                                text = if (item.isBreaking) "BREAKING" else "NEW",
                                fontSize = 9.sp,
                                fontWeight = FontWeight.Bold,
                                color = Color.White
                            )
                        }
                    }

                    Spacer(modifier = Modifier.weight(1f))

                    Text(
                        text = item.relativeTime,
                        fontSize = 11.sp,
                        color = TextSecondary
                    )

                    Spacer(modifier = Modifier.width(2.dp))

                    // Copy / Share Deep Link Button
                    IconButton(
                        onClick = {
                            shareDeepLink(context, item)
                        },
                        modifier = Modifier
                            .size(36.dp)
                            .testTag("inshorts_share_btn_${item.id}")
                    ) {
                        Icon(
                            imageVector = Icons.Outlined.Share,
                            contentDescription = "Share Deep Link",
                            tint = Slate600,
                            modifier = Modifier.size(19.dp)
                        )
                    }

                    // Save / Bookmark Button
                    IconButton(
                        onClick = onToggleSave,
                        modifier = Modifier
                            .size(36.dp)
                            .testTag("inshorts_save_btn_${item.id}")
                    ) {
                        Icon(
                            imageVector = if (item.isSaved) Icons.Filled.Bookmark else Icons.Outlined.BookmarkBorder,
                            contentDescription = if (item.isSaved) "Saved" else "Save",
                            tint = if (item.isSaved) BrandGreen else Slate600,
                            modifier = Modifier.size(20.dp)
                        )
                    }
                }

                Spacer(modifier = Modifier.height(10.dp))

                // Image Banner or Thematic Artwork
                InshortsMediaBanner(
                    item = item,
                    modifier = Modifier
                        .fillMaxWidth()
                        .height(130.dp)
                )

                Spacer(modifier = Modifier.height(12.dp))

                // Headline / Title
                Text(
                    text = item.title,
                    fontSize = 17.5.sp,
                    fontWeight = FontWeight.Bold,
                    color = TextPrimary,
                    lineHeight = 23.sp
                )

                Spacer(modifier = Modifier.height(10.dp))

                // Concise 60-Word Inshorts Summary
                Text(
                    text = item.summary,
                    fontSize = 13.5.sp,
                    color = TextSecondary,
                    lineHeight = 21.sp,
                    letterSpacing = 0.15.sp
                )

                Spacer(modifier = Modifier.height(12.dp))

                // Key Info Box (Job Details or Exam Takeaway)
                if (item.isJob) {
                    JobKeyInfoBox(item = item)
                } else {
                    ExamTakeawayBox(item = item)
                }

                Spacer(modifier = Modifier.height(10.dp))
            }

            // Anchored Bottom Section: Deep-link Call to Action & Navigation Links
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

                // Primary Deep-Link Button: Open Full Details in-app or via deep-link
                Box(
                    modifier = Modifier
                        .fillMaxWidth()
                        .clip(RoundedCornerShape(12.dp))
                        .background(BrandGreenLight)
                        .border(1.dp, BrandGreen.copy(alpha = 0.35f), RoundedCornerShape(12.dp))
                        .clickable {
                            if (onDeepLinkClick != null) {
                                onDeepLinkClick(item.deepLinkUri)
                            } else {
                                onArticleClick()
                            }
                        }
                        .padding(horizontal = 14.dp, vertical = 10.dp)
                        .testTag("inshorts_deep_link_cta_${item.id}")
                ) {
                    Row(
                        modifier = Modifier.fillMaxWidth(),
                        verticalAlignment = Alignment.CenterVertically,
                        horizontalArrangement = Arrangement.SpaceBetween
                    ) {
                        Row(
                            verticalAlignment = Alignment.CenterVertically,
                            modifier = Modifier.weight(1f)
                        ) {
                            Box(
                                modifier = Modifier
                                    .size(30.dp)
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
                                    text = if (item.isJob) "पूरी भर्ती विवरण (Full Details)" else "पूरी खबर पढ़ें (Read Full Story)",
                                    fontSize = 12.5.sp,
                                    fontWeight = FontWeight.Bold,
                                    color = BrandGreenDark
                                )
                                Text(
                                    text = if (item.isJob) "विस्तृत योग्यता, सिलेबस व आधिकारिक PDF" else "विस्तृत तथ्य व विश्लेषण",
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

                // Secondary Row: Direct External Portal Link + Copy Deep Link + Swipe Cue
                Row(
                    modifier = Modifier.fillMaxWidth(),
                    verticalAlignment = Alignment.CenterVertically,
                    horizontalArrangement = Arrangement.SpaceBetween
                ) {
                    val targetUrl = item.applyUrl ?: item.officialNotificationUrl ?: item.sourceUrl
                    if (!targetUrl.isNullOrBlank()) {
                        Row(
                            verticalAlignment = Alignment.CenterVertically,
                            modifier = Modifier
                                .clip(RoundedCornerShape(8.dp))
                                .clickable {
                                    openExternalUrl(context, targetUrl)
                                }
                                .padding(horizontal = 4.dp, vertical = 3.dp)
                        ) {
                            Text(
                                text = if (item.isJob && !item.applyUrl.isNullOrBlank()) "ऑनलाइन आवेदन लिंक" else "आधिकारिक पोर्टल",
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
                    } else {
                        Spacer(modifier = Modifier.width(1.dp))
                    }

                    // Deep Link Copy Badge
                    Row(
                        verticalAlignment = Alignment.CenterVertically,
                        modifier = Modifier
                            .clip(RoundedCornerShape(8.dp))
                            .background(Slate50)
                            .border(0.8.dp, BorderLight, RoundedCornerShape(8.dp))
                            .clickable {
                                copyDeepLinkToClipboard(context, item.deepLinkUri)
                            }
                            .padding(horizontal = 6.dp, vertical = 3.dp)
                    ) {
                        Icon(
                            imageVector = Icons.Outlined.Link,
                            contentDescription = "Copy Link",
                            tint = Slate600,
                            modifier = Modifier.size(12.dp)
                        )
                        Spacer(modifier = Modifier.width(3.dp))
                        Text(
                            text = "डीप लिंक",
                            fontSize = 10.sp,
                            color = Slate600
                        )
                    }

                    // Swipe Cue Indicator
                    if (pageIndex < totalPages - 1) {
                        Text(
                            text = "स्वाइप करें ↑",
                            fontSize = 10.5.sp,
                            fontWeight = FontWeight.SemiBold,
                            color = BrandGreen
                        )
                    } else {
                        Text(
                            text = "समाप्त ✓",
                            fontSize = 10.sp,
                            color = TextSecondary
                        )
                    }
                }
            }
        }
    }
}

/**
 * Media banner supporting remote network images via Coil with fallback to artistic gradients.
 */
@Composable
fun InshortsMediaBanner(
    item: InshortsPostItem,
    modifier: Modifier = Modifier
) {
    if (!item.imageUrl.isNullOrBlank()) {
        // Coil AsyncImage with loading and error states
        Box(
            modifier = modifier
                .clip(RoundedCornerShape(14.dp))
                .background(Slate100)
        ) {
            SubcomposeAsyncImage(
                model = item.imageUrl,
                contentDescription = item.title,
                contentScale = ContentScale.Crop,
                modifier = Modifier.fillMaxSize(),
                loading = {
                    Box(
                        modifier = Modifier
                            .fillMaxSize()
                            .background(Slate100),
                        contentAlignment = Alignment.Center
                    ) {
                        CircularProgressIndicator(
                            color = BrandGreen,
                            strokeWidth = 2.dp,
                            modifier = Modifier.size(24.dp)
                        )
                    }
                },
                error = {
                    // Fallback to thematic artwork if remote image fails
                    ThematicFallbackBanner(item = item, modifier = Modifier.fillMaxSize())
                }
            )

            // Bottom subtle gradient overlay for contrast
            Box(
                modifier = Modifier
                    .fillMaxSize()
                    .background(
                        Brush.verticalGradient(
                            colors = listOf(Color.Transparent, Color.Black.copy(alpha = 0.45f))
                        )
                    )
            )

            // Top-right Source badge
            Box(
                modifier = Modifier
                    .align(Alignment.TopEnd)
                    .padding(8.dp)
                    .clip(RoundedCornerShape(6.dp))
                    .background(Color.Black.copy(alpha = 0.6f))
                    .padding(horizontal = 8.dp, vertical = 3.dp)
            ) {
                Text(
                    text = item.source,
                    fontSize = 10.sp,
                    color = Color.White,
                    fontWeight = FontWeight.Medium
                )
            }
        }
    } else {
        ThematicFallbackBanner(item = item, modifier = modifier)
    }
}

/**
 * Thematic gradient artwork for articles and jobs without remote image URLs.
 */
@Composable
fun ThematicFallbackBanner(
    item: InshortsPostItem,
    modifier: Modifier = Modifier
) {
    val gradientColors = when {
        item.category.contains("Police", ignoreCase = true) -> listOf(Color(0xFF1E3A8A), Color(0xFF1E40AF))
        item.category.contains("Teacher", ignoreCase = true) || item.category.contains("Education", ignoreCase = true) -> listOf(Color(0xFF9A3412), Color(0xFFC2410C))
        item.category.contains("CGPSC", ignoreCase = true) -> listOf(Color(0xFF1E40AF), Color(0xFF3B82F6))
        item.category.contains("Vyapam", ignoreCase = true) -> listOf(Color(0xFF831843), Color(0xFF9D174D))
        item.category.contains("Current", ignoreCase = true) || item.category.contains("समसामयिकी", ignoreCase = true) -> listOf(Color(0xFF065F46), Color(0xFF047857))
        else -> listOf(Color(0xFF0F5132), Color(0xFF198754))
    }

    Box(
        modifier = modifier
            .clip(RoundedCornerShape(14.dp))
            .background(Brush.linearGradient(gradientColors))
            .padding(12.dp)
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
                        text = if (item.isJob) "🏛️ ${item.category} भर्ती" else "📢 ${item.category} समाचार",
                        fontSize = 10.sp,
                        fontWeight = FontWeight.Bold,
                        color = Color.White
                    )
                }

                Text(
                    text = "स्रोत: ${item.source}",
                    fontSize = 10.sp,
                    color = Color.White.copy(alpha = 0.85f)
                )
            }

            Column {
                Text(
                    text = if (item.isJob) "निशुल्क भर्ती सूचना व ऑनलाइन आवेदन" else "दैनिक समसामयिकी व परीक्षा उपयोगी तथ्य",
                    fontSize = 11.5.sp,
                    fontWeight = FontWeight.SemiBold,
                    color = Color.White
                )
                Text(
                    text = "अपडेट: ${item.publishedAt}",
                    fontSize = 9.5.sp,
                    color = Color.White.copy(alpha = 0.8f)
                )
            }
        }
    }
}

/**
 * Job-specific Key Info box.
 */
@Composable
fun JobKeyInfoBox(item: InshortsPostItem) {
    Box(
        modifier = Modifier
            .fillMaxWidth()
            .clip(RoundedCornerShape(12.dp))
            .background(Slate50)
            .border(1.dp, BorderLight, RoundedCornerShape(12.dp))
            .padding(10.dp)
    ) {
        Column(verticalArrangement = Arrangement.spacedBy(4.dp)) {
            if (!item.vacancies.isNullOrBlank()) {
                Row(verticalAlignment = Alignment.CenterVertically) {
                    Text(
                        text = "कुल पद (Vacancies):",
                        fontSize = 11.5.sp,
                        fontWeight = FontWeight.Bold,
                        color = TextPrimary
                    )
                    Spacer(modifier = Modifier.width(6.dp))
                    Text(
                        text = item.vacancies,
                        fontSize = 11.5.sp,
                        fontWeight = FontWeight.SemiBold,
                        color = BrandGreen
                    )
                }
            }

            if (!item.eligibility.isNullOrBlank()) {
                Row(verticalAlignment = Alignment.Top) {
                    Text(
                        text = "योग्यता (Eligibility):",
                        fontSize = 11.5.sp,
                        fontWeight = FontWeight.Bold,
                        color = TextPrimary
                    )
                    Spacer(modifier = Modifier.width(6.dp))
                    Text(
                        text = item.eligibility,
                        fontSize = 11.5.sp,
                        color = TextSecondary,
                        maxLines = 2,
                        overflow = TextOverflow.Ellipsis
                    )
                }
            }

            if (!item.lastDate.isNullOrBlank()) {
                Row(verticalAlignment = Alignment.CenterVertically) {
                    Text(
                        text = "अंतिम तिथि (Last Date):",
                        fontSize = 11.5.sp,
                        fontWeight = FontWeight.Bold,
                        color = BreakingRed
                    )
                    Spacer(modifier = Modifier.width(6.dp))
                    Text(
                        text = item.lastDate,
                        fontSize = 11.5.sp,
                        fontWeight = FontWeight.Bold,
                        color = BreakingRed
                    )
                }
            }
        }
    }
}

/**
 * News / Current Affairs Exam Takeaways Box.
 */
@Composable
fun ExamTakeawayBox(item: InshortsPostItem) {
    Box(
        modifier = Modifier
            .fillMaxWidth()
            .clip(RoundedCornerShape(12.dp))
            .background(BrandGreenLight.copy(alpha = 0.5f))
            .border(1.dp, BrandGreen.copy(alpha = 0.25f), RoundedCornerShape(12.dp))
            .padding(10.dp)
    ) {
        Column {
            Row(verticalAlignment = Alignment.CenterVertically) {
                Icon(
                    imageVector = Icons.Outlined.School,
                    contentDescription = null,
                    tint = BrandGreen,
                    modifier = Modifier.size(15.dp)
                )
                Spacer(modifier = Modifier.width(5.dp))
                Text(
                    text = "परीक्षा उपयोगी तथ्य (Key Takeaway):",
                    fontSize = 11.5.sp,
                    fontWeight = FontWeight.Bold,
                    color = BrandGreenDark
                )
            }
            Spacer(modifier = Modifier.height(3.dp))
            Text(
                text = item.examTakeaway ?: "CGPSC, व्यापम व राज्य स्तरीय प्रतियोगी परीक्षाओं हेतु महत्वपूर्ण।",
                fontSize = 11.sp,
                color = TextPrimary,
                lineHeight = 16.sp
            )
        }
    }
}

/**
 * Vertical Inshorts Feed Container managing VerticalPager, page counts, quick scrollers, and gestures.
 */
@Composable
fun InshortsVerticalFeed(
    items: List<InshortsPostItem>,
    onArticleClick: (InshortsPostItem) -> Unit,
    onToggleSave: (String) -> Unit,
    onDeepLinkClick: ((String, InshortsPostItem) -> Unit)? = null,
    modifier: Modifier = Modifier
) {
    val coroutineScope = rememberCoroutineScope()
    val pagerState = rememberPagerState(pageCount = { items.size })

    Box(modifier = modifier.fillMaxSize()) {
        VerticalPager(
            state = pagerState,
            modifier = Modifier
                .fillMaxSize()
                .testTag("inshorts_vertical_feed_pager")
        ) { page ->
            val item = items[page]
            InshortsSwipeableCard(
                item = item,
                pageIndex = page,
                totalPages = items.size,
                onArticleClick = { onArticleClick(item) },
                onToggleSave = { onToggleSave(item.id) },
                onDeepLinkClick = { uri ->
                    if (onDeepLinkClick != null) {
                        onDeepLinkClick(uri, item)
                    } else {
                        onArticleClick(item)
                    }
                },
                modifier = Modifier.fillMaxSize()
            )
        }

        // Quick Jump Up/Down floating pill controls
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
                    color = MaterialTheme.colorScheme.surface.copy(alpha = 0.92f),
                    shadowElevation = 3.dp,
                    shape = CircleShape
                ) {
                    Box(contentAlignment = Alignment.Center) {
                        Icon(
                            imageVector = Icons.Default.KeyboardArrowUp,
                            contentDescription = "Previous Article",
                            tint = TextPrimary,
                            modifier = Modifier.size(22.dp)
                        )
                    }
                }
            }

            if (pagerState.currentPage < items.size - 1) {
                Surface(
                    modifier = Modifier
                        .size(36.dp)
                        .clip(CircleShape)
                        .clickable {
                            coroutineScope.launch {
                                pagerState.animateScrollToPage(pagerState.currentPage + 1)
                            }
                        },
                    color = MaterialTheme.colorScheme.surface.copy(alpha = 0.92f),
                    shadowElevation = 3.dp,
                    shape = CircleShape
                ) {
                    Box(contentAlignment = Alignment.Center) {
                        Icon(
                            imageVector = Icons.Default.KeyboardArrowDown,
                            contentDescription = "Next Article",
                            tint = TextPrimary,
                            modifier = Modifier.size(22.dp)
                        )
                    }
                }
            }
        }
    }
}

// ==========================================
// DEEP LINKING & SYSTEM INTENT UTILITIES
// ==========================================

fun openExternalUrl(context: Context, url: String) {
    try {
        val intent = Intent(Intent.ACTION_VIEW, Uri.parse(url)).apply {
            addFlags(Intent.FLAG_ACTIVITY_NEW_TASK)
        }
        context.startActivity(intent)
    } catch (e: Exception) {
        Toast.makeText(context, "लिंक खोलने में असमर्थ", Toast.LENGTH_SHORT).show()
    }
}

fun copyDeepLinkToClipboard(context: Context, deepLink: String) {
    val clipboard = context.getSystemService(Context.CLIPBOARD_SERVICE) as ClipboardManager
    val clip = ClipData.newPlainText("CGJobs Deep Link", deepLink)
    clipboard.setPrimaryClip(clip)
    Toast.makeText(context, "डीप-लिंक कॉपी हो गया: $deepLink", Toast.LENGTH_SHORT).show()
}

fun shareDeepLink(context: Context, item: InshortsPostItem) {
    val shareText = buildString {
        append("📢 ${item.title}\n\n")
        append("${item.summary}\n\n")
        if (item.isJob && !item.lastDate.isNullOrBlank()) {
            append("🗓️ अंतिम तिथि: ${item.lastDate}\n")
        }
        if (item.isJob && !item.vacancies.isNullOrBlank()) {
            append("👥 कुल पद: ${item.vacancies}\n")
        }
        append("\n🔗 ऐप में खोलें (Deep Link): ${item.deepLinkUri}\n")
        val webUrl = item.applyUrl ?: item.officialNotificationUrl ?: item.sourceUrl
        if (!webUrl.isNullOrBlank()) {
            append("🌐 आधिकारिक वेब लिंक: $webUrl\n")
        }
        append("\n📲 डाउनलोड CG Rojgar Samachar App")
    }

    val intent = Intent(Intent.ACTION_SEND).apply {
        type = "text/plain"
        putExtra(Intent.EXTRA_SUBJECT, item.title)
        putExtra(Intent.EXTRA_TEXT, shareText)
    }
    context.startActivity(Intent.createChooser(intent, "शेयर करें (Share)"))
}
