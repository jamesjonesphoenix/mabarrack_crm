<?php


namespace Phoenix\Page\DetailPage;

use Phoenix\Entity\Entity;
use Phoenix\Form\DetailPageForm;
use Phoenix\Form\GroupByForm;
use Phoenix\Page\EntityPageBuilder;
use function Phoenix\redirect;

/**
 * Class DetailPageBuilder
 *
 * @author James Jones
 * @package Phoenix\DetailPage
 */
abstract class DetailPageBuilder extends EntityPageBuilder
{
    /**
     * @var Entity
     */
    protected Entity $entity;

    /**
     * @var DetailPage
     */
    protected DetailPage $page;

    /**
     * @var string
     */
    protected string $groupBy = '';

    /**
     * @param int|null $entityID
     * @return $this
     */
    public function setEntity(int $entityID = null): self
    {
        $entityFactory = $this->getEntityFactory();
        if ( $entityID === null ) {
            $this->entity = $entityFactory->getNew(); //New entity
            return $this;
        }
        //Existing entity
        $entity = $entityFactory->getEntities( ['ID' => $entityID], true )[$entityID];
//d($entity);
        if ( $entity === null ) {
            $this->messages->add( ucfirst( $entityFactory->getNew()->entityName ) . ' with id <strong>' . $entityID . '</strong> does not exist. Redirected to main page.' );
            redirect( 'index' );
        }
        $this->entity = $entity;
        return $this;
    }

    /**
     * @param array $inputArgs
     * @return $this
     */
    public function setInputArgs(array $inputArgs = []): self
    {
        $this->setEntity( $inputArgs['id'] ?? null );
        if ( !empty( $inputArgs['group_by'] ) ) {
            $this->groupBy = $inputArgs['group_by'];
        }
        return $this;
    }

    /**
     * @return Entity
     */
    public function getEntity(): Entity
    {
        return $this->entity;
    }

    /**
     * @return DetailPageForm
     */
    abstract protected function getForm(): DetailPageForm;

    /**
     * @return array
     */
    public function getReports(): array
    {
        return [];
    }

    /**
     * @return DetailPage
     */
    protected function getNewPage(): DetailPage
    {
        return new DetailPage( $this->HTMLUtility );
    }

    /**
     * @return $this
     */
    public function addForm(): self
    {
        $form = $this->getForm();

        if ( $form->isDisabled() && $form->getDBAction() === 'add' ) {
            $this->messages->add( '<strong>Error:</strong> Adding a new ' . $this->getEntityFactory()->getEntityName() . ' is not allowed.' );
        }

        $this->page->setForm(
            $form->makeFields()->render()
        );
        return $this;
    }

    /**
     * @return $this
     */
    public function addReports(): self
    {
        return $this;
    }



    /**
     * @return $this
     */
    public function buildPage(): self
    {
        $this->page = $this->getNewPage();
        $entity = $this->getEntity();
        $this->page
            ->setEntity( $entity )
            ->setNavLinks(
                ($this->getMenuItems())->getMenuItems()
            )
            ->setGoToIDForm(
                $this->getGoToIDForm()->render()
            );
        if ( $entity->exists && !empty( $healthCheck = $entity->healthCheck()) ) {
            $this->addError( '<h5 class="alert-heading">Problems with ' . $entity->entityName . ' ID: <strong>' . $entity->id . '</strong>:</h5>' . $healthCheck );
        }

        //setGoToIDForm

        $this->addForm();
        $this->addReports();
        return $this;
    }

    /**
     * @return GroupByForm
     */
    public function getGroupByForm(): GroupByForm
    {
        $entity = $this->getEntity();
        return (new GroupByForm( $this->HTMLUtility, $entity ))
            ->makeHiddenFields( [
                'page' => 'detail',
                'entity' => $entity->entityName,
                'id' => $entity->id
            ] )
            ->setFormAction( '#archive-table' );
    }

}