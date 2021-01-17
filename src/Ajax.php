<?php

namespace Phoenix;


use Phoenix\Entity\ActivityFactory;
use Phoenix\Entity\CustomerFactory;
use Phoenix\Entity\Entity;
use Phoenix\Entity\EntityFactory;
use Phoenix\Entity\FurnitureFactory;
use Phoenix\Entity\JobFactory;
use Phoenix\Entity\SettingFactory;
use Phoenix\Entity\ShiftFactory;
use Phoenix\Entity\UserFactory;
use Phoenix\Utility\HTMLTags;

/**
 * Class Ajax
 *
 * @package Phoenix
 */
class Ajax extends AbstractCRM
{
    /**
     * @var string
     */
    private string $action = '';

    /**
     * @var Entity|null
     */
    private ?Entity $entity;

    /**
     * @var array
     */
    private array $inputData = [];

    /**
     * @var bool
     */
    private bool $initialised = false;

    /**
     * @var array
     */
    private array $returnData = [];

    /**
     * @var HTMLTags
     */
    private HTMLTags $htmlUtility;

    /**
     * Base constructor.
     *
     * @param PDOWrap|null  $db
     * @param Messages|null $messages
     * @param HTMLTags|null $htmlUtility
     */
    public function __construct(PDOWrap $db = null, Messages $messages = null, HTMLTags $htmlUtility = null)
    {
        if ( $htmlUtility !== null ) {
            $this->htmlUtility = $htmlUtility;
        }
        parent::__construct( $db, $messages );

    }

    /**
     * @param array $data
     * @return bool
     */
    public function init(array $data = []): bool
    {
        if ( empty( $data['db_action'] ) ) {
            return $this->addError( 'No action requested.' );
        }
        $this->action = $data['db_action'];

        if ( empty( $data['entity'] ) ) {
            return $this->addError( 'No entity type input.' );
        }
        $entityName = $data['entity'] ?? '';
        $entityFactory = $this->getEntityFactory( $entityName );

        if ( $entityFactory === null ) {
            return false;
        }

        $id = phValidateID( $data['ID'] ?? null );

        switch( $this->action ) {
            case 'update':
            case 'delete-dry-run':
            case 'delete-for-real':
                if ( !isset( $data['ID'] ) ) {
                    return $this->addError( "Can't update " . $entityName . '. ID missing from input.' );
                }
                if ( !$id ) {
                    return $this->addError( "Can't update " . $entityName . '. ID is not a valid number.' );
                }
                $this->entity = $entityFactory->getEntity( $id );

                if ( $this->entity === null || !$this->entity->exists ) {
                    return $this->addError( ucwords( $this->entity->entityName )
                        . $this->entity->getIDBadge( $id, 'danger' )
                        . " doesn't exist in database." );
                }
                break;
            case 'add':
                if ( $id !== false ) {
                    $existingEntity = $entityFactory->getEntity( $id );
                    if ( $existingEntity !== null ) {
                        return $this->addError( ucwords( $existingEntity->entityName )
                            . $existingEntity->getIDBadge( $id, 'danger' )
                            . ' already exists. ' . $this->htmlUtility::getViewButton(
                                $existingEntity->getLink(),
                                'View ' . ucfirst( $existingEntity->entityName )
                            )
                        );
                    }
                }
                $this->entity = $entityFactory->getNew();
                break;
            default:
                return $this->addError( 'Invalid action requested.' );
        }


        unset( $data['db_action'], $data['entity'] );
        if ( empty( $data ) ) {
            return $this->addError( ucwords( $this->entity->entityName ) . ' data missing from input.' );
        }
        $this->inputData = $data;
        $this->initialised = true;
        return true;
    }

    /**
     * @param string $entityName
     * @return EntityFactory
     */
    private function getEntityFactory(string $entityName = ''): ?EntityFactory
    {
        switch( $entityName ) {
            case 'activities':
                return new ActivityFactory( $this->db, $this->messages );
            case 'customer':
                return new CustomerFactory( $this->db, $this->messages );
            case 'furniture':
                return new FurnitureFactory( $this->db, $this->messages );
            case 'job':
                return new JobFactory( $this->db, $this->messages );
            case 'setting':
                return new SettingFactory( $this->db, $this->messages );
            case 'shift':
                return new ShiftFactory( $this->db, $this->messages );
            case 'user':
                return new UserFactory( $this->db, $this->messages );
        }
        $this->addError( ucfirst( $entityName ) . ' is not a legitimate entity.' );
        return null;
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function doFormAction(): bool
    {
        if ( !$this->initialised ) {
            return false;
        }
        //$entity = $this->entity->init( $this->inputData );
        $entity = $this->entity;
        // $this->addError(print_r($this->inputData, true));
        foreach ( $this->inputData as $key => $value ) {
            $entity->setProperty( $key, $value );
        }

        $entityFactory = $this->getEntityFactory( $entity->entityName );
        if ( $entityFactory === null ) {
            return false;
        }
        $entity = $entityFactory->provisionEntity( $entity, true );

        if ( $this->action === 'add' || $this->action === 'update' ) {
            $result = $entity->save();
        } elseif ( $this->action === 'delete-dry-run' ) {
            $result = $entity->deleteDryRun();
        } elseif ( $this->action === 'delete-for-real' ) {
            $result = $entity->delete();
        }

        $this->returnData['id'] = $entity->id;

        if ( !empty( $result ) ) {
            $this->returnData['result'] = 'success';
            if ( $this->action === 'add' ) {
                $this->returnData['redirect'] = true;
                //$this->returnData['redirectURL'] = $this->entity->entityName . '.php?id=' . $entity->id;
                $this->returnData['redirectURL'] = $this->entity->getLink();
            } elseif ( $this->action === 'delete-for-real' ) {
                $this->returnData['redirect'] = true;
                $this->returnData['redirectURL'] = $this->entity->getArchiveLink() . '&limit=1000';
            }
            //formEntity + '.php?id=' + data['id']
        }
        return true;
    }

    /**
     *
     * @throws
     */
    public function returnData(): void
    {
        if ( empty( $this->returnData['redirect'] ) ) {
            $this->returnData['message'] = $this->messages->getMessagesHTML();
        }
        echo json_encode( $this->returnData );
        die();
    }
}