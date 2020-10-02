<?php


namespace Phoenix\Page;


use Phoenix\Base;
use Phoenix\Entity\CurrentUser;
use Phoenix\Messages;
use Phoenix\Utility\HTMLTags;
use function Phoenix\getScriptFilename;

/**
 * Class DetailPage
 *
 * @author James Jones
 * @package Phoenix
 *
 */
class Page extends Base
{
    /**
     * @var string
     */
    public string $title = '';

    /**
     * @var HTMLTags
     */
    private HTMLTags $htmlUtility;

    /**
     * @var array
     */
    private array $menuItems = [];

    /**
     * @var string
     */
    protected string $content = '';

    /**
     * @var string
     */
    private string $headTitle;

    /**
     * @var string
     */
    private string $navRightContent;

    /**
     * Page constructor.
     *
     * @param HTMLTags $htmlUtility
     */
    public function __construct(HTMLTags $htmlUtility)
    {
        $this->htmlUtility = $htmlUtility;
    }

    /**
     * @param string $content
     * @return $this
     */
    public
    function addContent(string $content = ''): self
    {
        $this->content .= $content;
        return $this;
    }

    /**
     * @return string
     */
    public function renderBody(): string
    {
        return $this->content;
    }

    /**
     * @return string
     */
    public function renderFooterJS(): string
    {
        return '';
    }

    /**
     * @return string
     */
    public function renderHeader(): string
    {
        ob_start();
        ?>
        <div class="row p-3">
            <div class="col-md-9 col-sm-8 col-xs-11 logo_title">
                <a href="index.php">
                    <img alt="logo" src="img/logo.png"/>
                    <h1 class='crm-title mb-0 text-decoration-none text-white'><?php echo SYSTEM_TITLE; ?></h1>
                </a>
            </div>
            <div class="col-md-3 col-sm-4 col-xs-1">
                <div class="d-flex flex-row justify-content-end mb-2">
                    <div class="ml-2">
                        <a href='login.php?logout=true' class="btn btn-danger">Log Out</a>
                    </div>

                    <?php if ( CurrentUser::instance()->role === 'admin' ) { ?>
                        <div class="ml-2">
                            <a href="index.php?page=archive&entity=settings" id="settings-button" class="btn btn-info"><img alt="settings cog" src="img/admin/settings.svg"></a>
                        </div>
                    <?php } ?>
                    <div class="ml-2">
                        <a href='index.php' class="btn btn-secondary">Home Page</a>
                    </div>
                </div>
                <div class="d-flex flex-row justify-content-end">
                    <span>Welcome <strong><?php echo CurrentUser::instance()->name; ?></strong></span>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        if ( !empty( $this->title ) ) {
            return $this->title;
        }
        return $this->getHeadTitle();
    }

    /**
     * @param string $title
     * @return $this
     */
    public
    function setTitle(string $title = ''): self
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @param string $title
     * @return $this
     */
    public function setHeadTitle(string $title = ''): self
    {
        $this->headTitle = $title;
        return $this;
    }

    /**
     * @return string
     */
    public function getHeadTitle(): string
    {
        return $this->headTitle ?? ucfirst( getScriptFilename( '.php' ) );
    }

    /**
     * @param array $menuItems
     * @return $this
     */
    public function setNavLinks(array $menuItems = []): self
    {
        $this->menuItems = $menuItems;
        return $this;
    }

    /**
     * @return string[][]
     */
    protected function getNavLinks(): array
    {
        return $this->menuItems;
    }

    /**
     * @return string
     */
    public function renderNavbar(): ?string
    {
        ob_start(); ?>
        <div class="container mb-2">
            <?php
            echo $this->htmlUtility::getNavHTML( [
                'title' => $this->getTitle(),
                'nav_links' => $this->getNavLinks(),
                'html_right_aligned' => $this->getNavbarRightContent()
            ] );
            ?>
        </div>
        <?php return ob_get_clean();
    }

    /**
     * @return bool
     */
    public function render(): bool
    {

        //$footerJS = $this->renderFooterJS();
        //$footerJS = !empty($footerJS) ? '<script>' . $footerJS . '</script' : '';
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <title><?php echo $this->getHeadTitle() . ' - ' . SYSTEM_TITLE; ?></title>
            <link rel="stylesheet" type="text/css" href="css/styles.css">
            <link rel="stylesheet" type="text/css" href="css/datepicker.min.css">
            <link rel="stylesheet" type="text/css" href="css/fonts.css">
            <script type="text/javascript" src="js/jquery.min.js"></script>
            <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
            <script type="text/javascript" src="js/bootstrap.min.js"></script>
            <script type="text/javascript" src="js/jquery.tablesorter.combined.js"></script>
            <script type="text/javascript" src="js/mousetrap.min.js"></script>
            <script type="text/javascript" src="js/mousetrap-global-bind.min.js"></script>
            <script type="text/javascript" src="js/jquery.matchHeight.js"></script>
            <script type="text/javascript" src="js/functions.js"></script>

            <link rel="apple-touch-icon" sizes="114x114" href="/apple-touch-icon.png">
            <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
            <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
            <link rel="manifest" href="/site.webmanifest">
            <link rel="mask-icon" href="/safari-pinned-tab.svg" color="#266a00">
            <meta name="msapplication-TileColor" content="#266a00">
            <meta name="theme-color" content="#266a00">
        </head>
        <body class="<?php echo $this->getBodyClasses(); ?>">
        <header class="mb-3">
            <div class="container">
                <?php echo $this->renderHeader(); ?>
            </div>
        </header>
        <?php if ( Messages::instance()->isMessage() ) { ?>
            <div class="container messages collapse show mb-3" id="collapse-messages-container">
                <div class="row">
                    <div class="col">
                        <div class="px-3">
                            <h2><i class="fas fa-sticky-note"></i> Messages</h2>
                        </div>
                        <div class="grey-bg px-3 py-2">
                            <?php echo Messages::instance()->getMessagesHTML(); ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php }
        echo $this->renderNavbar();
        echo $this->renderBody(); ?>
        </body>
        <?php echo $this->renderFooterJS(); ?>
        </html>
        <?php return true;
    }

    /**
     * @return string
     */
    public function getBodyClasses(): string
    {
        return str_replace( ' ', '-',
            strtolower( $this->getHeadTitle() )
        );
    }

    /**
     * @param string $navRightContent
     * @return $this
     */
    public function setNavbarRightContent(string $navRightContent = ''): self
    {
        $this->navRightContent = $navRightContent;
        return $this;
    }

    /**
     * @return string
     */
    public function getNavbarRightContent(): string
    {
        return $this->navRightContent ?? '';
    }
}