<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat;

use Closure;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\RequestInterface;
use Yansongda\Pay\Contract\LoggerInterface;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Request;
use Yansongda\Pay\Rocket;

abstract class GeneralPlugin implements PluginInterface
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
        $this->logger->info('[wechat][GeneralPlugin] 通用插件开始装载', ['rocket' => $rocket]);

        $rocket->setRadar($this->getRequest($rocket));
        $this->doSomething($rocket);

        $this->logger->info('[wechat][GeneralPlugin] 通用插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }

    protected function getRequest(Rocket $rocket): RequestInterface
    {
        return new Request(
            $this->getMethod(),
            $this->getUrl($rocket),
            $this->getHeaders(),
        );
    }

    protected function getMethod(): string
    {
        return 'POST';
    }

    protected function getUrl(Rocket $rocket): string
    {
        $params = $rocket->getParams();

        $url = Pay::MODE_SERVICE == get_wechat_config($params)->get('mode') ? $this->getPartnerUri($rocket) : $this->getUri($rocket);

        return 0 === strpos($url, 'http') ? $url : (get_wechat_base_uri($params).$url);
    }

    protected function getHeaders(): array
    {
        return [
            'Accept' => 'application/json, text/plain, application/x-gzip',
            'User-Agent' => 'yansongda/pay-v3.0',
            'Content-Type' => 'application/json; charset=utf-8',
        ];
    }

    abstract protected function doSomething(Rocket $rocket): void;

    abstract protected function getUri(Rocket $rocket): string;

    protected function getPartnerUri(Rocket $rocket): string
    {
        return $this->getUri($rocket);
    }
}
