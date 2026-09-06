package com.example.data

import com.example.model.AppCategory
import com.example.model.AppSection
import com.example.model.ImportantDates
import com.example.model.JobUpdate
import kotlinx.coroutines.flow.Flow
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.flow.map

class MockNewsRepository : NewsRepository {

    private val initialUpdates = listOf(
        JobUpdate(
            id = "cgssb_teacher_2026",
            title = "CGSSB शिक्षक भर्ती 2026 के लिए नोटिफिकेशन जारी",
            summary = "छत्तीसगढ़ कर्मचारी चयन मंडल (CGSSB) ने शिक्षक भर्ती 2026 के लिए आधिकारिक अधिसूचना जारी कर दी है। इस भर्ती के तहत विभिन्न पदों पर नियुक्ति की जाएगी। इच्छुक उम्मीदवार निर्धारित तिथि के भीतर ऑनलाइन आवेदन कर सकते हैं।",
            detailedContent = """
छत्तीसगढ़ कर्मचारी चयन मंडल (CGSSB) रायपुर द्वारा प्रदेश के शासकीय प्राथमिक, माध्यमिक और उच्चतर माध्यमिक विद्यालयों में शिक्षकों के 12,489 पदों पर सीधी भर्ती हेतु आधिकारिक विस्तृत विज्ञापन जारी कर दिया गया है। 

इस भर्ती अभियान के माध्यम से सहायक शिक्षक, शिक्षक (ई व टी संवर्ग) तथा विभिन्न विषयों के व्याख्याताओं के रिक्त पदों को भरा जाएगा। बस्तर एवं सरगुजा संभाग के स्थानीय निवासियों के लिए विशेष प्राथमिकता प्रावधान लागू रहेंगे।

अभ्यर्थी CGSSB की आधिकारिक वेबसाइट पर जाकर ऑनलाइन आवेदन प्रक्रिया पूरी कर सकते हैं। आवेदन शुल्क छत्तीसगढ़ राज्य के मूल निवासियों के लिए शून्य (निशुल्क) रखा गया है।

ऑनलाइन आवेदन करते समय सभी शैक्षणिक प्रमाण पत्र, जाति, निवास एवं रोजगार पंजीयन क्रमांक सावधानीपूर्वक दर्ज करें। त्रुटि सुधार हेतु विशेष अवसर प्रदान किया जाएगा।
            """.trimIndent(),
            category = "CGSSB",
            source = "CGSSB Raipur",
            sourceUrl = "https://vyapam.cgstate.gov.in",
            publishedAt = "05 Sept 2026",
            relativeTime = "2h ago",
            isBreaking = true,
            isNew = true,
            vacancies = "12,489",
            eligibility = "संबंधित विषय में स्नातक + B.Ed / D.El.Ed + CG TET / CTET",
            ageLimit = "21 - 40 वर्ष (आरक्षित वर्ग को नियमानुसार छूट)",
            selectionProcess = "लिखित परीक्षा + दस्तावेज सत्यापन + मेरिट सूची",
            officialNotificationUrl = "https://cgstate.gov.in/notifications/teacher-2026.pdf",
            applyUrl = "https://cgstate.gov.in/apply",
            importantDates = ImportantDates(
                applicationStart = "01 सितम्बर 2026",
                lastDate = "30 सितम्बर 2026",
                examDate = "15 नवम्बर 2026",
                admitCardDate = "05 नवम्बर 2026"
            ),
            isSaved = true
        ),
        JobUpdate(
            id = "cg_vyapam_patwari_2026",
            title = "CG Vyapam ने पटवारी भर्ती 2026 का एग्जाम डेट जारी किया",
            summary = "छत्तीसगढ़ व्यावसायिक परीक्षा मंडल (CG Vyapam) ने राजस्व विभाग अंतर्गत पटवारी परीक्षा 2026 की परीक्षा तिथि व कार्यक्रम घोषित किया। परीक्षा राज्य के सभी 33 जिला मुख्यालयों में आयोजित की जाएगी।",
            detailedContent = """
छत्तीसगढ़ व्यावसायिक परीक्षा मंडल (व्यापम) द्वारा राजस्व एवं आपदा प्रबंधन विभाग के अंतर्गत पटवारी प्रशिक्षण चयन परीक्षा 2026 (RDP26) की समय सारिणी जारी कर दी गई है।

प्रदेश के विभिन्न जिलों में 350 से अधिक पदों के लिए यह परीक्षा आयोजित की जाएगी। परीक्षा दो पालियों में ओएमआर शीट आधारित ऑफलाइन मोड में ली जाएगी। 

सिलेबस में सामान्य ज्ञान, छत्तीसगढ़ का सामान्य ज्ञान, कंप्यूटर ज्ञान, गणित एवं मानसिक योग्यता तथा हिंदी-अंग्रेजी व्याकरण शामिल हैं। 

परीक्षा के 10 दिन पूर्व व्यापम की वेबसाइट से प्रवेश पत्र डाउनलोड किए जा सकेंगे।
            """.trimIndent(),
            category = "CG Vyapam",
            source = "CG Vyapam",
            sourceUrl = "https://vyapam.cgstate.gov.in",
            publishedAt = "04 Sept 2026",
            relativeTime = "5h ago",
            isBreaking = false,
            isNew = true,
            vacancies = "350+",
            eligibility = "12वीं उत्तीर्ण + 1 वर्षीय कंप्यूटर डिप्लोमा (DCA/PGDCA)",
            ageLimit = "18 - 35 वर्ष",
            selectionProcess = "वस्तुनिष्ठ लिखित परीक्षा (150 अंक) + मेरिट",
            officialNotificationUrl = "https://vyapam.cgstate.gov.in/patwari-schedule.pdf",
            applyUrl = "https://vyapam.cgstate.gov.in",
            importantDates = ImportantDates(
                applicationStart = "15 अगस्त 2026",
                lastDate = "05 सितम्बर 2026",
                examDate = "18 अक्टूबर 2026",
                admitCardDate = "08 अक्टूबर 2026"
            ),
            isSaved = true
        ),
        JobUpdate(
            id = "cg_tet_2026_syllabus",
            title = "CG TET 2026: प्राथमिक व उच्च प्राथमिक शिक्षक पात्रता सिलेबस",
            summary = "राज्य शैक्षिक अनुसंधान और प्रशिक्षण परिषद (SCERT) एवं व्यापम ने CG TET 2026 का नवीनतम विस्तृत पाठ्यक्रम एवं अंक विभाजन सार्वजनिक किया।",
            detailedContent = """
छत्तीसगढ़ शिक्षक पात्रता परीक्षा (CG TET) 2026 के लिए पेपर-1 (कक्षा 1 से 5) तथा पेपर-2 (कक्षा 6 से 8) का अद्यतन सिलेबस जारी हुआ है।

बाल विकास एवं शिक्षाशास्त्र, भाषा-1 (हिंदी), भाषा-2 (अंग्रेजी), गणित और पर्यावरण अध्ययन के 150 बहुविकल्पीय प्रश्न पूछे जाएंगे। परीक्षा में कोई नकारात्मक अंकन (Negative Marking) नहीं होगा।

सामान्य वर्ग के लिए न्यूनतम 60% (90 अंक) तथा आरक्षित वर्ग (SC/ST/OBC) के लिए 50% (75 अंक) अर्हक अंक निर्धारित हैं। प्रमाण पत्र आजीवन वैध रहेगा।
            """.trimIndent(),
            category = "Syllabus",
            source = "SCERT Raipur",
            sourceUrl = "https://scert.cg.gov.in",
            publishedAt = "02 Sept 2026",
            relativeTime = "1d ago",
            isBreaking = false,
            isNew = false,
            vacancies = "पात्रता परीक्षा",
            eligibility = "D.El.Ed / B.Ed अध्ययनरत अथवा उत्तीर्ण अभ्यर्थी",
            ageLimit = "न्यूनतम 18 वर्ष (अधिकतम कोई सीमा नहीं)",
            selectionProcess = "टीईटी पात्रता परीक्षा (150 मिनट, 150 प्रश्न)",
            officialNotificationUrl = "https://vyapam.cgstate.gov.in/tet-syllabus.pdf",
            applyUrl = "https://vyapam.cgstate.gov.in",
            importantDates = ImportantDates(
                applicationStart = "20 अगस्त 2026",
                lastDate = "10 सितम्बर 2026",
                examDate = "25 अक्टूबर 2026",
                admitCardDate = "15 अक्टूबर 2026"
            ),
            isSaved = true
        ),
        JobUpdate(
            id = "cg_education_new_college",
            title = "छत्तीसगढ़ में नए कॉलेजों की स्वीकृति एवं प्राध्यापक पदों का सृजन",
            summary = "उच्च शिक्षा विभाग ने 18 नए शासकीय महाविद्यालयों की स्थापना और 720 शैक्षणिक व गैर-शैक्षणिक संवर्ग के पदों के सृजन हेतु प्रशासनिक स्वीकृति प्रदान की।",
            detailedContent = """
छत्तीसगढ़ शासन उच्च शिक्षा विभाग द्वारा प्रदेश के ग्रामीण व आदिवासी अंचलों में उच्च शिक्षा की सुलभता हेतु 18 नवीन स्नातक महाविद्यालय शुरू करने का फैसला लिया गया है।

इसके साथ ही सहायक प्राध्यापक, क्रीड़ा अधिकारी, ग्रंथपाल, प्रयोगशाला परिचारक एवं सहायक ग्रेड-3 के 720 नवीन पदों के भर्ती प्रस्ताव को वित्त विभाग से हरी झंडी मिल चुकी है। 

आगामी सत्र से इन पदों पर CGPSC एवं व्यापम के माध्यम से भर्ती प्रक्रिया शुरू की जाएगी।
            """.trimIndent(),
            category = "Education",
            source = "उच्च शिक्षा विभाग",
            sourceUrl = "https://highereducation.cg.gov.in",
            publishedAt = "01 Sept 2026",
            relativeTime = "2d ago",
            isBreaking = false,
            isNew = false,
            vacancies = "720 पद",
            eligibility = "पदानुसार NET/SET/Ph.D अथवा 12वीं/स्नातक",
            ageLimit = "21 - 40 वर्ष",
            selectionProcess = "CGPSC परीक्षा / साक्षात्कार",
            officialNotificationUrl = "https://highereducation.cg.gov.in/order-2026.pdf",
            applyUrl = null,
            importantDates = ImportantDates(
                applicationStart = "अक्टूबर 2026 (संभावित)",
                lastDate = "नवंबर 2026 (संभावित)",
                examDate = "दिसंबर 2026",
                admitCardDate = null
            ),
            isSaved = true
        ),
        JobUpdate(
            id = "cg_tet_2026_result",
            title = "CG TET 2026 परिणाम घोषित: यहाँ से अपना मेरिट व स्कोरकार्ड देखें",
            summary = "छत्तीसगढ़ व्यापम ने राज्य शिक्षक पात्रता परीक्षा का फाइनल रिजल्ट और अंतिम उत्तर कुंजी जारी कर दी है। परीक्षार्थी अपने रोल नंबर से स्कोर देख सकते हैं।",
            detailedContent = """
व्यापम द्वारा आयोजित छत्तीसगढ़ शिक्षक पात्रता परीक्षा (CG TET) का परिणाम आधिकारिक पोर्टल पर अपलोड कर दिया गया है। 

कुल 3 लाख 85 हजार परीक्षार्थियों ने यह परीक्षा दी थी। दावा-आपत्तियों के निराकरण के उपरांत विषय विशेषज्ञों की अंतिम समीक्षा के आधार पर 4 प्रश्नों को विलोपित किया गया है तथा बोनस अंक प्रदान किए गए हैं।

सफल उम्मीदवार अपना ई-प्रमाण पत्र व्यापम प्रोफाइल लॉगिन के माध्यम से आजीवन वैधता के साथ डाउनलोड कर सकेंगे।
            """.trimIndent(),
            category = "Result",
            source = "CG Vyapam",
            sourceUrl = "https://vyapam.cgstate.gov.in",
            publishedAt = "31 Aug 2026",
            relativeTime = "2d ago",
            isBreaking = false,
            isNew = false,
            vacancies = "योग्यता प्रमाण पत्र",
            eligibility = "परीक्षा में सम्मिलित सभी अभ्यर्थी",
            ageLimit = "लागू नहीं",
            selectionProcess = "अंतिम उत्तर कुंजी उपरांत परिणाम घोषित",
            officialNotificationUrl = "https://vyapam.cgstate.gov.in/result/tet2026",
            applyUrl = null,
            importantDates = ImportantDates(
                applicationStart = "संपन्न",
                lastDate = "संपन्न",
                examDate = "15 जुलाई 2026",
                resultDate = "31 अगस्त 2026"
            ),
            isSaved = false
        ),
        JobUpdate(
            id = "cgpsc_prelims_2026",
            title = "CGPSC राज्य सेवा परीक्षा 2026 की तैयारी गाइड व विस्तृत विज्ञापन",
            summary = "छत्तीसगढ़ लोक सेवा आयोग (CGPSC) ने डिप्टी कलेक्टर, डीएसपी एवं नायब तहसीलदार सहित 242 पदों हेतु राज्य सेवा परीक्षा (SSE) की अधिसूचना जारी की।",
            detailedContent = """
CGPSC रायपुर द्वारा राज्य सेवा परीक्षा 2026 के लिए विस्तृत विज्ञापन जारी कर दिया गया है। 

इस वर्ष डिप्टी कलेक्टर के 15 पद, उप पुलिस अधीक्षक (DSP) के 22 पद, वाणिज्यिक कर अधिकारी के 18 पद तथा नायब तहसीलदार के 45 पदों सहित कुल 242 प्रशासनिक पदों पर भर्ती की जाएगी।

प्रारंभिक परीक्षा में दो अनिवार्य प्रश्नपत्र (सामान्य अध्ययन एवं एप्टीट्यूड टेस्ट) होंगे। प्रारंभिक परीक्षा में सफल अभ्यर्थियों को मुख्य परीक्षा एवं साक्षात्कार हेतु बुलाया जाएगा।
            """.trimIndent(),
            category = "CGPSC",
            source = "CGPSC Raipur",
            sourceUrl = "https://psc.cg.gov.in",
            publishedAt = "30 Aug 2026",
            relativeTime = "3d ago",
            isBreaking = true,
            isNew = true,
            vacancies = "242 पद",
            eligibility = "किसी भी मान्यता प्राप्त विश्वविद्यालय से स्नातक उपाधि",
            ageLimit = "21 - 30 वर्ष (छत्तीसगढ़ निवासियों हेतु अधिकतम 40 वर्ष)",
            selectionProcess = "प्रारंभिक परीक्षा + मुख्य परीक्षा (7 पेपर्स) + साक्षात्कार",
            officialNotificationUrl = "https://psc.cg.gov.in/sse-2026-advt.pdf",
            applyUrl = "https://psc.cg.gov.in",
            importantDates = ImportantDates(
                applicationStart = "01 दिसंबर 2026",
                lastDate = "30 दिसंबर 2026",
                examDate = "08 फरवरी 2027",
                admitCardDate = "25 जनवरी 2027"
            ),
            isSaved = false
        ),
        JobUpdate(
            id = "cg_police_constable_2026",
            title = "CG पुलिस आरक्षक 5967 पदों पर शारीरिक दक्षता परीक्षा (PET) शेड्यूल",
            summary = "छत्तीसगढ़ पुलिस मुख्यालय रायपुर ने जिला पुलिस बल आरक्षक संवर्ग भर्ती हेतु शारीरिक नापजोख एवं दक्षता परीक्षा (PET/PST) की संभागवार तिथियां घोषित की।",
            detailedContent = """
छत्तीसगढ़ पुलिस बल में आरक्षक (GD), चालक एवं ट्रेडमैन के कुल 5967 रिक्त पदों पर भर्ती प्रक्रिया के द्वितीय चरण का टाइम-टेबल घोषित किया गया है।

शारीरिक दक्षता परीक्षा रायपुर, दुर्ग, बिलासपुर, जगदलपुर और सरगुजा रेंज के निर्धारित पुलिस ग्राउंड्स में आयोजित होगी। इसमें 100 मीटर दौड़, 800 मीटर दौड़, लंबी कूद, ऊंची कूद एवं गोला फेंक की स्पर्धाएं होंगी।

उम्मीदवार अपने एडमिट कार्ड में अंकित रेंज केंद्र पर निर्धारित समय पर सभी मूल दस्तावेजों के साथ उपस्थित हों।
            """.trimIndent(),
            category = "Police",
            source = "CG Police PHQ",
            sourceUrl = "https://cgpolice.gov.in",
            publishedAt = "29 Aug 2026",
            relativeTime = "4d ago",
            isBreaking = false,
            isNew = true,
            vacancies = "5,967 पद",
            eligibility = "10वीं उत्तीर्ण (आदिवासी वर्ग हेतु 8वीं उत्तीर्ण)",
            ageLimit = "18 - 28 वर्ष (छूट सहित 33 वर्ष)",
            selectionProcess = "दस्तावेज जांच + शारीरिक नापजोख + शारीरिक दक्षता (100 अंक) + लिखित परीक्षा (100 अंक)",
            officialNotificationUrl = "https://cgpolice.gov.in/recruitment/pet-schedule.pdf",
            applyUrl = "https://cgpolice.gov.in",
            importantDates = ImportantDates(
                applicationStart = "संपन्न",
                lastDate = "संपन्न",
                examDate = "15 सितंबर 2026 से 10 अक्टूबर 2026",
                admitCardDate = "05 सितंबर 2026"
            ),
            isSaved = false
        ),
        JobUpdate(
            id = "cg_hostel_warden_answer_key",
            title = "छात्रावास अधीक्षक (Hostel Warden) भर्ती परीक्षा: मॉडल उत्तर जारी",
            summary = "व्यापम ने आदिम जाति तथा अनुसूचित जाति विकास विभाग अंतर्गत 300 छात्रावास अधीक्षक श्रेणी 'द' भर्ती परीक्षा की मॉडल आंसर-की जारी की।",
            detailedContent = """
छत्तीसगढ़ आदिम जाति कल्याण विभाग में 300 पदों के लिए आयोजित छात्रावास अधीक्षक भर्ती परीक्षा का मॉडल उत्तर सेट A, B, C, D का प्रकाशन व्यापम वेबसाइट पर कर दिया गया है।

अभ्यर्थी 08 सितंबर संध्या 5 बजे तक प्रति प्रश्न निर्धारित शुल्क के साथ ऑनलाइन दावा-आपत्ति दर्ज कर सकते हैं। डाक या व्यक्तिगत रूप से दिए गए अभ्यावेदन स्वीकार नहीं किए जाएंगे।

कंप्यूटर संबंधित 50 प्रश्नों में न्यूनतम 25 अंक प्राप्त करना अनिवार्य अर्हता रखी गई थी।
            """.trimIndent(),
            category = "Answer Key",
            source = "CG Vyapam",
            sourceUrl = "https://vyapam.cgstate.gov.in",
            publishedAt = "28 Aug 2026",
            relativeTime = "5d ago",
            isBreaking = false,
            isNew = false,
            vacancies = "300 पद",
            eligibility = "12वीं उत्तीर्ण + कंप्यूटर प्रशिक्षित",
            ageLimit = "21 - 35 वर्ष",
            selectionProcess = "लिखित परीक्षा (150 अंक) + मेरिट",
            officialNotificationUrl = "https://vyapam.cgstate.gov.in/anskey/thw2026.pdf",
            applyUrl = "https://vyapam.cgstate.gov.in",
            importantDates = ImportantDates(
                applicationStart = "संपन्न",
                lastDate = "दावा आपत्ति: 08 सितम्बर 2026",
                examDate = "18 अगस्त 2026",
                resultDate = "सितंबर अंत तक संभावित"
            ),
            isSaved = false
        ),
        JobUpdate(
            id = "cg_current_affairs_bastar",
            title = "छत्तीसगढ़ समसामयिकी: बस्तर ओलंपिक्स और महतारी वंदन योजना अपडेट",
            summary = "प्रतियोगी परीक्षाओं की दृष्टि से अत्यंत उपयोगी: बस्तर संभाग में पारंपरिक खेल महाकुंभ 'बस्तर ओलंपिक्स' और महतारी वंदन योजना की 7वीं किस्त का विवरण।",
            detailedContent = """
छत्तीसगढ़ लोक सेवा आयोग (CGPSC) एवं व्यापम की आगामी परीक्षाओं के लिए महत्वपूर्ण समसामयिक घटनाक्रम:

1. बस्तर ओलंपिक्स: माओवाद प्रभावित अंचलों में युवाओं को मुख्यधारा से जोड़ने एवं पारंपरिक जनजातीय खेलों (गेड़ी दौड़, रस्साकशी, तीरंदाजी, कबड्डी) को बढ़ावा देने हेतु 7 जिलों में भव्य आयोजन।
2. महतारी वंदन योजना: प्रदेश की 70 लाख से अधिक पात्र महिलाओं के खाते में प्रतिमाह 1,000 रुपये डीबीटी के माध्यम से सफलतापूर्वक अंतरित।
3. अमृत भारत स्टेशन योजना के तहत छत्तीसगढ़ के बिलासपुर, रायपुर, दुर्ग सहित 32 रेलवे स्टेशनों के पुनर्विकास की प्रगति रिपोर्ट।
4. गुरु घासीदास राष्ट्रीय उद्यान व तमोर पिंगला को संयुक्त रूप से देश का 56वां टाइगर रिजर्व अधिसूचित किए जाने की प्रक्रिया।
            """.trimIndent(),
            category = "Current Affairs",
            source = "CGJobs Editorial Team",
            sourceUrl = "https://dprcg.gov.in",
            publishedAt = "27 Aug 2026",
            relativeTime = "6d ago",
            isBreaking = false,
            isNew = true,
            vacancies = "अध्ययन सामग्री",
            eligibility = "समस्त प्रतियोगी परीक्षार्थियों हेतु उपयोगी",
            ageLimit = "लागू नहीं",
            selectionProcess = "प्रतियोगी परीक्षा विशेष",
            officialNotificationUrl = "https://dprcg.gov.in/press-release",
            applyUrl = null,
            importantDates = ImportantDates(
                applicationStart = "दैनिक अध्ययन",
                lastDate = "साप्ताहिक संकलन",
                examDate = "आगामी सभी परीक्षाएं"
            ),
            isSaved = false
        ),
        JobUpdate(
            id = "cg_ca_tiger_reserve",
            title = "गुरु घासीदास-तमोर पिंगला: छत्तीसगढ़ का चौथा व देश का 56वां टाइगर रिजर्व अधिसूचित",
            summary = "राष्ट्रीय बाघ संरक्षण प्राधिकरण (NTCA) ने गुरु घासीदास राष्ट्रीय उद्यान और तमोर पिंगला अभयारण्य को मिलाकर देश का 56वां टाइगर रिजर्व घोषित किया। 2,829 वर्ग किमी में फैला यह रिजर्व प्रदेश का चौथा टाइगर रिजर्व बना।",
            detailedContent = """
छत्तीसगढ़ सरकार ने गुरु घासीदास राष्ट्रीय उद्यान व तमोर पिंगला वन्यजीव अभयारण्य के संयुक्त क्षेत्र को विधिवत 'गुरु घासीदास-तमोर पिंगला टाइगर रिजर्व' के रूप में अधिसूचित कर दिया है।

भौगोलिक व प्रशासनिक तथ्य:
1. विस्तार: कोरिया, मनेंद्रगढ़-चिरमिरी-भरतपुर (MCB) तथा सूरजपुर जिले।
2. कुल क्षेत्रफल: 2,829.38 वर्ग किलोमीटर (कोर जोन: 2,049.2 वर्ग किमी, बफर जोन: 780.18 वर्ग किमी)।
3. कॉरिडोर: यह टाइगर रिजर्व मध्य प्रदेश के संजय-डुबरी और बांधवगढ़ टाइगर रिजर्व से सीधा वन्यजीव गलियारा बनाता है।

प्रतियोगी परीक्षा विशेष (CGPSC/व्यापम प्रश्नोत्तर):
- छत्तीसगढ़ के 4 टाइगर रिजर्व: इंद्रावती (बीजापुर), अचानकमार (मुंगेली), उदंती-सीतानदी (गरियाबंद) और गुरु घासीदास-तमोर पिंगला।
- तमोर पिंगला का नामकरण पिंगला नाले पर हुआ है, जिसे 1978 में अभयारण्य का दर्जा मिला था।
            """.trimIndent(),
            category = "Current Affairs",
            source = "वन एवं जलवायु परिवर्तन विभाग CG",
            sourceUrl = "https://forest.cg.gov.in",
            publishedAt = "04 Sept 2026",
            relativeTime = "1d ago",
            isBreaking = true,
            isNew = true,
            vacancies = "पर्यावरण व भूगोल विशेष",
            eligibility = "CGPSC व व्यापम परीक्षार्थी",
            ageLimit = "लागू नहीं",
            selectionProcess = "समसामयिक अध्ययन",
            officialNotificationUrl = "https://forest.cg.gov.in/tiger-reserve-notification.pdf",
            applyUrl = null,
            importantDates = ImportantDates(
                applicationStart = "अधिसूचना तिथि: 04 सितंबर 2026",
                lastDate = "दैनिक संकलन",
                examDate = "आगामी परीक्षाएं"
            ),
            isSaved = true
        ),
        JobUpdate(
            id = "cg_ca_tendupatta_hike",
            title = "तेंदूपत्ता संग्रहण पारिश्रमिक दर बढ़कर ₹5,500 प्रति मानक बोरा: 12.5 लाख परिवारों को संबल",
            summary = "राज्य शासन ने तेंदूपत्ता संग्रहण की पारिश्रमिक दर ₹4,000 से बढ़ाकर ₹5,500 प्रति मानक बोरा कर दी है। प्रदेश के 12 लाख 50 हजार से अधिक जनजातीय संग्राहक परिवारों को प्रत्यक्ष आर्थिक लाभ प्राप्त होगा।",
            detailedContent = """
छत्तीसगढ़ राज्य लघु वनोपज सहकारी संघ ने तेंदूपत्ता संग्रहण सत्र 2026 के लिए ऐतिहासिक पारिश्रमिक वृद्धि लागू कर दी है।

महत्वपूर्ण बिंदु:
1. नई संग्रहण दर: ₹5,500 प्रति मानक बोरा (गत वर्ष की तुलना में ₹1,500 की रिकॉर्ड वृद्धि)।
2. लाभान्वित संग्राहक: 902 प्राथमिक वनोपज समितियों से जुड़े 12.5 लाख से अधिक परिवार।
3. चरण पादुका वितरण: महिला संग्राहकों को चरण पादुकाएं और पेयजल हेतु वाटर बॉटल किट का निशुल्क वितरण पुनरारंभ।
4. छात्रवृत्ति व बीमा: संग्राहक परिवारों के मेधावी छात्र-छात्राओं हेतु 'एकलव्य शिक्षा प्रोत्साहन योजना' के तहत छात्रवृत्ति राशि में भी 50% की वृद्धि की गई है।
            """.trimIndent(),
            category = "Current Affairs",
            source = "लघु वनोपज सहकारी संघ CG",
            sourceUrl = "https://cgmfpfed.org",
            publishedAt = "03 Sept 2026",
            relativeTime = "2d ago",
            isBreaking = false,
            isNew = true,
            vacancies = "आर्थिकी व वनोपज विशेष",
            eligibility = "समस्त राज्य प्रतियोगी परीक्षाएं",
            officialNotificationUrl = "https://cgmfpfed.org/rates-2026.pdf",
            applyUrl = null,
            importantDates = ImportantDates(
                applicationStart = "लागू सत्र 2026",
                lastDate = "संपन्न",
                examDate = "आगामी परीक्षाएं"
            ),
            isSaved = false
        ),
        JobUpdate(
            id = "cg_ca_nava_raipur_it_hub",
            title = "नवा रायपुर अटल नगर में ₹10,000 करोड़ का AI व डेटा सेंटर पार्क: 15,000 रोजगार",
            summary = "राज्य की नवीन औद्योगिक विकास नीति 2024-30 के अंतर्गत नवा रायपुर के सेक्टर-22 में 200 एकड़ में अत्याधुनिक ग्रीन डेटा सेंटर एवं चिप डिजाइनिंग हब का शिलान्यास किया गया।",
            detailedContent = """
छत्तीसगढ़ को मध्य भारत का सबसे बड़ा सूचना प्रौद्योगिकी एवं आर्टिफिशियल इंटेलिजेंस हब बनाने की दिशा में नवा रायपुर में विशाल आईटी पार्क का भूमिपूजन संपन्न हुआ।

परियोजना की विशेषताएं:
- प्रस्तावित कुल निवेश: ₹10,000 करोड़ (सार्वजनिक-निजी सहभागिता)।
- रोजगार लक्ष्य: 15,000 प्रत्यक्ष आईटी इंजीनियर्स और 30,000 सहायक तकनीकी रोजगार।
- नवा रायपुर एआई सेंटर ऑफ एक्सीलेंस: ट्रिपल आईटी (IIIT) नया रायपुर और एनआईटी (NIT) रायपुर के शोधार्थियों हेतु इनक्यूबेशन की सुविधा।
- औद्योगिक नीति 2024-30: डेटा सेंटर इकाइयों को 10 वर्षों तक 100% विद्युत शुल्क से छूट एवं 40% पूंजीगत अनुदान दिया जा रहा है।
            """.trimIndent(),
            category = "Current Affairs",
            source = "इलेक्ट्रॉनिक्स एवं आईटी विभाग CG",
            sourceUrl = "https://it.cg.gov.in",
            publishedAt = "01 Sept 2026",
            relativeTime = "4d ago",
            isBreaking = false,
            isNew = true,
            vacancies = "उद्योग व विज्ञान-प्रौद्योगिकी",
            eligibility = "प्रतियोगी परीक्षार्थी",
            officialNotificationUrl = "https://it.cg.gov.in/it-park-mou.pdf",
            applyUrl = null,
            importantDates = ImportantDates(
                applicationStart = "शिलान्यास: सितंबर 2026",
                lastDate = "परियोजना अवधि 3 वर्ष",
                examDate = "आगामी परीक्षाएं"
            ),
            isSaved = false
        ),
        JobUpdate(
            id = "cg_ca_ramlala_darshan",
            title = "श्री रामलला दर्शन योजना: 60,000 श्रद्धालुओं ने पूरी की निशुल्क अयोध्या तीर्थ यात्रा",
            summary = "छत्तीसगढ़ पर्यटन एवं संस्कृति विभाग द्वारा संचालित महत्वाकांक्षी तीर्थ यात्रा योजना के अंतर्गत विशेष आस्था ट्रेनों के माध्यम से अब तक 60,000 से अधिक प्रदेशवासियों ने अयोध्या धाम की यात्रा पूरी की।",
            detailedContent = """
प्रदेश के श्रद्धालुओं की आस्था और धार्मिक पर्यटन को बढ़ावा देने हेतु संचालित 'श्री रामलला दर्शन योजना' ने नया कीर्तिमान स्थापित किया है।

योजना से जुड़े प्रमुख तथ्य:
1. नोडल विभाग: छत्तीसगढ़ पर्यटन मंडल एवं धर्मस्व विभाग।
2. संचालन एजेंसी: आईआरसीटीसी (IRCTC) के समन्वय से रायपुर, बिलासपुर व दुर्ग से विशेष आस्था ट्रेनों का संचालन।
3. पात्रता: 18 से 75 वर्ष आयु वर्ग के छत्तीसगढ़ के मूल निवासी। प्रथम चरण में 55 वर्ष से अधिक उम्र के वरिष्ठ नागरिकों को वरीयता दी गई।
4. संपूर्ण व्यय: यात्रा, खानपान, होटल आवास, स्थानीय बस परिवहन एवं यात्रा बीमा का संपूर्ण व्यय छत्तीसगढ़ सरकार द्वारा वहन किया जा रहा है।
            """.trimIndent(),
            category = "Current Affairs",
            source = "छत्तीसगढ़ पर्यटन मंडल",
            sourceUrl = "https://tourism.cg.gov.in",
            publishedAt = "30 Aug 2026",
            relativeTime = "5d ago",
            isBreaking = false,
            isNew = false,
            vacancies = "संस्कृति व पर्यटन विशेष",
            eligibility = "प्रतियोगी परीक्षार्थी",
            officialNotificationUrl = "https://tourism.cg.gov.in/ramlala-scheme.pdf",
            applyUrl = null,
            importantDates = ImportantDates(
                applicationStart = "योजना प्रारंभ: जनवरी 2024",
                lastDate = "निरंतर जारी",
                examDate = "आगामी परीक्षाएं"
            ),
            isSaved = false
        ),
        JobUpdate(
            id = "cg_ca_national_sports_medals",
            title = "38वें राष्ट्रीय खेल: छत्तीसगढ़ के दल ने तीरंदाजी व तलवारबाजी में जीते 5 पदक",
            summary = "राष्ट्रीय खेल प्रतियोगिता में छत्तीसगढ़ के खेल प्रतिभाओं ने शानदार प्रदर्शन करते हुए तीरंदाजी में 2 स्वर्ण तथा फेंसिंग (तलवारबाजी) में 3 पदक हासिल किए। पदक विजेताओं को शासकीय सेवा में सीधी नियुक्ति की घोषणा।",
            detailedContent = """
38वें राष्ट्रीय खेलों में छत्तीसगढ़ के खिलाड़ियों ने उत्कृष्ट खेल कौशल का प्रदर्शन कर प्रदेश को गौरवान्वित किया है।

प्रमुख उपलब्धियां:
- तीरंदाजी (रिकर्व व्यक्तिगत): बस्तर स्पोर्ट्स अकादमी के युवा तीरंदाज अमित कश्यप ने स्वर्ण पदक जीता।
- तलवारबाजी (महिला टीम ईपी स्पर्धा): छत्तीसगढ़ महिला टीम ने फाइनल में कांस्य पदक अर्जित किया।
- खेल प्रोत्साहन नीति: राज्य सरकार द्वारा राष्ट्रीय स्तर पर स्वर्ण पदक विजेताओं को ₹31 लाख तथा द्वितीय श्रेणी राजपत्रित अधिकारी के पद पर सीधी नियुक्ति प्रदान करने का प्रावधान लागू है।
- राज्य खेल अलंकरण: आगामी राज्योत्सव पर इन खिलाड़ियों को 'गुंडाधूर सम्मान' से विभूषित किया जाएगा।
            """.trimIndent(),
            category = "Current Affairs",
            source = "खेल एवं युवा कल्याण संचालनालय",
            sourceUrl = "https://sports.cg.gov.in",
            publishedAt = "26 Aug 2026",
            relativeTime = "1w ago",
            isBreaking = false,
            isNew = false,
            vacancies = "खेलकूद व पुरस्कार विशेष",
            eligibility = "प्रतियोगी परीक्षार्थी",
            officialNotificationUrl = "https://sports.cg.gov.in/awards-list.pdf",
            applyUrl = null,
            importantDates = ImportantDates(
                applicationStart = "अगस्त 2026",
                lastDate = "संपन्न",
                examDate = "आगामी परीक्षाएं"
            ),
            isSaved = false
        ),
        JobUpdate(
            id = "cg_admit_card_sub_engineer",
            title = "CG Vyapam सब-इंजीनियर (Civil/Mech) प्रवेश पत्र डाउनलोड शुरू",
            summary = "जल संसाधन एवं लोक निर्माण विभाग में 180 सब-इंजीनियर पदों हेतु 14 सितंबर को आयोजित होने वाली परीक्षा के ई-प्रवेश पत्र जारी कर दिए गए हैं।",
            detailedContent = """
छत्तीसगढ़ जल संसाधन विभाग तथा लोक निर्माण विभाग (PWD) में उप-अभियंता (सिविल एवं मैकेनिकल) पदों के लिए लिखित परीक्षा 14 सितंबर को सुबह 10 बजे से दोपहर 1:15 बजे तक आयोजित की जाएगी।

परीक्षार्थी अपने रजिस्टर्ड मोबाइल नंबर और पासवर्ड के जरिए एडमिट कार्ड डाउनलोड कर सकते हैं। 

परीक्षा केंद्र पर प्रवेश पत्र के साथ आधार कार्ड, पैन कार्ड अथवा ड्राइविंग लाइसेंस की मूल प्रति और दो पासपोर्ट साइज फोटो ले जाना अनिवार्य है।
            """.trimIndent(),
            category = "Admit Card",
            source = "CG Vyapam",
            sourceUrl = "https://vyapam.cgstate.gov.in",
            publishedAt = "26 Aug 2026",
            relativeTime = "1w ago",
            isBreaking = false,
            isNew = false,
            vacancies = "180 पद",
            eligibility = "सिविल/मैकेनिकल इंजीनियरिंग में 3 वर्षीय डिप्लोमा अथवा B.Tech/B.E.",
            ageLimit = "18 - 35 वर्ष",
            selectionProcess = "व्यापम वस्तुनिष्ठ परीक्षा (150 अंक)",
            officialNotificationUrl = "https://vyapam.cgstate.gov.in/admitcard/se26",
            applyUrl = "https://vyapam.cgstate.gov.in",
            importantDates = ImportantDates(
                applicationStart = "संपन्न",
                lastDate = "संपन्न",
                examDate = "14 सितम्बर 2026",
                admitCardDate = "26 अगस्त 2026 (जारी)"
            ),
            isSaved = false
        ),
        JobUpdate(
            id = "cg_raeo_agriculture_result",
            title = "ग्रामीण कृषि विस्तार अधिकारी (RAEO) अंतिम मेरिट सूची जारी",
            summary = "कृषि विभाग अंतर्गत 305 ग्रामीण कृषि विस्तार अधिकारी पदों हेतु आयोजित भर्ती परीक्षा की अंतिम चयन सूची एवं कट-ऑफ अंक घोषित किए गए।",
            detailedContent = """
संचालनालय कृषि छत्तीसगढ़ द्वारा ग्रामीण कृषि विस्तार अधिकारी भर्ती परीक्षा के दस्तावेज सत्यापन के उपरांत अंतिम चयन सूची एवं प्रतीक्षा सूची जारी कर दी गई है।

अनारक्षित वर्ग का कट-ऑफ 118.50 अंक, अन्य पिछड़ा वर्ग 114.25 अंक, अनुसूचित जाति 102.00 अंक तथा अनुसूचित जनजाति का कट-ऑफ 94.75 अंक रहा है।

चयनित अभ्यर्थियों को चिकित्सा परीक्षण एवं पुलिस चरित्र सत्यापन हेतु प्रपत्र शीघ्र उनके पंजीकृत पते पर प्रेषित किए जाएंगे।
            """.trimIndent(),
            category = "Result",
            source = "संचालनालय कृषि रायपुर",
            sourceUrl = "https://agriportal.cg.nic.in",
            publishedAt = "24 Aug 2026",
            relativeTime = "1w ago",
            isBreaking = false,
            isNew = false,
            vacancies = "305 पद",
            eligibility = "B.Sc (कृषि/उद्यानिकी/कृषि अभियांत्रिकी)",
            ageLimit = "21 - 35 वर्ष",
            selectionProcess = "लिखित परीक्षा + दस्तावेज सत्यापन",
            officialNotificationUrl = "https://agriportal.cg.nic.in/merit-raeo.pdf",
            applyUrl = null,
            importantDates = ImportantDates(
                applicationStart = "संपन्न",
                lastDate = "संपन्न",
                examDate = "फरवरी 2026",
                resultDate = "24 अगस्त 2026"
            ),
            isSaved = false
        ),
        JobUpdate(
            id = "cgpsc_forest_service_syllabus",
            title = "CGPSC वन सेवा (संयुक्त) परीक्षा 2026 पाठ्यक्रम व परीक्षा योजना",
            summary = "सहायक वन संरक्षक (ACF) एवं वन क्षेत्रपाल (Ranger) के 89 पदों हेतु द्विस्तरीय परीक्षा का विस्तृत पाठ्यक्रम व शारीरिक मापदंड नियम जारी।",
            detailedContent = """
छत्तीसगढ़ वन एवं जलवायु परिवर्तन विभाग हेतु राज्य वन सेवा परीक्षा 2026 की परीक्षा योजना आयोग द्वारा जारी की गई है।

लिखित परीक्षा में दो प्रश्नपत्र होंगे:
1. सामान्य अध्ययन, छत्तीसगढ़ का सामान्य ज्ञान, बुद्धिमत्ता परीक्षण एवं भाषा ज्ञान (150 प्रश्न, 300 अंक)।
2. विज्ञान, प्रौद्योगिकी, पर्यावरण, कृषि एवं वानिकी (150 प्रश्न, 300 अंक)।

लिखित परीक्षा में सफल अभ्यर्थियों को 25 किमी (पुरुष) व 14 किमी (महिला) की 4 घंटे में पैदल चाल शारीरिक दक्षता पूर्ण करनी होगी।
            """.trimIndent(),
            category = "CGPSC",
            source = "CGPSC Raipur",
            sourceUrl = "https://psc.cg.gov.in",
            publishedAt = "22 Aug 2026",
            relativeTime = "2w ago",
            isBreaking = false,
            isNew = false,
            vacancies = "89 पद",
            eligibility = "विज्ञान/इंजीनियरिंग/कृषि/वानिकी विषय में स्नातक",
            ageLimit = "21 - 30 वर्ष",
            selectionProcess = "लिखित परीक्षा (600 अंक) + साक्षात्कार (75 अंक) + शारीरिक पैदल चाल",
            officialNotificationUrl = "https://psc.cg.gov.in/forest-syllabus-2026.pdf",
            applyUrl = "https://psc.cg.gov.in",
            importantDates = ImportantDates(
                applicationStart = "अक्टूबर 2026 प्रथम सप्ताह",
                lastDate = "नवंबर 2026 प्रथम सप्ताह",
                examDate = "दिसंबर 2026",
                admitCardDate = "दिसंबर प्रथम सप्ताह"
            ),
            isSaved = false
        ),
        JobUpdate(
            id = "cgssb_clerk_deoo_2026",
            title = "CGSSB कनिष्ठ प्रशासनिक सहायक व डाटा एंट्री ऑपरेटर भर्ती",
            summary = "विभिन्न विभागाध्यक्ष कार्यालयों (इंद्रावती भवन नवा रायपुर) में 850 रिक्त पदों हेतु आवेदन आमंत्रित किए गए हैं।",
            detailedContent = """
छत्तीसगढ़ कर्मचारी चयन मंडल (CGSSB) द्वारा विभागाध्यक्ष संवर्ग में कनिष्ठ प्रशासनिक सहायक (LDC) के 520 पद तथा डाटा एंट्री ऑपरेटर (DEO) के 330 पदों हेतु सीधी भर्ती अधिसूचना जारी की गई है।

कंप्यूटर में 8000 की-डिप्रेशन प्रति घंटा (हिंदी व अंग्रेजी) कौशल परीक्षा आयोजित की जाएगी। कौशल परीक्षा के अंक मेरिट में जोड़े जाएंगे।

ऑनलाइन आवेदन हेतु CGSSB पोर्टल पर ओटीआर (वन टाइम रजिस्ट्रेशन) अनिवार्य किया गया है।
            """.trimIndent(),
            category = "CGSSB",
            source = "CGSSB Raipur",
            sourceUrl = "https://cgstate.gov.in",
            publishedAt = "20 Aug 2026",
            relativeTime = "2w ago",
            isBreaking = false,
            isNew = false,
            vacancies = "850 पद",
            eligibility = "12वीं उत्तीर्ण + मान्यता प्राप्त संस्था से 1 वर्षीय डिप्लोमा इन कंप्यूटर (DCA)",
            ageLimit = "18 - 40 वर्ष",
            selectionProcess = "लिखित परीक्षा (100 अंक) + कंप्यूटर टाइपिंग कौशल परीक्षा (50 अंक)",
            officialNotificationUrl = "https://cgstate.gov.in/deoo-clerk-advt.pdf",
            applyUrl = "https://cgstate.gov.in",
            importantDates = ImportantDates(
                applicationStart = "05 सितंबर 2026",
                lastDate = "28 सितंबर 2026",
                examDate = "22 नवंबर 2026",
                admitCardDate = "12 नवंबर 2026"
            ),
            isSaved = false
        ),
        JobUpdate(
            id = "cg_excise_constable_2026",
            title = "छत्तीसगढ़ आबकारी आरक्षक शारीरिक नापजोख प्रवेश पत्र जारी",
            summary = "वाणिज्यिक कर (आबकारी) विभाग में आबकारी आरक्षक के 150 पदों हेतु शारीरिक मानक परीक्षण (PST) के एडमिट कार्ड जारी।",
            detailedContent = """
आबकारी विभाग छत्तीसगढ़ अंतर्गत आबकारी आरक्षक भर्ती परीक्षा के शारीरिक नापजोख एवं प्रमाण पत्र सत्यापन हेतु प्रवेश पत्र वेबसाइट पर उपलब्ध करा दिए गए हैं।

परीक्षार्थी अपने आवेदन क्रमांक एवं जन्मतिथि की सहायता से एडमिट कार्ड डाउनलोड कर सकते हैं। 

ऊंचाई एवं सीना माप में उत्तीर्ण अभ्यर्थियों को ही आगामी लिखित परीक्षा में सम्मिलित होने की अनुमति दी जाएगी।
            """.trimIndent(),
            category = "Admit Card",
            source = "आबकारी विभाग रायपुर",
            sourceUrl = "https://excise.cg.nic.in",
            publishedAt = "18 Aug 2026",
            relativeTime = "2w ago",
            isBreaking = false,
            isNew = false,
            vacancies = "150 पद",
            eligibility = "12वीं उत्तीर्ण + निर्धारित शारीरिक मापदंड",
            ageLimit = "18 - 35 वर्ष",
            selectionProcess = "शारीरिक मानक परीक्षण (PST) + लिखित परीक्षा (100 अंक)",
            officialNotificationUrl = "https://excise.cg.nic.in/admit-pst.pdf",
            applyUrl = "https://excise.cg.nic.in",
            importantDates = ImportantDates(
                applicationStart = "संपन्न",
                lastDate = "संपन्न",
                examDate = "शारीरिक नापजोख: 10 सितम्बर 2026 से",
                admitCardDate = "18 अगस्त 2026 (जारी)"
            ),
            isSaved = false
        ),
        JobUpdate(
            id = "cg_budget_current_affairs_special",
            title = "छत्तीसगढ़ बजट 2026-27: युवाओं और रोजगार से जुड़े प्रमुख प्रावधान",
            summary = "परीक्षा विशेष: राज्य के नए बजट में 25,000 नई सरकारी नौकरियों, नवा रायपुर आईटी हब एवं युवा इंटर्नशिप योजना की घोषणाएं।",
            detailedContent = """
छत्तीसगढ़ विधानसभा में प्रस्तुत राज्य के वार्षिक बजट में युवा एवं रोजगार संवर्ग के महत्वपूर्ण बिंदु जो प्रतियोगी परीक्षाओं में पूछे जा सकते हैं:

1. 'मिशन 25K': आगामी एक वर्ष में विभिन्न विभागों में 25,000 नियमित पदों पर पारदर्शी भर्ती का लक्ष्य।
2. मुख्यमंत्री युवा स्वरोजगार योजना हेतु 500 करोड़ रुपये का विशेष कोष।
3. नवा रायपुर में 200 एकड़ में अत्याधुनिक सेमीकंडक्टर व आईटी पार्क की स्थापना।
4. बस्तर व सरगुजा के 14 जिलों में प्रतियोगी परीक्षा कोचिंग हेतु 'प्रयास' आवासीय केंद्रों का विस्तार।
5. सरकारी परीक्षाओं के लिए स्थानीय अभ्यर्थियों हेतु परीक्षा शुल्क पूर्णतः माफ करने की नीति जारी रहेगी।
            """.trimIndent(),
            category = "Current Affairs",
            source = "CGJobs विशेष अध्ययन",
            sourceUrl = "https://finance.cg.gov.in",
            publishedAt = "15 Aug 2026",
            relativeTime = "3w ago",
            isBreaking = false,
            isNew = false,
            vacancies = "25,000 पद (लक्ष्य)",
            eligibility = "छत्तीसगढ़ के सभी प्रतियोगी परीक्षार्थियों हेतु",
            ageLimit = "लागू नहीं",
            selectionProcess = "समसामयिक संदर्भ",
            officialNotificationUrl = "https://finance.cg.gov.in/budget-highlights.pdf",
            applyUrl = null,
            importantDates = ImportantDates(
                applicationStart = "चालू वित्तीय वर्ष",
                lastDate = "मार्च 2027",
                examDate = "आगामी परीक्षाएं"
            ),
            isSaved = false
        ),
        JobUpdate(
            id = "cg_vyapam_annual_calendar",
            title = "CG Vyapam वार्षिक परीक्षा कैलेंडर 2026-27 संशोधित संस्करण",
            summary = "व्यापम ने आगामी 12 प्रतियोगी व प्रवेश परीक्षाओं की संशोधित संभावित तिथियों का कैलेंडर जारी किया।",
            detailedContent = """
छत्तीसगढ़ व्यावसायिक परीक्षा मंडल रायपुर द्वारा शैक्षणिक सत्र 2026-27 के लिए संशोधित परीक्षा कैलेंडर जारी किया गया है।

इसमें पीईटी (PET), पीएमटी (PMT), पीपीएचटी (PPHT), प्री-बीएड, प्री-डीएलएड सहित पटवारी, आरआई (राजस्व निरीक्षक), सहायक सांख्यिकी अधिकारी और मंडी उप-निरीक्षक भर्ती परीक्षाओं की संभावित तिथियां घोषित की गई हैं।

अभ्यर्थी कैलेंडर के अनुसार अपनी अध्ययन योजना तैयार कर सकते हैं। समय-समय पर विस्तृत अधिसूचनाएं पृथक से जारी होंगी।
            """.trimIndent(),
            category = "CG Vyapam",
            source = "CG Vyapam रायपुर",
            sourceUrl = "https://vyapam.cgstate.gov.in",
            publishedAt = "12 Aug 2026",
            relativeTime = "3w ago",
            isBreaking = false,
            isNew = false,
            vacancies = "विभिन्न पद व प्रवेश परीक्षाएं",
            eligibility = "पदानुसार 12वीं / डिप्लोमा / स्नातक",
            ageLimit = "पदानुसार",
            selectionProcess = "व्यापम वस्तुनिष्ठ प्रतियोगी परीक्षाएं",
            officialNotificationUrl = "https://vyapam.cgstate.gov.in/calendar-2026.pdf",
            applyUrl = "https://vyapam.cgstate.gov.in",
            importantDates = ImportantDates(
                applicationStart = "कैलेंडर अनुसार",
                lastDate = "पृथक से सूचित",
                examDate = "सितम्बर 2026 से फरवरी 2027",
                admitCardDate = "परीक्षा से 10 दिन पूर्व"
            ),
            isSaved = false
        )
    )

