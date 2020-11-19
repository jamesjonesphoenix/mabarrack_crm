<?php


namespace Phoenix\Form;


use Phoenix\Entity\User;
use Phoenix\URL;
use Phoenix\Utility\DateTimeUtility;
use Phoenix\Utility\FormFields;

/**
 * Class GoToIDEntityForm
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

    private bool $disableDateFinish = false;

    /**
     * @var URL
     */
    private URL $url;

    /**
     * EntityForm constructor.
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
        // d( $this );
        $this->fields['date_start'] = $this->htmlUtility::getDateFieldHTML( [
            'name' => 'date_start',
            'placeholder' => 'Start Date',
            'value' => $this->dateStart
        ] );
        $this->fields['date_finish'] = $this->htmlUtility::getDateFieldHTML( [
            'name' => 'date_finish',
            'placeholder' => 'Finish Date',
            'value' => $this->dateFinish,
            'disabled' => $this->disableDateFinish
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
        $this->fields['user'] = $this->htmlUtility::getOptionDropdownFieldHTML( [
            'name' => 'user',
            'placeholder' => $placeholder,
            'value' => $this->dateFinish,

            'options' => $userOptions,

            'selected' => $this->user->id ?? null,
            'id' => 'report-input-user',

            //'append' => $this->htmlUtility::getViewButton( '#', 'View Customer' ),

        ] );

        return $this;
    }

    /**
     * @return string
     */
    public function render(): string
    {
        $dateString = $this->disableDateFinish ? 'Choose Week Start' : 'Choose Dates';

        ob_start(); ?>
        <div class="container mb-4 position-relative d-print-none">
            <div class="row">
                <div class="col">
                    <form id="<?php echo $this->formID; ?>" class="form-inline my-2 my-lg-0 ml-3">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><?php echo $dateString; ?></span>
                            </div>
                            <?php echo $this->fields['date_start'];
                            if ( !$this->disableDateFinish ) { ?>
                                <span class="input-group-text">to</span>
                                <?php echo $this->fields['date_finish'];
                            }
                            foreach ( $this->fields['hidden'] as $field ) {
                                echo $field;
                            }
                            if ( isset( $this->fields['user'] ) ) { ?>
                                <span class="" style="width: 1px; background: #a4a9ae;"></span>
                                <span class="input-group-text">Choose Worker</span>
                                <?php echo $this->fields['user'];

                            } ?>
                            <div class="input-group-append">
                                <?php if ( $this->user !== null ) { ?>
                                    <?php
                                    /*
                                    echo $this->htmlUtility::getViewButton(
                                        $this->user->getLink() ?? '',
                                        'View ' . (($this->user->name ?? null) !== null ? $this->user->name : 'Worker') . ' Detail'
                                    );
                                    */
                                    ?>
                                <?php } ?>
                                <button class="btn btn-primary my-2 my-sm-0" type="submit">Generate Report</button>
                            </div>
                        </div>
                        <?php if ( $this->user !== null ) { ?>
                            <div class="float-right ml-2">
                                <?php echo $this->htmlUtility::getViewButton(
                                    $this->user->getLink() ?? '',
                                    'View ' . (!empty( $this->user->getFirstName() ) ? $this->user->getFirstName() : 'Worker') . ' Detail'
                                ); ?>
                            </div>
                        <?php } ?>
                    </form>
                </div>
            </div>
        </div>
        <?php return ob_get_clean();
    }
}