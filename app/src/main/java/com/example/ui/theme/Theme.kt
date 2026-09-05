package com.example.ui.theme

import androidx.compose.foundation.isSystemInDarkTheme
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.darkColorScheme
import androidx.compose.material3.lightColorScheme
import androidx.compose.runtime.Composable
import androidx.compose.ui.graphics.Color

private val DarkColorScheme = darkColorScheme(
    primary = BrandGreenDarkTheme,
    onPrimary = Color(0xFF003822),
    primaryContainer = Color(0xFF005234),
    onPrimaryContainer = Color(0xFF8FF5BE),
    secondary = BrandGold,
    onSecondary = Color(0xFF432C00),
    secondaryContainer = Color(0xFF5F4100),
    onSecondaryContainer = Color(0xFFFFDEA3),
    tertiary = CategoryCgpsc,
    background = BackgroundDark,
    onBackground = TextPrimaryDark,
    surface = SurfaceDark,
    onSurface = TextPrimaryDark,
    surfaceVariant = SurfaceVariantDark,
    onSurfaceVariant = TextSecondaryDark,
    outline = BorderDark,
    error = Color(0xFFFFB4AB),
    onError = Color(0xFF690005)
)

private val LightColorScheme = lightColorScheme(
    primary = BrandGreen,
    onPrimary = Color.White,
    primaryContainer = BrandGreenContainer,
    onPrimaryContainer = BrandGreenDark,
    secondary = BrandGold,
    onSecondary = Color.White,
    secondaryContainer = BrandGoldLight,
    onSecondaryContainer = Color(0xFF7A4A00),
    tertiary = CategoryCgpsc,
    background = BackgroundLight,
    onBackground = TextPrimaryStatic,
    surface = SurfaceLight,
    onSurface = TextPrimaryStatic,
    surfaceVariant = SurfaceVariantLight,
    onSurfaceVariant = TextSecondaryStatic,
    outline = BorderLightStatic,
    outlineVariant = DividerLightStatic,
    error = BreakingRed,
    onError = Color.White
)

@Composable
fun CGJobsTheme(
    darkTheme: Boolean = false,
    content: @Composable () -> Unit
) {
    val colorScheme = if (darkTheme) DarkColorScheme else LightColorScheme

    MaterialTheme(
        colorScheme = colorScheme,
        typography = Typography,
        content = content
    )
}
