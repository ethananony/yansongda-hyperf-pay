<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay;

use Closure;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\RequestInterface;
use Yansongda\Pay\Contract\LoggerInterface;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Provider\Alipay;
use Yansongda\Pay\Request;
use Yansongda\Pay\Rocket;

class RadarPlugin implements PluginInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->logger = $container->get(LoggerInterface::class);
    }

    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        $this->logger->info('[alipay][RadarPlugin] 插件开始装载', ['rocket' => $rocket]);

        $rocket->setRadar($this->getRequest($rocket));

        $this->logger->info('[alipay][RadarPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }

    protected function getRequest(Rocket $rocket): RequestInterface
    {
        return new Request(
            $this->getMethod($rocket),
            $this->getUrl($rocket),
            $this->getHeaders(),
            $this->getBody($rocket),
        );
    }

    protected function getMethod(Rocket $rocket): string
    {
        return strtoupper($rocket->getParams()['_method'] ?? 'POST');
    }

    protected function getUrl(Rocket $rocket): string
    {
        $config = get_alipay_config($rocket->getParams());

        return Alipay::URL[$config->get('mode', Pay::MODE_NORMAL)];
    }

    protected function getHeaders(): array
    {
        return [
            'Content-Type' => 'application/x-www-form-urlencoded',
        ];
    }

    protected function getBody(Rocket $rocket): string
    {
        return $rocket->getPayload()->query();
    }
}
