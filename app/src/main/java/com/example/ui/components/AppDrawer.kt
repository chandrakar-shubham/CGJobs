package com.example.ui.components

import android.content.Context
import android.content.Intent
import android.net.Uri
import androidx.compose.foundation.background
import androidx.compose.foundation.clickable
import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.fillMaxHeight
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.layout.size
import androidx.compose.foundation.layout.width
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.foundation.verticalScroll
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.outlined.Call
import androidx.compose.material.icons.outlined.Cloud
import androidx.compose.material.icons.outlined.DarkMode
import androidx.compose.material.icons.outlined.DateRange
import androidx.compose.material.icons.outlined.Description
import androidx.compose.material.icons.outlined.EditNote
import androidx.compose.material.icons.outlined.Gavel
import androidx.compose.material.icons.outlined.Home
import androidx.compose.material.icons.outlined.LightMode
import androidx.compose.material.icons.outlined.MenuBook
import androidx.compose.material.icons.outlined.Message
import androidx.compose.material.icons.outlined.Policy
import androidx.compose.material.icons.outlined.Public
import androidx.compose.material.icons.outlined.Search
import androidx.compose.material3.Divider
import androidx.compose.material3.HorizontalDivider
import androidx.compose.material3.Icon
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.ModalDrawerSheet
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
import com.example.ui.theme.BrandGold
import com.example.ui.theme.BrandGreen
import com.example.ui.theme.BrandGreenDark
import com.example.ui.theme.DividerLight

@Composable
fun AppDrawerContent(
    isDarkMode: Boolean,
    onNavigateHome: () -> Unit,
    onNavigateExplore: () -> Unit,
    onCategorySelected: (String) -> Unit,
    onToggleDarkMode: (Boolean) -> Unit,
    onCloseDrawer: () -> Unit,
    modifier: Modifier = Modifier,
    onNavigateCurrentAffairs: (() -> Unit)? = null,
    onNavigateStaticGk: (() -> Unit)? = null,
    onOpenServerSettings: (() -> Unit)? = null
) {
    val context = LocalContext.current
    val scrollState = rememberScrollState()

    ModalDrawerSheet(
        modifier = modifier
            .width(300.dp)
            .fillMaxHeight()
            .testTag("app_drawer_sheet"),
        drawerContainerColor = MaterialTheme.colorScheme.surface
    ) {
        Column(
            modifier = Modifier
                .fillMaxHeight()
                .verticalScroll(scrollState)
                .padding(bottom = 24.dp)
        ) {
            // Header: Brand & Tagline
            Box(
                modifier = Modifier
                    .fillMaxWidth()
                    .background(BrandGreenDark)
                    .padding(horizontal = 20.dp, vertical = 28.dp)
            ) {
                Column {
                    CGJobsLogo(fontSize = 26, capSize = 22.dp, textColor = Color.White)
                    Spacer(modifier = Modifier.height(4.dp))
                    Text(
                        text = "हर सरकारी नौकरी की सही जानकारी",
                        fontSize = 12.sp,
                        color = Color.White.copy(alpha = 0.85f),
                        fontWeight = FontWeight.Medium
                    )
                }
            }

            Spacer(modifier = Modifier.height(12.dp))

            // Menu Items
            DrawerMenuItem(
                icon = Icons.Outlined.Home,
                label = "Home",
                onClick = {
                    onNavigateHome()
                    onCloseDrawer()
                }
            )

            DrawerMenuItem(
                icon = Icons.Outlined.Search,
                label = "Explore",
                onClick = {
                    onNavigateExplore()
                    onCloseDrawer()
                }
            )

            DrawerMenuItem(
                icon = Icons.Outlined.DateRange,
                label = "Exam Calendar",
                onClick = {
                    onCategorySelected("CG Vyapam")
                    onCloseDrawer()
                }
            )

            DrawerMenuItem(
                icon = Icons.Outlined.Public,
                label = "Current Affairs",
                onClick = {
                    if (onNavigateCurrentAffairs != null) {
                        onNavigateCurrentAffairs()
                    } else {
                        onCategorySelected("Current Affairs")
                    }
                    onCloseDrawer()
                }
            )

            DrawerMenuItem(
                icon = Icons.Outlined.MenuBook,
                label = "Static GK (सामान्य ज्ञान)",
                onClick = {
                    if (onNavigateStaticGk != null) {
                        onNavigateStaticGk()
                    } else {
                        onCategorySelected("Syllabus")
                    }
                    onCloseDrawer()
                }
            )

            DrawerMenuItem(
                icon = Icons.Outlined.EditNote,
                label = "Mock Tests",
                badge = "Coming Soon",
                onClick = {}
            )

            DrawerMenuItem(
                icon = Icons.Outlined.Description,
                label = "Previous Papers",
                badge = "Coming Soon",
                onClick = {}
            )

            DrawerMenuItem(
                icon = Icons.Outlined.Cloud,
                label = "सर्वर व एडमिन (Server & Admin)",
                badge = "Live Sync",
                onClick = {
                    onOpenServerSettings?.invoke()
                    onCloseDrawer()
                }
            )

            HorizontalDivider(
                modifier = Modifier.padding(horizontal = 16.dp, vertical = 8.dp),
                color = DividerLight
            )

            // Dark Mode toggle option in Drawer
            Row(
                modifier = Modifier
                    .fillMaxWidth()
                    .clickable { onToggleDarkMode(!isDarkMode) }
                    .padding(horizontal = 20.dp, vertical = 8.dp)
                    .testTag("drawer_dark_mode_row"),
                verticalAlignment = Alignment.CenterVertically
            ) {
                Icon(
                    imageVector = if (isDarkMode) Icons.Outlined.LightMode else Icons.Outlined.DarkMode,
                    contentDescription = "Dark Theme",
                    tint = if (isDarkMode) BrandGold else MaterialTheme.colorScheme.onSurfaceVariant,
                    modifier = Modifier.size(22.dp)
                )
                Spacer(modifier = Modifier.width(16.dp))
                Column(modifier = Modifier.weight(1f)) {
                    Text(
                        text = "डार्क मोड (Dark Theme)",
                        fontSize = 14.sp,
                        fontWeight = FontWeight.Medium,
                        color = MaterialTheme.colorScheme.onSurface
                    )
                    Text(
                        text = if (isDarkMode) "चालू (Enabled)" else "बंद (Disabled)",
                        fontSize = 11.5.sp,
                        color = MaterialTheme.colorScheme.onSurfaceVariant
                    )
                }
                Switch(
                    checked = isDarkMode,
                    onCheckedChange = { onToggleDarkMode(it) },
                    colors = SwitchDefaults.colors(
                        checkedThumbColor = Color.White,
                        checkedTrackColor = BrandGreen,
                        uncheckedThumbColor = Color.Gray,
                        uncheckedTrackColor = MaterialTheme.colorScheme.surfaceVariant
                    )
                )
            }

            HorizontalDivider(
                modifier = Modifier.padding(horizontal = 16.dp, vertical = 8.dp),
                color = DividerLight
            )

            DrawerMenuItem(
                icon = Icons.Outlined.Message,
                label = "Updates via WhatsApp",
                iconTint = Color(0xFF25D366),
                onClick = {
                    openWhatsApp(context)
                    onCloseDrawer()
                }
            )

            DrawerMenuItem(
                icon = Icons.Outlined.Call,
                label = "Contact Us",
                onClick = {
                    openEmailSupport(context)
                    onCloseDrawer()
                }
            )

            DrawerMenuItem(
                icon = Icons.Outlined.Policy,
                label = "Privacy Policy",
                onClick = {
                    openUrl(context, "https://cgjobs.info/privacy")
                    onCloseDrawer()
                }
            )

            DrawerMenuItem(
                icon = Icons.Outlined.Gavel,
                label = "Terms & Conditions",
                onClick = {
                    openUrl(context, "https://cgjobs.info/terms")
                    onCloseDrawer()
                }
            )

            Spacer(modifier = Modifier.weight(1f, fill = false))
            Spacer(modifier = Modifier.height(24.dp))

            // Footer Chhattisgarh Motif
            Column(
                modifier = Modifier
                    .fillMaxWidth()
                    .padding(horizontal = 20.dp),
                horizontalAlignment = Alignment.CenterHorizontally
            ) {
                ArtisticChhattisgarhMap(
                    modifier = Modifier.size(width = 90.dp, height = 110.dp),
                    isSimplified = true
                )
                Spacer(modifier = Modifier.height(4.dp))
                Text(
                    text = "हमारा छत्तीसगढ़",
                    fontSize = 12.sp,
                    fontWeight = FontWeight.Bold,
                    color = BrandGreen
                )
                Text(
                    text = "हमारा भविष्य",
                    fontSize = 11.sp,
                    color = MaterialTheme.colorScheme.onSurfaceVariant
                )
                Spacer(modifier = Modifier.height(6.dp))
                Text(
                    text = "v1.0.0",
                    fontSize = 10.sp,
                    color = MaterialTheme.colorScheme.onSurfaceVariant.copy(alpha = 0.6f)
                )
            }
        }
    }
}

