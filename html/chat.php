<?php
require_once 'includes/db.php';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/jpeg" href="/assets/images/cover.jpg">
    <title>AI 对话 - DuoDuo's Daily</title>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Serif+SC:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .chat-layout {
            position: relative; z-index: 10;
            max-width: 800px;
            margin: 0 auto;
            padding: 80px 20px 40px;
            height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .chat-card {
            background: var(--card);
            border: 1px solid var(--card-border);
            border-radius: 20px;
            box-shadow: var(--shadow);
            backdrop-filter: blur(8px);
            display: flex;
            flex-direction: column;
            flex: 1;
            overflow: hidden;
        }
        .chat-header {
            padding: 18px 24px;
            border-bottom: 1px solid var(--card-border);
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .chat-avatar {
            width: 40px; height: 40px;
            border-radius: 50%;
            overflow: hidden;
            background: linear-gradient(135deg, #3ab4c8, #1a7a8a);
            display: flex; align-items: center; justify-content: center;
            font-size: 1.2rem;
            color: white;
        }
        .chat-avatar img, .message-avatar img {
            width: 100%; height: 100%;
            display: block;
            object-fit: cover;
        }
        .chat-header-info h3 {
            font-size: 1rem;
            color: var(--text);
        }
        .chat-header-info p {
            font-size: 0.75rem;
            color: var(--text-light);
        }
        .chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 20px 24px;
            display: flex;
            flex-direction: column;
            gap: 16px;
        }
        .message {
            display: flex;
            gap: 10px;
            max-width: 85%;
        }
        .message.user {
            align-self: flex-end;
            flex-direction: row-reverse;
        }
        .message-avatar {
            width: 32px; height: 32px;
            border-radius: 50%;
            flex-shrink: 0;
            display: flex; align-items: center; justify-content: center;
            font-size: 0.9rem;
        }
        .message.ai .message-avatar {
            background: linear-gradient(135deg, #3ab4c8, #1a7a8a);
            color: white;
        }
        .message.user .message-avatar {
            background: linear-gradient(135deg, #ffb3c6, #ff8fab);
            color: white;
        }
        .message-bubble {
            padding: 10px 14px;
            border-radius: 16px;
            font-size: 0.9rem;
            line-height: 1.6;
        }
        .message.ai .message-bubble {
            background: rgba(58,180,200,0.1);
            color: var(--text);
            border-bottom-left-radius: 4px;
        }
        .message.user .message-bubble {
            background: linear-gradient(135deg, #3ab4c8, #1a7a8a);
            color: white;
            border-bottom-right-radius: 4px;
        }
        .message-bubble.loading::after {
            content: '●●●';
            animation: blink 1s infinite;
            letter-spacing: 4px;
        }
        @keyframes blink {
            0%,100% { opacity: 0.3; }
            50% { opacity: 1; }
        }
        .chat-input-area {
            padding: 16px 24px;
            border-top: 1px solid var(--card-border);
            display: flex;
            gap: 10px;
            align-items: flex-end;
        }
        .chat-input {
            flex: 1;
            padding: 10px 16px;
            border: 1px solid var(--card-border);
            border-radius: 20px;
            background: rgba(255,255,255,0.6);
            color: var(--text);
            font-family: inherit;
            font-size: 0.9rem;
            outline: none;
            resize: none;
            max-height: 120px;
            transition: border-color 0.2s;
        }
        .chat-input:focus { border-color: var(--accent); }
        .chat-send {
            width: 40px; height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #3ab4c8, #1a7a8a);
            border: none;
            color: white;
            font-size: 1rem;
            cursor: pointer;
            flex-shrink: 0;
            transition: opacity 0.2s, transform 0.1s;
        }
        .chat-send:hover { opacity: 0.9; transform: scale(1.05); }
        .chat-send:disabled { opacity: 0.5; cursor: not-allowed; }
        .chat-clear {
            font-size: 0.75rem;
            color: var(--text-light);
            background: none;
            border: none;
            cursor: pointer;
            padding: 4px 8px;
            border-radius: 10px;
            transition: all 0.2s;
        }
        .chat-clear:hover { background: rgba(58,180,200,0.1); color: var(--accent); }
    </style>
</head>
<body>
<div class="bg-overlay"></div>
<div class="particles" id="particles"></div>

<nav class="navbar">
    <div class="nav-inner">
        <a href="index.php" class="nav-logo">DuoDuo's Daily</a>
        <ul class="nav-links">
            <li><a href="index.php">首页</a></li>
            <li><a href="index.php?cat=life">生活</a></li>
            <li><a href="index.php?cat=essay">随笔</a></li>
            <li><a href="index.php?cat=tech">技术</a></li>
            <li><a href="music.php">音乐</a></li>
            <li><a href="about.php">关于</a></li>
            <li><a href="chat.php">AI 对话</a></li>i>
            <li><a href="account.php">账户</a></li>
        </ul>
    </div>
</nav>

<div class="chat-layout">
    <div class="chat-card">
        <div class="chat-header">
            <div class="chat-avatar"><img src="/assets/images/Qwen.jpg" alt="通义千问头像"></div>
            <div class="chat-header-info">
                <h3>通义千问</h3>
                <p>Qwen-turbo · 随时为你解答</p>
            </div>
            <button class="chat-clear" onclick="clearChat()" style="margin-left:auto">清空对话</button>
        </div>
        <div class="chat-messages" id="chat-messages">
            <div class="message ai">
                <div class="message-avatar"><img src="/assets/images/Qwen.jpg" alt="通义千问头像"></div>
                <div class="message-bubble">你好！我是通义千问，有什么可以帮助你的？😊</div>
            </div>
        </div>
        <div class="chat-input-area">
            <textarea class="chat-input" id="chat-input" placeholder="输入消息，Enter 发送..." rows="1"></textarea>
            <button class="chat-send" id="send-btn" onclick="sendMessage()">➤</button>
        </div>
    </div>
</div>

<script src="assets/js/main.js"></script>
<script>
    const systemMsg = { role: 'system', content: '你是DuoDuo博客的AI助手，友好、简洁地回答问题。' };
    const saved = localStorage.getItem('chat_messages');
    const messages = saved ? JSON.parse(saved) : [systemMsg];

    const chatMessages = document.getElementById('chat-messages');
    const chatInput = document.getElementById('chat-input');
    const sendBtn = document.getElementById('send-btn');

    chatInput.addEventListener('keydown', e => {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    });

    chatInput.addEventListener('input', () => {
        chatInput.style.height = 'auto';
        chatInput.style.height = chatInput.scrollHeight + 'px';
    });

    function addMessage(role, content, loading = false) {
        const div = document.createElement('div');
        div.className = `message ${role}`;
        div.innerHTML = `
                <div class="message-avatar">${role === 'ai' ? '<img src="/assets/images/Qwen.jpg" alt="通义千问头像">' : '👤'}</div>
                <div class="message-bubble${loading ? ' loading' : ''}">${loading ? '' : content}</div>
            `;
        chatMessages.appendChild(div);
        chatMessages.scrollTop = chatMessages.scrollHeight;
        return div.querySelector('.message-bubble');
    }

    async function sendMessage() {
        const text = chatInput.value.trim();
        if (!text) return;

        chatInput.value = '';
        chatInput.style.height = 'auto';
        sendBtn.disabled = true;

        addMessage('user', text);
        localStorage.setItem('chat_messages', JSON.stringify(messages));
        messages.push({ role: 'user', content: text });

        const bubble = addMessage('ai', '', true);

        try {
            const res = await fetch('api/chat.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ messages })
            });
            const data = await res.json();
            const reply = data.reply || '抱歉，出错了';
            bubble.classList.remove('loading');
            bubble.classList.remove('loading');
            bubble.innerHTML = reply
                .replace(/&/g,'&amp;')
                .replace(/</g,'&lt;')
                .replace(/>/g,'&gt;')
                .replace(/\n/g,'<br>')
                .replace(/```[\s\S]*?```/g, m => `<pre style="background:rgba(0,0,0,0.1);padding:8px;border-radius:6px;overflow-x:auto;font-size:0.8rem">${m.slice(3,-3)}</pre>`);
            messages.push({ role: 'assistant', content: reply });
        } catch(e) {
            bubble.classList.remove('loading');
            bubble.textContent = '网络错误，请重试';
        }

        sendBtn.disabled = false;
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    function clearChat() {
        chatMessages.innerHTML = `
                <div class="message ai">
                    <div class="message-avatar"><img src="/assets/images/Qwen.jpg" alt="通义千问头像"></div>
                    <div class="message-bubble">对话已清空，有什么可以帮助你的？😊</div>
                </div>`;
        messages.splice(1);
        localStorage.removeItem('chat_messages');
    }

    // 渲染历史消息
    if (saved) {
        messages.forEach(msg => {
            if (msg.role === 'user' || msg.role === 'assistant') {
                addMessage(msg.role === 'user' ? 'user' : 'ai', msg.content);
            }
        });
    }
</script>
</body>
</html>
