package com.example.ui.screens

import android.content.Context
import android.content.Intent
import android.widget.Toast
import androidx.compose.foundation.BorderStroke
import androidx.compose.foundation.background
import androidx.compose.foundation.border
import androidx.compose.foundation.clickable
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.ExperimentalLayoutApi
import androidx.compose.foundation.layout.FlowRow
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.layout.size
import androidx.compose.foundation.layout.width
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.shape.CircleShape
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.foundation.verticalScroll
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.automirrored.filled.KeyboardArrowRight
import androidx.compose.material.icons.outlined.Cloud
import androidx.compose.material.icons.outlined.DarkMode
import androidx.compose.material.icons.outlined.Edit
import androidx.compose.material.icons.outlined.Info
import androidx.compose.material.icons.outlined.Language
import androidx.compose.material.icons.outlined.Notifications
import androidx.compose.material.icons.outlined.Share
import androidx.compose.material.icons.outlined.Star
import androidx.compose.material.icons.outlined.Tune
import androidx.compose.material3.Card
import androidx.compose.material3.CardDefaults
import androidx.compose.material3.HorizontalDivider
import androidx.compose.material3.Icon
import androidx.compose.material3.IconButton
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Switch
import androidx.compose.material3.SwitchDefaults
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.clip
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.graphics.vector.ImageVector
import androidx.compose.ui.platform.LocalContext
import androidx.compose.ui.platform.testTag
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import com.example.model.UserProfile
import com.example.ui.theme.BorderLight
import com.example.ui.theme.BrandGreen
import com.example.ui.theme.BrandGreenLight
import com.example.ui.theme.DividerLight
import com.example.ui.theme.Slate50
import com.example.ui.theme.Slate600
import com.example.ui.theme.TextPrimary
import com.example.ui.theme.TextSecondary

