<?php
/**
 * @copyright Digitas France
 * @author Pierre-Louis LAUNAY <pllaunay@digitas.com>
 */

abstract class Pebble_Core_Application
{
    public $config;
    protected $controller;
    protected $routes;
    protected $twig;
    protected $request;

    abstract public function getControllers();

    /**
     * Constructor
     */
    public function __construct($environment = 'prod')
    {
        ob_start();
        $this->request = new Pebble_Core_Request();
        $this->parseConfig($environment);
        $this->setTwig();
        $this->controller = new Pebble_Core_Controller();
        $this->controller->setTwig($this->twig);
        $this->controller->setConfig($this->config);
        $this->routes = array();

        if (isset($this->config['db'])) {
            Pebble_Core_Database::getInstance($this->config['db']);
        }

        set_exception_handler(array($this, 'exception'));
        set_error_handler(array($this, 'error'));

        $this->registerControllerProviders();
    }

    /**
     * Set a new route GET
     *
     * @param string $route
     * @param array $callback
     */
    public function get($route, array $callback)
    {
        if (count($callback) !== 2) {
            throw new InvalidArgumentException('The callback must be an array with the controller and method to call', 500);
        }

        if (!is_subclass_of($callback[0], 'Pebble_Core_Controller')) {
            throw new InvalidArgumentException('The controller must extend Pebble_Core_Controller', 500);
        }

        $this->routes[$route]['get']['controller'] = $callback[0];
        $this->routes[$route]['get']['method'] = $callback[1];
    }

    /**
     * Set a new route POST
     *
     * @param string $route
     * @param array $callback
     */
    public function post($route, array $callback)
    {
        if (count($callback) !== 2) {
            throw new InvalidArgumentException('The callback must be an array with the controller and method to call', 500);
        }

        if (!is_subclass_of($callback[0], 'Pebble_Core_Controller')) {
            throw new InvalidArgumentException('The controller must extend Pebble_Core_Controller', 500);
        }

        $this->routes[$route]['post']['controller'] = $callback[0];
        $this->routes[$route]['post']['method'] = $callback[1];
    }

    /**
     * Set a new route POST and GET
     *
     * @param string $route
     * @param array $callback
     */
    public function match($route, array $callback)
    {
        $this->get($route, $callback);
        $this->post($route, $callback);
    }

    /**
     * Execute the action corresponding the path info of current request
     */
    public function run()
    {
        if (!$this->request->isSecure() && $this->config['app']['ssl']) {
            return $this->controller->redirect('https://' . $this->request->getHost() . $this->request->getRequestUri());
        }

        if ($this->config['app']['session']) {
            if (session_id() === '') {
                session_start();
            }
        }

        $requestUri =  $this->request->getRequestUri();

        //redirect URL with trailing slash
        if ($requestUri !== '/' && $requestUri[strlen($requestUri)-1] === '/') {
            $controller = new Pebble_Core_Controller();
            $controller->setConfig($this->config);
            $controller->redirect(rtrim($requestUri, '/'), 301);
            return;
        }

        $pathInfo = $this->request->getPathInfo();
        $method = strtolower($this->request->getMethod());
        if (!isset($this->routes[$pathInfo][$method])){

            throw new Pebble_Core_Exception_NotFoundHttpException();
        }

        $route = $this->routes[$pathInfo][$method];
        $this->controller = $route['controller'];
        $methodName = $route['method'] . 'Action';
        $this->controller->setTwig($this->twig);
        $this->controller->setConfig($this->config);

        if (!method_exists($this->controller, $methodName)) {
            throw new BadMethodCallException('The method called ' . $methodName . ' is not implemented', 500);
        }

        $parameters = array();

        //cookie compatibility header for ie7
//        header('P3P: CP="ALL ADM DEV PSAi COM OUR OTRo STP IND ONL"');

        echo call_user_func_array(array($this->controller, $methodName), $parameters);

        ob_end_flush();
    }

