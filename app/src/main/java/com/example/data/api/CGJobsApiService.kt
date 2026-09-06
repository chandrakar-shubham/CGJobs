package com.example.data.api

import com.example.data.api.model.AlertsApiResponse
import com.example.data.api.model.CategoriesApiResponse
import com.example.data.api.model.HealthResponse
import com.example.data.api.model.NewsApiResponse
import com.example.data.api.model.RegisterTokenRequest
import com.example.data.api.model.SectionsApiResponse
import com.example.data.api.model.SingleNewsResponse
import com.example.data.api.model.StaticGkApiResponse
import retrofit2.http.Body
import retrofit2.http.GET
import retrofit2.http.POST
import retrofit2.http.Path
import retrofit2.http.Query

interface CGJobsApiService {

    @GET("api/health")
    suspend fun checkHealth(): HealthResponse

    @GET("api/news")
    suspend fun getNews(
        @Query("category") category: String? = null,
        @Query("query") query: String? = null,
        @Query("section") section: String? = null,
        @Query("limit") limit: Int? = 100
    ): NewsApiResponse

    @GET("api/jobs")
    suspend fun getJobs(
        @Query("category") category: String? = null,
        @Query("query") query: String? = null,
        @Query("limit") limit: Int? = 100
    ): NewsApiResponse

    @GET("api/news/{id}")
    suspend fun getNewsDetail(
        @Path("id") id: String
    ): SingleNewsResponse

    @GET("api/sections")
    suspend fun getSections(): SectionsApiResponse

    @GET("api/categories")
    suspend fun getCategories(
        @Query("section") section: String? = null
    ): CategoriesApiResponse

    @GET("api/static-gk")
    suspend fun getStaticGk(
        @Query("category") category: String? = null,
        @Query("query") query: String? = null
    ): StaticGkApiResponse

    @GET("api/alerts")
    suspend fun getAlerts(): AlertsApiResponse

    @POST("api/alerts/register-token")
    suspend fun registerDeviceToken(
        @Body request: RegisterTokenRequest
    ): Map<String, Any>
}