@OptIn(ExperimentalLayoutApi::class)
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
    onServerSettingsClick: (() -> Unit)? = null
) {
    val context = LocalContext.current
    val scrollState = rememberScrollState()

    Column(
        modifier = modifier
            .fillMaxSize()
            .background(MaterialTheme.colorScheme.background)
            .verticalScroll(scrollState)
            .padding(bottom = 90.dp)
            .testTag("profile_screen_root")
    ) {
        // Top Header
        Column(
            modifier = Modifier
                .fillMaxWidth()
                .padding(horizontal = 20.dp, vertical = 14.dp)
        ) {
            Text(
                text = "Profile",
                fontSize = 24.sp,
                fontWeight = FontWeight.Bold,
                color = TextPrimary
            )
            Text(
                text = "अपनी प्राथमिकताएं और सेटिंग्स प्रबंधित करें",
                fontSize = 12.5.sp,
                color = TextSecondary
            )
        }

        // Profile Card
        Card(
            modifier = Modifier
                .fillMaxWidth()
                .padding(horizontal = 16.dp),
            shape = RoundedCornerShape(20.dp),
            colors = CardDefaults.cardColors(containerColor = MaterialTheme.colorScheme.surface),
            border = BorderStroke(1.dp, BorderLight),
            elevation = CardDefaults.cardElevation(defaultElevation = 0.5.dp)
        ) {
            Row(
                modifier = Modifier
                    .fillMaxWidth()
                    .padding(16.dp),
                verticalAlignment = Alignment.CenterVertically
            ) {
                Box(
                    modifier = Modifier
                        .size(54.dp)
                        .clip(CircleShape)
                        .background(BrandGreen),
                    contentAlignment = Alignment.Center
                ) {
                    Text(
                        text = userProfile.name.take(2).uppercase(),
                        fontSize = 20.sp,
                        fontWeight = FontWeight.Bold,
                        color = Color.White
                    )
                }

                Spacer(modifier = Modifier.width(14.dp))

                Column(modifier = Modifier.weight(1f)) {
                    Text(
                        text = userProfile.name,
                        fontSize = 16.sp,
                        fontWeight = FontWeight.Bold,
                        color = TextPrimary
                    )
                    Text(
                        text = userProfile.email,
                        fontSize = 12.sp,
                        color = TextSecondary
                    )
                }

                Box(
                    modifier = Modifier
                        .size(40.dp)
                        .clip(CircleShape)
                        .background(Slate50)
                        .border(1.dp, BorderLight, CircleShape),
                    contentAlignment = Alignment.Center
                ) {
                    IconButton(
                        onClick = onEditProfileClick,
                        modifier = Modifier.testTag("profile_edit_btn")
                    ) {
                        Icon(
                            imageVector = Icons.Outlined.Edit,
                            contentDescription = "Edit Profile",
                            tint = BrandGreen,
                            modifier = Modifier.size(18.dp)
                        )
                    }
                }
            }
        }

        Spacer(modifier = Modifier.height(20.dp))

        // My Interests Section
        Column(
            modifier = Modifier
                .fillMaxWidth()
                .padding(horizontal = 16.dp)
        ) {
            Row(
                modifier = Modifier.fillMaxWidth(),
                verticalAlignment = Alignment.CenterVertically,
                horizontalArrangement = Arrangement.SpaceBetween
            ) {
                Text(
                    text = "मेरी रुचियां (My Interests)",
                    fontSize = 15.sp,
                    fontWeight = FontWeight.Bold,
                    color = TextPrimary
                )
                Box(
                    modifier = Modifier
                        .clip(RoundedCornerShape(10.dp))
                        .background(BrandGreenLight)
                        .clickable { onChangeInterestsClick() }
                        .padding(horizontal = 10.dp, vertical = 4.dp)
                        .testTag("change_interests_btn")
                ) {
                    Text(
                        text = "बदलें",
                        fontSize = 12.sp,
                        fontWeight = FontWeight.Bold,
                        color = BrandGreen
                    )
                }
            }

            Spacer(modifier = Modifier.height(12.dp))

            FlowRow(
                horizontalArrangement = Arrangement.spacedBy(8.dp),
                verticalArrangement = Arrangement.spacedBy(8.dp)
            ) {
                userProfile.selectedInterests.forEach { interest ->
                    Box(
                        modifier = Modifier
                            .clip(CircleShape)
                            .background(BrandGreenLight)
                            .border(1.dp, BrandGreen.copy(alpha = 0.2f), CircleShape)
                            .padding(horizontal = 14.dp, vertical = 6.dp)
                    ) {
                        Text(
                            text = interest,
                            fontSize = 12.sp,
                            color = BrandGreen,
                            fontWeight = FontWeight.Bold
                        )
                    }
                }
            }
        }

        Spacer(modifier = Modifier.height(24.dp))

        // Settings Section
        Column(
            modifier = Modifier
                .fillMaxWidth()
                .padding(horizontal = 16.dp)
        ) {
            Text(
                text = "सेटिंग्स (Settings)",
                fontSize = 15.sp,
                fontWeight = FontWeight.Bold,
                color = TextPrimary
            )

            Spacer(modifier = Modifier.height(10.dp))

            Card(
                modifier = Modifier.fillMaxWidth(),
                shape = RoundedCornerShape(20.dp),
                colors = CardDefaults.cardColors(containerColor = MaterialTheme.colorScheme.surface),
                border = BorderStroke(1.dp, BorderLight),
                elevation = CardDefaults.cardElevation(defaultElevation = 0.5.dp)
            ) {
                Column(modifier = Modifier.padding(16.dp)) {
                    // Push Notification Switch
                    SettingToggleRow(
                        icon = Icons.Outlined.Notifications,
                        title = "Push Notifications",
                        subtitle = "महत्वपूर्ण भर्ती सूचनाएं तुरंत प्राप्त करें",
                        checked = userProfile.notificationsEnabled,
                        onCheckedChange = onNotificationToggle
                    )

                    HorizontalDivider(modifier = Modifier.padding(vertical = 12.dp), color = DividerLight)

                    // Exam Date Alerts Switch
                    SettingToggleRow(
                        icon = Icons.Outlined.Tune,
                        title = "Exam Date Alerts",
                        subtitle = "परीक्षा तिथियों व एडमिट कार्ड की याद दिलाएं",
                        checked = userProfile.examAlertsEnabled,
                        onCheckedChange = onExamAlertsToggle
                    )

                    HorizontalDivider(modifier = Modifier.padding(vertical = 12.dp), color = DividerLight)

                    // Result Alerts Switch
                    SettingToggleRow(
                        icon = Icons.Outlined.Tune,
                        title = "Result & Merit Alerts",
                        subtitle = "परिणाम और चयन सूची अपडेट्स",
                        checked = userProfile.resultAlertsEnabled,
                        onCheckedChange = onResultAlertsToggle
                    )

                    HorizontalDivider(modifier = Modifier.padding(vertical = 12.dp), color = DividerLight)

                    // Dark Mode Switch
                    SettingToggleRow(
                        icon = Icons.Outlined.DarkMode,
                        title = "Dark Theme",
                        subtitle = "आँखों के लिए आरामदायक डार्क मोड",
                        checked = userProfile.isDarkMode,
                        onCheckedChange = onDarkModeToggle
                    )

                    HorizontalDivider(modifier = Modifier.padding(vertical = 12.dp), color = DividerLight)

                    // Language Selector Row
                    Row(
                        modifier = Modifier
                            .fillMaxWidth()
                            .clickable {
                                Toast.makeText(context, "भाषा: हिंदी (डिफ़ॉल्ट)", Toast.LENGTH_SHORT).show()
                            }
                            .padding(vertical = 4.dp),
                        verticalAlignment = Alignment.CenterVertically
                    ) {
                        Box(
                            modifier = Modifier
                                .size(38.dp)
                                .clip(RoundedCornerShape(12.dp))
                                .background(BrandGreenLight),
                            contentAlignment = Alignment.Center
                        ) {
                            Icon(
                                imageVector = Icons.Outlined.Language,
                                contentDescription = "Language",
                                tint = BrandGreen,
                                modifier = Modifier.size(20.dp)
                            )
                        }
                        Spacer(modifier = Modifier.width(14.dp))
                        Column(modifier = Modifier.weight(1f)) {
                            Text(
                                text = "भाषा / Language",
                                fontSize = 14.sp,
                                fontWeight = FontWeight.SemiBold,
                                color = TextPrimary
                            )
                            Text(
                                text = "हिंदी (Hindi)",
                                fontSize = 12.sp,
                                color = TextSecondary
                            )
                        }
                        Icon(
                            imageVector = Icons.AutoMirrored.Filled.KeyboardArrowRight,
                            contentDescription = "Select",
                            tint = Slate600
                        )
                    }
                }
            }
        }

        Spacer(modifier = Modifier.height(24.dp))

        // Support & About Section
        Column(
            modifier = Modifier
                .fillMaxWidth()
                .padding(horizontal = 16.dp)
        ) {
            Text(
                text = "सपोर्ट व जानकारी (Support & About)",
                fontSize = 15.sp,
                fontWeight = FontWeight.Bold,
                color = TextPrimary
            )

            Spacer(modifier = Modifier.height(10.dp))

            Card(
                modifier = Modifier.fillMaxWidth(),
                shape = RoundedCornerShape(20.dp),
                colors = CardDefaults.cardColors(containerColor = MaterialTheme.colorScheme.surface),
                border = BorderStroke(1.dp, BorderLight),
                elevation = CardDefaults.cardElevation(defaultElevation = 0.5.dp)
            ) {
                Column(modifier = Modifier.padding(16.dp)) {
                    SettingActionRow(
                        icon = Icons.Outlined.Share,
                        title = "मित्रों के साथ शेयर करें",
                        onClick = { shareApp(context) }
                    )

                    HorizontalDivider(modifier = Modifier.padding(vertical = 12.dp), color = DividerLight)

                    SettingActionRow(
                        icon = Icons.Outlined.Star,
                        title = "रेटिंग दें (Rate Us)",
                        onClick = {
                            Toast.makeText(context, "CGJobs को 5 स्टार देने के लिए धन्यवाद!", Toast.LENGTH_SHORT).show()
                        }
                    )

                    HorizontalDivider(modifier = Modifier.padding(vertical = 12.dp), color = DividerLight)

                    SettingActionRow(
                        icon = Icons.Outlined.Info,
                        title = "About CGJobs",
                        onClick = onAboutClick
                    )

                    HorizontalDivider(modifier = Modifier.padding(vertical = 12.dp), color = DividerLight)

                    SettingActionRow(
                        icon = Icons.Outlined.Cloud,
                        title = "सर्वर व बैकएंड सेटिंग्स (Server Settings)",
                        onClick = { onServerSettingsClick?.invoke() }
                    )
                }
            }
        }

        Spacer(modifier = Modifier.height(24.dp))

        // Official Disclaimer
        Text(
            text = "अस्वीकरण (Disclaimer): CGJobs छत्तीसगढ़ के युवाओं के लिए सरकारी नौकरियों, परीक्षाओं और परिणामों की जानकारी एकत्रित करने वाला एक स्वतंत्र मंच है। यह किसी भी सरकारी विभाग का आधिकारिक ऐप नहीं है। सभी जानकारियां संबंधित विभागों के आधिकारिक विज्ञापनों पर आधारित हैं।",
            fontSize = 11.sp,
            lineHeight = 16.sp,
            color = TextSecondary.copy(alpha = 0.8f),
            modifier = Modifier.padding(horizontal = 20.dp)
        )
    }
}

