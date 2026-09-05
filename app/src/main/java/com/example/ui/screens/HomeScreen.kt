package com.example.ui.screens

import androidx.compose.foundation.background
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
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.outlined.SearchOff
import androidx.compose.material3.Button
import androidx.compose.material3.ButtonDefaults
import androidx.compose.material3.CircularProgressIndicator
import androidx.compose.material3.Icon
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.clip
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.platform.testTag
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.style.TextAlign
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import com.example.model.JobUpdate
import com.example.ui.components.CategorySelector
import com.example.ui.components.JobNewsCard
import com.example.ui.components.SecondaryJobCard
import com.example.ui.theme.BrandGreen
import com.example.ui.theme.Slate600
import com.example.ui.theme.TextPrimary
import com.example.ui.theme.TextSecondary

@Composable
fun HomeScreen(
    newsList: List<JobUpdate>,
    selectedCategory: String,
    isRefreshing: Boolean,
    onCategorySelected: (String) -> Unit,
    onArticleClick: (JobUpdate) -> Unit,
    onToggleSave: (String) -> Unit,
    onRefresh: () -> Unit,
    modifier: Modifier = Modifier
) {
    Column(
        modifier = modifier
            .fillMaxSize()
            .background(MaterialTheme.colorScheme.background)
            .testTag("home_screen_root")
    ) {
        // Category Selector Chips
        CategorySelector(
            selectedCategory = selectedCategory,
            onCategorySelected = onCategorySelected,
            modifier = Modifier.fillMaxWidth()
        )

        Spacer(modifier = Modifier.height(4.dp))

        if (isRefreshing) {
            Box(
                modifier = Modifier
                    .fillMaxWidth()
                    .padding(8.dp),
                contentAlignment = Alignment.Center
            ) {
                CircularProgressIndicator(
                    modifier = Modifier.size(24.dp),
                    color = BrandGreen,
                    strokeWidth = 2.5.dp
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
        } else {
            // Feed List
            LazyColumn(
                modifier = Modifier
                    .fillMaxSize()
                    .testTag("home_feed_lazy_column"),
                contentPadding = PaddingValues(start = 16.dp, end = 16.dp, top = 4.dp, bottom = 84.dp),
                verticalArrangement = Arrangement.spacedBy(14.dp)
            ) {
                // The first item (or breaking item) is displayed as the full featured Inshorts-style Card
                item(key = "featured_${newsList.first().id}") {
                    JobNewsCard(
                        job = newsList.first(),
                        onReadMore = { onArticleClick(newsList.first()) },
                        onOfficialNotification = { onArticleClick(newsList.first()) },
                        onToggleSave = { onToggleSave(newsList.first().id) }
                    )
                }

                // Subsequent items
                itemsIndexed(
                    items = newsList.drop(1),
                    key = { _, item -> item.id }
                ) { index, item ->
                    if (index % 3 == 0 && item.isBreaking) {
                        // Periodic featured breaking card
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
