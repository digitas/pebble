<?php
/**
 * Request represents an HTTP request.
 *
 * @copyright Digitas France
 * @author Pierre-Louis LAUNAY <pllaunay@digitas.com>
 */
class Pebble_Core_Request
{
    protected $pathInfo;
    protected $baseUrl;
    protected $requestUri;
    protected $method;
    protected $basePath;

    /**
     * Returns the path being requested relative to the executed script.
     *
     * The path info always starts with a /.
     *
     * Suppose this request is instantiated from /mysite on localhost:
     *
     *  * http://localhost/mysite              returns an empty string
     *  * http://localhost/mysite/about        returns '/about'
     *  * http://localhost/mysite/about?var=1  returns '/about'
     *
     * @return string
     *
     * The following methods are derived from code of the Symfony framework
     *
     * Code subject to the MIT license (http://symfony.com/doc/current/contributing/code/license.html).
     *
     * (c) Fabien Potencier <fabien@symfony.com>
     *
     */
    public function getPathInfo()
    {
        if (null === $this->pathInfo) {
            $this->pathInfo = $this->preparePathInfo();
        }

        return $this->pathInfo;
    }

    /**
     * Returns the root url from which this request is executed.
     *
     * The base URL never ends with a /.
     *
     * This is similar to getBasePath(), except that it also includes the
     * script filename (e.g. index.php) if one exists.
     *
     * @return string
     *
     * The following methods are derived from code of the Symfony framework
     *
     * Code subject to the MIT license (http://symfony.com/doc/current/contributing/code/license.html).
     *
     * (c) Fabien Potencier <fabien@symfony.com>
     */
    public function getBaseUrl()
    {
        if (null === $this->baseUrl) {
            $this->baseUrl = $this->prepareBaseUrl();
        }

        return $this->baseUrl;
    }

    /**
     * Returns the requested URI.
     *
     * @return string
     *
     * The following methods are derived from code of the Symfony framework
     *
     * Code subject to the MIT license (http://symfony.com/doc/current/contributing/code/license.html).
     *
     * (c) Fabien Potencier <fabien@symfony.com>
     */
    public function getRequestUri()
    {
        if (null === $this->requestUri) {
            $this->requestUri = $this->prepareRequestUri();
        }

        return $this->requestUri;
    }

    /**
     * Checks whether the request is secure or not.
     *
     * @return Boolean
     *
     */
    public function isSecure()
    {
        return (isset($_SERVER['HTTPS']));
    }

    /**
     * Returns the host name
     *
     * @return string
     */
    public function getHost()
    {
        return $_SERVER['HTTP_HOST'];
    }

