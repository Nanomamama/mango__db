<style>
    .location-showcase {
        --location-primary: #0d6b63;
        --location-primary-deep: #064e4a;
        --location-accent: #f5b971;
        --location-surface: rgba(255, 255, 255, 0.82);
        --location-border: rgba(13, 107, 99, 0.14);
        --location-text: #17312e;
        --location-muted: #5f7672;
        position: relative;
        isolation: isolate;
        overflow: hidden;
        padding: 92px 0;
        margin: 36px 0 48px;
        font-family: 'Prompt', sans-serif;
        color: var(--location-text);
       
    }

    .location-showcase::before,
    .location-showcase::after {
        content: "";
        position: absolute;
        inset: auto;
        border-radius: 999px;
        pointer-events: none;
        z-index: -1;
        filter: blur(8px);
    }

 
    .location-shell {
        width: min(1180px, calc(100% - 32px));
        margin: 0 auto;
    }

    .location-hero {
        display: grid;
        grid-template-columns: minmax(0, 1.1fr) minmax(290px, 0.9fr);
        gap: 28px;
        align-items: stretch;
        margin-bottom: 28px;
    }

    .location-panel {
        position: relative;
        overflow: hidden;
        padding: 32px;
        border: 1px solid var(--location-border);
        border-radius: 28px;
        background: var(--location-surface);
        box-shadow: 0 20px 45px rgba(16, 63, 58, 0.08);
        backdrop-filter: blur(14px);
    }

    .location-panel::before {
        content: "";
        position: absolute;
        inset: 0;
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.2), transparent 46%);
        pointer-events: none;
    }

    .location-kicker {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 10px 16px;
        border-radius: 999px;
        margin-bottom: 18px;
        font-size: 0.95rem;
        font-weight: 600;
        letter-spacing: 0.02em;
        color: var(--location-primary-deep);
        background: rgba(255, 255, 255, 0.74);
        border: 1px solid rgba(13, 107, 99, 0.12);
    }

    .location-kicker::before {
        content: "";
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--location-primary), var(--location-accent));
        box-shadow: 0 0 0 6px rgba(13, 107, 99, 0.09);
    }

    .location-heading {
        margin: 0;
        font-size: clamp(2rem, 4vw, 3.35rem);
        line-height: 1.08;
        color: #113330;
    }

    .location-copy {
        margin: 18px 0 0;
        max-width: 62ch;
        font-size: 1.04rem;
        line-height: 1.85;
        color: var(--location-muted);
    }

    .location-badges {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        margin-top: 26px;
    }

    .location-badge {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 12px 16px;
        border-radius: 16px;
        color: #16433e;
        background: rgba(255, 255, 255, 0.78);
        border: 1px solid rgba(13, 107, 99, 0.1);
        box-shadow: 0 12px 24px rgba(13, 107, 99, 0.06);
    }

    .location-badge strong {
        display: block;
        font-size: 0.95rem;
    }

    .location-badge span {
        display: block;
        font-size: 0.82rem;
        color: var(--location-muted);
    }

    .location-badge-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 42px;
        height: 42px;
        border-radius: 14px;
        flex-shrink: 0;
        color: #fff;
        background: linear-gradient(135deg, var(--location-primary), #1c9481);
        box-shadow: 0 12px 22px rgba(13, 107, 99, 0.18);
    }

    .location-spotlight {
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        gap: 18px;
        background:
            linear-gradient(155deg, rgba(6, 78, 74, 0.92), rgba(13, 107, 99, 0.88)),
            linear-gradient(135deg, rgba(255, 255, 255, 0.08), transparent);
        color: #f5fffb;
    }

    .location-spotlight h3 {
        margin: 0;
        font-size: 1.4rem;
        line-height: 1.4;
    }

    .location-spotlight p {
        margin: 10px 0 0;
        line-height: 1.8;
        color: rgba(245, 255, 251, 0.8);
    }

    .location-stats {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
    }

    .location-stat {
        padding: 18px;
        border-radius: 20px;
        background: rgba(255, 255, 255, 0.12);
        border: 1px solid rgba(255, 255, 255, 0.16);
    }

    .location-stat strong {
        display: block;
        font-size: 1.55rem;
        line-height: 1;
        margin-bottom: 8px;
    }

    .location-stat span {
        font-size: 0.92rem;
        color: rgba(245, 255, 251, 0.76);
    }

    .location-grid {
        display: grid;
        grid-template-columns: minmax(0, 0.95fr) minmax(0, 1.05fr);
        gap: 28px;
    }

    .location-card {
        min-height: 100%;
    }

    .location-card h3,
    .location-map-card h3 {
        margin: 0 0 22px;
        font-size: 1.55rem;
        color: #163b37;
    }

    .location-list {
        display: grid;
        gap: 16px;
    }

    .location-item {
        display: grid;
        grid-template-columns: 58px minmax(0, 1fr);
        gap: 16px;
        align-items: start;
        padding: 18px;
        border-radius: 22px;
        background: rgba(255, 255, 255, 0.68);
        border: 1px solid rgba(13, 107, 99, 0.08);
        transition: transform 0.35s ease, box-shadow 0.35s ease, border-color 0.35s ease;
    }

    .location-item:hover {
        transform: translateY(-4px);
        border-color: rgba(13, 107, 99, 0.18);
        box-shadow: 0 18px 26px rgba(13, 107, 99, 0.08);
    }

    .location-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 58px;
        height: 58px;
        border-radius: 18px;
        color: #fff;
        background: linear-gradient(135deg, #0d6b63, #25a38e);
        box-shadow: 0 12px 24px rgba(13, 107, 99, 0.18);
        transition: transform 0.35s ease;
    }

    .location-item:hover .location-icon {
        transform: rotate(-6deg) scale(1.04);
    }

    .location-item h4 {
        margin: 0 0 6px;
        font-size: 1.05rem;
        color: #17312e;
    }

    .location-item p {
        margin: 0;
        color: var(--location-muted);
        line-height: 1.72;
    }

    .location-map-card {
        display: flex;
        flex-direction: column;
        gap: 18px;
    }

    .location-map-frame {
        position: relative;
        overflow: hidden;
        min-height: 420px;
        border-radius: 26px;
        border: 1px solid rgba(13, 107, 99, 0.12);
        background: #d8ebe3;
        box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.3);
    }

    .location-map-frame::after {
        content: "";
        position: absolute;
        inset: 0;
        background: linear-gradient(to top, rgba(8, 45, 43, 0.16), transparent 38%);
        pointer-events: none;
    }

    .location-map-frame iframe {
        width: 100%;
        height: 100%;
        min-height: 420px;
        border: 0;
        filter: saturate(1.05) contrast(1.03);
    }

    .location-map-note {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 18px 20px;
        border-radius: 20px;
        background: linear-gradient(135deg, rgba(13, 107, 99, 0.08), rgba(245, 185, 113, 0.14));
        color: #1c4741;
    }

    .location-map-note p {
        margin: 0;
        line-height: 1.7;
    }

    .location-map-link {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 12px 18px;
        border-radius: 999px;
        font-weight: 600;
        color: #fff;
        text-decoration: none;
        white-space: nowrap;
        background: linear-gradient(135deg, var(--location-primary), #1a8779);
        box-shadow: 0 12px 24px rgba(13, 107, 99, 0.18);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .location-map-link:hover {
        transform: translateY(-2px);
        box-shadow: 0 16px 28px rgba(13, 107, 99, 0.22);
        color: #fff;
    }

    .location-reveal {
        opacity: 0;
        transform: translateY(28px);
        transition: opacity 0.75s ease, transform 0.75s ease;
    }

    .location-reveal.is-visible {
        opacity: 1;
        transform: translateY(0);
    }

    .location-delay-1 {
        transition-delay: 0.08s;
    }

    .location-delay-2 {
        transition-delay: 0.16s;
    }

    .location-delay-3 {
        transition-delay: 0.24s;
    }

    @keyframes locationFloat {
        0%, 100% {
            transform: translate3d(0, 0, 0);
        }

        50% {
            transform: translate3d(0, -18px, 0);
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .location-showcase::before,
        .location-showcase::after,
        .location-reveal,
        .location-item,
        .location-icon,
        .location-map-link {
            animation: none !important;
            transition: none !important;
        }

        .location-reveal {
            opacity: 1;
            transform: none;
        }
    }

    @media (max-width: 991.98px) {
        .location-showcase {
            padding: 78px 0;
            border-radius: 28px;
        }

        .location-hero,
        .location-grid {
            grid-template-columns: 1fr;
        }

        .location-panel {
            padding: 28px;
        }
    }

    @media (max-width: 767.98px) {
        .location-showcase {
            width: calc(100% - 12px);
            padding: 64px 0;
            margin: 24px auto 36px;
            border-radius: 24px;
        }

        .location-shell {
            width: min(100%, calc(100% - 20px));
        }

        .location-panel {
            padding: 22px;
            border-radius: 22px;
        }

        .location-stats {
            grid-template-columns: 1fr;
        }

        .location-item {
            grid-template-columns: 1fr;
        }

        .location-map-frame,
        .location-map-frame iframe {
            min-height: 320px;
        }

        .location-map-note {
            flex-direction: column;
            align-items: flex-start;
        }
    }
</style>

<section class="location-showcase" id="location">
    <div class="location-shell">
        <div class="location-hero">
            <div class="location-panel location-reveal is-visible">
                <span class="location-kicker">ศูนย์เรียนรู้และหน้าร้าน</span>
                <h2 class="location-heading">แวะมาหาเราได้ง่าย ทั้งซื้อสินค้า รับหน้าร้าน และสอบถามข้อมูล</h2>
                <p class="location-copy">
                    สวนลุงเผือกพร้อมต้อนรับทั้งลูกค้าที่อยากแวะมาซื้อผลไม้ตามฤดูกาล สินค้าแปรรูป
                    หรือสอบถามเรื่องการสั่งซื้อและบริการจัดส่งในพื้นที่บ้านบุฮม เราออกแบบจุดนี้ให้ค้นหาข้อมูลสำคัญได้เร็วและดูสบายตาขึ้น
                </p>

                <div class="location-badges">
                    <div class="location-badge">
                        
                        <div>
                            <strong>รับหน้าร้านได้</strong>
                            <span>เดินทางตามแผนที่ได้ทันที</span>
                        </div>
                    </div>

                    <div class="location-badge">
                        
                        <div>
                            <strong>นัดรับสะดวก</strong>
                            <span>โทรสอบถามก่อนเข้ามาได้</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="location-panel location-spotlight location-reveal location-delay-1">
                <div>
                    <h3>เส้นทางชัดเจน บรรยากาศเป็นกันเอง และมีบริการสำหรับลูกค้าในพื้นที่</h3>
                    <p>เหมาะทั้งสำหรับลูกค้าที่ต้องการแวะซื้อเองและผู้ที่อยากติดต่อสอบถามเรื่องการสั่งจองล่วงหน้า</p>
                </div>

                <div class="location-stats">
                    <div class="location-stat">
                        <strong>2</strong>
                        <span>หมายเลขโทรศัพท์สำหรับติดต่อ</span>
                    </div>
                    <div class="location-stat">
                        <strong>1</strong>
                        <span>จุดหมายเดียวสำหรับรับสินค้าและสอบถามข้อมูล</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="location-grid">
            <div class="location-panel location-card location-reveal location-delay-1">
                <h3>ข้อมูลการติดต่อ</h3>
                <div class="location-list">
                    <article class="location-item">
                        <span class="location-icon" aria-hidden="true">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                                <path d="M12 21s7-5.686 7-11a7 7 0 1 0-14 0c0 5.314 7 11 7 11Z" stroke="currentColor" stroke-width="1.8"/>
                                <circle cx="12" cy="10" r="2.5" stroke="currentColor" stroke-width="1.8"/>
                            </svg>
                        </span>
                        <div>
                            <h4>ที่อยู่ร้าน</h4>
                            <p>ร้านอิ่มเลย (สวนลุงเผือก) 26/4 ตำบลบุฮม อำเภอเชียงคาน จังหวัดเลย 42110</p>
                        </div>
                    </article>

                    <article class="location-item">
                        <span class="location-icon" aria-hidden="true">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                                <path d="M5 5h3l2 5-1.5 1.5a15.4 15.4 0 0 0 4 4L14 14l5 2v3a2 2 0 0 1-2 2C10.925 21 3 13.075 3 7a2 2 0 0 1 2-2Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                            </svg>
                        </span>
                        <div>
                            <h4>เบอร์โทรศัพท์</h4>
                            <p>062-197-0420 และ 089-898-0821</p>
                        </div>
                    </article>

                    <article class="location-item">
                        <span class="location-icon" aria-hidden="true">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                                <path d="M4 7.5 12 13l8-5.5M5 19h14a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                            </svg>
                        </span>
                        <div>
                            <h4>อีเมล</h4>
                            <p>contact@siamcafe.com</p>
                        </div>
                    </article>

                    <article class="location-item">
                        <span class="location-icon" aria-hidden="true">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                                <path d="M12 3c4.97 0 9 3.582 9 8 0 1.6-.53 3.09-1.44 4.33L21 21l-5.1-1.6A10.1 10.1 0 0 1 12 20c-4.97 0-9-3.582-9-8s4.03-9 9-9Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                                <path d="M8.5 12h.01M12 12h.01M15.5 12h.01" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/>
                            </svg>
                        </span>
                        <div>
                            <h4>บริการ</h4>
                            <p>มีที่จอดรถ รับจองล่วงหน้า และมีบริการจัดส่งสินค้าเฉพาะในพื้นที่</p>
                        </div>
                    </article>
                </div>
            </div>

            <div class="location-panel location-map-card location-reveal location-delay-2">
                <h3>แผนที่และเส้นทาง</h3>
                <div class="location-map-frame">
                    <iframe
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d583.2269561143113!2d101.73779659421837!3d17.93598673381639!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3126b932695c11e9%3A0x663ac49e3dd2b5bc!2z4Lij4LmJ4Liy4LiZ4Lit4Li04LmI4Lih4LmA4Lil4LiiKOC4quC4p-C4meC4peC4uOC4h-C5gOC4nOC4t-C4reC4gSk!5e1!3m2!1sth!2sth!4v1769708605758!5m2!1sth!2sth"
                        loading="lazy"
                        allowfullscreen=""
                        referrerpolicy="no-referrer-when-downgrade"
                        title="แผนที่ร้านอิ่มเลย สวนลุงเผือก">
                    </iframe>
                </div>

                <div class="location-map-note">
                    <p>หากต้องการนำทางทันที สามารถเปิดแผนที่แบบเต็มหน้าจอเพื่อดูเส้นทางจากตำแหน่งปัจจุบันได้</p>
                    <a
                        class="location-map-link"
                        href="https://www.google.com/maps?q=17.93598673381639,101.73779659421837"
                        target="_blank"
                        rel="noopener noreferrer">
                        เปิด Google Maps
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const revealItems = document.querySelectorAll('.location-showcase .location-reveal');

        if (!('IntersectionObserver' in window)) {
            revealItems.forEach(function (item) {
                item.classList.add('is-visible');
            });
            return;
        }

        const revealObserver = new IntersectionObserver(function (entries, observer) {
            entries.forEach(function (entry) {
                if (!entry.isIntersecting) {
                    return;
                }

                entry.target.classList.add('is-visible');
                observer.unobserve(entry.target);
            });
        }, {
            threshold: 0.18,
            rootMargin: '0px 0px -40px 0px'
        });

        revealItems.forEach(function (item) {
            revealObserver.observe(item);
        });
    });
</script>
