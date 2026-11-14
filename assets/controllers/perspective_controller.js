import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['image', 'polygon', 'coordinate', 'status', 'submit', 'handle'];
    static values = { url: String, token: String, corners: Array };
    connect() {
        this.nextCorner = 0;
        if (this.cornersValue.length === 4) {
            this.cornersValue.flat().forEach((value, i) => { this.coordinateTargets[i].value = value * 100; });
            this.nextCorner = 4;
            this.statusTarget.textContent = 'Saved corners loaded. Drag a point or edit its coordinates.';
            this.draw();
        }
    }
    start(event) {
        event.preventDefault();
        this.dragIndex = Number(event.currentTarget.dataset.index);
        event.currentTarget.setPointerCapture(event.pointerId);
    }
    move(event) {
        if (this.dragIndex == null) return;
        const rect = this.imageTarget.getBoundingClientRect();
        [(event.clientX - rect.left) / rect.width, (event.clientY - rect.top) / rect.height].forEach((value, axis) => {
            this.coordinateTargets[this.dragIndex * 2 + axis].value = (Math.max(0, Math.min(1, value)) * 100).toFixed(2);
        });
        this.draw();
    }
    stop() { this.dragIndex = null; }
    nudge(event) {
        const delta = {ArrowLeft: [-1,0], ArrowRight: [1,0], ArrowUp: [0,-1], ArrowDown: [0,1]}[event.key];
        if (!delta) return;
        event.preventDefault();
        const index = Number(event.currentTarget.dataset.index);
        delta.forEach((value, axis) => {
            const input = this.coordinateTargets[index * 2 + axis];
            input.value = Math.max(0, Math.min(100, Number(input.value) + value * (event.shiftKey ? 1 : 0.1))).toFixed(2);
        });
        this.draw();
    }
    select(event) {
        if (this.nextCorner >= 4) return;
        const rect = this.imageTarget.getBoundingClientRect();
        const x = (event.clientX - rect.left) / rect.width;
        const y = (event.clientY - rect.top) / rect.height;
        if (x < 0 || y < 0 || x > 1 || y > 1) return;
        this.coordinateTargets[this.nextCorner * 2].value = (x * 100).toFixed(2);
        this.coordinateTargets[this.nextCorner * 2 + 1].value = (y * 100).toFixed(2);
        this.nextCorner++;
        this.draw();
        this.statusTarget.textContent = ['Select top-left.', 'Select top-right.', 'Select bottom-right.', 'Select bottom-left.', 'Four corners selected. Ready to correct.'][this.nextCorner];
    }
    points() {
        return [0, 1, 2, 3].map(i => this.coordinateTargets.slice(i * 2, i * 2 + 2).map(input => input.value === '' ? NaN : Number(input.value) / 100));
    }
    draw() {
        this.points().forEach(([x, y], i) => {
            const handle = this.handleTargets[i];
            handle.hidden = !Number.isFinite(x) || !Number.isFinite(y);
            handle.style.left = `${x * 100}%`;
            handle.style.top = `${y * 100}%`;
        });
        this.polygonTarget.setAttribute('points', this.points().filter(p => p.every(Number.isFinite)).map(([x, y]) => `${x * 1000},${y * 1000}`).join(' '));
    }
    reset() {
        this.coordinateTargets.forEach(input => { input.value = ''; });
        this.nextCorner = 0;
        this.draw();
        this.statusTarget.textContent = 'Select top-left.';
    }
    async save() {
        const corners = this.points();
        if (!corners.flat().every(n => Number.isFinite(n) && n >= 0 && n <= 1)) {
            this.statusTarget.textContent = 'Select all four corners inside the photograph.';
            return;
        }
        this.submitTarget.disabled = true;
        this.statusTarget.textContent = 'Submitting correction…';
        try {
            const response = await fetch(this.urlValue, {method: 'POST', headers: {'Content-Type': 'application/json', 'Accept': 'application/json'}, body: JSON.stringify({corners, token: this.tokenValue})});
            const data = await response.json();
            if (!response.ok) throw new Error(data.error || data.detail || 'Could not queue correction.');
            window.location.assign(data.url);
        } catch (error) {
            this.statusTarget.textContent = error.message;
            this.submitTarget.disabled = false;
        }
    }
}
