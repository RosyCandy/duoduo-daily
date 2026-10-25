(() => {
    // Based on RosyCandy.github.io/_layouts/tags.html: independent drift,
    // faint proximity links at 170px, and pointer links at 210px. No attraction.
    const canvas = document.querySelector('canvas[data-tag-garden]');
    const data = document.getElementById('tag-garden-data');
    if (!canvas || !data) return;
    const tags = JSON.parse(data.textContent);
    if (!tags.length) return;
    const ctx = canvas.getContext('2d');
    if (!ctx) return;
    const height = 420;
    const linkDistance = 170;
    const pointerDistance = linkDistance + 40;
    const reducedMotion = matchMedia('(prefers-reduced-motion: reduce)');
    const pauseButton = document.querySelector('[data-tag-pause]');
    const status = document.querySelector('.constellation-toolbar>span');
    let width = 0, frame = 0, previous = 0;
    let visible = false, paused = reducedMotion.matches;
    let colors = {}, dragging = null, didDrag = false;
    const mouse = { x: -10000, y: -10000 };
    ctx.font = '13px system-ui, sans-serif';
    const nodes = tags.map(tag => ({
        ...tag, x: .1 + Math.random() * .8, y: .15 + Math.random() * .7,
        vx: (Math.random() - .5) * .0018, vy: (Math.random() - .5) * .0018,
        radius: Math.max(26, ctx.measureText(tag.name).width / 2 + 14)
    }));
    function updateColors() {
        const css = getComputedStyle(document.body);
        colors = { text: css.getPropertyValue('--ink').trim(), accent: css.getPropertyValue('--green').trim(), dark: document.body.dataset.theme === 'dark' };
    }
    function resize() {
        width = canvas.parentElement.clientWidth;
        const dpr = Math.min(window.devicePixelRatio || 1, 2);
        canvas.width = Math.round(width * dpr);
        canvas.height = Math.round(height * dpr);
        canvas.style.height = height + 'px';
        ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
        paint();
    }
    function stroke(x1, y1, x2, y2, opacity) {
        ctx.strokeStyle = `rgba(92,139,116,${opacity})`;
        ctx.beginPath(); ctx.moveTo(x1, y1); ctx.lineTo(x2, y2); ctx.stroke();
    }
    function paint() {
        if (!width) return;
        ctx.clearRect(0, 0, width, height);
        ctx.lineWidth = 1;
        for (let i = 0; i < nodes.length; i++) {
            const a = nodes[i];
            for (let j = i + 1; j < nodes.length; j++) {
                const b = nodes[j];
                const distance = Math.hypot((a.x - b.x) * width, (a.y - b.y) * height);
                if (distance < linkDistance) stroke(a.x * width, a.y * height, b.x * width, b.y * height, .55 * (1 - distance / linkDistance));
            }
            const distance = Math.hypot(a.x * width - mouse.x, a.y * height - mouse.y);
            if (distance < pointerDistance) stroke(a.x * width, a.y * height, mouse.x, mouse.y, .45 * (1 - distance / pointerDistance));
        }
        let hovered = false;
        ctx.font = '13px system-ui, sans-serif';
        ctx.textAlign = 'center'; ctx.textBaseline = 'middle';
        nodes.forEach(node => {
            const x = node.x * width, y = node.y * height;
            const near = Math.hypot(x - mouse.x, y - mouse.y) < node.radius + 6;
            hovered ||= near;
            ctx.beginPath(); ctx.arc(x, y, node.radius, 0, Math.PI * 2);
            ctx.fillStyle = near ? 'rgba(90,140,115,.2)' : (colors.dark ? 'rgba(110,170,140,.15)' : 'rgba(90,140,115,.1)');
            ctx.fill(); ctx.lineWidth = near ? 2 : 1.5; ctx.strokeStyle = colors.accent; ctx.stroke();
            ctx.fillStyle = colors.text; ctx.fillText(node.name, x, y);
        });
        canvas.style.cursor = dragging ? 'grabbing' : (hovered ? 'pointer' : 'default');
    }
    function step(timestamp) {
        frame = 0;
        if (paused || !visible || document.hidden) { previous = 0; paint(); return; }
        const delta = previous ? Math.min((timestamp - previous) / 16.67, 2) : 1;
        previous = timestamp;
        nodes.forEach(node => {
            if (node === dragging) return;
            node.x += node.vx * delta; node.y += node.vy * delta;
            const marginX = Math.max(.08, (node.radius + 8) / width);
            const marginY = Math.max(.12, (node.radius + 8) / height);
            if (node.x < marginX || node.x > 1 - marginX) { node.x = Math.max(marginX, Math.min(1 - marginX, node.x)); node.vx *= -1; }
            if (node.y < marginY || node.y > 1 - marginY) { node.y = Math.max(marginY, Math.min(1 - marginY, node.y)); node.vy *= -1; }
        });
        paint(); frame = requestAnimationFrame(step);
    }
    function schedule() {
        if (!frame && !paused && visible && !document.hidden) frame = requestAnimationFrame(step);
        else if (paused) paint();
    }
    function updateControl() {
        pauseButton.setAttribute('aria-pressed', String(paused));
        pauseButton.textContent = paused ? '继续漫游' : '暂停漫游';
        status.lastChild.textContent = paused ? ' 标签暂歇中' : ' 标签正在漫游';
    }
    function point(event) {
        const rect = canvas.getBoundingClientRect();
        return { x: (event.clientX - rect.left) * width / rect.width, y: (event.clientY - rect.top) * height / rect.height };
    }
    function hit(pos) { return nodes.find(node => Math.hypot(node.x * width - pos.x, node.y * height - pos.y) < node.radius + 6); }
    canvas.addEventListener('pointermove', event => {
        const pos = point(event); Object.assign(mouse, pos);
        if (dragging) {
            didDrag ||= Math.hypot(pos.x - dragging.x * width, pos.y - dragging.y * height) > 3;
            dragging.x = Math.max(dragging.radius + 8, Math.min(width - dragging.radius - 8, pos.x)) / width;
            dragging.y = Math.max(dragging.radius + 8, Math.min(height - dragging.radius - 8, pos.y)) / height;
        }
        if (paused) paint();
    });
    canvas.addEventListener('pointerleave', () => { if (!dragging) { mouse.x = mouse.y = -10000; if (paused) paint(); } });
    canvas.addEventListener('pointerdown', event => {
        if (event.button !== 0) return;
        const pos = point(event); dragging = hit(pos); didDrag = false;
        if (dragging) canvas.setPointerCapture(event.pointerId);
    });
    canvas.addEventListener('pointerup', () => { dragging = null; if (paused) paint(); });
    canvas.addEventListener('pointercancel', () => { dragging = null; didDrag = true; mouse.x = mouse.y = -10000; paint(); });
    canvas.addEventListener('click', event => {
        if (didDrag) { didDrag = false; return; }
        const node = hit(point(event));
        if (node) window.location.href = node.url;
    });
    pauseButton.addEventListener('click', () => { paused = !paused; updateControl(); schedule(); });
    reducedMotion.addEventListener('change', event => { paused = event.matches; updateControl(); schedule(); });
    document.addEventListener('visibilitychange', schedule);
    new ResizeObserver(resize).observe(canvas.parentElement);
    new MutationObserver(() => { updateColors(); paint(); }).observe(document.body, {attributes:true, attributeFilter:['data-theme']});
    new IntersectionObserver(entries => { visible = entries[0].isIntersecting; schedule(); }, {threshold:.05}).observe(canvas);
    updateColors(); resize(); updateControl(); schedule();
})();
