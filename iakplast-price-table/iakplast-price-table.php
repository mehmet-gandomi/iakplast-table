<?php
/**
 * Plugin Name: IAK Plast Price Table
 * Plugin URI:  https://iakplast.com
 * Description: جدول قیمت مواد اولیه IAK Plast – مدیریت محصولات، دسته‌بندی‌ها و صفحه انتخاب ورودی
 * Version:     1.0.0
 * Author:      IAKPlast
 * Text Domain: iakplast
 * Requires WP: 5.8
 * Requires PHP: 7.4
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'IAKP_VERSION',      '1.0.0' );
define( 'IAKP_PLUGIN_DIR',   plugin_dir_path( __FILE__ ) );
define( 'IAKP_PLUGIN_URL',   plugin_dir_url( __FILE__ ) );
define( 'IAKP_TABLE',        'iakplast_products' );
define( 'IAKP_CAT_TABLE',    'iakplast_categories' );

/* ════════════════════════════════════════════════════
   §1  ACTIVATION / DEACTIVATION
════════════════════════════════════════════════════ */
register_activation_hook( __FILE__, 'iakp_activate' );

function iakp_activate() {
    global $wpdb;
    $cs = $wpdb->get_charset_collate();
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';

    // categories table
    dbDelta( "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}" . IAKP_CAT_TABLE . " (
        id        BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        name      VARCHAR(255)    NOT NULL DEFAULT '',
        slug      VARCHAR(255)    NOT NULL DEFAULT '',
        sort      INT             NOT NULL DEFAULT 0,
        PRIMARY KEY (id),
        UNIQUE KEY slug (slug)
    ) $cs;" );

    // products table
    dbDelta( "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}" . IAKP_TABLE . " (
        id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        name        VARCHAR(255)    NOT NULL DEFAULT '',
        category_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
        unit        VARCHAR(100)    NOT NULL DEFAULT '',
        price       VARCHAR(100)    NOT NULL DEFAULT '',
        note        TEXT,
        sort        INT             NOT NULL DEFAULT 0,
        updated_at  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY category_id (category_id)
    ) $cs;" );

    // create the price-table page once
    if ( ! get_option('iakp_page_id') ) {
        $pid = wp_insert_post([
            'post_title'   => 'لیست قیمت محصولات',
            'post_name'    => 'price-table',
            'post_status'  => 'publish',
            'post_type'    => 'page',
            'post_content' => '',
        ]);
        update_option( 'iakp_page_id', (int) $pid );
    }

    // insert a sample category if empty
    $has_cat = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}" . IAKP_CAT_TABLE );
    if ( ! $has_cat ) {
        $wpdb->insert( $wpdb->prefix . IAKP_CAT_TABLE, [
            'name' => 'لوله و اتصالات', 'slug' => 'pipe-fittings', 'sort' => 1,
        ]);
        $wpdb->insert( $wpdb->prefix . IAKP_CAT_TABLE, [
            'name' => 'مخازن پلی‌اتیلن', 'slug' => 'tanks', 'sort' => 2,
        ]);
    }

    flush_rewrite_rules();
}

/* ════════════════════════════════════════════════════
   §2  HOMEPAGE CHOICE SCREEN
════════════════════════════════════════════════════ */
add_action( 'init',              'iakp_handle_skip' );
add_action( 'template_redirect', 'iakp_maybe_show_choice' );

function iakp_handle_skip() {
    if ( isset( $_GET['skip_choice'] ) ) {
        setcookie( 'iakp_choice', 'site', 0, COOKIEPATH, COOKIE_DOMAIN );
    }
}

function iakp_maybe_show_choice() {
    if ( is_admin() || wp_doing_ajax() ) return;
    // Only intercept the static front page, never blog/archive/other pages
    if ( ! is_front_page() ) return;
    // Session cookie set when user clicked a button — let them browse freely
    if ( ! empty( $_COOKIE['iakp_choice'] ) ) return;
    // ?skip_choice=1 also bypasses (used by direct links)
    if ( isset( $_GET['skip_choice'] ) ) return;
    iakp_render_choice_page();
    exit;
}

/* ════════════════════════════════════════════════════
   §3  TEMPLATE OVERRIDE (standalone – no WP chrome)
════════════════════════════════════════════════════ */
add_filter( 'template_include', 'iakp_override_template' );

function iakp_override_template( $template ) {
    $pid = (int) get_option('iakp_page_id');
    if ( $pid && is_page( $pid ) ) {
        return IAKP_PLUGIN_DIR . 'templates/price-table.php';
    }
    return $template;
}

/* ════════════════════════════════════════════════════
   §4  ADMIN MENU  (two sub-pages)
════════════════════════════════════════════════════ */
add_action( 'admin_menu', 'iakp_admin_menu' );

