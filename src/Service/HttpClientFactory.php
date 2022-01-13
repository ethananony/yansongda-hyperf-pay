<?php

declare(strict_types=1);

namespace Yansongda\Pay\Service;

use GuzzleHttp\Client;
use Psr\Container\ContainerInterface;

class HttpClientFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $config = config('pay.http', []);

        return new Client($config);
    }
}
