<?php


namespace Phoenix\Page;

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
     * @param array  $item
     * @param string $contextualClass
     * @return string
     */
    public static function renderMenuItem(array $item, string $contextualClass): string
    {
        ob_start(); ?>

        <a href="<?php echo $item['url']; ?>"
           class="list-group-item list-group-item-action list-group-item-<?php echo $contextualClass; ?> d-flex justify-content-between align-items-center h5 mb-0">
            <span>
            <?php echo !empty( $item['icon'] ) ? '<i class="fas fa-' . $item['icon'] . ' fa-fw"></i> ' : '';
            echo $item['text']; ?></span><span class="badge badge-<?php echo $contextualClass; ?> badge-pill"><?php echo $item['number'] ?? ''; ?></span>
        </a>
        <?php return ob_get_clean();
    }

    /**
     * @param array $menu
     * @return string
     */
    public static function renderMainMenu(array $menu = []): string
    {
        $i = 1;
        foreach ( $menu as $categoryName => $category ) {
            if ( $i > 3 ) {
                $i = 1;
            }
            $columns[$i][$categoryName] = $category;
            $i++;
        }
        ob_start(); ?>
        <div class="container">
            <div class="row ">
                <?php foreach ( $columns ?? [] as $column ) { ?>
                    <div class="col-md-4 my-3">
                        <?php foreach ( $column as $categoryName => $category ) { ?>
                            <h2 class="px-3"><?php echo !empty( $category['icon'] ) ? $category['icon'] . ' ' : '';
                                echo ucwords( $categoryName ); ?></h2>
                            <div class="grey-bg clearfix p-3 mb-4">
                                <div class="list-group">
                                    <?php foreach ( $category['items'] as $itemName => $menuItem ) {
                                        echo self::renderMenuItem( $menuItem, $category['contextual_class'] ?? 'primary' );
                                    } ?>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                <?php } ?>
            </div>
        </div>
        <?php return ob_get_clean();
        /*
        ob_start(); ?>
        <div class="container">
            <div class="row ">
                <?php foreach ( $menu as $categoryName => $category ) { ?>
                    <div class="col-md-4 my-3">
                        <h2 class="px-3"><?php echo !empty( $category['icon'] ) ? $category['icon'] . ' ' : '';
                            echo ucwords( $categoryName ); ?></h2>
                        <div class="grey-bg clearfix p-3">
                            <div class="list-group">
                                <?php foreach ( $category['items'] as $itemName => $menuItem ) {
                                    echo self::renderMenuItem( $menuItem, $category['contextual_class'] ?? 'primary' );
                                } ?>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>
        <?php return ob_get_clean();
        */
    }

    /**
     * @param array $menu
     * @return $this
     */
    public function setMainMenu(array $menu = []): self
    {
        $this->addContent( self::renderMainMenu( $menu ) );
        return $this;
    }
}