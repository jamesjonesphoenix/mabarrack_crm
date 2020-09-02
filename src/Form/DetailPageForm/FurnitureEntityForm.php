<?php

namespace Phoenix\Form\DetailPageForm;

use Phoenix\Entity\Furniture;

/**
 * @author James Jones
 * @property  Furniture entity
 *
 * Class FurnitureEntityForm
 *
 * @package Phoenix\EntityForm
 *
 */
class FurnitureEntityForm extends DetailPageEntityForm
{
    /**
     * HTML id property of form
     *
     * @var string
     */
    public string $formID = 'furniture_form';

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
        $this->fields['plural_name'] = $this->htmlUtility::getTextFieldHTML( [
            'name' => 'plural_name',
            'value' => $this->entity->namePlural,
            'label' => 'Plural Name',
            'disabled' => $this->isDisabled(),
            'small' => '<small>Enter plural of name manually if it is different to simply appending an "s".</small>'
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
                <?php echo $this->fields['plural_name']; ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}