    private val _news = MutableStateFlow<List<JobUpdate>>(initialUpdates)
    val news = _news.asStateFlow()

    override fun getNewsStream(): Flow<List<JobUpdate>> = news

    override fun getNewsByCategory(category: String): Flow<List<JobUpdate>> {
        return news.map { list ->
            if (category == "सभी" || category.equals("All", ignoreCase = true)) {
                list
            } else {
                list.filter { item ->
                    item.category.equals(category, ignoreCase = true) ||
                            (category == "Teacher" && (item.category == "CGSSB" || item.category == "Education" || item.title.contains("शिक्षक") || item.title.contains("Teacher"))) ||
                            (category == "Patwari" && item.title.contains("पटवारी")) ||
                            (category == "Police" && (item.category == "Police" || item.title.contains("पुलिस") || item.title.contains("आबकारी"))) ||
                            (category == "TET" && (item.title.contains("TET") || item.category == "TET")) ||
                            (category == "Admit Card" && (item.category == "Admit Card" || item.title.contains("प्रवेश पत्र") || item.title.contains("Admit Card"))) ||
                            (category == "Result" && (item.category == "Result" || item.title.contains("परिणाम") || item.title.contains("Result"))) ||
                            (category == "Answer Key" && (item.category == "Answer Key" || item.title.contains("उत्तर कुंजी") || item.title.contains("Answer Key"))) ||
                            ((category.contains("Current", ignoreCase = true) || category.contains("Affairs", ignoreCase = true) || category.contains("समसामयिकी")) &&
                                    (item.category.equals("Current Affairs", ignoreCase = true) || item.title.contains("समसामयिकी") || item.title.contains("करेंट अफेयर्स")))
                }
            }
        }
    }

