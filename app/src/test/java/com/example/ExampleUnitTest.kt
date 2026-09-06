package com.example

import com.example.model.ImportantDates
import com.example.model.JobUpdate
import com.example.ui.components.toInshortsPostItem
import org.junit.Assert.*
import org.junit.Test

class ExampleUnitTest {
  @Test
  fun addition_isCorrect() {
    assertEquals(4, 2 + 2)
  }

  @Test
  fun inshortsPostItem_mappingAndDeepLink_isCorrect() {
    val job = JobUpdate(
        id = "cg-police-constable-2025",
        title = "छत्तीसगढ़ पुलिस आरक्षक (GD) भर्ती",
        summary = "छत्तीसगढ़ पुलिस विभाग ने 5,967 आरक्षक पदों पर आवेदन आमंत्रित किए हैं।",
        detailedContent = "विस्तृत विवरण...",
        category = "Police",
        source = "CG Police",
        sourceUrl = "https://cgpolice.gov.in",
        imageUrl = "https://example.com/banner.jpg",
        publishedAt = "02 मार्च 2025",
        relativeTime = "आज",
        isBreaking = true,
        isNew = true,
        importantDates = ImportantDates(
            applicationStart = "01 जनवरी 2025",
            lastDate = "15 मार्च 2025"
        ),
        vacancies = "5,967 पद",
        eligibility = "10वीं / 12वीं उत्तीर्ण",
        applyUrl = "https://cgpolice.gov.in/apply"
    )

    val inshortItem = job.toInshortsPostItem(isJob = true)

    assertEquals("cg-police-constable-2025", inshortItem.id)
    assertEquals("छत्तीसगढ़ पुलिस आरक्षक (GD) भर्ती", inshortItem.title)
    assertEquals("cgjobs://post/cg-police-constable-2025", inshortItem.deepLinkUri)
    assertTrue(inshortItem.isJob)
    assertTrue(inshortItem.isBreaking)
    assertEquals("5,967 पद", inshortItem.vacancies)
    assertEquals("15 मार्च 2025", inshortItem.lastDate)
    assertEquals("https://cgpolice.gov.in/apply", inshortItem.applyUrl)
    assertEquals("https://example.com/banner.jpg", inshortItem.imageUrl)
  }
}
