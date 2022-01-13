<?php

declare(strict_types=1);

namespace Yansongda\Pay\Event;

class PayStarted
{
    /**
     * @var \Yansongda\Pay\Contract\PluginInterface[]
     */
    public $plugins;

    /**
     * @var array
     */
    public $params;

    public function __construct(array $plugins, array $params)
    {
        $this->plugins = $plugins;
        $this->params = $params;
    }
}
