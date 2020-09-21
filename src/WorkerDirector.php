<?php


namespace Phoenix;


use Phoenix\Entity\CurrentUser;
use Phoenix\Entity\ShiftFactory;
use Phoenix\Page\AddCommentPageBuilder;
use Phoenix\Page\PageBuilder;
use Phoenix\Page\WorkerChoose\ChoosePageBuilderActivity;
use Phoenix\Page\WorkerChoose\ChoosePageBuilderFurniture;
use Phoenix\Page\WorkerChoose\ChoosePageBuilderJob;
use Phoenix\Page\WorkerHomePageBuilder;
use Phoenix\Utility\HTMLTags;

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
     * @var HTMLTags
     */
    private HTMLTags $htmlUtility;

    /**
     * Base constructor.
     *
     * @param PDOWrap|null     $db
     * @param Messages|null    $messages
     * @param HTMLTags         $htmlUtility
     * @param CurrentUser|null $user
     */
    public function __construct(PDOWrap $db, Messages $messages, HTMLTags $htmlUtility, CurrentUser $user = null)
    {
        parent::__construct( $db, $messages );
        $this->htmlUtility = $htmlUtility;
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
                if ( !empty( $inputArray['other_comment'] ) ) {
                    $pageBuilder = (new AddCommentPageBuilder( $this->db, $this->messages ))
                        ->setShiftID( $inputArray['shift'] ?? null );
                } else {
                    $pageBuilder = (new WorkerHomePageBuilder( $this->db, $this->messages ))
                        ->setStartDate( $inputArray['start_date'] ?? '' );
                }
        }
        return $pageBuilder->setUser( $this->user );
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
        //when trying to start lunch after already having lunch
        if ( !empty( $inputArray['additional_lunch'] ) && $this->user->hadLunchToday() ) {
            $this->messages->add( "Are you sure you want to start lunch? You've already clocked a lunch period today." . $this->htmlUtility::getButton( [
                    'class' => 'btn btn-primary ml-3',
                    'element' => 'a',
                    'content' => 'Yes, Start Lunch',
                    'href' => 'worker.php?job=0&activity=0&next_shift=1',
                ] ), 'warning' );
        }
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
            $newShift = $this->user->startNewShift(
                $inputArray['activity'],
                $inputArray['job'],
                $inputArray['furniture'] ?? null,
                $inputArray['comment'] ?? '',
            );
            if ( empty( $inputArray['other_comment'] ) ) {
                redirect( 'worker' );
                exit;
            }
            redirect( 'worker', ['other_comment' => 1, 'shift' => $newShift->id] );
            exit;
        }

        if ( !empty( $inputArray['add_comment'] ) ) {
            $shiftID = $inputArray['shift'] ?? null;
            if($shiftID !== null) {
                $shift = (new ShiftFactory( $this->db, $this->messages ))->getEntity( $inputArray['shift'] ?? null );
                if ( empty( $shift ) ) {

                }
                $shift->activityComments = $inputArray['comment'] ?? '';
                $shift->save();
            }
        }
        return true;
    }
}