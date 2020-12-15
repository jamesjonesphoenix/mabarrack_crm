<?php


namespace Phoenix\Form;


use Phoenix\Entity\Customer;
use Phoenix\Entity\User;
use Phoenix\URL;
use Phoenix\Utility\DateTimeUtility;
use Phoenix\Utility\FormFields;

/**
 * Class PeriodicReportForm
 *
 * @author James Jones
 * @package Phoenix\EntityForm
 *
 */
class PeriodicReportForm extends Form
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
     * @var User|null
     */
    private ?User $user = null;

    /**
     * @var Customer|null
     */
    private ?Customer $customer = null;

    /**
     * @var bool
     */
    private bool $disableDateFinish = false;

    /**
     * @var URL
     */
    private URL $url;

    /**
     * @var string
     */
    private string $jobType = '';

    /**
     * @var bool
     */
    private bool $exclusiveJobType = false;


    /**
     * PeriodicReportForm constructor.
     *
     * @param FormFields $htmlUtility
     * @param URL        $url
     */
    public function __construct(FormFields $htmlUtility, URL $url)
    {
        $this->url = $url;
        parent::__construct( $htmlUtility );
    }

    /**
     * @param string $dateStart
     * @param string $dateFinish
     * @return $this
     */
    public function setDates(string $dateStart = '', string $dateFinish = ''): self
    {
        $this->dateStart = DateTimeUtility::validateDate( $dateStart ) ? $dateStart : '';
        $this->dateFinish = DateTimeUtility::validateDate( $dateFinish ) ? $dateFinish : '';
        return $this;
    }

    /**
     * @param User|null $user
     * @return $this
     */
    public function setUser(User $user = null): self
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @param Customer|null $customer
     * @return $this
     */
    public function setCustomer(Customer $customer = null): self
    {
        $this->customer = $customer;
        return $this;
    }

    /**
     * @param string $jobType
     * @return $this
     */
    public function setJobType(string $jobType): self
    {
        $this->jobType = $jobType;
        return $this;
    }

    /**
     * @return $this
     */
    public function disableDateFinish(): self
    {
        $this->disableDateFinish = true;
        return $this;
    }

    /**
     * @return $this
     */
    public function makeFields(): self
    {
        $dateStartString = $this->disableDateFinish ? 'Week Start Date' : 'Start Date';
        // d( $this );
        $this->fields['date_start'] = $this->htmlUtility::getDateFieldHTML( [
            'name' => 'date_start',
            'placeholder' => 'Start Date',
            'value' => $this->dateStart,
            'label' => $dateStartString
        ] );

        $this->fields['date_finish'] = $this->htmlUtility::getDateFieldHTML( [
            'name' => 'date_finish',
            'placeholder' => 'Finish Date',
            'value' => $this->dateFinish,
            'disabled' => $this->disableDateFinish,
            'label' => 'Finish Date' . ($this->disableDateFinish ? ' <small>(Auto generated)</small>' : '')
        ] );


        $inputArgs = $this->url->getQueryArgs();
        unset(
            $inputArgs['date_start'],
            $inputArgs['date_finish'],
            $inputArgs['user']
        );


        $this->makeHiddenFields( $inputArgs );
        return $this;
    }

    /**
     * @param array  $userOptions
     * @param string $placeholder
     * @return $this
     */
    public function makeUserField(array $userOptions = [], string $placeholder = 'Select Worker'): self
    {
        if ( $this->user !== null ) {
            $append = $this->htmlUtility::getViewButton(
                $this->user->getLink() ?? '',
                'View ' . $this->user->getFirstName()
            );
        }

        $this->fields['user'] = $this->htmlUtility::getOptionDropdownFieldHTML( [
            'name' => 'user',
            'placeholder' => $placeholder,

            'options' => $userOptions,

            'selected' => $this->user->id ?? null,
            'id' => 'report-input-user',
            'label' => 'Select Employee',

            'append' => $append ?? ''
        ] );

        return $this;
    }

    /**
     * @param array $customerOptions
     * @return $this
     */
    public function makeCustomerField(array $customerOptions = []): self
    {
        if ( $this->customer !== null ) {
            $append = $this->htmlUtility::getViewButton(
                $this->customer->getLink() ?? '',
                'View ' . ($this->customer->name ?? 'Customer')
            );
        }

        $this->fields['customer'] = $this->htmlUtility::getOptionDropdownFieldHTML( [
            'name' => 'customer',
            'placeholder' => 'All Customers',

            'options' => $customerOptions,

            'selected' => $this->customer->id ?? null,
            'id' => 'report-input-customer',
            'label' => 'Select Customer',

            'append' => $append ?? ''

        ] );
        return $this;
    }


    /**
     * @return $this
     */
    public function setExclusiveJobType(): self
    {
        $this->exclusiveJobType = true;
        return $this;
    }

    /**
     * @return $this
     */
    public function makeJobTypeField(): self
    {
        $this->fields['job_type'] = $this->htmlUtility::getOptionDropdownFieldHTML( [
            'name' => 'job_type',

            'options' => [
                'all' => 'All',
                'CNC' => 'CNC Jobs Only',
                'Manual' => 'Manual Jobs Only'
            ],

            'selected' => $this->jobType ?? 'all',
            'id' => 'report-input-type',
            'label' => 'Select Jobs Type',
        ] );

        $this->fields['exclusive'] = $this->htmlUtility::getCheckboxesFieldHTML( [
            'name' => 'exclusive',
            'label' => 'Exclusively filter job type',
            'id' => 'report-input-type-exclusive',
            'checked' => $this->exclusiveJobType ? 'true' : '',
            // 'value' => true,
            'small' => 'Jobs with CNC <strong>and</strong> Manual shifts will be filtered out'
        ] );

        return $this;
    }

    /**
     * @return string
     */
    public function render(): string
    {
        ob_start(); ?>
        <div class="container mb-4 position-relative d-print-none">
            <div class="row">
                <div class="col">
                    <div class="grey-bg p-3">
                        <form id="<?php echo $this->formID; ?>" class="">
                            <fieldset>
                                <?php foreach ( $this->fields['hidden'] as $field ) {
                                    echo $field;
                                } ?>
                                <div class="form-row align-items-end">
                                    <div class="form-group col-md-4">
                                        <?php echo $this->fields['date_start']; ?>
                                    </div>
                                    <div class="form-group col-md-4">
                                        <?php echo $this->fields['date_finish']; ?>
                                    </div>
                                    <?php if ( isset( $this->fields['user'] ) ) { ?>
                                        <div class="form-group col-md-4">
                                            <?php echo $this->fields['user']; ?>
                                        </div>
                                    <?php } ?>
                                    <?php if ( isset( $this->fields['customer'] ) ) { ?>
                                        <div class="form-group col-md-4">
                                            <?php echo $this->fields['customer']; ?>
                                        </div>
                                    <?php } ?>
                                    <?php if ( isset( $this->fields['job_type'] ) ) { ?>
                                        <div class="form-group col-md-4">
                                            <?php echo $this->fields['job_type']; ?>
                                        </div>
                                        <div class="form-group col-md-6">
                                            <?php echo $this->fields['exclusive']; ?>
                                        </div>
                                    <?php } ?>
                                    <div class="form-group col mb-3 text-right">
                                        <?php echo $this->htmlUtility::getButton( [
                                            'element' => 'input',
                                            'type' => 'submit',
                                            'class' => [
                                                'btn', 'btn-success', 'btn-lg', 'mt-2', 'mr-1'
                                            ],
                                            'id' => 'submit-button',
                                            'value' => 'Generate'
                                        ] ); ?>
                                    </div>
                                </div>
                            </fieldset>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php return ob_get_clean();
    }


}