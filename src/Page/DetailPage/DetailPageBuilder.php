<?php

namespace Phoenix\Page\DetailPage;

use Phoenix\Entity\Entity;
use Phoenix\Form\DetailPageForm\DetailPageEntityForm;
use Phoenix\Form\GroupByEntityForm;
use Phoenix\Messages;
use Phoenix\Page\EntityPageBuilder;
use Phoenix\PDOWrap;
use Phoenix\URL;
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
     * @param int|null $entityID
     * @return $this
     */
    public function setEntity(int $entityID = null, $preFillArgs = []): self
    {
        $entityFactory = $this->getEntityFactory();
        if ( $entityID === null ) {
            $this->entity = $entityFactory->getNew(); //New entity
            return $this;
        }
        //Existing entity
        $entity = $entityFactory->getEntity( $entityID );
        if ( $entity === null ) {
            $entity = $entityFactory->getNew();
            $this->messages->add( ucfirst( $entity->entityName )
                . $entity->getIDBadge( $entityID, 'danger' )
                . " doesn't exist. Redirected to main page." );
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

        $id = $inputArgs['id'] ?? null;
        $this->setEntity( $id );

        $preFillArgs = $inputArgs['prefill'] ?? null;
        if ( $id === null && is_array( $preFillArgs ) ) { //prefill

            $entity = $this->getEntity();

            foreach ( $preFillArgs as $argName => $preFillArg ) {
                $entity->$argName = $preFillArg;
            }
        }

        return parent::setInputArgs( $inputArgs );
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
            $this->messages->add(
                '<strong>Error:</strong> Adding a new '
                . $this->getEntityFactory()->getEntityName()
                . ' is not allowed.'
            );
        }
        $this->page->addContent(
            $form
                ->makeFields()
                ->setDisplayEntityName(
                    $this->getDisplayEntityName()
                )
                ->render()
        );
        return $this;
    }

    /**
     * @return $this
     */
    public function buildPage(): self
    {
        $entity = $this->getEntity();
        $this->page = $this->getNewPage()
            ->setNavLinks(
                ($this->getMenuItems())->getMenuItems()
            )
            ->setNavbarRightContent(
                $this->getGoToIDForm()->render()
            );
        if ( $entity->exists && !empty( $healthCheck = $entity->healthCheck() ) ) {
            $plural = count( $healthCheck ) > 1 ? 's' : '';
            $entityBadge = !empty( $entity->name ) ? $this->HTMLUtility::getBadgeHTML( $entity->name, 'danger' ) : $entity->getIDBadge( null, 'danger' );
            $this->addError(
                '<h5 class="alert-heading">Problem'
                . $plural
                . ' with '
                . $entity->entityName
                . ' '
                . $entityBadge
                . '</h5>'
                . $this->HTMLUtility::getListGroup( $healthCheck ) );
        }
        $this
            ->addForm()
            ->addReports()
            ->addTitle();
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
        $this->page
            ->setTitle( $this->getMenuItems()->getIcon() . ' ' . $title )
            ->setHeadTitle( $title );
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
        return (new GroupByEntityForm( $this->HTMLUtility, $entity, $this->getURL() ))
            ->makeHiddenFields( [
                'page' => 'detail',
                'entity' => $entity->entityName,
                'id' => $entity->id
            ] );
    }

    /**
     * @param PDOWrap  $db
     * @param Messages $messages
     * @param URL      $url
     * @param string   $entityType
     * @return static|null
     */
    public static function create(PDOWrap $db, Messages $messages, URL $url, string $entityType = ''): ?self
    {
        switch( $entityType ) {
            case 'customer':
            case 'customers':
                return new DetailPageBuilderCustomer( $db, $messages, $url );
            case 'furniture':
                return new DetailPageBuilderFurniture( $db, $messages, $url );
            case 'job':
            case 'jobs':
                return new DetailPageBuilderJob( $db, $messages, $url );
            case 'shift':
            case 'shifts':
                return new DetailPageBuilderShift( $db, $messages, $url );
            case 'user':
            case 'users':
                return new DetailPageBuilderUser( $db, $messages, $url );
            case 'setting':
            case 'settings':
                return new DetailPageBuilderSetting( $db, $messages, $url );
        }
        return null;
    }
}