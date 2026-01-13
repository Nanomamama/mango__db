<?php
require_once '../admin/db.php';

// รับ booking_id จาก GET
$booking_id = isset($_GET['id']) ? intval($_GET['id']) : null;
if (!$booking_id) {
    die("ไม่พบข้อมูลการจอง");
}

$sql = "SELECT * FROM bookings WHERE bookings_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $booking_id);
$stmt->execute();
$result = $stmt->get_result();
$booking = $result->fetch_assoc();
if (!$booking) { die("ไม่พบข้อมูลการจอง"); }
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>ใบเสร็จการจองเข้าชมสวนลุงเผือก</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --ant-primary-color: #1890ff;
            --ant-success-color: #52c41a;
            --ant-warning-color: #faad14;
            --ant-error-color: #ff4d4f;
            --ant-heading-color: #262626;
            --ant-text-color: #595959;
            --ant-border-color: #d9d9d9;
            --ant-background-color: #f5f5f5;
        }
        
        * {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e8f0 100%);
            min-height: 100vh;
            padding: 20px;
            color: var(--ant-text-color);
        }
        
        .ant-card {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 3px 6px -4px rgba(0,0,0,0.12), 0 6px 16px 0 rgba(0,0,0,0.08), 0 9px 28px 8px rgba(0,0,0,0.05);
            border: 1px solid var(--ant-border-color);
            max-width: 800px;
            margin: 0 auto;
            overflow: hidden;
        }
        
        .ant-card-header {
            background: var(--ant-primary-color);
            color: white;
            padding: 20px 24px;
            border-bottom: 1px solid var(--ant-border-color);
        }
        
        .ant-card-body {
            padding: 24px;
        }
        
        .ant-typography {
            margin-bottom: 0;
        }
        
        .ant-divider {
            margin: 20px 0;
            border-top: 1px solid var(--ant-border-color);
        }
        
        .ant-descriptions {
            width: 100%;
        }
        
        .ant-descriptions-title {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 16px;
            color: var(--ant-heading-color);
        }
        
        .ant-descriptions-item-label {
            font-weight: 500;
            color: var(--ant-text-color);
            padding: 8px 16px;
            background: #fafafa;
            border-right: 1px solid var(--ant-border-color);
        }
        
        .ant-descriptions-item-content {
            padding: 8px 16px;
            border-bottom: 1px solid var(--ant-border-color);
        }
        
        .ant-row {
            display: flex;
            flex-wrap: wrap;
        }
        
        .ant-col {
            flex: 1;
        }
        
        .ant-statistic {
            text-align: center;
            padding: 16px;
            border-radius: 6px;
            background: #fafafa;
        }
        
        .ant-statistic-title {
            font-size: 14px;
            color: var(--ant-text-color);
            margin-bottom: 4px;
        }
        
        .ant-statistic-content {
            font-size: 24px;
            font-weight: 600;
            color: var(--ant-heading-color);
        }
        
        .ant-statistic-content-value {
            color: var(--ant-success-color);
        }
        
        .ant-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 400;
            white-space: nowrap;
            text-align: center;
            background-image: none;
            border: 1px solid transparent;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.645, 0.045, 0.355, 1);
            user-select: none;
            touch-action: manipulation;
            height: 40px;
            padding: 8px 16px;
            font-size: 14px;
            border-radius: 6px;
            margin: 0 8px 8px 0;
        }
        
        .ant-btn-primary {
            color: #fff;
            background: var(--ant-primary-color);
            border-color: var(--ant-primary-color);
            box-shadow: 0 2px 0 rgba(5, 145, 255, 0.1);
        }
        
        .ant-btn-primary:hover {
            background: #40a9ff;
            border-color: #40a9ff;
        }
        
        .ant-btn-default {
            color: var(--ant-text-color);
            background: #fff;
            border-color: var(--ant-border-color);
        }
        
        .ant-btn-default:hover {
            color: var(--ant-primary-color);
            border-color: var(--ant-primary-color);
        }
        
        .ant-alert {
            position: relative;
            padding: 12px 16px;
            border-radius: 6px;
            margin-bottom: 16px;
        }
        
        .ant-alert-info {
            background-color: #e6f7ff;
            border: 1px solid #91d5ff;
        }
        
        .ant-alert-message {
            color: var(--ant-heading-color);
            font-weight: 500;
        }
        
        .ant-alert-description {
            color: var(--ant-text-color);
            margin-top: 4px;
        }
        
        .ant-tag {
            display: inline-block;
            padding: 2px 8px;
            font-size: 12px;
            line-height: 1.5;
            border-radius: 4px;
            border: 1px solid var(--ant-border-color);
        }
        
        .ant-tag-success {
            color: var(--ant-success-color);
            background: #f6ffed;
            border-color: #b7eb8f;
        }
        
        .text-center {
            text-align: center;
        }
        
        .mt-3 {
            margin-top: 16px;
        }
        
        .mb-3 {
            margin-bottom: 16px;
        }
        
        .d-flex {
            display: flex;
        }
        
        .justify-center {
            justify-content: center;
        }
        
        .align-center {
            align-items: center;
        }
        
        .flex-wrap {
            flex-wrap: wrap;
        }
        
        @media print {
            body {
                background: #fff;
                padding: 0;
            }
            
            .no-print {
                display: none !important;
            }
            
            .ant-card {
                box-shadow: none;
                border: 1px solid #ddd;
            }
        }
        
        @media (max-width: 768px) {
            .ant-row {
                flex-direction: column;
            }
            
            .ant-col {
                margin-bottom: 16px;
            }
        }
    </style>