function iakp_admin_menu() {
    add_menu_page(
        'IAK Plast',
        'IAK Plast',
        'manage_options',
        'iakplast',
        'iakp_page_products',
        'dashicons-list-view',
        25
    );
    add_submenu_page(
        'iakplast',
        'مدیریت محصولات',
        'محصولات',
        'manage_options',
        'iakplast',
        'iakp_page_products'
    );
    add_submenu_page(
        'iakplast',
        'مدیریت دسته‌بندی‌ها',
        'دسته‌بندی‌ها',
        'manage_options',
        'iakplast-cats',
        'iakp_page_cats'
    );
    add_submenu_page(
        'iakplast',
        'تنظیمات',
        'تنظیمات',
        'manage_options',
        'iakplast-settings',
        'iakp_page_settings'
    );
}

/* ════════════════════════════════════════════════════
   §5  ADMIN STYLES / SCRIPTS  (shared)
════════════════════════════════════════════════════ */
add_action( 'admin_enqueue_scripts', 'iakp_admin_assets' );

function iakp_admin_assets( $hook ) {
    if ( strpos($hook, 'iakplast') === false ) return;
    wp_enqueue_style(
        'iakp-admin',
        IAKP_PLUGIN_URL . 'assets/admin.css',
        [],
        IAKP_VERSION
    );
}

/* ════════════════════════════════════════════════════
   §6  AJAX – PRODUCTS
════════════════════════════════════════════════════ */
add_action( 'wp_ajax_iakp_get_products',    'iakp_ajax_get_products' );
add_action( 'wp_ajax_iakp_save_product',    'iakp_ajax_save_product' );
add_action( 'wp_ajax_iakp_delete_product',  'iakp_ajax_delete_product' );

function iakp_ajax_get_products() {
    check_ajax_referer( 'iakp_admin_nonce', 'nonce' );
    if ( ! current_user_can('manage_options') ) wp_die('Forbidden');

    global $wpdb;
    $p  = $wpdb->prefix;
    $rows = $wpdb->get_results(
        "SELECT pr.*, COALESCE(c.name,'—') AS category_name
         FROM {$p}" . IAKP_TABLE . " pr
         LEFT JOIN {$p}" . IAKP_CAT_TABLE . " c ON c.id = pr.category_id
         ORDER BY pr.sort ASC, pr.id DESC"
    );
    wp_send_json_success( $rows );
}

function iakp_ajax_save_product() {
    check_ajax_referer( 'iakp_admin_nonce', 'nonce' );
    if ( ! current_user_can('manage_options') ) wp_die('Forbidden');

    global $wpdb;
    $t   = $wpdb->prefix . IAKP_TABLE;
    $id  = (int)( $_POST['id'] ?? 0 );
    $data = [
        'name'        => sanitize_text_field( $_POST['name']        ?? '' ),
        'category_id' => (int)( $_POST['category_id'] ?? 0 ),
        'unit'        => sanitize_text_field( $_POST['unit']        ?? '' ),
        'price'       => sanitize_text_field( $_POST['price']       ?? '' ),
        'note'        => sanitize_textarea_field( $_POST['note']    ?? '' ),
        'sort'        => (int)( $_POST['sort'] ?? 0 ),
    ];

    if ( $id ) {
        $wpdb->update( $t, $data, ['id' => $id] );
    } else {
        $wpdb->insert( $t, $data );
        $id = $wpdb->insert_id;
    }
    wp_send_json_success( ['id' => $id] );
}

function iakp_ajax_delete_product() {
    check_ajax_referer( 'iakp_admin_nonce', 'nonce' );
    if ( ! current_user_can('manage_options') ) wp_die('Forbidden');

    global $wpdb;
    $id = (int)( $_POST['id'] ?? 0 );
    $wpdb->delete( $wpdb->prefix . IAKP_TABLE, ['id' => $id] );
    wp_send_json_success();
}

/* ════════════════════════════════════════════════════
   §7  AJAX – CATEGORIES
════════════════════════════════════════════════════ */
add_action( 'wp_ajax_iakp_get_cats',    'iakp_ajax_get_cats' );
add_action( 'wp_ajax_iakp_save_cat',    'iakp_ajax_save_cat' );
add_action( 'wp_ajax_iakp_delete_cat',  'iakp_ajax_delete_cat' );

function iakp_ajax_get_cats() {
    check_ajax_referer( 'iakp_admin_nonce', 'nonce' );
    global $wpdb;
    $p  = $wpdb->prefix;
    $rows = $wpdb->get_results(
        "SELECT c.*, COUNT(pr.id) AS product_count
         FROM {$p}" . IAKP_CAT_TABLE . " c
         LEFT JOIN {$p}" . IAKP_TABLE . " pr ON pr.category_id = c.id
         GROUP BY c.id ORDER BY c.sort ASC, c.id ASC"
    );
    wp_send_json_success( $rows );
}

