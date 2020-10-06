<?php


namespace Phoenix\Utility;

use Donquixote\Cellbrush\Table\Table;

/**
 * Class HTMLTags
 *
 * @author James Jones
 * @package Phoenix
 *
 */
class HTMLTags
{
    /**
     * @param string $content
     * @param string $contextualClass
     * @param bool   $showCloseButton
     * @return string
     */
    public static function getAlertHTML(string $content = '', string $contextualClass = '', bool $showCloseButton = true): string
    {
        $contextualClass ??= 'danger';
        ob_start(); ?>
        <div class="alert alert-<?php echo $contextualClass; ?> my-2" role="alert">
            <?php if ( $showCloseButton ) { ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            <?php } ?>
            <?php echo $content; ?>
        </div>
        <?php return ob_get_clean();
    }

    /**
     * @param string $content
     * @param string $contextualClass
     * @param string $url
     * @return string
     */
    public static function getBadgeHTML(string $content = '', string $contextualClass = 'primary', string $url = ''): string
    {
        return !empty($content) ? '<span class="badge badge-' . $contextualClass . '">' . $content . '</span>' : '';
    }

    /**
     * @param array  $args
     * @param string $type
     * @return array
     */
    public static function mergeDefaultArgs(array $args = [], string $type = ''): array
    {
        if ( empty( $args['id'] ) ) {
            $args['id'] = !empty( $type ) ? uniqid( 'input' . ucfirst( $type ) . '-', true ) : '';
        }
        $args['name'] ??= '';
        $args['class'] ??= '';
        $args['label'] ??= '';
        return $args;
    }

    /**
     * @param string $url
     * @param string $text
     * @return string
     */
    public static function getViewButton(string $url = '', string $text = ''): string
    {
        if ( empty( $url ) ) {
            return '';
        }
        return self::getButton( [
            'element' => 'a',
            'href' => $url,
            'class' => 'btn btn-primary',
            'content' => $text ?? 'View'
        ] );

    }

    /**
     * @param array  $items
     * @param string $contextualClass
     * @return string
     */
    public static function getListGroup(array $items = [], $contextualClass = 'danger'): string
    {
        if ( empty( $items ) ) {
            return '';
        }
        $li = '<li class="list-group-item list-group-item-' . $contextualClass . '">';
        return '<ul class="list-group list-group-flush">'
            . $li
            . implode( '</li>' . $li, $items )
            . '</li></ul>';
    }

    /**
     * @param array $args
     * @return string
     */
    public static function getButton(array $args = []): string
    {
        $element = $args['element'] ?? 'button';
        $content = $args['content'] ?? '';
        $suffix = $element !== 'input' ? $content . '</' . $element . '>' : '';
        return '<' . $element . self::getAttributes( $args ) . '>' . $suffix;
    }

    /**
     * @param array $args
     * @return Table
     * @throws \Exception
     */
    public static function makeTable(array $args = []): Table
    {
        $args = self::mergeDefaultArgs( $args, 'table' );
        $columns = $args['columns'] ?? [];
        $columnIDsToCheckForValues = $columnIDs = array_keys( $columns );
        foreach ( $columnIDs as $columnID ) {
            $explodedID = explode( '.', $columnID );
            if ( (count( $explodedID ) === 2) && !in_array( $explodedID[0], $columnIDsToCheckForValues, true ) ) {
                $columnIDsToCheckForValues[] = $explodedID[0];
            }
        }
        if ( empty( $columnIDsToCheckForValues ) ) {
            $columnIDsToCheckForValues = array_keys( current( $args['data'] ?? [] ) );
            $columnIDs = $columnIDsToCheckForValues;
        }

        if ( is_string( $args['class'] ) ) {
            $args['class'] = [$args['class']];
        }
        $classes = array_merge( $args['class'], ['table', 'table-hover', 'table-dark', 'table-striped', 'mb-0'] );

        $table = Table::create()
            ->addColNames( $columnIDs )
            ->addColClasses( array_combine( $columnIDs, $columnIDs ) );
        $table->addClasses( $classes );
        if ( !empty( $columns ) ) {
            foreach ( $columns as $columnID => $columnArgs ) {
                $title = is_string( $columnArgs ) ? $columnArgs : $columnArgs['title'] ?? '';
                $headerColumns[$columnID] = $title;

                if ( !empty( $columnArgs['class'] ) ) {
                    $table->addColClass( $columnID, $columnArgs['class'] );
                }
            }
            if ( !empty( $headerColumns ) ) {
                $table->headRow( 'head' )
                    ->thMultiple( $headerColumns ?? [] );
            }
        }
        foreach ( $args['data'] ?? [] as $rowID => $row ) {
            $table->addRowName( $rowID );
            foreach ( $columnIDsToCheckForValues as $columnID ) {

                if ( !array_key_exists( $columnID, $row ) ) {
                    continue;
                }
                if ( !empty( $args['rows'][$rowID]['subheader'] ) || !empty( $args['columns'][$columnID]['subheader'] ) ) {
                    $table->th( $rowID, $columnID, $row[$columnID] );
                } else {
                    $table->td( $rowID, $columnID, $row[$columnID] );
                }
            }
        }
        foreach ( $args['rows'] ?? [] as $rowID => $row ) {
            $table->addRowClass( $rowID, $row['class'] ?? '' );
        }
        return $table;
    }

