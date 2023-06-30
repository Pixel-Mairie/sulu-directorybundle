<?php

declare(strict_types=1);

namespace Pixel\DirectoryBundle\Admin;

use Pixel\DirectoryBundle\Entity\Card;
use Sulu\Bundle\ActivityBundle\Infrastructure\Sulu\Admin\View\ActivityViewBuilderFactoryInterface;
use Sulu\Bundle\AdminBundle\Admin\Admin;
use Sulu\Bundle\AdminBundle\Admin\Navigation\NavigationItem;
use Sulu\Bundle\AdminBundle\Admin\Navigation\NavigationItemCollection;
use Sulu\Bundle\AdminBundle\Admin\View\TogglerToolbarAction;
use Sulu\Bundle\AdminBundle\Admin\View\ToolbarAction;
use Sulu\Bundle\AdminBundle\Admin\View\ViewBuilderFactoryInterface;
use Sulu\Bundle\AdminBundle\Admin\View\ViewCollection;
use Sulu\Component\Security\Authorization\PermissionTypes;
use Sulu\Component\Security\Authorization\SecurityCheckerInterface;
use Sulu\Component\Webspace\Manager\WebspaceManagerInterface;

class CardAdmin extends Admin
{
    public const LIST_VIEW = 'directory.card.list';

    public const ADD_FORM_VIEW = 'directory.card.add_form';

    public const ADD_FORM_DETAILS_VIEW = 'directory.card.add_form.details';

    public const EDIT_FORM_VIEW = 'directory.card.edit_form';

    public const EDIT_FORM_SEO_VIEW = 'directory.card.seo.edit_form';

    public const EDIT_FORM_DETAILS_VIEW = 'directory.card.edit_form.details';

    private ViewBuilderFactoryInterface $viewBuilderFactory;

    private SecurityCheckerInterface $securityChecker;

    private WebspaceManagerInterface $webspaceManager;

    private ActivityViewBuilderFactoryInterface $activityViewBuilderFactory;

    public function __construct(
        ViewBuilderFactoryInterface $viewBuilderFactory,
        SecurityCheckerInterface $securityChecker,
        WebspaceManagerInterface $webspaceManager,
        ActivityViewBuilderFactoryInterface $activityViewBuilderFactory
    ) {
        $this->viewBuilderFactory = $viewBuilderFactory;
        $this->securityChecker = $securityChecker;
        $this->webspaceManager = $webspaceManager;
        $this->activityViewBuilderFactory = $activityViewBuilderFactory;
    }

    public function configureNavigationItems(NavigationItemCollection $navigationItemCollection): void
    {
        if ($this->securityChecker->hasPermission(Card::SECURITY_CONTEXT, PermissionTypes::EDIT)) {
            $rootNavigationItem = new NavigationItem('directory');
            $rootNavigationItem->setIcon('fa-map');
            $rootNavigationItem->setPosition(15);
            $rootNavigationItem->setView(static::LIST_VIEW);
            $navigationItemCollection->add($rootNavigationItem);
        }
    }

