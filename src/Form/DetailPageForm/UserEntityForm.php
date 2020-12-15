<?php

namespace Phoenix\Form\DetailPageForm;

use Phoenix\Entity\User;

/**
 * @author  James Jones
 * @property  User entity
 *
 * Class UserEntityForm
 *
 * @package Phoenix
 *
 */
class UserEntityForm extends DetailPageEntityForm
{
    /**
     * HTML id property of form
     *
     * @var string
     */
    public string $formID = 'worker_form';

    /**
     * @return array
     */
    public function getButtonsArray(): array
    {
        $buttons = parent::getButtonsArray();
        if ( $this->entity->exists ) {
            $buttons[] = [
                'class' => 'btn btn-lg btn-primary mr-2 float-left',
                'element' => 'a',
                'id' => 'view-worker-week',
                'content' => 'View ' . $this->entity->getNamePossessive( true ) . ' Employee Week',
                'href' => $this->entity->getWorkerWeekLink()
            ];
            /*
            $buttons[] = [
                'class' => 'btn btn-lg btn-success float-right ml-2',
                'element' => 'a',
                'content' => 'Add Shift to ' . $this->entity->getFirstName(),
                'href' => $this->entity->getWorkerWeekLink()
            ];
            */
        }
        return $buttons;
    }

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

        $this->fields['pin'] = $this->htmlUtility::getIntegerFieldHTML( [
            'name' => 'pin',
            'value' => $this->entity->pin,
            'label' => 'Pin',
            'max' => 9999,
            'disabled' => $this->isDisabled()
        ] );
        $this->fields['rate'] = $this->htmlUtility::getCurrencyFieldHTML( [
            'name' => 'rate',
            'label' => 'Rate',
            'value' => $this->entity->rate,
            'append' => '<span class="input-group-text">/Hour</span>',
            'disabled' => $this->isDisabled()
        ] );


        $this->fields['active'] = $this->htmlUtility::getCheckboxesFieldHTML( [

            'name' => 'active',
            'label' => 'Active User',
            // 'id' => 'report-input-type-exclusive',
            'checked' => $this->entity->exists ? $this->entity->active : true,
            'small' => 'Inactive users will be unable to login and clock shifts.',
            'disabled' => $this->isDisabled()
        ] );


        $this->fields['type'] = $this->htmlUtility::getOptionDropdownFieldHTML( [
            'options' => [
                'staff' => 'Staff',
                'admin' => 'Admin'
            ],
            'selected' => $this->entity->role,
            'id' => 'inputType',
            'name' => 'type',
            'label' => 'User Role',
            'disabled' => $this->isDisabled()
        ] );
        $changePasswordToggleButton = $this->getDBAction() === 'update';
        $this->fields['password'] = $this->htmlUtility::getPasswordFieldHTML( [
            'name' => 'unencrypted-password',
            'label' => 'Password',
            'id' => 'inputPassword',
            'value' => '',
            'placeholder' => 'Enter a password',
            'change_password_toggle' => $changePasswordToggleButton,
            'disabled' => $this->isDisabled()
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
                <?php echo $this->fields['pin']; ?>
            </div>
        </div>
        <div class="form-row align-items-end">
            <div class="form-group col-md-4">
                <?php echo $this->fields['type']; ?>
            </div>
            <div class="form-group col-md-4">
                <?php echo $this->fields['rate']; ?>
            </div>
            <div class="form-group col-md-4">
                <?php echo $this->fields['active']; ?>
            </div>
        </div>

        <div class="form-row align-items-end">
            <div class="form-group col">
                <?php echo $this->fields['password']; ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}