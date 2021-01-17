<?php

namespace Phoenix\Form\DetailPageForm;

use Phoenix\Entity\Job;
use Phoenix\Entity\Shift;
use Phoenix\URL;
use stdClass;

/**
 * @author  James Jones
 * @property  Job entity
 *
 * Class JobEntityForm
 *
 * @package Phoenix
 *
 */
class JobEntityForm extends DetailPageEntityForm
{
    /**
     * HTML id property of form
     *
     * @var string
     */
    public string $formID = 'job_form';

    /**
     * @var bool
     */
    protected bool $manuallySetID = true;

    /**
     * @return string
     */
    public function render(): string
    {
        if ( $this->entity->id === 0 ) {
            return '';
        }
        return parent::render();
    }

    /**
     * @return array
     */
    public function getButtonsArray(): array
    {

        $buttons = parent::getButtonsArray();
        if ( $this->entity->exists ) {
            $buttons[] = [
                'class' => 'btn btn-lg btn-success float-right ml-2',
                'element' => 'a',
                'content' => 'Add Shift to Job ' . $this->entity->getIDBadge(),
                'href' => (new URL( (new Shift())->getLink( false ) ))
                    ->setQueryArg( 'prefill', ['job' => $this->entity->id] )
                    ->write()
            ];
        }
        return $buttons;
    }

    /**
     * @param array $jobStatusOptions
     * @param array $customerOptions
     * @param array $furnitureOptions
     * @return $this
     */
    public function makeOptionsDropdownFields(array $jobStatusOptions = [], array $customerOptions = [], array $furnitureOptions = []): self
    {
        $this->fields['status'] = $this->htmlUtility::getOptionDropdownFieldHTML( [
            'options' => $jobStatusOptions,
            'selected' => $this->entity->status->name,
            'id' => 'job-status',
            'class' => $this->entity->status->name,
            'name' => 'status',

            'label' => 'Job Status',
            'disabled' => $this->isDisabled()
        ] );

        $this->fields['customer'] = $this->htmlUtility::getOptionDropdownFieldHTML( [
            'options' => $customerOptions,
            'selected' => $this->entity->customer->id,
            'id' => 'inputCustomer',
            'name' => 'customer',
            'label' => 'Customer',
            'append' => $this->htmlUtility::getViewButton(
                $this->entity->customer->getLink(),
                'View ' . ($this->entity->customer->name ?? 'Customer')
            ),
            'disabled' => $this->isDisabled(),
            'placeholder' => 'Select Customer'
        ] );


        $jobFurniture = $this->entity->furniture;
        if ( !is_array( $jobFurniture ) || count( $jobFurniture ) === 0 ) { //if Job has no furniture we add a dummy furniture so we at least have one <select>
            $dummyFurniture = new stdClass();
            $dummyFurniture->quantity = 1;
            $dummyFurniture->id = '';
            $jobFurniture = ['dummy' => $dummyFurniture];
        }
        foreach ( $jobFurniture as $furniture ) {

            $this->fields['furniture'][$furniture->id]['dropdown'] = $this->htmlUtility::getOptionDropdownFieldHTML(
                [
                    'options' => $furnitureOptions,
                    'selected' => $furniture->id ?? null,
                    'class' => 'w300 furniture-name',
                    'id' => 'inputFurniture' . (!empty( $furniture->id ) ? '-' . $furniture->id : ''),
                    'placeholder' => 'Select Furniture',
                    'disabled' => $this->isDisabled(),
                    /*
                    'append' => $this->htmlUtility::getViewButton(
                            $furniture->getLink(),
                            'View ' . ($furniture->name ?? 'Furniture')
                        )
                    */


                ] );
            $this->fields['furniture'][$furniture->id]['quantity'] = $this->htmlUtility::getIntegerFieldHTML( [
                    'value' => $furniture->quantity,
                    'class' => 'furniture-quantity w100',
                    'disabled' => $this->isDisabled()
                ] )
                . '<a class="btn btn-danger remove-furniture'
                . (empty( $loopedOnce ) || $this->isDisabled() ? ' disabled' : '')
                . '" type="button">&minus;</a>'
                . ' '
                . $this->htmlUtility::getViewButton(
                    $furniture instanceof stdClass ? '' : $furniture->getLink(),
                    'View ' . ($furniture->name ?? 'Furniture')
                );
            $loopedOnce = true;
        }
        return $this;
    }

