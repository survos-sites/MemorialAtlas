import { Controller } from '@hotwired/stimulus';

/*
 * Posts the question to /chat/stream and shows the tool calls and the answer while they arrive
 * (server-sent events over fetch, since EventSource cannot POST). The "done" event replaces the
 * streamed text with the rendered turn. Without JavaScript the form posts to /chat as before.
 */
export default class extends Controller {
    static targets = ['log', 'input', 'button'];
    static values = { url: String };

    async ask(event) {
        event.preventDefault();
        const question = this.inputTarget.value.trim();
        if (!question) return;
        this.inputTarget.value = '';
        this.buttonTarget.disabled = true;

        const turn = document.createElement('div');
        turn.style.display = 'contents';
        const q = document.createElement('div');
        q.className = 'chat-q';
        q.textContent = question;
        const tools = document.createElement('div');
        tools.className = 'chat-tools';
        const answer = document.createElement('div');
        answer.className = 'chat-a streaming';
        turn.append(q, tools, answer);
        this.logTarget.append(turn);
        q.scrollIntoView({ behavior: 'smooth', block: 'start' });

        try {
            const response = await fetch(this.urlValue, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({ question }),
            });
            if (!response.ok) throw new Error(`HTTP ${response.status}`);
            const reader = response.body.pipeThrough(new TextDecoderStream()).getReader();
            let buffer = '';
            for (;;) {
                const { value, done } = await reader.read();
                if (done) break;
                buffer += value;
                let end;
                while ((end = buffer.indexOf('\n\n')) !== -1) {
                    this.#handle(buffer.slice(0, end), turn, tools, answer);
                    buffer = buffer.slice(end + 2);
                }
            }
        } catch (error) {
            answer.textContent = `The assistant could not answer: ${error.message}`;
        } finally {
            this.buttonTarget.disabled = false;
            this.inputTarget.focus();
        }
    }

    #handle(block, turn, tools, answer) {
        const event = block.match(/^event: (.*)$/m)?.[1];
        const data = JSON.parse(block.match(/^data: (.*)$/m)?.[1] ?? 'null');
        if (event === 'tool') {
            const chip = document.createElement('span');
            chip.className = 'chat-tool';
            chip.textContent = `${data.name}(${Object.values(data.arguments).map((v) => JSON.stringify(v)).join(', ')})`;
            tools.append(chip);
            answer.textContent = '';
        } else if (event === 'delta') {
            answer.textContent += data;
        } else if (event === 'done') {
            turn.innerHTML = data.html;
        }
    }
}
