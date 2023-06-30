<?php

namespace Pixel\DirectoryBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Sulu\Bundle\CategoryBundle\Entity\Category;
use Sulu\Bundle\CategoryBundle\Entity\CategoryInterface;
use Sulu\Bundle\MediaBundle\Entity\MediaInterface;

/**
 * @ORM\Entity()
 * @ORM\Table(name="directory_card")
 * @ORM\Entity(repositoryClass="Pixel\DirectoryBundle\Repository\CardRepository")
 * @Serializer\ExclusionPolicy("all")
 */
class Card
{
    public const RESOURCE_KEY = 'cards';

    public const FORM_KEY = 'card_details';

    public const LIST_KEY = 'cards';

    public const SECURITY_CONTEXT = 'directory.cars';

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     *
     * @Serializer\Expose()
     */
    private ?int $id = null;

    /**
     * @ORM\ManyToOne(targetEntity=CategoryInterface::class)
     * @ORM\JoinColumn(nullable=false)
     * @Serializer\Expose()
     */
    private CategoryInterface $type;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     *
     * @Serializer\Expose()
     */
    private ?bool $isActive;

    /**
     * @ORM\Column(type="json", nullable=true)
     *
     * @Serializer\Expose()
     * @var array<mixed>|null location
     */
    private ?array $location = null;

    /**
     * @ORM\ManyToOne(targetEntity=MediaInterface::class)
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private ?MediaInterface $logo = null;

    /**
     * @ORM\ManyToOne(targetEntity=CategoryInterface::class)
     * @ORM\JoinColumn(nullable=false)
     * @Serializer\Expose()
     */
    private CategoryInterface $category;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Serializer\Expose()
     */
    private ?string $url;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Serializer\Expose()
     */
    private ?string $email;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @Serializer\Expose()
     */
    private ?string $phoneNumber;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @Serializer\Expose()
     */
    private ?string $facebook;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @Serializer\Expose()
     */
    private ?string $instagram;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @Serializer\Expose()
     */
    private ?string $twitter;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @Serializer\Expose()
     */
    private ?string $linkedin;

    /**
     * @var Collection<string, CardTranslation>
     * @ORM\OneToMany(targetEntity="Pixel\DirectoryBundle\Entity\CardTranslation", mappedBy="card", cascade={"ALL"}, indexBy="locale")
     * @Serializer\Exclude
     */
    private $translations;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $defaultLocale;

    private string $locale = 'fr';

    /**
     * @ORM\Column(type="json", nullable=true)
     * @Serializer\Expose()
     * @var array<mixed>
     */
    private ?array $pdfs = null;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @Serializer\Expose()
     */
    private ?string $youtubeId = null;

    /**
     * @ORM\Column(type="json", nullable=true)
     * @Serializer\Expose()
     * @var array<mixed>
     */
    private ?array $medias;

