const weatherIcons = {
    '晴': '☀️', '多云': '⛅', '阴': '☁️', '小雨': '🌧️', '中雨': '🌧️',
    '大雨': '⛈️', '雷阵雨': '⛈️', '小雪': '❄️', '中雪': '🌨️', '大雪': '☃️',
    '雾': '🌫️', '霾': '😷'
};

function getWeatherIcon(desc) {
    for (const key in weatherIcons) {
        if (desc.includes(key)) return weatherIcons[key];
    }
    return '🌤️';
}

const cityLabel = document.getElementById('weather-city');
const changeCityButton = document.getElementById('change-city');
const savedCity = localStorage.getItem('weather_city') || '苏州';
const cityModal = document.getElementById('city-picker-modal');
const cityList = document.getElementById('city-list');
const cityLetters = document.getElementById('city-letters');
const citySearch = document.getElementById('city-search');
let cities = [];
let counties = [];
const cityDataPromise = fetch('https://cdn.jsdelivr.net/npm/china-area-data@5.0.0/data.json').then(response => response.json()).then(data => {
    const toLetter = name => (window.pinyinPro ? window.pinyinPro.pinyin(name, { pattern: 'first', toneType: 'none' })[0] : name[0]).toUpperCase();
    Object.entries(data['86'] || {}).forEach(([provinceCode, provinceName]) => {
        Object.entries(data[provinceCode] || {}).forEach(([cityCode, cityName]) => {
            cities.push({ letter: toLetter(cityName), name: cityName, code: cityCode });
            Object.entries(data[cityCode] || {}).forEach(([countyCode, countyName]) => {
                counties.push({ letter: toLetter(countyName), name: countyName, code: countyCode });
            });
        });
    });
    cities = cities.filter((city, index, list) => list.findIndex(item => item.name === city.name) === index);
    counties = counties.filter((county, index, list) => list.findIndex(item => item.name === county.name) === index);
});

function renderCityList(filter = '', letter = '') {
    const keyword = filter.trim();
    const source = keyword ? cities.concat(counties) : cities;
    const result = source.filter(city => (!letter || city.letter === letter) && (!keyword || city.name.includes(keyword)));
    cityList.innerHTML = result.length ? result.map(city => `<button type="button" data-city="${city.name}">${city.name}</button>`).join('') : '<p>没有找到匹配城市</p>';
    cityList.querySelectorAll('[data-city]').forEach(button => button.addEventListener('click', () => selectCity(button.dataset.city)));
}

function openCityPicker() {
    cityModal.hidden = false;
    citySearch.value = '';
    cityLetters.innerHTML = '<button type="button" data-letter="">全部</button>' + 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'.split('').map(letter => `<button type="button" data-letter="${letter}">${letter}</button>`).join('');
    cityLetters.querySelectorAll('button').forEach(button => button.addEventListener('click', () => renderCityList(citySearch.value, button.dataset.letter)));
    cityList.innerHTML = '<p>城市列表加载中...</p>';
    cityDataPromise.then(() => renderCityList());
    citySearch.focus();
}

function selectCity(city) {
    cityModal.hidden = true;
    localStorage.setItem('weather_city', city);
    if (cityLabel) cityLabel.textContent = city;
    loadWeather(city);
}

function loadWeather(city) {
    fetch('/api/weather.php?location=' + encodeURIComponent(city))
    .then(r => r.json())
    .then(data => {
        if (data.error) {
            window.alert(data.error);
            return;
        }
        if (cityLabel) cityLabel.textContent = data.city || city;
        document.getElementById('weather-icon').textContent = getWeatherIcon(data.now.text);
        document.getElementById('weather-temp').textContent = data.now.temp + '°';
        document.getElementById('weather-desc').textContent = data.now.text;
        if (data.forecast) {
            const days = ['今天','明天','后天'];
            document.getElementById('weather-forecast').innerHTML = data.forecast.slice(0,3).map((f,i) => `
                <div class="forecast-item">
                    <div>${days[i]}</div>
                    <div class="f-icon">${getWeatherIcon(f.textDay)}</div>
                    <div class="f-temp">${f.tempMin}~${f.tempMax}°</div>
                </div>
            `).join('');
        }
    });
}

if (cityLabel) cityLabel.textContent = savedCity;
loadWeather(savedCity);

if (changeCityButton) {
    changeCityButton.addEventListener('click', openCityPicker);
}
document.getElementById('close-city-picker')?.addEventListener('click', () => { cityModal.hidden = true; });
citySearch?.addEventListener('input', () => renderCityList(citySearch.value));
cityModal?.addEventListener('click', event => { if (event.target === cityModal) cityModal.hidden = true; });