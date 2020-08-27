<?php

namespace Phoenix\Page\WorkerChoose;

use Phoenix\Page\Page;
use Phoenix\Report\Report;

/**
 * @author James Jones
 *
 * Class ChoosePage
 *
 * @package Phoenix\Page\WorkerChoose
 *
 */
class ChoosePage extends Page
{
    /**
     * @var Report[]
     */
    protected array $chooseTables = [];

    /**
     * @param $chooseTable
     * @return $this
     */
    public function addChooseTable(Report $chooseTable): self
    {
        $this->chooseTables[] = $chooseTable;
        return $this;
    }

    /**
     * @return string
     * @throws \Exception
     */
    public
    function renderBody(): string
    {
        ob_start();
        foreach ( $this->chooseTables as $chooseTable ) {
            echo $chooseTable->render();
        }
        return ob_get_clean();
    }
}