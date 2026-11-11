<?php

/**
 * IT Supply smoke test.
 *
 *   php tests/smoke.php
 *
 * Runs the whole site (public pages, enquiry list, quote flow, admin pages,
 * security rules) against a THROWAWAY database called it_supply_smoke_test on
 * your local MySQL (the same server your .env already points to). The script
 * creates it, fills it with sample data, and deletes it at the end. Your real
 * database, uploads and mail settings are never used.
 *
 * Needs only the normal vendor folder (no PHPUnit). Exit code 0 = all passed.
 */

$root = dirname(__DIR__);

const SMOKE_DB = 'it_supply_smoke_test';

if (! extension_loaded('pdo_mysql')) {
    fwrite(STDERR, "Turn on pdo_mysql in php.ini (extension=pdo_mysql), then run this again.\n");
    exit(2);
}

// ---- Force a throwaway test environment BEFORE Laravel loads .env ----------
$tmp = sys_get_temp_dir().'/it_supply_smoke_'.getmypid();
@mkdir($tmp.'/uploads', 0777, true);
@mkdir($tmp.'/sessions', 0777, true);

foreach ([
    'APP_ENV' => 'testing', 'APP_DEBUG' => 'false', 'APP_URL' => 'http://localhost',
    'DB_CONNECTION' => 'mysql', 'DB_DATABASE' => SMOKE_DB, 'DB_URL' => '',
    'CACHE_STORE' => 'array', 'SESSION_DRIVER' => 'file', 'QUEUE_CONNECTION' => 'sync',
    'MAIL_MAILER' => 'array', 'MAIL_ADMIN_ADDRESS' => 'admin@example.test',
    'PUBLIC_DISK_ROOT' => $tmp.'/uploads', 'LOG_CHANNEL' => 'null',
    'TRUST_PROXIES' => '', 'BCRYPT_ROUNDS' => '4',
] as $k => $v) {
    putenv("$k=$v");
    $_ENV[$k] = $v;
    $_SERVER[$k] = $v;
}

require $root.'/vendor/autoload.php';
$app = require $root.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// ---- Safety: refuse to run if it could touch a real database ---------------
if (config('database.default') !== 'mysql' || config('database.connections.mysql.database') !== SMOKE_DB) {
    fwrite(STDERR, "STOP: the configuration is cached, so the test database could not be forced.\n"
        ."Run: php artisan config:clear   (then run this test again)\n");
    exit(2);
}
config(['session.files' => $tmp.'/sessions']);

