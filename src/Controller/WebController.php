<?php

declare(strict_types=1);

namespace GeoProxy\Controller;

use GeoProxy\Repository\FixtureRepository;
use GeoProxy\Service\PlanCatalog;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class WebController
{
    #[Route('/', name: 'app_home', methods: ['GET'])]
    public function home(): Response
    {
        return new Response($this->layout('GeoProxy — Premium proxy infrastructure', 'home', $this->homeView()));
    }

    #[Route('/login', name: 'app_login_page', methods: ['GET'])]
    public function login(): Response
    {
        return new Response($this->layout('Sign in — GeoProxy', 'login', $this->authView('login')));
    }

    #[Route('/register', name: 'app_register_page', methods: ['GET'])]
    public function register(): Response
    {
        return new Response($this->layout('Create account — GeoProxy', 'register', $this->authView('register')));
    }

    #[Route('/plans', name: 'app_plans_page', methods: ['GET'])]
    public function plans(): Response
    {
        return new Response($this->layout('Plans — GeoProxy', 'plans', $this->plansView(new PlanCatalog()->all())));
    }

    #[Route('/admin', name: 'app_admin_page', methods: ['GET'])]
    public function admin(): Response
    {
        $fixtures = new FixtureRepository();

        return new Response($this->layout('Admin — GeoProxy', 'admin', $this->adminView($fixtures->countries(), $fixtures->nodes())));
    }

    private function homeView(): string
    {
        return <<<'HTML'
            <section class="hero">
                <div class="hero-copy">
                    <p class="eyebrow">Apple-inspired proxy SaaS</p>
                    <h1>Global residential-style routing with a calm, professional control plane.</h1>
                    <p class="lede">GeoProxy gives teams a clean place to manage API keys, proxy credentials, usage, billing, and node health without crowding login, registration, and admin workflows into one page.</p>
                    <div class="actions">
                        <a class="button primary" href="/register">Start free</a>
                        <a class="button secondary" href="/plans">View plans</a>
                    </div>
                </div>
                <div class="device-card" aria-label="Product preview">
                    <div class="traffic-light"><span></span><span></span><span></span></div>
                    <div class="metric-row"><span>Healthy nodes</span><strong>99.98%</strong></div>
                    <div class="metric-row"><span>Median latency</span><strong>84 ms</strong></div>
                    <div class="metric-row"><span>Active regions</span><strong>6</strong></div>
                    <div class="chart"><i style="height:42%"></i><i style="height:66%"></i><i style="height:52%"></i><i style="height:88%"></i><i style="height:74%"></i></div>
                </div>
            </section>
            <section class="section feature-grid">
                <article class="panel"><h2>Separate workspaces</h2><p>Dedicated pages for marketing, login, registration, plans, and admin keep every workflow focused and easier to extend.</p></article>
                <article class="panel"><h2>API-first</h2><p>Every interface maps to documented endpoints for auth, countries, usage, plans, keys, nodes, and operations telemetry.</p></article>
                <article class="panel"><h2>Production ready shell</h2><p>Soft white surfaces, iOS-like controls, responsive cards, and clear navigation make the product feel polished from day one.</p></article>
            </section>
            HTML;
    }

    private function authView(string $mode): string
    {
        $isLogin = $mode === 'login';
        $title = $isLogin ? 'Welcome back.' : 'Create your GeoProxy account.';
        $copy = $isLogin ? 'Sign in to monitor usage, rotate keys, and manage proxy credentials.' : 'Choose a plan, create your first API key, and start routing traffic by geography.';
        $endpoint = $isLogin ? '/auth/login' : '/auth/register';
        $button = $isLogin ? 'Sign in' : 'Create account';
        $switch = $isLogin ? '<p class="muted">New to GeoProxy? <a href="/register">Create an account</a>.</p>' : '<p class="muted">Already have an account? <a href="/login">Sign in</a>.</p>';
        $plan = $isLogin ? '' : '<label>Plan<select name="plan"><option value="free">Free</option><option value="starter">Starter</option><option value="pro">Pro</option><option value="enterprise">Enterprise</option></select></label>';

        return <<<HTML
            <section class="auth-shell">
                <form class="auth-card" method="post" action="{$endpoint}">
                    <p class="eyebrow">{$endpoint}</p>
                    <h1>{$title}</h1>
                    <p class="lede small">{$copy}</p>
                    <label>Email<input name="email" type="email" placeholder="you@example.com" autocomplete="email" required></label>
                    <label>Password<input name="password" type="password" placeholder="••••••••" autocomplete="current-password" required></label>
                    {$plan}
                    <button class="button primary full" type="submit">{$button}</button>
                    {$switch}
                </form>
            </section>
            HTML;
    }

    /** @param list<array<string, mixed>> $plans */
    private function plansView(array $plans): string
    {
        $cards = array_map(static function (array $plan): string {
            $price = $plan['price_cents'] === null ? 'Custom' : '$' . number_format(((int) $plan['price_cents']) / 100, 0) . '/mo';
            $bandwidth = $plan['monthly_bandwidth_limit_gb'] === null ? 'Unlimited bandwidth' : $plan['monthly_bandwidth_limit_gb'] . ' GB bandwidth';
            $requests = $plan['monthly_request_limit'] === null ? 'Unlimited requests' : number_format((int) $plan['monthly_request_limit']) . ' requests';
            $features = implode('', array_map(static fn(string $feature): string => '<li>' . htmlspecialchars(str_replace('_', ' ', $feature), ENT_QUOTES) . '</li>', $plan['features']));

            return sprintf('<article class="price-card"><p class="eyebrow">%s</p><h2>%s</h2><strong>%s</strong><p>%s · %s</p><ul>%s</ul><a class="button secondary full" href="/register">Choose plan</a></article>', htmlspecialchars((string) $plan['code'], ENT_QUOTES), htmlspecialchars((string) $plan['name'], ENT_QUOTES), htmlspecialchars($price, ENT_QUOTES), htmlspecialchars($requests, ENT_QUOTES), htmlspecialchars($bandwidth, ENT_QUOTES), $features);
        }, $plans);

        return '<section class="section page-head"><p class="eyebrow">Plans</p><h1>Simple pricing for every traffic profile.</h1><p class="lede small">Upgrade as request volume, bandwidth, locations, and dedicated routing needs grow.</p></section><section class="section pricing-grid">' . implode('', $cards) . '</section>';
    }

    /** @param list<array<string, mixed>> $countries @param list<array<string, mixed>> $nodes */
    private function adminView(array $countries, array $nodes): string
    {
        $countryRows = array_map(static fn(array $country): string => sprintf('<tr><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>', htmlspecialchars((string) $country['country'], ENT_QUOTES), htmlspecialchars((string) $country['code'], ENT_QUOTES), htmlspecialchars(implode(', ', $country['cities']), ENT_QUOTES), htmlspecialchars(implode(', ', $country['current_ips']), ENT_QUOTES)), $countries);
        $nodeRows = array_map(static fn(array $node): string => sprintf('<tr><td>%s</td><td>%s</td><td>%s</td><td>%d/%d</td><td>%d ms</td></tr>', htmlspecialchars((string) $node['id'], ENT_QUOTES), htmlspecialchars((string) $node['country_code'], ENT_QUOTES), $node['healthy'] ? '<span class="status ok">Healthy</span>' : '<span class="status warn">Offline</span>', (int) $node['active_connections'], (int) $node['capacity'], (int) $node['latency_ms']), $nodes);

        return '<section class="section page-head"><p class="eyebrow">Admin</p><h1>Operations dashboard</h1><p class="lede small">Monitor availability, coverage, and platform usage from a dedicated admin view.</p></section><section class="section stats"><article class="panel"><span>Users</span><strong>1,248</strong><p>Active customers</p></article><article class="panel"><span>Nodes</span><strong>' . count($nodes) . '</strong><p>Registered exit nodes</p></article><article class="panel"><span>Usage</span><strong>10.6 GB</strong><p>Demo period transfer</p></article></section><section class="section table-panel"><h2>Coverage</h2><table><thead><tr><th>Country</th><th>Code</th><th>Cities</th><th>Current IPs</th></tr></thead><tbody>' . implode('', $countryRows) . '</tbody></table></section><section class="section table-panel"><h2>Node health</h2><table><thead><tr><th>Node</th><th>Country</th><th>Status</th><th>Load</th><th>Latency</th></tr></thead><tbody>' . implode('', $nodeRows) . '</tbody></table></section>';
    }

    private function layout(string $title, string $active, string $content): string
    {
        $nav = [
            'home' => ['/', 'Home'],
            'login' => ['/login', 'Login'],
            'register' => ['/register', 'Register'],
            'plans' => ['/plans', 'Plans'],
            'admin' => ['/admin', 'Admin'],
        ];
        $links = '';
        foreach ($nav as $key => [$href, $label]) {
            $class = $key === $active ? ' class="active"' : '';
            $links .= sprintf('<a%s href="%s">%s</a>', $class, $href, $label);
        }

        return '<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>' . htmlspecialchars($title, ENT_QUOTES) . '</title><style>' . $this->styles() . '</style></head><body><nav class="nav"><a class="brand" href="/">GeoProxy</a><div>' . $links . '</div></nav><main>' . $content . '</main><footer class="footer"><span>GeoProxy API</span><a href="/v1/plans">/v1/plans</a><a href="/v1/countries">/v1/countries</a><a href="/v1/admin/dashboard">/v1/admin/dashboard</a></footer></body></html>';
    }

    private function styles(): string
    {
        return 'body{margin:0;font-family:-apple-system,BlinkMacSystemFont,"SF Pro Display","Segoe UI",sans-serif;background:#f5f5f7;color:#1d1d1f}a{color:#06c;text-decoration:none}.nav{position:sticky;top:0;z-index:5;display:flex;justify-content:space-between;align-items:center;padding:16px 7vw;background:rgba(255,255,255,.78);backdrop-filter:saturate(180%) blur(22px);border-bottom:1px solid #e8e8ed}.brand{font-weight:800;color:#111}.nav div{display:flex;gap:8px;flex-wrap:wrap}.nav a:not(.brand){color:#515154;padding:8px 12px;border-radius:999px}.nav a.active,.nav a:not(.brand):hover{background:#fff;color:#111;box-shadow:0 1px 8px #00000012}.hero{display:grid;grid-template-columns:minmax(0,1.2fr) minmax(320px,.8fr);gap:36px;align-items:center;min-height:620px;padding:56px 7vw;background:radial-gradient(circle at 20% 10%,#fff 0,#f5f5f7 34%,#eaf3ff 100%)}.hero h1,.page-head h1,.auth-card h1{font-size:clamp(42px,7vw,86px);line-height:.96;letter-spacing:-.06em;margin:0}.lede{font-size:21px;line-height:1.45;color:#6e6e73;max-width:760px}.lede.small{font-size:18px}.eyebrow{text-transform:uppercase;letter-spacing:.16em;font-size:12px;font-weight:800;color:#86868b}.actions{display:flex;gap:12px;flex-wrap:wrap;margin-top:28px}.button{display:inline-flex;justify-content:center;align-items:center;border:0;border-radius:999px;padding:13px 22px;font-weight:700;cursor:pointer}.button.primary{background:#0071e3;color:#fff}.button.secondary{background:#fff;color:#06c;box-shadow:inset 0 0 0 1px #d2d2d7}.button.full{width:100%;box-sizing:border-box}.device-card,.panel,.auth-card,.price-card,.table-panel{background:rgba(255,255,255,.86);border:1px solid #fff;border-radius:32px;box-shadow:0 24px 80px #1d1d1f1a;padding:28px}.traffic-light{display:flex;gap:8px}.traffic-light span{width:12px;height:12px;border-radius:50%;background:#ff5f57}.traffic-light span:nth-child(2){background:#ffbd2e}.traffic-light span:nth-child(3){background:#28c840}.metric-row{display:flex;justify-content:space-between;gap:20px;border-bottom:1px solid #ececf1;padding:22px 0;color:#6e6e73}.metric-row strong{font-size:28px;color:#1d1d1f}.chart{height:150px;display:flex;gap:14px;align-items:end;padding-top:24px}.chart i{flex:1;border-radius:16px 16px 6px 6px;background:linear-gradient(#007aff,#5ac8fa)}.section{padding:34px 7vw}.feature-grid,.pricing-grid,.stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:18px}.panel h2,.price-card h2{margin-top:0}.panel p,.price-card p,.muted{color:#6e6e73}.auth-shell{min-height:calc(100vh - 150px);display:grid;place-items:center;padding:48px 7vw}.auth-card{width:min(100%,460px)}label{display:block;margin-top:18px;font-weight:700}input,select{width:100%;box-sizing:border-box;margin-top:8px;border:1px solid #d2d2d7;border-radius:16px;background:#fbfbfd;color:#1d1d1f;padding:15px;font:inherit}button{font:inherit;margin-top:22px}.price-card strong,.stats strong{display:block;font-size:42px;letter-spacing:-.04em;margin:10px 0}.price-card ul{padding-left:20px;color:#424245;line-height:1.8}.page-head{padding-top:58px}.table-panel{margin:18px 7vw;overflow:auto}table{width:100%;border-collapse:collapse;min-width:680px}th,td{text-align:left;padding:16px;border-bottom:1px solid #ececf1}th{color:#86868b;font-size:12px;text-transform:uppercase;letter-spacing:.08em}.status{font-weight:800}.ok{color:#248a3d}.warn{color:#bf5b00}.footer{display:flex;gap:14px;flex-wrap:wrap;padding:28px 7vw;color:#86868b}@media(max-width:780px){.hero{grid-template-columns:1fr;min-height:auto}.nav{align-items:flex-start;gap:12px;flex-direction:column}.hero h1,.page-head h1,.auth-card h1{font-size:44px}}';
    }
}