@Composable
fun SettingToggleRow(
    icon: ImageVector,
    title: String,
    subtitle: String,
    checked: Boolean,
    onCheckedChange: (Boolean) -> Unit
) {
    Row(
        modifier = Modifier.fillMaxWidth(),
        verticalAlignment = Alignment.CenterVertically
    ) {
        Box(
            modifier = Modifier
                .size(38.dp)
                .clip(RoundedCornerShape(12.dp))
                .background(BrandGreenLight),
            contentAlignment = Alignment.Center
        ) {
            Icon(
                imageVector = icon,
                contentDescription = title,
                tint = BrandGreen,
                modifier = Modifier.size(20.dp)
            )
        }
        Spacer(modifier = Modifier.width(14.dp))
        Column(modifier = Modifier.weight(1f)) {
            Text(
                text = title,
                fontSize = 14.sp,
                fontWeight = FontWeight.SemiBold,
                color = TextPrimary
            )
            Text(
                text = subtitle,
                fontSize = 11.5.sp,
                color = TextSecondary
            )
        }
        Switch(
            checked = checked,
            onCheckedChange = onCheckedChange,
            colors = SwitchDefaults.colors(
                checkedThumbColor = Color.White,
                checkedTrackColor = BrandGreen
            )
        )
    }
}

