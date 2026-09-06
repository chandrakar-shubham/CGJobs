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
    if (isSplashVisible) { SplashScreen { viewModel.dismissSplash() }; return }
    if (selectedArticle != null) { BackHandler { viewModel.closeArticle() }; ArticleDetailScreen(selectedArticle!!, { viewModel.closeArticle() }, { viewModel.toggleSave(selectedArticle!!.id) }); return }
    if (isSearchActive) { BackHandler { viewModel.closeSearch() }; SearchScreen(searchQuery, searchResults, { viewModel.setSearchQuery(it) }, { viewModel.closeSearch() }, { viewModel.openArticle(it) }, { viewModel.toggleSave(it) }); return }
    ModalNavigationDrawer(drawerState = drawerState, drawerContent = {
        AppDrawerContent(isDarkMode = userProfile.isDarkMode, onNavigateHome = { viewModel.selectTab(ScreenTab.HOME) }, onNavigateExplore = { viewModel.selectTab(ScreenTab.EXPLORE) }, onNavigateCurrentAffairs = { viewModel.selectTab(ScreenTab.CURRENT_AFFAIRS) }, onNavigateStaticGk = { viewModel.selectTab(ScreenTab.STATIC_GK) }, onCategorySelected = { viewModel.selectCategory(it); viewModel.selectTab(ScreenTab.HOME) }, onToggleDarkMode = { viewModel.toggleDarkMode(it) }, onOpenServerSettings = { viewModel.setServerSettingsDialogVisible(true) }, onCloseDrawer = { scope.launch { drawerState.close() } })
    }) {
        Scaffold(modifier.fillMaxSize(), topBar = { CGJobsTopBar(userProfile.isDarkMode, unreadAlertsCount, { scope.launch { drawerState.open() } }, { viewModel.openSearch() }, { viewModel.selectTab(ScreenTab.ALERTS) }, { viewModel.toggleDarkMode(!userProfile.isDarkMode) }) }, bottomBar = { CGJobsBottomBar(currentTab) { viewModel.selectTab(it) } }) { padding ->
            Box(Modifier.fillMaxSize().padding(padding)) {
                when (currentTab) {
                    ScreenTab.HOME -> HomeScreen(newsList, selectedCategory, isRefreshing, { viewModel.selectCategory(it) }, { viewModel.openArticle(it) }, { viewModel.toggleSave(it) }, { viewModel.refreshFeed() }, jobsCategories)
                    ScreenTab.CURRENT_AFFAIRS -> CurrentAffairsScreen(if (currentAffairsList.isNotEmpty()) currentAffairsList else newsList, { viewModel.openArticle(it) }, { viewModel.toggleSave(it) }, newsCategories)
                    ScreenTab.STATIC_GK -> StaticGkScreen(staticGkList, { viewModel.toggleGkSave(it) }, staticGkCategories)
                    ScreenTab.ALERTS -> AlertsScreen(alertsList, selectedAlertFilter, { viewModel.selectAlertFilter(it) }, { alert -> viewModel.markAlertAsRead(alert.id); alert.articleId?.let(viewModel::openArticleById) }, { viewModel.markAllAlertsAsRead() }, { viewModel.selectTab(ScreenTab.HOME) })
                    ScreenTab.EXPLORE -> ExploreScreen(newsList, { viewModel.selectCategory(it); viewModel.selectTab(ScreenTab.HOME) }, { viewModel.openArticle(it) }, { viewModel.toggleSave(it) }, { viewModel.openSearch() }, sections, { target -> val p = target.split(":", limit = 2); when (p.firstOrNull()) { "news" -> viewModel.selectTab(ScreenTab.CURRENT_AFFAIRS); "static_gk" -> viewModel.selectTab(ScreenTab.STATIC_GK); else -> { viewModel.selectCategory(p.getOrNull(1) ?: ""); viewModel.selectTab(ScreenTab.HOME) } } })
                    ScreenTab.SAVED -> SavedScreen(savedNews, { viewModel.openArticle(it) }, { viewModel.toggleSave(it) }, { viewModel.selectTab(ScreenTab.EXPLORE) })
                    ScreenTab.PROFILE -> ProfileScreen(userProfile, { viewModel.setEditProfileDialogVisible(true) }, { viewModel.setInterestsDialogVisible(true) }, { viewModel.updateNotificationSettings(it, userProfile.examAlertsEnabled, userProfile.resultAlertsEnabled) }, { viewModel.updateNotificationSettings(userProfile.notificationsEnabled, it, userProfile.resultAlertsEnabled) }, { viewModel.updateNotificationSettings(userProfile.notificationsEnabled, userProfile.examAlertsEnabled, it) }, { viewModel.toggleDarkMode(it) }, { viewModel.setAboutDialogVisible(true) }, { viewModel.setServerSettingsDialogVisible(true) }, { viewModel.setLanguage(it) })
                }
            }
        }
    }
    if (showInterestsDialog) InterestsDialog(userProfile.selectedInterests, { viewModel.setInterestsDialogVisible(false) }, { viewModel.updateInterests(it); viewModel.setInterestsDialogVisible(false) })
    if (showEditProfileDialog) EditProfileDialog(userProfile.name, userProfile.email, { viewModel.setEditProfileDialogVisible(false) }, { n,e -> viewModel.updateProfile(n,e); viewModel.setEditProfileDialogVisible(false) })
    if (showAboutDialog) AboutDialog { viewModel.setAboutDialogVisible(false) }
    if (showServerSettingsDialog) ServerSettingsDialog(true, { viewModel.setServerSettingsDialogVisible(false) }, { viewModel.refreshFromServer() })
}
