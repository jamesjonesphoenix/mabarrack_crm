<?php


namespace Phoenix\Page;


use Phoenix\Base;
use Phoenix\Entity\CurrentUser;
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
     * @var string
     */
    private string $version = '0.4';

    /**
     * @var bool
     */
    private bool $hidePageTitleWhenPrinting = true;

    /**
     * @var string
     */
    protected string $systemTitle = 'CRM';

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
        $user = CurrentUser::instance();
        if ( $user->role === 'admin' ) {
            $username = '<a class="text-white" href="' . $user->getLink() . '">' . $user->name . '</a>';
            $home = 'Main Menu';
        } else {
            $username = $user->name;
            $home = 'Dashboard';
        }

        ob_start();
        ?>
        <div class="row align-items-center p-3">
            <div class="col-auto logo_title">
                <a href="index.php">
                    <img alt="logo" src="img/logo.png"/>
                    <h1 class='crm-title mb-0 text-decoration-none text-white'><?php echo $this->systemTitle; ?></h1>
                </a>
            </div>
            <div class="col d-print-none">
                <div class="d-flex flex-row justify-content-end align-items-center">
                    <div>
                        <span>Welcome <strong><?php echo $username; ?></strong></span>
                    </div>
                    <div class="ml-3">
                        <a href='index.php' class="btn btn-secondary"><?php echo $home; ?></a>
                    </div>
                    <div class="ml-2">
                        <a href='login.php?logout=true' class="btn btn-danger">Log Out</a>
                    </div>
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
     * @return $this
     */
    public function showTitleWhenPrinting(): self
    {
        $this->hidePageTitleWhenPrinting = false;
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
        <div class="container mb-2 mt-3">
            <?php echo $this->htmlUtility::getNavHTML( [
                'title' => $this->getTitle(),
                'nav_links' => $this->getNavLinks(),
                'html_right_aligned' => $this->getNavbarRightContent(),
                'hide_when_printing' => $this->hidePageTitleWhenPrinting
            ] ); ?>
        </div>
        <?php return ob_get_clean();
    }

    /**
     * @param string $messages
     * @return bool
     */
    public function render(string $messages = ''): bool
    {

        //$footerJS = $this->renderFooterJS();
        //$footerJS = !empty($footerJS) ? '<script>' . $footerJS . '</script' : '';

        $version = '?ver=' . $this->version;
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <title><?php echo $this->getHeadTitle() . ' - ' . $this->systemTitle; ?></title>
            <link rel="stylesheet" type="text/css" href="css/styles.css<?php echo $version; ?>">
            <link rel="stylesheet" type="text/css" href="css/datepicker.min.css<?php echo $version; ?>">
            <link rel="stylesheet" type="text/css" href="css/fonts.css<?php echo $version; ?>">
            <script type="text/javascript" src="js/jquery.min.js<?php echo $version; ?>"></script>
            <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
            <script type="text/javascript" src="js/bootstrap.min.js<?php echo $version; ?>"></script>
            <script type="text/javascript" src="js/jquery.tablesorter.combined.js<?php echo $version; ?>"></script>
            <script type="text/javascript" src="js/mousetrap.min.js<?php echo $version; ?>"></script>
            <script type="text/javascript" src="js/mousetrap-global-bind.min.js<?php echo $version; ?>"></script>
            <script type="text/javascript" src="js/jquery.matchHeight.js<?php echo $version; ?>"></script>
            <script type="text/javascript" src="js/functions.js<?php echo $version; ?>"></script>

            <link rel="apple-touch-icon" sizes="114x114" href="/apple-touch-icon.png">
            <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
            <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
            <link rel="manifest" href="/site.webmanifest">
            <link rel="mask-icon" href="/safari-pinned-tab.svg" color="#266a00">
            <meta name="msapplication-TileColor" content="#266a00">
            <meta name="theme-color" content="#266a00">
        </head>
        <body class="<?php echo $this->getBodyClasses(); ?>">
        <header>
            <div class="container">
                <?php echo $this->renderHeader(); ?>
            </div>
        </header>
        <?php if ( !empty( $messages ) ) { ?>
            <div class="container messages collapse show my-3 d-print-none" id="collapse-messages-container">
                <div class="row">
                    <div class="col">
                        <div class="px-3">
                            <h2><i class="fas fa-sticky-note"></i> Messages</h2>
                        </div>
                        <div class="grey-bg px-3 py-2">
                            <?php echo $messages; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php }
        echo $this->renderNavbar()
            . $this->renderBody(); ?>
        <a href="#" id="scroll-to-top" class="bg-primary" style="display: none;"><i class="fas fa-chevron-up fa-lg text-white"></i></a>
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

    /**
     * @param string $system_title
     * @return $this
     */
    public function setSystemTitle(string $system_title = ''): self
    {
        if ( !empty( $system_title ) ) {
            $this->systemTitle = $system_title;
        }
        return $this;
    }
}