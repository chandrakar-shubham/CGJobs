package com.example.ui

import androidx.activity.compose.BackHandler
import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.padding
import androidx.compose.material3.DrawerValue
import androidx.compose.material3.ModalNavigationDrawer
import androidx.compose.material3.Scaffold
import androidx.compose.material3.rememberDrawerState
import androidx.compose.runtime.Composable
import androidx.compose.runtime.collectAsState
import androidx.compose.runtime.getValue
import androidx.compose.runtime.rememberCoroutineScope
import androidx.compose.ui.Modifier
import androidx.lifecycle.viewmodel.compose.viewModel
import com.example.ui.components.AboutDialog
import com.example.ui.components.AppDrawerContent
import com.example.ui.components.CGJobsBottomBar
import com.example.ui.components.CGJobsTopBar
import com.example.ui.components.EditProfileDialog
import com.example.ui.components.InterestsDialog
import com.example.ui.components.ServerSettingsDialog
import com.example.ui.screens.AlertsScreen
import com.example.ui.screens.ArticleDetailScreen
import com.example.ui.screens.CurrentAffairsScreen
import com.example.ui.screens.ExploreScreen
import com.example.ui.screens.HomeScreen
import com.example.ui.screens.ProfileScreen
import com.example.ui.screens.SavedScreen
import com.example.ui.screens.SearchScreen
import com.example.ui.screens.SplashScreen
import com.example.ui.screens.StaticGkScreen
import com.example.ui.viewmodel.CGJobsViewModel
import com.example.ui.viewmodel.ScreenTab
import kotlinx.coroutines.launch

