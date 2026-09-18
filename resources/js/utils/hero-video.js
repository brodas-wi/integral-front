export function initHeroVideoMuteButtons() {
    document.querySelectorAll("[data-hv-mute-btn]").forEach((btn) => {
        if (btn.__hvInit) return;
        btn.__hvInit = true;

        const videoId = btn.dataset.hvTarget;
        const video = videoId ? document.getElementById(videoId) : null;
        if (!video) return;

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
            if (!video.muted) {
                video.play().catch(() => { });
            }
            syncIcon();
        });

        video.addEventListener("volumechange", syncIcon);
        syncIcon();
    });
}