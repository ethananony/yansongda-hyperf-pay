<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat;

use Closure;
use GuzzleHttp\Psr7\Utils;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yansongda\Pay\Contract\LoggerInterface;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidParamsException;
use Yansongda\Pay\Parser\NoHttpRequestParser;
use Yansongda\Pay\Rocket;
use Yansongda\Supports\Collection;

class CallbackPlugin implements PluginInterface
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
        $this->logger->info('[wechat][CallbackPlugin] 插件开始装载', ['rocket' => $rocket]);

        $this->formatRequestAndParams($rocket);

        /* @phpstan-ignore-next-line */
        verify_wechat_sign($this->container, $rocket->getDestinationOrigin(), $rocket->getParams());

        $body = json_decode($rocket->getDestination()->getBody()->getContents(), true);

        $rocket->setDirection(NoHttpRequestParser::class)->setPayload(new Collection($body));

        $body['resource'] = decrypt_wechat_resource($body['resource'] ?? [], $rocket->getParams());

        $rocket->setDestination(new Collection($body));

        $this->logger->info('[wechat][CallbackPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }

    protected function formatRequestAndParams(Rocket $rocket): void
    {
        $request = $rocket->getParams()['request'] ?? null;

        if (!($request instanceof ServerRequestInterface)) {
            throw new InvalidParamsException(Exception::REQUEST_NULL_ERROR);
        }

        $contents = $request->getBody()->getContents();

        $rocket->setDestination($request->withBody(Utils::streamFor($contents)))
            ->setDestinationOrigin($request->withBody(Utils::streamFor($contents)))
            ->setParams($rocket->getParams()['params'] ?? []);
    }
}
