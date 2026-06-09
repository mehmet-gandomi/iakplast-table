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
$font_url  = IAKP_PLUGIN_URL . 'assets/RaviVF.ttf';
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>لیست قیمت محصولات – IAK Plast</title>
<?php wp_head(); ?>
<style>
@font-face{
  font-family:'Ravi';
  src:url('<?= esc_url($font_url) ?>') format('truetype');
  font-weight:100 900;font-style:normal;font-display:swap;
}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{
  --navy:      #0B1F3A;
  --navy2:     #112848;
  --accent:    #E8A020;
  --accent2:   #C8780A;
  --light:     #F5F7FA;
  --white:     #FFFFFF;
  --text:      #1E2D40;
  --muted:     #6B7C93;
  --border:    #E2E8F0;
  --radius:    12px;
  --trans:     .2s ease;
  --shadow:    0 2px 8px rgba(0,0,0,.07);
}
html,body{min-height:100%;font-family:'Ravi',Tahoma,sans-serif;color:var(--text);background:var(--light)}

/* ══ HEADER ══════════════════════════════════════ */
.iak-header{
  background:linear-gradient(135deg,var(--navy) 0%,var(--navy2) 60%,#0d2d52 100%);
  color:#fff;position:relative;overflow:hidden;
}
.iak-header::before{
  content:'';position:absolute;inset:0;
  background:url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%23fff' fill-opacity='.02'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/svg%3E");
}
.header-inner{
  position:relative;z-index:1;
  max-width:1200px;margin:0 auto;
  padding:24px 20px 0;
  display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap;
}
.brand-name{font-size:22px;font-weight:800;letter-spacing:.5px}
.brand-tagline{font-size:12px;color:rgba(255,255,255,.5);margin-top:3px}
.header-nav a{
  display:inline-flex;align-items:center;gap:6px;
  padding:9px 18px;border-radius:8px;font-size:13px;font-weight:600;
  border:1.5px solid rgba(255,255,255,.2);color:rgba(255,255,255,.85);
  text-decoration:none;transition:var(--trans);white-space:nowrap;
}
.header-nav a:hover{background:rgba(255,255,255,.08);border-color:rgba(255,255,255,.4);color:#fff}

.hero-strip{
  position:relative;z-index:1;
  max-width:1200px;margin:20px auto 0;padding:0 20px 28px;
}
.hero-strip h1{font-size:clamp(18px,2.5vw,26px);font-weight:700;color:#fff;margin-bottom:6px}
.hero-strip p{font-size:13px;color:rgba(255,255,255,.6);max-width:520px;line-height:1.8}
.hero-meta{display:flex;gap:12px;margin-top:16px;flex-wrap:wrap}
.meta-chip{
  display:flex;align-items:center;gap:7px;
  background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.1);
  border-radius:100px;padding:5px 13px;font-size:12px;color:rgba(255,255,255,.75);
}
.meta-chip strong{color:var(--accent)}

/* ══ BODY ════════════════════════════════════════ */
.iak-body{max-width:1200px;margin:0 auto;padding:24px 16px 60px}

/* ══ FILTER BAR ══════════════════════════════════ */
.filter-bar{
  background:var(--white);border-radius:var(--radius);
  padding:16px 20px;border:1px solid var(--border);
  box-shadow:var(--shadow);margin-bottom:20px;
  display:flex;align-items:center;flex-wrap:wrap;gap:10px;
}
.filter-label{font-size:13px;font-weight:600;color:var(--muted);flex-shrink:0;white-space:nowrap}
.filter-cats{display:flex;flex-wrap:wrap;gap:7px;flex:1;min-width:0}
.cat-pill{
  padding:6px 14px;border-radius:100px;font-size:13px;font-weight:500;
  border:1.5px solid var(--border);background:transparent;color:var(--muted);
  cursor:pointer;transition:var(--trans);font-family:inherit;white-space:nowrap;
}
.cat-pill:hover{border-color:var(--accent);color:var(--accent2)}
.cat-pill.active{background:var(--accent);border-color:var(--accent);color:#fff;font-weight:600}
.search-wrap{position:relative;width:220px;flex-shrink:0}
.search-wrap input{
  width:100%;padding:8px 36px 8px 12px;
  border:1.5px solid var(--border);border-radius:100px;
  font-size:13px;font-family:inherit;
  color:var(--text);transition:var(--trans);direction:rtl;background:#fff;
}
.search-wrap input:focus{outline:none;border-color:var(--accent);box-shadow:0 0 0 3px rgba(232,160,32,.12)}
.search-wrap::after{
  content:'🔍';position:absolute;left:11px;top:50%;transform:translateY(-50%);
  font-size:13px;pointer-events:none;
}

/* ══ TABLE CARD ══════════════════════════════════ */
.table-card{
  background:var(--white);border-radius:var(--radius);
  border:1px solid var(--border);box-shadow:var(--shadow);overflow:hidden;
}
.table-card-head{
  display:flex;justify-content:space-between;align-items:center;
  padding:14px 20px;border-bottom:1px solid var(--border);flex-wrap:wrap;gap:8px;
}
.table-card-head h2{font-size:15px;font-weight:700;color:var(--navy);margin:0}
#iak-count{font-size:13px;color:var(--muted)}
#iak-count strong{color:var(--accent)}

/* ── Desktop table ──────────────────────────────── */
.iak-table{width:100%;border-collapse:collapse}
.iak-table thead th{
  background:var(--navy);color:#fff;
  padding:12px 18px;text-align:right;
  font-size:13px;font-weight:600;white-space:nowrap;
}
.iak-table thead th:first-child{width:48px;text-align:center}
.iak-table tbody tr{transition:background var(--trans)}
.iak-table tbody tr:nth-child(even){background:#FAFBFD}
.iak-table tbody tr:hover{background:#F0F6FF}
.iak-table td{
  padding:13px 18px;font-size:14px;color:var(--text);
  border-bottom:1px solid var(--border);
}
.iak-table td:first-child{text-align:center;color:var(--muted);font-size:12px}
.td-name{font-weight:600}
.cat-tag{
  display:inline-block;padding:3px 11px;border-radius:100px;
  background:#EEF2FF;color:#1A3C6E;font-size:12px;font-weight:500;
}
.td-price{font-weight:700;color:var(--navy);font-size:15px}
.td-date{font-size:12px;color:var(--muted)}
.td-unit{color:var(--muted);font-size:13px}
.no-results{padding:60px 20px;text-align:center;color:var(--muted);font-size:14px}

/* skeleton */
@keyframes shimmer{0%{background-position:-800px 0}100%{background-position:800px 0}}
.skeleton{
  background:linear-gradient(90deg,#f0f2f5 25%,#e8eaed 50%,#f0f2f5 75%);
  background-size:800px 100%;animation:shimmer 1.4s infinite;
  border-radius:4px;height:14px;
}

/* ── FOOTER ─────────────────────────────────────── */
.iak-footer{
  text-align:center;margin-top:40px;
  font-size:12px;color:var(--muted);line-height:2;
}
.iak-footer a{color:var(--accent2);text-decoration:none;font-weight:600}
.iak-footer a:hover{text-decoration:underline}

/* ══════════════════════════════════════════════════
   RESPONSIVE  —  card layout on mobile
══════════════════════════════════════════════════ */

/* Tablet: slightly tighten */
@media(max-width:900px){
  .iak-body{padding:20px 12px 50px}
  .filter-bar{padding:14px 16px}
  .search-wrap{width:100%}
}

/* Mobile: switch table → cards */
@media(max-width:640px){
  .header-inner{padding:18px 16px 0}
  .hero-strip{padding:0 16px 24px;margin-top:14px}
  .hero-meta{gap:8px}
  .meta-chip{font-size:11px;padding:5px 10px}
  .header-nav a{padding:7px 13px;font-size:12px}

  .filter-bar{padding:14px 14px;gap:8px}
  .filter-cats{gap:6px}
  .cat-pill{font-size:12px;padding:5px 12px}

  /* Hide normal thead */
  .iak-table thead{display:none}

  /* Each row becomes a card */
  .iak-table,
  .iak-table tbody,
  .iak-table tr,
  .iak-table td{display:block;width:100%}

  .iak-table tbody tr{
    border:1px solid var(--border);
    border-radius:10px;
    margin:0 12px 12px;
    width:calc(100% - 24px);
    padding:4px 0;
    background:#fff;
    box-shadow:0 1px 4px rgba(0,0,0,.05);
  }
  .iak-table tbody tr:nth-child(even){background:#fff}
  .iak-table tbody tr:hover{background:#F8FAFF}

  /* Hide row-number cell on mobile */
  .iak-table td:first-child{display:none}

  /* All cells: label on right, value on left */
  .iak-table td{
    display:flex;
    justify-content:space-between;
    align-items:center;
    padding:10px 16px;
    border-bottom:1px solid #F0F2F5;
    font-size:13px;
    text-align:right;
  }
  .iak-table td:last-child{border-bottom:none}

  /* data-label attribute shown as row header */
  .iak-table td::before{
    content:attr(data-label);
    font-size:11px;font-weight:600;
    color:var(--muted);
    text-align:left;
    flex-shrink:0;
    margin-left:12px;
    white-space:nowrap;
  }

  /* name cell: full-width header style */
  .iak-table td.td-name{
    background:linear-gradient(135deg,var(--navy),var(--navy2));
    color:#fff;font-size:14px;font-weight:700;
    border-radius:9px 9px 0 0;
    padding:13px 16px;
    justify-content:space-between;
    border-bottom:none;
  }
  .iak-table td.td-name::before{
    color:rgba(255,255,255,.5);font-size:11px;
  }

  /* price: highlight */
  .iak-table td.td-price{
    background:#FFFBF0;
    font-size:16px;font-weight:800;color:var(--accent2);
  }
  .iak-table td.td-price::before{color:var(--muted)}

  .td-unit,.td-date{display:flex}

  /* table-card: remove overflow constraint */
  .table-card{overflow:visible;background:transparent;border:none;box-shadow:none}
  .table-card-head{
    background:#fff;border-radius:var(--radius);border:1px solid var(--border);
    box-shadow:var(--shadow);margin-bottom:4px;
  }

  /* overflow wrapper not needed */
  .table-scroll{overflow:visible!important}
}

/* Very small phones */
@media(max-width:360px){
  .iak-table tbody tr{margin:0 8px 10px;width:calc(100% - 16px)}
}
</style>
</head>
<body>

<!-- ══ HEADER ══════════════════════════════════ -->
<header class="iak-header">
  <div class="header-inner">
    <div class="brand">
      <div>
        <div class="brand-name">IAK Plast</div>
        <div class="brand-tagline"><?= esc_html($tagline) ?></div>
      </div>
    </div>
    <nav class="header-nav">
      <a href="<?= esc_url($site_url) ?>">🌐 بازگشت به Kidioki</a>
    </nav>
  </div>
  <div class="hero-strip">
    <h1>لیست قیمت رسمی مواد اولیه</h1>
    <p>قیمت‌ها به‌روز و مستقیم از کارخانه اعلام می‌شوند. جهت اطمینان از آخرین قیمت با واحد فروش تماس بگیرید.</p>
    <div class="hero-meta">
      <div class="meta-chip">📅 آخرین به‌روزرسانی: <strong id="last-update">—</strong></div>
      <div class="meta-chip">📦 تعداد محصولات: <strong id="total-count">—</strong></div>
      <div class="meta-chip">☎️ <strong dir="ltr"><?= esc_html($phone) ?></strong></div>
    </div>
  </div>
</header>

<!-- ══ BODY ════════════════════════════════════ -->
<main class="iak-body">

  <div class="filter-bar">
    <span class="filter-label">دسته‌بندی:</span>
    <div class="filter-cats" id="filter-cats">
      <button class="cat-pill active" data-cat="all">همه</button>
    </div>
    <div class="search-wrap">
      <input type="text" id="search-input" placeholder="جستجوی محصول...">
    </div>
  </div>

  <div class="table-card">
    <div class="table-card-head">
      <h2>جدول قیمت‌ها</h2>
      <span id="iak-count">در حال بارگذاری...</span>
    </div>
    <div class="table-scroll" style="overflow-x:auto">
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
              <div style="padding:16px 0;display:grid;gap:10px">
                <?php for($i=0;$i<6;$i++): ?>
                <div style="display:grid;grid-template-columns:40px 2fr 1fr 1fr 1fr 1fr;gap:14px;padding:8px 18px">
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

<footer class="iak-footer">
  <p>© <?= date('Y') ?> IAK Plast — تمامی حقوق محفوظ است</p>
  <p><?= esc_html($foot_note) ?> | <a href="<?= esc_url($site_url) ?>">بازگشت به Kidioki</a></p>
</footer>

<script>
(function(){
  var ajaxUrl = '<?= esc_js($ajax_url) ?>';
  var allProducts = [], currentCat = 'all', searchTerm = '';

  function fmt(dt){
    if(!dt) return '—';
    try{var d=new Date(dt.replace(' ','T'));return d.toLocaleDateString('fa-IR',{year:'numeric',month:'short',day:'numeric'});}
    catch(e){return dt;}
  }
  function esc(s){var d=document.createElement('div');d.textContent=s||'';return d.innerHTML;}

  function render(){
    var filtered=allProducts.filter(function(p){
      var mc=currentCat==='all'||p.category===currentCat;
      var mq=!searchTerm||p.name.indexOf(searchTerm)>-1||p.category.indexOf(searchTerm)>-1;
      return mc&&mq;
    });
    document.getElementById('iak-count').innerHTML='نمایش <strong>'+filtered.length+'</strong> محصول';
    var tbody=document.getElementById('iak-tbody');
    if(!filtered.length){
      tbody.innerHTML='<tr><td colspan="6" class="no-results">محصولی یافت نشد 🔍</td></tr>';
      return;
    }
    tbody.innerHTML=filtered.map(function(p,i){
      return '<tr>'
        +'<td>'+(i+1)+'</td>'
        +'<td class="td-name" data-label="نام محصول">'+esc(p.name)+'</td>'
        +'<td data-label="دسته‌بندی"><span class="cat-tag">'+esc(p.category)+'</span></td>'
        +'<td class="td-unit" data-label="واحد">'+esc(p.unit)+'</td>'
        +'<td class="td-price" data-label="قیمت (ریال)">'+esc(p.price)+'</td>'
        +'<td class="td-date" data-label="آخرین آپدیت">'+fmt(p.updated_at)+'</td>'
        +'</tr>';
    }).join('');
  }

  function buildCats(){
    var cats=['all'];
    allProducts.forEach(function(p){if(p.category&&cats.indexOf(p.category)<0)cats.push(p.category);});
    var wrap=document.getElementById('filter-cats');
    wrap.innerHTML=cats.map(function(c){
      return '<button class="cat-pill'+(c===currentCat?' active':'')+'" data-cat="'+esc(c)+'">'+(c==='all'?'همه':esc(c))+'</button>';
    }).join('');
    wrap.querySelectorAll('.cat-pill').forEach(function(btn){
      btn.addEventListener('click',function(){
        currentCat=this.dataset.cat;
        wrap.querySelectorAll('.cat-pill').forEach(function(b){b.classList.remove('active');});
        this.classList.add('active');render();
      });
    });
  }

  function updateMeta(){
    document.getElementById('total-count').textContent=allProducts.length;
    if(allProducts.length){
      var latest=allProducts.slice().sort(function(a,b){return b.updated_at>a.updated_at?1:-1;})[0];
      document.getElementById('last-update').textContent=fmt(latest.updated_at);
    }
  }

  fetch(ajaxUrl+'?action=iakp_pub_products')
    .then(function(r){return r.json();})
    .then(function(res){
      if(!res.success)return;
      allProducts=res.data;buildCats();render();updateMeta();
    });

  document.getElementById('search-input').addEventListener('input',function(){
    searchTerm=this.value.trim();render();
  });
})();
</script>
<?php wp_footer(); ?>
</body>
</html>
