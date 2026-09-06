package com.example.data

import android.content.Context
import android.util.Log
import com.example.data.api.ServerConfig
import com.example.data.api.model.toDomain
import com.example.model.AppCategory
import com.example.model.AppSection
import com.example.model.JobUpdate
import kotlinx.coroutines.CoroutineScope
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.flow.*
import kotlinx.coroutines.launch
import kotlinx.coroutines.withContext

class ServerBackedNewsRepository(private val context: Context? = null, private val fallbackRepository: NewsRepository = MockNewsRepository()) : NewsRepository {
    private val scope=CoroutineScope(Dispatchers.IO)
    private val _newsStream=MutableStateFlow<List<JobUpdate>>(emptyList())
    private val _sectionsStream=MutableStateFlow<List<AppSection>>(emptyList())
    private val _categoriesStream=MutableStateFlow<List<AppCategory>>(emptyList())
    private val _isSyncing=MutableStateFlow(false)
    private val _serverAvailable=MutableStateFlow(false)
    @Volatile private var language: String = ServerConfig.getLanguage(context)
    val isSyncing:Flow<Boolean> = _isSyncing.asStateFlow()
    val serverAvailable:Flow<Boolean> = _serverAvailable.asStateFlow()
    init{scope.launch{tryFetchFromServer()}}
    fun setLanguage(language:String){this.language=if(language.equals("English",true)||language=="en")"en" else "hi";ServerConfig.setLanguage(context,this.language);scope.launch{tryFetchFromServer()}}
    suspend fun tryFetchFromServer():Boolean=withContext(Dispatchers.IO){_isSyncing.value=true;var success=false;try{val api=ServerConfig.getApiService(context);try{api.getSections().let{if(it.success&&!it.sections.isNullOrEmpty())_sectionsStream.value=it.sections.map{ s->s.toDomain()}}}catch(e:Exception){Log.w("CGJobsRepo","sections: ${e.message}")};try{api.getCategories(null,language).let{if(it.success&&!it.categories.isNullOrEmpty())_categoriesStream.value=it.categories.map{ c->c.toDomain()}}}catch(e:Exception){Log.w("CGJobsRepo","categories: ${e.message}")};val response=api.getNews(limit=100,language=language);if(response.success&&!response.news.isNullOrEmpty()){val saved=_newsStream.value.filter{it.isSaved}.map{it.id}.toSet();_newsStream.value=response.news.map{it.toDomain()}.map{if(it.id in saved)it.copy(isSaved=true)else it};_serverAvailable.value=true;success=true}}catch(e:Exception){_serverAvailable.value=false;Log.w("CGJobsRepo","sync: ${e.message}")};_isSyncing.value=false;success}
    override fun getNewsStream():Flow<List<JobUpdate>>=_newsStream.asStateFlow()
    override fun getSectionsStream():Flow<List<AppSection>>=_sectionsStream.asStateFlow()
    override fun getCategoriesStream(section:String?):Flow<List<AppCategory>>=_categoriesStream.map{list->if(section.isNullOrBlank())list else list.filter{it.section.equals(section,true)}}
    override fun getNewsByCategory(category:String):Flow<List<JobUpdate>>=_newsStream.map{list->if(category=="सभी"||category.equals("All",true)||category.startsWith("सभी")||category.startsWith("All"))list else list.filter{it.category.equals(category,true)||it.category.contains(category,true)}}
    override fun getNewsById(id:String):Flow<JobUpdate?>=_newsStream.map{it.find{item->item.id==id}}
    override fun searchNews(query:String):Flow<List<JobUpdate>>=_newsStream.map{list->if(query.isBlank())list else list.filter{it.title.contains(query,true)||it.summary.contains(query,true)||it.category.contains(query,true)||it.source.contains(query,true)}}
    override fun getSavedNews():Flow<List<JobUpdate>>=_newsStream.map{it.filter(JobUpdate::isSaved)}
    override suspend fun toggleSave(id:String){_newsStream.value=_newsStream.value.map{if(it.id==id)it.copy(isSaved=!it.isSaved)else it};fallbackRepository.toggleSave(id)}
    override suspend fun refreshNews(){if(!tryFetchFromServer()&&_newsStream.value.isEmpty()){fallbackRepository.refreshNews();_newsStream.value=fallbackRepository.getNewsStream().first()}}
}
