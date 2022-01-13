<?php

declare(strict_types=1);

namespace Yansongda\Pay\Provider;

use GuzzleHttp\Psr7\Utils;
use Psr\Container\ContainerInterface;
use Psr\Http\Client\ClientInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Throwable;
use Yansongda\Pay\Contract\HttpClientInterface;
use Yansongda\Pay\Contract\LoggerInterface;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Contract\ProviderInterface;
use Yansongda\Pay\Contract\ShortcutInterface;
use Yansongda\Pay\Event;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidConfigException;
use Yansongda\Pay\Exception\InvalidParamsException;
use Yansongda\Pay\Exception\InvalidResponseException;
use Yansongda\Pay\Rocket;
use Yansongda\Supports\Collection;
use Yansongda\Supports\Pipeline;

abstract class AbstractProvider implements ProviderInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->logger = $container->get(LoggerInterface::class);
        $this->eventDispatcher = $container->get(EventDispatcherInterface::class);
    }

    /**
     * @return \Psr\Http\Message\MessageInterface|\Yansongda\Supports\Collection|array|null
     */
    public function call(string $plugin, array $params = [])
    {
        if (!class_exists($plugin) || !in_array(ShortcutInterface::class, class_implements($plugin))) {
            throw new InvalidParamsException(Exception::SHORTCUT_NOT_FOUND, "[$plugin] is not incompatible");
        }

        /* @var ShortcutInterface $money */
        $money = $this->container->get($plugin);

        return $this->pay(
            $this->mergeCommonPlugins($money->getPlugins($params)), $params
        );
    }

    /**
     * @return \Psr\Http\Message\MessageInterface|\Yansongda\Supports\Collection|array|null
     */
    public function pay(array $plugins, array $params)
    {
        $this->logger->info('[AbstractProvider] 即将进行 pay 操作', func_get_args());

        $this->eventDispatcher->dispatch(new Event\PayStarted($plugins, $params));

        $this->verifyPlugin($plugins);

        /* @var Pipeline $pipeline */
        $pipeline = $this->container->make(Pipeline::class);

        /* @var Rocket $rocket */
        $rocket = $pipeline
            ->send((new Rocket())->setParams($params)->setPayload(new Collection()))
            ->through($plugins)
            ->via('assembly')
            ->then(function ($rocket) {
                return $this->ignite($rocket);
            });

        $this->eventDispatcher->dispatch(new Event\PayFinish());

        return $rocket->getDestination();
    }

    public function ignite(Rocket $rocket): Rocket
    {
        if (!should_do_http_request($rocket->getDirection())) {
            return $rocket;
        }

        /* @var HttpClientInterface $http */
        $http = $this->container->get(HttpClientInterface::class);

        if (!($http instanceof ClientInterface)) {
            throw new InvalidConfigException(Exception::HTTP_CLIENT_CONFIG_ERROR);
        }

        $this->logger->info('[AbstractProvider] 准备请求支付服务商 API', $rocket->toArray());

        $this->eventDispatcher->dispatch(new Event\ApiRequesting());

        try {
            $response = $http->sendRequest($rocket->getRadar());

            $contents = $response->getBody()->getContents();

            $rocket->setDestination($response->withBody(Utils::streamFor($contents)))
                ->setDestinationOrigin($response->withBody(Utils::streamFor($contents)));
        } catch (Throwable $e) {
            $this->logger->error('[AbstractProvider] 请求支付服务商 API 出错', ['message' => $e->getMessage(), 'rocket' => $rocket->toArray(), 'trace' => $e->getTrace()]);

            throw new InvalidResponseException(Exception::REQUEST_RESPONSE_ERROR, $e->getMessage(), [], $e);
        }

        $this->logger->info('[AbstractProvider] 请求支付服务商 API 成功', ['response' => $response, 'rocket' => $rocket->toArray()]);

        $this->eventDispatcher->dispatch(new Event\ApiRequested());

        return $rocket;
    }

    abstract public function mergeCommonPlugins(array $plugins): array;

    protected function verifyPlugin(array $plugins): void
    {
        foreach ($plugins as $plugin) {
            if (is_callable($plugin)) {
                continue;
            }

            if ((is_object($plugin) ||
                    (is_string($plugin) && class_exists($plugin))) &&
                in_array(PluginInterface::class, class_implements($plugin))) {
                continue;
            }

            throw new InvalidParamsException(Exception::PLUGIN_ERROR, "[$plugin] is not incompatible");
        }
    }
}
