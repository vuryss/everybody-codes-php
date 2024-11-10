<?php

declare(strict_types=1);

namespace App\Event;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class EventQuestCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->has(EventQuestRegistry::class)) {
            return;
        }

        $registry = $container->findDefinition(EventQuestRegistry::class);

        $taggedDays = $container->findTaggedServiceIds('app.event_quest');

        foreach ($taggedDays as $id => $tags) {
            preg_match('/Year(\d+)\\\\Quest(\d+)/', $id, $matches);
            $registry->addMethodCall('addQuest', [(int) $matches[1], (int) $matches[2], new Reference($id)]);
        }
    }
}
