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

namespace UnitedPrototype\GoogleAnalytics\Internals\Request;

use UnitedPrototype\GoogleAnalytics\Tracker;
use UnitedPrototype\GoogleAnalytics\Visitor;
use UnitedPrototype\GoogleAnalytics\Session;
use UnitedPrototype\GoogleAnalytics\CustomVariable;

use UnitedPrototype\GoogleAnalytics\Internals\ParameterHolder;
use UnitedPrototype\GoogleAnalytics\Internals\Util;
use UnitedPrototype\GoogleAnalytics\Internals\X10;

abstract class Request extends HttpRequest {
	
	/**
	 * @var Tracker
	 */
	protected $tracker;
	
	/**
	 * @var Visitor
	 */
	protected $visitor;
	
	/**
	 * @var Session
	 */
	protected $session;
	
	
	/**
	 * @const string
	 */
	const TYPE_PAGE           = null;
	/**
	 * @const string
	 */
	const TYPE_EVENT          = 'event';
	/**
	 * @const string
	 */
	const TYPE_TRANSACTION    = 'tran';
	/**
	 * @const string
	 */
	const TYPE_ITEM           = 'item';
	/**
	 * This type of request is deprecated in favor of encoding custom variables
	 * within the "utme" parameter, but we include it here for completeness
	 * 
	 * @see ParameterHolder::$__utmv
	 * @link http://code.google.com/apis/analytics/docs/gaJS/gaJSApiBasicConfiguration.html#_gat.GA_Tracker_._setVar
	 * @deprecated
	 * @const string
	 */
	const TYPE_CUSTOMVARIABLE = 'var';
	
	/**
	 * @const int
	 */
	const X10_CUSTOMVAR_NAME_PROJECT_ID  = 8;
	/**
	 * @const int
	 */
	const X10_CUSTOMVAR_VALUE_PROJECT_ID = 9;
	/**
	 * @const int
	 */
	const X10_CUSTOMVAR_SCOPE_PROJECT_ID = 11;
	
	
	/**
	 * Indicates the type of request, will be mapped to "utmt" parameter
	 * 
	 * @see ParameterHolder::$utmt
	 * @return string See Request::TYPE_* constants
	 */
	protected abstract function getType();
	
	/**
	 * @return string
	 */
	protected function buildHttpRequest() {
		$this->setXForwardedFor($this->visitor->getIpAddress());
		$this->setUserAgent($this->visitor->getUserAgent());
		
		return parent::buildHttpRequest();
	}
	
	/**
	 * @return ParameterHolder
	 */
	protected function buildParameters() {		
		$p = new ParameterHolder();
		
		$p->utmac = $this->tracker->getAccountId();
		$p->utmhn = $this->tracker->getDomainName();
		
		$p->utmt = $this->getType();
		$p->utmn = Util::generate32bitRandom();
		
		$p->aip = $this->tracker->getConfig()->getAnonymizeIpAddresses() ? 1 : null;
		
		// The IP parameter does sadly seem to be ignored by GA, so we
		// shouldn't set it as of today but keep it here for later reference
		// $p->utmip = $this->visitor->getIpAddress();
		
		$p->utmhid = $this->session->getSessionId();
		
		$p = $this->buildVisitorParameters($p);
		$p = $this->buildCustomVariablesParameter($p);
		$p = $this->buildCookieParameter($p);
		
		return $p;
	}
	
	/**
	 * @param ParameterHolder $p
	 * @return ParameterHolder
	 */
	protected function buildVisitorParameters(ParameterHolder $p) {
		$p->utmul = strtolower($this->visitor->getLocale());
		if($this->visitor->getFlashVersion() !== null) {
			$p->utmfl = $this->visitor->getFlashVersion();
		}
		if($this->visitor->getJavaEnabled() !== null) {
			$p->utmje = $this->visitor->getJavaEnabled();
		}
		if($this->visitor->getScreenColorDepth() !== null) {
			$p->utmsc = $this->visitor->getScreenColorDepth() . '-bit';
		}
		$p->utmsr = $this->visitor->getScreenResolution();
		
		return $p;
	}
	
