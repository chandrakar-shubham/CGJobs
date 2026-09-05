package com.example.ui.screens

import android.content.Context
import android.content.Intent
import android.net.Uri
import android.widget.Toast
import androidx.compose.foundation.BorderStroke
import androidx.compose.foundation.background
import androidx.compose.foundation.border
import androidx.compose.foundation.clickable
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.layout.size
import androidx.compose.foundation.layout.width
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.shape.CircleShape
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.foundation.verticalScroll
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.automirrored.filled.ArrowBack
import androidx.compose.material.icons.filled.Bookmark
import androidx.compose.material.icons.outlined.BookmarkBorder
import androidx.compose.material.icons.outlined.CheckCircle
import androidx.compose.material.icons.outlined.DateRange
import androidx.compose.material.icons.outlined.Description
import androidx.compose.material.icons.outlined.Language
import androidx.compose.material.icons.outlined.Link
import androidx.compose.material.icons.outlined.OpenInNew
import androidx.compose.material.icons.outlined.People
import androidx.compose.material.icons.outlined.School
import androidx.compose.material.icons.outlined.Share
import androidx.compose.material.icons.outlined.Timer
import androidx.compose.material3.Button
import androidx.compose.material3.ButtonDefaults
import androidx.compose.material3.Card
import androidx.compose.material3.CardDefaults
import androidx.compose.material3.HorizontalDivider
import androidx.compose.material3.Icon
import androidx.compose.material3.IconButton
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.OutlinedButton
import androidx.compose.material3.Scaffold
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.alpha
import androidx.compose.ui.draw.clip
import androidx.compose.ui.graphics.Brush
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.graphics.vector.ImageVector
import androidx.compose.ui.platform.LocalContext
import androidx.compose.ui.platform.testTag
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import com.example.model.JobUpdate
import com.example.ui.components.getCategoryTheme
import com.example.ui.components.shareJobUpdate
import com.example.ui.theme.BorderLight
import com.example.ui.theme.BrandGreen
import com.example.ui.theme.BrandGreenDark
import com.example.ui.theme.DividerLight

