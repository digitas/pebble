<?php
/**
 * @author Pierre-Louis LAUNAY <pllaunay@digitas.com>
 * @copyright Digitas France <http://digitas.fr>
 */

class Digitas_Core_Application
{
    public $config;
    protected $controller;
    protected $routes;
    protected $twig;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        ob_start();
        $this->parseConfig();
        $this->setTwig();
        $this->controller = new Digitas_Core_Controller();
        $this->controller->setTwig($this->twig);
        $this->controller->setConfig($this->config);
        $this->routes = array();
        set_exception_handler(array($this, 'exception'));
        set_error_handler(array($this, 'error'));
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
        
        if (!is_subclass_of($callback[0], 'Digitas_Core_Controller')) {
            throw new InvalidArgumentException('The controller must be extend to Digitas\Application\BaseController', 500);
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
        
        if (!is_subclass_of($callback[0], 'Digitas_Core_Controller')) {
            throw new InvalidArgumentException('The controller must be extend to Digitas\Application\BaseController', 500);
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
     * 
     */
    public function run()
    {
        if (!isset($_SERVER['HTTPS']) && $this->config['app']['ssl']) {
            return $this->controller->redirect('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
        }
        
        if ($this->config['app']['session']) {
            if (session_id() === '') {
                session_start();
            }
        }
        
        $requestUri = $_SERVER['REQUEST_URI'];
        
        if (!isset($this->routes[$requestUri][strtolower($_SERVER['REQUEST_METHOD'])])){
            throw new Digitas_Core_Exception_NotFoundHttpException();
        }
        
        $this->controller = $this->routes[$requestUri][strtolower($_SERVER['REQUEST_METHOD'])]['controller'];
        $methodName = $this->routes[$requestUri][strtolower($_SERVER['REQUEST_METHOD'])]['method'] . 'Action';
        $this->controller->setTwig($this->twig);
        $this->controller->setConfig($this->config);
        
        if (!method_exists($this->controller, $methodName)) {
            throw new BadMethodCallException('The method called ' . $methodName . ' is not implemented', 500);
        }
        
        $parameters = array();
        echo call_user_func_array(array($this->controller, $methodName), $parameters);
        
        ob_end_flush();
    }
    
    /**
     *
     * @return type 
     */
    public function exception(Exception $e)
    {
        ob_end_clean();
        
        if ($e instanceof Digitas_Core_Exception_HttpException) {
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
     *
     * @return type 
     */
    public function error($errno, $errstr, $errfile, $errline, $errcontext)
    {
        throw new ErrorException($errstr, 500, $errno, $errfile, $errline);
    }
    
    /**
     * Parse the config
     */
    protected function parseConfig()
    {
        $this->config = parse_ini_file(dirname(__FILE__) . '/../../../app/config/config.ini', true);
        
        /**
         * Override configuration on a specific environment.
         * 
         * Declare the apache constant ENV in VHOST configuration file to declare the
         * current environment.
         * 
         * Example : SetEnv ENV dev to use config.dev.ini
         */
        $env = getenv('ENV');
        $envConfig = @parse_ini_file(dirname(__FILE__) . '/../../../app/config/config_' . $env . '.ini', true);
        
        if ($envConfig) {
            foreach ($envConfig as $key => $parameters) {
                $this->config[$key] = array_merge($this->config[$key], $envConfig[$key]);
            }
        }
        
        if (!isset($this->config['app']['restriction'])) {
            $this->config['app']['restriction'] = false;
        } else {
            $this->config['app']['restriction'] = $this->convertToBoolean($this->config['app']['restriction']);
            
            if (!is_bool($this->config['app']['restriction'])){
                $this->config['app']['restriction'] = explode(',', $this->config['app']['restriction']);
            }
        }
        
        if (!isset($this->config['app']['session'])) {
            $this->config['app']['session'] = false;
        } else {
            $this->config['app']['session'] = $this->convertToBoolean($this->config['app']['session']);
        }
        
        if (!isset($this->config['app']['debug'])) {
            $this->config['app']['debug'] = false;
        } else {
            $this->config['app']['debug'] = $this->convertToBoolean($this->config['app']['debug']);
        }
        
        if (!isset($this->config['app']['ssl'])) {
            $this->config['app']['ssl'] = false;
        } else {
            $this->config['app']['ssl'] = $this->convertToBoolean($this->config['app']['ssl']);
        }
        
        if (!isset($this->config['twig']['path'])) {
            $this->config['twig']['path'] = dirname(__FILE__) . '/../../../app/Resources/views';
        }
        
        $this->config['twig']['options'] = array();
        if (!isset($this->config['twig']['cache']) && (!isset($this->config['app']['debug']) || !$this->config['app']['debug'])) {
            
            if (!file_exists(dirname(__FILE__)) . '/../../../app/cache/twig') {
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
    
    /**
     * Convert a string|integer value to boolean value if the value is 1, 
     * "true", 0 and "false".
     * 
     * @param string $value
     * @return bool|mixed
     */
    private function convertToBoolean($value)
    {
        switch($value) {
            case 1:
            case '1':
            case 'true':
                return true;
                break;
            
            case 0:
            case '0';
            case 'false':
                return false;
                break;
                
            default:
                break;
        }
        
        return $value;
    }
}
