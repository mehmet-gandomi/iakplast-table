<?php
/**
 * Standalone price-table page template (no WP header/footer).
 */
if ( ! defined('ABSPATH') ) exit;

$ajax_url  = admin_url('admin-ajax.php');
$site_url  = home_url('/');
$phone     = get_option('iakp_phone',   '+98 21 XXXX XXXX');
$tagline   = get_option('iakp_tagline', 'تولیدکننده لوله و اتصالات پلی‌اتیلن');
$foot_note = get_option('iakp_note',    'قیمت‌ها به ریال بوده و ممکن است بدون اطلاع قبلی تغییر کنند.');
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>لیست قیمت محصولات – آیاک پلاست</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<?php wp_head(); // keeps WP hooks happy without outputting theme styles ?>
<style>
/* ── Reset & Base ──────────────────────────────── */
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{
  --navy:      #0B1F3A;
  --navy2:     #112848;
  --blue:      #1A3C6E;
  --accent:    #E8A020;
  --accent2:   #C8780A;
  --green:     #27AE60;
  --light:     #F5F7FA;
  --white:     #FFFFFF;
  --text:      #1E2D40;
  --muted:     #6B7C93;
  --border:    #E2E8F0;
  --shadow-sm: 0 2px 8px rgba(0,0,0,.07);
  --shadow-md: 0 8px 32px rgba(0,0,0,.1);
  --radius:    12px;
  --trans:     .22s ease;
}
html,body{min-height:100%;font-family:'Vazirmatn',Tahoma,sans-serif;color:var(--text);background:var(--light)}

