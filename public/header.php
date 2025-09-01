<?php
// ❌ محاولة حقن newline
$url = "https://example.com\nInjected: X-Hacked: 1";
header("Location: " . $url);
exit;