    /**
     * Gets the request method.
     *
     * @return string The request method
     */
    public function getMethod()
    {
        if (null === $this->method) {
            $this->method = strtoupper(isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET');
        }

        return $this->method;
    }

    /**
     * Gets the request's scheme.
     *
     * @return string
     *
     * The following methods are derived from code of the Symfony framework
     *
     * Code subject to the MIT license (http://symfony.com/doc/current/contributing/code/license.html).
     *
     * (c) Fabien Potencier <fabien@symfony.com>
     */
    public function getScheme()
    {
        return $this->isSecure() ? 'https' : 'http';
    }

    /**
     * Returns the port on which the request is made.
     *
     * @return string
     *
     * The following methods are derived from code of the Symfony framework
     *
     * Code subject to the MIT license (http://symfony.com/doc/current/contributing/code/license.html).
     *
     * (c) Fabien Potencier <fabien@symfony.com>
     */
    public function getPort()
    {
        return $_SERVER['SERVER_PORT'];
    }

    /**
     * Returns the HTTP host being requested.
     *
     * The port name will be appended to the host if it's non-standard.
     *
     * @return string
     *
     * The following methods are derived from code of the Symfony framework
     *
     * Code subject to the MIT license (http://symfony.com/doc/current/contributing/code/license.html).
     *
     * (c) Fabien Potencier <fabien@symfony.com>
     */
    public function getHttpHost()
    {
        $scheme = $this->getScheme();
        $port   = $this->getPort();

        if (('http' == $scheme && $port == 80) || ('https' == $scheme && $port == 443)) {
            return $this->getHost();
        }

        return $this->getHost().':'.$port;
    }

    /**
     * Returns the root path from which this request is executed.
     *
     * Suppose that an index.php file instantiates this request object:
     *
     *  * http://localhost/index.php        returns an empty string
     *  * http://localhost/index.php/page   returns an empty string
     *  * http://localhost/web/index.php    return '/web'
     *
     * @return string
     *
     * The following methods are derived from code of the Zend Framework (1.10dev - 2010-01-24)
     *
     * Code subject to the new BSD license (http://framework.zend.com/license/new-bsd).
     *
     * Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
     */
    public function getBasePath()
    {
        if (null === $this->basePath) {
            $this->basePath = $this->prepareBasePath();
        }

        return $this->basePath;
    }

    /**
     * Prepares the path info.
     *
     * @return string path info
     *
     * The following methods are derived from code of the Symfony framework
     *
     * Code subject to the MIT license (http://symfony.com/doc/current/contributing/code/license.html).
     *
     * (c) Fabien Potencier <fabien@symfony.com>
     */
    protected function preparePathInfo()
    {
        $baseUrl = $this->getBaseUrl();

        if (null === ($requestUri = $this->getRequestUri())) {
            return '/';
        }

        $pathInfo = '/';

        // Remove the query string from REQUEST_URI
        if (($pos = strpos($requestUri, '?'))) {
            $requestUri = substr($requestUri, 0, $pos);
        }

        if ((null !== $baseUrl) && (false === ($pathInfo = substr(urldecode($requestUri), strlen(urldecode($baseUrl)))))) {
            // If substr() returns false then PATH_INFO is set to an empty string
            return '/';
        } elseif (null === $baseUrl) {
            return $requestUri;
        }

        return (string) $pathInfo;
    }

    /**
     * Prepares the base URL.
     *
     * @return string
     *
     * The following methods are derived from code of the Symfony framework
     *
     * Code subject to the MIT license (http://symfony.com/doc/current/contributing/code/license.html).
     *
     * (c) Fabien Potencier <fabien@symfony.com>
     */
    protected function prepareBaseUrl()
    {
        $filename = basename($_SERVER['SCRIPT_FILENAME']);

        if (basename($_SERVER['SCRIPT_NAME']) === $filename) {
            $baseUrl = $_SERVER['SCRIPT_NAME'];
        } elseif (basename($_SERVER['PHP_SELF']) === $filename) {
            $baseUrl = $_SERVER['PHP_SELF'];
        } elseif (basename($_SERVER['ORIG_SCRIPT_NAME']) === $filename) {
            $baseUrl = $_SERVER['ORIG_SCRIPT_NAME']; // 1and1 shared hosting compatibility
        } else {
            // Backtrack up the script_filename to find the portion matching
            // php_self
            $path    = isset($_SERVER['PHP_SELF']) ? $_SERVER['PHP_SELF'] : '';
            $file    = isset($_SERVER['SCRIPT_FILENAME']) ? $_SERVER['SCRIPT_FILENAME'] : '';
            $segs    = explode('/', trim($file, '/'));
            $segs    = array_reverse($segs);
            $index   = 0;
            $last    = count($segs);
            $baseUrl = '';
            do {
                $seg     = $segs[$index];
                $baseUrl = '/'.$seg.$baseUrl;
                ++$index;
            } while (($last > $index) && (false !== ($pos = strpos($path, $baseUrl))) && (0 != $pos));
        }

        // Does the baseUrl have anything in common with the request_uri?
        $requestUri = $this->getRequestUri();

        if ($baseUrl && 0 === strpos($requestUri, $baseUrl)) {
            // full $baseUrl matches
            return $baseUrl;
        }

        if ($baseUrl && 0 === strpos($requestUri, dirname($baseUrl))) {
            // directory portion of $baseUrl matches
            return rtrim(dirname($baseUrl), '/');
        }

        $truncatedRequestUri = $requestUri;
        if (($pos = strpos($requestUri, '?')) !== false) {
            $truncatedRequestUri = substr($requestUri, 0, $pos);
        }

        $basename = basename($baseUrl);
        if (empty($basename) || !strpos($truncatedRequestUri, $basename)) {
            // no match whatsoever; set it blank
            return '';
        }

        // If using mod_rewrite or ISAPI_Rewrite strip the script filename
        // out of baseUrl. $pos !== 0 makes sure it is not matching a value
        // from PATH_INFO or QUERY_STRING
        if ((strlen($requestUri) >= strlen($baseUrl)) && ((false !== ($pos = strpos($requestUri, $baseUrl))) && ($pos !== 0))) {
            $baseUrl = substr($requestUri, 0, $pos + strlen($baseUrl));
        }

        return rtrim($baseUrl, '/');
    }

    /*
     * The following methods are derived from code of the Zend Framework (1.10dev - 2010-01-24)
     *
     * Code subject to the new BSD license (http://framework.zend.com/license/new-bsd).
     *
     * Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
     */
    protected function prepareRequestUri()
    {
        $requestUri = '';

        if (isset($_SERVER['HTTP_X_REWRITE_URL']) && false !== stripos(PHP_OS, 'WIN')) {
            // check this first so IIS will catch
            $requestUri = $_SERVER['HTTP_X_REWRITE_URL'];
        } elseif (isset($_SERVER['IIS_WasUrlRewritten']) && $_SERVER['IIS_WasUrlRewritten'] == '1' && isset($_SERVER['UNENCODED_URL']) && $_SERVER['UNENCODED_URL'] != '') {
            // IIS7 with URL Rewrite: make sure we get the unencoded url (double slash problem)
            $requestUri = $_SERVER['UNENCODED_URL'];
        } elseif (isset($_SERVER['REQUEST_URI'])) {
            $requestUri = $_SERVER['REQUEST_URI'];
            // HTTP proxy reqs setup request uri with scheme and host [and port] + the url path, only use url path
            $schemeAndHttpHost = $this->getScheme() . '://' . $this->getHttpHost();
            if (strpos($requestUri, $schemeAndHttpHost) === 0) {
                $requestUri = substr($requestUri, strlen($schemeAndHttpHost));
            }
        } elseif (isset($_SERVER['ORIG_PATH_INFO'])) {
            // IIS 5.0, PHP as CGI
            $requestUri = $_SERVER['ORIG_PATH_INFO'];
            if (isset($_SERVER['QUERY_STRING']) && $_SERVER['QUERY_STRING']) {
                $requestUri .= '?'.$_SERVER['QUERY_STRING'];
            }
        }

        return $requestUri;
    }

    /**
     * Prepares the base path.
     *
     * @return string base path
     *
     * The following methods are derived from code of the Zend Framework (1.10dev - 2010-01-24)
     *
     * Code subject to the new BSD license (http://framework.zend.com/license/new-bsd).
     *
     * Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
     */
    protected function prepareBasePath()
    {
        $filename = basename($_SERVER['SCRIPT_FILENAME']);
        $baseUrl = $this->getBaseUrl();
        if (empty($baseUrl)) {
            return '';
        }

        if (basename($baseUrl) === $filename) {
            $basePath = dirname($baseUrl);
        } else {
            $basePath = $baseUrl;
        }

        if ('\\' === DIRECTORY_SEPARATOR) {
            $basePath = str_replace('\\', '/', $basePath);
        }

        return rtrim($basePath, '/');
    }
}