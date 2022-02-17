<?php

/*
 * @copyright   2019 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticBadgeGeneratorBundle\Entity;

use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\Mapping as ORM;
use Mautic\ApiBundle\Serializer\Driver\ApiMetadataDriver;
use Mautic\CoreBundle\Doctrine\Mapping\ClassMetadataBuilder;
use Mautic\StageBundle\Entity\Stage;

class Badge
{
    protected int $id;

    protected ?string $name = null;

    protected array $properties = [];

    protected ?string $source = null;

    protected int $width = 283;

    protected int $height = 425;

    protected ?\DateTime $dateAdded = null;

    protected ?\Mautic\StageBundle\Entity\Stage $stage = null;

    public function __construct()
    {
        $this->setDateAdded(new \DateTime());
    }

    public static function loadMetadata(ORM\ClassMetadata $metadata): void
    {
        $builder = new ClassMetadataBuilder($metadata);
        $builder->setTable('badges')
            ->setCustomRepositoryClass(BadgeRepository::class)
            ->addId()
            ->addNamedField('name', Type::STRING, 'name')
            ->addNamedField('source', Type::STRING, 'source')
            ->addNamedField('width', Type::INTEGER, 'width')
            ->addNamedField('height', Type::INTEGER, 'height')
            ->addNamedField('dateAdded', Type::DATETIME, 'date_added');

        $builder->createManyToOne('stage', 'Mautic\StageBundle\Entity\Stage')
            ->inversedBy('log')
            ->addJoinColumn('stage_id', 'id', true, false, 'CASCADE')
            ->build();

        $builder->addField('properties', 'json_array');
    }

    /**
     * Prepares the metadata for API usage.
     *
     * @param $metadata
     */
    public static function loadApiMetadata(ApiMetadataDriver $metadata): void
    {
        $metadata->setGroupPrefix('badges')
            ->addListProperties(
                [
                    'id',
                    'name',
                    'source',
                    'dateAdded',
                ]
            )->addProperties(
                [
                    'properties',
                ]
            )
            ->build();
    }

    /**
     * Get id.
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Set name.
     *
     * @param string $name
     */
    public function setName($name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Set dateAdded.
     *
     * @param \DateTime $dateAdded
     */
    public function setDateAdded($dateAdded): self
    {
        $this->dateAdded = $dateAdded;

        return $this;
    }

    /**
     * Get dateAdded.
     */
    public function getDateAdded(): \DateTime
    {
        return $this->dateAdded;
    }

    public function getCreatedBy(): void
    {
    }

    public function getHeader(): void
    {
    }

    public function getPublishStatus(): void
    {
    }

    public function getSource(): string
    {
        return $this->source;
    }

    /**
     * @param string $source
     */
    public function setSource($source): self
    {
        $this->source = $source;

        return $this;
    }

    public function getProperties(): array
    {
        return $this->properties;
    }

    /**
     * @param array $properties
     */
    public function setProperties($properties): self
    {
        $this->properties = $properties;

        return $this;
    }

    public function getWidth(): int
    {
        return $this->width;
    }

    /**
     * @param int $width
     */
    public function setWidth($width): self
    {
        $this->width = $width;

        return $this;
    }

    /**
     * @return height
     */
    public function getHeight(): int
    {
        return $this->height;
    }

    /**
     * @param int $height
     */
    public function setHeight($height): self
    {
        $this->height = $height;

        return $this;
    }

    public function getStage(): ?\Mautic\StageBundle\Entity\Stage
    {
        return $this->stage;
    }

    /**
     * @param Stage $stage
     */
    public function setStage($stage): self
    {
        $this->stage = $stage;

        return $this;
    }
}
