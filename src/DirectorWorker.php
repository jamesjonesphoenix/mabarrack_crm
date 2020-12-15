<?php

namespace Phoenix;

use DateTime;
use Phoenix\Entity\CurrentUser;
use Phoenix\Entity\SettingFactory;
use Phoenix\Page\AddCommentPageBuilder;
use Phoenix\Page\WorkerChoose\ChoosePageBuilderActivity;
use Phoenix\Page\WorkerChoose\ChoosePageBuilderFurniture;
use Phoenix\Page\WorkerChoose\ChoosePageBuilderJob;
use Phoenix\Page\WorkerHomePageBuilder;
use Phoenix\Page\WorkerPageBuilder;
use Phoenix\Utility\DateTimeUtility;
use Phoenix\Utility\HTMLTags;

/**
 * Class DirectorWorker
 *
 * @author James Jones
 * @package Phoenix
 *
 */
class DirectorWorker extends Director
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
     * @param URL              $url
     * @param HTMLTags         $htmlUtility
     * @param CurrentUser|null $user
     */
    public function __construct(PDOWrap $db, Messages $messages, URL $url, HTMLTags $htmlUtility, CurrentUser $user = null)
    {
        parent::__construct( $db, $messages, $url );
        $this->htmlUtility = $htmlUtility;
        if ( $user !== null ) {
            $this->user = $user;
        }
    }

    /**
     * @param array $inputArray
     * @return WorkerPageBuilder
     */
    public function getWorkerPageBuilder(array $inputArray = []): WorkerPageBuilder
    {
        switch( $inputArray['choose'] ?? '' ) {
            case 'job':
                return new ChoosePageBuilderJob( $this->db, $this->messages, $this->url );
            case 'furniture':
                return (new ChoosePageBuilderFurniture( $this->db, $this->messages, $this->url ))
                    ->setJobID( $inputArray['job'] ?? null );
            case 'activity':
                return (new ChoosePageBuilderActivity( $this->db, $this->messages, $this->url ))
                    ->setJob( $inputArray['job'] ?? null )
                    ->setFurnitureID( $inputArray['furniture'] ?? null );
        }
        if ( !empty( $inputArray['other_comment'] ) ) {
            return (new AddCommentPageBuilder( $this->db, $this->messages, $this->url ))
                ->setShiftID( $inputArray['shift'] ?? null );
        }
        return (new WorkerHomePageBuilder( $this->db, $this->messages, $this->url ))
            ->setDateStart( $inputArray['date_start'] ?? '' );
    }

    /**
     * @param array $inputArray from $_GET array
     * @return WorkerPageBuilder
     */
    public function getPageBuilder(array $inputArray = []): WorkerPageBuilder
    {
        if ( !isset( $this->user ) ) {
            $this->addError( 'Cannot load an employee page without a current user.' );
        }
        return $this->getWorkerPageBuilder( $inputArray )->setUser( $this->user );
    }

    /**
     * @throws \Exception
     */
    public function finishDay(): void
    {
        $this->user->finishCurrentShift();
        redirect( 'employee' );
        exit;
    }

    /**
     * @param array $inputArray
     * @return bool
     * @throws \Exception
     */
    public function doActions(array $inputArray = []): bool
    {
        $canStartOrFinishShifts = $this->user->healthCheck();
        if ( !empty( $canStartOrFinishShifts ) ) {
            $plural = count( $canStartOrFinishShifts ) > 1 ? 's' : '';
            $this->messages->add(
                '<h5 class="alert-heading">You cannot clock shifts due to error'
                . $plural
                . ':</h5>'
                . $this->htmlUtility::getListGroup( $canStartOrFinishShifts )
            );
            if ( !empty( $inputArray['choose'] ) ) {
                redirect( 'employee' );
            }
        }

        if ( !empty( $inputArray['additional_lunch'] ) ) { // When trying to start lunch after already having lunch
            $this->checkLunches();
        }

        if ( !empty( $inputArray['finish_day'] ) ) {
            $this->finishDay();
        }

        if ( !empty( $inputArray['next_shift'] ) ) {
            $this->nextShift(
                $inputArray['activity'] ?? null,
                $inputArray['job'] ?? null,
                $inputArray['furniture'] ?? null,
                $inputArray['comment'] ?? '',
                $inputArray['other_comment'] ?? null
            );
        }

        if ( !empty( $inputArray['add_comment'] ) ) {
            $this->addComment(
                $inputArray['shift'] ?? null,
                $inputArray['comment'] ?? ''
            );
        }

        $cutoffTime = (new SettingFactory( $this->db, $this->messages ))->getCutoffTime();
        $currentTime = date( 'H:i' );
        if ( DateTimeUtility::isAfter( $currentTime, $cutoffTime ) ) {
            $unfinishedShift = $this->user->shifts->getUnfinishedShifts()->getOne();
            if ( $unfinishedShift !== null ) {
                $this->messages->add( 'Automatically clocking off shift due to cutoff time being reached.', 'primary' );
                $unfinishedShift->finishShift();
            }
        }

        return true;
    }

    /**
     *
     */
    private function checkLunches(): void
    {
        if ( !$this->user->hadLunchToday() ) {
            return;
        }
        $numberOfLunches = 0;
        foreach ( $this->user->shifts->getShiftsToday()->getAll() as $shift ) {
            if ( $shift->isLunch() === 'lunch' ) {
                $numberOfLunches++;
            }
        }
        $numberOfLunches = $numberOfLunches === 1 ? 'a lunch period' : $numberOfLunches . ' lunch periods';
        $this->messages->add( "Are you sure you want to start lunch? You've already clocked " . $numberOfLunches . ' today.' . $this->htmlUtility::getButton( [
                'class' => 'btn btn-primary ml-3',
                'element' => 'a',
                'content' => 'Yes, Start Lunch',
                'href' => 'employee.php?job=0&activity=0&next_shift=1',
            ] ), 'warning' );
    }

    /**
     * @param int|null $activityID
     * @param int|null $jobID
     * @param int|null $furnitureID
     * @param string   $comment
     * @param null     $otherComment
     * @return bool
     * @throws \Exception
     */
    private function nextShift(int $activityID = null, int $jobID = null, int $furnitureID = null, string $comment = '', $otherComment = null): ?bool
    {
        if ( $activityID === null ) {
            return $this->addError( "Can't start new shift without an activity ID." );
        }
        if ( $jobID === null ) {
            return $this->addError( "Can't start new shift without a job ID." );
        }
        $newShift = $this->user->startNewShift(
            $activityID,
            $jobID,
            $furnitureID,
            $comment ?? '',
        );
        if ( empty( $otherComment ) ) {
            redirect( 'employee' );
            exit;
        }
        redirect( 'employee', ['other_comment' => 1, 'shift' => $newShift->id] );
        exit;
    }

    /**
     * @param        $shiftID
     * @param string $comment
     * @return bool|int|mixed
     * @throws \Exception
     */
    private function addComment(int $shiftID = null, string $comment = '')
    {
        if ( $shiftID === null ) {
            return false;
        }

        $shift = $this->user->shifts->getOne( $shiftID );
        if ( $shift === null ) {
            return false;
        }
        $shift->activityComments = $comment ?? '';
        return !empty( $shift->save() );
    }
}