@Composable
fun SettingActionRow(
    icon: ImageVector,
    title: String,
    onClick: () -> Unit
) {
    Row(
        modifier = Modifier
            .fillMaxWidth()
            .clickable { onClick() }
            .padding(vertical = 4.dp),
        verticalAlignment = Alignment.CenterVertically
    ) {
        Box(
            modifier = Modifier
                .size(38.dp)
                .clip(RoundedCornerShape(12.dp))
                .background(BrandGreenLight),
            contentAlignment = Alignment.Center
        ) {
            Icon(
                imageVector = icon,
                contentDescription = title,
                tint = BrandGreen,
                modifier = Modifier.size(20.dp)
            )
        }
        Spacer(modifier = Modifier.width(14.dp))
        Text(
            text = title,
            fontSize = 14.sp,
            fontWeight = FontWeight.SemiBold,
            color = TextPrimary,
            modifier = Modifier.weight(1f)
        )
        Icon(
            imageVector = Icons.AutoMirrored.Filled.KeyboardArrowRight,
            contentDescription = "Open",
            tint = Slate600
        )
    }
}

private fun shareApp(context: Context) {
    val shareIntent = Intent(Intent.ACTION_SEND).apply {
        type = "text/plain"
        putExtra(
            Intent.EXTRA_TEXT,
            "छत्तीसगढ़ की हर सरकारी नौकरी, व्यापम, CGPSC, शिक्षक भर्ती की सटीक जानकारी के लिए CGJobs ऐप डाउनलोड करें: https://cgjobs.info"
        )
    }
    context.startActivity(Intent.createChooser(shareIntent, "Share CGJobs App"))
}
