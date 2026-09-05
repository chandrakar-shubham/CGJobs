package com.example.ui.screens

import androidx.compose.animation.core.Animatable
import androidx.compose.animation.core.FastOutSlowInEasing
import androidx.compose.animation.core.tween
import androidx.compose.foundation.Canvas
import androidx.compose.foundation.background
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.layout.size
import androidx.compose.foundation.layout.width
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.automirrored.filled.ArrowForward
import androidx.compose.material3.Button
import androidx.compose.material3.ButtonDefaults
import androidx.compose.material3.Icon
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.runtime.LaunchedEffect
import androidx.compose.runtime.remember
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.alpha
import androidx.compose.ui.draw.scale
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.graphics.Path
import androidx.compose.ui.platform.testTag
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.style.TextAlign
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import com.example.ui.components.ArtisticChhattisgarhMap
import com.example.ui.components.CGJobsLogo
import com.example.ui.theme.BrandGold
import com.example.ui.theme.BrandGreen
import com.example.ui.theme.BrandGreenDark
import com.example.ui.theme.TextPrimary
import com.example.ui.theme.TextSecondary

@Composable
fun SplashScreen(
    onDismiss: () -> Unit,
    modifier: Modifier = Modifier
) {
    val alphaAnim = remember { Animatable(0f) }
    val scaleAnim = remember { Animatable(0.92f) }

    LaunchedEffect(Unit) {
        alphaAnim.animateTo(
            targetValue = 1f,
            animationSpec = tween(durationMillis = 650, easing = FastOutSlowInEasing)
        )
        scaleAnim.animateTo(
            targetValue = 1f,
            animationSpec = tween(durationMillis = 650, easing = FastOutSlowInEasing)
        )
    }

    Box(
        modifier = modifier
            .fillMaxSize()
            .background(Color.White)
            .testTag("splash_screen_root")
    ) {
        // Decorative bottom wave curves in green & gold
        Canvas(
            modifier = Modifier
                .fillMaxWidth()
                .height(130.dp)
                .align(Alignment.BottomCenter)
        ) {
            val w = size.width
            val h = size.height

            // Gold accent wave
            val goldWave = Path().apply {
                moveTo(0f, h * 0.40f)
                quadraticTo(w * 0.35f, h * 0.15f, w * 0.70f, h * 0.45f)
                quadraticTo(w * 0.88f, h * 0.60f, w, h * 0.35f)
                lineTo(w, h)
                lineTo(0f, h)
                close()
            }
            drawPath(goldWave, color = BrandGold)

            // Deep green wave
            val greenWave = Path().apply {
                moveTo(0f, h * 0.50f)
                quadraticTo(w * 0.30f, h * 0.75f, w * 0.65f, h * 0.45f)
                quadraticTo(w * 0.85f, h * 0.28f, w, h * 0.45f)
                lineTo(w, h)
                lineTo(0f, h)
                close()
            }
            drawPath(greenWave, color = BrandGreen)

            // Dark green bottom wave
            val darkWave = Path().apply {
                moveTo(0f, h * 0.70f)
                quadraticTo(w * 0.40f, h * 0.50f, w * 0.80f, h * 0.70f)
                lineTo(w, h)
                lineTo(0f, h)
                close()
            }
            drawPath(darkWave, color = BrandGreenDark)
        }

        // Main Splash Content
        Column(
            modifier = Modifier
                .fillMaxSize()
                .padding(horizontal = 24.dp)
                .padding(top = 48.dp, bottom = 40.dp)
                .alpha(alphaAnim.value)
                .scale(scaleAnim.value),
            horizontalAlignment = Alignment.CenterHorizontally,
            verticalArrangement = Arrangement.SpaceBetween
        ) {
            // Top Section
            Column(horizontalAlignment = Alignment.CenterHorizontally) {
                Text(
                    text = "छत्तीसगढ़ के युवाओं के लिए\nएक भरोसेमंद साथी",
                    fontSize = 15.sp,
                    lineHeight = 22.sp,
                    fontWeight = FontWeight.Medium,
                    color = TextSecondary,
                    textAlign = TextAlign.Center
                )

                Spacer(modifier = Modifier.height(18.dp))

                CGJobsLogo(
                    fontSize = 42,
                    capSize = 34.dp
                )

                Spacer(modifier = Modifier.height(6.dp))

                Text(
                    text = "हर सरकारी नौकरी की सही जानकारी",
                    fontSize = 14.5.sp,
                    fontWeight = FontWeight.SemiBold,
                    color = BrandGreen,
                    textAlign = TextAlign.Center,
                    letterSpacing = 0.2.sp
                )
            }

            // Center Illustration: Beautiful Chhattisgarh State Map Artwork
            Box(
                modifier = Modifier
                    .size(width = 240.dp, height = 310.dp)
                    .padding(vertical = 12.dp),
                contentAlignment = Alignment.Center
            ) {
                ArtisticChhattisgarhMap(
                    modifier = Modifier.fillMaxSize()
                )
            }

            // Bottom Section: Tagline & Enter Button
            Column(
                horizontalAlignment = Alignment.CenterHorizontally,
                modifier = Modifier.fillMaxWidth()
            ) {
                Text(
                    text = "शिक्षा | रोजगार | अवसर | उज्ज्वल भविष्य",
                    fontSize = 13.5.sp,
                    fontWeight = FontWeight.SemiBold,
                    color = TextPrimary,
                    textAlign = TextAlign.Center
                )

                Spacer(modifier = Modifier.height(16.dp))

                Button(
                    onClick = onDismiss,
                    colors = ButtonDefaults.buttonColors(containerColor = BrandGreen),
                    shape = RoundedCornerShape(24.dp),
                    modifier = Modifier
                        .fillMaxWidth(0.65f)
                        .height(44.dp)
                        .testTag("splash_enter_button")
                ) {
                    Text(
                        text = "शुरू करें",
                        fontSize = 15.sp,
                        fontWeight = FontWeight.Bold,
                        color = Color.White
                    )
                    Spacer(modifier = Modifier.width(6.dp))
                    Icon(
                        imageVector = Icons.AutoMirrored.Filled.ArrowForward,
                        contentDescription = "Explore",
                        tint = Color.White,
                        modifier = Modifier.size(16.dp)
                    )
                }
            }
        }
    }
}
