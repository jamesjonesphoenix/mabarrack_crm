<?php


namespace Phoenix\Form;


/**
 * Class GoToIDEntityForm
 *
 * @author James Jones
 * @package Phoenix\EntityForm
 *
 */
class GoToIDEntityForm extends EntityForm
{
    /**
     * HTML id property of form
     *
     * @var string
     */
    public string $formID = 'go-to-id-form';

    /**
     * @return $this
     */
    public function makeFields(): self
    {
        $entityName = $this->entity->entityName;
        $this->fields['entity'] = $this->htmlUtility::getHiddenFieldHTML( [
            'name' => 'entity',
            'value' => $entityName,
        ] );
        $this->fields['page'] = $this->htmlUtility::getHiddenFieldHTML( [
            'name' => 'page',
            'value' => 'detail',
        ] );

        $this->fields['id'] = $this->htmlUtility::getIntegerFieldHTML( [
            'name' => 'id',
            //'value' => $entityName,
            'placeholder' => ucfirst($entityName) . ' ID',
            'max' => 1000000
        ] );

        return $this;
    }

    /**
     * @return string
     */
    public function render(): string
    {
        ob_start(); ?>
        <form action="<?php echo $this->entity->getLink( false ); ?>" class="form-inline my-2 my-lg-0 ml-2 search-by-id search-by-id-<?php echo $this->entity->entityName; ?> float-left">
            <div class="input-group">
                <?php echo $this->fields['id'] . $this->fields['entity'] . $this->fields['page']; ?>
                <div class="input-group-append">
                    <button class="btn btn-primary my-2 my-sm-0" type="submit">Go to <?php echo ucfirst( $this->entity->entityName ); ?></button>
                </div>
            </div>
        </form>
        <?php return ob_get_clean();
    }
}