// Legacy navigation remains available to account and administration pages.
function toggleNav() {
    document.querySelector('.nav-links')?.classList.toggle('open');
}

const audio = document.getElementById('audio');
const playBtn = document.getElementById('play-btn');
const disc = document.getElementById('disc');
const songName = document.getElementById('song-name');
const songArtist = document.getElementById('song-artist');
const progressFill = document.getElementById('progress-fill');
const currentTimeEl = document.getElementById('current-time');
const totalTimeEl = document.getElementById('total-time');
const playlistEl = document.querySelector('[data-full-playlist]') || document.getElementById('playlist');
let songs = [];
let currentIndex = 0;
let isPlaying = false;

if (audio) {
    const initial = document.getElementById('music-data');
    const request = initial ? Promise.resolve(JSON.parse(initial.textContent)) : fetch('api/music.php').then(response => {
        if (!response.ok) throw new Error('Music unavailable');
        return response.json();
    });
    request.then(data => {
        songs = Array.isArray(data) ? data : [];
        renderPlaylist();
        if (songs.length) loadSong(0);
        else if (songName) songName.textContent = '暂无匹配的音乐';
    }).catch(() => {
        if (songName) songName.textContent = '歌单暂时未能加载';
        if (songArtist) songArtist.textContent = '稍后刷新再试试';
    });
}

function renderPlaylist() {
    if (!playlistEl) return;
    playlistEl.replaceChildren();
    if (!songs.length) {
        const empty = document.createElement('p');
        empty.className = 'panel-intro';
        empty.textContent = '没有找到匹配的音乐，换一个关键词试试。';
        playlistEl.appendChild(empty);
    }
    songs.forEach((song, index) => {
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'playlist-item' + (index === currentIndex ? ' active' : '');
        button.setAttribute('aria-label', '播放：' + song.name);
        button.setAttribute('aria-current', index === currentIndex ? 'true' : 'false');
        if (playlistEl.hasAttribute('data-full-playlist')) {
            const number = document.createElement('span');
            number.className = 'track-number';
            number.textContent = String(index + 1).padStart(2, '0');
            const info = document.createElement('span');
            info.className = 'track-info';
            const title = document.createElement('span');
            title.textContent = song.name;
            const artist = document.createElement('small');
            artist.textContent = song.artist || '未知艺术家';
            info.append(title, artist);
            const play = document.createElement('span');
            play.className = 'track-play';
            play.textContent = '▶';
            play.setAttribute('aria-hidden', 'true');
            button.append(number, info, play);
        } else button.textContent = (song.artist ? song.artist + ' - ' : '') + song.name;
        button.addEventListener('click', () => { loadSong(index); togglePlay(true); });
        playlistEl.appendChild(button);
    });
}

function loadSong(index) {
    if (!audio || !songs.length) return;
    currentIndex = index;
    audio.src = songs[index].file_path;
    if (songName) songName.textContent = songs[index].name;
    if (songArtist) songArtist.textContent = songs[index].artist || '未知艺术家';
    if (progressFill) progressFill.style.width = '0%';
    if (currentTimeEl) currentTimeEl.textContent = '0:00';
    if (totalTimeEl) totalTimeEl.textContent = '0:00';
    renderPlaylist();
}
function togglePlay(forcePlay) {
    if (!audio || !songs.length) return;
    if (forcePlay || audio.paused) audio.play().catch(() => {
        if (songArtist) songArtist.textContent = '暂时无法播放，请重试或切换歌曲';
    });
    else audio.pause();
}
function prevSong() {
    const resume = isPlaying;
    loadSong((currentIndex - 1 + songs.length) % songs.length);
    if (resume) togglePlay(true);
}
function nextSong() {
    const resume = isPlaying || (audio && audio.ended);
    loadSong((currentIndex + 1) % songs.length);
    if (resume) togglePlay(true);
}
function syncPlayer() {
    isPlaying = !audio.paused && !audio.ended;
    if (playBtn) { playBtn.textContent = isPlaying ? '⏸' : '▶'; playBtn.setAttribute('aria-label', isPlaying ? '暂停' : '播放'); }
    disc?.classList.toggle('spinning', isPlaying);
}
if (audio) {
    audio.addEventListener('play', syncPlayer);
    audio.addEventListener('pause', syncPlayer);
    const progress = document.querySelector('.progress-bar');
    audio.addEventListener('timeupdate', () => {
        if (!Number.isFinite(audio.duration)) return;
        const percent = audio.currentTime / audio.duration * 100;
        if (progressFill) progressFill.style.width = percent + '%';
        if (currentTimeEl) currentTimeEl.textContent = formatTime(audio.currentTime);
        progress?.setAttribute('aria-valuenow', String(Math.round(percent)));
    });
    audio.addEventListener('loadedmetadata', () => { if (totalTimeEl) totalTimeEl.textContent = formatTime(audio.duration); });
    audio.addEventListener('ended', nextSong);
    progress?.addEventListener('click', event => {
        if (!Number.isFinite(audio.duration)) return;
        const rect = progress.getBoundingClientRect();
        audio.currentTime = Math.max(0, Math.min(1, (event.clientX - rect.left) / rect.width)) * audio.duration;
    });
    progress?.addEventListener('keydown', event => {
        if (!['ArrowLeft', 'ArrowRight'].includes(event.key) || !Number.isFinite(audio.duration)) return;
        event.preventDefault();
        audio.currentTime = Math.max(0, Math.min(audio.duration, audio.currentTime + (event.key === 'ArrowRight' ? 5 : -5)));
    });
}
function formatTime(seconds) {
    if (!Number.isFinite(seconds)) return '0:00';
    return `${Math.floor(seconds / 60)}:${Math.floor(seconds % 60).toString().padStart(2, '0')}`;
}
function updateCalendar() {
    const now = new Date();
    const values = {
        'cal-date': now.getDate(),
        'cal-weekday': ['星期日', '星期一', '星期二', '星期三', '星期四', '星期五', '星期六'][now.getDay()],
        'cal-lunar': `${now.getFullYear()}.${String(now.getMonth() + 1).padStart(2, '0')}`
    };
    Object.entries(values).forEach(([id, value]) => { const el = document.getElementById(id); if (el) el.textContent = value; });
}
updateCalendar();
