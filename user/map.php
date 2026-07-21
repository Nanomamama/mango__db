<?php
session_start();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ปักหมุดแผนที่จัดส่ง</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css">
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f7f5;
            color: #1f2933;
        }

        .delivery-map-page {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 360px;
            min-height: 100vh;
        }

        #map {
            min-height: 100vh;
            width: 100%;
        }

        .map-panel {
            background: #ffffff;
            border-left: 1px solid #dbe5dd;
            padding: 24px;
            box-shadow: -8px 0 24px rgba(15, 23, 42, 0.08);
        }

        .map-panel h1 {
            margin: 0 0 8px;
            font-size: 24px;
            line-height: 1.25;
            color: #0f5f55;
        }

        .map-panel p {
            margin: 0 0 18px;
            color: #52635d;
            line-height: 1.6;
        }

        .map-status {
            padding: 12px 14px;
            border-radius: 8px;
            border: 1px solid #d6e3dc;
            background: #f8fbf9;
            font-weight: 700;
            line-height: 1.5;
            margin-bottom: 16px;
        }

        .map-status.is-valid {
            border-color: #9ad7ad;
            background: #eefaf1;
            color: #146c2e;
        }

        .map-status.is-error {
            border-color: #f0b7b7;
            background: #fff3f3;
            color: #9b1c1c;
        }

        .coordinate-box {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-bottom: 16px;
        }

        .coordinate-item {
            padding: 10px;
            border: 1px solid #dce7df;
            border-radius: 8px;
            background: #fbfdfb;
        }

        .coordinate-item span {
            display: block;
            margin-bottom: 4px;
            color: #60756d;
            font-size: 12px;
            font-weight: 700;
        }

        .coordinate-item strong {
            display: block;
            min-height: 20px;
            font-size: 14px;
            overflow-wrap: anywhere;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #33443e;
            font-weight: 700;
        }

        textarea {
            width: 100%;
            min-height: 96px;
            resize: vertical;
            padding: 12px;
            border: 1px solid #cfdcd4;
            border-radius: 8px;
            font: inherit;
            line-height: 1.5;
            margin-bottom: 16px;
        }

        .save-button {
            width: 100%;
            min-height: 46px;
            border: 0;
            border-radius: 8px;
            background: #0f766e;
            color: #ffffff;
            font-size: 16px;
            font-weight: 800;
            cursor: pointer;
        }

        .save-button:disabled {
            background: #9ca3af;
            cursor: not-allowed;
        }

        .save-result {
            min-height: 24px;
            margin-top: 14px;
            line-height: 1.5;
            font-weight: 700;
        }

        .save-result.is-success {
            color: #146c2e;
        }

        .save-result.is-error {
            color: #9b1c1c;
        }

        @media (max-width: 900px) {
            .delivery-map-page {
                grid-template-columns: 1fr;
            }

            #map {
                min-height: 58vh;
            }

            .map-panel {
                border-left: 0;
                border-top: 1px solid #dbe5dd;
                box-shadow: 0 -8px 24px rgba(15, 23, 42, 0.08);
            }
        }
    </style>
</head>