@Composable
fun ArticleDetailScreen(
    article: JobUpdate,
    onBack: () -> Unit,
    onToggleSave: () -> Unit,
    modifier: Modifier = Modifier
) {
    val context = LocalContext.current
    val categoryTheme = getCategoryTheme(article.category)
    val scrollState = rememberScrollState()

    Scaffold(
        modifier = modifier.testTag("article_detail_screen"),
        topBar = {
            Row(
                modifier = Modifier
                    .fillMaxWidth()
                    .background(MaterialTheme.colorScheme.surface)
                    .padding(horizontal = 14.dp, vertical = 10.dp),
                verticalAlignment = Alignment.CenterVertically
            ) {
                Box(
                    modifier = Modifier
                        .size(44.dp)
                        .clip(CircleShape)
                        .background(com.example.ui.theme.Slate50)
                        .border(1.dp, BorderLight.copy(alpha = 0.5f), CircleShape),
                    contentAlignment = Alignment.Center
                ) {
                    IconButton(
                        onClick = onBack,
                        modifier = Modifier.testTag("article_back_button")
                    ) {
                        Icon(
                            imageVector = Icons.AutoMirrored.Filled.ArrowBack,
                            contentDescription = "Back",
                            tint = com.example.ui.theme.TextPrimary,
                            modifier = Modifier.size(22.dp)
                        )
                    }
                }

                Spacer(modifier = Modifier.weight(1f))

                // WhatsApp Share Pill
                Box(
                    modifier = Modifier
                        .clip(RoundedCornerShape(16.dp))
                        .background(Color(0xFF25D366))
                        .clickable { shareToWhatsApp(context, article) }
                        .padding(horizontal = 12.dp, vertical = 6.dp),
                    contentAlignment = Alignment.Center
                ) {
                    Row(verticalAlignment = Alignment.CenterVertically) {
                        Text(
                            text = "WhatsApp",
                            fontSize = 11.5.sp,
                            fontWeight = FontWeight.Bold,
                            color = Color.White
                        )
                    }
                }

                Spacer(modifier = Modifier.width(8.dp))

                // General Share
                Box(
                    modifier = Modifier
                        .size(44.dp)
                        .clip(CircleShape)
                        .background(com.example.ui.theme.Slate50)
                        .border(1.dp, BorderLight.copy(alpha = 0.5f), CircleShape),
                    contentAlignment = Alignment.Center
                ) {
                    IconButton(
                        onClick = { shareJobUpdate(context, article) },
                        modifier = Modifier.testTag("article_share_button")
                    ) {
                        Icon(
                            imageVector = Icons.Outlined.Share,
                            contentDescription = "Share",
                            tint = com.example.ui.theme.Slate600,
                            modifier = Modifier.size(20.dp)
                        )
                    }
                }

                Spacer(modifier = Modifier.width(8.dp))

                // Bookmark Toggle
                Box(
                    modifier = Modifier
                        .size(44.dp)
                        .clip(CircleShape)
                        .background(com.example.ui.theme.Slate50)
                        .border(1.dp, BorderLight.copy(alpha = 0.5f), CircleShape),
                    contentAlignment = Alignment.Center
                ) {
                    IconButton(
                        onClick = onToggleSave,
                        modifier = Modifier.testTag("article_bookmark_button")
                    ) {
                        Icon(
                            imageVector = if (article.isSaved) Icons.Filled.Bookmark else Icons.Outlined.BookmarkBorder,
                            contentDescription = if (article.isSaved) "Saved" else "Save",
                            tint = if (article.isSaved) BrandGreen else com.example.ui.theme.Slate600,
                            modifier = Modifier.size(20.dp)
                        )
                    }
                }
            }
        }
    ) { innerPadding ->
        Column(
            modifier = Modifier
                .fillMaxSize()
                .padding(innerPadding)
                .background(MaterialTheme.colorScheme.background)
                .verticalScroll(scrollState)
        ) {
            // Geometric Hero Image Area: #E8F5E9 with watermark
            Box(
                modifier = Modifier
                    .fillMaxWidth()
                    .height(180.dp)
                    .background(com.example.ui.theme.BrandGreenLight),
                contentAlignment = Alignment.Center
            ) {
                // Large geometric watermark icon (20% opacity)
                Box(
                    modifier = Modifier
                        .align(Alignment.Center)
                        .alpha(0.18f),
                    contentAlignment = Alignment.Center
                ) {
                    Icon(
                        imageVector = categoryTheme.icon,
                        contentDescription = null,
                        tint = BrandGreen,
                        modifier = Modifier.size(120.dp)
                    )
                }

                // Floating Badges at top left
                Row(
                    modifier = Modifier
                        .align(Alignment.TopStart)
                        .padding(16.dp),
                    horizontalArrangement = Arrangement.spacedBy(8.dp),
                    verticalAlignment = Alignment.CenterVertically
                ) {
                    Box(
                        modifier = Modifier
                            .clip(RoundedCornerShape(6.dp))
                            .background(BrandGreen)
                            .padding(horizontal = 12.dp, vertical = 5.dp)
                    ) {
                        Text(
                            text = article.category.uppercase(),
                            color = Color.White,
                            fontSize = 11.sp,
                            fontWeight = FontWeight.Bold,
                            letterSpacing = 0.5.sp
                        )
                    }

                    if (article.isBreaking) {
                        Box(
                            modifier = Modifier
                                .clip(RoundedCornerShape(6.dp))
                                .background(com.example.ui.theme.BadgeOrange)
                                .padding(horizontal = 10.dp, vertical = 5.dp)
                        ) {
                            Text(
                                text = "NEW",
                                color = Color.White,
                                fontSize = 11.sp,
                                fontWeight = FontWeight.Bold,
                                letterSpacing = 0.5.sp
                            )
                        }
                    }
                }

                // Source indicator badge in center bottom
                Box(
                    modifier = Modifier
                        .align(Alignment.BottomStart)
                        .padding(16.dp)
                        .clip(RoundedCornerShape(8.dp))
                        .background(Color.White.copy(alpha = 0.9f))
                        .padding(horizontal = 12.dp, vertical = 5.dp)
                ) {
                    Text(
                        text = "आधिकारिक स्रोत: ${article.source}",
                        fontSize = 11.5.sp,
                        fontWeight = FontWeight.Bold,
                        color = BrandGreen
                    )
                }
            }

            Column(
                modifier = Modifier
                    .fillMaxWidth()
                    .padding(20.dp)
            ) {
                // Headline
                Text(
                    text = article.title,
                    fontSize = 21.sp,
                    lineHeight = 29.sp,
                    fontWeight = FontWeight.Bold,
                    color = MaterialTheme.colorScheme.onSurface
                )

                Spacer(modifier = Modifier.height(8.dp))

                // Metadata Date & Source
                Text(
                    text = "${article.publishedAt} • By CGJobs Team",
                    fontSize = 12.5.sp,
                    color = MaterialTheme.colorScheme.onSurfaceVariant,
                    fontWeight = FontWeight.Medium
                )

                Spacer(modifier = Modifier.height(16.dp))

                // Short summary callout
                Box(
                    modifier = Modifier
                        .fillMaxWidth()
                        .clip(RoundedCornerShape(12.dp))
                        .background(categoryTheme.backgroundColor.copy(alpha = 0.7f))
                        .padding(14.dp)
                ) {
                    Text(
                        text = article.summary,
                        fontSize = 14.sp,
                        lineHeight = 21.sp,
                        color = MaterialTheme.colorScheme.onSurface,
                        fontWeight = FontWeight.Medium
                    )
                }

                Spacer(modifier = Modifier.height(20.dp))

                // "मुख्य जानकारी" (Key Information) Section Table
                Text(
                    text = "मुख्य जानकारी",
                    fontSize = 18.sp,
                    fontWeight = FontWeight.Bold,
                    color = MaterialTheme.colorScheme.onSurface
                )

                Spacer(modifier = Modifier.height(10.dp))

                Card(
                    modifier = Modifier.fillMaxWidth(),
                    shape = RoundedCornerShape(14.dp),
                    colors = CardDefaults.cardColors(containerColor = MaterialTheme.colorScheme.surface),
                    border = BorderStroke(1.dp, BorderLight)
                ) {
                    Column(modifier = Modifier.padding(16.dp)) {
                        DetailInfoRow(
                            icon = Icons.Outlined.People,
                            label = "पदों की संख्या",
                            value = article.vacancies ?: "विभिन्न पद"
                        )
                        HorizontalDivider(modifier = Modifier.padding(vertical = 10.dp), color = DividerLight)

                        DetailInfoRow(
                            icon = Icons.Outlined.DateRange,
                            label = "आवेदन प्रारंभ",
                            value = article.importantDates.applicationStart
                        )
                        HorizontalDivider(modifier = Modifier.padding(vertical = 10.dp), color = DividerLight)

                        DetailInfoRow(
                            icon = Icons.Outlined.Timer,
                            label = "अंतिम तिथि",
                            value = article.importantDates.lastDate
                        )

                        if (article.importantDates.examDate != null) {
                            HorizontalDivider(modifier = Modifier.padding(vertical = 10.dp), color = DividerLight)
                            DetailInfoRow(
                                icon = Icons.Outlined.CheckCircle,
                                label = "परीक्षा तिथि",
                                value = article.importantDates.examDate
                            )
                        }

                        if (article.eligibility != null) {
                            HorizontalDivider(modifier = Modifier.padding(vertical = 10.dp), color = DividerLight)
                            DetailInfoRow(
                                icon = Icons.Outlined.School,
                                label = "योग्यता",
                                value = article.eligibility
                            )
                        }

                        if (article.ageLimit != null) {
                            HorizontalDivider(modifier = Modifier.padding(vertical = 10.dp), color = DividerLight)
                            DetailInfoRow(
                                icon = Icons.Outlined.DateRange,
                                label = "आयु सीमा",
                                value = article.ageLimit
                            )
                        }

                        if (article.selectionProcess != null) {
                            HorizontalDivider(modifier = Modifier.padding(vertical = 10.dp), color = DividerLight)
                            DetailInfoRow(
                                icon = Icons.Outlined.CheckCircle,
                                label = "चयन प्रक्रिया",
                                value = article.selectionProcess
                            )
                        }
                    }
                }

                Spacer(modifier = Modifier.height(20.dp))

                // Detailed Article Content
                Text(
                    text = "विस्तृत विवरण",
                    fontSize = 17.sp,
                    fontWeight = FontWeight.Bold,
                    color = MaterialTheme.colorScheme.onSurface
                )

                Spacer(modifier = Modifier.height(10.dp))

                Text(
                    text = article.detailedContent,
                    fontSize = 14.5.sp,
                    lineHeight = 23.sp,
                    color = MaterialTheme.colorScheme.onSurfaceVariant
                )

                Spacer(modifier = Modifier.height(28.dp))

                // Call to Action Buttons
                Button(
                    onClick = {
                        if (article.officialNotificationUrl != null) {
                            val intent = Intent(Intent.ACTION_VIEW, Uri.parse(article.officialNotificationUrl))
                            runCatching { context.startActivity(intent) }.onFailure {
                                Toast.makeText(context, "आधिकारिक अधिसूचना लिंक खोला जा रहा है...", Toast.LENGTH_SHORT).show()
                            }
                        } else {
                            Toast.makeText(context, "आधिकारिक सूचना शीघ्र उपलब्ध होगी", Toast.LENGTH_SHORT).show()
                        }
                    },
                    modifier = Modifier
                        .fillMaxWidth()
                        .height(48.dp)
                        .testTag("official_notification_cta"),
                    colors = ButtonDefaults.buttonColors(containerColor = BrandGreen),
                    shape = RoundedCornerShape(12.dp)
                ) {
                    Icon(
                        imageVector = Icons.Outlined.Description,
                        contentDescription = "Notification",
                        tint = Color.White
                    )
                    Spacer(modifier = Modifier.width(8.dp))
                    Text(
                        text = "Official Notification",
                        fontSize = 14.5.sp,
                        fontWeight = FontWeight.Bold,
                        color = Color.White
                    )
                }

                Spacer(modifier = Modifier.height(12.dp))

                OutlinedButton(
                    onClick = {
                        if (article.applyUrl != null) {
                            val intent = Intent(Intent.ACTION_VIEW, Uri.parse(article.applyUrl))
                            runCatching { context.startActivity(intent) }.onFailure {
                                Toast.makeText(context, "आवेदन पोर्टल खोला जा रहा है...", Toast.LENGTH_SHORT).show()
                            }
                        } else {
                            Toast.makeText(context, "ऑनलाइन आवेदन तिथि शीघ्र घोषित होगी", Toast.LENGTH_SHORT).show()
                        }
                    },
                    modifier = Modifier
                        .fillMaxWidth()
                        .height(48.dp)
                        .testTag("apply_online_cta"),
                    shape = RoundedCornerShape(12.dp),
                    border = BorderStroke(1.dp, BorderLight)
                ) {
                    Icon(
                        imageVector = Icons.Outlined.Link,
                        contentDescription = "Apply",
                        tint = BrandGreen
                    )
                    Spacer(modifier = Modifier.width(8.dp))
                    Text(
                        text = if (article.applyUrl != null) "Apply Online (आधिकारिक पोर्टल)" else "Apply Online (Soon)",
                        fontSize = 14.5.sp,
                        fontWeight = FontWeight.Medium,
                        color = MaterialTheme.colorScheme.onSurface
                    )
                }

                Spacer(modifier = Modifier.height(40.dp))
            }
        }
    }
}

