<?php

namespace App\EventListener;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

#[AsEventListener]
final readonly class RobotsNoindexListener
{
    public function __construct(
        #[Autowire(env: 'bool:ROBOTS_NOINDEX')]
        private bool $enabled,
    ) {
    }

    public function __invoke(ResponseEvent $event): void
    {
        if ($this->enabled) {
            $event->getResponse()->headers->set('X-Robots-Tag', 'noindex');
        }
    }
}