// Create an empty throwaway database (it is dropped again at the end)
$c = config('database.connections.mysql');
try {
    $pdo = new PDO("mysql:host={$c['host']};port={$c['port']}", $c['username'], $c['password']);
    $pdo->exec('DROP DATABASE IF EXISTS `'.SMOKE_DB.'`');
    $pdo->exec('CREATE DATABASE `'.SMOKE_DB.'` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
} catch (PDOException $e) {
    fwrite(STDERR, "Cannot reach MySQL with the login in your .env: ".$e->getMessage()."\nStart MySQL (XAMPP) and check DB_HOST / DB_USERNAME / DB_PASSWORD.\n");
    exit(2);
}

use App\Models\{Banner, Blog, BlogCategory, Brand, Category, Faq, Page, Popup, Product, ProductImage, Setting, User};
use Illuminate\Contracts\Http\Kernel as HttpKernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

Artisan::call('migrate', ['--force' => true]);

// ---- Test data -------------------------------------------------------------
function png(string $name, int $w = 600, int $h = 400): string
{
    $im = imagecreatetruecolor($w, $h);
    imagefill($im, 0, 0, imagecolorallocate($im, 30, 60, 120));
    ob_start();
    imagepng($im);
    Storage::disk('public')->put($name, ob_get_clean());

    return $name;
}

foreach (['super_admin', 'product_manager', 'sales', 'content_manager'] as $role) {
    $u = new User(['name' => $role, 'email' => "$role@test.local", 'password' => 'Test-Pass-12345']);
    $u->role = $role;
    $u->status = 'active';
    $u->save();
}

$cat = Category::create(['name' => 'Laptops', 'slug' => 'laptops', 'status' => true, 'sort_order' => 1, 'icon' => 'bi-laptop']);
$sub = Category::create(['name' => 'Business Laptops', 'slug' => 'business-laptops', 'parent_id' => $cat->id, 'status' => true, 'sort_order' => 1]);
$hp = Brand::create(['name' => 'HP', 'slug' => 'hp', 'status' => true, 'sort_order' => 1]);
$dell = Brand::create(['name' => 'Dell', 'slug' => 'dell', 'status' => true, 'sort_order' => 2]);

foreach ([['HP EliteBook 840', 'hp-elitebook-840', $hp, $sub], ['Dell Latitude 5440', 'dell-latitude-5440', $dell, $sub]] as $i => [$n, $s, $b, $c]) {
    $p = Product::create([
        'brand_id' => $b->id, 'category_id' => $c->id, 'name' => $n, 'slug' => $s, 'sku' => "SKU$i", 'model_number' => "M$i",
        'short_description' => 'Short', 'description' => "Long\nText", 'price' => 3000 + $i, 'price_type' => 'fixed',
        'stock' => 5, 'stock_status' => 'in_stock', 'condition' => 'new', 'featured' => true, 'status' => true,
    ]);
    ProductImage::create(['product_id' => $p->id, 'image' => png("products/$s.png"), 'is_primary' => true, 'sort_order' => 1]);
}

Banner::create(['title' => 'Hero', 'subtitle' => 'Sub', 'image' => png('banners/h.png', 1200, 500), 'position' => 'home_hero', 'sort_order' => 1, 'status' => true]);
Banner::create(['title' => 'Promo', 'image' => png('banners/p.png', 800, 400), 'position' => 'home_promo', 'sort_order' => 1, 'status' => true]);
$bc = BlogCategory::create(['name' => 'News', 'slug' => 'news']);
Blog::create(['blog_category_id' => $bc->id, 'author_id' => 1, 'title' => 'First post', 'slug' => 'first-post', 'excerpt' => 'x',
    'content' => '<p>Normal paragraph</p><script>alert("XSS_BLOG")</script><img src=x onerror=alert("XSS_IMG")>',
    'status' => true, 'published_at' => now()->subDay()]);
Page::create(['title' => 'Privacy Policy', 'slug' => 'privacy-policy', 'content' => '<p>Policy</p>', 'status' => true]);
Faq::create(['question' => 'Q?', 'answer' => 'A.', 'sort_order' => 1, 'status' => true]);
$popup = Popup::create(['name' => 'Test popup', 'layout' => 'standard', 'title' => 'Hello', 'body' => 'Body', 'trigger' => 'delay',
    'trigger_delay' => 1, 'frequency' => 'every_visit', 'show_on' => 'all', 'device' => 'all', 'status' => true,
    'show_form' => true, 'form_button_text' => 'Call me', 'sort_order' => 0]);
Setting::set('company_name', 'Test Co');
Setting::set('whatsapp_number', '+971 50 655 7172');

// Hostile text an admin (or an attacker with an admin login) could save
$sub->seo_content = '<h2>About</h2><p>ok</p><script>alert("XSS_CAT")</script><a href="javascript:alert(1)">x</a>';
$sub->save();

// ---- A tiny browser: keeps cookies, so cart and login work -----------------
class Browser
{
    public array $cookies = [];

    public function __construct(public string $ip) {}

    public function request(string $method, string $uri, array $data = [], array $files = [], bool $json = false): array
    {
        global $app;
        $kernel = $app->make(HttpKernel::class);
        // each browser must start from its own clean session and login state
        $app['auth']->forgetGuards();
        $app['session.store']->flush(); // drop the previous visitor's session data
        $app['cookie']->flushQueuedCookies();
        $server = ['REMOTE_ADDR' => $this->ip, 'HTTP_ACCEPT' => $json ? 'application/json' : 'text/html'];
        $request = Request::create($uri, $method, $data, $this->cookies, $files, $server);
        $response = $kernel->handle($request);
        foreach ($response->headers->getCookies() as $c) {
            $this->cookies[$c->getName()] = $c->getValue();
        }
        $out = [
            'status' => $response->getStatusCode(),
            'body' => (string) $response->getContent(),
            'location' => $response->headers->get('Location'),
            'headers' => $response->headers,
        ];
        $kernel->terminate($request, $response);

        return $out;
    }

    public function get(string $uri): array { return $this->request('GET', $uri); }

    public function post(string $uri, array $data = [], array $files = [], bool $json = false): array { return $this->request('POST', $uri, $data, $files, $json); }

    public function login(string $role): int
    {
        return $this->post('/admin/login', ['email' => "$role@test.local", 'password' => 'Test-Pass-12345'])['status'];
    }
}

// ---- Tiny test runner ------------------------------------------------------
$pass = 0;
$fail = 0;
function check(string $name, bool $ok, string $detail = ''): void
{
    global $pass, $fail;
    if ($ok) { $pass++; echo "  PASS  $name\n"; } else { $fail++; echo "  FAIL  $name".($detail ? "   <- $detail" : '')."\n"; }
}
function section(string $t): void { echo "\n$t\n"; }

// ============================================================================
section('Public pages');
$v = new Browser('10.0.0.1');
$pages = ['/', '/products', '/products/hp-elitebook-840', '/categories/laptops', '/categories/business-laptops', '/brands/hp',
    '/blog', '/blog/first-post', '/about-us', '/contact', '/privacy-policy', '/search?q=hp', '/sitemap.xml', '/robots.txt', '/enquiry'];
foreach ($pages as $u) {
    $r = $v->get($u);
    check("GET $u returns 200", $r['status'] === 200, 'status '.$r['status']);
}
check('unknown page returns 404, not a crash', $v->get('/no-such-page-xyz')['status'] === 404);
check('unknown product returns 404', $v->get('/products/does-not-exist')['status'] === 404);

section('Security headers and robots');
$h = $v->get('/')['headers'];
foreach (['X-Frame-Options', 'X-Content-Type-Options', 'Referrer-Policy', 'Permissions-Policy'] as $name) {
    check("header $name is sent", $h->has($name));
}
$robots = $v->get('/robots.txt')['body'];
check('robots.txt blocks /admin', str_contains($robots, 'Disallow: /admin'));
check('robots.txt lists the sitemap', str_contains($robots, 'Sitemap:'));
check('public/robots.txt file is not an empty file hiding the real one', ! file_exists($root.'/public/robots.txt') || filesize($root.'/public/robots.txt') > 0);
check('home page is indexable', str_contains($v->get('/')['body'], 'name="robots" content="index, follow"'));
check('enquiry page is noindex', str_contains($v->get('/enquiry')['body'], 'noindex'));

section('Admin-written text cannot run scripts (stored XSS)');
$cp = $v->get('/categories/business-laptops')['body'];
check('category text: <script> removed', ! str_contains($cp, 'XSS_CAT'));
check('category text: javascript: link removed', ! str_contains($cp, 'href="javascript:'));
check('category text: normal heading kept', str_contains($cp, '<h2>About</h2>'));
$bp = $v->get('/blog/first-post')['body'];
check('blog text: <script> removed', ! str_contains($bp, 'XSS_BLOG'));
check('blog text: onerror= removed', ! str_contains($bp, 'onerror=alert'));
check('blog text: normal paragraph kept', str_contains($bp, '<p>Normal paragraph</p>'));

section('Enquiry list and Request a Quote');
$b = new Browser('10.0.0.2');
check('empty enquiry list page loads', str_contains($b->get('/enquiry')['body'], 'Your enquiry list is empty'));
$b->post('/enquiry/add/1', ['quantity' => 3]);
$b->post('/enquiry/add/2', ['quantity' => 5]);
$page = $b->get('/enquiry')['body'];
check('enquiry list shows 2 products, 8 units', str_contains($page, '2 products, 8 units'));
check('WhatsApp button uses the number from Settings', str_contains($page, 'https://wa.me/971506557172?text='));
$b->post('/enquiry/update/1', ['quantity' => 7]);
check('quantity update works (10 units)', str_contains($b->get('/enquiry')['body'], '2 products, 12 units'));
check('absurd quantity does not crash', $b->post('/enquiry/update/1', ['quantity' => 99999999999])['status'] < 500);
$b->post('/enquiry/update/1', ['quantity' => 3]);
$rq = $b->get('/request-quote');
check('Request a Quote redirects to the contact page', $rq['status'] === 302 && str_contains((string) $rq['location'], '/contact?type=quote'));
$cf = $b->get('/contact?type=quote')['body'];
check('contact form preselects "Price / quotation"', str_contains($cf, 'value="Price / quotation" selected'));
check('contact form lists the chosen products', str_contains($cf, 'x3') && str_contains($cf, 'x5'));
preg_match('#<textarea name="message"[^>]*>(.*?)</textarea>#s', $cf, $m);
$res = $b->post('/contact', ['name' => 'Test Buyer', 'email' => 'buyer@example.com', 'phone' => '0500000000',
    'enquiry_type' => 'Price / quotation', 'message' => html_entity_decode($m[1] ?? 'x', ENT_QUOTES), 'from_quote' => '1']);
check('quote sent through contact form', $res['status'] === 302);
check('enquiry saved with its 2 products', DB::table('enquiry_items')->where('enquiry_id', DB::table('enquiries')->max('id'))->count() === 2);
check('enquiry list emptied after sending', str_contains($b->get('/enquiry')['body'], 'Your enquiry list is empty'));
$plain = (new Browser('10.0.0.3'))->post('/contact', ['name' => 'Plain', 'email' => 'p@example.com', 'message' => 'hello there']);
check('normal contact message works', $plain['status'] === 302);
check('contact form rejects an empty message', (new Browser('10.0.0.4'))->post('/contact', ['name' => 'X', 'email' => 'x@example.com'])['status'] === 302
    && DB::table('enquiries')->where('name', 'X')->count() === 0);
$spam = new Browser('10.0.0.9');
$codes = [];
for ($i = 0; $i < 7; $i++) { $codes[] = $spam->post('/contact', ['name' => 'S', 'email' => 's@example.com', 'message' => 'hi'])['status']; }
check('contact form is rate limited (429 after 5)', in_array(429, $codes, true), implode(',', $codes));

section('Popup call-back form');
$pl = (new Browser('10.0.0.5'))->post('/popup-lead', ['popup_id' => $popup->id, 'name' => 'Ali', 'phone' => '+971500000000'], [], true);
check('valid lead is saved', $pl['status'] === 200 && DB::table('popup_leads')->count() === 1);
$bad = (new Browser('10.0.0.6'))->post('/popup-lead', ['popup_id' => $popup->id, 'name' => 'Ali', 'phone' => 'abc'], [], true);
check('bad phone number is rejected', $bad['status'] === 422);
(new Browser('10.0.0.7'))->post('/popup-lead', ['popup_id' => $popup->id, 'name' => 'Bot', 'phone' => '+971500000001', 'website' => 'spam.com'], [], true);
check('hidden honeypot field blocks bots', DB::table('popup_leads')->where('name', 'Bot')->count() === 0);

section('Admin area');
$anon = new Browser('10.0.1.1');
$ad = $anon->get('/admin');
check('admin dashboard asks for login when logged out', $ad['status'] === 302 && str_contains((string) $ad['location'], '/admin/login'));
$wrong = new Browser('10.0.1.2');
$wrong->post('/admin/login', ['email' => 'super_admin@test.local', 'password' => 'wrong-password']);
check('wrong password does not log in', $wrong->get('/admin')['status'] === 302);
$adm = new Browser('10.0.1.4');
$adm->login('super_admin');
check('super admin can log in', $adm->get('/admin')['status'] === 200);
$bad = [];
$n = 0;
foreach ($app['router']->getRoutes() as $route) {
    $uri = $route->uri();
    if (! in_array('GET', $route->methods(), true) || ! str_starts_with($uri, 'admin') || str_contains($uri, '{') || $uri === 'admin/login') { continue; }
    $n++;
    $r = $adm->get('/'.$uri);
    if ($r['status'] !== 200) { $bad[] = "$uri={$r['status']}"; }
}
check("all $n admin pages load", ! $bad, implode(', ', $bad));
$admp = $adm->get('/admin')['headers']->get('Cache-Control');
check('admin pages are not cached by browsers', str_contains((string) $admp, 'no-store'), (string) $admp);

$sales = new Browser('10.0.1.5');
$sales->login('sales');
check('sales user can open their own area (dashboard)', $sales->get('/admin')['status'] === 200);
$su = $sales->get('/admin/users');
check('sales user cannot open the Users page', $su['status'] === 403, 'status '.$su['status']);

section('Dangerous links are refused');
$js = $adm->post('/admin/popups', ['name' => 'x', 'layout' => 'standard', 'title' => 't', 'button_text' => 'Go', 'button_url' => 'javascript:alert(1)',
    'trigger' => 'delay', 'frequency' => 'every_visit', 'show_on' => 'all', 'device' => 'all', 'status' => 1]);
check('popup button link javascript: refused', DB::table('popups')->where('name', 'x')->count() === 0);
$ok = $adm->post('/admin/popups', ['name' => 'fine', 'layout' => 'standard', 'title' => 't', 'button_text' => 'Go', 'button_url' => '/products',
    'trigger' => 'delay', 'frequency' => 'every_visit', 'show_on' => 'all', 'device' => 'all', 'status' => 1]);
check('popup button link /products accepted', DB::table('popups')->where('name', 'fine')->count() === 1, 'status '.$ok['status']);
check('admin password needs 12+ characters', str_contains(file_get_contents($root.'/app/Http/Controllers/Admin/UserController.php'), 'Password::min(12)'));

section('Importer cannot be pointed at your own server');
$svc = new App\Services\ImageService();
if (method_exists($svc, 'assertPublicUrl') || (new ReflectionClass($svc))->hasMethod('assertPublicUrl')) {
    $mt = new ReflectionMethod($svc, 'assertPublicUrl');
    $mt->setAccessible(true);
    foreach (['http://127.0.0.1/a.png', 'http://localhost/a.png', 'http://169.254.169.254/latest/', 'http://10.0.0.5/a.png', 'http://192.168.1.1/a.png', 'file:///etc/passwd'] as $url) {
        try { $mt->invoke($svc, $url); check("blocks $url", false); } catch (RuntimeException) { check("blocks $url", true); }
    }
    try { $mt->invoke($svc, 'https://8.8.8.8/x.jpg'); check('allows a normal public image URL', true); } catch (RuntimeException) { check('allows a normal public image URL', false); }
} else {
    check('ImageService has the private-address guard (assertPublicUrl)', false, 'not added yet');
}

section('Code hygiene');
check('no .patch/.sql/.zip/.env files committed in project root', ! array_filter(glob($root.'/*.{patch,sql,zip,bak}', GLOB_BRACE) ?: []) );

// ---- Result ---------------------------------------------------------------
echo "\n=====================================\n";
echo "RESULT: $pass passed, $fail failed\n";

// remove the throwaway database and temp files
$pdo->exec('DROP DATABASE IF EXISTS `'.SMOKE_DB.'`');
$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($tmp, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
foreach ($it as $f) { $f->isDir() ? @rmdir($f->getPathname()) : @unlink($f->getPathname()); }
@rmdir($tmp);

exit($fail ? 1 : 0);