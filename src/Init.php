<?php


namespace Phoenix;

use Phoenix\Entity\CurrentUser;
use Phoenix\Entity\CurrentUserFactory;
use Phoenix\Utility\HTMLTags;

/**
 * Class Init
 *
 * @author James Jones
 * @package Phoenix
 *
 */
class Init
{
    /**
     * @var string
     */
    private string $scriptFilename;

    /**
     * @var int|null
     */
    private ?int $userID;

    /**
     * @var array
     */
    private array $config = [];

    /**
     * @var PDOWrap|null
     */
    private ?PDOWrap $pdo = null;

    /**
     * @var Messages
     */
    private Messages $messages;

    /**
     * @var HTMLTags
     */
    private HTMLTags $htmlUtility;

    /**
     * @var bool
     */
    private bool $doingAJAX = false;

    /**
     * @var URL
     */
    private URL $url;

    /**
     * Init constructor.
     */
    public function __construct()
    {
        $this->scriptFilename = basename( $_SERVER['SCRIPT_FILENAME'] );
        $this->getConfig();

        $this->htmlUtility = new HTMLTags();
        /*
                $this->messages = Messages::instance()->initStatefulMessages(
                    $this->htmlUtility
                );
        */
        $this->messages = new Messages( $this->htmlUtility );
        $this->secureSessionStart();
        $this->url = new URL();
    }

    /**
     * @param bool $usingPin
     * @return CurrentUser|null
     */
    private function getCurrentUser(bool $usingPin = false): ?CurrentUser
    {
        $userFactory = new CurrentUserFactory( $this->getDB(), $this->messages );
        if ( !$usingPin ) {
            if ( $this->userID === null ) {
                return null;
            }
            $user = $userFactory->getEntity( $this->userID, false );

            if ( $user === null ) {
                $this->messages->add( 'Could not get current user' . $this->htmlUtility::getBadgeHTML( 'ID: ' . $this->userID, 'danger' )  );
            }

            // if ( $user->role === 'staff' ) {
            $user = $userFactory->provisionEntity( $user, ['shifts' => [
                'activity' => true,
                'furniture' => true,
                'job' => ['customer' => true],
                'employee' => false //Don't waste CPU time provisioning shifts with worker - we already have the worker
            ]] );
            // }
            $user->ipRestrictions = $this->config['ip_restrictions'];
            return $user;
        }
        $pin = $_POST['pin'] ?? null;
        if ( empty( $pin ) ) {
            $this->messages->add( 'The pin field is empty. Please try again.' );
            return null;
        }
        $pin = phValidateID( $pin );
        if ( empty( $pin ) ) {
            $this->messages->add( 'Pin should be a number only.' );
            return null;
        }
        $user = $userFactory->getUserFromPin( $pin );
        if ( $user === null ) {
            $this->messages->add( 'Could not get user with pin: <strong>' . $pin . '</strong>.' );
        }
        $user->ipRestrictions = $this->config['ip_restrictions'];
        return $user;
    }

    /**
     *
     */
    private function initLoginPage(): void
    {
        $user = $this->getCurrentUser();

        if ( $user !== null && $user->isLoggedIn() ) {
            if ( !empty( $_GET['logout'] ) ) {
                $user->logout();
                return;
            }
            redirect( $user->getHomePage() );
        }

        /*Not logged in so lets process Login*/
        if ( empty( $_POST['login-attempt'] ) ) {
            return;
        }
        $user = $this->getCurrentUser( true );
        if ( $user === null ) {
            return;
        }

        $password = $_POST['password'] ?? '';
        if ( empty( $password ) ) {
            $this->messages->add( 'The password field is empty. Please try again.' );
        }
        if ( $user->login( $password ) ) {
            $this->messages->add( 'Logged in successfully.', 'success' );
            redirect( $user->getHomePage() );
            return;
        }
        if ( !$this->messages->isMessage() ) {
            $this->messages->add( 'Login error, but not quite sure what it is.' );
        }
    }

    /**
     *
     */
    private function initCRMPage(): void
    {
        $deniedNotLoggedIn = 'You cannot access that page until you are logged in. Please login.';
        /*check user logged in and permissions*/
        if ( empty( $this->userID ) ) {
            if ( $this->scriptFilename !== 'index.php' ) { //avoid error message for index.php as this would be annoying
                $this->messages->add( $deniedNotLoggedIn );
            }
            redirect( 'login' );
        }

        //set current user for access by class code
        $user = $this->getCurrentUser();
        if ( $user === null ) {
            redirect( 'login' );
        }
        if ( !$user->isLoggedIn() ) {
            $this->messages->add( $deniedNotLoggedIn );
            redirect( 'login', ['logout' => 'true'] );
        }
        //logged in, check user can use this page
        if ( !$user->isUserAllowed() ) {
            if ( $this->scriptFilename !== 'index.php' ) {
                $this->messages->add(
                    'You were redirected to the ' . $user->role . ' homepage because you are not allowed to visit <strong>' . $this->scriptFilename . '</strong>.',
                    'warning'
                );
            }
            redirect( $user->getHomePage(), ['page' => $this->scriptFilename] );
        }
    }

