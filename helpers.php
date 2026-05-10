<?php

// helpers.php

// Autoritative list of peripheral categories
if (!function_exists('getPeripheralCategories')) {
    function getPeripheralCategories()
    {
        return ["ADAPTOR", "BATTERY", "CABLE HDMI", "CABLE UTP", "CONNECTOR", "HARDDISK", "KEYBOARD", "MEMORY", "MOUSE", "PATCH CORD", "PRINTER", "PROJECTOR", "ROLLER", "SCANNER", "SWITCH", "WIRELESS"];
    }
}

// Fungsi untuk keamanan (mencegah XSS)
if (!function_exists('e')) {
    function e($text)
    {
        return htmlspecialchars((string) ($text ?? ''), ENT_QUOTES, 'UTF-8');
    }
}

## FUNGSI BARU UNTUK WEBSOCKETS
if (!function_exists('pushWebSocketUpdate')) {
    function pushWebSocketUpdate($id, $action): void
    {
        if (!defined('NODE_FULL_BROADCAST_URL') || !function_exists('curl_init')) {
            return;
        }

        $url = NODE_FULL_BROADCAST_URL;
        $broadcast_data = json_encode(['id' => $id, 'action' => $action]);
        if ($broadcast_data === false) {
            return;
        }

        $ch = curl_init($url);
        if (!$ch) {
            return;
        }

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $broadcast_data,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'Content-Length: ' . strlen($broadcast_data)],
            CURLOPT_TIMEOUT => 2, // Batasan waktu singkat agar tidak memblokir
        ]);

        $response = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        // Logging untuk Debugging
        if ($httpcode !== 200 || curl_errno($ch)) {
            error_log("❌ WS Broadcast Failed (ID: $id, Action: $action). HTTP Code: $httpcode. cURL Error: " . curl_error($ch));
        } else {
            error_log("✅ WS Broadcast Success (ID: $id, Action: $action). Response: " . $response);
        }
        curl_close($ch);
    }
}
// HINDARI TAG PENUTUP
