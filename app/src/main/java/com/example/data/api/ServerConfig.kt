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
    private const val KEY_LANGUAGE = "app_language"
    const val LIVE_SERVER_URL = "https://darkgoldenrod-camel-860943.hostingersite.com/"
    const val DEFAULT_EMULATOR_URL = "http://10.0.2.2:5000/"
    const val DEFAULT_LARAVEL_URL = "https://darkgoldenrod-camel-860943.hostingersite.com/"

    private var appContext: Context? = null
    private var cachedUrl: String? = null
    private var cachedService: CGJobsApiService? = null

    fun init(context: Context) {
        appContext = context.applicationContext
        val prefs = appContext?.getSharedPreferences(PREFS_NAME, Context.MODE_PRIVATE)
        prefs?.getString(KEY_SERVER_URL, null)?.takeIf { it.isNotBlank() }?.let { cachedUrl = normalizeUrl(it) }
    }

    fun getLanguage(context: Context? = null): String {
        val ctx = context?.applicationContext ?: appContext
        return ctx?.getSharedPreferences(PREFS_NAME, Context.MODE_PRIVATE)?.getString(KEY_LANGUAGE, "hi")?.let { if (it == "en") "en" else "hi" } ?: "hi"
    }

    fun setLanguage(context: Context? = null, language: String) {
        val ctx = context?.applicationContext ?: appContext ?: return
        val value = if (language == "en" || language.equals("English", true)) "en" else "hi"
        ctx.getSharedPreferences(PREFS_NAME, Context.MODE_PRIVATE).edit().putString(KEY_LANGUAGE, value).apply()
    }

    fun getServerUrl(context: Context? = null): String {
        if (cachedUrl != null) return cachedUrl!!
        val ctx = context ?: appContext
        if (ctx != null) ctx.getSharedPreferences(PREFS_NAME, Context.MODE_PRIVATE).getString(KEY_SERVER_URL, null)?.takeIf { it.isNotBlank() }?.let { cachedUrl = normalizeUrl(it); return cachedUrl!! }
        return LIVE_SERVER_URL
    }

    fun setServerUrl(context: Context, url: String) {
        val normalized = normalizeUrl(url); cachedUrl = normalized; cachedService = null; val ctx=context.applicationContext; appContext=ctx
        ctx.getSharedPreferences(PREFS_NAME, Context.MODE_PRIVATE).edit().putString(KEY_SERVER_URL, normalized).apply()
    }

    fun isServerEnabled(context: Context? = null): Boolean = (context ?: appContext)?.getSharedPreferences(PREFS_NAME, Context.MODE_PRIVATE)?.getBoolean(KEY_SERVER_ENABLED, true) ?: false
    fun setServerEnabled(context: Context, enabled: Boolean) { val ctx=context.applicationContext; appContext=ctx; ctx.getSharedPreferences(PREFS_NAME, Context.MODE_PRIVATE).edit().putBoolean(KEY_SERVER_ENABLED, enabled).apply() }

    private fun normalizeUrl(url: String): String { var clean=url.trim(); if(!clean.startsWith("http://")&&!clean.startsWith("https://"))clean="https://$clean"; if(!clean.endsWith("/"))clean="$clean/"; return clean }

    fun getApiService(context: Context? = null): CGJobsApiService {
        cachedService?.let { return it }
        val logging=HttpLoggingInterceptor().apply{level=HttpLoggingInterceptor.Level.BASIC}
        val client=OkHttpClient.Builder().addInterceptor(logging).connectTimeout(8,TimeUnit.SECONDS).readTimeout(10,TimeUnit.SECONDS).writeTimeout(10,TimeUnit.SECONDS).build()
        val moshi=Moshi.Builder().add(KotlinJsonAdapterFactory()).build()
        val service=Retrofit.Builder().baseUrl(getServerUrl(context)).client(client).addConverterFactory(MoshiConverterFactory.create(moshi)).build().create(CGJobsApiService::class.java)
        cachedService=service; return service
    }

    suspend fun pingServer(testUrl: String): Pair<Boolean,String> = withContext(Dispatchers.IO) {
        try { val normalized=normalizeUrl(testUrl); val moshi=Moshi.Builder().add(KotlinJsonAdapterFactory()).build(); val client=OkHttpClient.Builder().connectTimeout(5,TimeUnit.SECONDS).readTimeout(5,TimeUnit.SECONDS).build(); val service=Retrofit.Builder().baseUrl(normalized).client(client).addConverterFactory(MoshiConverterFactory.create(moshi)).build().create(CGJobsApiService::class.java); val resp=service.checkHealth(); if(resp.status=="ok") Pair(true,"Connected successfully! Server time: ${resp.serverTime ?: "Live"}") else Pair(false,"Server returned status: ${resp.status}") } catch(e:Exception){ Log.e("ServerConfig","Ping failed: ${e.message}"); Pair(false,e.localizedMessage ?: "Connection failed. Please check the URL.") }
    }
}