@Composable
fun DetailInfoRow(
    icon: ImageVector,
    label: String,
    value: String
) {
    Row(
        modifier = Modifier.fillMaxWidth(),
        verticalAlignment = Alignment.Top
    ) {
        Icon(
            imageVector = icon,
            contentDescription = label,
            tint = BrandGreen,
            modifier = Modifier
                .size(20.dp)
                .padding(top = 2.dp)
        )
        Spacer(modifier = Modifier.width(10.dp))
        Column(modifier = Modifier.weight(1f)) {
            Text(
                text = label,
                fontSize = 12.sp,
                color = MaterialTheme.colorScheme.onSurfaceVariant
            )
            Text(
                text = value,
                fontSize = 14.sp,
                fontWeight = FontWeight.SemiBold,
                color = MaterialTheme.colorScheme.onSurface
            )
        }
    }
}

private fun shareToWhatsApp(context: Context, article: JobUpdate) {
    val message = """
📢 *${article.title}*

${article.summary}

📋 पदों की संख्या: ${article.vacancies ?: "N/A"}
📅 अंतिम तिथि: ${article.importantDates.lastDate}

📱 CGJobs ऐप - हर सरकारी नौकरी की सही जानकारी।
    """.trimIndent()

    val intent = Intent(Intent.ACTION_SEND).apply {
        type = "text/plain"
        `package` = "com.whatsapp"
        putExtra(Intent.EXTRA_TEXT, message)
    }
    runCatching { context.startActivity(intent) }.onFailure {
        // Fallback to normal share if WhatsApp isn't directly targetable
        val fallback = Intent(Intent.ACTION_SEND).apply {
            type = "text/plain"
            putExtra(Intent.EXTRA_TEXT, message)
        }
        context.startActivity(Intent.createChooser(fallback, "Share"))
    }
}