@Composable
fun CGJobsApp(
    viewModel: CGJobsViewModel = viewModel(),
    modifier: Modifier = Modifier
) {
    val isSplashVisible by viewModel.isSplashVisible.collectAsState()
    val selectedArticle by viewModel.selectedArticle.collectAsState()
    val isSearchActive by viewModel.isSearchActive.collectAsState()
    val searchQuery by viewModel.searchQuery.collectAsState()
    val searchResults by viewModel.searchResults.collectAsState()

    val currentTab by viewModel.currentTab.collectAsState()
    val newsList by viewModel.newsList.collectAsState()
    val currentAffairsList by viewModel.currentAffairsList.collectAsState()
    val staticGkList by viewModel.staticGkList.collectAsState()
    val savedNews by viewModel.savedNews.collectAsState()
    val alertsList by viewModel.alertsList.collectAsState()
    val unreadAlertsCount by viewModel.unreadAlertsCount.collectAsState()
    val selectedCategory by viewModel.selectedCategory.collectAsState()
    val selectedAlertFilter by viewModel.selectedAlertFilter.collectAsState()
    val isRefreshing by viewModel.isRefreshing.collectAsState()
    val userProfile by viewModel.userProfile.collectAsState()

    val sections by viewModel.sections.collectAsState()
    val jobsCategories by viewModel.jobsCategories.collectAsState()
    val newsCategories by viewModel.newsCategories.collectAsState()
    val staticGkCategories by viewModel.staticGkCategories.collectAsState()

    val showInterestsDialog by viewModel.showInterestsDialog.collectAsState()
    val showEditProfileDialog by viewModel.showEditProfileDialog.collectAsState()
    val showAboutDialog by viewModel.showAboutDialog.collectAsState()
    val showServerSettingsDialog by viewModel.showServerSettingsDialog.collectAsState()

    val drawerState = rememberDrawerState(initialValue = DrawerValue.Closed)
    val coroutineScope = rememberCoroutineScope()

    // Splash Screen takes precedence
    if (isSplashVisible) {
        SplashScreen(
            onDismiss = { viewModel.dismissSplash() }
        )
        return
    }

    // Detail Screen takes precedence over main tabs
    if (selectedArticle != null) {
        BackHandler { viewModel.closeArticle() }
        ArticleDetailScreen(
            article = selectedArticle!!,
            onBack = { viewModel.closeArticle() },
            onToggleSave = { viewModel.toggleSave(selectedArticle!!.id) }
        )
        return
    }

    // Search Screen
    if (isSearchActive) {
        BackHandler { viewModel.closeSearch() }
        SearchScreen(
            query = searchQuery,
            searchResults = searchResults,
            onQueryChange = { viewModel.setSearchQuery(it) },
            onBack = { viewModel.closeSearch() },
            onArticleClick = { viewModel.openArticle(it) },
            onToggleSave = { viewModel.toggleSave(it) }
        )
        return
    }

    // Alerts Back Navigation to Home
    if (currentTab == ScreenTab.ALERTS) {
        BackHandler { viewModel.selectTab(ScreenTab.HOME) }
    }

    // Drawer Back Handling
    BackHandler(enabled = drawerState.isOpen) {
        coroutineScope.launch { drawerState.close() }
    }

    // Root Navigation Drawer
    ModalNavigationDrawer(
        drawerState = drawerState,
        drawerContent = {
            AppDrawerContent(
                isDarkMode = userProfile.isDarkMode,
                onNavigateHome = {
                    viewModel.selectTab(ScreenTab.HOME)
                },
                onNavigateExplore = {
                    viewModel.selectTab(ScreenTab.EXPLORE)
                },
                onNavigateCurrentAffairs = {
                    viewModel.selectTab(ScreenTab.CURRENT_AFFAIRS)
                },
                onNavigateStaticGk = {
                    viewModel.selectTab(ScreenTab.STATIC_GK)
                },
                onCategorySelected = { category ->
                    viewModel.selectCategory(category)
                    viewModel.selectTab(ScreenTab.HOME)
                },
                onToggleDarkMode = { enabled ->
                    viewModel.toggleDarkMode(enabled)
                },
                onOpenServerSettings = {
                    viewModel.setServerSettingsDialogVisible(true)
                },
                onCloseDrawer = {
                    coroutineScope.launch { drawerState.close() }
                }
            )
        }
    ) {
        Scaffold(
            modifier = modifier.fillMaxSize(),
            topBar = {
                CGJobsTopBar(
                    isDarkMode = userProfile.isDarkMode,
                    unreadAlertsCount = unreadAlertsCount,
                    onMenuClick = {
                        coroutineScope.launch { drawerState.open() }
                    },
                    onSearchClick = {
                        viewModel.openSearch()
                    },
                    onNotificationClick = {
                        viewModel.selectTab(ScreenTab.ALERTS)
                    },
                    onToggleDarkMode = {
                        viewModel.toggleDarkMode(!userProfile.isDarkMode)
                    }
                )
            },
            bottomBar = {
                CGJobsBottomBar(
                    currentTab = currentTab,
                    onTabSelected = { tab ->
                        viewModel.selectTab(tab)
                    }
                )
            }
        ) { innerPadding ->
            Box(
                modifier = Modifier
                    .fillMaxSize()
                    .padding(innerPadding)
            ) {
                when (currentTab) {
                    ScreenTab.HOME -> {
                        HomeScreen(
                            newsList = newsList,
                            selectedCategory = selectedCategory,
                            isRefreshing = isRefreshing,
                            onCategorySelected = { viewModel.selectCategory(it) },
                            onArticleClick = { viewModel.openArticle(it) },
                            onToggleSave = { viewModel.toggleSave(it) },
                            onRefresh = { viewModel.refreshFeed() },
                            categories = jobsCategories
                        )
                    }
                    ScreenTab.CURRENT_AFFAIRS -> {
                        CurrentAffairsScreen(
                            newsList = if (currentAffairsList.isNotEmpty()) currentAffairsList else newsList,
                            onArticleClick = { viewModel.openArticle(it) },
                            onToggleSave = { viewModel.toggleSave(it) },
                            newsCategories = newsCategories
                        )
                    }
                    ScreenTab.STATIC_GK -> {
                        StaticGkScreen(
                            gkList = staticGkList,
                            onToggleSave = { viewModel.toggleGkSave(it) },
                            categories = staticGkCategories
                        )
                    }
                    ScreenTab.ALERTS -> {
                        AlertsScreen(
                            alerts = alertsList,
                            selectedFilter = selectedAlertFilter,
                            onFilterSelected = { viewModel.selectAlertFilter(it) },
                            onAlertClick = { alert ->
                                viewModel.markAlertAsRead(alert.id)
                                alert.articleId?.let { articleId ->
                                    viewModel.openArticleById(articleId)
                                }
                            },
                            onMarkAllRead = { viewModel.markAllAlertsAsRead() },
                            onBackClick = { viewModel.selectTab(ScreenTab.HOME) }
                        )
                    }
                    ScreenTab.EXPLORE -> {
                        ExploreScreen(
                            newsList = newsList,
                            onCategoryClick = { category ->
                                viewModel.selectCategory(category)
                                viewModel.selectTab(ScreenTab.HOME)
                            },
                            onArticleClick = { viewModel.openArticle(it) },
                            onToggleSave = { viewModel.toggleSave(it) },
                            onSearchClick = { viewModel.openSearch() },
                            sections = sections,
                            onSectionNavigate = { target ->
                                val parts = target.split(":", limit = 2)
                                val section = parts.getOrNull(0)
                                val catName = parts.getOrNull(1) ?: ""
                                when (section) {
                                    "jobs" -> {
                                        viewModel.selectCategory(catName)
                                        viewModel.selectTab(ScreenTab.HOME)
                                    }
                                    "news" -> {
                                        viewModel.selectTab(ScreenTab.CURRENT_AFFAIRS)
                                    }
                                    "static_gk" -> {
                                        viewModel.selectTab(ScreenTab.STATIC_GK)
                                    }
                                    else -> {
                                        viewModel.selectCategory(catName)
                                        viewModel.selectTab(ScreenTab.HOME)
                                    }
                                }
                            }
                        )
                    }
                    ScreenTab.SAVED -> {
                        SavedScreen(
                            savedNews = savedNews,
                            onArticleClick = { viewModel.openArticle(it) },
                            onToggleSave = { viewModel.toggleSave(it) },
                            onExploreClick = { viewModel.selectTab(ScreenTab.EXPLORE) }
                        )
                    }
                    ScreenTab.PROFILE -> {
                        ProfileScreen(
                            userProfile = userProfile,
                            onEditProfileClick = { viewModel.setEditProfileDialogVisible(true) },
                            onChangeInterestsClick = { viewModel.setInterestsDialogVisible(true) },
                            onNotificationToggle = { enabled ->
                                viewModel.updateNotificationSettings(
                                    enabled,
                                    userProfile.examAlertsEnabled,
                                    userProfile.resultAlertsEnabled
                                )
                            },
                            onExamAlertsToggle = { enabled ->
                                viewModel.updateNotificationSettings(
                                    userProfile.notificationsEnabled,
                                    enabled,
                                    userProfile.resultAlertsEnabled
                                )
                            },
                            onResultAlertsToggle = { enabled ->
                                viewModel.updateNotificationSettings(
                                    userProfile.notificationsEnabled,
                                    userProfile.examAlertsEnabled,
                                    enabled
                                )
                            },
                            onDarkModeToggle = { viewModel.toggleDarkMode(it) },
                            onAboutClick = { viewModel.setAboutDialogVisible(true) },
                            onServerSettingsClick = { viewModel.setServerSettingsDialogVisible(true) }
                        )
                    }
                }
            }
        }
    }

    // Dialogs
    if (showInterestsDialog) {
        InterestsDialog(
            currentInterests = userProfile.selectedInterests,
            onDismiss = { viewModel.setInterestsDialogVisible(false) },
            onSave = { selected ->
                viewModel.updateInterests(selected)
                viewModel.setInterestsDialogVisible(false)
            }
        )
    }

    if (showEditProfileDialog) {
        EditProfileDialog(
            currentName = userProfile.name,
            currentEmail = userProfile.email,
            onDismiss = { viewModel.setEditProfileDialogVisible(false) },
            onSave = { name, email ->
                viewModel.updateProfile(name, email)
                viewModel.setEditProfileDialogVisible(false)
            }
        )
    }

    if (showAboutDialog) {
        AboutDialog(
            onDismiss = { viewModel.setAboutDialogVisible(false) }
        )
    }

    if (showServerSettingsDialog) {
        ServerSettingsDialog(
            isOpen = true,
            onDismiss = { viewModel.setServerSettingsDialogVisible(false) },
            onServerConnected = { viewModel.refreshFromServer() }
        )
    }
}
