import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['button', 'status', 'record'];

    async submit(event) {
        event.preventDefault();
        this.buttonTarget.disabled = true;
        this.recordTarget.hidden = true;
        this.statusTarget.textContent = 'Uploading photograph…';
        const body = new FormData(this.element);
        if (body.get('cemeteryId') === '') body.delete('cemeteryId');
        try {
            const response = await fetch(this.element.action, { method: 'POST', body, headers: { Accept: 'application/json' } });
            if (response.redirected) throw new Error('Your session expired. Reload and sign in before uploading.');
            const result = await response.json();
            if (result.url) {
                this.recordTarget.href = result.url;
                this.recordTarget.hidden = false;
            }
            if (!response.ok) throw new Error(result.error || result.detail || 'Upload rejected. Check the photograph and cemetery selection.');
            this.statusTarget.textContent = 'Photograph saved and queued for processing. Open the record to follow its progress.';
        } catch (error) {
            this.statusTarget.textContent = error.message;
        } finally {
            this.buttonTarget.disabled = false;
        }
    }
}
