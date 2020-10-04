<?php

namespace Phoenix\Page\DetailPage;

use Phoenix\Entity\Entity;
use Phoenix\Form\DetailPageForm\DetailPageEntityForm;
use Phoenix\Form\GroupByEntityForm;
use Phoenix\Page\EntityPageBuilder;
use function Phoenix\redirect;

/**
 * Class DetailPageBuilder
 *
 * @author James Jones
 *
 * @package Phoenix\DetailPage
 */
abstract class DetailPageBuilder extends EntityPageBuilder
{
    /**
     * @var Entity
     */
    protected Entity $entity;

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
        $entity = $entityFactory->getEntity( $entityID );
        if ( $entity === null ) {
            $this->messages->add( ucfirst( $entityFactory->getNew()->entityName ) . ' <span class="badge badge-danger">ID: ' . $entityID . "</span> doesn't exist. Redirected to main page." );
            redirect( 'index' );
            exit;
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
     * @return DetailPageEntityForm
     */
    abstract protected function getForm(): DetailPageEntityForm;

    /**
     * @return array
     */
    public function getReports(): array
    {
        return [];
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
        $this->page->addContent(
            $form->makeFields()->setDisplayEntityName( $this->getDisplayEntityName() )->render()
        );
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
            ->setNavLinks(
                ($this->getMenuItems())->getMenuItems()
            )
            ->setNavbarRightContent(
                $this->getGoToIDForm()->render()
            );
        if ( $entity->exists && !empty( $healthCheck = $entity->healthCheck() ) ) {
            $this->addError( '<h5 class="alert-heading">Problems with ' . $entity->entityName . ' <span class="badge badge-primary">ID: ' . $entity->id . '</span></h5>' . $this->HTMLUtility::getListGroup( $healthCheck ) );
        }
        $this->addForm();
        $this->addReports();

        $this->addTitle();
        return $this;
    }

    /**
     * @return string
     */
    public function getDisplayEntityName(): string
    {
        return $this->getEntity()->entityName;
    }

    /**
     * @return $this
     */
    public function addTitle(): self
    {
        $entityName = ucwords( $this->getDisplayEntityName() ) ?? 'Entity';
        $title = $this->entity->id !== null ? $entityName . ' Details' : 'New ' . $entityName;
        //' <span class="badge badge-primary"> ' . $entity->id  . '</span>
        $this->page->setTitle( $this->getEntity()->getIcon() . ' ' . $title );
        $this->page->setHeadTitle( $title );
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
     * @return GroupByEntityForm
     */
    public function getGroupByForm(): GroupByEntityForm
    {
        $entity = $this->getEntity();
        return (new GroupByEntityForm( $this->HTMLUtility, $entity ))
            ->makeHiddenFields( [
                'page' => 'detail',
                'entity' => $entity->entityName,
                'id' => $entity->id
            ] )
            ->setFormAction( '#archive-table' );
    }
}