package com.example.data

import com.example.model.JobUpdate
import kotlinx.coroutines.flow.Flow

/**
 * NewsRepository defines the contract for job news and updates discovery.
 * Designed for future REST API / Backend compatibility without changing UI code.
 */
interface NewsRepository {
    fun getNewsStream(): Flow<List<JobUpdate>>
    fun getNewsByCategory(category: String): Flow<List<JobUpdate>>
    fun getNewsById(id: String): Flow<JobUpdate?>
    fun searchNews(query: String): Flow<List<JobUpdate>>
    fun getSavedNews(): Flow<List<JobUpdate>>
    suspend fun toggleSave(id: String)
    suspend fun refreshNews()
}
