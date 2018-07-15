<?php
declare(strict_types=1);

namespace Sokil\TelegramBot\Service\ConversationManager\DependencyInjection\Compilerpass;

use Sokil\TelegramBot\Service\ConversationManager\ConversationDispatcher;
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
        // read yaml config
        $yamlConfigPath = $container->getParameterBag()->get('kernel.config_dir') . '/workflows.yml';
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