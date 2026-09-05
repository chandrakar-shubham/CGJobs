package com.example.ui.components

import androidx.compose.foundation.Canvas
import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.size
import androidx.compose.runtime.Composable
import androidx.compose.ui.Modifier
import androidx.compose.ui.geometry.Offset
import androidx.compose.ui.geometry.Size
import androidx.compose.ui.graphics.Brush
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.graphics.Path
import androidx.compose.ui.graphics.drawscope.DrawScope
import androidx.compose.ui.graphics.drawscope.clipPath
import androidx.compose.ui.unit.Dp
import androidx.compose.ui.unit.dp
import com.example.ui.theme.BrandGold
import com.example.ui.theme.BrandGreen
import com.example.ui.theme.BrandGreenDark

/**
 * Artistic representation of Chhattisgarh state map with Chitrakote waterfall,
 * lush Sal forests, temple art and rising sun motifs.
 */
@Composable
fun ArtisticChhattisgarhMap(
    modifier: Modifier = Modifier,
    isSimplified: Boolean = false
) {
    Canvas(modifier = modifier) {
        val w = size.width
        val h = size.height

        // Chhattisgarh state geographic outline path (sea-horse shape: narrow north Sarguja, wide central plains Raipur/Bilaspur, long southern tail Bastar/Dantewada)
        val cgOutline = Path().apply {
            moveTo(w * 0.48f, h * 0.05f) // North tip (Balrampur)
            lineTo(w * 0.62f, h * 0.09f)
            lineTo(w * 0.70f, h * 0.16f) // Jashpur
            lineTo(w * 0.62f, h * 0.24f) // Raigarh
            lineTo(w * 0.68f, h * 0.35f)
            lineTo(w * 0.60f, h * 0.48f) // Mahasamund
            lineTo(w * 0.55f, h * 0.58f) // Gariaband
            lineTo(w * 0.58f, h * 0.70f) // Bastar / Jagdalpur
            lineTo(w * 0.50f, h * 0.88f) // Sukma
            lineTo(w * 0.42f, h * 0.95f) // South tip (Konta)
            lineTo(w * 0.36f, h * 0.86f) // Bijapur
            lineTo(w * 0.40f, h * 0.72f) // Dantewada
            lineTo(w * 0.33f, h * 0.60f) // Kanker
            lineTo(w * 0.28f, h * 0.48f) // Rajnandgaon
            lineTo(w * 0.32f, h * 0.38f) // Kawardha
            lineTo(w * 0.30f, h * 0.26f) // Mungeli / Gaurela-Pendra
            lineTo(w * 0.38f, h * 0.16f) // Koriya
            lineTo(w * 0.44f, h * 0.08f)
            close()
        }

        if (isSimplified) {
            // Silhouette version for drawer or accents
            drawPath(
                path = cgOutline,
                color = BrandGreen.copy(alpha = 0.2f)
            )
            return@Canvas
        }

        // Clip artistic illustration inside the Chhattisgarh map contour
        clipPath(cgOutline) {
            // Gradient base: Sky to lush green and earth
            drawRect(
                brush = Brush.verticalGradient(
                    colors = listOf(
                        Color(0xFFE8F5E9),
                        Color(0xFFC8E6C9),
                        Color(0xFF81C784),
                        Color(0xFF2E7D32),
                        Color(0xFF1B5E20)
                    )
                )
            )

            // Rising sun of knowledge behind Raipur Vidhan Sabha / Education
            drawCircle(
                color = BrandGold.copy(alpha = 0.5f),
                radius = w * 0.32f,
                center = Offset(w * 0.48f, h * 0.38f)
            )

            // Green Forest Ridges (Surguja / Kanger Valley)
            val hills = Path().apply {
                moveTo(w * 0.2f, h * 0.42f)
                quadraticTo(w * 0.38f, h * 0.32f, w * 0.55f, h * 0.40f)
                quadraticTo(w * 0.72f, h * 0.35f, w * 0.85f, h * 0.46f)
                lineTo(w * 0.85f, h * 0.8f)
                lineTo(w * 0.2f, h * 0.8f)
                close()
            }
            drawPath(hills, color = Color(0xFF1B5E20).copy(alpha = 0.65f))

            // Chitrakote Waterfall (Niagara of India) representation in southern Bastar
            val waterfallStream = Path().apply {
                moveTo(w * 0.36f, h * 0.62f)
                lineTo(w * 0.60f, h * 0.62f)
                lineTo(w * 0.58f, h * 0.78f)
                quadraticTo(w * 0.48f, h * 0.82f, w * 0.38f, h * 0.78f)
                close()
            }
            drawPath(waterfallStream, color = Color(0xFF29B6F6).copy(alpha = 0.85f))

            // Water foam / spray
            val foam = Path().apply {
                moveTo(w * 0.34f, h * 0.76f)
                lineTo(w * 0.62f, h * 0.76f)
                lineTo(w * 0.58f, h * 0.82f)
                lineTo(w * 0.38f, h * 0.82f)
                close()
            }
            drawPath(foam, color = Color.White.copy(alpha = 0.75f))

            // Bastar Danteshwari Temple / Cultural Heritage terracotta spire
            val templeSpire = Path().apply {
                moveTo(w * 0.47f, h * 0.68f)
                lineTo(w * 0.50f, h * 0.60f)
                lineTo(w * 0.53f, h * 0.68f)
                close()
            }
            drawPath(templeSpire, color = Color(0xFFD84315))
            drawRect(
                color = Color(0xFFBF360C),
                topLeft = Offset(w * 0.45f, h * 0.68f),
                size = Size(w * 0.10f, h * 0.08f)
            )

            // Dome of Vidhan Sabha / State Secretariat at Raipur
            val dome = Path().apply {
                moveTo(w * 0.42f, h * 0.35f)
                quadraticTo(w * 0.48f, h * 0.28f, w * 0.54f, h * 0.35f)
                close()
            }
            drawPath(dome, color = Color(0xFFFFD54F))
            drawRect(
                color = Color(0xFFFFF8E1),
                topLeft = Offset(w * 0.41f, h * 0.35f),
                size = Size(w * 0.14f, h * 0.05f)
            )
        }

        // Sharp Outer Border of Chhattisgarh
        drawPath(
            path = cgOutline,
            color = BrandGreenDark,
            style = androidx.compose.ui.graphics.drawscope.Stroke(width = 3.dp.toPx())
        )
    }
}
