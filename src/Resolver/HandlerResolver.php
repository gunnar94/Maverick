<?php
/**
 * Maverick Framework
 *
 * @author Alec Carpenter <alecgunnar@gmail.com>
 */
declare(strict_types=1);

namespace Maverick\Resolver;

use Interop\Container\ContainerInterface;

class HandlerResolver implements ResolverInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @todo Add support for handlers defined like: service.name->methodName
     * @inheritDoc
     */
    public function resolve($given): callable
    {
        if (is_callable($given)) {
            return $given;
        }

        return $this->container->get($given);
    }
}