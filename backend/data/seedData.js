/**
 * Seed data for CGJobs - Chhattisgarh Recruitment & Exam Updates
 */

const SEED_CATEGORIES = [
  { id: "all", name: "All Jobs", hindiName: "सभी भर्तियां" },
  { id: "cgpsc", name: "CGPSC", hindiName: "सीजीपीएससी" },
  { id: "cg_vyapam", name: "CG Vyapam", hindiName: "व्यापम" },
  { id: "police_defence", name: "Police & Defence", hindiName: "पुलिस व सुरक्षा" },
  { id: "teaching", name: "Teaching", hindiName: "शिक्षक भर्ती" },
  { id: "current_affairs", name: "Current Affairs", hindiName: "समसामयिकी" },
  { id: "admit_card", name: "Admit Card", hindiName: "प्रवेश पत्र" },
  { id: "result", name: "Result", hindiName: "परीक्षा परिणाम" },
  { id: "answer_key", name: "Answer Key", hindiName: "उत्तर कुंजी" }
];

const SEED_NEWS = [
  {
    id: "cg_ca_tiger_reserve",
    title: "गुरु घासीदास-तमोर पिंगला: छत्तीसगढ़ का चौथा व देश का 56वां टाइगर रिजर्व अधिसूचित",
    summary: "राष्ट्रीय बाघ संरक्षण प्राधिकरण (NTCA) ने गुरु घासीदास राष्ट्रीय उद्यान और तमोर पिंगला अभयारण्य को मिलाकर देश का 56वां टाइगर रिजर्व घोषित किया। 2,829 वर्ग किमी में फैला यह रिजर्व प्रदेश का चौथा टाइगर रिजर्व बना।",
    detailedContent: "छत्तीसगढ़ सरकार ने गुरु घासीदास राष्ट्रीय उद्यान व तमोर पिंगला वन्यजीव अभयारण्य के संयुक्त क्षेत्र को विधिवत 'गुरु घासीदास-तमोर पिंगला टाइगर रिजर्व' के रूप में अधिसूचित कर दिया है।\n\nभौगोलिक व प्रशासनिक तथ्य:\n1. विस्तार: कोरिया, मनेंद्रगढ़-चिरमिरी-भरतपुर (MCB) तथा सूरजपुर जिले।\n2. कुल क्षेत्रफल: 2,829.38 वर्ग किलोमीटर (कोर जोन: 2,049.2 वर्ग किमी, बफर जोन: 780.18 वर्ग किमी)।\n3. कॉरिडोर: यह टाइगर रिजर्व मध्य प्रदेश के संजय-डुबरी और बांधवगढ़ टाइगर रिजर्व से सीधा वन्यजीव गलियारा बनाता है।\n\nप्रतियोगी परीक्षा विशेष (CGPSC/व्यापम प्रश्नोत्तर):\n- छत्तीसगढ़ के 4 टाइगर रिजर्व: इंद्रावती (बीजापुर), अचानकमार (मुंगेली), उदंती-सीतानदी (गरियाबंद) और गुरु घासीदास-तमोर पिंगला।\n- तमोर पिंगला का नामकरण पिंगला नाले पर हुआ है, जिसे 1978 में अभयारण्य का दर्जा मिला था।",
    category: "Current Affairs",
    source: "वन एवं जलवायु परिवर्तन विभाग CG",
    sourceUrl: "https://forest.cg.gov.in",
    imageUrl: "https://images.unsplash.com/photo-1561731216-c3a4d99437d5?w=800",
    publishedAt: "04 Sept 2026",
    relativeTime: "1d ago",
    isBreaking: true,
    isNew: true,
    vacancies: "पर्यावरण व भूगोल विशेष",
    eligibility: "CGPSC व व्यापम परीक्षार्थी",
    ageLimit: "लागू नहीं",
    selectionProcess: "समसामयिक अध्ययन",
    officialNotificationUrl: "https://forest.cg.gov.in/tiger-reserve-notification.pdf",
    applyUrl: null,
    importantDates: {
      applicationStart: "अधिसूचना तिथि: 04 सितंबर 2026",
      lastDate: "दैनिक संकलन",
      examDate: "आगामी परीक्षाएं"
    }
  },
  {
    id: "cg_ca_tendupatta_hike",
    title: "तेंदूपत्ता संग्रहण पारिश्रमिक दर बढ़कर ₹5,500 प्रति मानक बोरा: 12.5 लाख परिवारों को संबल",
    summary: "राज्य शासन ने तेंदूपत्ता संग्रहण की पारिश्रमिक दर ₹4,000 से बढ़ाकर ₹5,500 प्रति मानक बोरा कर दी है। प्रदेश के 12 लाख 50 हजार से अधिक जनजातीय संग्राहक परिवारों को प्रत्यक्ष आर्थिक लाभ प्राप्त होगा।",
    detailedContent: "छत्तीसगढ़ राज्य लघु वनोपज सहकारी संघ ने तेंदूपत्ता संग्रहण सत्र 2026 के लिए ऐतिहासिक पारिश्रमिक वृद्धि लागू कर दी है।\n\nमहत्वपूर्ण बिंदु:\n1. नई संग्रहण दर: ₹5,500 प्रति मानक बोरा (गत वर्ष की तुलना में ₹1,500 की रिकॉर्ड वृद्धि)।\n2. लाभान्वित संग्राहक: 902 प्राथमिक वनोपज समितियों से जुड़े 12.5 लाख से अधिक परिवार।\n3. चरण पादुका वितरण: महिला संग्राहकों को चरण पादुकाएं और पेयजल हेतु वाटर बॉटल किट का निशुल्क वितरण पुनरारंभ।\n4. छात्रवृत्ति व बीमा: संग्राहक परिवारों के मेधावी छात्र-छात्राओं हेतु 'एकलव्य शिक्षा प्रोत्साहन योजना' के तहत छात्रवृत्ति राशि में भी 50% की वृद्धि की गई है।",
    category: "Current Affairs",
    source: "लघु वनोपज सहकारी संघ CG",
    sourceUrl: "https://cgmfpfed.org",
    imageUrl: "https://images.unsplash.com/photo-1542601906990-b4d3fb778b09?w=800",
    publishedAt: "03 Sept 2026",
    relativeTime: "2d ago",
    isBreaking: false,
    isNew: true,
    vacancies: "आर्थिकी व वनोपज विशेष",
    eligibility: "समस्त राज्य प्रतियोगी परीक्षाएं",
    officialNotificationUrl: "https://cgmfpfed.org/rates-2026.pdf",
    applyUrl: null,
    importantDates: {
      applicationStart: "लागू सत्र 2026",
      lastDate: "संपन्न",
      examDate: "आगामी परीक्षाएं"
    }
  },
  {
    id: "cgpsc_sse_2026",
    title: "CGPSC राज्य सेवा परीक्षा (State Service Exam) 2026: 242 पदों हेतु अधिसूचना जारी",
    summary: "छत्तीसगढ़ लोक सेवा आयोग द्वारा डिप्टी कलेक्टर, डीएसपी, नायब तहसीलदार एवं अन्य 242 प्रशासनिक पदों हेतु ऑनलाइन आवेदन आमंत्रित किए गए हैं।",
    detailedContent: "छत्तीसगढ़ लोक सेवा आयोग (CGPSC), रायपुर द्वारा राज्य सेवा परीक्षा 2026 के लिए विस्तृत विज्ञापन जारी किया गया है।\n\nपदों का विवरण:\n- डिप्टी कलेक्टर: 15 पद\n- उप पुलिस अधीक्षक (DSP): 25 पद\n- छत्तीसगढ़ राज्य वित्त सेवा: 12 पद\n- नायब तहसीलदार: 70 पद\n- आबकारी उप निरीक्षक: 34 पद\n- अन्य सहायक पद: 86 पद\n\nशैक्षणिक योग्यता: किसी भी मान्यता प्राप्त विश्वविद्यालय से स्नातक उपाधि (Graduation).\nआयु सीमा: 21 से 30 वर्ष (छत्तीसगढ़ के मूल निवासियों हेतु अधिकतम 40 वर्ष तक छूट).\nचयन प्रक्रिया: प्रारंभिक परीक्षा, मुख्य परीक्षा एवं साक्षात्कार।",
    category: "CGPSC",
    source: "छत्तीसगढ़ लोक सेवा आयोग",
    sourceUrl: "https://psc.cg.gov.in",
    imageUrl: "https://images.unsplash.com/photo-1434030216411-0b793f4b4173?w=800",
    publishedAt: "02 Sept 2026",
    relativeTime: "3d ago",
    isBreaking: true,
    isNew: true,
    vacancies: "242 पद",
    eligibility: "स्नातक (Graduation in any stream)",
    ageLimit: "21 से 40 वर्ष",
    selectionProcess: "प्रारंभिक, मुख्य परीक्षा व इंटरव्यू",
    officialNotificationUrl: "https://psc.cg.gov.in/pdf/advt_sse_2026.pdf",
    applyUrl: "https://psc.cg.gov.in/apply-sse",
    importantDates: {
      applicationStart: "01 सितंबर 2026",
      lastDate: "30 सितंबर 2026 (रात्रि 11:59 तक)",
      examDate: "प्रारंभिक परीक्षा: 14 फरवरी 2027",
      admitCardDate: "05 फरवरी 2027"
    }
  },
  {
    id: "cg_vyapam_hostel_warden",
    title: "CG व्यापम छात्रावास अधीक्षक (Hostel Warden) 300 पदों पर भर्ती परीक्षा घोषित",
    summary: "आदिम जाति तथा अनुसूचित जाति विकास विभाग अंतर्गत छात्रावास अधीक्षक श्रेणी 'द' के 300 रिक्त पदों हेतु आवेदन एवं परीक्षा तिथि जारी।",
    detailedContent: "छत्तीसगढ़ व्यावसायिक परीक्षा मंडल (व्यापम) द्वारा छात्रावास अधीक्षक श्रेणी 'द' भर्ती परीक्षा के लिए विस्तृत दिशानिर्देश जारी कर दिए गए हैं।\n\nशैक्षणिक योग्यता: हायर सेकेंडरी (12वीं उत्तीर्ण) एवं कंप्यूटर का आधारभूत ज्ञान।\nपरीक्षा पैटर्न: 100 अंक कंप्यूटर ज्ञान, 50 अंक सामान्य ज्ञान एवं समसामयिकी।",
    category: "CG Vyapam",
    source: "CG Vyapam, Raipur",
    sourceUrl: "https://vyapam.cgstate.gov.in",
    imageUrl: "https://images.unsplash.com/photo-1523240795612-9a054b0db644?w=800",
    publishedAt: "31 Aug 2026",
    relativeTime: "5d ago",
    isBreaking: false,
    isNew: true,
    vacancies: "300 पद",
    eligibility: "12वीं कक्षा उत्तीर्ण + कंप्यूटर ज्ञान",
    ageLimit: "21 से 40 वर्ष",
    selectionProcess: "व्यापम लिखित परीक्षा",
    officialNotificationUrl: "https://vyapam.cgstate.gov.in/notification/thw26.pdf",
    applyUrl: "https://vyapam.cgstate.gov.in/online",
    importantDates: {
      applicationStart: "25 अगस्त 2026",
      lastDate: "20 सितंबर 2026",
      examDate: "27 अक्टूबर 2026"
    }
  },
  {
    id: "cg_police_constable_physical",
    title: "छत्तीसगढ़ पुलिस आरक्षक (GD) 5,967 पद: शारीरिक दक्षता परीक्षा (PET) शेड्यूल जारी",
    summary: "पुलिस मुख्यालय रायपुर ने आरक्षक संवर्ग भर्ती हेतु रेंजवार शारीरिक नापजोख एवं दक्षता परीक्षा (दौड़, लंबी कूद, ऊंची कूद, गोला फेंक) की तिथियां घोषित कर दी हैं।",
    detailedContent: "छत्तीसगढ़ पुलिस विभाग अंतर्गत आरक्षक संवर्ग (GD, चालक, ट्रेड) के कुल 5,967 पदों पर भर्ती हेतु द्वितीय चरण 'शारीरिक दक्षता परीक्षा' (PET) 16 अक्टूबर 2026 से विभिन्न संभागीय मुख्यालयों (रायपुर, दुर्ग, बिलासपुर, जगदलपुर, सरगुजा) पर आयोजित की जाएगी।",
    category: "Police & Defence",
    source: "पुलिस मुख्यालय, नवा रायपुर",
    sourceUrl: "https://cgpolice.gov.in",
    imageUrl: "https://images.unsplash.com/photo-1541872703-74c5e44368f9?w=800",
    publishedAt: "28 Aug 2026",
    relativeTime: "1w ago",
    isBreaking: true,
    isNew: false,
    vacancies: "5,967 पद",
    eligibility: "10वीं/12वीं पास (बस्तर संभाग हेतु 8वीं/5वीं पास)",
    ageLimit: "18 से 33 वर्ष (छूट नियमानुसार)",
    selectionProcess: "दस्तावेज जांच, शारीरिक माप, PET व लिखित परीक्षा",
    officialNotificationUrl: "https://cgpolice.gov.in/pet-schedule.pdf",
    applyUrl: null,
    importantDates: {
      applicationStart: "आवेदन संपन्न",
      lastDate: "सत्यापन पूर्ण",
      examDate: "PET प्रारंभ: 16 अक्टूबर 2026"
    }
  },
  {
    id: "cg_teacher_recruitment_33000",
    title: "छत्तीसगढ़ स्कूल शिक्षा विभाग: 33,000 शिक्षक पदों पर भर्ती प्रक्रिया की सैद्धांतिक स्वीकृति",
    summary: "व्याख्याता, शिक्षक एवं सहायक शिक्षक के कुल 33,000 पदों पर चरणबद्ध भर्ती हेतु वित्त विभाग से सहमति प्राप्त। व्यापम द्वारा जल्द जारी होगा विस्तृत विज्ञापन।",
    detailedContent: "राज्य के प्राथमिक, माध्यमिक एवं उच्चतर माध्यमिक विद्यालयों में शिक्षकों की कमी दूर करने हेतु 33,000 नवीन पदों पर भर्ती का मार्ग प्रशस्त हो गया है।\n\nप्रस्तावित पद:\n- सहायक शिक्षक: 19,000 पद\n- शिक्षक (वर्ग-2): 10,000 पद\n- व्याख्याता (वर्ग-1): 4,000 पद\n\nअनिवार्यता: D.El.Ed / B.Ed तथा CG-TET अथवा CTET उत्तीर्ण होना अनिवार्य होगा।",
    category: "Teaching",
    source: "स्कूल शिक्षा विभाग, छत्तीसगढ़",
    sourceUrl: "https://eduportal.cg.nic.in",
    imageUrl: "https://images.unsplash.com/photo-1509062522246-3755977927d7?w=800",
    publishedAt: "25 Aug 2026",
    relativeTime: "1w ago",
    isBreaking: true,
    isNew: true,
    vacancies: "33,000 पद (प्रस्तावित)",
    eligibility: "12वीं/स्नातक/स्नातकोत्तर + D.El.Ed/B.Ed + CG-TET",
    ageLimit: "21 से 40 वर्ष",
    selectionProcess: "व्यापम शिक्षक पात्रता व चयन परीक्षा",
    officialNotificationUrl: "https://eduportal.cg.nic.in/press-release.pdf",
    applyUrl: null,
    importantDates: {
      applicationStart: "अक्टूबर 2026 (संभावित)",
      lastDate: "अधिसूचना जल्द",
      examDate: "दिसंबर 2026"
    }
  },
  {
    id: "cg_admit_card_sub_engineer",
    title: "CG Vyapam सब-इंजीनियर (Civil/Mech) प्रवेश पत्र डाउनलोड शुरू",
    summary: "जल संसाधन एवं लोक निर्माण विभाग में 180 सब-इंजीनियर पदों हेतु 14 सितंबर को आयोजित होने वाली परीक्षा के ई-प्रवेश पत्र जारी कर दिए गए हैं।",
    detailedContent: "व्यापम ने उप अभियंता भर्ती परीक्षा के एडमिट कार्ड अपनी वेबसाइट पर लाइव कर दिए हैं। अभ्यर्थी अपने पंजीकृत मोबाइल नंबर व पासवर्ड से लॉगिन कर एडमिट कार्ड डाउनलोड कर सकते हैं।",
    category: "Admit Card",
    source: "CG Vyapam",
    sourceUrl: "https://vyapam.cgstate.gov.in",
    imageUrl: "https://images.unsplash.com/photo-1454165804606-c3d57bc86b40?w=800",
    publishedAt: "01 Sept 2026",
    relativeTime: "4d ago",
    isBreaking: false,
    isNew: true,
    vacancies: "180 पद",
    eligibility: "सिविल/मैकेनिकल इंजीनियरिंग डिप्लोमा या डिग्री",
    selectionProcess: "लिखित परीक्षा",
    officialNotificationUrl: "https://vyapam.cgstate.gov.in/admit-card-subeng.pdf",
    applyUrl: "https://vyapam.cgstate.gov.in/download-admit-card",
    importantDates: {
      applicationStart: "आवेदन संपन्न",
      lastDate: "संपन्न",
      examDate: "14 सितंबर 2026",
      admitCardDate: "जारी (01 सितंबर से डाउनलोड)"
    }
  },
  {
    id: "cgpsc_ap_result_2026",
    title: "CGPSC सहायक प्राध्यापक (Assistant Professor) परीक्षा 2025 का अंतिम परिणाम घोषित",
    summary: "उच्च शिक्षा विभाग अंतर्गत विभिन्न विषयों के 595 सहायक प्राध्यापकों के चयन सूची व कट-ऑफ मार्क्स जारी कर दिए गए हैं।",
    detailedContent: "आयोग द्वारा आयोजित साक्षात्कार उपरांत अंतिम मेरिट सूची जारी की गई है। चयनित अभ्यर्थी वेबसाइट पर अपने रोल नंबर की जांच कर सकते हैं।",
    category: "Result",
    source: "CGPSC Raipur",
    sourceUrl: "https://psc.cg.gov.in",
    imageUrl: "https://images.unsplash.com/photo-1523050854058-8df90110c9f1?w=800",
    publishedAt: "29 Aug 2026",
    relativeTime: "6d ago",
    isBreaking: false,
    isNew: false,
    vacancies: "595 पद",
    eligibility: "स्नातकोत्तर (PG) + NET/SET/Ph.D",
    selectionProcess: "लिखित परीक्षा व साक्षात्कार",
    officialNotificationUrl: "https://psc.cg.gov.in/results/ap-final-2025.pdf",
    applyUrl: null,
    importantDates: {
      applicationStart: "संपन्न",
      lastDate: "संपन्न",
      resultDate: "29 अगस्त 2026"
    }
  }
];