/* ── Header band ───────────────────────────────── */
.iak-header{
  background:linear-gradient(135deg,var(--navy) 0%,var(--navy2) 60%,#0d2d52 100%);
  color:#fff;padding:0;
  position:relative;overflow:hidden;
}
.iak-header::before{
  content:'';position:absolute;inset:0;
  background:url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.02'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
}
.header-inner{
  position:relative;z-index:1;
  max-width:1200px;margin:0 auto;padding:32px 24px 0;
  display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:20px;
}
.brand{display:flex;align-items:center;gap:16px}
.brand-hex{
  width:56px;height:56px;
  background:linear-gradient(135deg,var(--accent),var(--accent2));
  clip-path:polygon(50% 0%,100% 25%,100% 75%,50% 100%,0% 75%,0% 25%);
  display:flex;align-items:center;justify-content:center;
  font-size:16px;font-weight:900;color:#fff;letter-spacing:-1px;
  box-shadow:0 6px 24px rgba(232,160,32,.45);flex-shrink:0;
}
.brand-name{font-size:20px;font-weight:700;line-height:1.2}
.brand-tagline{font-size:12px;color:rgba(255,255,255,.55);margin-top:3px;letter-spacing:1px}
.header-nav a{
  display:inline-flex;align-items:center;gap:6px;
  padding:10px 20px;border-radius:8px;font-size:13px;font-weight:600;
  border:1.5px solid rgba(255,255,255,.2);color:rgba(255,255,255,.85);
  text-decoration:none;transition:var(--trans);
}
.header-nav a:hover{background:rgba(255,255,255,.08);border-color:rgba(255,255,255,.4);color:#fff}

/* hero strip */
.hero-strip{
  max-width:1200px;margin:28px auto 0;padding:0 24px 36px;
  position:relative;z-index:1;
}
.hero-strip h1{font-size:clamp(20px,3vw,28px);font-weight:700;color:#fff;margin-bottom:8px}
.hero-strip p{font-size:14px;color:rgba(255,255,255,.6);max-width:520px;line-height:1.8}
.hero-meta{display:flex;gap:24px;margin-top:20px;flex-wrap:wrap}
.meta-chip{
  display:flex;align-items:center;gap:8px;
  background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.1);
  border-radius:100px;padding:6px 14px;font-size:12px;color:rgba(255,255,255,.75);
}
.meta-chip strong{color:var(--accent)}

/* ── Main body ─────────────────────────────────── */
.iak-body{max-width:1200px;margin:0 auto;padding:32px 24px 60px}

/* ── Filter bar ────────────────────────────────── */
.filter-bar{
  background:var(--white);border-radius:var(--radius);
  padding:20px 24px;
  box-shadow:var(--shadow-sm);
  border:1px solid var(--border);
  display:flex;align-items:center;flex-wrap:wrap;gap:12px;
  margin-bottom:24px;
}
.filter-bar label{font-size:13px;font-weight:600;color:var(--muted);flex-shrink:0}
.filter-cats{display:flex;flex-wrap:wrap;gap:8px;flex:1}
.cat-pill{
  padding:7px 16px;border-radius:100px;font-size:13px;font-weight:500;
  border:1.5px solid var(--border);background:transparent;color:var(--muted);
  cursor:pointer;transition:var(--trans);font-family:inherit;
}
.cat-pill:hover{border-color:var(--accent);color:var(--accent2)}
.cat-pill.active{background:var(--accent);border-color:var(--accent);color:#fff;font-weight:600}

.search-wrap{position:relative;width:240px;flex-shrink:0}
.search-wrap input{
  width:100%;padding:9px 36px 9px 14px;border:1.5px solid var(--border);
  border-radius:100px;font-size:13px;font-family:inherit;
  color:var(--text);transition:var(--trans);direction:rtl;background:#fff;
}
.search-wrap input:focus{outline:none;border-color:var(--accent);box-shadow:0 0 0 3px rgba(232,160,32,.12)}
.search-wrap::after{
  content:'🔍';position:absolute;left:12px;top:50%;transform:translateY(-50%);
  font-size:14px;pointer-events:none;
}

/* ── Table card ────────────────────────────────── */
.table-card{
  background:var(--white);border-radius:var(--radius);
  box-shadow:var(--shadow-sm);border:1px solid var(--border);
  overflow:hidden;
}
.table-header{
  display:flex;justify-content:space-between;align-items:center;
  padding:16px 24px;border-bottom:1px solid var(--border);flex-wrap:wrap;gap:8px;
}
.table-header h2{font-size:15px;font-weight:700;color:var(--navy)}
.table-header span{font-size:13px;color:var(--muted)}
#iak-count strong{color:var(--accent)}

.iak-table{width:100%;border-collapse:collapse}
.iak-table thead th{
  background:var(--navy);color:#fff;
  padding:13px 20px;text-align:right;
  font-size:13px;font-weight:600;white-space:nowrap;
}
.iak-table thead th:first-child{width:52px;text-align:center}
.iak-table tbody tr{transition:background var(--trans)}
.iak-table tbody tr:nth-child(even){background:#FAFBFD}
.iak-table tbody tr:hover{background:#F0F6FF}
.iak-table td{
  padding:14px 20px;font-size:14px;color:var(--text);
  border-bottom:1px solid var(--border);
}
.iak-table td:first-child{text-align:center;color:var(--muted);font-size:12px}
.iak-table .td-name{font-weight:600}
.iak-table .td-cat .cat-tag{
  display:inline-block;padding:3px 12px;border-radius:100px;
  background:#EEF2FF;color:#1A3C6E;font-size:12px;font-weight:500;
}
.iak-table .td-price{
  font-weight:700;color:var(--navy);font-size:15px;
}
.iak-table .td-date{font-size:12px;color:var(--muted)}
.iak-table .td-unit{color:var(--muted);font-size:13px}

.no-results{padding:60px 20px;text-align:center;color:var(--muted);font-size:14px}

/* ── Footer note ───────────────────────────────── */
.iak-footer{
  text-align:center;margin-top:40px;
  font-size:12px;color:var(--muted);line-height:2;
}
.iak-footer a{color:var(--accent2);text-decoration:none;font-weight:600}
.iak-footer a:hover{text-decoration:underline}

/* ── Responsive ────────────────────────────────── */
@media(max-width:700px){
  .iak-table .td-date,.iak-table .td-unit{display:none}
  .iak-table thead th:nth-child(4),
  .iak-table thead th:nth-child(6){display:none}
  .filter-bar{flex-direction:column;align-items:flex-start}
  .search-wrap{width:100%}
  .header-nav{display:none}
}

/* skeleton loader */
@keyframes shimmer{0%{background-position:-800px 0}100%{background-position:800px 0}}
.skeleton{
  background:linear-gradient(90deg,#f0f2f5 25%,#e8eaed 50%,#f0f2f5 75%);
  background-size:800px 100%;animation:shimmer 1.4s infinite;
  border-radius:4px;height:14px;
}
</style>
</head>
<body>

<!-- ══ HEADER ══════════════════════════════════ -->
<header class="iak-header">
  <div class="header-inner">
    <div class="brand">
      <div class="brand-hex">IAK</div>
      <div>
        <div class="brand-name">آیاک پلاست</div>
        <div class="brand-tagline"><?= esc_html($tagline) ?></div>
      </div>
    </div>
    <nav class="header-nav">
      <a href="<?= esc_url($site_url) ?>">🌐 بازگشت به وب‌سایت</a>
    </nav>
  </div>

  <div class="hero-strip">
    <h1>لیست قیمت رسمی محصولات</h1>
    <p>قیمت‌ها به‌روز و مستقیم از کارخانه اعلام می‌شوند. جهت اطمینان از آخرین قیمت با واحد فروش تماس بگیرید.</p>
    <div class="hero-meta">
      <div class="meta-chip">📅 آخرین به‌روزرسانی: <strong id="last-update">—</strong></div>
      <div class="meta-chip">📦 تعداد محصولات: <strong id="total-count">—</strong></div>
      <div class="meta-chip">☎️ تماس: <strong dir="ltr"><?= esc_html($phone) ?></strong></div>
    </div>
  </div>
</header>

<!-- ══ BODY ════════════════════════════════════ -->
<main class="iak-body">

  <!-- Filter bar -->
  <div class="filter-bar">
    <label>دسته‌بندی:</label>
    <div class="filter-cats" id="filter-cats">
      <button class="cat-pill active" data-cat="all">همه</button>
    </div>
    <div class="search-wrap">
      <input type="text" id="search-input" placeholder="جستجوی محصول...">
    </div>
  </div>

  <!-- Table card -->
  <div class="table-card">
    <div class="table-header">
      <h2>جدول قیمت‌ها</h2>
      <span id="iak-count">در حال بارگذاری...</span>
    </div>
    <div style="overflow-x:auto">
      <table class="iak-table">
        <thead>
          <tr>
            <th>#</th>
            <th>نام محصول</th>
            <th>دسته‌بندی</th>
            <th>واحد</th>
            <th>قیمت (ریال)</th>
            <th>آخرین آپدیت</th>
          </tr>
        </thead>
        <tbody id="iak-tbody">
          <tr>
            <td colspan="6">
              <div style="padding:20px 0;display:grid;gap:10px">
                <?php for($i=0;$i<6;$i++): ?>
                <div style="display:grid;grid-template-columns:40px 2fr 1fr 1fr 1fr 1fr;gap:16px;padding:8px 20px">
                  <?php for($j=0;$j<6;$j++): ?><div class="skeleton"></div><?php endfor ?>
                </div>
                <?php endfor ?>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

</main>

<!-- ══ FOOTER ══════════════════════════════════ -->
<footer class="iak-footer">
  <p>© <?= date('Y') ?> شرکت آیاک پلاست — تمامی حقوق محفوظ است</p>
  <p><?= esc_html($foot_note) ?> | <a href="<?= esc_url($site_url) ?>">بازگشت به وب‌سایت اصلی</a></p>
</footer>

<script>
(function(){
  var ajaxUrl = '<?= esc_js($ajax_url) ?>';
  var allProducts = [], currentCat = 'all', searchTerm = '';

  function fmt(dt){
    if(!dt) return '—';
    var d = new Date(dt.replace(' ','T'));
    return d.toLocaleDateString('fa-IR',{year:'numeric',month:'short',day:'numeric'});
  }
  function esc(s){var d=document.createElement('div');d.textContent=s||'';return d.innerHTML;}

  function render(){
    var filtered = allProducts.filter(function(p){
      var matchCat = currentCat === 'all' || p.category === currentCat;
      var matchQ   = !searchTerm || p.name.indexOf(searchTerm) > -1 || p.category.indexOf(searchTerm) > -1;
      return matchCat && matchQ;
    });

    var count = document.getElementById('iak-count');
    count.innerHTML = 'نمایش <strong>'+ filtered.length +'</strong> محصول';

    var tbody = document.getElementById('iak-tbody');
    if(!filtered.length){
      tbody.innerHTML = '<tr><td colspan="6" class="no-results">محصولی یافت نشد 🔍</td></tr>';
      return;
    }
    tbody.innerHTML = filtered.map(function(p,i){
      return '<tr>'
        +'<td>'+(i+1)+'</td>'
        +'<td class="td-name">'+esc(p.name)+'</td>'
        +'<td class="td-cat"><span class="cat-tag">'+esc(p.category)+'</span></td>'
        +'<td class="td-unit">'+esc(p.unit)+'</td>'
        +'<td class="td-price">'+esc(p.price)+'</td>'
        +'<td class="td-date">'+fmt(p.updated_at)+'</td>'
        +'</tr>';
    }).join('');
  }

  function buildCats(){
    var cats = ['all'];
    allProducts.forEach(function(p){if(p.category && cats.indexOf(p.category)<0) cats.push(p.category);});
    var wrap = document.getElementById('filter-cats');
    wrap.innerHTML = cats.map(function(c){
      return '<button class="cat-pill'+(c===currentCat?' active':'')+'" data-cat="'+esc(c)+'">'+(c==='all'?'همه':esc(c))+'</button>';
    }).join('');
    wrap.querySelectorAll('.cat-pill').forEach(function(btn){
      btn.addEventListener('click',function(){
        currentCat = this.dataset.cat;
        wrap.querySelectorAll('.cat-pill').forEach(function(b){b.classList.remove('active');});
        this.classList.add('active');
        render();
      });
    });
  }

  function updateMeta(){
    document.getElementById('total-count').textContent = allProducts.length;
    if(allProducts.length){
      var latest = allProducts.slice().sort(function(a,b){return b.updated_at > a.updated_at ? 1 : -1;})[0];
      document.getElementById('last-update').textContent = fmt(latest.updated_at);
    }
  }

  fetch(ajaxUrl+'?action=iakp_pub_products')
    .then(function(r){return r.json();})
    .then(function(res){
      if(!res.success) return;
      allProducts = res.data;
      buildCats();
      render();
      updateMeta();
    });

  document.getElementById('search-input').addEventListener('input',function(){
    searchTerm = this.value.trim();
    render();
  });
})();
</script>
<?php wp_footer(); ?>
</body>
</html>