<body>
    <main class="delivery-map-page">
        <div id="map"></div>

        <aside class="map-panel">
            <h1>ปักหมุดตำแหน่งจัดส่ง</h1>
            <p>แตะตำแหน่งบนแผนที่ภายในพื้นที่สีเขียว แล้วกดบันทึกตำแหน่งเพื่อส่งข้อมูลให้ระบบจัดส่ง</p>

            <div id="mapStatus" class="map-status">กำลังโหลดพื้นที่จัดส่ง...</div>

            <div class="coordinate-box">
                <div class="coordinate-item">
                    <span>ละติจูด</span>
                    <strong id="selectedLat">-</strong>
                </div>
                <div class="coordinate-item">
                    <span>ลองจิจูด</span>
                    <strong id="selectedLng">-</strong>
                </div>
            </div>

            <label for="addressNote">รายละเอียดจุดส่ง/หมายเหตุ</label>
            <textarea id="addressNote" maxlength="500" placeholder="เช่น บ้านหลังสีขาว ซอย 5 หรือจุดสังเกตใกล้เคียง"></textarea>

            <button type="button" id="saveLocationBtn" class="save-button" disabled>บันทึกตำแหน่งจัดส่ง</button>
            <div id="saveResult" class="save-result" aria-live="polite"></div>
        </aside>
    </main>

    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@turf/turf@6/turf.min.js"></script>
    <script>
        const csrfToken = <?= json_encode($_SESSION['csrf_token'], JSON_UNESCAPED_UNICODE) ?>;
        const map = L.map('map').setView([17.936707, 101.738149], 17);
        const mapStatus = document.getElementById('mapStatus');
        const saveResult = document.getElementById('saveResult');
        const saveButton = document.getElementById('saveLocationBtn');
        const selectedLat = document.getElementById('selectedLat');
        const selectedLng = document.getElementById('selectedLng');
        const addressNote = document.getElementById('addressNote');

        let polygonData = null;
        let marker = null;
        let selectedLocation = null;

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        function setStatus(message, type = '') {
            mapStatus.textContent = message;
            mapStatus.className = 'map-status';
            if (type) {
                mapStatus.classList.add(type);
            }
        }

        function setSaveResult(message, type = '') {
            saveResult.textContent = message;
            saveResult.className = 'save-result';
            if (type) {
                saveResult.classList.add(type);
            }
        }

        fetch('Map/DeliveryArea.geojson')
            .then(response => {
                if (!response.ok) {
                    throw new Error('Cannot load delivery area');
                }

                return response.json();
            })
            .then(data => {
                polygonData = data;

                const polygon = L.geoJSON(data, {
                    style: {
                        color: '#28a745',
                        weight: 3,
                        fillColor: '#28a745',
                        fillOpacity: 0.3
                    }
                }).addTo(map);

                map.fitBounds(polygon.getBounds());
                setStatus('แตะตำแหน่งภายในพื้นที่สีเขียวเพื่อปักหมุด', 'is-valid');
            })
            .catch(() => {
                setStatus('โหลดพื้นที่จัดส่งไม่สำเร็จ กรุณาลองใหม่อีกครั้ง', 'is-error');
            });

        map.on('click', event => {
            if (!polygonData) {
                setStatus('กำลังโหลดพื้นที่จัดส่ง กรุณารอสักครู่', 'is-error');
                return;
            }

            const point = turf.point([event.latlng.lng, event.latlng.lat]);
            const inside = turf.booleanPointInPolygon(point, polygonData.features[0]);

            if (!inside) {
                setStatus('ตำแหน่งนี้อยู่นอกพื้นที่จัดส่ง กรุณาเลือกในพื้นที่สีเขียว', 'is-error');
                saveButton.disabled = true;
                selectedLocation = null;
                return;
            }

            if (marker) {
                map.removeLayer(marker);
            }

            marker = L.marker(event.latlng).addTo(map);
            selectedLocation = {
                latitude: event.latlng.lat,
                longitude: event.latlng.lng
            };

            selectedLat.textContent = selectedLocation.latitude.toFixed(7);
            selectedLng.textContent = selectedLocation.longitude.toFixed(7);
            saveButton.disabled = false;
            setStatus('เลือกตำแหน่งจัดส่งแล้ว กดบันทึกเพื่อบันทึกลงฐานข้อมูล', 'is-valid');
            setSaveResult('');
        });

        saveButton.addEventListener('click', () => {
            if (!selectedLocation) {
                setSaveResult('กรุณาปักหมุดตำแหน่งจัดส่งก่อนบันทึก', 'is-error');
                return;
            }

            saveButton.disabled = true;
            setSaveResult('กำลังบันทึกตำแหน่ง...', '');

            fetch('save_location.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    csrf_token: csrfToken,
                    latitude: selectedLocation.latitude,
                    longitude: selectedLocation.longitude,
                    address_note: addressNote.value.trim()
                })
            })
                .then(response => response.json())
                .then(data => {
                    if (!data.success) {
                        throw new Error(data.message || 'บันทึกตำแหน่งไม่สำเร็จ');
                    }

                    setSaveResult(data.message || 'บันทึกตำแหน่งจัดส่งสำเร็จ', 'is-success');
                })
                .catch(error => {
                    setSaveResult(error.message || 'บันทึกตำแหน่งไม่สำเร็จ', 'is-error');
                })
                .finally(() => {
                    saveButton.disabled = false;
                });
        });
    </script>
</body>

</html>
