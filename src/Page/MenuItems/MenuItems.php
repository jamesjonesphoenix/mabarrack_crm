<?php


namespace Phoenix\Page\MenuItems;

use Phoenix\Utility\HTMLTags;

/**
 * Class MenuItems
 *
 * @author James Jones
 * @package Phoenix\Page
 *
 */
abstract class MenuItems
{
    /**
     * @var string
     */
    protected string $icon = '';

    /**
     * @return array[]
     */
    abstract public function getMenuItems(): array;

    /**
     * @return string
     */
    public function getIcon(): string
    {
        return HTMLTags::getIconHTML( $this->icon );
    }

    /**
     * @return string
     */
    public function getContextualClass(): string
    {
        return 'primary';
    }

    /**
     * @param array  $item
     * @param string $contextualClass
     * @return string
     */
    public static function renderMainMenuItem(array $item, string $contextualClass): string
    {
        ob_start(); ?>

        <a href="<?php echo $item['href']; ?>"
           class="list-group-item list-group-item-action list-group-item-<?php echo $contextualClass; ?> d-flex justify-content-between align-items-center h5 mb-0">
            <span>
            <?php echo !empty( $item['icon'] ) ? '<i class="fas fa-' . $item['icon'] . ' fa-fw"></i> ' : '';
            echo $item['content']; ?></span><span
                    class="badge badge-<?php echo $contextualClass; ?> badge-pill"><?php echo $item['number'] ?? ''; ?></span>
        </a>
        <?php return ob_get_clean();
    }

    /**
     * @return string
     */
    public function renderMainMenu(): string
    {
        ob_start(); ?>
        <div class="container">
            <div class="row ">
                <?php foreach ( $this->menu as $categoryName => $category ) { ?>
                    <div class="col-md-4 my-3">
                        <h2 class="px-3"><?php echo !empty( $category['icon'] ) ? $category['icon'] . ' ' : '';
                            echo ucwords( $categoryName ); ?></h2>
                        <div class="grey-bg clearfix p-3">
                            <div class="list-group">
                                <?php foreach ( $category['items'] as $itemName => $menuItem ) {
                                    echo self::renderMainMenuItem( $menuItem, $category['contextual_class'] ?? 'primary' );
                                } ?>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>
        <?php return ob_get_clean();
    }
}