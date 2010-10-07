<?php

/**
 * Generic Server-Side Google Analytics PHP Client
 * 
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License (LGPL) as published by the Free Software Foundation; either
 * version 3 of the License, or (at your option) any later version.
 * 
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA.
 * 
 * Google Analytics is a registered trademark of Google Inc.
 * 
 * @link      http://code.google.com/p/php-ga
 * 
 * @license   http://www.gnu.org/licenses/lgpl.html
 * @author    Thomas Bachem <tb@unitedprototype.com>
 * @copyright Copyright (c) 2010 United Prototype GmbH (http://unitedprototype.com)
 */

namespace UnitedPrototype\GoogleAnalytics;

use UnitedPrototype\GoogleAnalytics\Internals\Util;
use UnitedPrototype\GoogleAnalytics\Internals\Request\PageviewRequest;
use UnitedPrototype\GoogleAnalytics\Internals\Request\EventRequest;
use UnitedPrototype\GoogleAnalytics\Internals\Request\TransactionRequest;
use UnitedPrototype\GoogleAnalytics\Internals\Request\ItemRequest;

use InvalidArgumentException;

class Tracker {
	
	/**
	 * Google Analytics client version on which this library is built upon,
	 * will be mapped to "utmwv" parameter
	 * 
	 * @link http://code.google.com/apis/analytics/docs/gaJS/changelog.html
	 * @const string
	 */
	const VERSION = '4.7.2';
	
	
	/**
	 * The configuration to use for this tracker instance
	 * 
	 * @var Config
	 */
	protected $config;
	
	/**
	 * Google Analytics account ID, e.g. "UA-1234567-8", will be mapped to
	 * "utmac" parameter
	 * 
	 * @see Internals\ParameterHolder::$utmac
	 * @var string
	 */
	protected $accountId;
	
	/**
	 * Host Name, e.g. "www.example.com", will be mapped to "utmhn" parameter
	 * 
	 * @see Internals\ParameterHolder::$utmhn
	 * @var string
	 */
	protected $domainName;
	
	/**
	 * Whether to generate a unique domain hash, default is true to be consistent
	 * with the GA Javascript Client
	 * 
	 * @link http://code.google.com/apis/analytics/docs/tracking/gaTrackingSite.html#setAllowHash
	 * @see Internals\Request\Request::generateDomainHash()
	 * @var bool
	 */
	protected $allowHash = true;
	
	/**
	 * @var array
	 */
	protected $customVariables = array();
	
	
	/**
	 * @param string $accountId
	 * @param string $domainName
	 * @param bool $allowHash
	 */
	public function __construct($accountId, $domainName, Config $config = null) {
		$this->setAccountId($accountId);
		$this->setDomainName($domainName);
		$this->setConfig($config ? $config : new Config());
	}
	
	/**
	 * @return Config
	 */
	public function getConfig() {
		return $this->config;
	}	
	
	/**
	 * @param Config $value
	 */
	public function setConfig(Config $value) {
		$this->config = $value;
	}
	
	/**
	 * @param string $value
	 */
	public function setAccountId($value) {
		if(!preg_match('/^UA-[0-9]*-[0-9]*$/', $value)) {
			throw new InvalidArgumentException('"' . $value . '" is not a valid Google Analytics account ID.');
		}
		
		$this->accountId = $value;
	}
	
	/**
	 * @return string
	 */
	public function getAccountId() {
		return $this->accountId;
	}
	
	/**
	 * @param string $value
	 */
	public function setDomainName($value) {
		$this->domainName = $value;
	}
	
	/**
	 * @return string
	 */
	public function getDomainName() {
		return $this->domainName;
	}
	
	/**
	 * @param bool $value
	 */
	public function setAllowHash($value) {
		$this->allowHash = (bool)$value;
	}
	
	/**
	 * @return bool
	 */
	public function getAllowHash() {
		return $this->allowHash;
	}
	
