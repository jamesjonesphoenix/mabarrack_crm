<?php


namespace Phoenix\Page\DetailPage;

use Phoenix\Entity\SettingFactory;
use Phoenix\Form\SettingForm;

/**
 *
 * Class DetailPageBuilderSetting
 *
 * @author James Jones
 * @package Phoenix\DetailPage
 *
 */
class DetailPageBuilderSetting extends DetailPageBuilder
{
    /**
     * @return SettingFactory
     */
    protected function getNewEntityFactory(): SettingFactory
    {
        return new SettingFactory( $this->db, $this->messages );
    }

    /**
     * @return SettingForm
     */
    public function getForm(): SettingForm
    {
        return new SettingForm(
            $this->HTMLUtility,
            $this->getEntity()
        );
    }
}