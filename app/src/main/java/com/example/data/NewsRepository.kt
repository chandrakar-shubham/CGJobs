package com.example.data

import com.example.model.AppCategory
import com.example.model.AppSection
import com.example.model.JobUpdate
import kotlinx.coroutines.flow.Flow

/**
 * NewsRepository defines the contract for job news, updates, sections, and categories discovery.
 * Designed for REST API / Backend dynamic synchronization.
 */
interface NewsRepository {
    fun getNewsStream(): Flow<List<JobUpdate>>
    fun getNewsByCategory(category: String): Flow<List<JobUpdate>>
    fun getNewsById(id: String): Flow<JobUpdate?>
    fun searchNews(query: String): Flow<List<JobUpdate>>
    fun getSavedNews(): Flow<List<JobUpdate>>
    suspend fun toggleSave(id: String)
    suspend fun refreshNews()

    // Synced Sections and Categories
    fun getSectionsStream(): Flow<List<AppSection>>
    fun getCategoriesStream(section: String? = null): Flow<List<AppCategory>>
}