const SEED_ALERTS = [
  {
    id: "alert_ca_tiger",
    category: "समसामयिकी",
    title: "देश का 56वां टाइगर रिजर्व छत्तीसगढ़ में घोषित",
    shortDescription: "गुरु घासीदास-तमोर पिंगला बना प्रदेश का चौथा टाइगर रिजर्व। आगामी CGPSC व व्यापम परीक्षाओं हेतु अत्यंत महत्वपूर्ण।",
    time: "1 दिन पहले",
    type: "BREAKING",
    isRead: false,
    articleId: "cg_ca_tiger_reserve"
  },
  {
    id: "alert_cgpsc_sse",
    category: "भर्ती",
    title: "CGPSC राज्य सेवा परीक्षा 2026 विज्ञापन जारी",
    shortDescription: "242 प्रशासनिक पदों (डिप्टी कलेक्टर, DSP आदि) हेतु 30 सितंबर तक ऑनलाइन आवेदन आमंत्रित।",
    time: "3 दिन पहले",
    type: "RECRUITMENT",
    isRead: false,
    articleId: "cgpsc_sse_2026"
  },
  {
    id: "alert_tendupatta",
    category: "समसामयिकी",
    title: "तेंदूपत्ता दर ₹5,500 प्रति मानक बोरा",
    shortDescription: "राज्य शासन का बड़ा निर्णय, संग्राहक पारिश्रमिक में ₹1,500 की वृद्धि।",
    time: "2 दिन पहले",
    type: "BREAKING",
    isRead: false,
    articleId: "cg_ca_tendupatta_hike"
  },
  {
    id: "alert_sub_eng_admit",
    category: "प्रवेश पत्र",
    title: "व्यापम सब-इंजीनियर एडमिट कार्ड डाउनलोड लिंक सक्रिय",
    shortDescription: "14 सितंबर की परीक्षा हेतु छात्र तुरंत ई-एडमिट कार्ड प्राप्त करें।",
    time: "4 दिन पहले",
    type: "ADMIT_CARD",
    isRead: false,
    articleId: "cg_admit_card_sub_engineer"
  }
];

module.exports = {
  SEED_CATEGORIES,
  SEED_NEWS,
  SEED_ALERTS
};
