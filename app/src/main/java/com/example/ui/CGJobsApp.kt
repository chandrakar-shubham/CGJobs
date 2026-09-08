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
import com.example.ui.components.*
import com.example.ui.screens.*
import com.example.ui.viewmodel.CGJobsViewModel
import com.example.ui.viewmodel.ScreenTab
import kotlinx.coroutines.launch

@Composable
fun CGJobsApp(viewModel: CGJobsViewModel = viewModel(), modifier: Modifier = Modifier) {
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
    val drawerState = rememberDrawerState(DrawerValue.Closed)
    val scope = rememberCoroutineScope()
    if (isSplashVisible) {
        SplashScreen(onDismiss = { viewModel.dismissSplash() })
        return
    }
    if (selectedArticle != null) {
        BackHandler { viewModel.closeArticle() }
        ArticleDetailScreen(
            article = selectedArticle!!,
            onBack = { viewModel.closeArticle() },
            onToggleSave = { viewModel.toggleSave(selectedArticle!!.id) }
        )
        return
    }
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
    ModalNavigationDrawer(
        drawerState = drawerState,
        drawerContent = {
            AppDrawerContent(
                isDarkMode = userProfile.isDarkMode,
                onNavigateHome = { viewModel.selectTab(ScreenTab.HOME) },
                onNavigateExplore = { viewModel.selectTab(ScreenTab.EXPLORE) },
                onNavigateCurrentAffairs = { viewModel.selectTab(ScreenTab.CURRENT_AFFAIRS) },
                onNavigateStaticGk = { viewModel.selectTab(ScreenTab.STATIC_GK) },
                onCategorySelected = {
                    viewModel.selectCategory(it)
                    viewModel.selectTab(ScreenTab.HOME)
                },
                onToggleDarkMode = { viewModel.toggleDarkMode(it) },
                onOpenServerSettings = { viewModel.setServerSettingsDialogVisible(true) },
                onCloseDrawer = { scope.launch { drawerState.close() } }
            )
        }
    ) {
        Scaffold(
            modifier = modifier.fillMaxSize(),
            topBar = {
                CGJobsTopBar(
                    isDarkMode = userProfile.isDarkMode,
                    unreadAlertsCount = unreadAlertsCount,
                    onMenuClick = { scope.launch { drawerState.open() } },
                    onSearchClick = { viewModel.openSearch() },
                    onNotificationClick = { viewModel.selectTab(ScreenTab.ALERTS) },
                    onToggleDarkMode = { viewModel.toggleDarkMode(!userProfile.isDarkMode) }
                )
            },
            bottomBar = {
                CGJobsBottomBar(
                    currentTab = currentTab,
                    onTabSelected = { viewModel.selectTab(it) },
                    unreadAlertsCount = unreadAlertsCount
                )
            }
        ) { padding ->
            Box(Modifier.fillMaxSize().padding(padding)) {
                when (currentTab) {
                    ScreenTab.HOME -> HomeScreen(
                        newsList = newsList,
                        selectedCategory = selectedCategory,
                        isRefreshing = isRefreshing,
                        onCategorySelected = { viewModel.selectCategory(it) },
                        onArticleClick = { viewModel.openArticle(it) },
                        onToggleSave = { viewModel.toggleSave(it) },
                        onRefresh = { viewModel.refreshFeed() },
                        categories = jobsCategories
                    )
                    ScreenTab.CURRENT_AFFAIRS -> CurrentAffairsScreen(
                        newsList = if (currentAffairsList.isNotEmpty()) currentAffairsList else newsList,
                        onArticleClick = { viewModel.openArticle(it) },
                        onToggleSave = { viewModel.toggleSave(it) },
                        newsCategories = newsCategories
                    )
                    ScreenTab.STATIC_GK -> StaticGkScreen(
                        gkList = staticGkList,
                        onToggleSave = { viewModel.toggleGkSave(it) },
                        categories = staticGkCategories
                    )
                    ScreenTab.ALERTS -> AlertsScreen(
                        alerts = alertsList,
                        selectedFilter = selectedAlertFilter,
                        onFilterSelected = { viewModel.selectAlertFilter(it) },
                        onAlertClick = { alert ->
                            viewModel.markAlertAsRead(alert.id)
                            alert.articleId?.let(viewModel::openArticleById)
                        },
                        onMarkAllRead = { viewModel.markAllAlertsAsRead() },
                        onBackClick = { viewModel.selectTab(ScreenTab.HOME) }
                    )
                    ScreenTab.EXPLORE -> ExploreScreen(
                        newsList = newsList,
                        onCategoryClick = {
                            viewModel.selectCategory(it)
                            viewModel.selectTab(ScreenTab.HOME)
                        },
                        onArticleClick = { viewModel.openArticle(it) },
                        onToggleSave = { viewModel.toggleSave(it) },
                        onSearchClick = { viewModel.openSearch() },
                        sections = sections,
                        onSectionNavigate = { target ->
                            val p = target.split(":", limit = 2)
                            when (p.firstOrNull()) {
                                "news" -> viewModel.selectTab(ScreenTab.CURRENT_AFFAIRS)
                                "static_gk" -> viewModel.selectTab(ScreenTab.STATIC_GK)
                                else -> {
                                    viewModel.selectCategory(p.getOrNull(1) ?: "")
                                    viewModel.selectTab(ScreenTab.HOME)
                                }
                            }
                        }
                    )
                    ScreenTab.SAVED -> SavedScreen(
                        savedNews = savedNews,
                        onArticleClick = { viewModel.openArticle(it) },
                        onToggleSave = { viewModel.toggleSave(it) },
                        onExploreClick = { viewModel.selectTab(ScreenTab.EXPLORE) }
                    )
                    ScreenTab.PROFILE -> ProfileScreen(
                        userProfile = userProfile,
                        onEditProfileClick = { viewModel.setEditProfileDialogVisible(true) },
                        onChangeInterestsClick = { viewModel.setInterestsDialogVisible(true) },
                        onNotificationToggle = { viewModel.updateNotificationSettings(it, userProfile.examAlertsEnabled, userProfile.resultAlertsEnabled) },
                        onExamAlertsToggle = { viewModel.updateNotificationSettings(userProfile.notificationsEnabled, it, userProfile.resultAlertsEnabled) },
                        onResultAlertsToggle = { viewModel.updateNotificationSettings(userProfile.notificationsEnabled, userProfile.examAlertsEnabled, it) },
                        onDarkModeToggle = { viewModel.toggleDarkMode(it) },
                        onAboutClick = { viewModel.setAboutDialogVisible(true) },
                        onServerSettingsClick = { viewModel.setServerSettingsDialogVisible(true) },
                        onLanguageChange = { viewModel.setLanguage(it) }
                    )
                }
            }
        }
    }
    if (showInterestsDialog) InterestsDialog(userProfile.selectedInterests, { viewModel.setInterestsDialogVisible(false) }, { viewModel.updateInterests(it); viewModel.setInterestsDialogVisible(false) })
    if (showEditProfileDialog) EditProfileDialog(userProfile.name, userProfile.email, { viewModel.setEditProfileDialogVisible(false) }, { n,e -> viewModel.updateProfile(n,e); viewModel.setEditProfileDialogVisible(false) })
    if (showAboutDialog) AboutDialog { viewModel.setAboutDialogVisible(false) }
    if (showServerSettingsDialog) ServerSettingsDialog(true, { viewModel.setServerSettingsDialogVisible(false) }, { viewModel.refreshFromServer() })
}
