<?php
/**
 * LINE Notification Service Handler
 * Supports LINE Messaging API (Push Message) and Standard LINE Notify API.
 * Supports text messages and image attachments with HTTP/HTTPS fallback handling.
 */

if (!function_exists('app_env')) {
    $envFile = __DIR__ . '/../config/env.php';
    if (is_file($envFile)) {
        require_once $envFile;
    }
}

if (!function_exists('send_line_message')) {
    /**
     * Send message and optional image via LINE Messaging API or LINE Notify
     * 
     * @param string $msgText Message content
     * @param string|null $imageUrl Public image URL (HTTPS required for LINE Messaging API)
     * @param string|null $imagePath Local image file path (for LINE Notify)
     * @param string|null $token Optional access token override
     * @param string|null $targetUserId Optional user ID override
     * @return bool Success status
     */
    function send_line_message(
        string $msgText, 
        ?string $imageUrl = null, 
        ?string $imagePath = null, 
        ?string $token = null, 
        ?string $targetUserId = null
    ): bool {
        if (trim($msgText) === '') {
            return false;
        }

        if (!function_exists('app_env')) {
            $envFile = __DIR__ . '/../config/env.php';
            if (is_file($envFile)) {
                require_once $envFile;
            }
        }

        // Default Credentials (Fallback)
        $defaultToken = "MuYBuMz3icicSJjBFPGUPWgcpCrC85pi7iCxDre1mJmBiG8Dc8OXoGbAPd6My4N8hOmzVrw28eLA9JUlIUBAbyK08/uAn2Iadpim0tzfl8NLHT6yOFHdmIA0OJcfa91I3TOXpvbbDOk/r7sfJMtVeAdB04t89/1O/w1cDnyilFU=";
        $defaultUserId = "U794725708d6128c50d908b154f7a9999";

        $accessToken = $token ?? (function_exists('app_env') ? app_env('LINE_ACCESS_TOKEN', $defaultToken) : $defaultToken);
        $userId = $targetUserId ?? (function_exists('app_env') ? app_env('LINE_USER_ID', $defaultUserId) : $defaultUserId);
        $notifyToken = function_exists('app_env') ? app_env('LINE_NOTIFY_TOKEN') : null;

        // 1. Standard LINE Notify API (If LINE_NOTIFY_TOKEN is configured in .env)
        if (!empty($notifyToken)) {
            $ch = curl_init("https://notify-api.line.me/api/notify");
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $notifyToken
            ]);
            curl_setopt($ch, CURLOPT_POST, true);

            $postData = ['message' => $msgText];
            if (!empty($imagePath) && is_file($imagePath)) {
                $postData['imageFile'] = new CURLFile($imagePath);
            } elseif (!empty($imageUrl)) {
                $postData['imageFullsize'] = $imageUrl;
                $postData['imageThumbnail'] = $imageUrl;
            }

            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
            curl_setopt($ch, CURLOPT_TIMEOUT, 8);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

            $result = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $err = curl_error($ch);
            curl_close($ch);

            if ($err || $httpCode !== 200) {
                error_log("LINE Notify Error [HTTP {$httpCode}]: " . ($err ?: $result));
                return false;
            }
            return true;
        }

        // 2. LINE Messaging API (Push Message)
        if (empty($accessToken)) {
            error_log('LINE Messaging API Error: Empty Access Token');
            return false;
        }

        if (empty($userId)) {
            error_log('LINE Messaging API Error: Empty User ID');
            return false;
        }

        // Closure to send a push message array via LINE Messaging API
        $sendPush = function(array $msgArray) use ($userId, $accessToken): bool {
            $data = [
                "to" => $userId,
                "messages" => $msgArray
            ];

            $ch = curl_init("https://api.line.me/v2/bot/message/push");
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $accessToken
            ]);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data, JSON_UNESCAPED_UNICODE));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
            curl_setopt($ch, CURLOPT_TIMEOUT, 8);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

            $result = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $err = curl_error($ch);
            curl_close($ch);

            if ($err || $httpCode !== 200) {
                error_log("LINE Messaging API Push Error [HTTP {$httpCode}]: " . ($err ?: $result));
                return false;
            }
            return true;
        };

        // LINE Messaging API requires HTTPS for image messages
        $isHttpsImage = (!empty($imageUrl) && str_starts_with(strtolower($imageUrl), 'https://'));

        // Prepare text content
        $textPayload = $msgText;
        if (!empty($imageUrl) && !$isHttpsImage) {
            // Fallback for non-HTTPS URL (e.g. localhost or http): attach image URL into text message
            $textPayload .= "\n📷 ดูรูปสลิป: " . $imageUrl;
        }

        // Send main text message first (guaranteed delivery)
        $textSent = $sendPush([
            [
                "type" => "text",
                "text" => $textPayload
            ]
        ]);

        // Send HTTPS image message separately if available
        if ($isHttpsImage) {
            $sendPush([
                [
                    "type" => "image",
                    "originalContentUrl" => $imageUrl,
                    "previewImageUrl" => $imageUrl
                ]
            ]);
        }

        return $textSent;
    }
}

// Global execution for backward compatibility (e.g., when file is included via `include 'line_notify.php'`)
if (isset($message)) {
    $imgUrl = $imageUrl ?? null;
    $imgPath = $imagePath ?? null;
    send_line_message($message, $imgUrl, $imgPath);
}
?>