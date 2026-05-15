<a href="https://m.me/thrng.phl.phat.hn.chiy"
    target="_blank"
    rel="noopener noreferrer"
    class="fb-icon"
    id="messenger-icon"
    aria-label="แชทผ่าน Messenger">
    <img src="https://upload.wikimedia.org/wikipedia/commons/b/be/Facebook_Messenger_logo_2020.svg" alt="" loading="lazy">
</a>

<style>
    .fb-icon {
        --messenger-size: clamp(52px, 6vw, 65px);
        --messenger-icon-size: calc(var(--messenger-size) * 0.5);
        position: fixed;
        right: max(15px, env(safe-area-inset-right));
        bottom: calc(20px + env(safe-area-inset-bottom));
        width: var(--messenger-size);
        height: var(--messenger-size);
        background: #ffffff;
        border-radius: 50%;
        display: flex;
        justify-content: center;
        align-items: center;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
        z-index: 1040;
        text-decoration: none;
        touch-action: manipulation;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .fb-icon:hover,
    .fb-icon:focus-visible {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.24);
    }

    .fb-icon:focus-visible {
        outline: 3px solid rgba(0, 132, 255, 0.35);
        outline-offset: 4px;
    }

    .fb-icon img {
        width: var(--messenger-icon-size);
        height: var(--messenger-icon-size);
        display: block;
    }

    @keyframes messenger-shake {
        0% {
            transform: translateX(0);
        }

        25% {
            transform: translateX(-3px);
        }

        50% {
            transform: translateX(3px);
        }

        75% {
            transform: translateX(-3px);
        }

        100% {
            transform: translateX(0);
        }
    }

    .fb-icon.is-shaking {
        animation: messenger-shake 0.5s ease-in-out;
    }

    @media (max-width: 768px) {
        .fb-icon {
            --messenger-size: 54px;
            right: max(14px, env(safe-area-inset-right));
            bottom: calc(18px + env(safe-area-inset-bottom));
        }
    }

    @media (max-width: 420px) {
        .fb-icon {
            --messenger-size: 48px;
            right: max(20px, env(safe-area-inset-right));
            bottom: calc(35px + env(safe-area-inset-bottom));
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .fb-icon {
            transition: none;
        }

        .fb-icon.is-shaking {
            animation: none;
        }
    }
</style>

<script>
    (() => {
        const icon = document.getElementById('messenger-icon');
        if (!icon || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            return;
        }

        const triggerShake = () => {
            icon.classList.remove('is-shaking');
            void icon.offsetWidth;
            icon.classList.add('is-shaking');
        };

        icon.addEventListener('animationend', () => {
            icon.classList.remove('is-shaking');
        });

        window.addEventListener('load', triggerShake, {
            once: true
        });
        window.setInterval(triggerShake, 5000);
    })();
</script>
