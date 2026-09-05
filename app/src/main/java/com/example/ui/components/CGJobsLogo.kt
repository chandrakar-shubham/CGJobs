package com.example.ui.components

import androidx.compose.foundation.Canvas
import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.offset
import androidx.compose.foundation.layout.size
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.graphics.Path
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.unit.Dp
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import com.example.ui.theme.BrandGold
import com.example.ui.theme.BrandGreen

/**
 * Renders the distinctive CGJobs brand logo with the graduation mortarboard perched on top.
 */
@Composable
fun CGJobsLogo(
    modifier: Modifier = Modifier,
    fontSize: Int = 22,
    capSize: Dp = 18.dp,
    textColor: Color = MaterialTheme.colorScheme.onSurface
) {
    Box(
        modifier = modifier,
        contentAlignment = Alignment.CenterStart
    ) {
        Row(verticalAlignment = Alignment.CenterVertically) {
            Text(
                text = "CG",
                color = BrandGreen,
                fontSize = fontSize.sp,
                fontWeight = FontWeight.Black,
                letterSpacing = (-0.5).sp
            )
            Box {
                Text(
                    text = "Jobs",
                    color = textColor,
                    fontSize = fontSize.sp,
                    fontWeight = FontWeight.ExtraBold,
                    letterSpacing = (-0.5).sp
                )
                // Graduation mortarboard icon hovering over the 'J'
                GraduationCapMini(
                    modifier = Modifier
                        .size(capSize)
                        .offset(x = 2.dp, y = (-capSize / 2) + 2.dp)
                )
            }
        }
    }
}

@Composable
fun GraduationCapMini(
    modifier: Modifier = Modifier,
    capColor: Color = BrandGreen,
    tasselColor: Color = BrandGold
) {
    Canvas(modifier = modifier) {
        val w = size.width
        val h = size.height

        // Cap Diamond Top
        val diamond = Path().apply {
            moveTo(w * 0.5f, h * 0.15f)
            lineTo(w * 0.95f, h * 0.45f)
            lineTo(w * 0.5f, h * 0.75f)
            lineTo(w * 0.05f, h * 0.45f)
            close()
        }
        drawPath(diamond, color = capColor)

        // Cap lower skull
        val skull = Path().apply {
            moveTo(w * 0.25f, h * 0.55f)
            lineTo(w * 0.25f, h * 0.75f)
            quadraticTo(w * 0.5f, h * 0.98f, w * 0.75f, h * 0.75f)
            lineTo(w * 0.75f, h * 0.55f)
            close()
        }
        drawPath(skull, color = capColor.copy(alpha = 0.85f))

        // Tassel string & bead
        val tassel = Path().apply {
            moveTo(w * 0.5f, h * 0.45f)
            lineTo(w * 0.88f, h * 0.52f)
            lineTo(w * 0.88f, h * 0.82f)
        }
        drawPath(tassel, color = tasselColor, style = androidx.compose.ui.graphics.drawscope.Stroke(width = 2.dp.toPx()))
        drawCircle(
            color = tasselColor,
            radius = 1.5.dp.toPx(),
            center = androidx.compose.ui.geometry.Offset(w * 0.88f, h * 0.85f)
        )
    }
}
