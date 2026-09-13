function renderMarkdown(source) {
    if (!window.marked || !window.DOMPurify) {
        return '<p>预览组件加载失败，请检查网络连接。</p>';
    }
    return DOMPurify.sanitize(marked.parse(source, { breaks: true }));
}

function toggleMarkdownPreview(button) {
    const editor = button.closest('.form-group').querySelector('.markdown-editor');
    const preview = button.closest('.form-group').querySelector('.markdown-preview');
    const showing = !preview.hidden;
    preview.hidden = showing;
    editor.hidden = !showing;
    button.textContent = showing ? '预览' : '编辑';
    if (!showing) preview.innerHTML = renderMarkdown(editor.value);
}

function wrapSelection(prefix, suffix, placeholder) {
    const editor = document.querySelector('.markdown-editor:not([hidden])');
    if (!editor) return;

    const start = editor.selectionStart;
    const end = editor.selectionEnd;
    const selected = editor.value.slice(start, end) || placeholder;
    const wrapped = `${prefix}${selected}${suffix}`;

    editor.value = editor.value.slice(0, start) + wrapped + editor.value.slice(end);
    editor.focus();
    editor.selectionStart = start + prefix.length;
    editor.selectionEnd = start + prefix.length + selected.length;
}

function insertContentImage(input, centered = false) {
    const file = input.files[0];
    const editor = input.closest('.form-group').querySelector('.markdown-editor');
    if (!file || !editor) return;

    const form = new FormData();
    form.append('image', file);
    form.append('type', 'post');
    input.disabled = true;
    fetch('upload_image.php', { method: 'POST', body: form })
        .then((response) => response.json())
        .then((data) => {
            if (!data.url) throw new Error(data.error || '上传失败');
            const alt = file.name.replace(/\.[^.]+$/, '').replace(/[\[\]()*_`]/g, '');
            const src = data.url.startsWith('/') ? data.url : `/${data.url}`;
            const content = centered
                ? `<div align="center"><img src="${src}" alt="${alt}" style="max-width: 100%; border-radius: 10px; display: block; margin: 12px auto;" /></div>\n`
                : `![${alt}](${src})`;
            const start = editor.selectionStart;
            editor.value = editor.value.slice(0, start) + content + editor.value.slice(editor.selectionEnd);
            editor.focus();
            editor.selectionStart = editor.selectionEnd = start + content.length;
        })
        .catch((error) => window.alert(error.message))
        .finally(() => {
            input.disabled = false;
            input.value = '';
        });
}
