<?php

declare(strict_types=1);

namespace Yansongda\Pay\Parser;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Yansongda\Pay\Contract\ParserInterface;
use Yansongda\Supports\Collection;

class CollectionParser implements ParserInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function parse(?ResponseInterface $response): Collection
    {
        return new Collection(
            $this->container->get(ArrayParser::class)->parse($response)
        );
    }
}
