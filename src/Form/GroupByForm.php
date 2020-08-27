<?php

namespace Phoenix\Form;


/**
 * @author James Jones
 *
 * Class GroupByForm
 *
 * @package Phoenix\Form
 *
 */
class GroupByForm extends Form
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

        return $this;
    }

    /**
     * @param array $inputArgs
     * @return $this
     */
    public function makeHiddenFields(array $inputArgs = []): self
    {
        foreach ( $inputArgs as $inputArgName => $inputArgValue ) {
            if ( is_string( $inputArgValue ) || is_numeric( $inputArgValue ) ) {
                $this->fields['hidden'][$inputArgName] = $this->htmlUtility::getHiddenFieldHTML( [
                    'name' => $inputArgName,
                    'value' => $inputArgValue,
                ] );
            } elseif ( is_iterable( $inputArgValue ) ) {
                foreach ( $inputArgValue as $nestedArgName => $nestedArgValue ) {
                    if ( is_string( $nestedArgValue ) ) {
                        $this->fields['hidden'][$inputArgName . '_' . $nestedArgName] = $this->htmlUtility::getHiddenFieldHTML( [
                            'name' => $inputArgName . '[' . $nestedArgName . ']',
                            'value' => $nestedArgValue,
                        ] );
                    }
                }
            }
        }
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
        ob_start();
        ?>
        <form action="<?php echo $this->formAction; ?>" class="form-inline group-by-form"><?php
            echo $this->fields['group_by'];
            foreach ( $this->fields['hidden'] ?? [] as $field ) {
                echo $field;
            } ?>
        </form>
        <?php
        return ob_get_clean();
    }
}