function iakp_ajax_save_cat() {
    check_ajax_referer( 'iakp_admin_nonce', 'nonce' );
    if ( ! current_user_can('manage_options') ) wp_die('Forbidden');

    global $wpdb;
    $t   = $wpdb->prefix . IAKP_CAT_TABLE;
    $id  = (int)( $_POST['id'] ?? 0 );
    $name = sanitize_text_field( $_POST['name'] ?? '' );
    $slug = sanitize_title( $_POST['slug'] ?? $name );
    $sort = (int)( $_POST['sort'] ?? 0 );

    if ( $id ) {
        $wpdb->update( $t, ['name' => $name, 'slug' => $slug, 'sort' => $sort], ['id' => $id] );
    } else {
        $wpdb->insert( $t, ['name' => $name, 'slug' => $slug, 'sort' => $sort] );
        $id = $wpdb->insert_id;
    }
    wp_send_json_success( ['id' => $id] );
}

function iakp_ajax_delete_cat() {
    check_ajax_referer( 'iakp_admin_nonce', 'nonce' );
    if ( ! current_user_can('manage_options') ) wp_die('Forbidden');

    global $wpdb;
    $id = (int)( $_POST['id'] ?? 0 );
    // move orphaned products to uncategorised
    $wpdb->update( $wpdb->prefix . IAKP_TABLE, ['category_id' => 0], ['category_id' => $id] );
    $wpdb->delete( $wpdb->prefix . IAKP_CAT_TABLE, ['id' => $id] );
    wp_send_json_success();
}

/* ════════════════════════════════════════════════════
   §8  PUBLIC AJAX – product list for front-end
════════════════════════════════════════════════════ */
add_action( 'wp_ajax_iakp_pub_products',        'iakp_pub_products' );
add_action( 'wp_ajax_nopriv_iakp_pub_products', 'iakp_pub_products' );

function iakp_pub_products() {
    global $wpdb;
    $p = $wpdb->prefix;
    $rows = $wpdb->get_results(
        "SELECT pr.id, pr.name, pr.unit, pr.price, pr.note, pr.updated_at,
                COALESCE(c.name,'سایر') AS category, COALESCE(c.slug,'other') AS cat_slug
         FROM {$p}" . IAKP_TABLE . " pr
         LEFT JOIN {$p}" . IAKP_CAT_TABLE . " c ON c.id = pr.category_id
         ORDER BY c.sort ASC, pr.sort ASC, pr.name ASC"
    );
    wp_send_json_success( $rows );
}

