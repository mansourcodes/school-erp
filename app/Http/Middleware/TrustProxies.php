<?php

namespace App\Http\Middleware;

use Fideloper\Proxy\TrustProxies as Middleware;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Http\Request;

class TrustProxies extends Middleware
{
    /**
     * The trusted proxies for this application, from config('portal.trusted_proxies').
     *
     * @var array|string|null
     */
    protected $proxies;

    /**
     * The headers that should be used to detect proxies.
     *
     * @var int
     */
    protected $headers = Request::HEADER_X_FORWARDED_FOR | Request::HEADER_X_FORWARDED_HOST | Request::HEADER_X_FORWARDED_PORT | Request::HEADER_X_FORWARDED_PROTO | Request::HEADER_X_FORWARDED_AWS_ELB;

    public function __construct(Repository $config)
    {
        parent::__construct($config);
        $this->proxies = $this->configuredProxies($config);
    }

    /** "cloudflare, 127.0.0.1" → Cloudflare ranges + the local tunnel. Null = trust none. */
    protected function configuredProxies(Repository $config)
    {
        $value = trim((string) $config->get('portal.trusted_proxies'));
        if ($value === '') {
            return null;
        }
        if ($value === '*') {
            return '*';
        }

        $proxies = [];
        foreach (array_filter(array_map('trim', explode(',', $value))) as $entry) {
            $proxies = array_merge($proxies, $entry === 'cloudflare' ? $config->get('portal.cloudflare_ips') : [$entry]);
        }

        return $proxies;
    }
}