    /**
     * @param array $args
     * @return string
     * @throws \Exception
     */
    public static function getTableHTML(array $args = []): string
    {
        $table = self::makeTable( $args );
        return '<div class="table-responsive">' . $table->render() . '</div>';
    }

    /**
     * Returns value, id, disabled, name, class, placeholder
     *
     * @param array $args
     * @return string
     */
    public static function getAttributes(array $args = []): string
    {
        $attributes['type'] = self::makeElementProperty( $args['type'] ?? '', 'type' );

        $attributes['value'] = self::makeElementProperty( $args['value'] ?? '', 'value' );

        $attributes['id'] = self::makeElementProperty( $args['id'] ?? '', 'id' );

        $attributes['disabled'] = !empty( $args['disabled'] ?? false ) ? self::makeElementProperty( '', 'disabled' ) : '';

        $attributes['name'] = self::makeElementProperty( $args['name'] ?? '', 'name' );

        $args['class'] = !empty( $args['class'] ) && is_array( $args['class'] ) ? implode( ' ', $args['class'] ) : ($args['class'] ?? '');
        $attributes['class'] = self::makeElementProperty( $args['class'], 'class' );

        $attributes['href'] = self::makeElementProperty( $args['href'] ?? '', 'href' );
        //!empty( $args['href'] ) ? ' href="' . $args['href'] . '"' : '';

        $attributes['placeholder'] = self::makeElementProperty(
            $args['placeholder'] ?? '',
            'placeholder'
        );
        return implode( '', $attributes ) ?? '';
    }

    /**
     * @param string $input
     * @param string $propertyName
     * @return string
     */
    public static function makeElementProperty(string $input = '', string $propertyName = ''): string
    {
        if ( empty( $input ) && !in_array( $propertyName, ['disabled', 'value'], true ) ) {
            return '';
        }
        if ( empty( $propertyName ) ) {
            return '';
        }
        return ' ' . $propertyName . '="' . $input . '"';
    }

    /**
     * @param string $icon
     * @return string
     */
    public static function getIconHTML(string $icon = 'home'): string
    {
        return '<i class="fas fa-' . $icon . '"></i>';
    }

    /**
     * @param array $args
     * @return string|null
     */
    public static function getNavHTML(array $args = []): ?string
    {
        $id = uniqid( 'navbar-', true );
        $args['nav_links'] ??= [];
        $args['heading_level'] ??= 1;

        if ( empty( $args['title'] ) && empty( $args['nav_links'] ) && empty( $args['html_left_aligned'] ) && empty( $args['html_right_aligned'] ) ) {
            return '';
        }
        ob_start();
        ?>
        <nav class="navbar navbar-expand-lg navbar-dark">
            <?php if ( !empty( $args['title'] ) ) { ?>
                <h<?php echo $args['heading_level']; ?>
                        class="navbar-brand h<?php echo $args['heading_level']; ?> py-0"><?php echo $args['title']; ?></h<?php echo $args['heading_level']; ?>>
            <?php } ?>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#<?php echo $id; ?>" aria-controls="<?php echo $id; ?>"
                    aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse flex-wrap justify-content-end mb-2" id="<?php echo $id; ?>"><?php
                if ( !empty( $args['html_left_aligned'] ) ) {
                    ?>
                    <div class="mr-auto"><?php
                        echo $args['html_left_aligned'] ?? ''; ?>
                    </div>
                <?php } ?>
                <?php if ( !empty( $args['nav_links'] ) ) { ?>
                    <ul class="navbar-nav nav-pills justify-content-end ml-auto flex-wrap">
                        <?php
                        foreach ( $args['nav_links'] as $navLink ) { ?>
                            <li class="nav-item ml-2 my-1">
                                <a class="nav-link text-white text-nowrap <?php echo $navLink['class'] ?? 'bg-primary'; ?>"
                                   href="<?php echo $navLink['url']; ?>"><?php echo $navLink['text'];
                                    if ( isset( $navLink['number'] ) ) { ?>
                                        <span class="badge badge-light"><?php echo $navLink['number'] ?? ''; ?></span><?php
                                    }
                                    ?></a>
                            </li>
                        <?php } ?>
                    </ul>
                <?php }
                if ( !empty( $args['html_right_aligned'] ) ) {
                    ?>
                    <div class="justify-content-end <?php echo !empty( $args['nav_links'] ) ? 'ml-0' : 'ml-auto'; ?>"><?php
                        echo $args['html_right_aligned'] ?? ''; ?>
                    </div>
                <?php } ?>
            </div>
        </nav>
        <?php return ob_get_clean();
    }
}