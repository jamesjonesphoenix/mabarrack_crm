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
        $this->fields['display_name'] = $this->htmlUtility::getTextFieldHTML( [
            'name' => 'display_name',
            'value' => $this->entity->displayName,
            'label' => 'Display Name',
            'disabled' => $this->isDisabled(),
            'not-toggleable' => true
        ] );
        $this->fields['description'] = $this->htmlUtility::getTextAreaFieldHTML( [
            'name' => 'description',
            'value' => $this->entity->description,
            'label' => 'Description',
            'disabled' => $this->isDisabled(),
            'class' => 'setting-textarea',
            'not-toggleable' => true
        ] );
        $this->fields['value'] = $this->htmlUtility::getTextAreaFieldHTML( [
            'name' => 'value',
            'value' => $this->entity->value,
            'label' => 'Value',
            'disabled' => $this->isDisabled(),
            'class' => 'setting-textarea'
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
                <div class="mb-3">
                    <?php echo $this->getIdFieldHTML(); ?>
                </div>
                <div class="mb-3">
                    <?php echo $this->fields['name'];  ?>
                </div>
                <?php echo $this->fields['display_name']; ?>
            </div>
            <div class="form-group col-md-4">
                <?php echo $this->fields['description']; ?>
            </div>
            <div class="form-group col-md-4">
                <?php echo $this->fields['value']; ?>
            </div>
        </div>
        <?php return ob_get_clean();
    }
}