/* ════════════════════════════════════════════════════
   §9  ADMIN PAGE – PRODUCTS
════════════════════════════════════════════════════ */
function iakp_page_products() {
    global $wpdb;
    $nonce    = wp_create_nonce('iakp_admin_nonce');
    $ajax_url = admin_url('admin-ajax.php');
    $price_url = get_permalink( (int) get_option('iakp_page_id') );

    // build category dropdown data
    $cats = $wpdb->get_results(
        "SELECT id, name FROM {$wpdb->prefix}" . IAKP_CAT_TABLE . " ORDER BY sort ASC, name ASC"
    );
    $cats_json = json_encode( $cats, JSON_UNESCAPED_UNICODE );
    ?>
<div class="wrap iakp-admin" dir="rtl">
<?php iakp_admin_header('products', $price_url); ?>

<div class="iakp-page-body">
  <div class="iakp-toolbar">
    <div class="iakp-toolbar-left">
      <button class="iakp-btn iakp-btn-primary" id="btn-add-product">
        <span class="dashicons dashicons-plus-alt2"></span> افزودن محصول
      </button>
      <div class="iakp-search-wrap">
        <span class="dashicons dashicons-search"></span>
        <input type="text" id="prod-search" placeholder="جستجو در محصولات...">
      </div>
    </div>
    <div class="iakp-toolbar-right">
      <select id="prod-filter-cat" class="iakp-select">
        <option value="0">همه دسته‌بندی‌ها</option>
      </select>
    </div>
  </div>

  <div class="iakp-card">
    <div class="iakp-card-head">
      <h2>لیست محصولات</h2>
      <span id="prod-count" class="iakp-badge">—</span>
    </div>
    <table class="iakp-table" id="prod-table">
      <thead>
        <tr>
          <th style="width:48px">#</th>
          <th>نام محصول</th>
          <th>دسته‌بندی</th>
          <th>واحد</th>
          <th>قیمت (ریال)</th>
          <th>آخرین آپدیت</th>
          <th style="width:130px">عملیات</th>
        </tr>
      </thead>
      <tbody id="prod-tbody">
        <tr><td colspan="7" class="iakp-loading">در حال بارگذاری...</td></tr>
      </tbody>
    </table>
  </div>
</div>

<!-- ── Modal ── -->
<div class="iakp-overlay" id="prod-modal">
  <div class="iakp-modal">
    <div class="iakp-modal-head">
      <h3 id="prod-modal-title">افزودن محصول جدید</h3>
      <button class="iakp-modal-close" onclick="iakpCloseModal('prod')">✕</button>
    </div>
    <div class="iakp-modal-body">
      <input type="hidden" id="prod-id">
      <div class="iakp-form-row">
        <div class="iakp-field">
          <label>نام محصول <span class="req">*</span></label>
          <input type="text" id="prod-name" placeholder="مثلاً: لوله پلی‌اتیلن ۲ اینچ">
        </div>
        <div class="iakp-field">
          <label>دسته‌بندی</label>
          <select id="prod-cat" class="iakp-select"></select>
        </div>
      </div>
      <div class="iakp-form-row">
        <div class="iakp-field">
          <label>واحد <span class="req">*</span></label>
          <input type="text" id="prod-unit" placeholder="مثلاً: متر / کیلوگرم / عدد">
        </div>
        <div class="iakp-field">
          <label>قیمت (ریال) <span class="req">*</span></label>
          <input type="text" id="prod-price" placeholder="مثلاً: ۱۲۵,۰۰۰">
        </div>
      </div>
      <div class="iakp-field">
        <label>توضیحات (اختیاری)</label>
        <textarea id="prod-note" rows="2" placeholder="هرگونه توضیح تکمیلی..."></textarea>
      </div>
      <div class="iakp-field half">
        <label>ترتیب نمایش</label>
        <input type="number" id="prod-sort" value="0" min="0">
      </div>
    </div>
    <div class="iakp-modal-foot">
      <button class="iakp-btn iakp-btn-ghost" onclick="iakpCloseModal('prod')">انصراف</button>
      <button class="iakp-btn iakp-btn-primary" onclick="iakpSaveProduct()">
        <span class="dashicons dashicons-saved"></span> ذخیره
      </button>
    </div>
  </div>
</div>

<script>
var IAKP_ADMIN = {
  ajax: '<?= $ajax_url ?>',
  nonce: '<?= $nonce ?>',
  cats: <?= $cats_json ?>
};

/* ── helpers ── */
function esc(s){var d=document.createElement('div');d.textContent=String(s||'');return d.innerHTML;}
function fmtDate(dt){
  if(!dt) return '—';
  try{
    var d=new Date(dt.replace(' ','T'));
    return d.toLocaleDateString('fa-IR',{year:'numeric',month:'short',day:'numeric'});
  }catch(e){return dt;}
}
function post(action,data,cb){
  var fd=new FormData();
  fd.append('action',action);fd.append('nonce',IAKP_ADMIN.nonce);
  Object.keys(data).forEach(function(k){fd.append(k,data[k]);});
  fetch(IAKP_ADMIN.ajax,{method:'POST',body:fd}).then(function(r){return r.json();}).then(cb);
}
function openModal(id){document.getElementById(id).classList.add('open');}
function iakpCloseModal(pfx){
  document.getElementById(pfx+'-modal').classList.remove('open');
  document.getElementById(pfx+'-id').value='';
}

/* ── fill category selects ── */
(function(){
  var filterSel=document.getElementById('prod-filter-cat');
  var formSel=document.getElementById('prod-cat');
  IAKP_ADMIN.cats.forEach(function(c){
    filterSel.innerHTML+='<option value="'+c.id+'">'+esc(c.name)+'</option>';
    formSel.innerHTML+='<option value="'+c.id+'">'+esc(c.name)+'</option>';
  });
})();

/* ── load & render products ── */
var _products=[];
function iakpLoadProducts(){
  post('iakp_get_products',{},function(res){
    if(!res.success)return;
    _products=res.data;
    iakpRenderProducts();
  });
}
function iakpRenderProducts(){
  var filterCat=parseInt(document.getElementById('prod-filter-cat').value)||0;
  var q=document.getElementById('prod-search').value.toLowerCase().trim();
  var list=_products.filter(function(p){
    return (!filterCat||parseInt(p.category_id)===filterCat)&&
           (!q||(p.name||'').toLowerCase().indexOf(q)>-1);
  });
  document.getElementById('prod-count').textContent=list.length+' محصول';
  var html=list.length?'':'<tr><td colspan="7" class="iakp-empty">محصولی یافت نشد</td></tr>';
  list.forEach(function(p,i){
    html+='<tr>'
      +'<td class="tc text-muted">'+(i+1)+'</td>'
      +'<td><strong>'+esc(p.name)+'</strong>'+(p.note?'<br><small class="text-muted">'+esc(p.note)+'</small>':'')+'</td>'
      +'<td><span class="iakp-tag">'+esc(p.category_name)+'</span></td>'
      +'<td class="text-muted">'+esc(p.unit)+'</td>'
      +'<td class="fw-bold text-navy">'+esc(p.price)+'</td>'
      +'<td class="text-muted small">'+fmtDate(p.updated_at)+'</td>'
      +'<td class="actions">'
        +'<button class="iakp-btn-icon edit" title="ویرایش" onclick="iakpEditProduct('+p.id+')">✏️</button>'
        +'<button class="iakp-btn-icon del"  title="حذف"   onclick="iakpDelProduct('+p.id+')">🗑️</button>'
      +'</td>'
    +'</tr>';
  });
  document.getElementById('prod-tbody').innerHTML=html;
}
document.getElementById('prod-search').addEventListener('input',iakpRenderProducts);
document.getElementById('prod-filter-cat').addEventListener('change',iakpRenderProducts);
document.getElementById('btn-add-product').addEventListener('click',function(){
  document.getElementById('prod-modal-title').textContent='افزودن محصول جدید';
  openModal('prod-modal');
});

function iakpEditProduct(id){
  var p=_products.find(function(x){return parseInt(x.id)===id;});
  if(!p) return;
  document.getElementById('prod-modal-title').textContent='ویرایش محصول';
  document.getElementById('prod-id').value=p.id;
  document.getElementById('prod-name').value=p.name;
  document.getElementById('prod-cat').value=p.category_id;
  document.getElementById('prod-unit').value=p.unit;
  document.getElementById('prod-price').value=p.price;
  document.getElementById('prod-note').value=p.note||'';
  document.getElementById('prod-sort').value=p.sort;
  openModal('prod-modal');
}
function iakpSaveProduct(){
  var name=document.getElementById('prod-name').value.trim();
  if(!name){alert('نام محصول الزامی است.');return;}
  post('iakp_save_product',{
    id:document.getElementById('prod-id').value,
    name:name,
    category_id:document.getElementById('prod-cat').value,
    unit:document.getElementById('prod-unit').value,
    price:document.getElementById('prod-price').value,
    note:document.getElementById('prod-note').value,
    sort:document.getElementById('prod-sort').value,
  },function(res){if(res.success){iakpCloseModal('prod');iakpLoadProducts();}});
}
function iakpDelProduct(id){
  if(!confirm('آیا از حذف این محصول اطمینان دارید؟'))return;
  post('iakp_delete_product',{id:id},function(res){if(res.success)iakpLoadProducts();});
}

iakpLoadProducts();
</script>
</div>
    <?php
}

