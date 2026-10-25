(() => {
    const body = document.body;
    const themeButton = document.querySelector('.theme-toggle');
    function applyTheme(theme) {
        body.dataset.theme = theme;
        themeButton?.setAttribute('aria-label', theme === 'dark' ? '切换浅色模式' : '切换深色模式');
        document.querySelector('meta[name="theme-color"]')?.setAttribute('content', theme === 'dark' ? '#172321' : '#f3f8f5');
    }
    try { applyTheme(localStorage.getItem('blog-theme') || 'light'); } catch (_) { applyTheme('light'); }
    themeButton?.addEventListener('click', () => {
        const theme = body.dataset.theme === 'dark' ? 'light' : 'dark';
        applyTheme(theme);
        try { localStorage.setItem('blog-theme', theme); } catch (_) {}
    });
    const menu = document.querySelector('.menu-toggle');
    const navigation = document.querySelector('.site-navigation');
    function closeMenu() { navigation?.classList.remove('is-open'); menu?.setAttribute('aria-expanded', 'false'); }
    menu?.addEventListener('click', () => {
        const open = navigation.classList.toggle('is-open');
        menu.setAttribute('aria-expanded', String(open));
        menu.setAttribute('aria-label', open ? '收起导航' : '展开导航');
    });
    navigation?.addEventListener('click', event => { if (event.target.closest('a')) closeMenu(); });
    document.addEventListener('keydown', event => {
        if (event.key === 'Escape') {
            closeMenu();
            const modal = document.getElementById('city-picker-modal');
            if (modal && !modal.hidden) { modal.hidden = true; document.getElementById('change-city')?.focus(); }
        }
    });
    // Markdown is rendered by markdown.js before this deferred script runs.
    const toc = document.querySelector('.article-toc');
    if (toc) {
        document.querySelectorAll('.post-content h2, .post-content h3').forEach((heading, index) => {
            heading.id = `section-${index + 1}`;
            const link = document.createElement('a');
            link.href = `#${heading.id}`;
            link.textContent = heading.textContent;
            if (heading.tagName === 'H3') link.className = 'toc-sub';
            toc.appendChild(link);
        });
        if (!toc.childElementCount) toc.textContent = '享受这一刻的阅读。';
    }
})();
