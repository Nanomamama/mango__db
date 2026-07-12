<a href="https://m.me/thrng.phl.phat.hn.chiy"
    target="_blank"
    rel="noopener noreferrer"
    class="fb-icon"
    id="messenger-icon"
    aria-label="แชทผ่าน Messenger">

    <img
        src="https://upload.wikimedia.org/wikipedia/commons/b/be/Facebook_Messenger_logo_2020.svg"
        alt="Messenger"
        loading="lazy">

</a>

<style>
    .fb-icon {

        /* ซ่อนไว้ก่อน */
        visibility: hidden;
        opacity: 0;

        --messenger-size: clamp(52px, 6vw, 65px);
        --messenger-icon-size: calc(var(--messenger-size) * .5);

        position: fixed;
        right: max(15px, env(safe-area-inset-right));
        bottom: calc(20px + env(safe-area-inset-bottom));

        width: var(--messenger-size);
        height: var(--messenger-size);

        background: #fff;
        border-radius: 50%;

        display: flex;
        justify-content: center;
        align-items: center;

        text-decoration: none;

        box-shadow: 0 4px 12px rgba(0,0,0,.28);

        z-index: 1040;

        touch-action: manipulation;

        transform: translateY(10px);

        transition:
            opacity .25s ease,
            visibility .25s ease,
            transform .25s ease,
            box-shadow .2s ease;
    }

    /* แสดงหลังโหลดเสร็จ */
    .fb-icon.show{
        visibility: visible;
        opacity: 1;
        transform: translateY(0);
    }

    .fb-icon:hover,
    .fb-icon:focus-visible{

        transform: translateY(-2px);

        box-shadow:
            0 8px 20px rgba(0,0,0,.22);
    }

    .fb-icon:focus-visible{

        outline:3px solid rgba(0,132,255,.35);

        outline-offset:4px;
    }

    .fb-icon img{

        width:var(--messenger-icon-size);

        height:var(--messenger-icon-size);

        display:block;

        user-select:none;

        pointer-events:none;
    }

    @keyframes messenger-shake{

        0%{transform:translateX(0)}

        20%{transform:translateX(-3px)}

        40%{transform:translateX(3px)}

        60%{transform:translateX(-3px)}

        80%{transform:translateX(3px)}

        100%{transform:translateX(0)}

    }

    .fb-icon.is-shaking{

        animation: messenger-shake .5s ease-in-out;

    }

    @media (max-width:768px){

        .fb-icon{

            --messenger-size:54px;

            right:max(14px, env(safe-area-inset-right));

            bottom:calc(18px + env(safe-area-inset-bottom));
        }

    }

    @media (max-width:420px){

        .fb-icon{

            --messenger-size:48px;

            right:max(18px, env(safe-area-inset-right));

            bottom:calc(35px + env(safe-area-inset-bottom));
        }

    }

    @media (prefers-reduced-motion:reduce){

        .fb-icon{

            transition:none;

        }

        .fb-icon.is-shaking{

            animation:none;

        }

    }
</style>

<script>
(() => {

    const icon = document.getElementById("messenger-icon");

    if (!icon) return;

    const reduceMotion = window.matchMedia("(prefers-reduced-motion: reduce)");

    function showIcon() {
        requestAnimationFrame(() => {
            icon.classList.add("show");
        });
    }

    function triggerShake() {

        if (reduceMotion.matches) return;

        icon.classList.remove("is-shaking");

        void icon.offsetWidth;

        icon.classList.add("is-shaking");
    }

    icon.addEventListener("animationend", () => {

        icon.classList.remove("is-shaking");

    });

    window.addEventListener("load", () => {

        showIcon();

        triggerShake();

    }, { once: true });

    if (!reduceMotion.matches) {

        setInterval(triggerShake, 5000);

    }

})();
</script>