</head>
<body>
    <div class="ant-card">
        <div class="ant-card-header">
            <div class="d-flex justify-content-between align-center">
                <div>
                    <h2 class="ant-typography" style="color: white; margin: 0;">
                        <i class="fas fa-file-invoice me-2"></i>ใบเสร็จการจองเข้าชมสวนลุงเผือก
                    </h2>
                    <div class="mt-2" style="color: rgba(255,255,255,0.85);">
                        <i class="fas fa-hashtag me-1"></i>รหัสการจอง: <?= htmlspecialchars($booking['bookings_id']) ?>
                    </div>
                </div>
                <div class="ant-tag ant-tag-success" style="font-size: 14px; padding: 4px 12px;">
                    <i class="fas fa-check-circle me-1"></i>จ่ายมัดจำแล้ว
                </div>
            </div>
        </div>
        
        <div class="ant-card-body">
            <div class="ant-alert ant-alert-info">
                <div class="ant-alert-message">
                    ขอบคุณสำหรับการจองเข้าชมสวนลุงเผือก
                </div>
                <div class="ant-alert-description">
                    การจองของคุณได้รับการบันทึกเรียบร้อยแล้ว กรุณานำใบเสร็จนี้แสดงในวันเข้าชม
                </div>
            </div>
            
            <div class="ant-descriptions">
                <div class="ant-descriptions-title">ข้อมูลการจอง</div>
                <div class="ant-row">
                    <div class="ant-col" style="flex: 0 0 50%;">
                        <div class="ant-descriptions-item-label">ชื่อคณะ</div>
                        <div class="ant-descriptions-item-content"><?= htmlspecialchars($booking['name']) ?></div>
                    </div>
                    <div class="ant-col" style="flex: 0 0 50%;">
                        <div class="ant-descriptions-item-label">วันที่จอง</div>
                        <div class="ant-descriptions-item-content"><?= htmlspecialchars($booking['date']) ?></div>
                    </div>
                </div>
                <div class="ant-row">
                    <div class="ant-col" style="flex: 0 0 50%;">
                        <div class="ant-descriptions-item-label">เวลาเข้าชม</div>
                        <div class="ant-descriptions-item-content"><?= htmlspecialchars($booking['time']) ?></div>
                    </div>
                    <div class="ant-col" style="flex: 0 0 50%;">
                        <div class="ant-descriptions-item-label">จำนวนผู้เข้าชม</div>
                        <div class="ant-descriptions-item-content"><?= htmlspecialchars($booking['people']) ?> คน</div>
                    </div>
                </div>
                <div class="ant-row">
                    <div class="ant-col" style="flex: 0 0 50%;">
                        <div class="ant-descriptions-item-label">อาหารกลางวัน</div>
                        <div class="ant-descriptions-item-content"><?php
                            if (!isset($booking['lunch']) || $booking['lunch'] === null || $booking['lunch'] === '') {
                                echo '-';
                            } else {
                                echo ($booking['lunch'] == 1) ? 'ต้องการ' : 'ไม่ต้องการ';
                            }
                        ?></div>
                    </div>
                </div>
                <?php if (!empty($booking['phone'])): ?>
                <div class="ant-row">
                    <div class="ant-col" style="flex: 0 0 50%;">
                        <div class="ant-descriptions-item-label">เบอร์โทรศัพท์</div>
                        <div class="ant-descriptions-item-content"><?= htmlspecialchars($booking['phone']) ?></div>
                    </div>
                    <div class="ant-col" style="flex: 0 0 50%;">
                        <div class="ant-descriptions-item-label">สถานะ</div>
                        <div class="ant-descriptions-item-content">
                            <span class="ant-tag ant-tag-success">รอการอนุมัติ</span>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="ant-divider"></div>
            
            <div class="ant-descriptions">
                <div class="ant-descriptions-title">รายละเอียดการชำระเงิน</div>
                <div class="ant-row">
                    <div class="ant-col" style="flex: 0 0 33.333%;">
                        <div class="ant-statistic">
                            <div class="ant-statistic-title">ยอดรวมทั้งหมด</div>
                            <div class="ant-statistic-content">
                                <span class="ant-statistic-content-value"><?= number_format($booking['total_amount'], 2) ?></span> บาท
                            </div>
                        </div>
                    </div>
                    <div class="ant-col" style="flex: 0 0 33.333%;">
                        <div class="ant-statistic">
                            <div class="ant-statistic-title">ยอดมัดจำ (30%)</div>
                            <div class="ant-statistic-content">
                                <span class="ant-statistic-content-value"><?= number_format($booking['deposit_amount'], 2) ?></span> บาท
                            </div>
                        </div>
                    </div>
                    <div class="ant-col" style="flex: 0 0 33.333%;">
                        <div class="ant-statistic">
                            <div class="ant-statistic-title">ยอดคงเหลือ</div>
                            <div class="ant-statistic-content">
                                <span class="ant-statistic-content-value"><?= number_format($booking['remain_amount'], 2) ?></span> บาท
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="ant-divider"></div>
            
            <div class="text-center no-print">
                <p style="color: var(--ant-text-color); margin-bottom: 20px;">
                    <i class="fas fa-info-circle me-1"></i>กรุณาบันทึกหรือพิมพ์ใบเสร็จนี้เพื่อใช้เป็นหลักฐาน
                </p>
                
                <div class="d-flex justify-center flex-wrap">
                    <button class="ant-btn ant-btn-primary" onclick="window.print()">
                        <i class="fas fa-print me-2"></i>พิมพ์ใบเสร็จ
                    </button>
                    <button class="ant-btn ant-btn-primary" id="downloadImgBtn">
                        <i class="fas fa-download me-2"></i>ดาวน์โหลดเป็นรูปภาพ
                    </button>
                    <?php if (!empty($booking['slip'])): ?>
                    <a class="ant-btn ant-btn-default" href="<?= 'download.php?type=slip&file=' . rawurlencode($booking['slip']) ?>" target="_blank" style="margin-left:8px;">
                        <i class="fas fa-file-image me-2"></i>ดูสลิปที่อัปโหลด
                    </a>
                    <?php else: ?>
                    <button class="ant-btn ant-btn-default" id="showUploadFormBtn" style="margin-left:8px;">
                        <i class="fas fa-upload me-2"></i>แจ้งสลิปการชำระ
                    </button>
                    <?php endif; ?>
                    <button class="ant-btn ant-btn-default" onclick="window.location.href='index.php'">
                        <i class="fas fa-arrow-left me-2"></i>กลับสู่หน้าหลัก
                    </button>
                </div>
            </div>

            <!-- ฟอร์มอัปโหลดสลิป (แสดงเมื่อยังไม่มีสลิป หรือผู้ใช้ต้องการอัปโหลดใหม่) -->
            <div id="uploadSlipSection" class="ant-card-body" style="max-width:700px;margin:18px auto; display: none;">
                <div class="ant-alert ant-alert-info">
                    <div class="ant-alert-message">อัปโหลดสลิปการชำระเงิน</div>
                    <div class="ant-alert-description">กรุณาอัปโหลดสลิปการชำระเงินเพื่อยืนยันการจองของคุณ เจ้าหน้าที่จะตรวจสอบและอนุมัติ</div>
                </div>
                <form method="POST" action="upload_slip_handler.php" enctype="multipart/form-data">
                    <input type="hidden" name="booking_id" value="<?= htmlspecialchars($booking['bookings_id']) ?>">
                    <div class="mb-3">
                        <label class="form-label">ไฟล์สลิป (jpg, png, pdf)</label>
                        <input type="file" class="form-control" name="slip" accept="image/*,application/pdf" required>
                    </div>
                    <div class="d-flex">
                        <button type="submit" class="ant-btn ant-btn-primary">อัปโหลดและแจ้งสลิป</button>
                        <button type="button" id="cancelUploadBtn" class="ant-btn ant-btn-default" style="margin-left:8px;">ยกเลิก</button>
                    </div>
                </form>
            </div>
            
            <div class="text-center mt-3" style="color: var(--ant-text-color); font-size: 14px;">
                <p>ขอบคุณที่ใช้บริการสวนมะม่วงลุงเผือก</p>
                <p>สอบถามข้อมูลเพิ่มเติม: 065-107-8576</p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
    <script>
        document.getElementById('downloadImgBtn').addEventListener('click', function() {
            html2canvas(document.querySelector('.ant-card')).then(function(canvas) {
                var link = document.createElement('a');
                link.download = 'receipt-<?= htmlspecialchars($booking['bookings_id']) ?>.png';
                link.href = canvas.toDataURL();
                link.click();
            });
        });

        // แสดงฟอร์มอัปโหลดสลิปเมื่อกดปุ่ม
        const showUploadBtn = document.getElementById('showUploadFormBtn');
        if (showUploadBtn) {
            showUploadBtn.addEventListener('click', function() {
                document.getElementById('uploadSlipSection').style.display = 'block';
                showUploadBtn.style.display = 'none';
                window.scrollTo({ top: document.getElementById('uploadSlipSection').offsetTop - 20, behavior: 'smooth' });
            });
        }
        const cancelUploadBtn = document.getElementById('cancelUploadBtn');
        if (cancelUploadBtn) {
            cancelUploadBtn.addEventListener('click', function() {
                document.getElementById('uploadSlipSection').style.display = 'none';
                if (showUploadBtn) showUploadBtn.style.display = 'inline-block';
            });
        }
    </script>
</body>
</html>