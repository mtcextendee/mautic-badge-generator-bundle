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
    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var array
     */
    protected $properties = [];

    /**
     * @var string
     */
    protected $source;

    /**
     * @var int
     */
    protected $width = 283;

    /**
     * @var int
     */
    protected $height = 425;

    /**
     * @var \DateTime
     */
    protected $dateAdded;

    /** @var Stage */
    protected $stage;

    public function __construct()
    {
        $this->setDateAdded(new \DateTime());
    }

    public static function loadMetadata(ORM\ClassMetadata $metadata)
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
    public static function loadApiMetadata(ApiMetadataDriver $metadata)
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
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name.
     *
     * @param string $name
     *
     * @return LeadEventLog
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set dateAdded.
     *
     * @param \DateTime $dateAdded
     *
     * @return LeadEventLog
     */
    public function setDateAdded($dateAdded)
    {
        $this->dateAdded = $dateAdded;

        return $this;
    }

    /**
     * Get dateAdded.
     *
     * @return \DateTime
     */
    public function getDateAdded()
    {
        return $this->dateAdded;
    }

    public function getCreatedBy()
    {
    }

    public function getHeader()
    {
    }

    public function getPublishStatus()
    {
    }

    /**
     * @return string
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * @param string $source
     *
     * @return Badge
     */
    public function setSource($source)
    {
        $this->source = $source;

        return $this;
    }

    /**
     * @return array
     */
    public function getProperties()
    {
        return $this->properties;
    }

    /**
     * @param array $properties
     *
     * @return Badge
     */
    public function setProperties($properties)
    {
        $this->properties = $properties;

        return $this;
    }

    /**
     * @return int
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * @param int $width
     *
     * @return Badge
     */
    public function setWidth($width)
    {
        $this->width = $width;

        return $this;
    }

    /**
     * @return height
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * @param int $height
     *
     * @return Badge
     */
    public function setHeight($height)
    {
        $this->height = $height;

        return $this;
    }

    /**
     * @return Stage
     */
    public function getStage()
    {
        return $this->stage;
    }

    /**
     * @param Stage $stage
     *
     * @return Badge
     */
    public function setStage($stage)
    {
        $this->stage = $stage;

        return $this;
    }
}
