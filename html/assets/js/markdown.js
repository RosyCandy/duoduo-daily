document.querySelectorAll('[data-markdown-content]').forEach((element) => {
    const source = element.textContent.trim();
    if (window.marked && window.DOMPurify) {
        element.innerHTML = DOMPurify.sanitize(marked.parse(source, { breaks: true }));
    }
});