    public function configureViews(ViewCollection $viewCollection): void
    {
        $locales = $this->webspaceManager->getAllLocales();
        $formToolbarActions = [];
        $listToolbarActions = [];
        if ($this->securityChecker->hasPermission(Card::SECURITY_CONTEXT, PermissionTypes::ADD)) {
            $listToolbarActions[] = new ToolbarAction('sulu_admin.add');
        }

        if ($this->securityChecker->hasPermission(Card::SECURITY_CONTEXT, PermissionTypes::EDIT)) {
            $formToolbarActions[] = new ToolbarAction('sulu_admin.save');
            $formToolbarActions[] = new TogglerToolbarAction(
                'card.isActive',
                'isActive',
                'enable',
                'disable'
            );
        }

        if ($this->securityChecker->hasPermission(Card::SECURITY_CONTEXT, PermissionTypes::DELETE)) {
            $formToolbarActions[] = new ToolbarAction('sulu_admin.delete');
            $listToolbarActions[] = new ToolbarAction('sulu_admin.delete');
        }

        if ($this->securityChecker->hasPermission(Card::SECURITY_CONTEXT, PermissionTypes::VIEW)) {
            $listToolbarActions[] = new ToolbarAction('sulu_admin.export');
        }

        if ($this->securityChecker->hasPermission(Card::SECURITY_CONTEXT, PermissionTypes::EDIT)) {
            $viewCollection->add(
                $this->viewBuilderFactory->createListViewBuilder(static::LIST_VIEW, '/cards/:locale')
                    ->setResourceKey(Card::RESOURCE_KEY)
                    ->setListKey(Card::LIST_KEY)
                    ->setTitle('directory')
                    ->addListAdapters(['table'])
                    ->addLocales($locales)
                    ->setDefaultLocale($locales[0])
                    ->setAddView(static::ADD_FORM_VIEW)
                    ->setEditView(static::EDIT_FORM_VIEW)
                    ->addToolbarActions($listToolbarActions)
            );

            $viewCollection->add(
                $this->viewBuilderFactory->createResourceTabViewBuilder(static::ADD_FORM_VIEW, '/cards/:locale/add')
                    ->setResourceKey(Card::RESOURCE_KEY)
                    ->addLocales($locales)
                    ->setBackView(static::LIST_VIEW)
            );

            $viewCollection->add(
                $this->viewBuilderFactory->createFormViewBuilder(static::ADD_FORM_DETAILS_VIEW, '/details')
                    ->setResourceKey(Card::RESOURCE_KEY)
                    ->setFormKey(Card::FORM_KEY)
                    ->setTabTitle('sulu_admin.details')
                    ->setEditView(static::EDIT_FORM_VIEW)
                    ->addToolbarActions($formToolbarActions)
                    ->setParent(static::ADD_FORM_VIEW)
            );

            $viewCollection->add(
                $this->viewBuilderFactory->createResourceTabViewBuilder(static::EDIT_FORM_VIEW, '/cards/:locale/:id')
                    ->setResourceKey(Card::RESOURCE_KEY)
                    ->addLocales($locales)
                    ->setBackView(static::LIST_VIEW)
            );

            $viewCollection->add(
                $this->viewBuilderFactory->createPreviewFormViewBuilder(static::EDIT_FORM_DETAILS_VIEW, '/details')
                    ->setResourceKey(Card::RESOURCE_KEY)
                    ->setFormKey(Card::FORM_KEY)
                    ->setTabTitle('sulu_admin.details')
                    ->addToolbarActions($formToolbarActions)
                    ->setParent(static::EDIT_FORM_VIEW)
            );

            $viewCollection->add(
                $this->viewBuilderFactory
                    ->createFormViewBuilder(static::EDIT_FORM_SEO_VIEW, '/seo')
                    ->setResourceKey(Card::RESOURCE_KEY)
                    ->setFormKey('seo')
                    ->setTabTitle('sulu_page.seo')
                    ->addToolbarActions($formToolbarActions)
                    ->setTitleVisible(true)
                    ->setTabOrder(2048)
                    ->setParent(static::EDIT_FORM_VIEW)
            );

            if ($this->activityViewBuilderFactory->hasActivityListPermission()) {
                $viewCollection->add(
                    $this->activityViewBuilderFactory->createActivityListViewBuilder(static::EDIT_FORM_VIEW . ".activity", "/activity", Card::RESOURCE_KEY)
                        ->setParent(static::EDIT_FORM_VIEW)
                );
            }
        }
    }

    /**
     * @return mixed[]
     */
    public function getSecurityContexts(): array
    {
        return [
            self::SULU_ADMIN_SECURITY_SYSTEM => [
                'Card' => [
                    Card::SECURITY_CONTEXT => [
                        PermissionTypes::VIEW,
                        PermissionTypes::ADD,
                        PermissionTypes::EDIT,
                        PermissionTypes::DELETE,
                    ],
                ],
            ],
        ];
    }
}