    /**
     * @return $this
     */
    public function makeFields(): self
    {
        $this->fields['priority'] = $this->htmlUtility::getOptionDropdownFieldHTML(
            [
                'name' => 'priority',
                'options' => [1 => 1, 2 => 2, 3 => 3, 4 => 4],
                'selected' => $this->entity->priority,
                'html' => ['name' => 'priority'],
                'label' => 'Job Priority',
                'disabled' => $this->isDisabled()
            ] );
        $this->fields['sale_price'] = $this->htmlUtility::getCurrencyFieldHTML( [
            'name' => 'sale_price',
            'label' => 'Sale Price',
            'value' => $this->entity->salePrice,
            'id' => 'inputSalePrice',
            'disabled' => $this->isDisabled()
        ] );
        $this->fields['material_cost'] = $this->htmlUtility::getCurrencyFieldHTML( [
            'name' => 'material_cost',
            'label' => 'Material Cost',
            'value' => $this->entity->materialCost,
            'id' => 'inputMaterialCost',
            'disabled' => $this->isDisabled()
        ] );
        $this->fields['contractor_cost'] = $this->htmlUtility::getCurrencyFieldHTML( [
            'name' => 'contractor_cost',
            'label' => 'Contractor Cost',
            'value' => $this->entity->contractorCost,
            'id' => 'inputContractorCost',
            'disabled' => $this->isDisabled()
        ] );
        $this->fields['spare_cost'] = $this->htmlUtility::getCurrencyFieldHTML( [
            'name' => 'spare_cost',
            'label' => 'Spare Cost',
            'value' => $this->entity->spareCost,
            'id' => 'inputSpareCost',
            'disabled' => $this->isDisabled()
        ] );
        $this->fields['date_started'] = $this->htmlUtility::getDateFieldHTML( [
            'name' => 'date_started',
            'label' => 'Date Started',
            'value' => $this->entity->dateStarted,
            'id' => 'inputDateStarted',
            'disabled' => $this->isDisabled()
        ] );
        $this->fields['date_finished'] = $this->htmlUtility::getDateFieldHTML( [
            'name' => 'date_finished',
            'label' => 'Date Finished',
            'value' => $this->entity->dateFinished,
            'id' => 'inputDateFinished',
            'disabled' => $this->isDisabled()
        ] );
        $this->fields['description'] = $this->htmlUtility::getTextAreaFieldHTML( [
            'name' => 'description',
            'label' => 'Description',
            'value' => $this->entity->description,
            'id' => 'inputDescription',
            'disabled' => $this->isDisabled()
        ] );
        $this->fields['images'] = $this->htmlUtility::getFileFieldHTML( [
            'name' => 'images',
            'label' => 'Images',
            'value' => '',
            'id' => 'inputImages',
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
                <?php echo $this->fields['priority']; ?>
            </div>
            <div class="form-group col-md-4">
                <?php echo $this->fields['status']; ?>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group col-md-6">
                <?php echo $this->fields['date_started']; ?>
            </div>
            <div class="form-group col-md-6">
                <?php echo $this->fields['date_finished']; ?>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group col-md-6">
                <?php echo $this->fields['customer']; ?>
                <div class="mt-3">
                    <?php echo $this->fields['sale_price']; ?>
                </div>
            </div>
            <div class="form-group col-md-6 job-description">
                <?php echo $this->fields['description']; ?>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group col-md-4">
                <?php echo $this->fields['material_cost']; ?>
            </div>
            <div class="form-group col-md-4">
                <?php echo $this->fields['contractor_cost']; ?>
            </div>
            <div class="form-group col-md-4">
                <?php echo $this->fields['spare_cost']; ?>
            </div>
        </div>
        <?php echo $this->getJobFurnitureFieldsHTML();
        return ob_get_clean();
    }


    /**
     * @return string
     */
    private function getJobFurnitureFieldsHTML(): string
    {
        $disabled = $this->isDisabled() ? ' disabled' : '';
        ob_start(); ?>
        <div class="form-row job-furniture-row">
            <?php foreach ( $this->fields['furniture'] as $furnitureID => $furniture ) { ?>
                <div class="form-group furniture-group col-sm-12">
                    <?php if ( empty( $didLabel ) ) {
                        echo $this->htmlUtility::getFieldLabelHTML(
                            'Furniture',
                            'inputFurniture' . (!empty( $furnitureID ) ? '-' . $furnitureID : '')
                        );
                        $didLabel = true;
                    } ?>
                    <div class="row no-gutters">
                        <div class="col"><?php echo $furniture['dropdown']; ?></div>
                    </div>
                    <?php echo $furniture['quantity']; ?>
                </div>
            <?php } ?>
            <div class="form-group col-sm-12">
                <input id="add-furniture-button" class="btn btn-primary"
                       value="&plus; Add Furniture"
                       type="button"<?php echo $disabled; ?>/>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}