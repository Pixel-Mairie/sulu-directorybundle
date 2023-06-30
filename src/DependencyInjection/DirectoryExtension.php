<?php

namespace Pixel\DirectoryBundle\DependencyInjection;

use Pixel\DirectoryBundle\Admin\CardAdmin;
use Pixel\DirectoryBundle\Entity\Card;
use Sulu\Bundle\PersistenceBundle\DependencyInjection\PersistenceExtensionTrait;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader;

class DirectoryExtension extends Extension implements PrependExtensionInterface
{
    use PersistenceExtensionTrait;

    public function prepend(ContainerBuilder $container): void
    {
        if ($container->hasExtension('sulu_admin')) {
            $container->prependExtensionConfig(
                'sulu_admin',
                [
                    'forms' => [
                        'directories' => [
                            __DIR__ . '/../Resources/config/forms',
                        ],
                    ],
                    'lists' => [
                        'directories' => [
                            __DIR__ . '/../Resources/config/lists',
                        ],
                    ],
                    'resources' => [
                        'cards' => [
                            'routes' => [
                                'detail' => 'card.get_card',
                                'list' => 'card.get_cards',
                            ],
                        ],
                        'directory_settings' => [
                            'routes' => [
                                'detail' => 'directory.get_directory-settings',
                            ],
                        ],
                    ],
                    'field_type_options' => [
                        'selection' => [
                            'card_selection' => [
                                'default_type' => 'list_overlay',
                                'resource_key' => Card::RESOURCE_KEY,
                                'view' => [
                                    'name' => CardAdmin::EDIT_FORM_VIEW,
                                    'result_to_view' => [
                                        'id' => 'id',
                                    ],
                                ],
                                'types' => [
                                    'list_overlay' => [
                                        'adapter' => 'table',
                                        'list_key' => Card::LIST_KEY,
                                        'display_properties' => ['name'],
                                        'icon' => 'fa-map',
                                        'label' => 'directory',
                                        'overlay_title' => 'card.cardList',
                                    ],
                                ],
                            ],
                        ],
                        'single_selection' => [
                            'single_card_selection' => [
                                'default_type' => 'list_overlay',
                                'resource_key' => Card::RESOURCE_KEY,
                                'view' => [
                                    'name' => CardAdmin::EDIT_FORM_VIEW,
                                    'result_to_view' => [
                                        'id' => 'id',
                                    ],
                                ],
                                'types' => [
                                    'list_overlay' => [
                                        'adapter' => 'table',
                                        'list_key' => Card::LIST_KEY,
                                        'display_properties' => ['name'],
                                        'icon' => 'fa-map',
                                        'empty_text' => 'card.emptyCard',
                                        'overlay_title' => 'card.cardList',
                                    ],
                                    'auto_complete' => [
                                        'display_property' => 'name',
                                        'search_properties' => ['name'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ]
            );
        }
        if ($container->hasExtension('sulu_search')) {
            $container->prependExtensionConfig(
                'sulu_search',
                [
                    'indexes' => [
                        'card' => [
                            'name' => 'directory',
                            'icon' => 'su-house',
                            'view' => [
                                'name' => CardAdmin::EDIT_FORM_VIEW,
                                'result_to_view' => [
                                    'id' => 'id',
                                    'locale' => 'locale',
                                ],
                            ],
                            'security_context' => Card::SECURITY_CONTEXT,
                        ],
                    ],
                    'website' => [
                        "indexes" => [
                            "card",
                        ],
                    ],
                ]
            );
        }
    }

    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);
        $container->setParameter('pixel_directory.relation', $config['relation']);
        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loaderYaml = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.xml');
        $loaderYaml->load('services.yaml');
    }
}
