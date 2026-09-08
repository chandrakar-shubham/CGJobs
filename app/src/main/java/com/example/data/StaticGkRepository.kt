package com.example.data

import android.content.Context
import android.util.Log
import com.example.data.api.ServerConfig
import com.example.data.api.model.toDomain
import com.example.model.StaticGkCard
import kotlinx.coroutines.CoroutineScope
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.flow.Flow
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.flow.map
import kotlinx.coroutines.launch
import kotlinx.coroutines.withContext

class StaticGkRepository(
    private val context: Context? = null
) {

    private val scope = CoroutineScope(Dispatchers.IO)

    private val staticGkData = listOf(
        StaticGkCard(
            id = "gk_chitrakote_waterfall",
            title = "चित्रकोट जलप्रपात: भारत का नियाग्रा",
            category = "भूगोल",
            summary = "चित्रकोट जलप्रपात छत्तीसगढ़ राज्य के बस्तर जिले में जगदलपुर के समीप इंद्रावती नदी पर स्थित है। यह भारत का सबसे चौड़ा प्राकृतिक जलप्रपात है जिसे 'भारत का नियाग्रा' भी कहा जाता है।",
            facts = listOf(
                "नदी: इंद्रावती नदी (गोदावरी की प्रमुख सहायक नदी)",
                "जिला: बस्तर (जगदलपुर से 38 किमी)",
                "ऊंचाई: लगभग 90 फीट (29 मीटर)",
                "विशेषता: वर्षा ऋतु में इसका आकार घोड़े की नाल जैसा हो जाता है।"
            ),
            examTip = "बार-बार पूछा जाने वाला प्रश्न: चित्रकोट जलप्रपात किस नदी पर और किस जिले में स्थित है? सही उत्तर: इंद्रावती नदी, बस्तर जिला।",
            relatedExam = "CGPSC प्री, व्यापम RDP26, हॉस्टल वार्डन"
        ),
        StaticGkCard(
            id = "gk_sirpur_monuments",
            title = "सिरपुर: ऐतिहासिक लक्ष्मण मंदिर और बौद्ध विहार",
            category = "इतिहास",
            summary = "महासमुंद जिले में महानदी के तट पर स्थित सिरपुर (प्राचीन श्रीपुर) 5वीं से 8वीं शताब्दी में दक्षिण कोसल की राजधानी रहा है। यहाँ लाल ईंटों से निर्मित प्रसिद्ध लक्ष्मण मंदिर भारत की प्राचीन स्थापत्य कला का अनुपम उदाहरण है।",
            facts = listOf(
                "शासक: पांडुवंश के शासक महाशिवगुप्त बालार्जुन का स्वर्ण काल",
                "निर्माणकर्ता: महारानी वासटा देवी ने अपने पति हर्षगुप्त की स्मृति में बनवाया",
                "शैली: नागर शैली के लाल ईंटों का मंदिर",
                "बौद्ध केंद्र: चीनी यात्री ह्वेनसांग (Hiuen Tsang) ने 639 ईस्वी में सिरपुर की यात्रा की थी।"
            ),
            examTip = "परीक्षा तथ्य: सिरपुर का लक्ष्मण मंदिर विष्णु को समर्पित है तथा यह नागर शैली का ईंट-निर्मित मंदिर है।",
            relatedExam = "CGPSC मुख्य एवं प्रारंभिक परीक्षा"
        ),
        StaticGkCard(
            id = "gk_bastar_dussehra",
            title = "बस्तर दशहरा: विश्व का सबसे लंबा चलने वाला पर्व",
            category = "संस्कृति व जनजाति",
            summary = "बस्तर दशहरा 75 दिनों तक चलने वाला विश्व का सबसे लंबा और अनूठा सांस्कृतिक पर्व है। इसमें रावण का वध नहीं किया जाता, बल्कि माँ दंतेश्वरी देवी और स्थानीय देवी-देवताओं की पूजा की जाती है।",
            facts = listOf(
                "अवधि: 75 दिन (श्रावण अमावस्या हरेली से प्रारंभ होकर अश्विन शुक्ल त्रयोदशी तक)",
                "मुख्य रस्में: पाट जात्रा (लकड़ी पूजा), डेरी गड़ाई, काछिन गादी, बेल पूजा",
                "रथ परिक्रमा: स्थानीय आदिवासियों द्वारा हाथ से निर्मित 8 पहियों वाले विशाल काष्ठ रथ का संचालन",
                "आरंभकर्ता: चालुक्य (काकतीय) राजा पुरुषोत्तम देव (15वीं शताब्दी)"
            ),
            examTip = "अक्सर पूछा जाता है: काछिन गादी रस्म में किस देवी की अनुमति ली जाती है? उत्तर: काछिन देवी (कांटों की झूले पर पूजा)।",
            relatedExam = "CGPSC, व्यापम पुलिस, पटवारी"
        ),
        StaticGkCard(
            id = "gk_guru_ghasidas",
            title = "संत शिरोमणि गुरु घासीदास एवं सतनाम पंथ",
            category = "इतिहास",
            summary = "संत गुरु घासीदास जी (1756-1850) ने छत्तीसगढ़ में सामाजिक समानता, सत्य, अहिंसा और मानव मात्र की एकता का संदेश दिया। उन्होंने 'मनखे-मनखे एक समान' का क्रांतिकारी सिद्धांत प्रतिपादित किया।",
            facts = listOf(
                "जन्म स्थान: गिरौदपुरी धाम (बलौदाबाजार-भाटापारा जिला)",
                "मूल मंत्र: 'सतनाम' एवं 'मनखे-मनखे एक समान'",
                "तपोभूमि: छाता पहाड़ एवं औराधौरा",
                "जैतखाम: सतनाम पंथ का श्वेत ध्वज युक्त पवित्र प्रतीक चिन्ह, जिसकी ऊंचाई कुतुबमीनार से भी अधिक है।"
            ),
            examTip = "परीक्षा उपयोगी: गिरौदपुरी का जैतखाम 77 मीटर (243 फीट) ऊंचा है, जो विश्व के सबसे ऊंचे स्मारकों में से एक है।",
            relatedExam = "CGPSC राज्य सेवा, शिक्षक भर्ती"
        ),
        StaticGkCard(
            id = "gk_chhattisgarh_state_symbols",
            title = "छत्तीसगढ़ राज्य के राजकीय प्रतीक चिन्ह",
            category = "छत्तीसगढ़ विशेष",
            summary = "1 नवंबर 2000 को मध्य प्रदेश से पृथक होकर छत्तीसगढ़ भारत का 26वां राज्य बना। राज्य के विशिष्ट प्राकृतिक और सांस्कृतिक धरोहर को दर्शाने के लिए प्रतीक चिन्ह निर्धारित किए गए हैं।",
            facts = listOf(
                "राजकीय पशु: वनभैंसा (Wild Water Buffalo - Bubalus arnee)",
                "राजकीय पक्षी: पहाड़ी मैना (Bastar Hill Myna - Gracula religiosa peninsularis)",
                "राजकीय वृक्ष: साल (सरई - Shorea robusta)",
                "राज्य गीत: 'अरपा पैरी के धार, महानदी हे अपार' (रचयिता: डॉ. नरेंद्र देव वर्मा)"
            ),
            examTip = "प्रतीक चिन्ह में 36 किले (परकोटे) हरे रंग में, ऊर्जा का प्रतीक नीले रंग में और भारत का सारनाथ सिंह स्तंभ लाल रंग में अंकित है।",
            relatedExam = "सभी राज्य प्रतियोगी परीक्षाएं"
        ),
        StaticGkCard(
            id = "gk_bhoramdeo_temple",
            title = "भोरमदेव मंदिर: छत्तीसगढ़ का खजुराहो",
            category = "इतिहास",
            summary = "कबीरधाम (कवर्धा) जिले में मैकल पर्वत श्रृंखला के बीच स्थित 11वीं शताब्दी का भोरमदेव मंदिर भगवान शिव को समर्पित है। अपनी अद्भुत मूर्तिकला व कामुक शिल्पकला के कारण इसे 'छत्तीसगढ़ का खजुराहो' कहा जाता है।",
            facts = listOf(
                "निर्माण काल: 11वीं शताब्दी (1089 ईस्वी)",
                "राजवंश: फणि नागवंश (राजा गोपाल देव के शासनकाल में लक्ष्मण देव द्वारा)",
                "वास्तुकला: नागर शैली (चंदेल एवं कलचुरी शैली का अद्भुत सम्मिश्रण)",
                "समीपवर्ती मंदिर: मड़वा महल (दूल्हा देव मंदिर) एवं छेरकी महल"
            ),
            examTip = "परीक्षा प्रश्न: मड़वा महल का निर्माण रामचंद्र देव ने कलचुरी राजकुमारी अंबिका देवी के विवाह उत्सव में करवाया था।",
            relatedExam = "CGPSC, ADO, व्यापम"
        ),
        StaticGkCard(
            id = "gk_chhattisgarh_minerals",
            title = "खनिज संपदा: टिन और लौह अयस्क में शीर्ष स्थान",
            category = "राजव्यवस्था व अर्थव्यवस्था",
            summary = "छत्तीसगढ़ को खनिजों का कटोरा कहा जाता है। भारत में 100% टिन अयस्क (कैसिटराइट) का उत्पादन केवल छत्तीसगढ़ राज्य के दंतेवाड़ा और सुकमा जिले में होता है।",
            facts = listOf(
                "टिन (Tin): भारत का एकमात्र टिन उत्पादक राज्य (दंतेवाड़ा का कटेकल्याण क्षेत्र)",
                "लौह अयस्क: बैलाडीला (दंतेवाड़ा) - उच्च गुणवत्ता का हेमेटाइट अयस्क जो विशाखापट्टनम बंदरगाह से जापान भेजा जाता है",
                "कोयला (Coal): कोरबा एवं रायगढ़ क्षेत्र (SECL का मुख्यालय बिलासपुर में स्थित)",
                "हीरा (Diamond): गरियाबंद जिले के पायलीखंड, बेहराडीह और कोदोमाली क्षेत्र"
            ),
            examTip = "बैलाडीला की खदानें NMDC (राष्ट्रीय खनिज विकास निगम) द्वारा संचालित हैं और यहाँ एशिया की सबसे बड़ी यंत्रीकृत खदान है।",
            relatedExam = "CG व्यापम, खनन निरीक्षक, CGPSC"
        ),
        StaticGkCard(
            id = "gk_chhattisgarh_tribes",
            title = "छत्तीसगढ़ की जनजातीय विरासत व घोटुल प्रथा",
            category = "संस्कृति व जनजाति",
            summary = "छत्तीसगढ़ में कुल 42 जनजातियां निवास करती हैं, जिनमें गोंड सबसे बड़ा जनजातीय समूह है। बस्तर की मुरिया जनजाति में 'घोटुल' सामाजिक व सांस्कृतिक युवागृह की विश्वप्रसिद्ध संस्था है।",
            facts = listOf(
                "विशेष पिछड़ी जनजातियां (PVTG): 7 जनजातियां (कमर, बैगा, पहाड़ी कोरवा, बिरहोर, अबुझमाड़िया + भुंजिया व पंडो)",
                "घोटुल: मुरिया जनजाति का युवागृह जिसके देवता 'लिंगोपेन' हैं। वेरियर एल्विन ने इस पर 'द मुरिया एंड देयर घोटुल' पुस्तक लिखी।",
                "तीजा, पोला, हरेली: राज्य के प्रमुख पारंपरिक कृषि व लोक पर्व",
                "करमा नृत्य: उरांव, गोंड व बैगा जनजातियों का प्रमुख पारंपरिक नृत्य"
            ),
            examTip = "वेरियर एल्विन की प्रसिद्ध पुस्तक: 'द बैगाज' (1939) एवं 'द अगरिया'।",
            relatedExam = "CGPSC मेन्स पेपर 6, व्यापम सब-इंस्पेक्टर"
        ),
        StaticGkCard(
            id = "gk_national_parks_cg",
            title = "राष्ट्रीय उद्यान एवं बाघ अभयारण्य (Tiger Reserves)",
            category = "भूगोल",
            summary = "छत्तीसगढ़ में 3 राष्ट्रीय उद्यान और 4 घोषित टाइगर रिजर्व हैं। गुरु घासीदास राष्ट्रीय उद्यान व तमोर पिंगला अभयारण्य को मिलाकर देश का 54वां टाइगर रिजर्व बनाया गया है।",
            facts = listOf(
                "कांगेर घाटी राष्ट्रीय उद्यान: बस्तर (यहाँ कुटुमसर गुफा में अंधी मछली 'कपिओला शंकराई' पाई जाती है)",
                "इंद्रावती राष्ट्रीय उद्यान: बीजापुर (राज्य का पहला टाइगर रिजर्व एवं कुटरू गेम सेंचुरी)",
                "गुरु घासीदास राष्ट्रीय उद्यान: कोरिया/सूरजपुर (राज्य का सबसे बड़ा राष्ट्रीय उद्यान)",
                "कुटुमसर गुफा: प्रोफेसर शंकर तिवारी द्वारा खोजी गई चूना पत्थर की प्राकृतिक गुफा (स्टैलेक्टाइट व स्टैलेग्माइट)"
            ),
            examTip = "कांगेर घाटी में तीरथगढ़ जलप्रपात मुनगाबहार नदी पर 300 फीट ऊंचाई से गिरता है।",
            relatedExam = "CGPSC वन सेवा, रेंजर, व्यापम"
        )
    )

    private val _gkStream = MutableStateFlow(staticGkData)
    val gkStream = _gkStream.asStateFlow()

    init {
        scope.launch {
            tryFetchFromServer()
        }
    }

    suspend fun tryFetchFromServer(): Boolean = withContext(Dispatchers.IO) {
        try {
            val api = ServerConfig.getApiService(context)
            val response = api.getStaticGk()
            if (response.success && !response.cards.isNullOrEmpty()) {
                val mapped = response.cards.map { it.toDomain() }
                val savedIds = _gkStream.value.filter { it.isSaved }.map { it.id }.toSet()
                _gkStream.value = mapped.map { item ->
                    if (savedIds.contains(item.id)) item.copy(isSaved = true) else item
                }
                Log.d("StaticGkRepo", "Fetched ${mapped.size} Static GK items from server")
                return@withContext true
            }
        } catch (e: Exception) {
            Log.w("StaticGkRepo", "Server sync notice for GK: ${e.message}")
        }
        return@withContext false
    }

    fun getGkByCategory(category: String): Flow<List<StaticGkCard>> {
        return _gkStream.map { list ->
            val clean = category.trim()
            if (clean == "सभी (All)" || clean == "सभी" || clean.equals("All", ignoreCase = true) || clean.startsWith("सभी") || clean.startsWith("All")) list
            else list.filter {
                it.category.equals(clean, ignoreCase = true) ||
                clean.contains(it.category, ignoreCase = true) ||
                it.category.contains(clean, ignoreCase = true)
            }
        }
    }

    fun toggleSave(id: String) {
        _gkStream.value = _gkStream.value.map { item ->
            if (item.id == id) item.copy(isSaved = !item.isSaved)
            else item
        }
    }

    suspend fun refreshGk() {
        tryFetchFromServer()
    }
}
