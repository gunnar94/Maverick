<?php

namespace Maverick\Handler\Whoops;

use Whoops\Handler\Handler;
use Maverick\View\ViewInterface;

class SafeRenderWhoopsHandler extends Handler
{
    /**
     * @var ViewInterface
     */
    protected $defaultView;

    /**
     * @var ViewInterface[]
     */
    protected $views = [];

    /**
     * @param ViewInterface $default
     */
    public function __construct(ViewInterface $default)
    {
        $this->defaultView = $default;
    }

    public function handle()
    {
        $code = $this->getRun()->sendHttpCode();
        $view = $this->views[$code] ?? $this->defaultView;

        echo $view->render();

        return Handler::QUIT;
    }

    public function addView(int $code, ViewInterface $view)
    {
        $this->views[$code] = $view;
    }
}
