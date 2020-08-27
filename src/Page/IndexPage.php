<?php


namespace Phoenix\Page;

use Khill\FontAwesome\FontAwesome;

/**
 * Class IndexPage
 *
 * @author James Jones
 * @package Phoenix\Page
 *
 */
class IndexPage extends Page
{
    /**
     * @var array
     */
    private array $menu = [];

    /**
     * @return string
     */
    public function getPageHeadTitle(): string
    {
        return 'Main Menu';

    }

    /*
<i class="fas fa-home fa-5x"></i>
<i class="fas fa-hammer fa-5x"></i>
<i class="fas fa-chair fa-5x"></i>
<i class="fas fa-couch fa-5x"></i>
<i class="fas fa-exclamation-circle fa-5x"></i>
<i class="fas fa-exclamation-triangle fa-5x"></i>
<i class="fas fa-exclamation fa-5x"></i>
<i class="fas fa-stopwatch fa-5x"></i>
<i class="fas fa-clock fa-5x"></i>
<i class="fas fa-user-clock fa-5x"></i>
<div class="custom-control custom-checkbox">
<input type="checkbox" class="custom-control-input" id="customCheck1">
<label class="custom-control-label" for="customCheck1">Check this custom checkbox</label>
</div>
<div class="custom-control custom-checkbox">
<input type="checkbox" class="custom-control-input" id="customCheck2">
<label class="custom-control-label" for="customCheck2">Check this custom checkbox</label>
</div>
*/
    public function renderBody(): string
    {
        ob_start(); ?>
        <div class="container">
            <div class="row ">
                <?php foreach ( $this->menu as $categoryName => $category ) { ?>
                    <div class="col-md-4 my-3">
                        <h2 class="px-3"><?php echo !empty( $category['icon'] ) ? $category['icon'] . ' ' : ''; echo ucwords( $categoryName ); ?></h2>
                        <div class="grey-bg clearfix p-3">
                            <div class="list-group">
                                <?php foreach ( $category['items'] as $itemName => $menuItem ) {
                                    echo $this->renderMenuItem( $menuItem, $category['contextual_class'] ?? 'primary' );
                                } ?>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>
        <?php return ob_get_clean();
    }

    /**
     * @param array  $item
     * @param string $contextualClass
     * @return string
     */
    public function renderMenuItem(array $item, string $contextualClass): string
    {
        ob_start(); ?>

        <a href="<?php echo $item['url']; ?>"
           class="list-group-item list-group-item-action list-group-item-<?php echo $contextualClass; ?> d-flex justify-content-between align-items-center h5 mb-0">
            <span>
            <?php echo !empty( $item['icon'] ) ? '<i class="fas fa-' . $item['icon'] . ' fa-fw"></i> ' : '';
            echo $item['text']; ?></span><span
                    class="badge badge-<?php echo $contextualClass; ?> badge-pill"><?php echo $item['number'] ?? ''; ?></span>
        </a>
        <?php return ob_get_clean();
    }

    /**
     * @param array $menu
     * @return $this
     */
    public function setMainMenu(array $menu = []): self
    {
        $this->menu = $menu;
        return $this;
    }
}