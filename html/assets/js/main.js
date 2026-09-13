// 粒子效果
(function() {
    const container = document.getElementById('particles');
    if (!container) return;
    for (let i = 0; i < 18; i++) {
        const p = document.createElement('div');
        p.className = 'particle';
        p.style.cssText = `
            left: ${Math.random() * 100}%;
            width: ${4 + Math.random() * 6}px;
            height: ${4 + Math.random() * 6}px;
            animation-duration: ${6 + Math.random() * 10}s;
            animation-delay: ${Math.random() * 8}s;
        `;
        container.appendChild(p);
    }
})();

// 导航切换
function toggleNav() {
    document.querySelector('.nav-links').classList.toggle('open');
}

// 音乐播放器
const audio = document.getElementById('audio');
const playBtn = document.getElementById('play-btn');
const disc = document.getElementById('disc');
const songName = document.getElementById('song-name');
const songArtist = document.getElementById('song-artist');
const progressFill = document.getElementById('progress-fill');
const currentTimeEl = document.getElementById('current-time');
const totalTimeEl = document.getElementById('total-time');
const playlistEl = document.getElementById('playlist');

let songs = [];
let currentIndex = 0;
let isPlaying = false;

// 从服务器加载歌曲列表
fetch('api/music.php')
    .then(r => r.json())
    .then(data => {
        songs = data;
        renderPlaylist();
        if (songs.length > 0) loadSong(0);
    })
    .catch(() => {
        // 没有歌曲时默认提示
        if (songName) songName.textContent = '暂无音乐';
        if (songArtist) songArtist.textContent = '去后台添加';
    });

function renderPlaylist() {
    if (!playlistEl) return;
    playlistEl.innerHTML = '';
    songs.forEach((s, i) => {
        const div = document.createElement('div');
        div.className = 'playlist-item' + (i === currentIndex ? ' active' : '');
        div.textContent = (s.artist ? s.artist + ' - ' : '') + s.name;
        div.onclick = () => { loadSong(i); togglePlay(true); };
        playlistEl.appendChild(div);
    });
}

function loadSong(index) {
    if (!songs.length) return;
    currentIndex = index;
    const s = songs[index];
    audio.src = s.file_path;
    if (songName) songName.textContent = s.name;
    if (songArtist) songArtist.textContent = s.artist || '未知艺术家';
    renderPlaylist();
}

function togglePlay(forcePlay) {
    if (!songs.length) return;
    if (forcePlay || !isPlaying) {
        audio.play().then(() => {
            isPlaying = true;
            if (playBtn) playBtn.textContent = '⏸';
            if (disc) disc.classList.add('spinning');
        }).catch(() => {});
    } else {
        audio.pause();
        isPlaying = false;
        if (playBtn) playBtn.textContent = '▶';
        if (disc) disc.classList.remove('spinning');
    }
}

function prevSong() {
    loadSong((currentIndex - 1 + songs.length) % songs.length);
    if (isPlaying) togglePlay(true);
}

function nextSong() {
    loadSong((currentIndex + 1) % songs.length);
    if (isPlaying) togglePlay(true);
}

if (audio) {
    audio.addEventListener('timeupdate', () => {
        if (!audio.duration) return;
        const pct = (audio.currentTime / audio.duration) * 100;
        if (progressFill) progressFill.style.width = pct + '%';
        if (currentTimeEl) currentTimeEl.textContent = formatTime(audio.currentTime);
    });

    audio.addEventListener('loadedmetadata', () => {
        if (totalTimeEl) totalTimeEl.textContent = formatTime(audio.duration);
    });

    audio.addEventListener('ended', nextSong);

    const progressBar = document.querySelector('.progress-bar');
    if (progressBar) {
        progressBar.addEventListener('click', e => {
            const rect = progressBar.getBoundingClientRect();
            const pct = (e.clientX - rect.left) / rect.width;
            audio.currentTime = pct * audio.duration;
        });
    }
}

function formatTime(s) {
    if (isNaN(s)) return '0:00';
    const m = Math.floor(s / 60);
    const sec = Math.floor(s % 60).toString().padStart(2, '0');
    return `${m}:${sec}`;
}

// 日历
function updateCalendar() {
    const now = new Date();
    const weekdays = ['星期日','星期一','星期二','星期三','星期四','星期五','星期六'];
    document.getElementById('cal-date') && (document.getElementById('cal-date').textContent = now.getDate());
    document.getElementById('cal-weekday') && (document.getElementById('cal-weekday').textContent = weekdays[now.getDay()]);
    document.getElementById('cal-lunar') && (document.getElementById('cal-lunar').textContent =
        now.getFullYear() + '.' + String(now.getMonth()+1).padStart(2,'0'));
}
updateCalendar();
