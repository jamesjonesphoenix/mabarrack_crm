<?php

namespace Phoenix\Form;


use Phoenix\Entity\Entity;
use Phoenix\URL;
use Phoenix\Utility\FormFields;

/**
 * @author James Jones
 *
 * Class GroupByEntityForm
 *
 * @package Phoenix\EntityForm
 *
 */
class GroupByEntityForm extends EntityForm
{
    /**
     * HTML id property of form
     *
     * @var string
     */
    public string $formID = 'group_by';

    /**
     * @var string
     */
    private string $formAction = '';

    /**
     * @var URL
     */
    private URL $url;

    /**
     * EntityForm constructor.
     *
     * @param FormFields $htmlUtility
     * @param Entity     $entity
     * @param URL        $url
     */
    public function __construct(FormFields $htmlUtility, Entity $entity, URL $url)
    {
        $this->url = $url;
        parent::__construct( $htmlUtility, $entity );
    }

    /**
     * @param array  $columns
     * @param string $groupBy
     * @return $this
     */
    public function makeFields(array $columns = [], string $groupBy = ''): self
    {
        if ( !empty( $groupBy ) ) {
            $selected = $groupBy;
            $columns[$groupBy] = 'Grouped by <strong>' . $columns[$groupBy] . '</strong>';
        }

        $this->fields['group_by'] = $this->htmlUtility::getOptionDropdownFieldHTML( [
            'options' => $columns,
            'placeholder' => 'Group by...',
            'selected' => $selected ?? '',
            'name' => 'group_by',
            'append' => '<button class="btn btn-primary" type="submit">Group</button>'
        ] );
        $inputArgs = $this->url->getQueryArgs();
        unset( $inputArgs['group_by'] );
        $this->makeHiddenFields( $inputArgs );
        return $this;
    }



    /**
     * @param string $formAction
     * @return $this
     */
    public function setFormAction(string $formAction = ''): self
    {
        $this->formAction = $formAction;
        return $this;
    }

    /**
     * @return string
     */
    public function render(): string
    {
        ob_start(); ?>
        <form action="<?php echo $this->formAction; ?>" class="form-inline group-by-form"><?php
            echo $this->fields['group_by'];
            foreach ( $this->fields['hidden'] ?? [] as $field ) {
                echo $field;
            } ?>
        </form>
        <?php return ob_get_clean();
    }
}