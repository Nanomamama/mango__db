<?php
session_start();
require_once __DIR__ . '/line_service.php';

if (empty($_SESSION['member_id'])) {
    header('Location: member_login.php');
    exit;
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$liffId = app_env('LINE_LIFF_ID', '');
$addFriendUrl = line_bot_add_friend_url();
$redirect = (string) ($_GET['redirect'] ?? 'profile');
$liffState = (string) ($_GET['liff.state'] ?? '');
if ($redirect === 'profile' && $liffState !== '') {
    $stateQuery = [];
    parse_str(parse_url($liffState, PHP_URL_QUERY) ?: ltrim($liffState, '?'), $stateQuery);
    if (($stateQuery['redirect'] ?? '') === 'bookings') {
        $redirect = 'bookings';
    }
}
$redirect = $redirect === 'bookings' ? 'bookings.php' : 'member_profile.php';

if ($liffId === '') {
    $_SESSION['line_link_error'] = 'ยังไม่ได้ตั้งค่า LINE LIFF ID';
    header('Location: ' . $redirect);
    exit;
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เชื่อมต่อ LINE</title>
    <style>
        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            background: #f7fff9;
            color: #123;
        }
        .box {
            width: min(420px, calc(100vw - 32px));
            padding: 24px;
            background: #fff;
            border: 1px solid #d8f5dd;
            border-radius: 12px;
            box-shadow: 0 12px 30px rgba(0,0,0,0.08);
            text-align: center;
        }
        .brand {
            color: #06c755;
            font-size: 42px;
            font-weight: 800;
            margin-bottom: 10px;
        }
        .muted {
            color: #667085;
            line-height: 1.6;
        }
        .error {
            color: #b42318;
            background: #fff1f0;
            border: 1px solid #ffa39e;
            border-radius: 8px;
            padding: 10px;
            margin-top: 16px;
            display: none;
        }
        .add-friend {
            display: none;
            margin-top: 16px;
            padding: 10px 16px;
            border-radius: 8px;
            background: #06c755;
            color: #fff;
            text-decoration: none;
            font-weight: 700;
        }
        .retry-link {
            display: none;
            margin-top: 10px;
            border: 0;
            background: transparent;
            color: #06a046;
            font-weight: 700;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <main class="box">
        <div class="brand">LINE</div>
        <h1>กำลังเชื่อมต่อบัญชี LINE</h1>
        <p class="muted">กรุณารอสักครู่ ระบบกำลังยืนยันตัวตนผ่านแอป LINE</p>
        <div id="errorBox" class="error"></div>
        <a id="addFriendLink" class="add-friend" href="<?= htmlspecialchars($addFriendUrl, ENT_QUOTES, 'UTF-8') ?>">
            เพิ่มเพื่อน LINE Official Account
        </a>
        <button id="retryLinkButton" type="button" class="retry-link">
            เพิ่มเพื่อนแล้ว ตรวจสอบอีกครั้ง
        </button>
    </main>

    <script src="https://static.line-scdn.net/liff/edge/2/sdk.js"></script>
    <script>
        const LIFF_ID = <?= json_encode($liffId, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
        const REDIRECT_TO = <?= json_encode($redirect, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
        const ADD_FRIEND_URL = <?= json_encode($addFriendUrl, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
        const CSRF_TOKEN = <?= json_encode($_SESSION['csrf_token'], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;

        function showError(message) {
            const box = document.getElementById('errorBox');
            box.textContent = message;
            box.style.display = 'block';
        }

        function showAddFriend(message) {
            showError(message);
            const link = document.getElementById('addFriendLink');
            link.href = ADD_FRIEND_URL;
            link.style.display = 'inline-block';
            document.getElementById('retryLinkButton').style.display = 'inline-block';
        }

        document.getElementById('retryLinkButton').addEventListener('click', () => {
            window.location.reload();
        });

        async function connectLine() {
            try {
                await liff.init({ liffId: LIFF_ID });

                if (!liff.isLoggedIn()) {
                    liff.login({ redirectUri: window.location.href });
                    return;
                }

                const profile = await liff.getProfile();
                const response = await fetch('line_liff_save.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        csrf_token: CSRF_TOKEN,
                        line_user_id: profile.userId,
                        line_display_name: profile.displayName || ''
                    })
                });

                const result = await response.json();
                if (!response.ok || !result.success) {
                    const message = result.message || 'ไม่สามารถบันทึกข้อมูล LINE ได้';
                    if (response.status === 409) {
                        showAddFriend(message);
                        return;
                    }
                    throw new Error(message);
                }

                window.location.href = REDIRECT_TO;
            } catch (error) {
                showError(error.message || 'เชื่อมต่อ LINE ไม่สำเร็จ กรุณาลองใหม่');
            }
        }

        connectLine();
    </script>
</body>
</html>
