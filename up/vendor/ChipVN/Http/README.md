# PHP Http Client Class 

- ChipVN_Http_Client class used to sending request, scraping web content and get response like a browser.
- Use 2 functions: cURL, fsockopen, so you can use this class, "curl" **WITHOUT CURL** extension installed
- Supports POST (fields, raw data), file uploading, GET, PUT, etc..

**Note**: _fsockopen_ is faster also it is default. Both methods are the same, but _fsockopen_ limited use proxy feature (only this feature).

* Author:     Phan Thanh Cong <ptcong90@gmail.com>
* Copyright:  2011-2014 Phan Thanh Cong.
* License:    http://www.opensource.org/licenses/mit-license.php  MIT License
* Version:    2.5.5

## Change logs
##### Version 2.5.5: Apr 7, 2014
* Optimize and clear code
* Change class name to `ChipVN_Http_Client` (old class name still avaliable and just extends new class name)
* Improve `execute()`
* Sync request data  between Socket and cURL
* Allows unlimit follow redirect by `setFollowRedirect(true, true)`

##### Version 2.5.4: Apr 2, 2014
* Changed class name from ~~\ChipVN\Http\Request~~ to ~~ChipVN_Http_Request~~ to support PHP >= 5.0
* Fixed some bugs while scraping login Flickr.
* Improved followRedirect, parsing/ creating cookies.
* Added new method `resetFollowRedirect()`
* Added dynamic getters, setters (so you may get/set any properties for sending request easier)
* Added new method: `getRedirectedCount()` 
* Added new methods: `setCookies()`, `setParameters()`, `setHeaders()`, `removeCookies()`, `removeParameters()`, `removeHeaders()`
* Added new alias methods: `addCookies()`, `addParameters()`, `addHeaders()`
* Deprecated methods: `setCookie()`, `setParam()`, `setHeader()` (still avaliable)
* Changed method names: `readBinary()` -> `getFileData()`, `getMimeType()` -> `getFileType()`

##### Version 2.5.3: Apr 1, 2014
* Improve `setCookie()`
* Added new methods `resetResponse()`, `resetRequest()`, `parseCookie()`, `createCookie()`
* Added new method `setFollowRedirect()` to follow redirect
* Added new method `getResponseArrayCookies()` to get all cookies by array [name => [info]]
* Fixed a bug
* Change all properties to protected (need use set* methods to change the properties)

##### Version 2.5: Mar 07, 2014
* ~~Change class name to \ChipVN\Http\Request~~
* Most clean and clear
* Supports composer
* Added new method `setHttpVersion()` to change HTTP protocol version

##### Version 2.4: Jul 25, 2013
* Require PHP 5.3 or newer
* Change two static class methods (readBinary, mimeTye) to protected instance method

##### Version 2.3.4: Feb 20, 2013
* Fixed parse headers (typo)

##### Version 2.3.3: Nov 5, 2012
* Re-struct, something edited

##### Version 2.3.2: June 12, 2012
* Add some methods

##### Version 2.3.1: Mar 30, 2012
* Fixed some bugs to work well with PHP 5.3 (E_NOTICE default is enabled)

##### Version 2.3: Feb 2, 2012
* Update for picasa API

##### Version 2.2: Jan 1, 2012
* Support raw data for posting (upload image to picasa)

##### Version 2.1: Dec 23, 2011
* Fixed some bugs

##### Version 2.0: Jun 26, 2011
* Rewrite class to easy use
* Fixed some bugs

##### Version 1.2: April 19, 2011
* Mime-type bug on upload file fixed 

##### Version 1.1:
* Supports upload multiple files
* Fixed some bugs

##### Version 1.0:
* Supports send a basic request
* Proxy (only useCurl)
* Supports file uploading

## Usage

Add require `"ptcong/php-http-class": "dev-master"` to _composer.json_ and run `composer update` if you use composer

Create an `ChipVN_Http_Client` instnace
	
	$request = new ChipVN_Http_Client;

#### Send a request

**Use cURL or fsockopen**

	$request->useCurl(false);

**Set target url** (like to browse a url on browser)
	
	$request->setTarget('http://google.com');

**Use cookies**
	
	$request->setCookies('name=value');

	// or (does not support 'name' => 'value')
	$request->setCookies(array(
		'name1=value1',
		'name2=value2; expires=Tue, 01-Apr-2014 04:57:57 GMT'
	));

	$request->setCookies(array(
		'name1' => 'value1',
		'name2' => 'value2'
	));

