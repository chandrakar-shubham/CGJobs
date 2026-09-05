package com.example

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
        setContent {
            val userProfile by viewModel.userProfile.collectAsState()
            CGJobsTheme(darkTheme = userProfile.isDarkMode) {
                CGJobsApp(viewModel = viewModel)
            }
        }
    }
}

