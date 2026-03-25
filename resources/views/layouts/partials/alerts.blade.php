<script id="ui-feedback-payload" type="application/json">{{ $uiFeedbackJson ?? 'null' }}</script>

<script>
(() => {
    const payloadElement = document.getElementById('ui-feedback-payload');

    if (!payloadElement) {
        return;
    }

    const rawPayload = (payloadElement.textContent || 'null').trim();

    if (rawPayload === '' || rawPayload === 'null') {
        return;
    }

    let feedback = null;

    try {
        feedback = JSON.parse(rawPayload);
    } catch (_error) {
        return;
    }

    if (!feedback || !feedback.type) {
        return;
    }

    const supportWhatsappUrl = 'https://wa.me/628xxxxxxxxxx?text=Halo%20saya%20butuh%20bantuan';

    const ensureSweetAlertStyle = () => {
        if (document.querySelector('link[data-swal2-style="1"]')) {
            return;
        }

        const link = document.createElement('link');
        link.rel = 'stylesheet';
        link.href = 'https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css';
        link.setAttribute('data-swal2-style', '1');
        document.head.appendChild(link);
    };

    const loadSweetAlertScript = () => new Promise((resolve, reject) => {
        if (window.Swal) {
            resolve(window.Swal);
            return;
        }

        const existingScript = document.querySelector('script[data-swal2-script="1"]');

        if (existingScript) {
            existingScript.addEventListener('load', () => resolve(window.Swal), { once: true });
            existingScript.addEventListener('error', reject, { once: true });
            return;
        }

        const script = document.createElement('script');
        script.src = 'https://cdn.jsdelivr.net/npm/sweetalert2@11';
        script.setAttribute('data-swal2-script', '1');
        script.onload = () => resolve(window.Swal);
        script.onerror = reject;
        document.body.appendChild(script);
    });

    const escapeHtml = (value) => String(value)
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');

    const listHtml = Array.isArray(feedback.messages) && feedback.messages.length > 0
        ? `<ul class="text-start ps-3 mb-0">${feedback.messages.map((message) => `<li>${escapeHtml(message)}</li>`).join('')}</ul>`
        : '';

    const singleMessage = typeof feedback.message === 'string' ? feedback.message : '';

    ensureSweetAlertStyle();

    loadSweetAlertScript()
        .then((Swal) => {
            if (feedback.type === 'error') {
                Swal.fire({
                    icon: 'error',
                    title: feedback.title || 'Terjadi Kesalahan',
                    html: listHtml !== '' ? listHtml : `<div>${escapeHtml(singleMessage)}</div>`,
                    confirmButtonText: 'Tutup',
                    footer: supportWhatsappUrl === 'https://wa.me/628xxxxxxxxxx?text=Halo%20saya%20butuh%20bantuan'
                        ? ''
                        : `<a href="${supportWhatsappUrl}" target="_blank" rel="noopener noreferrer">Hubungi WhatsApp</a>`,
                });

                return;
            }

            const toastText = singleMessage !== ''
                ? singleMessage
                : (Array.isArray(feedback.messages) && feedback.messages.length > 0 ? feedback.messages[0] : '');

            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: feedback.type,
                title: feedback.title || 'Informasi',
                text: toastText,
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
            });
        })
        .catch(() => {
        });
})();
</script>