    /**
     * Display the error page
     */
    public function exception(Exception $e)
    {
        ob_end_clean();

        if ($e instanceof Pebble_Core_Exception_HttpException) {
            $statusCode = $e->getCode();

            switch($statusCode) {
                case 404:
                case 403:
                    $message = $e->getMessage();
                    break;

                default:
                    $message = 'Server error';
            }

            header($_SERVER["SERVER_PROTOCOL"] . $statusCode . ' ' . $message);
        }

        echo $this->controller->errorAction($e);
    }

    /**
     * Throw an exception
     *
     * @return ErrorException
     */
    public function error($errno, $errstr, $errfile, $errline, $errcontext)
    {
        throw new ErrorException($errstr, 500, $errno, $errfile, $errline);
    }

    /**
     * Registers controller
     */
    protected function registerControllerProviders()
    {
        foreach($this->getControllers() as $prefix => $controller) {
            $this->mount($prefix, $controller);
        }
    }

    /**
     * Built a collection of routes
     *
     * @param string $prefix
     * @param Pebble_Core_Controller $controller
     */
    protected function mount($prefix, Pebble_Core_Controller $controller)
    {
        //get every actions
        $controllerCollection = $controller->connect($this);

        foreach ($controllerCollection->getRoutes() as $route => $callbacks) {

            foreach ($callbacks as $method => $callback) {
                call_user_func_array(
                        array($this, $method),
                        array(
                            $route,
                            array($controller, $callback)
                            )
                        );
            }
        }
    }

    /**
     * Parse the config
     */
    protected function parseConfig($environment)
    {
        $this->config = parse_ini_file(dirname(__FILE__) . '/../../../app/config/config.ini', true);
        $this->config['app']['env'] = strtolower($environment);

        /**
         * Override configuration on a specific environment.
         *
         * Example : SetEnv ENV dev to use config.dev.ini
         */
        $envConfig = @parse_ini_file(dirname(__FILE__) . '/../../../app/config/config_' . $this->config['app']['env'] . '.ini', true);

        if ($envConfig) {
            foreach ($envConfig as $key => $parameters) {
                $this->config[$key] = array_merge($this->config[$key], $envConfig[$key]);
            }
        }

        if (!isset($this->config['app']['restriction'])) {
            $this->config['app']['restriction'] = null;
        } else {
            if ($this->config['app']['restriction'] == '') {
                $this->config['app']['restriction'] = null;
            } else {
                $this->config['app']['restriction'] = explode(',', $this->config['app']['restriction']);

                function trimValue(&$value, $key)
                {
                    $value = trim($value);
                }
                array_walk($this->config['app']['restriction'], 'trimValue');
            }
        }

        if (!isset($this->config['app']['session'])) {
            $this->config['app']['session'] = false;
        } else {
            $this->config['app']['session'] = (boolean)$this->config['app']['session'];
        }

        if (!isset($this->config['app']['debug'])) {
            $this->config['app']['debug'] = false;
        } else {
            $this->config['app']['debug'] = (boolean)$this->config['app']['debug'];
        }

        if (!isset($this->config['app']['ssl'])) {
            $this->config['app']['ssl'] = false;
        } else {
            $this->config['app']['ssl'] = (boolean)$this->config['app']['ssl'];
        }

        if (!isset($this->config['twig']['path'])) {
            $this->config['twig']['path'] = dirname(__FILE__) . '/../../../app/Resources/views';
        }

        $this->config['twig']['options'] = array();
        if (!isset($this->config['twig']['cache']) && (!isset($this->config['app']['debug']) || !$this->config['app']['debug'])) {

            if (!file_exists(dirname(__FILE__) . '/../../../app/cache/twig')) {
                mkdir(dirname(__FILE__) . '/../../../app/cache/twig', 0777);
            }

            $this->config['twig']['options'] = array('cache' => dirname(__FILE__) . '/../../../app/cache/twig');
        } elseif (isset($this->config['twig']['cache']) && (!isset($this->config['app']['debug']) || !$this->config['debug'])) {
            $this->config['twig']['options'] = array('cache' => $this->config['twig']['cache']);
        }
    }

    /**
     * Create a new Twig_Environment
     *
     * @param Twig_Environment $twig
     */
    protected function setTwig()
    {
        $loader = new Twig_Loader_Filesystem($this->config['twig']['path']);
        $this->twig = new Twig_Environment($loader, $this->config['twig']['options']);
    }
}
