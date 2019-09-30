<?php

/*
 * @copyright   2019 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticBadgeGeneratorBundle\Generator\Crate;

use Mautic\CoreBundle\Helper\ArrayHelper;

class PropertiesCrate
{
    /**
     * @var array
     */
    private $properties;

    /**
     * CodeImageCrate constructor.
     *
     * @param array $properties
     */
    public function __construct(array $properties)
    {
        $this->properties = $properties;
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        if (empty($this->getFields())) {
            return false;
        }

        return true;
    }

    /**
     * @return array|string
     */
    public function getFields()
    {
        if (ArrayHelper::getValue('contactId', $this->properties, false)) {
            return 'id';
        }

        return ArrayHelper::getValue('fields', $this->properties, []);
    }

    /**
     * @return int
     */
    public function getWidth()
    {
        if ($size = ArrayHelper::getValue('size', $this->properties, 0)) {
            return $size;
        }

        return ArrayHelper::getValue('width', $this->properties, 120);

    }

    /**
     * @return int
     */
    public function getHeight()
    {
        return ArrayHelper::getValue('height', $this->properties, 50);
    }

    public function getPositionY()
    {
        return ArrayHelper::getValue('position', $this->properties, 50);
    }

    public function getPositionX()
    {
        return ArrayHelper::getValue('positionX', $this->properties, 0);
    }

    /**
     * @return string
     */
    public function getAlign()
    {
        return ArrayHelper::getValue('align', $this->properties, 'C') == 'C' ? ArrayHelper::getValue('align', $this->properties, 'C') : '';
    }

    /**
     * @return array
     */
    public function getProperties()
    {
        return $this->properties;
    }

}