<?php

namespace Pixel\DirectoryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Sulu\Component\Persistence\Model\AuditableInterface;
use Sulu\Component\Persistence\Model\AuditableTrait;

/**
 * @ORM\Entity()
 * @ORM\Table(name="directory_card_translation")
 * @ORM\Entity(repositoryClass="Pixel\DirectoryBundle\Repository\CardRepository")
 * @Serializer\ExclusionPolicy("all")
 */
class CardTranslation implements AuditableInterface
{
    use AuditableTrait;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     *
     * @Serializer\Expose()
     */
    private ?int $id = null;

    /**
     * @var Card
     * @ORM\ManyToOne(targetEntity="Pixel\DirectoryBundle\Entity\Card", inversedBy="translations")
     * @ORM\JoinColumn(nullable=true)
     */
    private $card;

    /**
     * @ORM\Column(type="string", length=5)
     */
    private string $locale;

    /**
     * @ORM\Column(type="string", length=255)
     *
     * @Serializer\Expose()
     */
    private string $name;

    /**
     * @ORM\Column(type="text", nullable=true)
     *
     * @Serializer\Expose()
     */
    private ?string $description = null;

    /**
     * @ORM\Column(type="string", length=255)
     *
     * @Serializer\Expose()
     */
    private string $routePath;

    /**
     * @ORM\Column(type="json", nullable=true)
     *
     * @Serializer\Expose()
     * @var array<mixed>|null
     */
    private ?array $seo = null;

    public function __construct(Card $card, string $locale)
    {
        $this->card = $card;
        $this->locale = $locale;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCard(): Card
    {
        return $this->card;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function setLocale(string $locale): self
    {
        $this->locale = $locale;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = trim($name);
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getRoutePath(): string
    {
        return $this->routePath ?? '';
    }

    public function setRoutePath(string $routePath): void
    {
        $this->routePath = $routePath;
    }

    /**
     * @return array<mixed>|null
     */
    public function getSeo(): ?array
    {
        return $this->seo;
    }

    /**
     * @param array<mixed>|null $seo
     */
    public function setSeo(?array $seo): void
    {
        $this->seo = $seo;
    }
}
