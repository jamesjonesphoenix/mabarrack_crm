<?php


namespace Phoenix\Form\DetailPageForm;

use Phoenix\Form\EntityForm;

/**
 * Class DetailPageEntityForm
 *
 * @author James Jones
 * @package Phoenix\EntityForm
 *
 */
abstract class DetailPageEntityForm extends EntityForm
{
    /**
     * @var array
     */
    public array $buttons = [];

    /**
     * @return string
     */
    public function getDBAction(): string
    {
        if ( $this->entity->exists ) {
            return 'update';
        }
        return 'add';
    }

    /**
     * @return bool
     */
    public function isDisabled(): bool
    {
        if ( $this->entity->exists ) {

            return true; //Safety feature to prevent accidentally editing existing job
        }

        if ( $this->getDBAction() === 'add' && !$this->entity->canCreate() ) {
            return true;
        }

        return false;
    }

    /**
     * @return string
     */
    public function render(): string
    {
        //<div class="messages mt-1 mb-3"></div>
        $dbAction = $this->getDBAction();
        $submitButtonString = ucwords( $dbAction . ' ' . $this->entity->entityName );

//grey-bg
        ob_start(); ?>
        <div class="container mb-4 position-relative">
            <?php if ( $dbAction === 'update' ) { ?>
                <div class="row">
                    <div class="col">
                        <div class="grey-bg p-3 clearfix">
                            <?php
                            foreach ( $this->getButtonsArray() as $button ) {
                                echo $this->htmlUtility::getButton( $button );
                            }
                            ?>
                        </div>
                    </div>
                </div>
            <?php } ?>
            <div class="row">
                <div class="col">
                    <div class="grey-bg px-3 pt-3">
                        <form id="<?php echo $this->formID; ?>" class="detail-form">
                            <fieldset>
                                <?php echo $this->renderFields(); ?>
                                <div class="form-row mt-1">
                                    <div class="form-group col-auto mb-3">
                                        <?php echo $this->htmlUtility::getButton( [
                                            'element' => 'input',
                                            'type' => 'submit',
                                            'class' => ['btn', 'btn-primary', 'btn-lg', 'mt-3', 'mr-1'],
                                            'id' => 'submit-button',
                                            'disabled' => $this->isDisabled(),
                                            'value' => $submitButtonString
                                        ] ); ?>
                                    </div>
                                    <div class="form-group col">
                                        <div class="messages"></div>
                                    </div>
                                </div>
                                <input type="hidden" name="entity" value="<?php echo $this->entity->entityName; ?>">
                                <input type="hidden" name="submit_action" value="<?php echo $dbAction; ?>">
                            </fieldset>

                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * @return string
     */
    abstract protected function renderFields(): string;

    /**
     * @return \string[][]
     */
    public function getButtonsArray(): array
    {
        $entityName = $this->entity->entityName;
        $buttons = [
            [
                'class' => 'btn btn-lg btn-primary mr-2 float-left',
                'type' => 'button',
                'id' => 'edit-button',
                'content' => 'Edit ' . ucfirst( $entityName )
            ], [
                'class' => 'btn btn-lg btn-secondary mr-2 float-left',
                'type' => 'button',
                'id' => 'cancel-button',
                'content' => 'Cancel Edit'
            ]
        ];

        if ( $this->entity->canDeleteThisEntityType() ) {
            array_unshift( $buttons, [
                'class' => 'btn btn-lg btn-danger mr-2 float-left',
                'type' => 'button',
                'id' => 'delete-dry-run-button',
                'content' => 'Delete ' . ucfirst( $entityName )
            ] );
        }
        if ( $this->entity->canCreate() ) {
            $buttons[] = [
                'href' => $this->entity->getLink( false ),
                'type' => 'button',
                'class' => 'btn btn-lg btn-success float-right',
                'element' => 'a',
                'content' => 'Add New ' . ucfirst( $entityName )
            ];
        }
        return $buttons;
    }

    /**
     * @return string
     */
    public function getIdFieldHTML(): string
    {
        $idValue = $this->entity->id;
        $placeholder = $this->getDBAction() === 'add' ? 'Auto generated' : 'ID';
        ob_start();
        echo $this->htmlUtility::getFieldLabelHTML( ucwords( $this->entity->entityName ) . ' ID', 'inputFakeID' );
        ?>
        <input type="number" class="form-control" id="inputFakeID" placeholder="<?php echo $placeholder; ?>" name='fakeID' value="<?php echo $idValue; ?>" disabled>
        <input type="hidden" class="form-control" id="inputID" name='ID' value="<?php echo $idValue; ?>">
        <?php
        return ob_get_clean();
    }


    /**
     * @return bool
     */
    public function addNewIsAllowed(): bool
    {
        return true;
    }
}