/* ════════════════════════════════════════════════════
   §10  ADMIN PAGE – CATEGORIES
════════════════════════════════════════════════════ */
function iakp_page_cats() {
    $nonce    = wp_create_nonce('iakp_admin_nonce');
    $ajax_url = admin_url('admin-ajax.php');
    $price_url = get_permalink( (int) get_option('iakp_page_id') );
    ?>
<div class="wrap iakp-admin" dir="rtl">
<?php iakp_admin_header('cats', $price_url); ?>

<div class="iakp-page-body">
  <div class="iakp-toolbar">
    <button class="iakp-btn iakp-btn-primary" id="btn-add-cat">
      <span class="dashicons dashicons-plus-alt2"></span> افزودن دسته‌بندی
    </button>
  </div>

  <div class="iakp-card">
    <div class="iakp-card-head">
      <h2>دسته‌بندی‌های محصولات</h2>
      <span id="cat-count" class="iakp-badge">—</span>
    </div>
    <table class="iakp-table">
      <thead>
        <tr>
          <th style="width:48px">#</th>
          <th>نام دسته‌بندی</th>
          <th>اسلاگ</th>
          <th>تعداد محصولات</th>
          <th>ترتیب</th>
          <th style="width:130px">عملیات</th>
        </tr>
      </thead>
      <tbody id="cat-tbody">
        <tr><td colspan="6" class="iakp-loading">در حال بارگذاری...</td></tr>
      </tbody>
    </table>
  </div>
</div>

<!-- Modal -->
<div class="iakp-overlay" id="cat-modal">
  <div class="iakp-modal" style="max-width:400px">
    <div class="iakp-modal-head">
      <h3 id="cat-modal-title">افزودن دسته‌بندی</h3>
      <button class="iakp-modal-close" onclick="iakpCloseModal('cat')">✕</button>
    </div>
    <div class="iakp-modal-body">
      <input type="hidden" id="cat-id">
      <div class="iakp-field">
        <label>نام دسته‌بندی <span class="req">*</span></label>
        <input type="text" id="cat-name" placeholder="مثلاً: لوله و اتصالات">
      </div>
      <div class="iakp-field">
        <label>اسلاگ (انگلیسی)</label>
        <input type="text" id="cat-slug" placeholder="مثلاً: pipe-fittings" dir="ltr">
      </div>
      <div class="iakp-field half">
        <label>ترتیب نمایش</label>
        <input type="number" id="cat-sort" value="0" min="0">
      </div>
    </div>
    <div class="iakp-modal-foot">
      <button class="iakp-btn iakp-btn-ghost" onclick="iakpCloseModal('cat')">انصراف</button>
      <button class="iakp-btn iakp-btn-primary" onclick="iakpSaveCat()">
        <span class="dashicons dashicons-saved"></span> ذخیره
      </button>
    </div>
  </div>
</div>

<script>
var IAKP_CATS_NONCE = '<?= $nonce ?>';
var IAKP_CATS_AJAX  = '<?= $ajax_url ?>';
var _cats = [];

function esc(s){var d=document.createElement('div');d.textContent=String(s||'');return d.innerHTML;}
function post(action,data,cb){
  var fd=new FormData();fd.append('action',action);fd.append('nonce',IAKP_CATS_NONCE);
  Object.keys(data).forEach(function(k){fd.append(k,data[k]);});
  fetch(IAKP_CATS_AJAX,{method:'POST',body:fd}).then(function(r){return r.json();}).then(cb);
}
function openModal(id){document.getElementById(id).classList.add('open');}
function iakpCloseModal(pfx){document.getElementById(pfx+'-modal').classList.remove('open');document.getElementById(pfx+'-id').value='';}

function iakpLoadCats(){
  post('iakp_get_cats',{},function(res){
    if(!res.success)return;
    _cats=res.data;
    document.getElementById('cat-count').textContent=_cats.length+' دسته';
    var html=_cats.length?'':'<tr><td colspan="6" class="iakp-empty">دسته‌بندی‌ای وجود ندارد</td></tr>';
    _cats.forEach(function(c,i){
      html+='<tr>'
        +'<td class="tc text-muted">'+(i+1)+'</td>'
        +'<td><strong>'+esc(c.name)+'</strong></td>'
        +'<td><code>'+esc(c.slug)+'</code></td>'
        +'<td><span class="iakp-badge">'+c.product_count+'</span></td>'
        +'<td class="text-muted">'+c.sort+'</td>'
        +'<td class="actions">'
          +'<button class="iakp-btn-icon edit" onclick="iakpEditCat('+c.id+')">✏️</button>'
          +'<button class="iakp-btn-icon del"  onclick="iakpDelCat('+c.id+')">🗑️</button>'
        +'</td>'
      +'</tr>';
    });
    document.getElementById('cat-tbody').innerHTML=html;
  });
}
document.getElementById('btn-add-cat').addEventListener('click',function(){
  document.getElementById('cat-modal-title').textContent='افزودن دسته‌بندی';
  openModal('cat-modal');
});
function iakpEditCat(id){
  var c=_cats.find(function(x){return parseInt(x.id)===id;});
  if(!c)return;
  document.getElementById('cat-modal-title').textContent='ویرایش دسته‌بندی';
  document.getElementById('cat-id').value=c.id;
  document.getElementById('cat-name').value=c.name;
  document.getElementById('cat-slug').value=c.slug;
  document.getElementById('cat-sort').value=c.sort;
  openModal('cat-modal');
}
function iakpSaveCat(){
  var name=document.getElementById('cat-name').value.trim();
  if(!name){alert('نام دسته‌بندی الزامی است.');return;}
  post('iakp_save_cat',{
    id:document.getElementById('cat-id').value,
    name:name,
    slug:document.getElementById('cat-slug').value,
    sort:document.getElementById('cat-sort').value,
  },function(res){if(res.success){iakpCloseModal('cat');iakpLoadCats();}});
}
function iakpDelCat(id){
  if(!confirm('آیا این دسته‌بندی حذف شود؟ محصولات آن بدون دسته خواهند شد.'))return;
  post('iakp_delete_cat',{id:id},function(res){if(res.success)iakpLoadCats();});
}
iakpLoadCats();
</script>
</div>
    <?php
}

