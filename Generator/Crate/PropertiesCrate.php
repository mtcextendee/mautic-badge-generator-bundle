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
    private array $properties;

    /**
     * CodeImageCrate constructor.
     */
    public function __construct(array $properties)
    {
        $this->properties = $properties;
    }

    public function isEnabled(): bool
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

    public function getWidth(): int
    {
        if ($size = ArrayHelper::getValue('size', $this->properties, 0)) {
            return $size;
        }

        return ArrayHelper::getValue('width', $this->properties, 120);
    }

    public function getHeight(): int
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

    public function getAlign(): string
    {
        return 'C' == ArrayHelper::getValue('align', $this->properties, 'C') ? ArrayHelper::getValue('align', $this->properties, 'C') : '';
    }

    public function getProperties(): array
    {
        return $this->properties;
    }
}
