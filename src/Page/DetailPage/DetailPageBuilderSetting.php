<?php


namespace Phoenix\Page\DetailPage;

use Phoenix\Entity\SettingFactory;
use Phoenix\Form\DetailPageForm\SettingEntityForm;

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
     * @return SettingEntityForm
     */
    public function getForm(): SettingEntityForm
    {
        return new SettingEntityForm(
            $this->HTMLUtility,
            $this->getEntity()
        );
    }
}