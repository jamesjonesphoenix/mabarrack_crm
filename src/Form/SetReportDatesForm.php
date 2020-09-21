<?php


namespace Phoenix\Form;


use Phoenix\Utility\FormFields;

/**
 * Class GoToIDEntityForm
 *
 * @author James Jones
 * @package Phoenix\EntityForm
 *
 */
class SetReportDatesForm extends Form
{
    /**
     * HTML id property of form
     *
     * @var string
     */
    public string $formID = 'set-report-dates-form';

    /**
     * @var string
     */
    private string $dateStart;

    /**
     * @var string
     */
    private string $dateFinish;

    /**
     * @var string
     */
    private string $reportType;

    /**
     * EntityForm constructor.
     *
     * @param FormFields $htmlUtility
     * @param string     $dateStart
     * @param string     $dateFinish
     * @param string     $reportType
     */
    public function __construct(FormFields $htmlUtility, string $dateStart = '', string $dateFinish = '', string $reportType = '')
    {
        $this->htmlUtility = $htmlUtility;
        $this->dateStart = $dateStart;
        $this->dateFinish = $dateFinish;
        $this->reportType = $reportType;
    }

    /**
     * @return $this
     */
    public function makeFields(): self
    {
        $this->fields['report'] = $this->htmlUtility::getHiddenFieldHTML( [
            'name' => 'report',
            'value' => $this->reportType,
        ] );
        $this->fields['page'] = $this->htmlUtility::getHiddenFieldHTML( [
            'name' => 'page',
            'value' => 'report',
        ] );

        $this->fields['date_start'] = $this->htmlUtility::getDateFieldHTML( [
            'name' => 'date_start',
            'placeholder' => 'Start Date',
            'value' => $this->dateStart
        ] );
        $this->fields['date_finish'] = $this->htmlUtility::getDateFieldHTML( [
            'name' => 'date_finish',
            'placeholder' => 'Finish Date',
            'value' => $this->dateFinish
        ] );

        return $this;
    }

    /**
     * @return string
     */
    public function render(): string
    {
        ob_start(); ?>
        <div class="container mb-4 position-relative">
            <div class="row">
                <div class="col">
                    <form id="<?php echo $this->formID; ?>" class="form-inline my-2 my-lg-0 ml-3 float-left">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">Choose Date Range</span>
                            </div>
                            <?php echo $this->fields['date_start']; ?>
                            <span class="input-group-text">to</span>
                            <?php echo $this->fields['date_finish'] . $this->fields['page'] . $this->fields['report']; ?>
                            <div class="input-group-append">
                                <button class="btn btn-primary my-2 my-sm-0" type="submit">Generate Report</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <?php return ob_get_clean();
    }
}