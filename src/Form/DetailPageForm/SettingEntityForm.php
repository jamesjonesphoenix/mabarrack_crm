<?php

namespace Phoenix\Form\DetailPageForm;

use Phoenix\Entity\Setting;

/**
 * @author James Jones
 * @property  Setting entity
 *
 * Class SettingEntityForm
 *
 * @package Phoenix\EntityForm
 *
 */
class SettingEntityForm extends DetailPageEntityForm
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
            'disabled' => $this->isDisabled(),
            'not-toggleable' => true
        ] );
        $this->fields['description'] = $this->htmlUtility::getTextAreaFieldHTML( [
            'name' => 'description',
            'value' => $this->entity->description,
            'label' => 'Description',
            'disabled' => $this->isDisabled(),
            'not-toggleable' => true
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
            <div class="form-group col">
                <?php echo $this->fields['description']; ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}