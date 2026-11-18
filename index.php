<?php

/**
 * Fallback entry point untuk shared hosting (cPanel / RumahWeb)
 * apabila request root langsung masuk ke public_html tanpa rewrite ke public/.
 */
require __DIR__ . '/public/index.php';