    override fun getNewsById(id: String): Flow<JobUpdate?> {
        return news.map { list -> list.find { it.id == id } }
    }

    override fun searchNews(query: String): Flow<List<JobUpdate>> {
        val q = query.trim().lowercase()
        return news.map { list ->
            if (q.isEmpty()) emptyList()
            else {
                list.filter { item ->
                    item.title.lowercase().contains(q) ||
                            item.summary.lowercase().contains(q) ||
                            item.category.lowercase().contains(q) ||
                            item.source.lowercase().contains(q) ||
                            item.detailedContent.lowercase().contains(q)
                }
            }
        }
    }

    override fun getSavedNews(): Flow<List<JobUpdate>> {
        return news.map { list -> list.filter { it.isSaved } }
    }

    override suspend fun toggleSave(id: String) {
        _news.value = _news.value.map { item ->
            if (item.id == id) {
                item.copy(isSaved = !item.isSaved)
            } else {
                item
            }
        }
    }

    override suspend fun refreshNews() {
        // Simulates fresh sync from future backend
        _news.value = _news.value
    }

    private val defaultCategories = listOf(
        // Jobs
        AppCategory("all_jobs", "All Jobs", "सभी भर्तियां", "jobs"),
        AppCategory("cgpsc", "CGPSC", "सीजीपीएससी", "jobs"),
        AppCategory("cg_vyapam", "CG Vyapam", "व्यापम", "jobs"),
        AppCategory("police_defence", "Police & Defence", "पुलिस व सुरक्षा", "jobs"),
        AppCategory("teaching", "Teaching", "शिक्षक भर्ती", "jobs"),
        AppCategory("patwari_revenue", "Patwari & Revenue", "पटवारी व राजस्व", "jobs"),
        AppCategory("engineering", "Engineering & Tech", "इंजीनियरिंग", "jobs"),
        AppCategory("medical_health", "Medical & Health", "स्वास्थ्य व चिकित्सा", "jobs"),
        AppCategory("admit_card", "Admit Card", "प्रवेश पत्र", "jobs"),
        AppCategory("result", "Result", "परीक्षा परिणाम", "jobs"),
        AppCategory("answer_key", "Answer Key", "उत्तर कुंजी", "jobs"),
        AppCategory("syllabus", "Syllabus", "पाठ्यक्रम", "jobs"),

        // News
        AppCategory("all_news", "All Current Affairs", "सभी (All)", "news"),
        AppCategory("national", "National", "राष्ट्रीय", "news"),
        AppCategory("international", "International", "अंतर्राष्ट्रीय", "news"),
        AppCategory("chhattisgarh", "State Special", "छत्तीसगढ़", "news"),
        AppCategory("economy", "Economy & Budget", "अर्थव्यवस्था", "news"),
        AppCategory("sports", "Sports & Awards", "खेलकूद", "news"),
        AppCategory("schemes", "Government Schemes", "सरकारी योजनाएं", "news"),
        AppCategory("environment", "Environment & Forest", "पर्यावरण व भूगोल", "news"),

        // Static GK
        AppCategory("all_gk", "All Topics", "सभी (All)", "static_gk"),
        AppCategory("history", "History & Heritage", "इतिहास", "static_gk"),
        AppCategory("geography", "Geography & Rivers", "भूगोल", "static_gk"),
        AppCategory("culture_tribes", "Culture & Tribes", "संस्कृति व जनजाति", "static_gk"),
        AppCategory("polity_economy", "Polity & Economy", "राजव्यवस्था व अर्थव्यवस्था", "static_gk"),
        AppCategory("state_special", "State Special", "छत्तीसगढ़ विशेष", "static_gk")
    )

