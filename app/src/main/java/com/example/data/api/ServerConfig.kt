package com.example.data.api

import android.content.Context
import android.util.Log
import com.squareup.moshi.Moshi
import com.squareup.moshi.kotlin.reflect.KotlinJsonAdapterFactory
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.withContext
import okhttp3.OkHttpClient
import okhttp3.logging.HttpLoggingInterceptor
import retrofit2.Retrofit
import retrofit2.converter.moshi.MoshiConverterFactory
import java.util.concurrent.TimeUnit

object ServerConfig {
    private const val PREFS_NAME = "cgjobs_server_prefs"
    private const val KEY_SERVER_URL = "backend_server_url"
    private const val KEY_SERVER_ENABLED = "backend_server_enabled"

    // Default emulator loopback to the running backend
    const val DEFAULT_EMULATOR_URL = "http://10.0.2.2:5000/"
    const val DEFAULT_LARAVEL_URL = "http://10.0.2.2:8000/"

    private var appContext: Context? = null
    private var cachedUrl: String? = null
    private var cachedService: CGJobsApiService? = null

    fun init(context: Context) {
        appContext = context.applicationContext
        val prefs = appContext?.getSharedPreferences(PREFS_NAME, Context.MODE_PRIVATE)
        val saved = prefs?.getString(KEY_SERVER_URL, null)
        if (!saved.isNullOrBlank()) {
            cachedUrl = normalizeUrl(saved)
        }
    }

    fun getServerUrl(context: Context? = null): String {
        if (cachedUrl != null) return cachedUrl!!
        val ctx = context ?: appContext
        if (ctx != null) {
            val prefs = ctx.getSharedPreferences(PREFS_NAME, Context.MODE_PRIVATE)
            val saved = prefs.getString(KEY_SERVER_URL, null)
            if (!saved.isNullOrBlank()) {
                cachedUrl = normalizeUrl(saved)
                return cachedUrl!!
            }
        }
        return DEFAULT_EMULATOR_URL
    }

    fun setServerUrl(context: Context, url: String) {
        val normalized = normalizeUrl(url)
        cachedUrl = normalized
        cachedService = null // Force recreate client
        val ctx = context.applicationContext
        appContext = ctx
        val prefs = ctx.getSharedPreferences(PREFS_NAME, Context.MODE_PRIVATE)
        prefs.edit().putString(KEY_SERVER_URL, normalized).apply()
    }

    fun isServerEnabled(context: Context? = null): Boolean {
        val ctx = context ?: appContext ?: return false
        val prefs = ctx.getSharedPreferences(PREFS_NAME, Context.MODE_PRIVATE)
        return prefs.getBoolean(KEY_SERVER_ENABLED, true)
    }

    fun setServerEnabled(context: Context, enabled: Boolean) {
        val ctx = context.applicationContext
        appContext = ctx
        val prefs = ctx.getSharedPreferences(PREFS_NAME, Context.MODE_PRIVATE)
        prefs.edit().putBoolean(KEY_SERVER_ENABLED, enabled).apply()
    }

    private fun normalizeUrl(url: String): String {
        var clean = url.trim()
        if (!clean.startsWith("http://") && !clean.startsWith("https://")) {
            clean = "https://$clean"
        }
        if (!clean.endsWith("/")) {
            clean = "$clean/"
        }
        return clean
    }

    fun getApiService(context: Context? = null): CGJobsApiService {
        val currentService = cachedService
        if (currentService != null) return currentService

        val baseUrl = getServerUrl(context)

        val logging = HttpLoggingInterceptor().apply {
            level = HttpLoggingInterceptor.Level.BASIC
        }

        val okHttpClient = OkHttpClient.Builder()
            .addInterceptor(logging)
            .connectTimeout(8, TimeUnit.SECONDS)
            .readTimeout(10, TimeUnit.SECONDS)
            .writeTimeout(10, TimeUnit.SECONDS)
            .build()

        val moshi = Moshi.Builder()
            .add(KotlinJsonAdapterFactory())
            .build()

        val retrofit = Retrofit.Builder()
            .baseUrl(baseUrl)
            .client(okHttpClient)
            .addConverterFactory(MoshiConverterFactory.create(moshi))
            .build()

        val service = retrofit.create(CGJobsApiService::class.java)
        cachedService = service
        return service
    }

    /**
     * Test server connectivity
     */
    suspend fun pingServer(testUrl: String): Pair<Boolean, String> = withContext(Dispatchers.IO) {
        try {
            val normalized = normalizeUrl(testUrl)
            val moshi = Moshi.Builder().add(KotlinJsonAdapterFactory()).build()
            val client = OkHttpClient.Builder()
                .connectTimeout(5, TimeUnit.SECONDS)
                .readTimeout(5, TimeUnit.SECONDS)
                .build()

            val retrofit = Retrofit.Builder()
                .baseUrl(normalized)
                .client(client)
                .addConverterFactory(MoshiConverterFactory.create(moshi))
                .build()

            val service = retrofit.create(CGJobsApiService::class.java)
            val resp = service.checkHealth()
            if (resp.status == "ok") {
                Pair(true, "Connected successfully! Server time: ${resp.serverTime ?: "Live"}")
            } else {
                Pair(false, "Server returned status: ${resp.status}")
            }
        } catch (e: Exception) {
            Log.e("ServerConfig", "Ping failed: ${e.message}")
            Pair(false, e.localizedMessage ?: "Connection failed. Please check the URL.")
        }
    }
}
