<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat;

use Closure;
use GuzzleHttp\Psr7\Utils;
use Psr\Container\ContainerInterface;
use Yansongda\Pay\Contract\LoggerInterface;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidParamsException;
use Yansongda\Pay\Rocket;
use Yansongda\Supports\Collection;
use Yansongda\Supports\Str;

class SignPlugin implements PluginInterface
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
        $this->logger->info('[wechat][SignPlugin] 插件开始装载', ['rocket' => $rocket]);

        $timestamp = time();
        $random = Str::random(32);
        $body = $this->payloadToString($rocket->getPayload());
        $radar = $rocket->getRadar()->withHeader('Authorization', get_wechat_authorization(
            $rocket->getParams(), $timestamp, $random, $this->getContents($rocket, $timestamp, $random))
        );

        if (!empty($rocket->getParams()['_serial_no'])) {
            $radar = $radar->withHeader('Wechatpay-Serial', $rocket->getParams()['_serial_no']);
        }

        if (!empty($body)) {
            $radar = $radar->withBody(Utils::streamFor($body));
        }

        $rocket->setRadar($radar);

        $this->logger->info('[wechat][SignPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }

    protected function getContents(Rocket $rocket, int $timestamp, string $random): string
    {
        $request = $rocket->getRadar();

        if (is_null($request)) {
            throw new InvalidParamsException(Exception::REQUEST_NULL_ERROR);
        }

        $uri = $request->getUri();

        return $request->getMethod()."\n".
            $uri->getPath().(empty($uri->getQuery()) ? '' : '?'.$uri->getQuery())."\n".
            $timestamp."\n".
            $random."\n".
            $this->payloadToString($rocket->getPayload())."\n";
    }

    protected function payloadToString(?Collection $payload): string
    {
        return (is_null($payload) || 0 === $payload->count()) ? '' : $payload->toJson();
    }
}
