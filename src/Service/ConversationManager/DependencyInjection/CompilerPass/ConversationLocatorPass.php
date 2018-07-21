<?php
declare(strict_types=1);

/**
 * This file is part of the PHP Telegram Starter Kit.
 *
 * (c) Dmytro Sokil <dmytro.sokil@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sokil\TelegramBot\Service\ConversationManager\DependencyInjection\CompilerPass;

use Sokil\TelegramBot\Service\ConversationManager\ConversationDispatcher;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Yaml\Parser as YamlParser;
use Symfony\Component\Yaml\Yaml;

class ConversationLocatorPass implements CompilerPassInterface
{
    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        $fileLocator = new FileLocator([
            $container->getParameterBag()->get('project.config_dir'),
            $container->getParameterBag()->get('kernel.config_dir'),
        ]);

        $yamlConfigPath = $fileLocator->locate('conversations.yml');

        // read yaml config
        $yamlParser = new YamlParser();
        $config = $yamlParser->parseFile(
            $yamlConfigPath,
            Yaml::PARSE_CONSTANT | Yaml::PARSE_CUSTOM_TAGS
        );

        // build collection of definitions
        $conversationDispatcherDefinition = $container->getDefinition(ConversationDispatcher::class);
        $conversationDispatcherDefinition->replaceArgument(0, $config['conversations']);
    }

}