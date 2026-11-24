package id.web.digisafepurwakarta.digi_safe_app

import android.content.Intent
import android.graphics.Color
import android.net.Uri
import android.os.Build
import android.os.Bundle
import androidx.browser.customtabs.CustomTabColorSchemeParams
import androidx.browser.customtabs.CustomTabsClient
import androidx.browser.customtabs.CustomTabsIntent
import io.flutter.embedding.android.FlutterActivity
import io.flutter.embedding.engine.FlutterEngine
import io.flutter.plugin.common.MethodChannel

class MainActivity : FlutterActivity() {
    private val channelName = "id.web.digisafepurwakarta/oauth"
    private var methodChannel: MethodChannel? = null
    private var pendingCallbackUri: String? = null

    override fun configureFlutterEngine(flutterEngine: FlutterEngine) {
        super.configureFlutterEngine(flutterEngine)
        val channel = MethodChannel(flutterEngine.dartExecutor.binaryMessenger, channelName)
        methodChannel = channel

        channel.setMethodCallHandler { call, result ->
            when (call.method) {
                "openPopupAuth" -> {
                    val url = call.argument<String>("url")
                    if (url.isNullOrBlank()) {
                        result.error("INVALID_URL", "OAuth URL cannot be empty", null)
                        return@setMethodCallHandler
                    }
                    try {
                        openPartialBottomSheetOAuth(url)
                        result.success(true)
                    } catch (e: Exception) {
                        result.error("LAUNCH_ERROR", e.message, null)
                    }
                }
                "consumeInitialCallback" -> {
                    val cb = pendingCallbackUri
                    pendingCallbackUri = null
                    result.success(cb)
                }
                else -> result.notImplemented()
            }
        }

        // Handle cold-start deep link if present
        handleOAuthIntent(intent)
    }

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        handleOAuthIntent(intent)
    }

    override fun onNewIntent(intent: Intent) {
        super.onNewIntent(intent)
        setIntent(intent)
        handleOAuthIntent(intent)
    }

    private fun handleOAuthIntent(intent: Intent?) {
        val data = intent?.data ?: return
        if (data.scheme == "digisafe") {
            val uriStr = data.toString()
            if (methodChannel != null) {
                methodChannel?.invokeMethod("onOAuthCallback", uriStr)
            } else {
                pendingCallbackUri = uriStr
            }
        }
    }

    private fun openPartialBottomSheetOAuth(url: String) {
        val screenHeight = if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.R) {
            windowManager.currentWindowMetrics.bounds.height()
        } else {
            @Suppress("DEPRECATION")
            val dm = resources.displayMetrics
            dm.heightPixels
        }

        val initialPopupHeight = (screenHeight * 0.74).toInt().coerceAtLeast(640)

        val darkColors = CustomTabColorSchemeParams.Builder()
            .setToolbarColor(Color.parseColor("#0F172A"))
            .setNavigationBarColor(Color.parseColor("#0F172A"))
            .build()
        val lightColors = CustomTabColorSchemeParams.Builder()
            .setToolbarColor(Color.parseColor("#FFFFFF"))
            .setNavigationBarColor(Color.parseColor("#FFFFFF"))
            .build()

        val preferredPkg = CustomTabsClient.getPackageName(
            this,
            listOf("com.android.chrome", "com.chrome.beta", "com.sec.android.app.sbrowser", "org.mozilla.firefox"),
            true
        )

        try {
            val builder = CustomTabsIntent.Builder()
                .setDefaultColorSchemeParams(lightColors)
                .setColorSchemeParams(CustomTabsIntent.COLOR_SCHEME_DARK, darkColors)
                .setInitialActivityHeightPx(initialPopupHeight, CustomTabsIntent.ACTIVITY_HEIGHT_ADJUSTABLE)
                .setToolbarCornerRadiusDp(16)
                .setShowTitle(true)
                .setShareState(CustomTabsIntent.SHARE_STATE_OFF)
                .setUrlBarHidingEnabled(true)

            val customTabsIntent = builder.build()
            if (!preferredPkg.isNullOrBlank()) {
                customTabsIntent.intent.setPackage(preferredPkg)
            }
            customTabsIntent.launchUrl(this, Uri.parse(url))
        } catch (_: Exception) {
            try {
                val fallbackTabs = CustomTabsIntent.Builder()
                    .setDefaultColorSchemeParams(lightColors)
                    .setShowTitle(true)
                    .build()
                if (!preferredPkg.isNullOrBlank()) {
                    fallbackTabs.intent.setPackage(preferredPkg)
                }
                fallbackTabs.launchUrl(this, Uri.parse(url))
            } catch (_: Exception) {
                val fallback = Intent(Intent.ACTION_VIEW, Uri.parse(url))
                startActivity(fallback)
            }
        }
    }
}
