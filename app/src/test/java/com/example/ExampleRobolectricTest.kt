package com.example

import android.content.Context
import androidx.test.core.app.ApplicationProvider
import com.example.data.MockNewsRepository
import com.example.data.MockNotificationRepository
import kotlinx.coroutines.flow.first
import kotlinx.coroutines.runBlocking
import org.junit.Assert.assertEquals
import org.junit.Assert.assertNotNull
import org.junit.Assert.assertTrue
import org.junit.Test
import org.junit.runner.RunWith
import org.robolectric.RobolectricTestRunner
import org.robolectric.annotation.Config

@RunWith(RobolectricTestRunner::class)
@Config(sdk = [36])
class ExampleRobolectricTest {

    @Test
    fun `read string from context`() {
        val context = ApplicationProvider.getApplicationContext<Context>()
        val appName = context.getString(R.string.app_name)
        assertEquals("CGJobs", appName)
    }

    @Test
    fun `mock news repository provides realistic updates`() = runBlocking {
        val repo = MockNewsRepository()
        val allNews = repo.getNewsStream().first()
        assertTrue("News list should contain at least 15 items", allNews.size >= 15)

        val firstItem = allNews.first()
        assertTrue(firstItem.title.contains("शिक्षक भर्ती") || firstItem.category == "CGSSB")

        // Test category filtering
        val vyapamNews = repo.getNewsByCategory("CG Vyapam").first()
        assertTrue(vyapamNews.isNotEmpty())

        // Test search
        val searchResults = repo.searchNews("पटवारी").first()
        assertTrue(searchResults.isNotEmpty())

        // Test toggle bookmark
        val initialSaved = repo.getSavedNews().first().size
        repo.toggleSave("cgpsc_prelims_2026")
        val updatedSaved = repo.getSavedNews().first().size
        assertEquals(initialSaved + 1, updatedSaved)
    }

    @Test
    fun `mock notification repository manages alerts`() = runBlocking {
        val repo = MockNotificationRepository()
        val alerts = repo.getAlertsStream().first()
        assertTrue(alerts.isNotEmpty())

        val unreadInitial = repo.getUnreadCountStream().first()
        assertTrue(unreadInitial > 0)

        repo.markAllAsRead()
        val unreadAfter = repo.getUnreadCountStream().first()
        assertEquals(0, unreadAfter)
    }

    @Test
    fun `user repository toggles dark mode state`() = runBlocking {
        val repo = com.example.data.MockUserRepository()
        val initialProfile = repo.getUserProfile().first()
        assertEquals(false, initialProfile.isDarkMode)

        repo.updateDarkMode(true)
        val darkProfile = repo.getUserProfile().first()
        assertEquals(true, darkProfile.isDarkMode)

        repo.updateDarkMode(false)
        val lightProfile = repo.getUserProfile().first()
        assertEquals(false, lightProfile.isDarkMode)
    }

    @Test
    fun `current affairs items are populated and accessible`() = runBlocking {
        val repo = MockNewsRepository()
        val currentAffairs = repo.getNewsByCategory("Current Affairs").first()
        assertTrue("Current affairs should contain at least 5 articles", currentAffairs.size >= 5)

        // Ensure key items like Tiger reserve and Tendupatta are present
        val hasTigerReserve = currentAffairs.any { it.title.contains("टाइगर") }
        val hasTendupatta = currentAffairs.any { it.title.contains("तेंदूपत्ता") }
        assertTrue("Must contain Tiger Reserve current affair", hasTigerReserve)
        assertTrue("Must contain Tendupatta rate hike", hasTendupatta)

        // Ensure detailed content and source link are provided
        val sample = currentAffairs.first()
        assertNotNull(sample.detailedContent)
        assertTrue(sample.detailedContent.isNotBlank())
        assertTrue(sample.sourceUrl.isNotBlank())
    }

    @Test
    fun `view model manages current affairs tab and streams`() = runBlocking {
        val viewModel = com.example.ui.viewmodel.CGJobsViewModel()
        assertEquals(com.example.ui.viewmodel.ScreenTab.HOME, viewModel.currentTab.value)

        // Switch to Current Affairs tab
        viewModel.selectTab(com.example.ui.viewmodel.ScreenTab.CURRENT_AFFAIRS)
        assertEquals(com.example.ui.viewmodel.ScreenTab.CURRENT_AFFAIRS, viewModel.currentTab.value)

        // Verify current affairs list
        val affairs = viewModel.currentAffairsList.first()
        assertTrue(affairs.isNotEmpty())
    }

    @Test
    fun `server config normalizes url and handles defaults`() {
        val defaultUrl = com.example.data.api.ServerConfig.getServerUrl()
        assertTrue(defaultUrl.startsWith("http"))
        assertTrue(defaultUrl.endsWith("/"))
    }

    @Test
    fun `server backed news repository falls back smoothly offline`() = runBlocking {
        val repo = com.example.data.ServerBackedNewsRepository()
        val items = repo.getNewsStream().first()
        assertTrue("Repository must provide news even when server is offline", items.isNotEmpty())
    }
}