    /**
     *
     */
    public function startUp(): self
    {
        $this->messages->initStatefulMessages();
        $this->userID = $_SESSION['user_id'] ?? null;

        if ( $this->scriptFilename === 'login.php' ) {
            $this->initLoginPage();
        } else {
            $this->initCRMPage();
        }
        return $this;
    }

    /**
     * @param string $type
     * @return Director
     */
    public function getDirector($type = 'crm'): Director
    {
        if ( $type === 'worker' ) {
            return new DirectorWorker(
                $this->getDB(),
                $this->getMessages(),
                $this->getURL(),
                $this->getHtmlUtility(),
                $this->getCurrentUser()
            );
        }
        return new DirectorCRM(
            $this->getDB(),
            $this->getMessages(),
            $this->getURL()
        );
    }

    /**
     * @param string $type
     */
    public function executePage($type = 'crm'): void
    {
        $director = $this->getDirector( $type );
        if ( method_exists( $director, 'doActions' ) ) {
            $director->doActions( array_merge( $_GET, $_POST ) );
        }
        $director
            ->getPageBuilder( $_GET )
            ->buildPage()
            ->getPage()
            ->setSystemTitle( $this->config['system_title'] )
            ->render(
                $this->getMessages()->getMessagesHTML()
            );
    }

    /**
     * @return URL
     */
    public function getURL(): URL
    {
        return $this->url;
    }

    /**
     * @return $this
     */
    public function doingAJAX(): self
    {
        $this->doingAJAX = true;
        return $this;
    }

    /**
     * @return Messages
     */
    public function getMessages(): Messages
    {
        return $this->messages;
    }

    /**
     * @return PDOWrap
     */
    public function getDB(): PDOWrap
    {
        if ( $this->pdo !== null ) {
            return $this->pdo;
        }
        $dbArgs = array_merge( [
            'host' => 'localhost', //Default DB definitions
            'user' => 'root',
            'password' => '',
            'name' => 'mabdb',
            'port' => '3306',
        ], $this->getConfig()['db'] ?? []
        );
        return $this->pdo = PDOWrap::instance( $dbArgs, $this->messages );
    }

    /**
     * @return HTMLTags
     */
    public function getHtmlUtility(): HTMLTags
    {
        return $this->htmlUtility;
    }

    /**
     *
     */
    private function secureSessionStart(): void
    {
        // Forces sessions to only use cookies.
        if ( ini_set( 'session.use_only_cookies', 1 ) === false ) {
            $this->messages->add( 'Could not initiate a safe session (ini_set)' );
            redirect( 'login' );
            exit();
        }
        // Gets current cookies params.
        $cookieParams = session_get_cookie_params();
        session_set_cookie_params( 43200, $cookieParams['path'], $cookieParams['domain'], $this->config['using_ssl'] ?? true, true ); // true stops JavaScript being able to access the session id.
        // Sets the session name to the one set above.
        session_name( 'sec_session_id' ); // Set a custom session name

        session_start();            // Start the PHP session

        if ( !empty( $_SESSION['LAST_ACTIVITY'] ) && (time() - $_SESSION['LAST_ACTIVITY'] > 28800) ) { // last request was more than 8 hours ago
            session_unset();     // unset $_SESSION variable for the run-time
            session_destroy();   // destroy session data in storage
            $this->messages->add( 'Your login timed out due to inactivity. Please login again.' );
            redirect( 'login' );
        } else {
            $_SESSION['LAST_ACTIVITY'] = time(); // update last activity time stamp
            if ( empty( $_SESSION['CREATED'] ) ) {
                $_SESSION['CREATED'] = time();
            } elseif ( !$this->doingAJAX && time() - $_SESSION['CREATED'] > 1800 ) {
                // session started more than 30 minutes ago
                session_regenerate_id();    // change session ID for the current session and invalidate old session ID
                $_SESSION['CREATED'] = time();  // update creation time
            }
        }
        //session_regenerate_id();    // regenerated the session, delete the old one.
    }

    /**
     * @return array
     */
    public function getConfig(): array
    {
        if ( !empty( $this->config ) ) {
            return $this->config;
        }
        include __DIR__ . '/../config.php';
        return $this->config = $config ?? [];

        /*
                $defaults = [
                    'DB_HOST' => 'localhost', //Default DB definitions
                    'DB_USER' => 'root',
                    'DB_PASSWORD' => '',
                    'DB_NAME' => 'mabdb',
                    'DB_PORT' => '3306',
=                    'SYSTEM_TITLE' => 'CRM',
                    'ALLOWED_IP_NUMBERS' => '127.0.0.1',
                    'IP_RESTRICTED_ROLES' => 'staff',
                ];
                foreach ( $defaults as $name => $value ) {
                    if ( !defined( $name ) ) {
                        define( $name, $value );
                    }
                }
        */
    }
}