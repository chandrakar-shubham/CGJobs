package com.example.data

import com.example.model.AlertItem
import com.example.model.AlertType
import kotlinx.coroutines.flow.Flow
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.flow.map

class MockNotificationRepository : NotificationRepository {

    private val initialAlerts = listOf(
        AlertItem(
            id = "alert_1",
            category = "CGSSB",
            title = "शिक्षक भर्ती 2026 नोटिफिकेशन जारी",
            shortDescription = "12,489 पदों पर भर्ती, आवेदन 01 सितम्बर से शुरू हो रहे हैं",
            time = "2h ago",
            type = AlertType.BREAKING,
            isRead = false,
            articleId = "cgssb_teacher_2026"
        ),
        AlertItem(
            id = "alert_2",
            category = "CG Vyapam",
            title = "पटवारी भर्ती 2026 का एग्जाम डेट जारी",
            shortDescription = "परीक्षा 18 अक्टूबर 2026 को प्रदेशभर में आयोजित होगी",
            time = "5h ago",
            type = AlertType.EXAM_DATE,
            isRead = false,
            articleId = "cg_vyapam_patwari_2026"
        ),
        AlertItem(
            id = "alert_3",
            category = "Education",
            title = "स्कूलों में नई पदस्थापना नीति जारी",
            shortDescription = "छत्तीसगढ़ स्कूल शिक्षा विभाग द्वारा स्थानांतरण एवं पदस्थापना आदेश",
            time = "1d ago",
            type = AlertType.RECRUITMENT,
            isRead = true,
            articleId = "cg_education_new_college"
        ),
        AlertItem(
            id = "alert_4",
            category = "Result",
            title = "CG TET 2026 परिणाम घोषित",
            shortDescription = "व्यापम पोर्टल पर ई-स्कोरकार्ड व कट-ऑफ जारी, यहाँ से देखें",
            time = "2d ago",
            type = AlertType.RESULT,
            isRead = true,
            articleId = "cg_tet_2026_result"
        ),
        AlertItem(
            id = "alert_5",
            category = "CGPSC",
            title = "राज्य सेवा परीक्षा 2026 की तैयारी गाइड",
            shortDescription = "डिप्टी कलेक्टर व डीएसपी पदों हेतु विस्तृत सिलेबस एवं रणनीति",
            time = "2d ago",
            type = AlertType.RECRUITMENT,
            isRead = true,
            articleId = "cgpsc_prelims_2026"
        ),
        AlertItem(
            id = "alert_6",
            category = "Admit Card",
            title = "सब-इंजीनियर परीक्षा के प्रवेश पत्र जारी",
            shortDescription = "14 सितंबर की परीक्षा हेतु व्यापम पोर्टल से एडमिट कार्ड डाउनलोड करें",
            time = "3d ago",
            type = AlertType.ADMIT_CARD,
            isRead = true,
            articleId = "cg_admit_card_sub_engineer"
        ),
        AlertItem(
            id = "alert_7",
            category = "CG Police",
            title = "आरक्षक 5967 पदों की शारीरिक दक्षता तिथि",
            shortDescription = "15 सितंबर से 5 संभागों में ग्राउंड टेस्ट शुरू होंगे",
            time = "4d ago",
            type = AlertType.EXAM_DATE,
            isRead = true,
            articleId = "cg_police_constable_2026"
        )
    )

    private val _alerts = MutableStateFlow(initialAlerts)
    val alerts = _alerts.asStateFlow()

    override fun getAlertsStream(): Flow<List<AlertItem>> = alerts

    override fun getUnreadCountStream(): Flow<Int> {
        return alerts.map { list -> list.count { !it.isRead } }
    }

    override suspend fun markAsRead(alertId: String) {
        _alerts.value = _alerts.value.map { item ->
            if (item.id == alertId) item.copy(isRead = true) else item
        }
    }

    override suspend fun markAllAsRead() {
        _alerts.value = _alerts.value.map { it.copy(isRead = true) }
    }
}