/* ════════════════════════════════════════════════════
   §11  ADMIN PAGE – SETTINGS
════════════════════════════════════════════════════ */
function iakp_page_settings() {
    if ( $_SERVER['REQUEST_METHOD'] === 'POST' && check_admin_referer('iakp_settings') ) {
        update_option( 'iakp_phone',   sanitize_text_field( $_POST['phone']   ?? '' ) );
        update_option( 'iakp_tagline', sanitize_text_field( $_POST['tagline'] ?? '' ) );
        update_option( 'iakp_note',    sanitize_textarea_field( $_POST['note'] ?? '' ) );
        echo '<div class="notice notice-success"><p>تنظیمات با موفقیت ذخیره شد.</p></div>';
    }
    $phone   = get_option('iakp_phone',   '+98 21 XXXX XXXX');
    $tagline = get_option('iakp_tagline', 'تولیدکننده لوله و اتصالات پلی‌اتیلن');
    $note    = get_option('iakp_note',    'قیمت‌ها به ریال بوده و ممکن است بدون اطلاع قبلی تغییر کنند.');
    $price_url = get_permalink( (int) get_option('iakp_page_id') );
    ?>
<div class="wrap iakp-admin" dir="rtl">
<?php iakp_admin_header('settings', $price_url); ?>
<div class="iakp-page-body">
  <div class="iakp-card" style="max-width:580px">
    <div class="iakp-card-head"><h2>تنظیمات صفحه قیمت‌نامه</h2></div>
    <form method="post" style="padding:24px;display:grid;gap:18px">
      <?php wp_nonce_field('iakp_settings'); ?>
      <div class="iakp-field">
        <label>شماره تماس</label>
        <input type="text" name="phone" value="<?= esc_attr($phone) ?>" dir="ltr">
      </div>
      <div class="iakp-field">
        <label>شعار / توضیح برند</label>
        <input type="text" name="tagline" value="<?= esc_attr($tagline) ?>">
      </div>
      <div class="iakp-field">
        <label>یادداشت پایین صفحه قیمت</label>
        <textarea name="note" rows="3"><?= esc_textarea($note) ?></textarea>
      </div>
      <div>
        <button type="submit" class="iakp-btn iakp-btn-primary">
          <span class="dashicons dashicons-saved"></span> ذخیره تنظیمات
        </button>
      </div>
    </form>
  </div>
</div>
</div>
    <?php
}

