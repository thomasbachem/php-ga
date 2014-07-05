<?php
/**
 * User: sj
 * Date: 2014/7/5
 * Time: 00:25
 */
namespace UnitedPrototype\GoogleAnalytics;

use DateTime;

/**
 * Class PersistManager
 * Use PHP $_SESSION to store php-ga session and visitor object
 *
 * @package UnitedPrototype\GoogleAnalytics
 */
class PersistManager
{
    /**
     * @var null|Visitor
     */
    private $_visitor = null;
    /**
     * @var null|Session
     */
    private $_session = null;

    /**
     * Constructor
     */
    public function __construct ()
    {
        ini_set('session.cookie_lifetime', 0);
        ini_set('session.gc_maxlifetime', 0);
        @session_start();
    }

    /**
     * Make a tracker instance
     *
     * @param $trackID
     * @param $domain
     * @return Tracker
     */
    public function makeTracker ($trackID, $domain)
    {
        return new Tracker($trackID, $domain);
    }

    /**
     * Get singleton session (persist)
     *
     * @return mixed|Session
     */
    public function getSession ()
    {
        global $_SESSION;
        $this->_session = null;
        if (isset($_SESSION['session'])) {
            $this->_session = unserialize($_SESSION['session']);
        }
        if ($this->_session === null || get_class($this->_session) !== 'UnitedPrototype\GoogleAnalytics\Session') {
            $this->_session = new Session();
        }
        return $this->_session;
    }

    /**
     * Get singleton visitor (persist)
     *
     * @return mixed|Visitor
     */
    public function getVisitor ()
    {
        global $_SESSION;
        $this->_visitor = null;
        if (isset($_SESSION['visitor'])) {
            $this->_visitor = unserialize($_SESSION['visitor']);
        }
        if ($this->_visitor === null && get_class($this->_visitor) !== 'UnitedPrototype\GoogleAnalytics\Visitor') {
            $this->_visitor = new Visitor();
            $this->_visitor->fromServerVar($_SERVER);
        }
        $this->_visitor->setCurrentVisitTime(new DateTime());
        return $this->_visitor;
    }

    /**
     * Store php-ga session and visitor in $_SESSION
     */
    public function store ()
    {
        global $_SESSION;
        $_SESSION['visitor'] = serialize($this->getVisitor());
        $_SESSION['session'] = serialize($this->getSession());
    }
}