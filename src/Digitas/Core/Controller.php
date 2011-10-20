<?php
/**
 * Base controller
 * 
 * @author Pierre-Louis LAUNAY <pllaunay@digitas.com>
 * @copyright Digitas France <http://digitas.fr>
 */
class Digitas_Core_Controller
{
    protected $twig;
    protected $config;
    
    /**
     * Constructor
     * 
     * @param Twig_Environment $twig 
     */
    public function __construct()
    {}
    
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
     * Redirect to $route
     * 
     * @param string $route
     * @param int $status 
     */
    public function redirect($route, $status = 302)
    {
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
        
        header('Location: ' . $route);
    }
    
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
        if ($e->getCode() == 404) {
            header('Status: 404 Not Found', false, 404);
            return $this->render('error.html.twig', array(
                'title'         => 'Page not found',
                'message'       => $this->config['app']['debug'] ? $e->getMessage() : 'You may have clicked an expired link or mistyped the address. Some web addresses are case sensitive.'
            ));
        }
        
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
        
        return $template->render($parameters);
    }
}
