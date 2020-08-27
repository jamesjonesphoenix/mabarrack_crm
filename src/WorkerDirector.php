<?php


namespace Phoenix;


use Phoenix\Entity\CurrentUser;
use Phoenix\Page\PageBuilder;
use Phoenix\Page\WorkerChoose\ChoosePageBuilderActivity;
use Phoenix\Page\WorkerChoose\ChoosePageBuilderFurniture;
use Phoenix\Page\WorkerChoose\ChoosePageBuilderJob;
use Phoenix\Page\WorkerHomePageBuilder;

/**
 * Class WorkerDirector
 *
 * @author James Jones
 * @package Phoenix
 *
 */
class WorkerDirector extends AbstractCRM
{
    /**
     * @var CurrentUser
     */
    private CurrentUser $user;

    /**
     * Base constructor.
     *
     * @param PDOWrap|null     $db
     * @param Messages|null    $messages
     * @param CurrentUser|null $user
     */
    public function __construct(PDOWrap $db, Messages $messages, CurrentUser $user = null)
    {
        parent::__construct( $db, $messages );
        if ( $user !== null ) {
            $this->user = $user;
        }
    }

    /**
     * @param array $inputArray from $_GET array
     * @return PageBuilder
     */
    public function getPageBuilder(array $inputArray = []): PageBuilder
    {
        if ( !isset( $this->user ) ) {
            $this->addError( 'Cannot load a worker page without a current user.' );
        }

        switch( $inputArray['choose'] ?? '' ) {
            case 'job':
                $pageBuilder = new ChoosePageBuilderJob( $this->db, $this->messages );
                break;
            case 'furniture':
                $pageBuilder = (new ChoosePageBuilderFurniture( $this->db, $this->messages ))
                    ->setJobID( $inputArray['job'] ?? null );
                break;
            case 'activity':
                $pageBuilder = (new ChoosePageBuilderActivity( $this->db, $this->messages ))
                    ->setJobID( $inputArray['job'] ?? null )
                    ->setFurnitureID( $inputArray['furniture'] ?? null );
                break;
            default:
                $pageBuilder = (new WorkerHomePageBuilder( $this->db, $this->messages ))
                    ->addStartDate( $inputArray['start_date'] ?? '' );
        }
        return $pageBuilder->addUser( $this->user );
    }

    /**
     * @throws \Exception
     */
    public function finishDay(): void
    {
        $this->user->finishCurrentShift();
        redirect( 'worker' );
        exit;
    }

    /**
     * @param array $inputArray
     * @return bool
     * @throws \Exception
     */
    public function doWorkerAction(array $inputArray = []): bool
    {
        $canStartOrFinishShifts = $this->user->healthCheck();
        if ( !empty( $canStartOrFinishShifts ) ) {
            $this->messages->add( '<h5 class="alert-heading">You cannot clock shifts due to errors:</h5>' . $canStartOrFinishShifts );
            if ( !empty( $inputArray['choose'] ) ) {
                redirect( 'worker' );
            }
        }

        $success = true;
        if ( !empty( $inputArray['finish_day'] ) ) {
            $this->finishDay();
        }
        if ( !empty( $inputArray['next_shift'] ) ) {
            if ( !isset( $inputArray['activity'] ) ) {
                return $this->addError( "Can't start new shift without an activity ID." );
            }
            if ( !isset( $inputArray['job'] ) ) {
                return $this->addError( "Can't start new shift without a job ID." );
            }
            $this->user->startNewShift(
                $inputArray['activity'],
                $inputArray['job'],
                $inputArray['furniture'] ?? null,
                $inputArray['comment'] ?? '',
            );
            //redirect( 'worker' );
            //exit;
        }
        return true;
    }
}