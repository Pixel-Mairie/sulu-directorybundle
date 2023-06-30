<?php

declare(strict_types=1);

namespace Pixel\DirectoryBundle\Preview;

use Pixel\DirectoryBundle\Entity\Card;
use Pixel\DirectoryBundle\Repository\CardRepository;
use Sulu\Bundle\MediaBundle\Media\Manager\MediaManagerInterface;
use Sulu\Bundle\PreviewBundle\Preview\Object\PreviewObjectProviderInterface;

class CardObjectProvider implements PreviewObjectProviderInterface
{
    private CardRepository $cardRepository;

    private MediaManagerInterface $mediaManager;

    public function __construct(CardRepository $cardRepository, MediaManagerInterface $mediaManager)
    {
        $this->cardRepository = $cardRepository;
        $this->mediaManager = $mediaManager;
    }

    public function getObject($id, $locale): Card
    {
        return $this->cardRepository->find((int) $id);
    }

    /**
     * @param Card $object
     */
    public function getId($object): string
    {
        return (string) $object->getId();
    }

    /**
     * @param Card $object
     * @param string $locale
     * @param array<mixed> $data
     */
    public function setValues($object, $locale, array $data): void
    {
        $logoId = $data['logo']['id'] ?? null;
        $location = $data['location'] ?? null;
        $isActive = $data['isActive'] ?? null;
        $url = $data['url'] ?? null;
        $email = $data['email'] ?? null;
        $phoneNumber = $data['phoneNumber'] ?? null;
        $facebook = $data['facebook'] ?? null;
        $instagram = $data['instagram'] ?? null;
        $twitter = $data['twitter'] ?? null;
        $linkedin = $data['linkedin'] ?? null;
        $medias = $data['medias'] ?? null;
        $pdfs = $data['pdfs'] ?? null;
        $youtubeId = $data['youtubeId'] ?? null;

        $object->setName($data['name']);
        $object->setLocation($location);
        $object->setDescription($data['description']);
        $object->setLogo($logoId ? $this->mediaManager->getEntityById($logoId) : null);
        $object->setIsActive($isActive);
        $object->setUrl($url);
        $object->setEmail($email);
        $object->setPhoneNumber($phoneNumber);
        $object->setFacebook($facebook);
        $object->setInstagram($instagram);
        $object->setTwitter($twitter);
        $object->setLinkedin($linkedin);
        $object->setMedias($medias);
        $object->setPdfs($pdfs);
        $object->setYoutubeId($youtubeId);
    }

    /**
     * @param object $object
     * @param string $locale
     * @param array<mixed> $context
     * @return mixed
     */
    public function setContext($object, $locale, array $context)
    {
        if (\array_key_exists('template', $context)) {
            $object->setStructureType($context['template']);
        }

        return $object;
    }

    /**
     * @param Card $object
     * @return string
     */
    public function serialize($object)
    {
        if (! $object->getName()) {
            $object->setName('');
        }
        if (! $object->getDescription()) {
            $object->setDescription('');
        }

        return serialize($object);
    }

    /**
     * @return mixed
     */
    public function deserialize($serializedObject, $objectClass)
    {
        return unserialize($serializedObject);
    }

    public function getSecurityContext($id, $locale): ?string
    {
        return Card::SECURITY_CONTEXT;
    }
}
