<?php

namespace Phoenix\Form\DetailPageForm;

use Phoenix\Entity\Customer;

/**
 * @author James Jones
 * @property  Customer entity
 *
 * Class CustomerEntityForm
 *
 * @package Phoenix\EntityForm
 *
 */
class CustomerEntityForm extends DetailPageEntityForm
{
    /**
     * HTML id property of form
     *
     * @var string
     */
    public string $formID = 'customer_form';

    public function getButtonsArray(): array
    {
        $buttons = parent::getButtonsArray();
        if ( $this->entity->exists ) {
            $buttons[] = [
                'class' => 'btn btn-lg btn-primary mr-2 float-left',
                'element' => 'a',
                'content' => ucwords( $this->entity->getNamePossessive() . ' Profit/Loss Report' ),
                'href' => $this->entity->getProfitLossLink()
            ];
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
        $this->fields['email_address'] = $this->htmlUtility::getEmailFieldHTML( [
            'name' => 'email_address',
            'value' => $this->entity->emailAddress,
            'label' => 'Email Address',
            'disabled' => $this->isDisabled(),
            'append' => $this->htmlUtility::getViewButton( $this->entity->getEmailLink(), 'Email ' . ($this->entity->name ?? 'Customer') )
        ] );

        $this->fields['phone_number'] = $this->htmlUtility::getTextFieldHTML( [
            'name' => 'phone_number',
            'value' => $this->entity->phoneNumber,
            'label' => 'Phone Number',
            'disabled' => $this->isDisabled(),
            'append' => $this->htmlUtility::getViewButton( $this->entity->getPhoneLink(), 'Call ' . ($this->entity->name ?? 'Customer') )
        ] );
        return $this;

    }

    /**
     * @return string
     */
    public function renderFields(): string
    {
        ob_start(); ?>
        <div class="form-row">
            <div class="form-group col-md-6">
                <?php echo $this->getIdFieldHTML(); ?>
            </div>
            <div class="form-group col-md-6">
                <?php echo $this->fields['name']; ?>
            </div>
            <div class="form-group col-md-6">
                <?php echo $this->fields['email_address']; ?>
            </div>
            <div class="form-group col-md-6">
                <?php echo $this->fields['phone_number']; ?>
            </div>
        </div>
        <?php return ob_get_clean();
    }
}