@Composable
fun DrawerMenuItem(
    icon: ImageVector,
    label: String,
    onClick: () -> Unit,
    badge: String? = null,
    iconTint: Color = MaterialTheme.colorScheme.onSurfaceVariant
) {
    Row(
        modifier = Modifier
            .fillMaxWidth()
            .clickable { onClick() }
            .padding(horizontal = 20.dp, vertical = 12.dp),
        verticalAlignment = Alignment.CenterVertically
    ) {
        Icon(
            imageVector = icon,
            contentDescription = label,
            tint = iconTint,
            modifier = Modifier.size(22.dp)
        )
        Spacer(modifier = Modifier.width(16.dp))
        Text(
            text = label,
            fontSize = 14.sp,
            fontWeight = FontWeight.Medium,
            color = MaterialTheme.colorScheme.onSurface,
            modifier = Modifier.weight(1f)
        )
        if (badge != null) {
            Box(
                modifier = Modifier
                    .clip(RoundedCornerShape(6.dp))
                    .background(MaterialTheme.colorScheme.surfaceVariant)
                    .padding(horizontal = 6.dp, vertical = 2.dp)
            ) {
                Text(
                    text = badge,
                    fontSize = 9.5.sp,
                    color = MaterialTheme.colorScheme.onSurfaceVariant,
                    fontWeight = FontWeight.SemiBold
                )
            }
        }
    }
}

private fun openWhatsApp(context: Context) {
    val intent = Intent(Intent.ACTION_VIEW).apply {
        data = Uri.parse("https://wa.me/?text=Get%20instant%20Chhattisgarh%20government%20job%20updates%20with%20CGJobs%20App!")
    }
    runCatching { context.startActivity(intent) }
}

private fun openEmailSupport(context: Context) {
    val intent = Intent(Intent.ACTION_SENDTO).apply {
        data = Uri.parse("mailto:support@cgjobs.info?subject=CGJobs%20Feedback")
    }
    runCatching { context.startActivity(intent) }
}

private fun openUrl(context: Context, url: String) {
    val intent = Intent(Intent.ACTION_VIEW, Uri.parse(url))
    runCatching { context.startActivity(intent) }
}