	/**
	 * @link http://xahlee.org/js/google_analytics_tracker_2010-07-01_expanded.js line 575
	 * @param ParameterHolder $p
	 * @return ParameterHolder
	 */
	protected function buildCustomVariablesParameter(ParameterHolder $p) {
		$customVars = $this->tracker->getCustomVariables();
		if($customVars) {
			$x10 = new X10();
			
			$x10->clearKey(self::X10_CUSTOMVAR_NAME_PROJECT_ID);
			$x10->clearKey(self::X10_CUSTOMVAR_VALUE_PROJECT_ID);
			$x10->clearKey(self::X10_CUSTOMVAR_SCOPE_PROJECT_ID);
			
			foreach($customVars as $customVar) {
				// Name and value get encoded here,
				// see http://xahlee.org/js/google_analytics_tracker_2010-07-01_expanded.js line 563
				$name  = Util::encodeUriComponent($customVar->getName());
				$value = Util::encodeUriComponent($customVar->getValue());
				
				$x10->setKey(self::X10_CUSTOMVAR_NAME_PROJECT_ID, $customVar->getIndex(), $name);
				$x10->setKey(self::X10_CUSTOMVAR_VALUE_PROJECT_ID, $customVar->getIndex(), $value);
				if($customVar->getScope() !== null && $customVar->getScope() != CustomVariable::SCOPE_PAGE) {
					$x10->setKey(self::X10_CUSTOMVAR_SCOPE_PROJECT_ID, $customVar->getIndex(), $customVar->getScope());
				}
			}
			
			$p->utme .= $x10->renderUrlString();
		}
		
		return $p;
	}
	
	/**
	 * @link http://code.google.com/p/gaforflash/source/browse/trunk/src/com/google/analytics/core/GIFRequest.as#123
	 * @param ParameterHolder $p
	 * @return ParameterHolder
	 */
	protected function buildCookieParameter(ParameterHolder $p) {
		$p->__utma  = $this->generateDomainHash() . '.';
		$p->__utma .= $this->visitor->getUniqueId() . '.';
		$p->__utma .= $this->visitor->getFirstVisitTime()->format('U') . '.';
		$p->__utma .= $this->visitor->getPreviousVisitTime()->format('U') . '.';
		$p->__utma .= $this->session->getStartTime()->format('U') . '.';
		$p->__utma .= $this->visitor->getVisitCount();
		
		$p->utmhid = $this->session->getSessionId();
		
		$cookies = array();
		$cookies[] = '__utma=' . $p->__utma . ';';
		if($p->__utmz) {
			$cookies[] = '__utmz=' . $p->__utmz . ';';
		}
		if($p->__utmv) {
			$cookies[] = '__utmv=' . $p->__utmv . ';';
		}
		
		$p->utmcc = implode('+', $cookies);
		
		return $p;
	}
	
	/**
	 * @link http://code.google.com/p/gaforflash/source/browse/trunk/src/com/google/analytics/v4/Tracker.as#585
	 * @return string
	 */
	protected function generateDomainHash() {
		$hash = 1;
		
		if($this->tracker->getAllowHash()) {
			$hash = Util::generateHash($this->tracker->getDomainName());
		}
		
		return $hash;
	}
	
	/**
	 * @return Tracker
	 */
	public function getTracker() {
		return $this->tracker;
	}
	
	/**
	 * @param Tracker $tracker
	 */
	public function setTracker(Tracker $tracker) {
		$this->tracker = $tracker;
	}
	
	/**
	 * @return Visitor
	 */
	public function getVisitor() {
		return $this->visitor;
	}
	
	/**
	 * @param Visitor $visitor
	 */
	public function setVisitor(Visitor $visitor) {
		$this->visitor = $visitor;
	}
	
	/**
	 * @return Session
	 */
	public function getSession() {
		return $this->session;
	}
	
	/**
	 * @param Session $session
	 */
	public function setSession(Session $session) {
		$this->session = $session;
	}
	
}

?>