/* ════════════════════════════════════════════════════
   §12  SHARED ADMIN HEADER COMPONENT
════════════════════════════════════════════════════ */
function iakp_admin_header( $active, $price_url ) {
    $tabs = [
        'products' => ['url' => admin_url('admin.php?page=iakplast'),          'label' => 'محصولات',       'icon' => 'dashicons-list-view'],
        'cats'     => ['url' => admin_url('admin.php?page=iakplast-cats'),     'label' => 'دسته‌بندی‌ها',  'icon' => 'dashicons-category'],
        'settings' => ['url' => admin_url('admin.php?page=iakplast-settings'), 'label' => 'تنظیمات',       'icon' => 'dashicons-admin-settings'],
    ];
    ?>
<div class="iakp-admin-header">
  <div class="iakp-admin-brand">
    <div>
      <div class="iakp-admin-title">IAK Plast</div>
      <div class="iakp-admin-sub">مدیریت قیمت‌نامه محصولات</div>
    </div>
  </div>
  <a href="<?= esc_url($price_url) ?>" target="_blank" class="iakp-btn iakp-btn-outline iakp-btn-sm">
    <span class="dashicons dashicons-external"></span> مشاهده صفحه
  </a>
</div>
<nav class="iakp-tabs">
  <?php foreach ($tabs as $key => $t): ?>
  <a href="<?= esc_url($t['url']) ?>" class="iakp-tab<?= $active === $key ? ' active' : '' ?>">
    <span class="dashicons <?= $t['icon'] ?>"></span> <?= $t['label'] ?>
  </a>
  <?php endforeach; ?>
</nav>
    <?php
}

