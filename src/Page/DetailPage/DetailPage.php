<?php


namespace Phoenix\Page\DetailPage;

use Phoenix\Form\GoToIDEntityForm;
use Phoenix\Page\EntityPage;
use Phoenix\Report\Report;

/**
 * Class DetailPage
 *
 * @author James Jones
 * @package Phoenix
 *
 */
class DetailPage extends EntityPage
{
    /**
     * @var string
     */
    public string $formHTML;

    /**
     * @var Report[]
     */
    protected array $reports = [];

    /**
     * @var string
     */
    private string $goToIDForm = '';

    /**
     * @param array $reports
     * @return $this
     */
    public function setReports(array $reports = []): self
    {
        $this->reports = $reports;
        return $this;
    }

    /**
     * @param string $formHTML
     * @return $this
     */
    public function setForm(string $formHTML): self
    {
        $this->formHTML = $formHTML;
        return $this;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->entity->getIcon() . ' ' . $this->getTitleString();
    }

    /**
     * @return string
     */
    private function getTitleString(): string
    {
        if ( !empty( $this->title ) ) {
            return $this->title;
        }
        $entityName = ucwords( $this->entity->entityName ) ?? 'Entity';
        if ( $this->entity->id !== null ) { //Existing job
            return $this->title = $entityName . ' Details';
        } //New job
        return $this->title = 'New ' . $entityName;
    }


    /**
     * @return string
     */
    public function getPageHeadTitle(): string
    {
        return $this->getTitleString();
    }

    /**
     * @return string|null
     */
    public function renderFooterJS(): string
    {
        ob_start();
        ?>
        <script>
            entityPageFunctions();
            <?php if($this->entity->entityName === 'job'){ ?>
            jobDetailPageFunctions();
            <?php } ?>
        </script>
        <?php
        return ob_get_clean();
    }

    /**
     * @return string
     */
    public function renderBody(): string
    {
        ob_start(); ?>
        <div class="container mb-4 position-relative">
            <?php echo $this->formHTML; ?>
        </div>
        <?php
        foreach ( $this->reports as $report ) {
            echo $report->render();
        }
        return ob_get_clean();
    }

    /**
     * @return string
     */
    public function getBodyClasses(): string
    {
        return 'detail-page ' . parent::getBodyClasses();
    }

    /**
     * @return string
     */
    public function getMainNavbarNonMenuItems(): string
    {
        return $this->goToIDForm;
    }

    /**
     * @param string $goToIDForm
     * @return $this
     */
    public function setGoToIDForm(string $goToIDForm = ''): self
    {
        $this->goToIDForm = $goToIDForm;
        return $this;
    }
}