	/**
	 * Equivalent of _setCustomVar() in GA Javascript client.
	 * 
	 * @link http://code.google.com/apis/analytics/docs/tracking/gaTrackingCustomVariables.html
	 * @param CustomVariable $customVariable
	 */
	public function addCustomVariable(CustomVariable $customVariable) {
		// Ensure that all required parameters are set
		$customVariable->validate();
		
		$index = $customVariable->getIndex();
		$this->customVariables[$index] = $customVariable;
	}
	
	/**
	 * @return CustomVariable[]
	 */
	public function getCustomVariables() {
		return $this->customVariables;
	}
	
	/**
	 * Equivalent of _deleteCustomVar() in GA Javascript client.
	 * 
	 * @param int $index
	 */
	public function removeCustomVariable($index) {
		unset($this->customVariables[$index]);
	}
	
	/**
	 * Equivalent of _trackPageview() in GA Javascript client.
	 * 
	 * @link http://code.google.com/apis/analytics/docs/gaJS/gaJSApiBasicConfiguration.html#_gat.GA_Tracker_._trackPageview
	 * @param Page $page
	 * @param Session $session
	 * @param Visitor $visitor
	 */
	public function trackPageview(Page $page, Session $session, Visitor $visitor) {
		$session->setTrackCount($session->getTrackCount() + 1);
		
		$request = new PageviewRequest($this->config);
		$request->setPage($page);
		$request->setSession($session);
		$request->setVisitor($visitor);
		$request->setTracker($this);
		$request->fire();
	}
	
	/**
	 * Equivalent of _trackEvent() in GA Javascript client.
	 * 
	 * @link http://code.google.com/apis/analytics/docs/gaJS/gaJSApiEventTracking.html#_gat.GA_EventTracker_._trackEvent
	 * @param Event $event
	 * @param Session $session
	 * @param Visitor $visitor
	 */
	public function trackEvent(Event $event, Session $session, Visitor $visitor) {
		// Ensure that all required parameters are set
		$event->validate();
		
		$request = new EventRequest($this->config);
		$request->setEvent($event);
		$request->setSession($session);
		$request->setVisitor($visitor);
		$request->setTracker($this);
		$request->fire();
	}
	
	/**
	 * Combines _addTrans(), _addItem() (indirectly) and _trackTrans() of GA Javascript client.
	 * Although the naming of "_addTrans()" would suggest multiple possible transactions
	 * per reust, there is just one allowed actually.
	 * 
	 * @link http://code.google.com/apis/analytics/docs/gaJS/gaJSApiEcommerce.html#_gat.GA_Tracker_._addTrans
	 * @link http://code.google.com/apis/analytics/docs/gaJS/gaJSApiEcommerce.html#_gat.GA_Tracker_._addItem
	 * @link http://code.google.com/apis/analytics/docs/gaJS/gaJSApiEcommerce.html#_gat.GA_Tracker_._trackTrans
	 * 
	 * @param Transaction $transaction
	 * @param Session $session
	 * @param Visitor $visitor
	 */
	public function trackTransaction(Transaction $transaction, Session $session, Visitor $visitor) {
		// Ensure that all required parameters are set
		$transaction->validate();
		
		$request = new TransactionRequest($this->config);
		$request->setTransaction($transaction);
		$request->setSession($session);
		$request->setVisitor($visitor);
		$request->setTracker($this);
		$request->fire();
		
		// Every item gets a separate request,
		// see http://code.google.com/p/gaforflash/source/browse/trunk/src/com/google/analytics/v4/Tracker.as#312
		foreach($transaction->getItems() as $item) {
			// Ensure that all required parameters are set
			$item->validate();
			
			$request = new ItemRequest($this->config);
			$request->setItem($item);
			$request->setSession($session);
			$request->setVisitor($visitor);
			$request->setTracker($this);
			$request->fire();
		}
	}
	
}

?>