/* ════════════════════════════════════════════════════
   §13  CHOICE PAGE  (standalone HTML)
════════════════════════════════════════════════════ */
function iakp_render_choice_page() {
    $price_url = get_permalink( (int) get_option('iakp_page_id') );
    $site_url  = home_url('/?skip_choice=1');
    $font_url  = IAKP_PLUGIN_URL . 'assets/RaviVF.ttf';
    ?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>IAK Plast – خوش آمدید</title>
<style>
@font-face{
  font-family:'Ravi';
  src:url('<?= esc_url($font_url) ?>') format('truetype');
  font-weight:100 900;font-style:normal;font-display:swap;
}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{
  --navy:#0B1F3A;
  --blue:#1A3C6E;
  --accent:#E8A020;
  --accent2:#C8780A;
  --text:#1E2D40;
  --muted:#6B7C93;
  --border:#E2E8F0;
  --bg:#F0F4F8;
}
html,body{
  height:100%;font-family:'Ravi',Tahoma,sans-serif;
  direction:rtl;color:var(--text);
}
body{
  display:flex;align-items:center;justify-content:center;min-height:100vh;
  background:var(--bg);
  position:relative;overflow:hidden;
}
/* subtle light bg shapes */
.orb{position:fixed;border-radius:50%;pointer-events:none;z-index:0}
.orb-1{
  width:600px;height:600px;
  background:radial-gradient(circle,rgba(26,60,110,.07) 0%,transparent 70%);
  top:-150px;right:-150px;
}
.orb-2{
  width:500px;height:500px;
  background:radial-gradient(circle,rgba(232,160,32,.08) 0%,transparent 70%);
  bottom:-100px;left:-100px;
}

.card{
  position:relative;z-index:1;
  background:#fff;
  border:1px solid var(--border);
  border-radius:24px;
  padding:48px 40px 40px;
  max-width:440px;width:90%;
  box-shadow:0 8px 40px rgba(11,31,58,.08),0 2px 8px rgba(11,31,58,.04);
  text-align:center;
}

.logo-name{
  font-size:30px;font-weight:800;
  color:var(--navy);letter-spacing:.5px;
}
.logo-sub{
  font-size:11px;color:var(--muted);
  letter-spacing:2px;text-transform:uppercase;margin-top:5px;
}

.divider-line{
  width:40px;height:3px;border-radius:2px;
  background:linear-gradient(90deg,var(--accent),var(--accent2));
  margin:20px auto;
}

.card-title{
  font-size:16px;font-weight:700;color:var(--navy);
  margin-bottom:6px;
}
.card-sub{
  font-size:13px;color:var(--muted);line-height:1.8;
  margin-bottom:28px;
}

.choices{display:grid;gap:12px}

.choice-btn{
  display:flex;align-items:center;gap:14px;
  padding:18px 20px;border-radius:14px;
  border:1.5px solid var(--border);
  background:#fff;
  color:var(--text);text-decoration:none;
  transition:all .2s ease;text-align:right;
  box-shadow:0 2px 8px rgba(0,0,0,.04);
}
.choice-btn:hover{
  border-color:#c5d2e0;
  box-shadow:0 6px 24px rgba(11,31,58,.1);
  transform:translateY(-2px);
}
.choice-btn.cta{
  background:linear-gradient(135deg,var(--accent) 0%,var(--accent2) 100%);
  border-color:transparent;color:#fff;
  box-shadow:0 6px 24px rgba(232,160,32,.35);
}
.choice-btn.cta:hover{
  box-shadow:0 10px 36px rgba(232,160,32,.5);
  transform:translateY(-2px);
}
.choice-btn.cta .btn-desc{color:rgba(255,255,255,.8)}

.btn-icon{
  width:44px;height:44px;border-radius:12px;
  background:rgba(11,31,58,.06);
  display:flex;align-items:center;justify-content:center;
  font-size:21px;flex-shrink:0;
}
.choice-btn.cta .btn-icon{background:rgba(255,255,255,.2)}

.btn-text{flex:1;text-align:right}
.btn-title{font-size:14px;font-weight:700;display:block;color:inherit}
.btn-desc{font-size:12px;color:var(--muted);margin-top:3px;display:block;line-height:1.5}

.sep{
  display:flex;align-items:center;gap:10px;
  color:var(--muted);font-size:12px;margin:2px 0;
}
.sep::before,.sep::after{
  content:'';flex:1;height:1px;background:var(--border);
}
</style>
</head>
<body>
<div class="orb orb-1"></div>
<div class="orb orb-2"></div>

<div class="card">
  <div class="logo-name">IAK Plast</div>
  <div class="logo-sub">لیست قیمت رسمی</div>
  <div class="divider-line"></div>

  <p class="card-title">به کدام بخش می‌روید؟</p>
  <p class="card-sub">یکی از گزینه‌های زیر را انتخاب کنید</p>

  <div class="choices">

    <a href="<?= esc_url($price_url) ?>" class="choice-btn cta" onclick="setChoice()">
      <div class="btn-icon">📋</div>
      <div class="btn-text">
        <span class="btn-title">لیست قیمت مواد اولیه</span>
        <span class="btn-desc">مشاهده قیمت‌نامه به‌روز مواد اولیه IAK Plast</span>
      </div>
    </a>

    <div class="sep">یا</div>

    <a href="<?= esc_url($site_url) ?>" class="choice-btn" onclick="setChoice()">
      <div class="btn-icon">🧸</div>
      <div class="btn-text">
        <span class="btn-title">Kidioki</span>
        <span class="btn-desc">فروشگاه اسباب‌بازی کیدیوکی — خرید آنلاین اسباب‌بازی</span>
      </div>
    </a>

  </div>
</div>
<script>
/* session cookie – no expiry = cleared when browser closes */
function setChoice(){
  document.cookie='iakp_choice=1;path=/';
}
</script>
</body>
</html>
    <?php
}
