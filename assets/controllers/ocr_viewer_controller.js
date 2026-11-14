import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['overlay', 'region', 'line'];

    connect() {
        for (const line of this.lineTargets) {
            const [tl, tr, , bl] = JSON.parse(line.dataset.box);
            const width = Math.hypot(tr[0] - tl[0], tr[1] - tl[1]);
            const height = Math.hypot(bl[0] - tl[0], bl[1] - tl[1]);
            if (width <= 0 || height <= 0) continue;
            // Preserve the detected baseline angle instead of using its axis-aligned bounds.
            line.setAttribute('transform', `matrix(${(tr[0]-tl[0])/width} ${(tr[1]-tl[1])/width} ${(bl[0]-tl[0])/height} ${(bl[1]-tl[1])/height} ${tl[0]} ${tl[1]})`);
            const rect = line.querySelector('rect');
            for (const [key, value] of Object.entries({x: 0, y: 0, width, height})) rect.setAttribute(key, value);
            const text = line.querySelector('text');
            const rows = line.dataset.text.trim().split(/\n+/).map(row => row.replace(/^#{1,6}\s+/, ''));
            text.removeAttribute('textLength');
            const rowHeight = height / rows.length;
            const context = document.createElement('canvas').getContext('2d');
            const style = getComputedStyle(text);
            context.font = `${style.fontStyle} ${style.fontWeight} 100px ${style.fontFamily}`;
            const metrics = rows.map(row => context.measureText(row));
            const inkHeight = Math.max(...metrics.map(m => m.actualBoundingBoxAscent + m.actualBoundingBoxDescent));
            const naturalWidth = Math.max(...metrics.map(m => Math.max(m.width, m.actualBoundingBoxLeft + m.actualBoundingBoxRight)));
            if (inkHeight <= 0 || naturalWidth <= 0) continue;
            // Fit visible glyphs, not the font's em square, with one uniform size per block.
            const fontSize = 100 * Math.min(rowHeight * 0.85 / inkHeight, width * 0.96 / naturalWidth);
            text.setAttribute('font-size', fontSize);
            text.replaceChildren();
            rows.forEach((row, index) => {
                const span = document.createElementNS('http://www.w3.org/2000/svg', 'tspan');
                const metric = metrics[index];
                span.setAttribute('x', width / 2);
                span.setAttribute('dominant-baseline', 'alphabetic');
                span.setAttribute('y', rowHeight * (index + 0.5) + (metric.actualBoundingBoxAscent - metric.actualBoundingBoxDescent) * fontSize / 200);
                span.textContent = row;
                text.append(span);
            });
        }
    }

    highlight(event) {
        const index = event.currentTarget.dataset.line;
        for (const target of [...this.regionTargets, ...this.lineTargets]) {
            target.classList.toggle('is-active', target.dataset.line === index);
        }
    }

    clear() {
        for (const target of [...this.regionTargets, ...this.lineTargets]) {
            target.classList.remove('is-active');
        }
    }

    select(event) {
        event.preventDefault();
        this.lineTargets.find(line => line.dataset.line === event.currentTarget.dataset.line)?.focus({ preventScroll: true });
        this.highlight(event);
    }

    toggle(event) {
        this.overlayTarget.toggleAttribute('hidden', !event.currentTarget.checked);
        this.clear();
    }
}
