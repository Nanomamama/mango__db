<?php
/**
 * LINE Notification Service Handler
 * Supports LINE Messaging API (Push Message) and Standard LINE Notify API.
 */

if (!function_exists('app_env')) {
    $envFile = __DIR__ . '/../config/env.php';
    if (is_file($envFile)) {
        require_once $envFile;
    }
}

if (!function_exists('send_line_message')) {
    /**
     * Send message via LINE Messaging API or LINE Notify
     * 
     * @param string $msgText Message content
     * @param string|null $token Optional access token override
     * @param string|null $targetUserId Optional user ID override
     * @return bool Success status
     */
    function send_line_message(string $msgText, ?string $token = null, ?string $targetUserId = null): bool
    {
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
                'Content-Type: application/x-www-form-urlencoded',
                'Authorization: Bearer ' . $notifyToken
            ]);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(['message' => $msgText]));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

            $result = curl_exec($ch);
            $err = curl_error($ch);
            curl_close($ch);

            if ($err) {
                error_log('LINE Notify API Error: ' . $err);
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

        $data = [
            "to" => $userId,
            "messages" => [
                [
                    "type" => "text",
                    "text" => $msgText
                ]
            ]
        ];

        $ch = curl_init("https://api.line.me/v2/bot/message/push");
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $accessToken
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data, JSON_UNESCAPED_UNICODE));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $result = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);

        if ($err) {
            error_log('LINE Messaging API Error: ' . $err);
            return false;
        }

        return true;
    }
}

// Global execution for backward compatibility (e.g., when file is included via `include 'line_notify.php'`)
if (!isset($message)) {
    $message = "📦 มีคำสั่งซื้อใหม่";
}

send_line_message($message);
?>