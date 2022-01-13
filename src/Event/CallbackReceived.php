<?php

declare(strict_types=1);

namespace Yansongda\Pay\Event;

class CallbackReceived
{
    /**
     * @var string
     */
    public $provider;

    /**
     * @var array|\Psr\Http\Message\ServerRequestInterface|null
     */
    public $contents;

    /**
     * @var array|null
     */
    public $params;

    public function __construct(string $provider, $contents, ?array $params = [])
    {
        $this->provider = $provider;
        $this->contents = $contents;
        $this->params = $params;
    }
}
