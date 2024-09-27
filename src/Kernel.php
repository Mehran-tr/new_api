<?php

namespace App;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    protected function configureContainer(ContainerBuilder $container, LoaderInterface $loader): void
    {
        $loader->load($this->getProjectDir().'/config/{packages}/*.yaml', 'glob');
        $loader->load($this->getProjectDir().'/config/{packages}/'.$this->environment.'/*.yaml', 'glob');

        if (is_file(\dirname(__DIR__).'/config/services.yaml')) {
            $loader->load($this->getProjectDir().'/config/services.yaml');
            $loader->load($this->getProjectDir().'/config/{services}_'.$this->environment.'.yaml', 'glob');
        } elseif (is_file($path = \dirname(__DIR__).'/config/services.php')) {
            (require $path)($container, $this);
        }

        // Load .env.test or check for CORS_ALLOW_ORIGIN
        if (isset($_ENV['CORS_ALLOW_ORIGIN'])) {
            $container->setParameter('CORS_ALLOW_ORIGIN', $_ENV['CORS_ALLOW_ORIGIN']);
        }
    }

}