    private val defaultSections = listOf(
        AppSection(
            id = "jobs",
            name = "Jobs & Recruitment",
            hindiName = "सरकारी नौकरी व भर्ती",
            description = "Chhattisgarh State Government & Public Sector Recruitments",
            icon = "work",
            categories = defaultCategories.filter { it.section == "jobs" },
            itemCount = 12
        ),
        AppSection(
            id = "news",
            name = "News & Current Affairs",
            hindiName = "दैनिक समसामयिकी",
            description = "Daily Exam-relevant Current Affairs, Policies & Schemes",
            icon = "article",
            categories = defaultCategories.filter { it.section == "news" },
            itemCount = 8
        ),
        AppSection(
            id = "static_gk",
            name = "Static GK",
            hindiName = "छत्तीसगढ़ सामान्य ज्ञान",
            description = "High-yield Static GK cards for competitive exams",
            icon = "menu_book",
            categories = defaultCategories.filter { it.section == "static_gk" },
            itemCount = 6
        )
    )

    private val _sectionsStream = MutableStateFlow(defaultSections)
    private val _categoriesStream = MutableStateFlow(defaultCategories)

    override fun getSectionsStream(): Flow<List<AppSection>> = _sectionsStream.asStateFlow()

    override fun getCategoriesStream(section: String?): Flow<List<AppCategory>> {
        return _categoriesStream.map { list ->
            if (section.isNullOrBlank()) list
            else list.filter { it.section.equals(section, ignoreCase = true) }
        }
    }
}
