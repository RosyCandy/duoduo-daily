function renderMarkdown(source) {
    if (!window.marked || !window.DOMPurify) return '<p>预览组件暂时未能加载，请稍后重试。</p>';
    return DOMPurify.sanitize(marked.parse(source, { breaks: true }));
}
function toggleMarkdownPreview(button) {
    const group = button.closest('.form-group');
    const editor = group.querySelector('.markdown-editor');
    const preview = group.querySelector('.markdown-preview');
    if (!editor || !preview) return;
    const showing = !preview.hidden;
    preview.hidden = showing;
    editor.hidden = !showing;
    button.textContent = showing ? '预览' : '继续编辑';
    if (!showing) preview.innerHTML = renderMarkdown(editor.value);
}
function wrapSelection(prefix, suffix, placeholder) {
    const editor = document.querySelector('.markdown-editor:not([hidden])');
    if (!editor || editor.readOnly) return;
    const start = editor.selectionStart, end = editor.selectionEnd;
    const selected = editor.value.slice(start, end) || placeholder;
    editor.setRangeText(`${prefix}${selected}${suffix}`, start, end, 'select');
    editor.focus();
    editor.selectionStart = start + prefix.length;
    editor.selectionEnd = start + prefix.length + selected.length;
    editor.dispatchEvent(new Event('input', { bubbles: true }));
}
function imageUploadStatus(group, message, error = false) {
    let status = group.querySelector('.image-upload-status');
    if (!status) {
        status = document.createElement('p');
        status.className = 'image-upload-status';
        status.setAttribute('role', 'status');
        status.setAttribute('aria-live', 'polite');
        group.appendChild(status);
    }
    status.textContent = message;
    status.classList.toggle('is-error', error);
}
const uploadFormStates = new WeakMap();
function setUploadBusy(form, busy) {
    if (!form) return;
    let state = uploadFormStates.get(form);
    if (busy) {
        if (!state) {
            state = {count: 0, controls: [...form.querySelectorAll('button[type="submit"], button:not([type])')].map(button => ({button, disabled: button.disabled}))};
            uploadFormStates.set(form, state);
        }
        state.count++;
        state.controls.forEach(({button}) => { button.disabled = true; });
    } else if (state && --state.count === 0) {
        state.controls.forEach(({button, disabled}) => { button.disabled = disabled; });
        uploadFormStates.delete(form);
    }
}
async function uploadEditorImage(file, type = 'post') {
    if (file.size > 10 * 1024 * 1024) throw new Error('请选择 10MB 以内的图片。');
    const form = new FormData();
    form.append('image', file);
    form.append('type', type);
    const response = await fetch('/admin/upload_image.php', { method: 'POST', body: form, credentials: 'same-origin' });
    if (response.status === 413) throw new Error('图片过大，请选择 10MB 以内的图片。');
    let data;
    try { data = await response.json(); } catch (_) { throw new Error('图片未能上传，请稍后重试。'); }
    if (!response.ok || !data.url) throw new Error(data.error || '图片上传失败，请重试。');
    if (!/^\/assets\/images\/(posts|avatars)\/[a-zA-Z0-9_.-]+$/.test(data.url)) throw new Error('未能取得有效的图片地址，请重试。');
    return data.url;
}
function escapeImageAttribute(value) {
    return value.replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
}
async function insertContentImage(input) {
    const file = input.files?.[0];
    const group = input.closest('.form-group');
    const editor = group?.querySelector('.markdown-editor');
    if (!file || !editor) return;
    if (editor.readOnly) { imageUploadStatus(group, '上一张图片正在上传，请稍等。'); input.value = ''; return; }
    const start = editor.selectionStart, end = editor.selectionEnd;
    const form = input.closest('form');
    const wasReadOnly = editor.readOnly;
    editor.readOnly = true;
    input.disabled = true;
    setUploadBusy(form, true);
    imageUploadStatus(group, '正在上传图片，请稍等…');
    try {
        const src = await uploadEditorImage(file);
        const alt = escapeImageAttribute(file.name.replace(/\.[^.]+$/, ''));
        const before = editor.value.slice(0, start).replace(/\n*$/, '');
        const after = editor.value.slice(end).replace(/^\n*/, '');
        const image = `<figure class="article-image">\n  <img src="${src}" alt="${alt}" loading="lazy">\n</figure>`;
        const inserted = (before ? '\n\n' : '') + image + '\n\n';
        editor.value = before + inserted + after;
        editor.hidden = false;
        const preview = group.querySelector('.markdown-preview');
        if (preview) preview.hidden = true;
        const previewButton = group.querySelector('[data-preview-toggle]');
        if (previewButton) previewButton.textContent = '预览';
        editor.focus();
        editor.selectionStart = editor.selectionEnd = before.length + inserted.length;
        editor.dispatchEvent(new Event('input', { bubbles: true }));
        imageUploadStatus(group, '图片已居中插入。可以继续写下一段，完成后保存文章。');
    } catch (error) {
        imageUploadStatus(group, error.message || '图片上传失败，请重试。', true);
    } finally {
        editor.readOnly = wasReadOnly;
        input.disabled = false;
        input.value = '';
        setUploadBusy(form, false);
    }
}
async function uploadCover(input) {
    const file = input.files?.[0];
    const form = input.closest('form');
    const group = input.closest('.form-group');
    if (!file || !form || !group) return;
    input.disabled = true;
    setUploadBusy(form, true);
    imageUploadStatus(group, '正在上传封面…');
    try {
        const url = await uploadEditorImage(file);
        form.querySelector('[name="cover"]').value = url;
        let preview = group.querySelector('.cover-image-preview');
        if (!preview) {
            preview = document.createElement('img');
            preview.className = 'cover-image-preview';
            preview.alt = '已上传的文章封面';
            group.appendChild(preview);
        }
        preview.src = url;
        imageUploadStatus(group, '封面已上传，保存文章后生效。');
    } catch (error) { imageUploadStatus(group, error.message || '封面上传失败，请重试。', true); }
    finally { input.disabled = false; input.value = ''; setUploadBusy(form, false); }
}
async function uploadAvatar(input) {
    const file = input.files?.[0];
    const form = input.closest('form');
    const group = input.closest('.form-group');
    if (!file || !form || !group) return;
    input.disabled = true;
    setUploadBusy(form, true);
    imageUploadStatus(group, '正在上传头像…');
    try {
        form.querySelector('[name="avatar"]').value = await uploadEditorImage(file, 'avatar');
        imageUploadStatus(group, '头像已上传，请保存个人资料。');
    } catch (error) { imageUploadStatus(group, error.message || '头像上传失败，请重试。', true); }
    finally { input.disabled = false; input.value = ''; setUploadBusy(form, false); }
}
document.querySelectorAll('form').forEach(form => form.addEventListener('submit', event => {
    if (uploadFormStates.has(form)) {
        event.preventDefault();
        imageUploadStatus(form.querySelector('.form-group') || form, '图片仍在上传，请完成后再保存文章。');
    }
}));
