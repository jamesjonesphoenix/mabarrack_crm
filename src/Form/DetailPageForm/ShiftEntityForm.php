<?php

namespace Phoenix\Form\DetailPageForm;

use Phoenix\Entity\Shift;

/**
 * @author  James Jones
 * @property  Shift entity
 *
 * Class ShiftEntityForm
 *
 * @package Phoenix
 *
 */
class ShiftEntityForm extends DetailPageEntityForm
{
    /**
     * HTML id property of form
     *
     * @var string
     */
    public string $formID = 'update_shift_form';

    /**
     * @param array $jobOptions
     * @param array $workerOptions
     * @param array $activityOptions
     * @param array $furnitureOptions
     * @return $this
     */
    public function makeOptionsDropdownFields(array $jobOptions = [], array $workerOptions = [], array $activityOptions = [], array $furnitureOptions = []): self
    {
        $jobLink = is_object( $this->entity->job ) ? $this->htmlUtility::getViewButton( $this->entity->job->getLink() ?? '', 'View Job' ) : '';
        $workerLink = is_object( $this->entity->worker ) ? $this->htmlUtility::getViewButton( $this->entity->worker->getLink() ?? '', 'View Worker' ) : '';

        $this->fields['job'] = $this->htmlUtility::getOptionDropdownFieldHTML( [
            'options' => $jobOptions,
            'selected' => $this->entity->job->id ?? 0,
            'name' => 'job',
            'label' => 'Job',
            'placeholder' => 'Select Job',
            'append' => $jobLink,
            'disabled' => $this->isDisabled()
        ] );

        $this->fields['worker'] = $this->htmlUtility::getOptionDropdownFieldHTML( [
            'options' => $workerOptions,
            'selected' => $this->entity->worker->id ?? 0,
            'name' => 'worker',
            'label' => 'Worker',
            'placeholder' => 'Select Worker',
            'append' => $workerLink,
            'disabled' => $this->isDisabled()
        ] );

        $this->fields['activity'] = $this->htmlUtility::getOptionDropdownFieldHTML( [
            'options' => $activityOptions,
            'selected' => $this->entity->activity->id,
            'name' => 'activity',
            'label' => 'Activity',
            'placeholder' => 'Select Activity',
            'disabled' => $this->isDisabled()
        ] );

        $this->fields['furniture'] = '';
        if ( $this->entity->exists && ($this->entity->job->id !== 0 || (isset( $this->entity->activity->factoryOnly ) && $this->entity->activity->factoryOnly === false)) ) {
            $furnitureLink = is_object( $this->entity->furniture ) ? $this->entity->furniture->getLink() : '';
            $this->fields['furniture'] = $this->htmlUtility::getOptionDropdownFieldHTML(
                [
                    'options' => $furnitureOptions,
                    'selected' => $this->entity->furniture->id ?? $this->entity->furniture ?? null,
                    'name' => 'furniture',
                    'label' => 'Furniture',
                    'placeholder' => 'Select Furniture',

                    'append' => $this->htmlUtility::getViewButton(
                        $furnitureLink,
                        'View Furniture'
                    ),
                    'disabled' => $this->isDisabled()

                ] );
        }
        return $this;
    }

    /**
     * @return $this
     */
    public function makeFields(): self
    {
        $this->fields['date'] = $this->htmlUtility::getDateFieldHTML( [
            'name' => 'date',
            'label' => 'Date',
            'value' => $this->entity->date,
            'disabled' => $this->isDisabled()
        ] );
        $this->fields['time_started'] = $this->htmlUtility::getTimeFieldHTML( [
            'name' => 'time_started',
            'label' => 'Time Started',
            'value' => $this->entity->timeStarted,
            'disabled' => $this->isDisabled()
        ] );
        $this->fields['time_finished'] = $this->htmlUtility::getTimeFieldHTML( [
            'name' => 'time_finished',
            'label' => 'Time Finished',
            'value' => $this->entity->timeFinished,
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
                <?php echo $this->fields['job']; ?>
            </div>
            <div class="form-group col-md-4">
                <?php echo $this->fields['worker']; ?>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group col-md-4">
                <?php echo $this->fields['date']; ?>
            </div>
            <div class="form-group col-md-4">
                <?php echo $this->fields['time_started']; ?>
            </div>
            <div class="form-group col-md-4">
                <?php echo $this->fields['time_finished']; ?>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group col-md-6">
                <?php echo $this->fields['activity']; ?>
            </div>
            <div class="form-group col-md-6">
                <?php echo $this->fields['furniture']; ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}