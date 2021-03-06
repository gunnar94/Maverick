<?php

namespace Maverick;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Config\FileLocator;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Cached\CachedContainer;
use RuntimeException;
use InvalidArgumentException;

/**
 * This function will simply return the application's
 * container. If not in debug mode (specified by the
 * second argument), an attempt to load the cached
 * container will be made. If the cached container
 * cannot be loaded, the container will be built from
 * the existing configuration file.
 *
 * The expected configuration file should be called
 * `config.yml`, and should live inside of the `config`
 * directory which itself should live inside of the
 * `$root` directory of your application (specified by
 * the first argument).
 *
 * A second configuration file in the same directory
 * but called `environment.yml` may be created to
 * specify environment based configs.
 *
 * The last step in the bootstrap is to enable the
 * error handler if the third argument is true.
 *
 * @param string $root
 * @param bool $debug = false
 * @param bool $enable = true
 */
function bootstrap(string $root, bool $debug = false, bool $enable = true): ContainerInterface
{
    $container = null;

    /*
     * Try to load the container from the cache
     */

    if (!$debug && class_exists(CachedContainer::class)) {
        $container = new CachedContainer();
    }

    /*
     * Can't load the container from cache?
     * Build it from the config files
     */

    if (!($container instanceof ContainerInterface)) {
        $from = $root . '/config';
        $file = 'config.yml';
        $fqfp = $from . '/' . $file;

        $container = new ContainerBuilder();
        $container->setParameter('root_dir', $root);

        $loader = new YamlFileLoader($container, new FileLocator($from));

        try {
            $loader->load($file);
        } catch (InvalidArgumentException $exception) {
            throw new RuntimeException('Could not find the configuration file at: ' . $fqfp);
        }

        try {
            $loader->load('environment.yml');
        } catch (InvalidArgumentException $exception) {
            // Let it go -- don't worry about it
        }
    }

    if ($enable && $container->has('error_handler')) {
        $container->get('error_handler')->enable();
    }

    return $container;
}
