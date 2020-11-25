<?php


namespace Phoenix\Form;


use Phoenix\Utility\FormFields;

/**
 * Class AddCommentForm
 *
 * @author James Jones
 * @package Phoenix\Form
 *
 */
class AddCommentForm extends Form
{
    /**
     * @var int
     */
    private int $shiftID;

    /**
     * @var string
     */
    private string $comment;

    /**
     * AddCommentForm constructor.
     *
     * @param FormFields $htmlUtility
     * @param int        $shiftID
     * @param string     $comment
     */
    public function __construct(FormFields $htmlUtility, int $shiftID, string $comment = '')
    {
        $this->shiftID = $shiftID;
        $this->comment = $comment;
        parent::__construct($htmlUtility);
    }

    /**
     * @return $this|Form
     */
    public function makeFields(): Form
    {
        $this->fields['shift'] = $this->htmlUtility::getHiddenFieldHTML( [
            'name' => 'shift',
            'value' => $this->shiftID,
        ] );
        $this->fields['add_comment'] = $this->htmlUtility::getHiddenFieldHTML( [
            'name' => 'add_comment',
            'value' => 1,
        ] );
        if ( !empty( $this->comment ) ) {
            $submit = 'Update';
            $cancel = 'Cancel';
        } else {
            $submit = 'Add';
            $cancel = 'No comment';
        }

        $this->fields['comment'] = $this->htmlUtility::getTextAreaFieldHTML( [
            'name' => 'comment',
            //'label' => 'Comment',
            'value' => $this->comment ?? '',
            'id' => 'inputComment',
            'placeholder' => 'Write comment...',
            'append' => $this->htmlUtility::getButton( [
                    'element' => 'input',
                    'type' => 'submit',
                    'class' => ['btn', 'btn-primary', 'btn-lg'],
                    //'id' => 'submit-button',
                    'value' => $submit . ' comment'
                ] ) . $this->htmlUtility::getButton( [
                    'element' => 'a',
                    'class' => ['btn', 'btn-lg', 'btn-secondary'],
                    'content' => '<span class="centre-vertically">' . $cancel . '</span><span class="invisible">' . $cancel . '</span>',
                    'href' => 'worker.php'
                ] )
        ] );
        return $this;
    }

    /**
     * @return string
     */
    public function render(): string
    {
        ob_start(); ?>
        <div class="container">
            <div class="row">
                <div class="col">
                    <div class="grey-bg px-3 p-3">
                        <form id="<?php echo $this->formID; ?>" class="add-comment-form" action="worker.php" method="post">
                            <fieldset>
                                <?php foreach ( $this->fields as $field ) {
                                    echo $field;
                                } ?>
                            </fieldset>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }


}