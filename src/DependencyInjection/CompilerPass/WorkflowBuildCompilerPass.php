<?php
declare(strict_types=1);

namespace Sokil\TelegramBot\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition as DependencyInjectionDefinition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Workflow\Metadata\InMemoryMetadataStore;
use Symfony\Component\Workflow\SupportStrategy\InstanceOfSupportStrategy;
use Symfony\Component\Workflow\Transition;
use Symfony\Component\Workflow\Definition as WorkflowDefinition;
use Symfony\Component\Yaml\Parser as YamlParser;
use Symfony\Component\Yaml\Yaml;

/**
 * @see symfony/framework-bundle/DependencyInjection/FrameworkExtension.php
 */
class WorkflowBuildCompilerPass implements CompilerPassInterface
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

        $registryDefinition = $container->getDefinition('workflow.registry');

        // register workflows
        foreach ($config['workflows'] as $name => $workflow) {
            $type = $workflow['type'];

            // build metadata store
            $metadataStoreDefinition = new DependencyInjectionDefinition(
                InMemoryMetadataStore::class,
                [[], [], null]
            );

            // configure metadata store with metadata
            if ($workflow['metadata']) {
                $metadataStoreDefinition->replaceArgument(0, $workflow['metadata']);
            }

            // configure metadata store with places
            $placesMetadata = array();
            foreach ($workflow['places'] as $place) {
                if ($place['metadata']) {
                    $placesMetadata[$place['name']] = $place['metadata'];
                }
            }
            if ($placesMetadata) {
                $metadataStoreDefinition->replaceArgument(1, $placesMetadata);
            }

            // configure metadata store with transitions
            $transitions = [];
            $transitionsMetadataDefinition = new DependencyInjectionDefinition(\SplObjectStorage::class);
            foreach ($workflow['transitions'] as $transition) {
                if ('workflow' === $type) {
                    $transitionDefinition = new DependencyInjectionDefinition(
                        Transition::class,
                        [
                            $transition['name'],
                            $transition['from'],
                            $transition['to']
                        ]
                    );

                    $transitions[] = $transitionDefinition;

                    if ($transition['metadata']) {
                        $transitionsMetadataDefinition->addMethodCall('attach', array(
                            $transitionDefinition,
                            $transition['metadata'],
                        ));
                    }
                } elseif ('state_machine' === $type) {
                    foreach ($transition['from'] as $from) {
                        foreach ($transition['to'] as $to) {
                            $transitionDefinition = new DependencyInjectionDefinition(
                                Transition::class,
                                [
                                    $transition['name'],
                                    $from,
                                    $to
                                ]
                            );

                            $transitions[] = $transitionDefinition;

                            if ($transition['metadata']) {
                                $transitionsMetadataDefinition->addMethodCall('attach', array(
                                    $transitionDefinition,
                                    $transition['metadata'],
                                ));
                            }
                        }
                    }
                }
            }

            $metadataStoreDefinition->replaceArgument(2, $transitionsMetadataDefinition);

            // create places
            $places = array_map(
                function (array $place) {
                    return $place['name'];
                },
                $workflow['places']
            );

            // Create a Definition
            $definitionDefinition = new DependencyInjectionDefinition(WorkflowDefinition::class);

            $definitionDefinition->setPublic(false);
            $definitionDefinition->addArgument($places);
            $definitionDefinition->addArgument($transitions);
            $definitionDefinition->addArgument($workflow['initial_place'] ?? null);
            $definitionDefinition->addArgument($metadataStoreDefinition);
            $definitionDefinition->addTag('workflow.definition', array(
                'name' => $name,
                'type' => $type,
                'marking_store' => isset($workflow['marking_store']['type']) ? $workflow['marking_store']['type'] : null,
            ));

            // create MarkingStore
            if (isset($workflow['marking_store']['type'])) {
                $markingStoreDefinition = new ChildDefinition('workflow.marking_store.'.$workflow['marking_store']['type']);
                foreach ($workflow['marking_store']['arguments'] as $argument) {
                    $markingStoreDefinition->addArgument($argument);
                }
            } elseif (isset($workflow['marking_store']['service'])) {
                $markingStoreDefinition = new Reference($workflow['marking_store']['service']);
            }

            // create Workflow
            $workflowId = sprintf('%s.%s', $type, $name);
            $workflowDefinition = new ChildDefinition(sprintf('%s.abstract', $type));
            $workflowDefinition->replaceArgument(0, new Reference(sprintf('%s.definition', $workflowId)));
            if (isset($markingStoreDefinition)) {
                $workflowDefinition->replaceArgument(1, $markingStoreDefinition);
            }
            $workflowDefinition->replaceArgument(3, $name);

            // store to container
            $container->setDefinition($workflowId, $workflowDefinition);
            $container->setDefinition(sprintf('%s.definition', $workflowId), $definitionDefinition);

            // add workflow to Registry
            if ($workflow['supports']) {
                foreach ($workflow['supports'] as $supportedClassName) {
                    $strategyDefinition = new DependencyInjectionDefinition(
                        InstanceOfSupportStrategy::class,
                        array($supportedClassName)
                    );
                    $strategyDefinition->setPublic(false);
                    $registryDefinition->addMethodCall(
                        'addWorkflow',
                        array(
                            new Reference($workflowId),
                            $strategyDefinition
                        )
                    );
                }
            } elseif (isset($workflow['support_strategy'])) {
                $registryDefinition->addMethodCall(
                    'addWorkflow',
                    array(
                        new Reference($workflowId),
                        new Reference($workflow['support_strategy'])
                    )
                );
            }
        }
    }
}