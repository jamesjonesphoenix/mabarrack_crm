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
     * @return \string[][]
     */
    public function getButtonsArray(): array
    {
        return [
            [
                'class' => 'btn btn-lg btn-danger mr-2 float-left',
                'type' => 'button',
                'id' => 'delete-dry-run-button',
                'content' => 'Delete Worker'
            ], [
                'class' => 'btn btn-lg btn-primary mr-2 float-left',
                'type' => 'button',
                'id' => 'edit-button',
                'content' => 'Edit Worker'
            ], [
                'class' => 'btn btn-lg btn-secondary mr-2 float-left',
                'type' => 'button',
                'id' => 'cancel-button',
                'content' => 'Cancel Edit'
            ], [
                'href' => 'worker.php?add',
                'type' => 'button',
                'class' => 'btn btn-lg btn-success float-right',
                'element' => 'a',
                'content' => 'Add New Worker'
            ]
        ];
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
            'append' => '$/Hour',
            'disabled' => $this->isDisabled()
        ] );

        //$roles = new Roles();
        //Roles = $roles->roles;

        $this->fields['type'] = $this->htmlUtility::getOptionDropdownFieldHTML(
            [
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
            'disabled' => true
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
        <div class="form-row">
            <div class="form-group col-md-6">
                <?php echo $this->fields['type']; ?>
            </div>
            <div class="form-group col-md-6">
                <?php echo $this->fields['rate']; ?>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group col">
                <?php echo $this->fields['password']; ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}