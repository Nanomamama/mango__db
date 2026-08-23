<?php
session_start();
require_once __DIR__ . '/../db/db.php';
require_once __DIR__ . '/../config/env.php';
require_once __DIR__ . '/line_service.php';


$member_id = $_SESSION['member_id'] ?? null;
$member_name = '';
$member_phone = '';
$member_line_user_id = '';
$member_line_display_name = '';

if ($member_id) {
    $m = $conn->prepare("SELECT fullname, phone, line_user_id, line_display_name FROM members WHERE member_id=?");
    $m->bind_param("i", $member_id);
    $m->execute();
    $mem = $m->get_result()->fetch_assoc();
    $member_name = $mem['fullname'] ?? '';
    $member_phone = $mem['phone'] ?? '';
    $member_line_user_id = $mem['line_user_id'] ?? '';
    $member_line_display_name = $mem['line_display_name'] ?? '';
}

$order_error = $_SESSION['order_error'] ?? '';
unset($_SESSION['order_error']);

$line_link_success = $_SESSION['line_link_success'] ?? '';
$line_link_error = $_SESSION['line_link_error'] ?? '';
unset($_SESSION['line_link_success'], $_SESSION['line_link_error']);

$guest_line_user_id = $_SESSION['guest_line_user_id'] ?? '';
$guest_line_display_name = $_SESSION['guest_line_display_name'] ?? '';
$order_line_user_id = $member_id ? ($member_line_user_id ?: $guest_line_user_id) : $guest_line_user_id;
$order_line_display_name = $member_id ? ($member_line_display_name ?: $guest_line_display_name) : $guest_line_display_name;
$google_maps_api_key = app_env('GOOGLE_MAPS_API_KEY', '');
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <link rel="apple-touch-icon" sizes="180x180" href="../logo/logo_01.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../logo/logo_01.png">
    <title>สวนลุงเผือก</title>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&family=Mitr:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        /* ===== RESET & ROOT ===== */
        :root {
            --sage: #016A70;
            --sage-light: #2ad3bc;
            --sage-pale: #e8f4f3;
            --sage-mist: #dcfffc;
            --cream: #f4fdfa;
            --earth: #4e808b;
            --earth-pale: #e2f5f3;
            --text: #373833;
            --text-muted: #4a4b48;
            --white: #ffffff;
            --red: #f82424;

            --shadow-sm: 0 2px 12px rgba(60, 80, 30, .07);
            --shadow-md: 0 6px 28px rgba(60, 80, 30, .10);
            --shadow-lg: 0 16px 48px rgba(60, 80, 30, .13);
            --radius-sm: 10px;
            --radius-md: 16px;
            --radius-lg: 24px;
        }

        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            background: var(--cream);
            color: var(--text);

            font-size: 15px;
            line-height: 1.6;
            min-height: 100vh;
        }

        /* ===== AMBIENT BACKGROUND ===== */
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background:
                radial-gradient(ellipse 900px 600px at 80% -10%, rgba(127, 166, 96, .10) 0%, transparent 70%),
                radial-gradient(ellipse 600px 400px at -5% 80%, rgba(90, 122, 74, .08) 0%, transparent 60%);
            pointer-events: none;
            z-index: 0;
        }

        /* ===== PAGE HEADER ===== */
        .pg-header {
            background: linear-gradient(135deg, var(--sage) 0%, var(--sage-light) 100%);
            color: white;
            padding: 32px 0 40px;
            position: relative;
            overflow: hidden;
        }

        .pg-header::after {
            content: '';
            position: absolute;
            bottom: -1px;
            left: 0;
            right: 0;
            height: 28px;
            background: var(--cream);
            border-radius: 50% 50% 0 0 / 28px 28px 0 0;
        }

        .pg-header .breadcrumb {
            opacity: .75;
            font-size: 13px;
            margin-bottom: 10px;
        }

        .pg-header .breadcrumb a {
            color: white;
            text-decoration: none;
        }

        .pg-header .breadcrumb-item.active {
            color: var(--white);
        }

        .pg-header .breadcrumb-item+.breadcrumb-item::before {
            color:var(--white);
        }

    
        .pg-title {

            font-size: 2rem;
            font-weight: 500;
            color: white;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .pg-title-icon {
            width: 48px;
            height: 48px;
            background: var(--earth);
            border-radius: var(--radius-sm);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
        }

        /* ===== MAIN WRAPPER ===== */
        .main-wrap {
            position: relative;
            z-index: 1;
            padding: 32px 0 80px;
        }

        /* ===== SECTION LABEL ===== */
        .section-label {

            font-size: 1.5rem;
            font-weight: 700;
            letter-spacing: .12em;
            text-transform: uppercase;
            color: var(--sage);
            margin-bottom: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .section-label::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--border);
        }

        /* ===== CART HEADER ROW ===== */
        .cart-col-header {
            display: grid;
            grid-template-columns: 2.6fr 1fr 1.2fr 1fr 48px;
            gap: 16px;
            padding: 0 24px 14px;
            font-size: 1.25rem;
            font-weight: 600;
            letter-spacing: .06em;
            text-transform: uppercase;
            color: var(--text-muted);
        }

        .cart-col-header>* {
            text-align: center;
        }

        .cart-col-header>*:first-child {
            text-align: left;
        }

        /* ===== CART CARD ===== */
        .cart-card {
            background: var(--white);
            border-radius: var(--radius-lg);
            border: 1px solid var(--border);
            overflow: hidden;
            box-shadow: var(--shadow-sm);
        }

        /* ===== CART ITEM ===== */
        .cart-item {
            display: grid;
            grid-template-columns: 2.6fr 1fr 1.2fr 1fr 48px;
            gap: 16px;
            align-items: center;
            padding: 20px 24px;
            border-bottom: 1px solid #f0f5ea;
            transition: background .2s;
        }

        .cart-item:last-child {
            border-bottom: none;
        }

        .cart-item:hover {
            background: var(--sage-mist);
        }

        /* product cell */
        .item-product {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .item-img-wrap {
            position: relative;
            flex-shrink: 0;
        }

        .item-img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: var(--radius-sm);
            border: 1px solid var(--border);
        }

        .item-badge {
            position: absolute;
            top: -6px;
            right: -6px;
            background: var(--sage);
            color: white;
            font-size: .65rem;
            font-weight: 700;
            padding: 2px 7px;
            border-radius: 20px;
        }

        .item-name {
            font-weight: 600;
            color: var(--text);
            font-size: .97rem;
            line-height: 1.4;
        }

        .item-tag {
            display: inline-block;
            background: var(--sage-pale);
            color: var(--sage);
            font-size: .72rem;
            font-weight: 600;
            padding: 2px 10px;
            border-radius: 20px;
            margin-top: 6px;
        }

        /* price cell */
        .item-price {
            text-align: center;
            font-weight: 600;
            color: var(--text-muted);
            font-size: .93rem;
        }

        /* qty cell */
        .item-qty {
            text-align: center;
        }

        .item-actions {
            display: contents;
        }

        .qty-wrap {
            display: inline-flex;
            align-items: center;
            background: var(--sage-pale);
            border-radius: 50px;
            padding: 4px;
            gap: 4px;
        }

        .qty-btn {
            width: 30px;
            height: 30px;
            border: none;
            background: white;
            border-radius: 50%;
            color: var(--sage);
            font-size: .85rem;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: .18s;
            box-shadow: 0 1px 4px rgba(90, 122, 74, .15);
        }

        .qty-btn:hover {
            background: var(--sage);
            color: white;
        }

        .qty-num {
            width: 36px;
            text-align: center;
            border: none;
            background: transparent;

            font-weight: 500;
            font-size: 1rem;
            color: var(--text);
        }

        /* total cell */
        .item-total {
            text-align: center;
            ;
            font-weight: 500;
            font-size: 1rem;
            color: var(--sage);
        }

        /* remove btn */
        .btn-remove {
            width: 36px;
            height: 36px;
            border: 1px solid #f0ddd5;
            background: #fef4ef;
            color: var(--red);
            border-radius: var(--radius-sm);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: .18s;
            font-size: .85rem;
        }

        .btn-remove:hover {
            background: var(--red);
            color: white;
            border-color: var(--red);
        }

        /* ===== CART FOOTER ===== */
        .cart-footer {
            padding: 20px 24px;
            background: var(--sage-mist);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            border-top: 1px solid var(--border);
        }

        .btn-clear {
            border: 1px solid #e10000;
            background: white;
            color: var(--red);
            padding: 10px 18px;
            border-radius: 10px;
            /* fon; */
            font-size: .88rem;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: .2s;
        }

        .btn-clear:hover {
            background: #ff1919;
            color: white;
            border-color: #e05a38;
        }

        .footer-total-wrap {
            text-align: right;
        }

        .footer-total-label {
            font-size: .8rem;
            color: var(--text-muted);
            margin-bottom: 2px;
        }

        .footer-total-amount {

            font-size: 1.5rem;
            font-weight: 500;
            color: var(--sage);
        }

        /* ===== EMPTY STATE ===== */
        .empty-state {
            padding: 72px 24px;
            text-align: center;
        }

        .empty-icon {
            width: 100px;
            height: 100px;
            background: var(--sage-pale);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.6rem;
            color: var(--sage-light);
            margin: 0 auto 20px;
        }

        .empty-title {

            font-size: 1.3rem;
            font-weight: 500;
            color: var(--text);
            margin-bottom: 8px;
        }

        .empty-sub {
            color: var(--text-muted);
            font-size: .9rem;
            margin-bottom: 24px;
        }

        .btn-shop-now {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: var(--sage);
            color: white;
            padding: 12px 24px;
            border-radius: var(--radius-md);
            text-decoration: none;
            font-weight: 600;
            transition: .2s;
        }

        .btn-shop-now:hover {
            background: var(--sage-light);
            color: white;
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        /* ===== CHECKOUT PANEL ===== */
        .checkout-panel {
            background: var(--white);
            border-radius: var(--radius-lg);
            border: 1px solid var(--border);
            box-shadow: var(--shadow-md);
            overflow: hidden;
            position: sticky;
            top: 24px;
        }

        .panel-head {
            background: linear-gradient(135deg, var(--sage) 0%, var(--sage-light) 100%);
            padding: 20px 24px;
            color: white;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .panel-head-icon {
            width: 38px;
            height: 38px;
            background: rgba(255, 255, 255, .2);
            border-radius: var(--radius-sm);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .panel-head-title {

            font-size: 1.1rem;
            font-weight: 500;
        }

        .panel-body {
            padding: 24px;
        }

        /* summary box */
        .summary-box {
            background: var(--sage-mist);
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            padding: 16px 18px;
            margin-bottom: 24px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            font-size: .92rem;
            color: var(--text-muted);
        }

        .summary-row:not(:last-child) {
            border-bottom: 1px dashed var(--border);
        }

        .summary-row.total-row {
            color: var(--text);
            font-weight: 700;
            padding-top: 14px;
            margin-top: 4px;
        }

        .summary-row.total-row span:last-child {

            font-size: 1.3rem;
            color: var(--sage);
        }

        .free-tag {
            background: #e8f8e0;
            color: #4a8c30;
            font-size: .72rem;
            font-weight: 700;
            padding: 2px 10px;
            border-radius: 20px;
        }

        /* form */
        .form-label {
            font-weight: 600;
            font-size: .87rem;
            color: var(--text);
            display: block;
            margin-bottom: 8px;
        }

        .form-control,
        .form-input {
            width: 100%;
            padding: 12px 14px;
            border: 1.5px solid var(--border);
            border-radius: var(--radius-sm);
            background: var(--sage-light);
            font-size: .93rem;
            color: var(--text);
            transition: .2s;
            outline: none;
        }

        .form-control:focus,
        .form-input:focus {
            border-color: var(--sage);
            background: white;
            box-shadow: 0 0 0 4px rgba(90, 122, 74, .10);
        }

        textarea.form-control {
            resize: vertical;
            min-height: 80px;
        }

        .mb-field {
            margin-bottom: 18px;
        }

        /* receive cards */
        .receive-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-bottom: 18px;
        }

        .receive-card {
            border: 1.5px solid var(--border);
            border-radius: var(--radius-md);
            padding: 14px;
            cursor: pointer;
            transition: .2s;
            display: flex;
            align-items: center;
            gap: 12px;
            background: var(--sage-mist);
        }

        .receive-card input[type="radio"] {
            display: none;
        }

        .receive-card:hover {
            border-color: var(--sage);
            background: var(--sage-light);
        }

        .receive-card.active {
            border-color: var(--sage);
            background: var(--sage-light);
            box-shadow: 0 0 0 3px rgba(90, 122, 74, .12);
        }

        .receive-icon {
            width: 42px;
            height: 42px;
            border-radius: var(--radius-sm);
            background: white;
            border: 1.5px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--sage);
            font-size: 1.1rem;
            flex-shrink: 0;
            transition: .2s;
        }

        .receive-card.active .receive-icon {
            background: var(--sage);
            color: white;
            border-color: var(--sage);
        }

        .receive-text strong {
            display: block;
            font-size: .88rem;
            font-weight: 700;
            color: var(--text);
            margin-bottom: 2px;
        }

        .receive-text small {
            font-size: .76rem;
            color: var(--text-muted);
        }

        /* date trigger */
        .date-btn {
            width: 100%;
            border: 1.5px dashed var(--sage-light);
            background: var(--sage-mist);
            border-radius: var(--radius-md);
            padding: 14px 16px;
            display: flex;
            align-items: center;
            gap: 12px;
            cursor: pointer;
            transition: .2s;
            color: var(--text);

            font-size: .93rem;
            text-align: left;
        }

        .date-btn:hover {
            border-color: var(--sage);
            background: var(--sage-pale);
        }

        .date-btn-icon {
            width: 40px;
            height: 40px;
            background: var(--sage);
            border-radius: var(--radius-sm);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1rem;
            flex-shrink: 0;
        }

        #triggerText {
            font-weight: 600;
            color: var(--sage);
        }

        /* info alert */
        .info-alert {
            background: var(--sage-pale);
            border: 1px solid #61d1f3;
            border-left: 4px solid var(--sage);
            border-radius: var(--radius-sm);
            padding: 12px 14px;
            font-size: .85rem;
            color: var(--text);
            display: flex;
            gap: 10px;
            align-items: flex-start;
            margin-bottom: 20px;
        }

        /* submit */
        .btn-checkout {
            width: 100%;
            border: none;
            background: linear-gradient(135deg, var(--sage) 0%, var(--sage-light) 100%);
            color: white;
            height: 52px;
            border-radius: var(--radius-md);
            ;
            font-weight: 400;
            font-size: 1.05rem;
            cursor: pointer;
            transition: .25s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            box-shadow: 0 6px 20px rgba(90, 122, 74, .25);
        }

        .btn-checkout:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 12px 32px rgba(90, 122, 74, .32);
        }

        .btn-checkout:disabled {
            opacity: .5;
            cursor: not-allowed;
        }

        .btn-continue-link {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-top: 12px;
            height: 44px;
            border: 1.5px solid var(--border);
            border-radius: var(--radius-sm);
            text-decoration: none;
            color: var(--text-muted);
            font-weight: 600;
            font-size: .88rem;
            transition: .2s;
            background: white;
        }

        .btn-continue-link:hover {
            border-color: var(--sage);
            color: var(--sage);
        }

        /* ===== MODAL ===== */
        #dateModal .modal-content {
            border: none;
            border-radius: var(--radius-lg);
            overflow: hidden;
        }

        .modal-head {
            background: linear-gradient(135deg, var(--sage) 0%, var(--sage-light) 100%);
            padding: 22px 24px;
            color: white;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .modal-head h5 {
            font-size: 1.1rem;
            font-weight: 400;
            margin: 0;
        }

        .modal-head .btn-close {
            filter: brightness(0) invert(1);
            opacity: .8;
        }

        .modal-body {
            padding: 24px;
        }

        .quick-date-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-bottom: 20px;
        }

        .quick-btn {
            border: 1.5px solid var(--border);
            background: var(--cream);
            border-radius: var(--radius-md);
            padding: 14px;
            font-weight: 600;
            font-size: .9rem;
            color: var(--text);
            cursor: pointer;
            transition: .2s;
        }

        .quick-btn:hover {
            border-color: var(--sage-light);
            background: var(--sage-pale);
        }

        .quick-btn.active {
            border-color: var(--sage);
            background: var(--sage-pale);
            color: var(--sage);
        }

        .modal-label {
            font-weight: 700;
            font-size: .85rem;
            color: var(--text);
            display: block;
            margin-bottom: 10px;
        }

        .time-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 8px;
            margin-top: 10px;
        }

        .time-pill {
            border: 1.5px solid var(--border);
            background: var(--cream);
            border-radius: 50px;
            padding: 10px;
            text-align: center;
            font-weight: 700;
            font-size: .88rem;
            color: var(--text);
            cursor: pointer;
            transition: .18s;
        }

        .time-pill:hover {
            border-color: var(--sage-light);
            background: var(--sage-pale);
        }

        .time-pill.active {
            border-color: var(--sage);
            background: var(--sage);
            color: white;
        }

        .btn-confirm {
            width: 100%;
            border: none;
            background: linear-gradient(135deg, var(--sage) 0%, var(--sage-light) 100%);
            color: white;
            height: 50px;
            border-radius: var(--radius-md);
            ;
            font-size: 1rem;
            cursor: pointer;
            transition: .2s;
            margin-top: 24px;
        }

        .btn-confirm:hover {
            box-shadow: 0 8px 24px rgba(90, 122, 74, .28);
            transform: translateY(-1px);
        }

        /* ===== LOADING ===== */
        .loading-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(253, 250, 244, .92);
            z-index: 9999;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            gap: 16px;
        }

        .spinner-leaf {
            width: 52px;
            height: 52px;
            border: 4px solid var(--border);
            border-top-color: var(--sage);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        /* ===== RESPONSIVE ===== */
        .delivery-map-box {
            display: none;
            margin-top: 12px;
            padding: 12px;
            border: 1px solid rgba(1, 106, 112, .18);
            border-radius: var(--radius-sm);
            background: #f8fffd;
        }

        .delivery-map-box.is-visible {
            display: block;
        }

        #deliveryMap {
            width: 100%;
            height: 260px;
            border-radius: var(--radius-sm);
            overflow: hidden;
            border: 1px solid rgba(1, 106, 112, .16);
        }

        .delivery-map-actions {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 10px;
        }

        .delivery-current-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border: 1px solid rgba(1, 106, 112, .18);
            border-radius: 10px;
            background: var(--white);
            color: var(--sage);
            font-weight: 800;
            padding: 9px 12px;
            cursor: pointer;
            transition: .2s ease;
        }

        .delivery-current-btn:hover {
            border-color: rgba(1, 106, 112, .35);
            background: #eefaf8;
        }

        .delivery-current-btn:disabled {
            cursor: not-allowed;
            opacity: .65;
        }

        .delivery-map-modal .modal-content {
            min-height: 100vh;
            border: 0;
            border-radius: 0;
        }

        .delivery-map-modal .modal-body {
            padding: 12px;
            background: #f8fffd;
        }

        .delivery-map-modal-mount {
            min-height: calc(100vh - 162px);
        }

        .delivery-map-modal-mount #deliveryMap {
            height: calc(100vh - 162px);
            min-height: 420px;
        }

        .delivery-map-status {
            margin-top: 10px;
            padding: 9px 10px;
            border-radius: 10px;
            background: #eefaf8;
            color: var(--sage);
            font-weight: 700;
            line-height: 1.45;
        }

        .delivery-map-status.is-error {
            background: #fff1f1;
            color: #c62828;
        }

        .delivery-coordinate {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
            margin-top: 10px;
            font-size: .82rem;
            color: var(--text-muted);
        }

        .delivery-coordinate strong {
            display: block;
            color: var(--text);
            overflow-wrap: anywhere;
        }

        @media (max-width: 991px) {

            .cart-col-header {
                display: none;
            }

            .cart-item {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 10px;
                padding: 12px;
            }

            /* ฝั่งซ้าย */
            .item-product {
                flex: 1;
                min-width: 0;
                gap: 10px;
            }

            .item-img {
                width: 62px;
                height: 62px;
            }

            .item-name {
                font-size: .88rem;
                line-height: 1.3;
            }

            .item-tag {
                font-size: .65rem;
                padding: 2px 8px;
            }

            /* ซ่อนราคาเดี่ยว */
            .item-price {
                display: none;
            }

            /* ฝั่งขวา */
            .item-actions {
                display: flex;
                align-items: center;
                justify-content: flex-end;
                gap: 8px;
                flex-shrink: 0;
            }


        }

        .qty-wrap {
            gap: 2px;
            padding: 3px;
        }

        .qty-btn {
            width: 28px;
            height: 28px;
            font-size: .75rem;
        }

        .qty-num {
            width: 28px;
            font-size: .9rem;
        }

        .item-total {
            font-size: .9rem;
            min-width: 62px;
            text-align: right;
        }

        .btn-remove {
            width: 32px;
            height: 32px;
            flex-shrink: 0;
            border-radius: 10px;
            padding: 0;
        }

        .checkout-panel {
            position: static;
            margin-top: 24px;
        }

        .receive-grid {
            grid-template-columns: 1fr;
        }

        @media (max-width: 768px) {
            .pg-title {
                font-size: 1.5rem;
            }

            .footer-total-amount {
                font-size: 1.2rem;
            }
        }
    </style>
