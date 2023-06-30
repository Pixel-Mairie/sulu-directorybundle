<?php

namespace Pixel\DirectoryBundle\Twig;

use Doctrine\ORM\EntityManagerInterface;
use Pixel\DirectoryBundle\Entity\Setting;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class SettingsExtension extends AbstractExtension
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('directory_settings', [$this, 'directorySettings']),
        ];
    }

    public function directorySettings(): Setting
    {
        return $this->entityManager->getRepository(Setting::class)->findOneBy([]);
    }
}
