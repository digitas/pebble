<?php
/**
 * Base Controller
 *
 * @copyright Digitas France
 * @author Pierre-Louis LAUNAY <pllaunay@digitas.com>
 */
class Pebble_Core_Controller
{
    protected $twig;
    protected $config;
    protected $request;

    /**
     * Set the Twig_Environment object
     *
     * @param Twig_Environment $twig
     */
    public function setRequest(Pebble_Core_Request $request)
    {
        $this->request = $request;
    }

    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Set the Twig_Environment object
     *
     * @param Twig_Environment $twig
     */
    public function setTwig(Twig_Environment $twig)
    {
        $this->twig = $twig;
    }

    /**
     * Set the global config
     *
     * @param array $config
     */
    public function setConfig(array $config)
    {
        $this->config = $config;
    }

    /**
     * Declare all routes
     *
     * @param Pebble_Core_Application $app
     *
     * @return Pebble_Core_ControllerCollection
     */
    public function connect(Pebble_Core_Application $app)
    {
        $controllerCollection = new Pebble_Core_ControllerCollection();

        return $controllerCollection;
    }

    /**
     * Redirect to $route
     *
     * @param string $route
     * @param int $status
     */
    public function redirect($route, $status = 302)
    {
        if (!isset($this->config['app']['basedir'])) {
            $basedir = '/';
        } else {
            $basedir = $this->config['app']['basedir'];
            if (!$basedir[0] === '/') {
                $basedir = '/' . $basedir;
            }
        }

        //flush output buffer before sending http header
        ob_end_clean();

        switch($status) {
            case 301:
                header('Status: 301 Moved Permanently', false, 301);
                break;

            case 302:
                header('Status: 302 Found', false, 302);
                break;

            default:
                header('Status: ' . $status, false, $status);
        }

        if (preg_match('/^https?:\/\//', $route)) {//absolute
            header('Location: ' . $route);
        } else {
            if ($route[0] !== '/') {
                $route = '/' . $route;
            }

            if ($basedir !== '/') {
                $route = $basedir . $route;
            }

           header('Location: ' . $route);
        }
        die;
    }

    /**
     * Set the specific headers for download
     *
     * @param string $contentType
     * @param string $filename
     * @param string $content
     * @return string
     */
    public function download($contentType, $filename, $content)
    {
        header('Content-type: ' . $contentType);
        header('Content-disposition: attachment; filename="' . $filename . '"');

        return $content;
    }

    /**
     * Create a new token and return it
     *
     * @return string
     */
    public function getToken()
    {
        $token = md5(uniqid(mt_rand(), true));
        $_SESSION['token'] = $token;

        return $token;
    }

    /**
     * Check the token
     *
     * @return bool
     */
    public function checkToken()
    {
        return (isset($_SESSION['token'])
                && (
                    (isset($_GET['token']) && $_SESSION['token'] === $_GET['token'])
                    || (isset($_POST['token']) && $_SESSION['token'] === $_POST['token'])
                ));
    }

    /**
     * Display the error page
     *
     * @param Exception $e
     * @return string
     */
    final public function errorAction(Exception $e)
    {
        if ($e instanceof Pebble_Core_Exception_HttpException) {

            $status = isset(Pebble_Core_Exception_HttpException::$status[$e->getCode()])?Pebble_Core_Exception_HttpException::$status[$e->getCode()]:'Unknown error';

            header("Status: $status", false, $e->getCode());

            if ($e->getCode() == 404) {
                return $this->render('error.html.twig', array(
                    'title'         => 'Page not found',
                    'message'       => $this->config['app']['debug'] ? $e->getMessage() : 'The page you requested was not found.'
                ));
            } elseif ($e->getCode() == 403) {
                return $this->render('error.html.twig', array(
                    'title'         => 'Restricted area',
                    'message'       => $this->config['app']['debug'] ? $e->getMessage() : 'You are not allowed to access this area.'
                ));
            }
        }

        header("Status: Internal Server Error", false, 500);

        return $this->render('error.html.twig', array(
            'title'         => 'Oups, an error happened',
            'message'       => $this->config['app']['debug'] ? $e->getMessage() . ' (' . $e->getFile() . ':' . $e->getLine() . ')' : "We're sorry, something went wrong."
        ));
    }

