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
    #[Route('/login', name: 'app_login_page', methods: ['GET'])]
    #[Route('/register', name: 'app_register_page', methods: ['GET'])]
    #[Route('/plans', name: 'app_plans_page', methods: ['GET'])]
    #[Route('/admin', name: 'app_admin_page', methods: ['GET'])]
    public function home(): Response
    {
        $plans = new PlanCatalog()->all();
        $fixtures = new FixtureRepository();
        $countries = $fixtures->countries();
        $nodes = $fixtures->nodes();

        return new Response($this->render($plans, $countries, $nodes));
    }

    /**
     * @param list<array<string, mixed>> $plans
     * @param list<array<string, mixed>> $countries
     * @param list<array<string, mixed>> $nodes
     */
    private function render(array $plans, array $countries, array $nodes): string
    {
        $planCards = array_map(static function (array $plan): string {
            $price = $plan['price_cents'] === null ? 'Custom' : '$' . number_format(((int) $plan['price_cents']) / 100, 0) . '/mo';
            $bandwidth = $plan['monthly_bandwidth_limit_gb'] === null ? 'Unlimited bandwidth' : $plan['monthly_bandwidth_limit_gb'] . ' GB bandwidth';
            $requests = $plan['monthly_request_limit'] === null ? 'Unlimited requests' : number_format((int) $plan['monthly_request_limit']) . ' requests';
            $features = implode('', array_map(static fn(string $feature): string => '<li>' . htmlspecialchars(str_replace('_', ' ', $feature), ENT_QUOTES) . '</li>', $plan['features']));

            return sprintf(
                '<article class="plan"><p class="eyebrow">%s</p><h3>%s</h3><strong>%s</strong><p>%s · %s</p><ul>%s</ul><button type="button">Choose %s</button></article>',
                htmlspecialchars((string) $plan['code'], ENT_QUOTES),
                htmlspecialchars((string) $plan['name'], ENT_QUOTES),
                htmlspecialchars($price, ENT_QUOTES),
                htmlspecialchars($requests, ENT_QUOTES),
                htmlspecialchars($bandwidth, ENT_QUOTES),
                $features,
                htmlspecialchars((string) $plan['name'], ENT_QUOTES),
            );
        }, $plans);

        $countryRows = array_map(static fn(array $country): string => sprintf(
            '<tr><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>',
            htmlspecialchars((string) $country['country'], ENT_QUOTES),
            htmlspecialchars((string) $country['code'], ENT_QUOTES),
            htmlspecialchars(implode(', ', $country['cities']), ENT_QUOTES),
            htmlspecialchars(implode(', ', $country['current_ips']), ENT_QUOTES),
        ), $countries);

        $nodeRows = array_map(static fn(array $node): string => sprintf(
            '<tr><td>%s</td><td>%s</td><td>%s</td><td>%d/%d</td><td>%d ms</td></tr>',
            htmlspecialchars((string) $node['id'], ENT_QUOTES),
            htmlspecialchars((string) $node['country_code'], ENT_QUOTES),
            $node['healthy'] ? '<span class="ok">Healthy</span>' : '<span>Offline</span>',
            (int) $node['active_connections'],
            (int) $node['capacity'],
            (int) $node['latency_ms'],
        ), $nodes);

        return '<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>GeoProxy SaaS</title><style>'
            . 'body{margin:0;font-family:Inter,system-ui,sans-serif;background:#07111f;color:#e8f1ff}a{color:#7dd3fc}.nav{display:flex;justify-content:space-between;align-items:center;padding:24px 6vw;background:#0b1729;position:sticky;top:0}.nav div{display:flex;gap:18px}.hero{padding:72px 6vw;background:linear-gradient(135deg,#10213d,#0f766e)}.hero h1{font-size:clamp(40px,7vw,82px);line-height:.95;margin:0 0 18px}.hero p{max-width:760px;font-size:20px}.grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:20px}.section{padding:56px 6vw}.card,.plan,form{background:#0f1d33;border:1px solid #23415f;border-radius:20px;padding:24px;box-shadow:0 20px 60px #0005}.plan strong{font-size:34px}.eyebrow{color:#5eead4;text-transform:uppercase;letter-spacing:.14em;font-size:12px;font-weight:800}input,select,button{width:100%;box-sizing:border-box;border-radius:12px;border:1px solid #35516e;background:#091426;color:#e8f1ff;padding:13px;margin:8px 0}button{background:#22c55e;border:0;color:#03120a;font-weight:800;cursor:pointer}table{width:100%;border-collapse:collapse;background:#0f1d33;border-radius:18px;overflow:hidden}th,td{text-align:left;padding:14px;border-bottom:1px solid #223a58}.ok{color:#86efac;font-weight:800}.split{display:grid;grid-template-columns:repeat(auto-fit,minmax(320px,1fr));gap:24px}.footer{padding:28px 6vw;color:#9fb3ca}'
            . '</style></head><body><nav class="nav"><strong>GeoProxy</strong><div><a href="#login">Login</a><a href="#register">Register</a><a href="#plans">Plans</a><a href="#admin">Admin</a></div></nav>'
            . '<main><section class="hero"><p class="eyebrow">VPN-backed geo proxy platform</p><h1>Proxy routing, subscriptions, and admin controls are ready.</h1><p>Use this application shell to log in, register customers, compare plans, review node health, and manage the proxy SaaS from one UI instead of the default Symfony page.</p></section>'
            . '<section class="section split"><form id="login"><p class="eyebrow">Login</p><h2>Customer login</h2><input type="email" placeholder="you@example.com"><input type="password" placeholder="Password"><button type="button">Sign in</button><p>Posts to <code>/auth/login</code>.</p></form><form id="register"><p class="eyebrow">Register</p><h2>Create account</h2><input type="email" placeholder="new@example.com"><input type="password" placeholder="Password"><select><option>Free</option><option>Starter</option><option>Pro</option><option>Enterprise</option></select><button type="button">Create account</button><p>Posts to <code>/auth/register</code>.</p></form></section>'
            . '<section class="section" id="plans"><p class="eyebrow">Plans</p><h2>Billing plans</h2><div class="grid">' . implode('', $planCards) . '</div></section>'
            . '<section class="section" id="admin"><p class="eyebrow">Admin</p><h2>Operations dashboard</h2><div class="grid"><div class="card"><h3>Users</h3><strong>1,248</strong><p>Active SaaS customers</p></div><div class="card"><h3>Nodes</h3><strong>' . count($nodes) . '</strong><p>Registered exit nodes</p></div><div class="card"><h3>Usage</h3><strong>10.6 GB</strong><p>Demo period transfer</p></div></div></section>'
            . '<section class="section"><h2>Coverage</h2><table><thead><tr><th>Country</th><th>Code</th><th>Cities</th><th>Current IPs</th></tr></thead><tbody>' . implode('', $countryRows) . '</tbody></table></section>'
            . '<section class="section"><h2>Node health</h2><table><thead><tr><th>Node</th><th>Country</th><th>Status</th><th>Load</th><th>Latency</th></tr></thead><tbody>' . implode('', $nodeRows) . '</tbody></table></section></main><footer class="footer">API: /v1/plans · /v1/countries · /v1/admin/dashboard</footer></body></html>';
    }
}
