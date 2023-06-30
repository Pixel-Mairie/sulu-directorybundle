<?php

namespace Pixel\DirectoryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Sulu\Bundle\MediaBundle\Entity\MediaInterface;
use Sulu\Component\Persistence\Model\AuditableInterface;
use Sulu\Component\Persistence\Model\AuditableTrait;

/**
 * @ORM\Entity()
 * @ORM\Table(name="directory_setting")
 * @Serializer\ExclusionPolicy("all")
 */
class Setting implements AuditableInterface
{
    use AuditableTrait;

    public const RESOURCE_KEY = "directory_settings";

    public const FORM_KEY = "directory_settings";

    public const SECURITY_CONTEXT = "directory_settings.settings";

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Serializer\Expose()
     */
    private ?int $id = null;

    /**
     * @ORM\Column(type="json", nullable=true)
     * @Serializer\Expose()
     * @var array<mixed>|null
     */
    private ?array $location = null;

    /**
     * @ORM\ManyToOne(targetEntity=MediaInterface::class)
     * @ORM\JoinColumn(onDelete="SET NULL")
     * @Serializer\Expose()
     */
    private ?MediaInterface $defaultImage = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return array<mixed>|null
     */
    public function getLocation(): ?array
    {
        return $this->location;
    }

    /**
     * @param array<mixed>|null $location
     */
    public function setLocation(?array $location): void
    {
        $this->location = $location;
    }

    public function getDefaultImage(): ?MediaInterface
    {
        return $this->defaultImage;
    }

    public function setDefaultImage(?MediaInterface $defaultImage): void
    {
        $this->defaultImage = $defaultImage;
    }
}