    /**
     * Render a template
     *
     * @param type $templateName
     * @param array $parameter
     */
    protected function render($templateName, array $parameters = array())
    {
        $template = $this->twig->loadTemplate($templateName);
        $parameters = array_merge(array('app' => $this->config['app']), $parameters);

        return $template->render($parameters);
    }

    /**
     * Check if an user is logged
     *
     * @return bool
     */
    protected function isLogged()
    {
        return (isset($_SESSION['user']) && $_SESSION['user'] instanceof Pebble_Core_UserInterface);
    }

    /**
     * Return true if an user is authorized to admin
     *
     * @return bool
     */
    protected function protect()
    {
        $ip = isset($_SERVER['HTTP_TRUE_CLIENT_IP'])?$_SERVER['HTTP_TRUE_CLIENT_IP']:@$_SERVER['REMOTE_ADDR'];
        if ($this->config['app']['restriction'] !== null
            && !in_array($ip, $this->config['app']['restriction'])) {

            $found = false;
            foreach($this->config['app']['restriction'] as $allowed) {
                if ($this->ipInRange($ip, $allowed)) {
                    $found = true;
                    break;
                }
            }

            if (!$found) {
                throw new Pebble_Core_Exception_ForbiddenHttpException(sprintf('You are not allowed to access this area. Your IP is %s', $ip));
            }
        }
    }

    /**
     * Check if the $ip is in $range.
     *
     * $range can be in several format
     *      1. Wildcard format:     1.2.3.*
     *      2. CIDR format:         1.2.3/24  OR  1.2.3.4/255.255.255.0
     *      3. Start-End IP format: 1.2.3.0-1.2.3.255
     *
     * @param string $ip
     * @param string $range
     * @return boolean
     */
    protected function ipInRange($ip, $range)
    {
        if (strpos($range, '/') !== false) {

            // $range is in IP/NETMASK format
            list($range, $netmask) = explode('/', $range, 2);

            if (strpos($netmask, '.') !== false) {
              // $netmask is a 255.255.0.0 format
              $netmask = str_replace('*', '0', $netmask);
              $netmaskDec = ip2long($netmask);

              return ((ip2long($ip) & $netmaskDec) == (ip2long($range) & $netmaskDec));
            } else {
              // $netmask is a CIDR size block
              // fix the range argument
              $x = explode('.', $range);
              while(count($x)<4) $x[] = '0';
              list($a,$b,$c,$d) = $x;
              $range = sprintf("%u.%u.%u.%u", empty($a)?'0':$a, empty($b)?'0':$b,empty($c)?'0':$c,empty($d)?'0':$d);
              $range_dec = ip2long($range);
              $ipDec = ip2long($ip);

              # Strategy 1 - Create the netmask with 'netmask' 1s and then fill it to 32 with 0s
              #$netmaskDec = bindec(str_pad('', $netmask, '1') . str_pad('', 32-$netmask, '0'));

              # Strategy 2 - Use math to create it
              $wildcardDec = pow(2, (32 - $netmask)) - 1;
              $netmaskDec = ~ $wildcardDec;

              return (($ipDec & $netmaskDec) == ($range_dec & $netmaskDec));
            }
        } else {
            // range might be 255.255.*.* or 1.2.3.0-1.2.3.255
            if (strpos($range, '*') !== false) { // a.b.*.* format
              // Just convert to A-B format by setting * to 0 for A and 255 for B
              $lower = str_replace('*', '0', $range);
              $upper = str_replace('*', '255', $range);
              $range = "$lower-$upper";
            }

            if (strpos($range, '-') !== false) { // A-B format
              list($lower, $upper) = explode('-', $range, 2);
              $lowerDec = (float)sprintf("%u", ip2long($lower));
              $upperDec = (float)sprintf("%u", ip2long($upper));
              $ipDec = (float)sprintf("%u", ip2long($ip));

              return (($ipDec >= $lowerDec) && ($ipDec <= $upperDec));
            }

            return false;
        }
    }
}
