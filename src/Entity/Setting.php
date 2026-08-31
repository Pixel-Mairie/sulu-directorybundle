<?php

namespace Pixel\DirectoryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;
use JMS\Serializer\Annotation as Serializer;
use Sulu\Bundle\MediaBundle\Entity\MediaInterface;
use Sulu\Component\Persistence\Model\AuditableInterface;
use Sulu\Component\Persistence\Model\AuditableTrait;

#[ORM\Entity()]
#[ORM\Table(name: "directory_setting")]
#[Serializer\ExclusionPolicy("all")]
class Setting implements AuditableInterface
{
    use AuditableTrait;

    public const RESOURCE_KEY = "directory_settings";

    public const FORM_KEY = "directory_settings";

    public const SECURITY_CONTEXT = "directory_settings.settings";

    public const MAP_TILE_FILTER_WARM = 'warm';

    public const MAP_TILE_FILTER_NEUTRAL = 'neutral';

    public const MAP_TILE_FILTER_MUTED = 'muted';

    public const MAP_TILE_FILTERS = [
        self::MAP_TILE_FILTER_WARM,
        self::MAP_TILE_FILTER_NEUTRAL,
        self::MAP_TILE_FILTER_MUTED,
    ];

    #[ORM\Id()]
    #[ORM\GeneratedValue()]
    #[ORM\Column(type: "integer")]
    #[Serializer\Expose()]
    private ?int $id = null;

    /**
     * @var array<mixed>|null
     */
    #[ORM\Column(type: "json", nullable: true)]
    #[Serializer\Expose()]
    private ?array $location = null;

    #[ORM\Column(type: "string", length: 20, options: ["default" => self::MAP_TILE_FILTER_WARM])]
    #[Serializer\Expose()]
    private string $mapTileFilter = self::MAP_TILE_FILTER_WARM;

    #[ORM\ManyToOne(targetEntity: MediaInterface::class)]
    #[ORM\JoinColumn(onDelete: "SET NULL")]
    #[Serializer\Expose()]
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

    public function getMapTileFilter(): string
    {
        return $this->mapTileFilter;
    }

    public function setMapTileFilter(string $mapTileFilter): void
    {
        if (! in_array($mapTileFilter, self::MAP_TILE_FILTERS, true)) {
            throw new InvalidArgumentException(sprintf('Unknown map tile filter "%s".', $mapTileFilter));
        }

        $this->mapTileFilter = $mapTileFilter;
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
