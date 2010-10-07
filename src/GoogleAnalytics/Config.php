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

/**
 * Note: Doesn't necessarily have to be consistent across requests, as it doesn't
 * alter the actual tracking result.
 * 
 * @link http://code.google.com/p/gaforflash/source/browse/trunk/src/com/google/analytics/core/GIFRequest.as
 */
class Config {
	
	/**
	 * Whether to just queue all requests on HttpRequest::fire() and actually send
	 * them on script shutdown after all other tasks are done (so it effectively
	 * doesn't affect app performance)
	 * 
	 * @see Internals\Request\HttpRequest::fire()
	 * @var bool
	 */
	protected $sendOnShutdown = false;
	
	/**
	 * Whether to make asynchronous requests to GA without waiting for any
	 * response (speeds up doing requests).
	 * 
	 * @see Internals\Request\HttpRequest::send()
	 * @var bool
	 */
	protected $fireAndForget = false;
	
	/**
	 * Seconds (float allowed) to wait until timeout when connecting to the
	 * Google analytics endpoint host
	 * 
	 * @see Internals\Request\HttpRequest::send()
	 * @var float
	 */
	protected $requestTimeout = 1;
	
	/**
	 * Google Analytics tracking request endpoint host
	 * 
	 * @see Internals\Request\HttpRequest::send()
	 * @var string
	 */
	protected $endPointHost = 'www.google-analytics.com';
	
	/**
	 * Google Analytics tracking request endpoint path
	 * 
	 * @see Internals\Request\HttpRequest::send()
	 * @var string
	 */
	protected $endPointPath = '/__utm.gif';
	
	/**
	 * Whether to anonymize IP addresses within Google Analytics by stripping
	 * the last IP address block, will be mapped to "aip" parameter
	 * 
	 * @see Internals\ParameterHolder::$aip
	 * @link http://code.google.com/apis/analytics/docs/gaJS/gaJSApi_gat.html#_gat._anonymizeIp
	 * @var bool
	 */
	protected $anonymizeIpAddresses = false;
	
	
	/**
	 * @return bool
	 */
	public function getSendOnShutdown() {
		return $this->sendOnShutdown;
	}
	
	/**
	 * @param bool $sendOnShutdown
	 */
	public function setSendOnShutdown($sendOnShutdown) {
		$this->sendOnShutdown = $sendOnShutdown;
	}
	
	/**
	 * @return bool
	 */
	public function getFireAndForget() {
		return $this->fireAndForget;
	}
	
	/**
	 * @param bool $fireAndForget
	 */
	public function setFireAndForget($fireAndForget) {
		$this->fireAndForget = (bool)$fireAndForget;
	}
	
	/**
	 * @return float
	 */
	public function getRequestTimeout() {
		return $this->requestTimeout;
	}
	
	/**
	 * @param float $requestTimeout
	 */
	public function setRequestTimeout($requestTimeout) {
		$this->requestTimeout = (float)$requestTimeout;
	}
	
	/**
	 * @return string
	 */
	public function getEndPointHost() {
		return $this->endPointHost;
	}
	
	/**
	 * @param string $endPointHost
	 */
	public function setEndPointHost($endPointHost) {
		$this->endPointHost = $endPointHost;
	}
	
	/**
	 * @return string
	 */
	public function getEndPointPath() {
		return $this->endPointPath;
	}
	
	/**
	 * @param string $endPointPath
	 */
	public function setEndPointPath($endPointPath) {
		$this->endPointPath = $endPointPath;
	}
	
	/**
	 * @return bool
	 */
	public function getAnonymizeIpAddresses() {
		return $this->anonymizeIpAddresses;
	}
	
	/**
	 * @param bool $anonymizeIpAddresses
	 */
	public function setAnonymizeIpAddresses($anonymizeIpAddresses) {
		$this->anonymizeIpAddresses = $anonymizeIpAddresses;
	}

}

?>