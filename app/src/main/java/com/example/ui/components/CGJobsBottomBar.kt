package com.example.ui.components

import androidx.compose.foundation.border
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.size
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.automirrored.filled.Article
import androidx.compose.material.icons.automirrored.outlined.Article
import androidx.compose.material.icons.filled.Bookmark
import androidx.compose.material.icons.filled.Home
import androidx.compose.material.icons.filled.MenuBook
import androidx.compose.material.icons.filled.Person
import androidx.compose.material.icons.filled.Search
import androidx.compose.material.icons.outlined.BookmarkBorder
import androidx.compose.material.icons.outlined.Home
import androidx.compose.material.icons.outlined.MenuBook
import androidx.compose.material.icons.outlined.Person
import androidx.compose.material.icons.outlined.Search
import androidx.compose.material3.Icon
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.NavigationBar
import androidx.compose.material3.NavigationBarItem
import androidx.compose.material3.NavigationBarItemDefaults
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.ui.Modifier
import androidx.compose.ui.platform.testTag
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import com.example.ui.theme.BorderLight
import com.example.ui.theme.BrandGreen
import com.example.ui.theme.BrandGreenLight
import com.example.ui.theme.Slate600
import com.example.ui.viewmodel.ScreenTab

@Composable
fun CGJobsBottomBar(
    currentTab: ScreenTab,
    onTabSelected: (ScreenTab) -> Unit,
    modifier: Modifier = Modifier,
    unreadAlertsCount: Int = 0
) {
    NavigationBar(
        modifier = modifier
            .fillMaxWidth()
            .height(78.dp)
            .border(width = 1.dp, color = BorderLight)
            .testTag("cgjobs_bottom_nav_bar"),
        containerColor = MaterialTheme.colorScheme.surface,
        tonalElevation = 0.dp
    ) {
        val navItemColors = NavigationBarItemDefaults.colors(
            selectedIconColor = BrandGreen,
            selectedTextColor = BrandGreen,
            unselectedIconColor = Slate600.copy(alpha = 0.5f),
            unselectedTextColor = Slate600.copy(alpha = 0.6f),
            indicatorColor = BrandGreenLight
        )

        // 1. Jobs (Home - Inshorts Swipe format)
        NavigationBarItem(
            selected = currentTab == ScreenTab.HOME,
            onClick = { onTabSelected(ScreenTab.HOME) },
            icon = {
                Icon(
                    imageVector = if (currentTab == ScreenTab.HOME) Icons.Filled.Home else Icons.Outlined.Home,
                    contentDescription = "Jobs",
                    modifier = Modifier.size(24.dp)
                )
            },
            label = {
                Text(
                    text = "Jobs",
                    fontSize = 10.sp,
                    fontWeight = FontWeight.Bold
                )
            },
            colors = navItemColors,
            modifier = Modifier.testTag("nav_tab_home")
        )

        // 2. Current Affairs (Inshorts News format)
        NavigationBarItem(
            selected = currentTab == ScreenTab.CURRENT_AFFAIRS,
            onClick = { onTabSelected(ScreenTab.CURRENT_AFFAIRS) },
            icon = {
                Icon(
                    imageVector = if (currentTab == ScreenTab.CURRENT_AFFAIRS) Icons.AutoMirrored.Filled.Article else Icons.AutoMirrored.Outlined.Article,
                    contentDescription = "Current Affairs",
                    modifier = Modifier.size(24.dp)
                )
            },
            label = {
                Text(
                    text = "Affairs",
                    fontSize = 10.sp,
                    fontWeight = FontWeight.Bold
                )
            },
            colors = navItemColors,
            modifier = Modifier.testTag("nav_tab_current_affairs")
        )

        // 3. Static GK (Inshorts Facts format)
        NavigationBarItem(
            selected = currentTab == ScreenTab.STATIC_GK,
            onClick = { onTabSelected(ScreenTab.STATIC_GK) },
            icon = {
                Icon(
                    imageVector = if (currentTab == ScreenTab.STATIC_GK) Icons.Filled.MenuBook else Icons.Outlined.MenuBook,
                    contentDescription = "Static GK",
                    modifier = Modifier.size(24.dp)
                )
            },
            label = {
                Text(
                    text = "Static GK",
                    fontSize = 10.sp,
                    fontWeight = FontWeight.Bold
                )
            },
            colors = navItemColors,
            modifier = Modifier.testTag("nav_tab_static_gk")
        )

        // 4. Explore
        NavigationBarItem(
            selected = currentTab == ScreenTab.EXPLORE,
            onClick = { onTabSelected(ScreenTab.EXPLORE) },
            icon = {
                Icon(
                    imageVector = if (currentTab == ScreenTab.EXPLORE) Icons.Filled.Search else Icons.Outlined.Search,
                    contentDescription = "Explore",
                    modifier = Modifier.size(24.dp)
                )
            },
            label = {
                Text(
                    text = "Explore",
                    fontSize = 10.sp,
                    fontWeight = FontWeight.Bold
                )
            },
            colors = navItemColors,
            modifier = Modifier.testTag("nav_tab_explore")
        )

        // 5. Saved
        NavigationBarItem(
            selected = currentTab == ScreenTab.SAVED,
            onClick = { onTabSelected(ScreenTab.SAVED) },
            icon = {
                Icon(
                    imageVector = if (currentTab == ScreenTab.SAVED) Icons.Filled.Bookmark else Icons.Outlined.BookmarkBorder,
                    contentDescription = "Saved",
                    modifier = Modifier.size(24.dp)
                )
            },
            label = {
                Text(
                    text = "Saved",
                    fontSize = 10.sp,
                    fontWeight = FontWeight.Bold
                )
            },
            colors = navItemColors,
            modifier = Modifier.testTag("nav_tab_saved")
        )
    }
}