    public function __construct()
    {
        $this->isActive = true;
        $this->translations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @Serializer\VirtualProperty(name="name")
     */
    public function getName(): ?string
    {
        $translation = $this->getTranslation($this->locale);
        if (! $translation) {
            return null;
        }
        return $translation->getName();
    }

    protected function getTranslation(string $locale = 'fr'): ?CardTranslation
    {
        if (! $this->translations->containsKey($locale)) {
            return null;
        }
        return $this->translations->get($locale);
    }

    public function setName(string $name): self
    {
        $translation = $this->getTranslation($this->locale);
        if (! $translation) {
            $translation = $this->createTranslation($this->locale);
        }
        $translation->setName($name);
        return $this;
    }

    protected function createTranslation(string $locale): CardTranslation
    {
        $translation = new CardTranslation($this, $locale);
        $this->translations->set($locale, $translation);
        return $translation;
    }

    /**
     * @Serializer\VirtualProperty(name="description")
     */
    public function getDescription(): ?string
    {
        $translation = $this->getTranslation($this->locale);
        if (! $translation) {
            return null;
        }
        return $translation->getDescription();
    }

    public function setDescription(?string $description): self
    {
        $translation = $this->getTranslation($this->locale);
        if (! $translation) {
            $translation = $this->createTranslation($this->locale);
        }
        $translation->setDescription($description);
        return $this;
    }

    public function getCategory(): CategoryInterface
    {
        return $this->category;
    }

    public function setCategory(CategoryInterface $category): void
    {
        $this->category = $category;
    }

    /**
     * @Serializer\VirtualProperty(name="route")
     */
    public function getRoutePath(): ?string
    {
        $translation = $this->getTranslation($this->locale);
        if (! $translation) {
            return null;
        }
        return $translation->getRoutePath();
    }

    public function setRoutePath(string $routePath): self
    {
        $translation = $this->getTranslation($this->locale);
        if (! $translation) {
            $translation = $this->createTranslation($this->locale);
        }
        $translation->setRoutePath($routePath);
        return $this;
    }

    /**
     * @Serializer\VirtualProperty(name="seo")
     * @return array<mixed>|null
     */
    public function getSeo(): ?array
    {
        $translation = $this->getTranslation($this->locale);
        if (! $translation) {
            return null;
        }
        return $translation->getSeo();
    }

    /**
     * @return array<string, array<string, string>>
     */
    protected function emptySeo(): array
    {
        return [
            "seo" => [
                "title" => "",
                "description" => "",
                "keywords" => "",
                "canonicalUrl" => "",
                "noIndex" => "",
                "noFollow" => "",
                "hideinSitemap" => "",
            ],
        ];
    }

    /**
     * @Serializer\VirtualProperty(name="ext")
     * @return array<mixed>|null
     */
    public function getExt(): ?array
    {
        $translation = $this->getTranslation($this->locale);
        if (! $translation) {
            return null;
        }
        return ($translation->getSeo()) ? [
            'seo' => $translation->getSeo(),
        ] : $this->emptySeo();
    }

    /**
     * @param array<mixed>|null $seo
     */
    public function setSeo(?array $seo): self
    {
        $translation = $this->getTranslation($this->locale);
        if (! $translation) {
            $translation = $this->createTranslation($this->locale);
        }
        $translation->setSeo($seo);
        return $this;
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

    public function getUrl(): ?string
    {
        return $this->url;
    }

    /**
     * @param ?string $url
     */
    public function setUrl(?string $url): void
    {
        $this->url = $url;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): void
    {
        $this->email = $email;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(?string $phoneNumber): void
    {
        $this->phoneNumber = $phoneNumber;
    }

    /**
     * @return array<string, mixed>
     *
     * @Serializer\VirtualProperty()
     * @Serializer\SerializedName("logo")
     */
    public function getLogoData(): ?array
    {
        if ($logo = $this->getLogo()) {
            return [
                'id' => $logo->getId(),
            ];
        }

        return null;
    }

    public function getLogo(): ?MediaInterface
    {
        return $this->logo;
    }

    public function setLogo(?MediaInterface $logo): void
    {
        $this->logo = $logo;
    }

    public function getIsActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(?bool $isActive): void
    {
        $this->isActive = $isActive;
    }

    public function getType(): CategoryInterface
    {
        return $this->type;
    }

    public function setType(CategoryInterface $type): void
    {
        $this->type = $type;
    }

    public function getFacebook(): ?string
    {
        return $this->facebook;
    }

    public function setFacebook(?string $facebook): void
    {
        $this->facebook = $facebook;
    }

    public function getInstagram(): ?string
    {
        return $this->instagram;
    }

    public function setInstagram(?string $instagram): void
    {
        $this->instagram = $instagram;
    }

    public function getTwitter(): ?string
    {
        return $this->twitter;
    }

    public function setTwitter(?string $twitter): void
    {
        $this->twitter = $twitter;
    }

    public function getLinkedin(): ?string
    {
        return $this->linkedin;
    }

    public function setLinkedin(?string $linkedin): void
    {
        $this->linkedin = $linkedin;
    }

    /**
     * @return array<mixed>
     */
    public function getTranslations(): array
    {
        return $this->translations->toArray();
    }

    public function getDefaultLocale(): ?string
    {
        return $this->defaultLocale;
    }

    public function setDefaultLocale(?string $defaultLocale): void
    {
        $this->defaultLocale = $defaultLocale;
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

    /**
     * @return mixed
     */
    public function getMedias()
    {
        return $this->medias;
    }

    /**
     * @param mixed $medias
     */
    public function setMedias($medias): void
    {
        $this->medias = $medias;
    }

    /**
     * @return array<mixed>|null
     */
    public function getPdfs(): ?array
    {
        return $this->pdfs;
    }

    /**
     * @param array<mixed>|null $pdfs
     */
    public function setPdfs(?array $pdfs): void
    {
        $this->pdfs = $pdfs;
    }

    public function getYoutubeId(): ?string
    {
        return $this->youtubeId;
    }

    public function setYoutubeId(?string $youtubeId): void
    {
        $this->youtubeId = $youtubeId;
    }
}
