package com.example.ui.theme

import androidx.compose.material3.MaterialTheme
import androidx.compose.runtime.Composable
import androidx.compose.ui.graphics.Color

// Primary CGJobs Geometric Balance Green Palette
val BrandGreen = Color(0xFF2E7D32)
val BrandGreenDark = Color(0xFF1B5E20)
val BrandGreenLightStatic = Color(0xFFE8F5E9)
val BrandGreenContainer = Color(0xFFE8F5E9)
val BrandGreenAccent = Color(0xFF388E3C)

// Secondary and Accents
val BrandGold = Color(0xFFF59E0B)
val BrandGoldLight = Color(0xFFFEF3C7)
val BadgeOrange = Color(0xFFF97316)
val BreakingRed = Color(0xFFEF4444)
val BreakingRedLight = Color(0xFFFEE2E2)

// Slate & Geometric Balance Surfaces (Static references)
val Slate50Static = Color(0xFFF8FAFC)
val Slate100Static = Color(0xFFF1F5F9)
val Slate200Static = Color(0xFFE2E8F0)
val Slate600Static = Color(0xFF475569)

// Neutral Canvas & Surfaces (Static references)
val BackgroundLight = Color(0xFFF0F4F1)
val SurfaceLight = Color(0xFFFFFFFF)
val SurfaceVariantLight = Color(0xFFF1F5F9)
val BorderLightStatic = Color(0xFFE2E8F0)
val DividerLightStatic = Color(0xFFF1F5F9)

// Typography Colors (Static references)
val TextPrimaryStatic = Color(0xFF0F172A)
val TextSecondaryStatic = Color(0xFF64748B)
val TextMuted = Color(0xFF94A3B8)

// Dynamic Composable Color Getters that seamlessly adapt to Light / Dark theme:
val TextPrimary: Color
    @Composable get() = MaterialTheme.colorScheme.onSurface

val TextSecondary: Color
    @Composable get() = MaterialTheme.colorScheme.onSurfaceVariant

val Slate50: Color
    @Composable get() = MaterialTheme.colorScheme.surfaceVariant

val Slate100: Color
    @Composable get() = MaterialTheme.colorScheme.surfaceVariant

val Slate600: Color
    @Composable get() = MaterialTheme.colorScheme.onSurfaceVariant

val BorderLight: Color
    @Composable get() = MaterialTheme.colorScheme.outline

val DividerLight: Color
    @Composable get() = MaterialTheme.colorScheme.outline.copy(alpha = 0.25f)

val BrandGreenLight: Color
    @Composable get() = MaterialTheme.colorScheme.primaryContainer

// Category Specific Colors
val CategoryCgssb = Color(0xFF0B6B46)
val CategoryCgssbBg = Color(0xFFE6F4ED)

val CategoryVyapam = Color(0xFFC2185B)
val CategoryVyapamBg = Color(0xFFFCE4EC)

val CategoryCgpsc = Color(0xFF1565C0)
val CategoryCgpscBg = Color(0xFFE3F2FD)

val CategoryEducation = Color(0xFFE65100)
val CategoryEducationBg = Color(0xFFFFF3E0)

val CategoryAdmitCard = Color(0xFF6A1B9A)
val CategoryAdmitCardBg = Color(0xFFF3E5F5)

val CategoryResult = Color(0xFF00796B)
val CategoryResultBg = Color(0xFFE0F2F1)

val CategorySyllabus = Color(0xFFD97706)
val CategorySyllabusBg = Color(0xFFFEF3C7)

val CategoryCurrentAffairs = Color(0xFF283593)
val CategoryCurrentAffairsBg = Color(0xFFE8EAF6)

// Dark Palette for Dark Mode Support
val BrandGreenDarkTheme = Color(0xFF48D89C)
val BackgroundDark = Color(0xFF121815)
val SurfaceDark = Color(0xFF1B231F)
val SurfaceVariantDark = Color(0xFF25302B)
val TextPrimaryDark = Color(0xFFECF3EE)
val TextSecondaryDark = Color(0xFFA5B4AC)
val BorderDark = Color(0xFF2B3832)

