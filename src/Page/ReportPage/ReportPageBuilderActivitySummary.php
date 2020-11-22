<?php


namespace Phoenix\Page\ReportPage;

use Phoenix\Entity\ShiftFactory;
use Phoenix\Entity\User;
use Phoenix\Entity\UserFactory;
use Phoenix\Form\PeriodicReportForm;
use Phoenix\Report\Report;

/**
 * Class ReportPageBuilderActivitySummary
 *
 * @author James Jones
 * @package Phoenix\Page
 *
 */
class ReportPageBuilderActivitySummary extends ReportPageBuilder
{
    /**
     * @var string
     */
    protected string $title = 'Activity Summary';

    /**
     * @var string
     */
    protected string $userFieldPlaceholder = 'All Workers';

    /**
     * @var User|null
     */
    protected ?User $user = null;

    /**
     * @param array $inputArgs
     * @return $this
     */
    public function setInputArgs(array $inputArgs = []): self
    {
        $this->setUser( !empty( $inputArgs['user'] ) ? $inputArgs['user'] : null );
        return parent::setInputArgs( $inputArgs );
    }

    /**
     * @param string $dateStart
     * @param string $dateFinish
     * @return $this
     */
    public function setDates(string $dateStart = '', string $dateFinish = ''): self
    {
        $this->getReportClient()->getShiftsReportBuilder()->setDates( $dateStart, $dateFinish );
        return parent::setDates($dateStart, $dateFinish);
    }

    /**
     * @param int|null $userID
     * @return $this
     */
    public function setUser(int $userID = null): self
    {
        if ( $userID !== null ) {
            $this->user = (new UserFactory( $this->db, $this->messages ))->getEntity( $userID, false );
        }

        return $this;
    }

    /**
     * @return Report[]
     */
    public function getReports(): array
    {
        $reports[] = $this->getReportClient()->getShiftsReportBuilder()
            ->setUser( $this->user )
            ->getActivitySummary(
                $this->sortActivitiesBy,
                $this->groupActivities
            )->disableCollapseButton();

        return $reports;
    }

    /**
     * @param string $title
     * @return string
     */
    protected function makeTitle(string $title = ''): string
    {
        $username = $this->user !== null ? '<small>' . $this->HTMLUtility::getBadgeHTML( $this->user->getFirstName() ) . '</small> ' : '';
        return $username . parent::makeTitle( $title );
    }

    /**
     * @return PeriodicReportForm
     */
    public function getPeriodicReportForm(): PeriodicReportForm
    {
        $form = parent::getPeriodicReportForm();
        if ( $this->user !== null ) {
            $form->setUser( $this->user );
        }
        return $form
            ->makeUserField(
                (new UserFactory( $this->db, $this->messages ))->getOptionsArray(),
                $this->userFieldPlaceholder
            );
    }


}