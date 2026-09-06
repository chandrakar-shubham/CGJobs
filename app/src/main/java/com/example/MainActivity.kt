package com.example

import android.content.Intent
import android.net.Uri
import android.os.Bundle
import androidx.activity.ComponentActivity
import androidx.activity.compose.setContent
import androidx.activity.enableEdgeToEdge
import androidx.activity.viewModels
import androidx.compose.runtime.collectAsState
import androidx.compose.runtime.getValue
import com.example.ui.CGJobsApp
import com.example.ui.theme.CGJobsTheme
import com.example.ui.viewmodel.CGJobsViewModel

class MainActivity : ComponentActivity() {

    private val viewModel: CGJobsViewModel by viewModels()

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        com.example.data.api.ServerConfig.init(applicationContext)
        enableEdgeToEdge()

        handleDeepLink(intent)

        setContent {
            val userProfile by viewModel.userProfile.collectAsState()
            CGJobsTheme(darkTheme = userProfile.isDarkMode) {
                CGJobsApp(viewModel = viewModel)
            }
        }
    }

    override fun onNewIntent(intent: Intent) {
        super.onNewIntent(intent)
        setIntent(intent)
        handleDeepLink(intent)
    }

    private fun handleDeepLink(intent: Intent?) {
        val data: Uri? = intent?.data
        if (data != null) {
            // Examples: cgjobs://post/job-101 or https://cgjobs.app/post/job-101
            val pathSegments = data.pathSegments
            val postId = when {
                data.scheme == "cgjobs" && data.host == "post" -> {
                    pathSegments.firstOrNull() ?: data.lastPathSegment
                }
                data.scheme == "cgjobs" && data.host == "article" -> {
                    pathSegments.firstOrNull() ?: data.lastPathSegment
                }
                data.host == "cgjobs.app" && pathSegments.isNotEmpty() -> {
                    pathSegments.lastOrNull()
                }
                else -> data.lastPathSegment
            }

            if (!postId.isNullOrBlank()) {
                viewModel.openArticleById(postId)
            }
        }
    }
}

