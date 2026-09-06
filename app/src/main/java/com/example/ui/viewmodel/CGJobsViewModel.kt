package com.example.ui.viewmodel

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.example.data.MockUserRepository
import com.example.data.NewsRepository
import com.example.data.NotificationRepository
import com.example.data.ServerBackedNewsRepository
import com.example.data.ServerBackedNotificationRepository
import com.example.data.UserRepository
import com.example.data.BilingualStaticGkRepository
import com.example.data.api.ServerConfig
import com.example.model.AlertItem
import com.example.model.AppCategory
import com.example.model.AppSection
import com.example.model.JobUpdate
import com.example.model.StaticGkCard
import com.example.model.UserProfile
import kotlinx.coroutines.delay
import kotlinx.coroutines.flow.*
import kotlinx.coroutines.launch

enum class ScreenTab { HOME, CURRENT_AFFAIRS, STATIC_GK, EXPLORE, SAVED, PROFILE, ALERTS }

class CGJobsViewModel(
    private val newsRepository: NewsRepository = ServerBackedNewsRepository(),
    private val notificationRepository: NotificationRepository = ServerBackedNotificationRepository(),
    private val userRepository: UserRepository = MockUserRepository(),
    private val staticGkRepository: BilingualStaticGkRepository = BilingualStaticGkRepository()
) : ViewModel() {
    private val _currentTab=MutableStateFlow(ScreenTab.HOME); val currentTab:StateFlow<ScreenTab> = _currentTab.asStateFlow()
    private val _isSplashVisible=MutableStateFlow(true); val isSplashVisible:StateFlow<Boolean> = _isSplashVisible.asStateFlow()
    private val _selectedArticle=MutableStateFlow<JobUpdate?>(null); val selectedArticle:StateFlow<JobUpdate?> = _selectedArticle.asStateFlow()
    private val _selectedCategory=MutableStateFlow("सभी"); val selectedCategory:StateFlow<String> = _selectedCategory.asStateFlow()
    private val _selectedAlertFilter=MutableStateFlow("All"); val selectedAlertFilter:StateFlow<String> = _selectedAlertFilter.asStateFlow()
    private val _searchQuery=MutableStateFlow(""); val searchQuery:StateFlow<String> = _searchQuery.asStateFlow()
    private val _isSearchActive=MutableStateFlow(false); val isSearchActive:StateFlow<Boolean> = _isSearchActive.asStateFlow()
    private val _isRefreshing=MutableStateFlow(false); val isRefreshing:StateFlow<Boolean> = _isRefreshing.asStateFlow()
    private val _isDrawerOpen=MutableStateFlow(false); val isDrawerOpen:StateFlow<Boolean> = _isDrawerOpen.asStateFlow()
    private val _showInterestsDialog=MutableStateFlow(false); val showInterestsDialog:StateFlow<Boolean> = _showInterestsDialog.asStateFlow()
    private val _showNotificationDialog=MutableStateFlow(false); val showNotificationDialog:StateFlow<Boolean> = _showNotificationDialog.asStateFlow()
    private val _showEditProfileDialog=MutableStateFlow(false); val showEditProfileDialog:StateFlow<Boolean> = _showEditProfileDialog.asStateFlow()
    private val _showAboutDialog=MutableStateFlow(false); val showAboutDialog:StateFlow<Boolean> = _showAboutDialog.asStateFlow()
    private val _showServerSettingsDialog=MutableStateFlow(false); val showServerSettingsDialog:StateFlow<Boolean> = _showServerSettingsDialog.asStateFlow()
    val userProfile:StateFlow<UserProfile> = userRepository.getUserProfile().stateIn(viewModelScope,SharingStarted.Eagerly,UserProfile())
    val sections:StateFlow<List<AppSection>> = newsRepository.getSectionsStream().stateIn(viewModelScope,SharingStarted.WhileSubscribed(5000),emptyList())
    val allCategories:StateFlow<List<AppCategory>> = newsRepository.getCategoriesStream().stateIn(viewModelScope,SharingStarted.WhileSubscribed(5000),emptyList())
    val jobsCategories:StateFlow<List<AppCategory>> = newsRepository.getCategoriesStream("jobs").stateIn(viewModelScope,SharingStarted.WhileSubscribed(5000),emptyList())
    val newsCategories:StateFlow<List<AppCategory>> = newsRepository.getCategoriesStream("news").stateIn(viewModelScope,SharingStarted.WhileSubscribed(5000),emptyList())
    val staticGkCategories:StateFlow<List<AppCategory>> = newsRepository.getCategoriesStream("static_gk").stateIn(viewModelScope,SharingStarted.WhileSubscribed(5000),emptyList())
    val newsList:StateFlow<List<JobUpdate>> = _selectedCategory.flatMapLatest { category->newsRepository.getNewsByCategory(category) }.stateIn(viewModelScope,SharingStarted.WhileSubscribed(5000),emptyList())
    val currentAffairsList:StateFlow<List<JobUpdate>> = newsRepository.getNewsStream().map { list->list.filter { item->item.category.equals("Current Affairs",true)||item.category.contains("Affairs",true)||item.title.contains("समसामयिकी")||item.title.contains("करेंट अफेयर्स")||item.title.contains("बजट")||item.title.contains("योजना") } }.stateIn(viewModelScope,SharingStarted.WhileSubscribed(5000),emptyList())
    val staticGkList:StateFlow<List<StaticGkCard>> = staticGkRepository.gkStream.stateIn(viewModelScope,SharingStarted.WhileSubscribed(5000),emptyList())
    val savedNews:StateFlow<List<JobUpdate>> = newsRepository.getSavedNews().stateIn(viewModelScope,SharingStarted.WhileSubscribed(5000),emptyList())
    val searchResults:StateFlow<List<JobUpdate>> = _searchQuery.flatMapLatest { query->newsRepository.searchNews(query) }.stateIn(viewModelScope,SharingStarted.WhileSubscribed(5000),emptyList())
    private val rawAlerts=notificationRepository.getAlertsStream()
    val alertsList:StateFlow<List<AlertItem>> = combine(rawAlerts,_selectedAlertFilter){list,filter->when(filter){"Important"->list.filter{it.type==com.example.model.AlertType.BREAKING||it.type==com.example.model.AlertType.DEADLINE};"Exams"->list.filter{it.type==com.example.model.AlertType.EXAM_DATE||it.type==com.example.model.AlertType.ADMIT_CARD};"Results"->list.filter{it.type==com.example.model.AlertType.RESULT};else->list}}.stateIn(viewModelScope,SharingStarted.WhileSubscribed(5000),emptyList())
    val unreadAlertsCount:StateFlow<Int> = notificationRepository.getUnreadCountStream().stateIn(viewModelScope,SharingStarted.WhileSubscribed(5000),0)
    init { viewModelScope.launch { delay(1600); _isSplashVisible.value=false } }
    fun dismissSplash(){_isSplashVisible.value=false}
    fun selectTab(tab:ScreenTab){_selectedArticle.value=null;_isSearchActive.value=false;_currentTab.value=tab}
    fun selectCategory(category:String){_selectedCategory.value=category}
    fun selectAlertFilter(filter:String){_selectedAlertFilter.value=filter}
    fun openArticle(article:JobUpdate){_selectedArticle.value=article}
    fun closeArticle(){_selectedArticle.value=null}
    fun openArticleById(id:String){viewModelScope.launch{newsRepository.getNewsById(id).collect{if(it!=null)_selectedArticle.value=it}}}
    fun toggleSave(id:String){viewModelScope.launch{newsRepository.toggleSave(id);if(_selectedArticle.value?.id==id)_selectedArticle.value=_selectedArticle.value?.copy(isSaved=!(_selectedArticle.value?.isSaved?:false))}}
    fun toggleGkSave(id:String){staticGkRepository.toggleSave(id)}
    fun setSearchQuery(query:String){_searchQuery.value=query}
    fun openSearch(){_isSearchActive.value=true}
    fun closeSearch(){_isSearchActive.value=false;_searchQuery.value=""}
    fun refreshFeed(){viewModelScope.launch{_isRefreshing.value=true;newsRepository.refreshNews();staticGkRepository.refreshGk();delay(600);_isRefreshing.value=false}}
    fun markAlertAsRead(alertId:String){viewModelScope.launch{notificationRepository.markAsRead(alertId)}}
    fun markAllAlertsAsRead(){viewModelScope.launch{notificationRepository.markAllAsRead()}}
    fun setDrawerOpen(open:Boolean){_isDrawerOpen.value=open}
    fun setInterestsDialogVisible(visible:Boolean){_showInterestsDialog.value=visible}
    fun setNotificationDialogVisible(visible:Boolean){_showNotificationDialog.value=visible}
    fun setEditProfileDialogVisible(visible:Boolean){_showEditProfileDialog.value=visible}
    fun setAboutDialogVisible(visible:Boolean){_showAboutDialog.value=visible}
    fun setServerSettingsDialogVisible(visible:Boolean){_showServerSettingsDialog.value=visible}
    fun refreshFromServer(){viewModelScope.launch{_isRefreshing.value=true;newsRepository.refreshNews();staticGkRepository.refreshGk();(notificationRepository as? ServerBackedNotificationRepository)?.tryFetchAlerts();delay(500);_isRefreshing.value=false}}
    fun updateInterests(interests:Set<String>){viewModelScope.launch{userRepository.updateInterests(interests)}}
    fun updateNotificationSettings(enabled:Boolean,examAlerts:Boolean,resultAlerts:Boolean){viewModelScope.launch{userRepository.updateNotificationSettings(enabled,examAlerts,resultAlerts)}}
    fun updateProfile(name:String,email:String){viewModelScope.launch{userRepository.updateProfileInfo(name,email)}}
    fun toggleDarkMode(isDark:Boolean){viewModelScope.launch{userRepository.updateDarkMode(isDark)}}
    fun setLanguage(lang:String){val english=lang.equals("English",true)||lang=="en";val normalized=if(english)"English" else "Hindi";ServerConfig.setLanguage(language=if(english)"en" else "hi");if(newsRepository is ServerBackedNewsRepository)newsRepository.setLanguage(if(english)"en" else "hi");viewModelScope.launch{userRepository.updateLanguage(normalized);staticGkRepository.refreshGk()}}
}
