<!doctype html>
<html lang="uk">
<head>
<meta charset="utf-8">
<title>Перевірка позиції сайту в Google</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<style>
body{font-family:system-ui,'Segoe UI',Roboto,Arial,sans-serif;max-width:760px;margin:24px auto;padding:0 16px;line-height:1.5}
h1{margin-bottom:12px;color:#333}
p{color:#666;margin-bottom:20px}
.card{border:1px solid #eee;border-radius:12px;padding:20px;margin-top:16px;box-shadow:0 2px 4px rgba(0,0,0,0.1)}
label{font-weight:600;margin-top:12px;margin-bottom:6px;display:block;color:#333}
input,select,button{width:100%;padding:12px;border:1px solid #ddd;border-radius:8px;font-size:14px;box-sizing:border-box}
input:focus,select:focus{border-color:#4285f4;outline:none;box-shadow:0 0 0 2px rgba(66,133,244,0.2)}
.row{display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px}
@media (max-width: 600px){.row{grid-template-columns:1fr}}
button{background:#1a73e8;color:#fff;cursor:pointer;margin-top:16px;border:none;font-weight:500;transition:background-color 0.2s}
button:hover:not([disabled]){background:#1557b0}
button[disabled]{opacity:.6;cursor:not-allowed;background:#ccc}
.result-success{background:#e8f5e8;border-color:#4caf50;color:#2e7d32}
.result-warning{background:#fff8e1;border-color:#ff9800;color:#ef6c00}
.result-error{background:#ffebee;border-color:#f44336;color:#c62828}
.position-highlight{font-size:1.2em;font-weight:700;color:#1a73e8}
.search-info{margin-top:12px;font-size:0.9em;color:#666;border-top:1px solid #eee;padding-top:12px}
pre{white-space:pre-wrap;font-family:monospace;font-size:0.9em}
.loading{display:inline-block;width:16px;height:16px;border:2px solid #fff;border-radius:50%;border-top-color:transparent;animation:spin 1s linear infinite;margin-right:8px}
@keyframes spin{to{transform:rotate(360deg)}}
</style>
</head>
<body>
<h1>🔍 Перевірка позиції сайту в Google</h1>
<p>Введіть пошукове слово, домен, країну та мову — і отримаєте позицію у видачі Google.</p>

<div class="card">
<form id="searchForm">
<div class="row">
<div>
<label for="keyword">Ключове слово</label>
<input id="keyword" name="keyword" placeholder="наприклад: laravel tutorial" required>
</div>
<div>
<label for="domain">Домен</label>
<input id="domain" name="domain" placeholder="наприклад: laravel.com" required>
</div>
</div>

<div class="row">
<div>
<label for="location_code">Локація</label>
<select id="location_code" name="location_code" required>
@foreach($locations as $code => $name)
<option value="{{ $code }}"{{ $code == 2840 ? ' selected' : '' }}>{{ $name }}</option>
@endforeach
</select>
</div>
<div>
<label for="language_code">Мова</label>
<select id="language_code" name="language_code" required>
@foreach($languages as $code => $name)
<option value="{{ $code }}"{{ $code == 'en' ? ' selected' : '' }}>{{ $name }}</option>
@endforeach
</select>
</div>
</div>

<button type="submit" id="btnSearch">Пошук позиції</button>
</form>
</div>

<div id="result" class="card" style="display:none"></div>

<script>
const csrf = document.querySelector('meta[name="csrf-token"]').content;
const EL = id => document.getElementById(id);

function setLoading(loading) {
    const btn = EL('btnSearch');
    btn.disabled = loading;
    btn.innerHTML = loading ? '<span class="loading"></span>Шукаю...' : 'Пошук позиції';
}

function showResult(content, type = '') {
    const box = EL('result');
    box.style.display = 'block';
    box.className = 'card';
    if (type) box.classList.add(`result-${type}`);
    box.innerHTML = content;
}

async function doSearch(e) {
    e.preventDefault();
    
    const formData = new FormData(EL('searchForm'));
    const payload = Object.fromEntries(formData.entries());
    payload.location_code = parseInt(payload.location_code, 10);

    // Validación básica
    if (!payload.keyword?.trim() || !payload.domain?.trim()) {
        alert('Заповніть усі обов\'язкові поля.');
        return;
    }

    setLoading(true);
    
    try {
        const res = await fetch('{{ route('serp.search') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf,
                'Accept': 'application/json'
            },
            body: JSON.stringify(payload)
        });

        const j = await res.json();
        
        if (j.success && j.data) {
            const d = j.data;
            if (d.found) {
                showResult(`
                    <h3>✅ Знайдено!</h3>
                    <div class="position-highlight">Позиція #${d.position}</div>
                    <p><strong>Ключове слово:</strong> ${d.keyword}<br>
                    <strong>Домен:</strong> ${d.domain}</p>
                    ${d.search_info ? `<div class="search-info">
                        📍 ${d.search_info.location_name} | 🌐 ${d.search_info.language_name}<br>
                        ⏰ ${new Date(d.search_info.searched_at).toLocaleString('uk-UA')}
                    </div>` : ''}
                `, 'success');
            } else {
                showResult(`
                    <h3>⚠️ Сайт не знайдено</h3>
                    <p>Домен <strong>${d.domain}</strong> не знайдено у ТОП-100 результатах за запитом "<strong>${d.keyword}</strong>".</p>
                    <p>Спробуйте інші ключові слова або перевірте правильність домену.</p>
                `, 'warning');
            }
        } else {
            showResult(`
                <h3>❌ Помилка пошуку</h3>
                <p>${j.message || 'Невідома помилка сервера'}</p>
                ${j.errors ? '<pre>' + JSON.stringify(j.errors, null, 2) + '</pre>' : ''}
            `, 'error');
        }
    } catch (e) {
        showResult(`
            <h3>🌐 Помилка мережі</h3>
            <p>Не вдалося підключитися до сервера.</p>
            <pre>${e.message}</pre>
        `, 'error');
    } finally {
        setLoading(false);
    }
}

// Event listeners
EL('searchForm').addEventListener('submit', doSearch);

// Limpiar resultado cuando cambian los campos
['keyword', 'domain', 'location_code', 'language_code'].forEach(id => {
    EL(id).addEventListener('input', () => {
        EL('result').style.display = 'none';
    });
});
</script>
</body>
</html>