</head>

<body>
    <?php include __DIR__ . '/navbar.php'; ?>
    <?php include __DIR__ . '/fb_chat_button.php'; ?>

    <div class="loading-overlay" id="loadingOverlay">
        <div class="spinner-leaf"></div>
        <p style="color:var(--sage);font-weight:600;">กำลังดำเนินการ...</p>
    </div>

    <!-- PAGE HEADER -->
    <div class="pg-header">
        <div class="container">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="products.php"><i class="fas fa-home"></i> หน้าแรก</a></li>
                    <li class="breadcrumb-item active">ตะกร้าสินค้า</li>
                </ol>
            </nav>
            <div class="pg-title">
                <div class="pg-title-icon"><i class="fas fa-shopping-basket"></i></div>
                ตะกร้าสินค้า
            </div>
        </div>
    </div>

    <div class="main-wrap">
        <div class="container">
            <form action="save_order.php" method="post" onsubmit="return submitOrder()">
                <div class="row g-4 align-items-start">

                    <!-- LEFT: CART -->
                    <div class="col-lg-8">

                        <div class="section-label">
                           <i class="fa-solid fa-list"></i>
                            รายการสินค้า
                        </div>

                        <!-- Column headers (desktop) -->
                        <div class="cart-col-header d-none d-lg-grid">
                            <span>สินค้า</span>
                            <span>ราคา/ชิ้น</span>
                            <span>จำนวน</span>
                            <span>รวม</span>
                            <span></span>
                        </div>

                        <!-- Cart card -->
                        <div class="cart-card">
                            <div id="cartItems"></div>

                            <!-- Empty state -->
                            <div id="emptyCart" class="empty-state" style="display:none;">
                                <div class="empty-icon"><i class="fas fa-shopping-basket"></i></div>
                                <div class="empty-title">ตะกร้าว่างเปล่า</div>
                                <p class="empty-sub">ยังไม่มีสินค้าที่เลือก ไปเลือกผลผลิตสดจากสวนกันเลย!</p>
                                <a href="products.php" class="btn-shop-now">
                                    <i class="fas fa-seedling"></i> เลือกซื้อสินค้า
                                </a>
                            </div>

                            <!-- Cart footer -->
                            <div class="cart-footer" id="cartFooter" style="display:none;">
                                <button type="button" class="btn-clear" onclick="clearCart()">
                                    <i class="fas fa-trash-alt"></i> ล้างตะกร้า
                                </button>
                                <div class="footer-total-wrap">
                                    <div class="footer-total-label">ยอดรวมสินค้า</div>
                                    <div class="footer-total-amount" id="footerTotal" style="color: red;">฿0.00</div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- RIGHT: CHECKOUT -->
                    <div class="col-lg-4">

                        <div class="section-label">
                            <i class="fas fa-receipt"></i>
                            ดำเนินการสั่งซื้อ
                        </div>

                        <div class="checkout-panel">
                            <div class="panel-head">
                                <div class="panel-head-icon">
                                    <i class="fas fa-receipt"></i>
                                </div>
                                <div class="panel-head-title">สรุปคำสั่งซื้อ</div>
                            </div>

                            <div class="panel-body">
                                <input type="hidden" name="member_id" value="<?= $member_id ?>">

                                <!-- SUMMARY -->
                                <div class="summary-box">
                                    <div class="summary-row">
                                        <span>ยอดสินค้า</span>
                                        <span id="subtotal">฿0.00</span>
                                    </div>
                                    <div class="summary-row">
                                        <span>ค่าจัดส่ง</span>
                                        <span id="shippingText">-</span>
                                    </div>
                                    <div class="summary-row total-row ">
                                        <span>ยอดรวมทั้งหมด</span>
                                        <span id="totalPrice" style="color: red;">฿0.00</span>
                                    </div>
                                </div>

                                <?php if ($line_link_success !== '' || $line_link_error !== ''): ?>
                                    <div class="mb-field">
                                        <?php if ($line_link_success !== ''): ?>
                                            <div class="alert alert-success py-2 mb-2"><?= htmlspecialchars($line_link_success, ENT_QUOTES, 'UTF-8') ?></div>
                                        <?php endif; ?>
                                        <?php if ($line_link_error !== ''): ?>
                                            <div class="alert alert-danger py-2 mb-2"><?= htmlspecialchars($line_link_error, ENT_QUOTES, 'UTF-8') ?></div>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>

                                <div class="mb-field">
                                    <div style="background:#f7fff9;border:1px solid #9be7b2;border-radius:12px;padding:14px;">
                                        <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px;color:#0f5132;">
                                            <i class="fab fa-line" style="font-size:1.45rem;color:#06c755;"></i>
                                            <div>
                                                <strong>รับแจ้งเตือนคำสั่งซื้อผ่าน LINE</strong>
                                                <div class="small text-muted">เพิ่มเพื่อน OA และเชื่อมต่อ LINE เพื่อรับสถานะออเดอร์</div>
                                            </div>
                                        </div>

                                        <?php if (!empty($order_line_user_id)): ?>
                                            <div class="bg-white rounded-3 p-3" style="border:1px solid #8ee68e;color:#147a14;">
                                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                                    <i class="fas fa-check-circle"></i>
                                                    <strong>เชื่อมต่อ LINE แล้ว</strong>
                                                    <?php if (!empty($order_line_display_name)): ?>
                                                        <span>: <?= htmlspecialchars($order_line_display_name, ENT_QUOTES, 'UTF-8') ?></span>
                                                    <?php endif; ?>
                                                    <span class="small ms-auto">ระบบจะแจ้งเตือนเมื่อสถานะคำสั่งซื้อเปลี่ยน</span>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <div class="d-grid gap-2">
                                                <div class="bg-white border rounded-3 p-3 d-flex align-items-center justify-content-between gap-3 flex-wrap">
                                                    <div>
                                                        <strong class="d-block text-dark">1. เพิ่มเพื่อน LINE OA</strong>
                                                        <span class="small text-muted">เปิด LINE เพื่อเพิ่ม Official Account เป็นเพื่อน</span>
                                                    </div>
                                                    <a href="<?= htmlspecialchars(line_bot_add_friend_url(), ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener" class="btn btn-sm text-white" style="background:#06c755;border-color:#06c755;" onclick="showLineConnectStepOrder();">
                                                        <i class="fab fa-line me-1"></i> เพิ่มเพื่อน OA
                                                    </a>
                                                </div>

                                                <div id="lineConnectStepOrder" class="bg-white rounded-3 p-3" style="display:none;border:1px solid #016A70;">
                                                    <div class="d-flex align-items-center justify-content-between gap-3 flex-wrap">
                                                        <div>
                                                            <strong class="d-block" style="color:#016A70;">2. เชื่อมต่อบัญชี LINE</strong>
                                                            <span class="small text-muted">เพิ่มเพื่อนแล้ว กดปุ่มนี้เพื่อรับแจ้งเตือนคำสั่งซื้อ</span>
                                                        </div>
                                                        <a href="<?= htmlspecialchars(line_guest_order_link_url(), ENT_QUOTES, 'UTF-8') ?>" class="btn btn-sm text-white" style="background:#016A70;border-color:#016A70;">
                                                            <i class="fab fa-line me-1"></i> เชื่อมต่อ LINE
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- NAME -->
                                <div class="mb-field">
                                    <label class="form-label"><i class="fas fa-user" style="color:var(--sage);margin-right:6px"></i>ชื่อผู้สั่งซื้อ</label>
                                    <input type="text" name="customer_name" class="form-control"
                                        value="<?= htmlspecialchars($member_name) ?>"
                                        placeholder="กรอกชื่อ-นามสกุล">
                                </div>

                                <!-- PHONE -->
                                <div class="mb-field">
                                    <label class="form-label"><i class="fas fa-phone" style="color:var(--sage);margin-right:6px"></i>เบอร์โทรศัพท์</label>
                                    <input type="tel" name="customer_phone" class="form-control"
                                        value="<?= htmlspecialchars($member_phone) ?>"
                                        placeholder="เช่น 08XXXXXXXX"
                                        inputmode="numeric"
                                        maxlength="10"
                                        pattern="[0-9]{10}">
                                </div>

                                <!-- RECEIVE TYPE -->
                                <div class="mb-field">
                                    <label class="form-label"><i class="fas fa-box" style="color:var(--sage);margin-right:6px"></i>วิธีรับสินค้า</label>
                                    <div class="receive-grid">
                                        <label class="receive-card active" onclick="selectReceiveType('pickup')">
                                            <input type="radio" name="receive_type" value="pickup" checked>
                                            <div class="receive-icon"><i class="fas fa-store"></i></div>
                                            <div class="receive-text">
                                                <strong>รับที่สวน</strong>
                                                <small>รับเองที่สวนลุงเผือก</small>
                                            </div>
                                        </label>
                                        <label class="receive-card" onclick="selectReceiveType('delivery')">
                                            <input type="radio" name="receive_type" value="delivery">
                                            <div class="receive-icon"><i class="fas fa-truck"></i></div>
                                            <div class="receive-text">
                                                <strong>จัดส่ง</strong>
                                                <small>ขั้นต่ำ 500 บาท ส่งฟรี</small>
                                            </div>
                                        </label>
                                    </div>
                                </div>

                                <!-- ADDRESS / NOTE -->
                                <div class="mb-field">
                                    <label class="form-label" id="address_label">
                                        <i class="fas fa-sticky-note" style="color:var(--sage);margin-right:6px"></i>หมายเหตุ
                                    </label>
                                    <textarea class="form-control" name="customer_address" id="customer_address"
                                        placeholder="หมายเหตุเพิ่มเติม" rows="3"></textarea>
                                    <div class="delivery-map-box" id="deliveryMapBox">
                                        <div id="deliveryMapInlineMount">
                                            <div id="deliveryMap"></div>
                                        </div>
                                        <div class="delivery-map-actions">
                                            <button type="button" class="delivery-current-btn" id="useCurrentLocationBtn" data-current-location-btn onclick="useCurrentDeliveryLocation()">
                                                <i class="fas fa-location-crosshairs"></i>
                                                ใช้ตำแหน่งปัจจุบัน
                                            </button>
                                            <button type="button" class="delivery-current-btn" onclick="openDeliveryMapModal()">
                                                <i class="fas fa-expand"></i>
                                                ขยายแผนที่
                                            </button>
                                        </div>
                                        <div class="delivery-map-status" id="deliveryMapStatus">เลือกตำแหน่งจัดส่งบนแผนที่</div>
                                        <div class="delivery-coordinate">
                                            <div>ละติจูด<strong id="deliveryLatText">-</strong></div>
                                            <div>ลองจิจูด<strong id="deliveryLngText">-</strong></div>
                                        </div>
                                    </div>
                                    <input type="hidden" name="delivery_latitude" id="delivery_latitude">
                                    <input type="hidden" name="delivery_longitude" id="delivery_longitude">
                                </div>

                                <!-- DATE -->
                                <div class="mb-field">
                                    <label class="form-label"><i class="fas fa-calendar-alt" style="color:var(--sage);margin-right:6px"></i>วันเวลารับสินค้า</label>
                                    <button type="button" class="date-btn" onclick="openDateModal()">
                                        <div class="date-btn-icon"><i class="fas fa-calendar-alt"></i></div>
                                        <span id="triggerText">เลือกวันและเวลา</span>
                                    </button>
                                    <input type="hidden" id="receive_datetime" name="receive_datetime">
                                </div>

                                <!-- INFO ALERT -->
                                <div class="info-alert">
                                    <i class="fas fa-circle-exclamation" style="margin-top:2px;flex-shrink:0"></i>
                                    <span>สินค้าอาจมีจำนวนจำกัด ทางสวนจะติดต่อกลับหากสินค้าไม่พอ</span>
                                </div>

                                <input type="hidden" name="cart_data" id="cartData">

                                <!-- SUBMIT -->
                                <button type="submit" class="btn-checkout" id="submitBtn" disabled>
                                    <i class="fas fa-check-circle"></i> ยืนยันการสั่งซื้อ
                                </button>

                                <a href="products.php" class="btn-continue-link">
                                    <i class="fas fa-arrow-left"></i> เลือกซื้อเพิ่ม
                                </a>
                            </div>
                        </div>

                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- DELIVERY MAP MODAL -->
    <div class="modal fade delivery-map-modal" id="deliveryMapModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-fullscreen">
            <div class="modal-content">
                <div class="modal-head">
                    <h5><i class="fas fa-map-location-dot me-2"></i>ปักหมุดตำแหน่งจัดส่ง</h5>
                    <button class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="delivery-map-modal-mount" id="deliveryMapModalMount"></div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="delivery-current-btn" data-current-location-btn onclick="useCurrentDeliveryLocation()">
                        <i class="fas fa-location-crosshairs"></i>
                        ใช้ตำแหน่งปัจจุบัน
                    </button>
                    <button type="button" class="delivery-current-btn" data-bs-dismiss="modal">
                        <i class="fas fa-check"></i>
                        ยืนยันตำแหน่ง
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- DATE MODAL -->
    <div class="modal fade" id="dateModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">

                <div class="modal-head">
                    <h5><i class="fas fa-calendar-check me-2"></i>เลือกวันรับสินค้า</h5>
                    <button class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <label class="modal-label">วันที่รับสินค้า</label>

                    <div class="quick-date-grid">
                        <button type="button" class="quick-btn" onclick="setQuickDate(0,this)">
                            <label for="today">วันนี้</label>
                        </button>
                        <button type="button" class="quick-btn" onclick="setQuickDate(1,this)">
                            <label for="tomorrow">พรุ่งนี้</label>
                        </button>
                    </div>

                    <div style="margin-bottom:20px;">
                        <input type="date" id="customDate" class="form-control" style="background:var(--cream);border:1.5px solid var(--border);border-radius:var(--radius-sm);padding:12px 14px;">
                    </div>

                    <label class="modal-label">ช่วงเวลา</label>
                    <input type="time" id="customTime" class="form-control"
                        style="background:var(--cream);border:1.5px solid var(--border);border-radius:var(--radius-sm);padding:12px 14px;margin-bottom:14px;"
                        min="08:00" max="18:00" step="1800">

                    <div class="time-grid">
                        <button type="button" class="time-pill" onclick="setTime('08:30',this)">08:30</button>
                        <button type="button" class="time-pill" onclick="setTime('10:00',this)">10:00</button>
                        <button type="button" class="time-pill" onclick="setTime('13:00',this)">13:00</button>
                        <button type="button" class="time-pill" onclick="setTime('15:00',this)">15:00</button>
                        <button type="button" class="time-pill" onclick="setTime('17:00',this)">17:00</button>
                        <button type="button" class="time-pill" onclick="setTime('17:30',this)">17:30</button>
                    </div>

                    <button class="btn-confirm" onclick="confirmDate()">
                        <i class="fas fa-check-circle me-2"></i>ยืนยันวันรับสินค้า
                    </button>

                </div>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://maps.googleapis.com/maps/api/js?key=<?= rawurlencode((string) $google_maps_api_key) ?>&libraries=geometry&callback=onGoogleMapsReady" defer></script>
    <script>
        window.googleMapsReady = false;
        window.onGoogleMapsReady = function() {
            window.googleMapsReady = true;
            if (typeof initDeliveryMap === 'function') {
                const mapBox = document.getElementById('deliveryMapBox');
                if (mapBox && mapBox.classList.contains('is-visible')) {
                    initDeliveryMap();
                }
            }
        };

        let cart = [];
        let submittingAfterServerSync = false;
        let deliveryMap = null;
        let deliveryArea = null;
        let deliveryAreaPolygons = [];
        let deliveryAreaBounds = null;
        let deliveryMarker = null;
        let deliveryMapLoaded = false;
        let deliveryMapModalInstance = null;

        try {
            cart = JSON.parse(localStorage.getItem("cart")) || [];
        } catch (e) {
            cart = [];
        }


        // ฟอร์แมตราคา
        function formatPrice(price) {
            return '฿' + Number(price).toLocaleString('th-TH', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }

        function escapeHtml(value) {
            return String(value ?? '').replace(/[&<>"']/g, (char) => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            } [char]));
        }

        function safeImagePath(value) {
            const image = String(value || '../assets/no-image.png');
            if (/^(\.\.\/admin\/uploads\/products\/|assets\/|\.\/assets\/|\.\.\/assets\/)/.test(image)) {
                return escapeHtml(image);
            }
            return '../assets/no-image.png';
        }

        function setCartLoading() {
            const cartItems = document.getElementById("cartItems");
            const emptyCart = document.getElementById("emptyCart");
            const submitBtn = document.getElementById("submitBtn");
            const cartFooter = document.getElementById("cartFooter");

            emptyCart.style.display = "none";
            cartFooter.style.display = "none";
            submitBtn.disabled = true;
            cartItems.innerHTML = '<div class="empty-state"><div class="empty-icon"><i class="fas fa-spinner fa-spin"></i></div><div class="empty-title">กำลังตรวจสอบราคา</div></div>';
        }

        async function syncCartWithServer(showAlert = false) {
            if (!Array.isArray(cart)) cart = [];

            if (cart.length === 0) {
                saveCart();
                renderCart();
                return true;
            }

            const requestCart = cart.map(item => ({
                product_id: parseInt(item.product_id, 10) || 0,
                quantity: parseInt(item.quantity, 10) || 0
            }));

            setCartLoading();

            try {
                const response = await fetch('cart_prices.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        cart: requestCart
                    })
                });

                const data = await response.json();

                if (!response.ok || data.status !== 'success') {
                    throw new Error(data.message || 'Unable to check product prices');
                }

                cart = Array.isArray(data.items) ? data.items : [];
                saveCart();
                renderCart();
                return true;
            } catch (error) {
                renderCart();

                if (showAlert) {
                    Swal.fire({
                        icon: 'error',
                        title: 'ตรวจสอบราคาไม่สำเร็จ',
                        text: 'กรุณาลองใหม่อีกครั้งก่อนยืนยันคำสั่งซื้อ'
                    });
                }

                return false;
            }
        }

        function renderCart() {
            const cartItems = document.getElementById("cartItems");
            const emptyCart = document.getElementById("emptyCart");
            const submitBtn = document.getElementById("submitBtn");
            const cartFooter = document.getElementById("cartFooter");
            const shippingText = document.getElementById("shippingText");

            if (!Array.isArray(cart)) cart = [];

            if (cart.length === 0) {
                cartItems.innerHTML = "";
                emptyCart.style.display = "block";
                submitBtn.disabled = true;
                cartFooter.style.display = "none";
                document.getElementById("subtotal").textContent = "฿0.00";
                document.getElementById("totalPrice").textContent = "฿0.00";
                return;
            }

            emptyCart.style.display = "none";
            submitBtn.disabled = false;
            cartFooter.style.display = "flex";

            let total = 0;
            let html = "";

            const receiveType =
                document.querySelector('input[name="receive_type"]:checked')?.value || 'pickup';

            cart.forEach((item, idx) => {
                item.quantity = parseInt(item.quantity) || 1;
                item.price = parseFloat(item.price) || 0;
                const sub = item.price * item.quantity;
                total += sub;

                html += `
                <div class="cart-item">
                    <div class="item-product">
                        <div class="item-img-wrap">
                            <img src="${safeImagePath(item.image)}"
                                class="item-img"
                                onerror="this.src='assets/no-image.png'">
                            ${item.quantity > 1 ? `<span class="item-badge">${item.quantity}</span>` : ''}
                        </div>
                        <div>
                            <div class="item-name">${escapeHtml(item.name || 'ไม่พบชื่อสินค้า')}</div>
                            <span class="item-tag">แนะนำ</span>
                        </div>
                    </div>

                   <div class="item-price">${formatPrice(item.price)}</div>

              <div class="item-actions">

                <div class="item-qty">
                    <div class="qty-wrap">
                        <button type="button" class="qty-btn" onclick="decreaseQty(${idx})">
                            <i class="fas fa-minus"></i>
                        </button>

                        <input type="number" class="qty-num" value="${item.quantity}"
                            min="1" onchange="updateQty(${idx}, this.value)">

                        <button type="button" class="qty-btn" onclick="increaseQty(${idx})">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </div>

                <div class="item-total">${formatPrice(sub)}</div>

                <button type="button" class="btn-remove" onclick="removeItem(${idx})">
                    <i class="fas fa-trash"></i>
                </button>

            </div>
                </div>`;
            });

            cartItems.innerHTML = html;

            if (receiveType === 'delivery') {
                if (total >= 500) {
                    shippingText.innerHTML = '<span class="free-tag">ฟรี</span>';
                } else {
                    shippingText.innerHTML = '<span style="color:red;">ขั้นต่ำ 500 บาท</span>';
                }
            } else {
                shippingText.innerHTML = '<span style="color:var(--sage);">รับที่สวน</span>';
            }
            document.getElementById("subtotal").textContent = formatPrice(total);
            document.getElementById("totalPrice").textContent = formatPrice(total);
            document.getElementById("footerTotal").textContent = formatPrice(total);
        }

        function clearCart() {
            Swal.fire({
                title: 'ล้างตะกร้า?',
                text: 'ต้องการลบสินค้าทั้งหมดออกจากตะกร้า?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e05a38',
                cancelButtonColor: '#888',
                confirmButtonText: 'ลบทั้งหมด',
                cancelButtonText: 'ยกเลิก'
            }).then(r => {
                if (r.isConfirmed) {
                    cart = [];
                    saveCart();
                    renderCart();
                    Swal.fire({
                        icon: 'success',
                        title: 'ล้างตะกร้าแล้ว',
                        timer: 1400,
                        showConfirmButton: false
                    });
                }
            });
        }

        function increaseQty(i) {
            cart[i].quantity++;
            saveCart();
            syncCartWithServer(false);
        }

        function decreaseQty(i) {
            if (cart[i].quantity > 1) {
                cart[i].quantity--;
                saveCart();
                syncCartWithServer(false);
            }
        }

        function updateQty(i, v) {
            const q = parseInt(v);
            if (q > 0) {
                cart[i].quantity = Math.min(q, 99);
                saveCart();
                syncCartWithServer(false);
            }
        }

        function removeItem(i) {
            Swal.fire({
                title: 'ลบสินค้า?',
                text: 'ต้องการลบสินค้านี้ออกจากตะกร้า?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#e05a38',
                cancelButtonColor: '#888',
                confirmButtonText: 'ลบ',
                cancelButtonText: 'ยกเลิก'
            }).then(r => {
                if (r.isConfirmed) {
                    cart.splice(i, 1);
                    saveCart();
                    renderCart();
                    Swal.fire({
                        icon: 'success',
                        title: 'ลบสินค้าแล้ว',
                        timer: 1400,
                        showConfirmButton: false
                    });
                }
            });
        }

        function saveCart() {
            localStorage.setItem("cart", JSON.stringify(cart));
        }

        function setDeliveryMapStatus(message, isError = false) {
            const status = document.getElementById('deliveryMapStatus');
            if (!status) return;
            status.textContent = message;
            status.classList.toggle('is-error', isError);
        }

        function clearDeliveryLocation() {
            document.getElementById('delivery_latitude').value = '';
            document.getElementById('delivery_longitude').value = '';
            document.getElementById('deliveryLatText').textContent = '-';
            document.getElementById('deliveryLngText').textContent = '-';
            if (deliveryMarker) {
                deliveryMarker.setMap(null);
            }
            deliveryMarker = null;
        }

        function refreshDeliveryMapView() {
            if (!deliveryMap || !window.google?.maps) return;

            google.maps.event.trigger(deliveryMap, 'resize');

            if (deliveryMarker) {
                deliveryMap.panTo(deliveryMarker.getPosition());
                deliveryMap.setZoom(Math.max(deliveryMap.getZoom() || 16, 16));
                return;
            }

            if (deliveryAreaBounds) {
                deliveryMap.fitBounds(deliveryAreaBounds);
            }
        }

        function moveDeliveryMapTo(targetId) {
            const mapElement = document.getElementById('deliveryMap');
            const target = document.getElementById(targetId);
            if (!mapElement || !target) return;

            target.appendChild(mapElement);
            setTimeout(refreshDeliveryMapView, 120);
        }

        function openDeliveryMapModal() {
            if (!deliveryMapLoaded) {
                initDeliveryMap();
            }

            const modalElement = document.getElementById('deliveryMapModal');
            if (!modalElement) return;

            deliveryMapModalInstance = bootstrap.Modal.getOrCreateInstance(modalElement);
            modalElement.addEventListener('shown.bs.modal', () => {
                moveDeliveryMapTo('deliveryMapModalMount');
            }, { once: true });
            modalElement.addEventListener('hidden.bs.modal', () => {
                moveDeliveryMapTo('deliveryMapInlineMount');
            }, { once: true });
            deliveryMapModalInstance.show();
        }

        function geoJsonRingToGooglePath(ring) {
            return ring.map(point => ({
                lat: Number(point[1]),
                lng: Number(point[0])
            }));
        }

        function getDeliveryAreaFeatures(data) {
            if (!data) return [];
            return data.type === 'FeatureCollection' ? (data.features || []) : [data];
        }

        function buildDeliveryAreaPolygons(data) {
            const polygons = [];
            const bounds = new google.maps.LatLngBounds();

            getDeliveryAreaFeatures(data).forEach(feature => {
                const geometry = feature.type === 'Feature' ? feature.geometry : feature;
                if (!geometry || !Array.isArray(geometry.coordinates)) return;

                const polygonCoordinates = geometry.type === 'Polygon'
                    ? [geometry.coordinates]
                    : (geometry.type === 'MultiPolygon' ? geometry.coordinates : []);

                polygonCoordinates.forEach(polygonCoordinate => {
                    const paths = polygonCoordinate.map(geoJsonRingToGooglePath);
                    if (!paths.length || !paths[0].length) return;

                    paths[0].forEach(point => bounds.extend(point));

                    const polygon = new google.maps.Polygon({
                        paths,
                        strokeColor: '#28a745',
                        strokeOpacity: 1,
                        strokeWeight: 3,
                        fillColor: '#28a745',
                        fillOpacity: 0.25,
                        map: deliveryMap
                    });

                    polygon.addListener('click', event => {
                        if (event.latLng) {
                            placeDeliveryMarker(event.latLng);
                        }
                    });

                    polygons.push(polygon);
                });
            });

            return { polygons, bounds };
        }

        function pointOnDeliverySegment(x, y, x1, y1, x2, y2) {
            const cross = (y - y1) * (x2 - x1) - (x - x1) * (y2 - y1);
            if (Math.abs(cross) > 0.0000001) return false;

            return x >= Math.min(x1, x2) - 0.0000001 &&
                x <= Math.max(x1, x2) + 0.0000001 &&
                y >= Math.min(y1, y2) - 0.0000001 &&
                y <= Math.max(y1, y2) + 0.0000001;
        }

        function pointInDeliveryRing(longitude, latitude, ring) {
            let inside = false;
            const count = Array.isArray(ring) ? ring.length : 0;
            if (count < 4) return false;

            for (let i = 0, j = count - 1; i < count; j = i++) {
                const xi = Number(ring[i]?.[0] ?? 0);
                const yi = Number(ring[i]?.[1] ?? 0);
                const xj = Number(ring[j]?.[0] ?? 0);
                const yj = Number(ring[j]?.[1] ?? 0);

                if (pointOnDeliverySegment(longitude, latitude, xi, yi, xj, yj)) {
                    return true;
                }

                const intersects = ((yi > latitude) !== (yj > latitude)) &&
                    (longitude < ((xj - xi) * (latitude - yi) / ((yj - yi) || 0.0000001) + xi));

                if (intersects) inside = !inside;
            }

            return inside;
        }

        function pointInDeliveryPolygonCoordinates(longitude, latitude, polygon) {
            if (!Array.isArray(polygon) || !polygon[0] || !pointInDeliveryRing(longitude, latitude, polygon[0])) {
                return false;
            }

            for (let i = 1; i < polygon.length; i++) {
                if (pointInDeliveryRing(longitude, latitude, polygon[i])) {
                    return false;
                }
            }

            return true;
        }

        function pointInDeliveryAreaData(point) {
            if (!deliveryArea) return false;

            const latitude = point.lat();
            const longitude = point.lng();

            return getDeliveryAreaFeatures(deliveryArea).some(feature => {
                const geometry = feature.type === 'Feature' ? feature.geometry : feature;
                if (!geometry || !Array.isArray(geometry.coordinates)) return false;

                if (geometry.type === 'Polygon') {
                    return pointInDeliveryPolygonCoordinates(longitude, latitude, geometry.coordinates);
                }

                if (geometry.type === 'MultiPolygon') {
                    return geometry.coordinates.some(polygon =>
                        pointInDeliveryPolygonCoordinates(longitude, latitude, polygon)
                    );
                }

                return false;
            });
        }

        function isPointInDeliveryArea(point) {
            if (window.google?.maps?.geometry?.poly) {
                const insideGooglePolygon = deliveryAreaPolygons.some(polygon =>
                    google.maps.geometry.poly.containsLocation(point, polygon)
                );
                if (insideGooglePolygon) return true;
            }

            return pointInDeliveryAreaData(point);
        }

        function placeDeliveryMarker(point, successMessage = 'ปักหมุดตำแหน่งจัดส่งแล้ว') {
            if (!deliveryArea || !deliveryAreaPolygons.length) {
                setDeliveryMapStatus('กำลังโหลดพื้นที่จัดส่ง กรุณารอสักครู่', true);
                return false;
            }

            if (!isPointInDeliveryArea(point)) {
                clearDeliveryLocation();
                setDeliveryMapStatus('ตำแหน่งนี้อยู่นอกพื้นที่จัดส่ง กรุณาเลือกในพื้นที่สีเขียว', true);
                return false;
            }

            if (deliveryMarker) {
                deliveryMarker.setMap(null);
            }

            deliveryMarker = new google.maps.Marker({
                position: point,
                map: deliveryMap
            });

            const lat = point.lat();
            const lng = point.lng();
            document.getElementById('delivery_latitude').value = lat.toFixed(7);
            document.getElementById('delivery_longitude').value = lng.toFixed(7);
            document.getElementById('deliveryLatText').textContent = lat.toFixed(7);
            document.getElementById('deliveryLngText').textContent = lng.toFixed(7);
            setDeliveryMapStatus(successMessage);
            return true;
        }

        function setCurrentLocationButtonLoading(isLoading) {
            document.querySelectorAll('[data-current-location-btn]').forEach(button => {
                button.disabled = isLoading;
                button.innerHTML = isLoading
                    ? '<i class="fas fa-spinner fa-spin"></i> กำลังค้นหาตำแหน่ง'
                    : '<i class="fas fa-location-crosshairs"></i> ใช้ตำแหน่งปัจจุบัน';
            });
        }

        function useCurrentDeliveryLocation() {
            if (!navigator.geolocation) {
                setDeliveryMapStatus('เบราว์เซอร์นี้ไม่รองรับการขอตำแหน่งปัจจุบัน', true);
                return;
            }

            if (!deliveryMapLoaded) {
                initDeliveryMap();
            }

            if (!deliveryMap) {
                setDeliveryMapStatus('Google Maps ยังไม่พร้อม กรุณารอสักครู่แล้วลองใหม่', true);
                return;
            }

            setCurrentLocationButtonLoading(true);
            setDeliveryMapStatus('กรุณาอนุญาตให้เบราว์เซอร์เข้าถึงตำแหน่งปัจจุบัน');

            navigator.geolocation.getCurrentPosition(
                position => {
                    setCurrentLocationButtonLoading(false);

                    const point = new google.maps.LatLng(
                        position.coords.latitude,
                        position.coords.longitude
                    );

                    deliveryMap.panTo(point);
                    deliveryMap.setZoom(Math.max(deliveryMap.getZoom() || 16, 16));
                    placeDeliveryMarker(point, 'ใช้ตำแหน่งปัจจุบันเป็นจุดจัดส่งแล้ว');
                },
                error => {
                    setCurrentLocationButtonLoading(false);
                    let message = 'ไม่สามารถอ่านตำแหน่งปัจจุบันได้ กรุณาปักหมุดบนแผนที่แทน';

                    if (error.code === error.PERMISSION_DENIED) {
                        message = 'ไม่ได้รับอนุญาตให้เข้าถึงตำแหน่ง กรุณาอนุญาต GPS ในเบราว์เซอร์หรือปักหมุดเอง';
                    } else if (error.code === error.POSITION_UNAVAILABLE) {
                        message = 'ไม่พบตำแหน่งปัจจุบัน กรุณาเปิด GPS หรือปักหมุดเอง';
                    } else if (error.code === error.TIMEOUT) {
                        message = 'ค้นหาตำแหน่งนานเกินไป กรุณาลองอีกครั้งหรือปักหมุดเอง';
                    }

                    setDeliveryMapStatus(message, true);
                },
                {
                    enableHighAccuracy: true,
                    timeout: 12000,
                    maximumAge: 0
                }
            );
        }

        function initDeliveryMap() {
            if (deliveryMapLoaded || !document.getElementById('deliveryMap')) {
                return;
            }

            if (!window.googleMapsReady || !window.google?.maps?.Map) {
                setDeliveryMapStatus('Google Maps ยังไม่พร้อม กรุณาตรวจสอบ API key หรือรอสักครู่แล้วลองใหม่', true);
                return;
            }

            deliveryMapLoaded = true;
            const defaultCenter = { lat: 17.936707, lng: 101.738149 };
            deliveryMap = new google.maps.Map(document.getElementById('deliveryMap'), {
                center: defaultCenter,
                zoom: 14,
                mapTypeControl: false,
                streetViewControl: false,
                fullscreenControl: true
            });

            fetch('Map/DeliveryArea.geojson')
                .then(response => {
                    if (!response.ok) {
                        throw new Error('load failed');
                    }
                    return response.json();
                })
                .then(data => {
                    deliveryArea = data;
                    const builtArea = buildDeliveryAreaPolygons(data);
                    deliveryAreaPolygons = builtArea.polygons;
                    deliveryAreaBounds = builtArea.bounds;

                    if (!deliveryAreaPolygons.length) {
                        throw new Error('invalid area');
                    }

                    deliveryMap.fitBounds(deliveryAreaBounds);
                    setDeliveryMapStatus('แตะตำแหน่งภายในพื้นที่สีเขียวเพื่อปักหมุดจัดส่ง');
                })
                .catch(() => {
                    setDeliveryMapStatus('โหลดพื้นที่จัดส่งไม่สำเร็จ กรุณาลองใหม่อีกครั้ง', true);
                });

            deliveryMap.addListener('click', event => {
                placeDeliveryMarker(event.latLng);
            });
        }

        function toggleDeliveryMap(type) {
            const mapBox = document.getElementById('deliveryMapBox');
            if (!mapBox) return;

            if (type === 'delivery') {
                mapBox.classList.add('is-visible');
                initDeliveryMap();
                setTimeout(() => {
                    if (deliveryMap) {
                        google.maps.event.trigger(deliveryMap, 'resize');
                    }
                }, 80);
            } else {
                mapBox.classList.remove('is-visible');
                clearDeliveryLocation();
            }
        }

        function selectReceiveType(type) {
            document.querySelectorAll('.receive-card').forEach(c => c.classList.remove('active'));
            const inp = document.querySelector(`input[value="${type}"]`);
            inp.closest('.receive-card').classList.add('active');
            inp.checked = true;
            toggleAddressField(type);
            toggleDeliveryMap(type);
            syncCartWithServer(false);
        }

        function toggleAddressField(type) {
            const lbl = document.getElementById('address_label');
            const ta = document.getElementById('customer_address');
            if (type === 'pickup') {
                lbl.innerHTML = '<i class="fas fa-sticky-note" style="color:var(--sage);margin-right:6px"></i>หมายเหตุ';
                ta.placeholder = 'หมายเหตุเพิ่มเติม ';
                ta.removeAttribute('required');
            } else {
                lbl.innerHTML = '<i class="fas fa-map-marker-alt" style="color:var(--sage);margin-right:6px"></i>รายละเอียดที่อยู่จัดส่ง';
                ta.placeholder = 'รายละเอียดเพิ่มเติม (ถ้ามี) เช่น บ้านเลขที่ จุดสังเกต หรือซอย';
                ta.removeAttribute('required');
            }
        }

        function submitOrder() {
            if (submittingAfterServerSync) {
                return true;
            }

            const dt = document.getElementById("receive_datetime").value.trim();
            const type = document.querySelector('input[name="receive_type"]:checked')?.value || '';
            const name = document.querySelector('input[name="customer_name"]').value.trim();
            const phone = document.querySelector('input[name="customer_phone"]').value.trim();
            const phoneDigits = phone.replace(/\D/g, '');

            if (cart.length === 0) {
                Swal.fire({
                    icon: 'error',
                    title: 'ตะกร้าว่าง',
                    text: 'กรุณาเลือกสินค้าก่อนสั่งซื้อ'
                });
                return false;
            }
            if (!name || !phone) {
                Swal.fire({
                    icon: 'error',
                    title: 'ข้อมูลไม่ครบ',
                    text: 'กรุณากรอกชื่อและเบอร์โทรศัพท์'
                });
                return false;
            }
            if (!dt) {
                Swal.fire({
                    icon: 'error',
                    title: 'ยังไม่เลือกวัน-เวลา',
                    text: 'กรุณาเลือกวันและเวลารับสินค้า'
                });
                return false;
            }
            if (phoneDigits.length !== 10) {
                Swal.fire({
                    icon: 'error',
                    title: 'เบอร์โทรไม่ถูกต้อง',
                    text: 'กรุณากรอกเบอร์โทรศัพท์ให้ครบ 10 หลัก'
                });
                return false;
            }
            if (type === 'delivery' && (!document.getElementById('delivery_latitude').value || !document.getElementById('delivery_longitude').value)) {
                Swal.fire({
                    icon: 'error',
                    title: 'ยังไม่ได้ปักหมุด',
                    text: 'กรุณาปักหมุดตำแหน่งจัดส่งบนแผนที่'
                });
                return false;
            }

            document.getElementById("loadingOverlay").style.display = "flex";
            document.getElementById("submitBtn").disabled = true;

            syncCartWithServer(true).then((ok) => {
                if (!ok) {
                    document.getElementById("loadingOverlay").style.display = "none";
                    document.getElementById("submitBtn").disabled = false;
                    return;
                }

                const serverTotal = cart.reduce((s, i) => s + (Number(i.price) * Number(i.quantity)), 0);

                if (type === 'delivery' && serverTotal < 500) {
                    document.getElementById("loadingOverlay").style.display = "none";
                    document.getElementById("submitBtn").disabled = false;
                    Swal.fire({
                        icon: 'error',
                        title: 'ยอดไม่ถึงขั้นต่ำ',
                        text: 'การจัดส่งต้องมียอดขั้นต่ำ 500 บาท'
                    });
                    return;
                }

                document.getElementById("cartData").value = JSON.stringify(cart.map(item => ({
                    product_id: parseInt(item.product_id, 10),
                    quantity: parseInt(item.quantity, 10)
                })));

                submittingAfterServerSync = true;
                document.querySelector('form[action="save_order.php"]').submit();
            });

            return false;
        }

        document.addEventListener('DOMContentLoaded', function() {
            toggleAddressField('pickup');
            syncCartWithServer(true);

            try {
                if (localStorage.getItem('line_friend_added_order') === '1') {
                    const step = document.getElementById('lineConnectStepOrder');
                    if (step) step.style.display = 'block';
                }
            } catch (error) {}

            <?php if ($order_error !== ''): ?>
                Swal.fire({
                    icon: 'error',
                    title: 'ไม่สามารถสั่งซื้อได้',
                    text: <?= json_encode($order_error, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>
                });
            <?php endif; ?>
        });

        let selectedDate = "",
            selectedTime = "";

        function showLineConnectStepOrder() {
            try {
                localStorage.setItem('line_friend_added_order', '1');
            } catch (error) {}

            const step = document.getElementById('lineConnectStepOrder');
            if (step) {
                step.style.display = 'block';
                setTimeout(() => step.scrollIntoView({ behavior: 'smooth', block: 'center' }), 150);
            }
        }

        function openDateModal() {
            new bootstrap.Modal(document.getElementById('dateModal')).show();
        }

        function setQuickDate(offset, el) {
            const d = new Date();
            d.setDate(d.getDate() + offset);
            selectedDate = d.toISOString().split('T')[0];
            document.getElementById('customDate').value = selectedDate;
            document.querySelectorAll('.quick-btn').forEach(b => b.classList.remove('active'));
            el.classList.add('active');
        }

        function setTime(t, el) {
            selectedTime = t;
            document.getElementById('customTime').value = t;
            document.querySelectorAll('.time-pill').forEach(b => b.classList.remove('active'));
            el.classList.add('active');
        }

        function confirmDate() {
            const cd = document.getElementById("customDate").value;
            const ct = document.getElementById("customTime").value;
            if (cd) selectedDate = cd;
            if (ct) selectedTime = ct;

            if (!selectedDate || !selectedTime) {
                Swal.fire({
                    icon: 'warning',
                    title: 'กรุณาเลือกวัน-เวลา',
                    text: 'โปรดระบุวันที่และเวลารับสินค้า'
                });
                return;
            }

            document.getElementById("receive_datetime").value = `${selectedDate} ${selectedTime}`;
            document.getElementById("triggerText").textContent = `${selectedDate}  เวลา ${selectedTime} น.`;
            bootstrap.Modal.getInstance(document.getElementById('dateModal')).hide();
        }
    </script>
</body>

</html>
