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

namespace Sokil\TelegramBot\Service\Workflow\DependencyInjection\CompilerPass;

use Symfony\Component\Config\FileLocator;
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
        // locate config
        $fileLocator = new FileLocator([
            $container->getParameterBag()->get('project.config_dir'),
            $container->getParameterBag()->get('kernel.config_dir'),
        ]);

        $yamlConfigPath = $fileLocator->locate('workflows.yml');

        // read yaml config
        $yamlParser = new YamlParser();
        $config = $yamlParser->parseFile(
            $yamlConfigPath,
            Yaml::PARSE_CONSTANT | Yaml::PARSE_CUSTOM_TAGS
        );

        // validate config
        if (empty($config['workflows']) || !is_array($config['workflows'])) {
            return;
        }

        $registryDefinition = $container->getDefinition('workflow.registry');

        // register workflows
        foreach ($config['workflows'] as $name => $workflow) {
            $type = $workflow['type']; // may be 'workflow' or 'state_machine'

            // id of workflow service
            $workflowId = sprintf('%s.%s', $type, $name);

            // configure metadata store with places
            $placesMetadata = [];
            foreach ($workflow['places'] as &$place) {
                // normalize place
                if (is_string($place)) {
                    $place = ['name' => $place, 'metadata' => []];
                }

                if (isset($place['metadata'])) {
                    $placesMetadata[$place['name']] = $place['metadata'];
                }
            }

            // transitions
            $transitions = [];
            $transitionsMetadataDefinition = new DependencyInjectionDefinition(\SplObjectStorage::class);
            foreach ($workflow['transitions'] as $transitionName => $transition) {
                // normalise transition
                $transition['name'] = $transitionName;
                $transition['from'] = (array)$transition['from'];
                $transition['to'] = (array)$transition['to'];
                if (!isset($transition['metadata'])) {
                    $transition['metadata'] = [];
                }

                // build service definitions
                switch ($type) {
                    case 'workflow':
                        $transitionDefinition = new DependencyInjectionDefinition(
                            Transition::class,
                            [
                                $transition['name'],
                                $transition['from'],
                                $transition['to']
                            ]
                        );

                        $transitions[] = $transitionDefinition;

                        $transitionsMetadataDefinition->addMethodCall('attach', array(
                            $transitionDefinition,
                            $transition['metadata'],
                        ));

                        break;

                    case 'state_machine':
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

                                $transitionsMetadataDefinition->addMethodCall('attach', array(
                                    $transitionDefinition,
                                    $transition['metadata'],
                                ));
                            }
                        }

                        break;
                }
            }

            // build metadata store
            $metadataStoreDefinition = new DependencyInjectionDefinition(
                InMemoryMetadataStore::class,
                [
                    $workflow['metadata'] ?? [],
                    $placesMetadata,
                    $transitionsMetadataDefinition
                ]
            );

            // Create a Workflow Definition
            $workflowDefinitionDefinition = new DependencyInjectionDefinition(WorkflowDefinition::class);
            $workflowDefinitionDefinition->setPublic(false);
            $workflowDefinitionDefinition->addArgument(array_map(
                function (array $place) { return $place['name']; },
                $workflow['places']
            ));
            $workflowDefinitionDefinition->addArgument($transitions);
            $workflowDefinitionDefinition->addArgument($workflow['initial_place'] ?? null);
            $workflowDefinitionDefinition->addArgument($metadataStoreDefinition);

            $container->setDefinition(
                sprintf('%s.definition', $workflowId),
                $workflowDefinitionDefinition
            );

            // create MarkingStore
            if (isset($workflow['marking_store']['type'])) {
                $markingStoreDefinition = new ChildDefinition(
                    'workflow.marking_store.' . $workflow['marking_store']['type']
                );

                foreach ($workflow['marking_store']['arguments'] as $argument) {
                    $markingStoreDefinition->addArgument($argument);
                }
            } elseif (isset($workflow['marking_store']['service'])) {
                $markingStoreDefinition = new Reference($workflow['marking_store']['service']);
            }

            // create Workflow
            $workflowDefinition = new ChildDefinition(sprintf('%s.abstract', $type));
            $container->setDefinition($workflowId, $workflowDefinition);

            $workflowDefinition->replaceArgument(0, new Reference(sprintf('%s.definition', $workflowId)));
            $workflowDefinition->replaceArgument(1, $markingStoreDefinition ?? null);
            $workflowDefinition->replaceArgument(3, $name);

            // add workflow to Registry
            if ($workflow['supports']) {
                foreach ($workflow['supports'] as $supportedClassName) {
                    $strategyDefinition = new DependencyInjectionDefinition(
                        InstanceOfSupportStrategy::class,
                        [$supportedClassName]
                    );
                    $strategyDefinition->setPublic(false);
                    $registryDefinition->addMethodCall(
                        'addWorkflow',
                        [
                            new Reference($workflowId),
                            $strategyDefinition
                        ]
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