**Change HTTP Protocol version**
	
	$request->setHttpVersion('1.1');

	// or
	$request->setHttpVersion('1.0');

**Follow redirect**

	$request->setFollowRedirect(true);

	// or maximum redirect 5 times. Default is 3 times and return last response
	$request->setFollowRedirect(true, 5);

**Parameters / Upload file**
	
	$request->setParameters('name', 'value');

	// or
	$request->setParameters('name=value&name2=value2&name3=value3');

	$request->setParam(array(
		'name1=value1',
		'name2=value2'
	));

	// or
	$request->setParameters(array(
		'name1'  => 'value1',
		'name2'  => 'value2'
	));

	// for uploading
	$request->setParameters('filedata', '@/path/path/file.jpg');

**Post raw data**

	$request->setRawPost('your data');

**Referer**
	
	$request->setReferer('http://domain.com');

**User Agent**

	$request->setUserAgent('Mozilla/5.0 (Windows NT 6.1; WOW64; rv : 9.0.1) Gecko/20100101 Firefox/9.0.1');

**Connect timeout**

	$request->setTimeout($seconds);

**Method**

	$request->setMethod('POST');
	$request->setMethod('GET');
	$request->setMethod('PUT');
	$request->setMethod('HEAD');
	// etc

**Submit type**
	
	// use to upload file
	$request->setSubmitMultipart();

	// submit normal form
	$request->setSubmitNormal();

**Request mime content type**

	$request->setMimeContentType('application/x-www-form-urlencoded');

**Use Headers**
	
	$request->setHeaders('Origin', 'xxx');

	// or
	$request->setHeaders('User-Agent: Firefox/9.0.1');

	// or
	$request->setHeaders(array(
		'name1=value1',
		'name2=value2',
	));

	// or
	$request->setHeaders(array(
		'name1'  => 'value1',
		'name2'  => 'value2'
	));

**Use Cookie**

	$request->setCookies('name=value');

	// or
	$request->setCookies(array(
		'name=value',
		'name2=value2',
	));

	// or
	$request->setCookies(array(
		'name' => 'value',
		'name' => 'value',
	));

**Use Proxy** The method only avaliable if you use cURL for sending request 

	$request->setProxy('127.0.0.1:80');

	// or
	$request->setProxy('127.0.0.1:80', $username, $password);

**WWW-Authenticate**

	$request->setAuth('user', 'pass');

**Remove cookies/ parameters/ headers added**
	
	$request->removeHeaders(true); // remove all headers
	$request->removeHeaders('Referer'); // remove a header

	$request->removeCookies(true); // remove all cookies
	$request->removeCookies('name'); // remove a cookie

	$request->removeParameters(true); // remove all parameters
	$request->removeParameters('name'); // remove a parameter

#### Helpers 

**parseCookie()**: 

	$cookie = $request->parseCookie('gostep=1; expires=Tue, 01-Apr-2014 05:20:23 GMT; Max-Age=300; path=/; domain=domain.com; secure;');

	print_r($cookie);

	[gostep] => Array
    (
        [expires] => 'Tue, 01-Apr-2014 05:20:23 GMT'
        [Max-Age] => '300'
        [path] => '/''
        [name] => 'gostep'
        [value] => '1'
        [domain] => 'domain.com'
        [secure] => ''
        [httponly] => null
    )

**createCookie()**: This method used to create cookie from array with keys like above (parseCookie) to string

**Send request**

	$boolean = $request->execute();

	var_dump($request->errors); // if have


#### Get Response

**Get response headers**

	print_r($request->getResponseHeaders());

	// or
	echo $request->getResponseHeaders('location');

	// or "set-cookie" return an array if have.
	print_r($request->getResponseHeaders('set-cookie'));

**Get response cookies**
	
	// by string
	echo $request->getResponseCookies();

	// by array [name => [info]]
	print_r($request->getResponseArrayCookies()); // get all cookies

	print_r($request->getResponseArrayCookies('cookie-name'));

**Get response body text**

	echo $request->getResponseText();

**Reset request** Just call 

	$request->reset();

before send an other request instead of create a new $request instance.

or only reset response data and keep old request data

	$request->resetResponse();





