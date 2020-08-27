<?php

namespace Phoenix\Form;


use Phoenix\Entity\Setting;

/**
 * @author James Jones
 * @property  Setting entity
 *
 * Class SettingForm
 *
 * @package Phoenix\Form
 *
 */
class SettingForm extends DetailPageForm
{
    /**
     * HTML id property of form
     *
     * @var string
     */
    public string $formID = 'setting_form';

    /**
     * @return $this
     */
    public function makeFields(): self
    {
        $this->fields['name'] = $this->htmlUtility::getTextFieldHTML( [
            'name' => 'name',
            'value' => $this->entity->name,
            'label' => 'Name',
            'disabled' => $this->isDisabled()
        ] );
        $this->fields['value'] = $this->htmlUtility::getTextFieldHTML( [
            'name' => 'value',
            'value' => $this->entity->value,
            'label' => 'Value',
            'disabled' => $this->isDisabled(),
        ] );
        return $this;
    }

    /**
     * @return string
     */
    public function renderFields(): string
    {
        ob_start();
        ?>
        <div class="form-row">
            <div class="form-group col-md-4">
                <?php echo $this->getIdFieldHTML(); ?>
            </div>
            <div class="form-group col-md-4">
                <?php echo $this->fields['name']; ?>
            </div>
            <div class="form-group col-md-4">
                <?php echo $this->fields['value']; ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

}