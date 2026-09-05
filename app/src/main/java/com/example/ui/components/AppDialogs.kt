package com.example.ui.components

import androidx.compose.foundation.clickable
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.layout.size
import androidx.compose.foundation.layout.width
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.foundation.verticalScroll
import androidx.compose.material3.AlertDialog
import androidx.compose.material3.Button
import androidx.compose.material3.ButtonDefaults
import androidx.compose.material3.Checkbox
import androidx.compose.material3.CheckboxDefaults
import androidx.compose.material3.OutlinedTextField
import androidx.compose.material3.Text
import androidx.compose.material3.TextButton
import androidx.compose.runtime.Composable
import androidx.compose.runtime.getValue
import androidx.compose.runtime.mutableStateOf
import androidx.compose.runtime.remember
import androidx.compose.runtime.setValue
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import com.example.ui.theme.BrandGreen
import com.example.ui.theme.TextPrimary
import com.example.ui.theme.TextSecondary

val ALL_INTEREST_OPTIONS = listOf(
    "CGSSB (शिक्षक भर्ती)",
    "CG Vyapam (पटवारी/RI)",
    "CGPSC (राज्य सेवा)",
    "Police (आरक्षक/SI)",
    "शिक्षा व उच्च शिक्षा विभाग",
    "इंजीनियरिंग व तकनीकी पद",
    "स्वास्थ्य विभाग (चिकित्सा/नर्सिंग)",
    "दैनिक समसामयिकी (Current Affairs)"
)

@Composable
fun InterestsDialog(
    currentInterests: Set<String>,
    onDismiss: () -> Unit,
    onSave: (Set<String>) -> Unit
) {
    var selected by remember { mutableStateOf(currentInterests) }
    val scrollState = rememberScrollState()

    AlertDialog(
        onDismissRequest = onDismiss,
        shape = RoundedCornerShape(24.dp),
        title = {
            Text(
                text = "अपनी रुचियां चुनें",
                fontSize = 18.sp,
                fontWeight = FontWeight.Bold,
                color = TextPrimary
            )
        },
        text = {
            Column(
                modifier = Modifier
                    .fillMaxWidth()
                    .verticalScroll(scrollState)
            ) {
                Text(
                    text = "चयनित श्रेणियों के नोटिफिकेशन व अपडेट्स को प्राथमिकता दी जाएगी:",
                    fontSize = 13.sp,
                    color = TextSecondary
                )
                Spacer(modifier = Modifier.height(10.dp))

                ALL_INTEREST_OPTIONS.forEach { option ->
                    val isChecked = selected.contains(option)
                    Row(
                        modifier = Modifier
                            .fillMaxWidth()
                            .clickable {
                                selected = if (isChecked) selected - option else selected + option
                            }
                            .padding(vertical = 4.dp),
                        verticalAlignment = Alignment.CenterVertically
                    ) {
                        Checkbox(
                            checked = isChecked,
                            onCheckedChange = { check ->
                                selected = if (check) selected + option else selected - option
                            },
                            colors = CheckboxDefaults.colors(checkedColor = BrandGreen)
                        )
                        Spacer(modifier = Modifier.width(6.dp))
                        Text(
                            text = option,
                            fontSize = 13.5.sp,
                            color = TextPrimary
                        )
                    }
                }
            }
        },
        confirmButton = {
            Button(
                onClick = { onSave(selected) },
                colors = ButtonDefaults.buttonColors(containerColor = BrandGreen),
                shape = RoundedCornerShape(14.dp)
            ) {
                Text("सहेजें (Save)", fontWeight = FontWeight.Bold)
            }
        },
        dismissButton = {
            TextButton(onClick = onDismiss) {
                Text("रद्द करें", color = TextSecondary)
            }
        }
    )
}

@Composable
fun EditProfileDialog(
    currentName: String,
    currentEmail: String,
    onDismiss: () -> Unit,
    onSave: (String, String) -> Unit
) {
    var name by remember { mutableStateOf(currentName) }
    var email by remember { mutableStateOf(currentEmail) }

    AlertDialog(
        onDismissRequest = onDismiss,
        shape = RoundedCornerShape(24.dp),
        title = {
            Text(
                text = "प्रोफ़ाइल संपादित करें",
                fontSize = 18.sp,
                fontWeight = FontWeight.Bold,
                color = TextPrimary
            )
        },
        text = {
            Column(modifier = Modifier.fillMaxWidth()) {
                OutlinedTextField(
                    value = name,
                    onValueChange = { name = it },
                    label = { Text("नाम") },
                    singleLine = true,
                    shape = RoundedCornerShape(12.dp),
                    modifier = Modifier.fillMaxWidth()
                )
                Spacer(modifier = Modifier.height(12.dp))
                OutlinedTextField(
                    value = email,
                    onValueChange = { email = it },
                    label = { Text("ईमेल") },
                    singleLine = true,
                    shape = RoundedCornerShape(12.dp),
                    modifier = Modifier.fillMaxWidth()
                )
            }
        },
        confirmButton = {
            Button(
                onClick = { onSave(name, email) },
                colors = ButtonDefaults.buttonColors(containerColor = BrandGreen),
                shape = RoundedCornerShape(14.dp)
            ) {
                Text("अपडेट करें", fontWeight = FontWeight.Bold)
            }
        },
        dismissButton = {
            TextButton(onClick = onDismiss) {
                Text("रद्द करें", color = TextSecondary)
            }
        }
    )
}

@Composable
fun AboutDialog(
    onDismiss: () -> Unit
) {
    AlertDialog(
        onDismissRequest = onDismiss,
        shape = RoundedCornerShape(24.dp),
        title = {
            Row(verticalAlignment = Alignment.CenterVertically) {
                CGJobsLogo(fontSize = 20, capSize = 16.dp)
                Spacer(modifier = Modifier.width(6.dp))
                Text("के बारे में", fontSize = 16.sp, fontWeight = FontWeight.Bold, color = TextPrimary)
            }
        },
        text = {
            Column(modifier = Modifier.fillMaxWidth()) {
                Text(
                    text = "हर सरकारी नौकरी की सही जानकारी",
                    fontSize = 13.5.sp,
                    fontWeight = FontWeight.Bold,
                    color = BrandGreen
                )
                Spacer(modifier = Modifier.height(8.dp))
                Text(
                    text = "CGJobs छत्तीसगढ़ के लाखों प्रतियोगी छात्र-छात्राओं के लिए समर्पित एक अत्याधुनिक मंच है। हमारा उद्देश्य सरकारी नौकरी के विज्ञापनों, परीक्षा तिथियों, सिलेबस और परिणामों को बिना किसी भ्रामक जानकारी के सरल व तीव्र रूप में आप तक पहुंचाना है।",
                    fontSize = 12.5.sp,
                    lineHeight = 18.sp,
                    color = TextSecondary
                )
                Spacer(modifier = Modifier.height(12.dp))
                Text(
                    text = "संस्करण: v1.0.0 (Native Android Compose)",
                    fontSize = 11.5.sp,
                    fontWeight = FontWeight.Medium,
                    color = TextSecondary
                )
                Text(
                    text = "बनाया गया: छत्तीसगढ़ के युवाओं के उज्ज्वल भविष्य के लिए",
                    fontSize = 11.sp,
                    color = TextSecondary
                )
            }
        },
        confirmButton = {
            Button(
                onClick = onDismiss,
                colors = ButtonDefaults.buttonColors(containerColor = BrandGreen),
                shape = RoundedCornerShape(14.dp)
            ) {
                Text("ठीक है", fontWeight = FontWeight.Bold)
            }
        }
    )
}

