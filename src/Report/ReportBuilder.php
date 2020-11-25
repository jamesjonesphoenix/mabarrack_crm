<?php


namespace Phoenix\Report;


use Phoenix\Entity\Entities;
use Phoenix\Entity\EntityFactory;
use Phoenix\Report\Shifts\ShiftsReportBuilder;
use Phoenix\Utility\DateTimeUtility;
use Phoenix\Utility\HTMLTags;

/**
 * Class ReportBuilder
 *
 * @author James Jones
 * @package Phoenix\Report
 *
 */
abstract class ReportBuilder
{
    /**
     * @var string
     */
    protected string $dateStart = '';

    /**
     * @var string
     */
    protected string $dateFinish = '';

    /**
     * @var EntityFactory
     */
    protected EntityFactory $entityFactory;

    /**
     * @var ReportFactory
     */
    private ReportFactory $factory;

    /**
     * @var HTMLTags
     */
    protected HTMLTags $htmlUtility;

    /**
     * @var Entities|null
     */
    protected ?Entities $entities = null;

    /**
     * @var Report
     */
    protected Report $report;

    /**
     * Report Factory constructor.
     *
     * @param ReportFactory $factory
     * @param HTMLTags      $htmlUtility
     * @param EntityFactory $entityFactory
     */
    public function __construct(ReportFactory $factory, HTMLTags $htmlUtility, EntityFactory $entityFactory)
    {
        $this->factory = $factory;
        $this->htmlUtility = $htmlUtility;
        $this->entityFactory = $entityFactory;
    }

    /**
     * @return ReportFactory
     */
    protected function getFactory(): ReportFactoryBase
    {
        return $this->factory;
    }

    /**
     * @return string
     */
    public function getDateStart(): string
    {
        return $this->dateStart;
    }

    /**
     * @return string
     */
    public function getDateFinish(): string
    {
        return $this->dateFinish;
    }

    /**
     * @param string $dateStart
     * @param string $dateFinish
     * @return $this
     */
    public function setDates(string $dateStart = '', string $dateFinish = ''): self
    {
        $this->resetEntities();
        $this->dateStart = DateTimeUtility::validateDate( $dateStart ) ? $dateStart : '';
        $this->dateFinish = DateTimeUtility::validateDate( $dateFinish ) ? $dateFinish : '';
        return $this;
    }

    /**
     *
     */
    public function resetEntities(): void
    {
        $this->entities = null;
    }

    /**
     * @return $this
     */
    public function provisionReport(): self
    {
        $this->provisionReportStrings();

        $this->report->setEntities(
            $this->getEntities()
        );
        return $this;
    }

    /**
     * @return string
     */
    abstract public function getDefaultEmptyMessage(): string;

    /**
     * @param string $defaultMessage
     * @return $this
     */
    public function provisionReportStrings(  string $defaultMessage = ''): self
    {
        $emptyMessage = $this->validateInputs();
        if ( empty( $emptyMessage ) ) {
            $emptyMessage = !empty($defaultMessage) ? $defaultMessage : $this->getDefaultEmptyMessage();
        } else {
            $this->report->setEmptyMessageClass( 'danger' );
        }
        $this->report
            ->setEmptyMessage( $emptyMessage )
            ->setTitle(
                $this->annotateTitleWithInputs(
                    $this->report->getTitle()
                )
            );
        return $this;
    }

    /**
     * @param string $title
     * @return string
     */
    public function annotateTitleWithInputs(string $title = ''): string
    {
        if ( empty( $this->dateStart ) || empty( $this->dateFinish ) ) {
            return $title;
        }
        return $title
            . ' <small>'
            . $this->getDateString()
            . '</small>';
    }

    /**
     * @param string $contextualClass
     * @return string
     */
    public function getDateString(string $contextualClass = 'light'): string
    {
        if ( empty( $this->dateStart ) || empty( $this->dateFinish ) ) {
            return '';
        }
        return $this->htmlUtility::getBadgeHTML( date( 'd-m-Y', strtotime( $this->dateStart ) ), $contextualClass )
            . ' to '
            . $this->htmlUtility::getBadgeHTML( date( 'd-m-Y', strtotime( $this->dateFinish ) ), $contextualClass );
    }

    /**
     * @return string
     */
    public function validateInputs(): string
    {
        if ( empty( $this->dateStart ) ) {
            if ( empty( $this->dateFinish ) ) {
                return 'Please set a start and finish date for report.';
            }
            return 'Please set a start date for report.';
        }
        if ( empty( $this->dateFinish ) ) {
            return 'Please set a finish date for report.';
        }
        $differenceDays = (integer)(date_diff( date_create( $this->dateStart ), date_create( $this->dateFinish ) ))->format( '%R%a' );
        if ( $differenceDays < 0 ) {
            return "Can't generate report because end date "
                . $this->htmlUtility::getBadgeHTML( date( 'd-m-Y', strtotime( $this->dateFinish ) ), 'danger' )
                . ' is before start date'
                . $this->htmlUtility::getBadgeHTML( date( 'd-m-Y', strtotime( $this->dateStart ) ), 'danger' )
                . '.';
        }
        if ( $differenceDays === 0 ) {
            return "Can't generate report because start date and end date are identical.";
        }
        return '';
    }
}