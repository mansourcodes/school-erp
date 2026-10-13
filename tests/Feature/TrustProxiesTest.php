<?php

namespace Tests\Feature;

use App\Http\Middleware\TrustProxies;
use Illuminate\Http\Request;
use Tests\TestCase;

/** PLAN.md §10.5: behind Cloudflare, rate limits must see the visitor's IP. */
class TrustProxiesTest extends TestCase
{
    protected function clientIp($remoteAddr, $forwardedFor)
    {
        $request = Request::create('/', 'GET', [], [], [], ['REMOTE_ADDR' => $remoteAddr, 'HTTP_X_FORWARDED_FOR' => $forwardedFor]);

        return (new TrustProxies(app('config')))->handle($request, function ($r) {
            return $r->ip();
        });
    }

    public function test_nothing_is_trusted_by_default()
    {
        config(['portal.trusted_proxies' => null]);

        $this->assertSame('173.245.48.10', $this->clientIp('173.245.48.10', '203.0.113.7'));
    }

    public function test_cloudflare_ranges_reveal_the_visitor()
    {
        config(['portal.trusted_proxies' => 'cloudflare']);

        $this->assertSame('203.0.113.7', $this->clientIp('173.245.48.10', '203.0.113.7'));
        // A spoofed X-Forwarded-For from outside Cloudflare is ignored.
        $this->assertSame('198.51.100.9', $this->clientIp('198.51.100.9', '203.0.113.7'));
    }

    public function test_a_local_tunnel_can_be_trusted_alongside()
    {
        config(['portal.trusted_proxies' => 'cloudflare, 127.0.0.1']);

        $this->assertSame('203.0.113.7', $this->clientIp('127.0.0.1', '203.0.113.7'));
    }
}
