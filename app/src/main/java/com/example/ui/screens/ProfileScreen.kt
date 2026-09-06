package com.example.ui.screens

import androidx.compose.foundation.background
import androidx.compose.foundation.clickable
import androidx.compose.foundation.layout.*
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.foundation.verticalScroll
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.outlined.DarkMode
import androidx.compose.material.icons.outlined.Language
import androidx.compose.material.icons.outlined.Notifications
import androidx.compose.material.icons.outlined.Tune
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import com.example.model.UserProfile
import com.example.ui.theme.BrandGreen
import com.example.ui.theme.BrandGreenLight
import com.example.ui.theme.TextPrimary
import com.example.ui.theme.TextSecondary

@Composable
fun ProfileScreen(
    userProfile: UserProfile,
    onEditProfileClick: () -> Unit,
    onChangeInterestsClick: () -> Unit,
    onNotificationToggle: (Boolean) -> Unit,
    onExamAlertsToggle: (Boolean) -> Unit,
    onResultAlertsToggle: (Boolean) -> Unit,
    onDarkModeToggle: (Boolean) -> Unit,
    onAboutClick: () -> Unit,
    modifier: Modifier = Modifier,
    onServerSettingsClick: (() -> Unit)? = null,
    onLanguageChange: ((String) -> Unit)? = null
) {
    var languageDialog by remember { mutableStateOf(false) }
    val english = userProfile.language.equals("English", true) || userProfile.language == "en"
    val scroll = rememberScrollState()
    Column(modifier.fillMaxSize().verticalScroll(scroll).padding(20.dp), verticalArrangement = Arrangement.spacedBy(14.dp)) {
        Text(if (english) "Profile" else "प्रोफाइल", fontSize = 26.sp, color = TextPrimary)
        Text(if (english) "Manage your preferences and settings" else "अपनी प्राथमिकताएं और सेटिंग्स प्रबंधित करें", color = TextSecondary)
        Card(Modifier.fillMaxWidth(), shape = RoundedCornerShape(18.dp)) {
            Column(Modifier.padding(16.dp), verticalArrangement = Arrangement.spacedBy(4.dp)) {
                Text(userProfile.name, style = MaterialTheme.typography.titleMedium)
                Text(userProfile.email, color = TextSecondary)
            }
        }
        Text(if (english) "Settings" else "सेटिंग्स (Settings)", style = MaterialTheme.typography.titleMedium)
        Card(Modifier.fillMaxWidth(), shape = RoundedCornerShape(18.dp)) {
            Column(Modifier.padding(16.dp)) {
                SettingToggleRow(Icons.Outlined.Notifications, if (english) "Push Notifications" else "पुश नोटिफिकेशन", if (english) "Get important recruitment alerts" else "महत्वपूर्ण भर्ती सूचनाएं तुरंत प्राप्त करें", userProfile.notificationsEnabled, onNotificationToggle)
                HorizontalDivider(Modifier.padding(vertical = 10.dp))
                SettingToggleRow(Icons.Outlined.Tune, if (english) "Exam Date Alerts" else "परीक्षा तिथि अलर्ट", if (english) "Exam dates and admit card reminders" else "परीक्षा तिथियों व एडमिट कार्ड की याद दिलाएं", userProfile.examAlertsEnabled, onExamAlertsToggle)
                HorizontalDivider(Modifier.padding(vertical = 10.dp))
                SettingToggleRow(Icons.Outlined.Tune, if (english) "Result & Merit Alerts" else "परिणाम और मेरिट अलर्ट", if (english) "Results and selection list updates" else "परिणाम और चयन सूची अपडेट्स", userProfile.resultAlertsEnabled, onResultAlertsToggle)
                HorizontalDivider(Modifier.padding(vertical = 10.dp))
                SettingToggleRow(Icons.Outlined.DarkMode, if (english) "Dark Theme" else "डार्क थीम", if (english) "Comfortable dark mode" else "आँखों के लिए आरामदायक डार्क मोड", userProfile.isDarkMode, onDarkModeToggle)
                HorizontalDivider(Modifier.padding(vertical = 10.dp))
                Row(Modifier.fillMaxWidth().clickable { languageDialog = true }.padding(vertical = 8.dp), verticalAlignment = Alignment.CenterVertically) {
                    Icon(Icons.Outlined.Language, null, tint = BrandGreen, modifier = Modifier.size(26.dp))
                    Spacer(Modifier.width(14.dp))
                    Column(Modifier.weight(1f)) { Text(if (english) "Language" else "भाषा / Language", color = TextPrimary); Text(if (english) "English" else "हिंदी (Hindi)", fontSize = 12.sp, color = TextSecondary) }
                    Text("›", fontSize = 28.sp, color = TextSecondary)
                }
            }
        }
        Button(onClick = onChangeInterestsClick, modifier = Modifier.fillMaxWidth()) { Text(if (english) "Change Interests" else "रुचियां बदलें") }
        OutlinedButton(onClick = onEditProfileClick, modifier = Modifier.fillMaxWidth()) { Text(if (english) "Edit Profile" else "प्रोफाइल संपादित करें") }
        OutlinedButton(onClick = onAboutClick, modifier = Modifier.fillMaxWidth()) { Text("About CGJobs") }
        if (onServerSettingsClick != null) OutlinedButton(onClick = onServerSettingsClick, modifier = Modifier.fillMaxWidth()) { Text(if (english) "Server Settings" else "सर्वर सेटिंग्स") }
    }
    if (languageDialog) {
        AlertDialog(onDismissRequest = { languageDialog = false }, title = { Text("भाषा / Language") }, text = {
            Column {
                Row(Modifier.fillMaxWidth().clickable { onLanguageChange?.invoke("Hindi"); languageDialog = false }.padding(8.dp), verticalAlignment = Alignment.CenterVertically) { RadioButton(!english, null); Text("हिंदी (Hindi)") }
                Row(Modifier.fillMaxWidth().clickable { onLanguageChange?.invoke("English"); languageDialog = false }.padding(8.dp), verticalAlignment = Alignment.CenterVertically) { RadioButton(english, null); Text("English") }
            }
        }, confirmButton = { TextButton({ languageDialog = false }) { Text(if (english) "Done" else "ठीक है") } })
    }
}

@Composable
fun SettingToggleRow(icon: androidx.compose.ui.graphics.vector.ImageVector, title: String, subtitle: String, checked: Boolean, onCheckedChange: (Boolean) -> Unit) {
    Row(Modifier.fillMaxWidth(), verticalAlignment = Alignment.CenterVertically) {
        Icon(icon, null, tint = BrandGreen, modifier = Modifier.size(26.dp))
        Spacer(Modifier.width(12.dp))
        Column(Modifier.weight(1f)) { Text(title, color = TextPrimary); Text(subtitle, fontSize = 11.sp, color = TextSecondary) }
        Switch(checked, onCheckedChange)
    }
}
