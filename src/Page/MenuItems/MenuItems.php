<?php


namespace Phoenix\Page\MenuItems;

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
     * @return array[]
     */
    abstract public function getMenuItems(): array;

    /**
     * @param array  $item
     * @param string $contextualClass
     * @return string
     */
    public static function renderMainMenuItem(array $item, string $contextualClass): string
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
     * @return string
     */
    public function renderMainMenu(): string
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