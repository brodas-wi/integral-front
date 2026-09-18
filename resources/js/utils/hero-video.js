export function initHeroVideoMuteButtons() {
    document.querySelectorAll("[data-hv-mute-btn]").forEach((btn) => {
        if (btn.__hvInit) return;
        btn.__hvInit = true;

        const videoId = btn.dataset.hvTarget;
        const video = videoId ? document.getElementById(videoId) : null;
        if (!video) return;

        video.controls = false;
        video.removeAttribute("controls");
        video.setAttribute("controlsList", "nodownload noplaybackrate");

        const forceNoControls = new MutationObserver(() => {
            if (video.hasAttribute("controls")) {
                video.removeAttribute("controls");
            }
        });
        forceNoControls.observe(video, {
            attributes: true,
            attributeFilter: ["controls"],
        });

        const syncIcon = () => {
            const muted = video.muted;
            btn.dataset.muted = muted ? "true" : "false";
            btn.setAttribute(
                "aria-label",
                muted ? "Activar sonido" : "Silenciar video",
            );
            btn.innerHTML = muted
                ? '<i class="ri-volume-mute-line"></i>'
                : '<i class="ri-volume-up-line"></i>';
        };

        btn.addEventListener("click", () => {
            video.muted = !video.muted;
            video.controls = false;
            video.removeAttribute("controls");
            if (!video.muted) {
                video.play().catch(() => { });
            }
            syncIcon();
        });

        video.addEventListener("volumechange", () => {
            video.controls = false;
            video.removeAttribute("controls");
            syncIcon();
        });
        syncIcon();
    });
}