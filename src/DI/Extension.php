<?php

namespace Bauer01\FakeLogin\DI;

use Nette\DI\CompilerExtension,
    Nette\DI\Compiler,
    Nette\Configurator;

// Nette 2.0 back compatibility
if (!class_exists('Nette\DI\CompilerExtension')) {
    class_alias('Nette\Config\CompilerExtension', 'Nette\DI\CompilerExtension');
    class_alias('Nette\Config\Compiler', 'Nette\DI\Compiler');
}

class Extension extends CompilerExtension
{

    /** @var array $defaults Default configuration */
    private $defaults = [
        "id" => null,
        "roles" => [],
        "data" => []
    ];

    /**
     * Processes configuration data
     */
    public function loadConfiguration()
    {
        $builder = $this->getContainerBuilder();
        $config = $this->getConfig($this->defaults);


        // Create panel service in debug mode
        if ($builder->parameters["debugMode"]) {

            $builder->addDefinition($this->prefix("panel"))
                ->setClass("Bauer01\FakeLogin\Panel", array($config))
                ->addSetup('Nette\Diagnostics\Debugger::getBar()->addPanel(?)', array('@self'));

            $builder->getDefinition('application')
                ->addSetup('?->onPresenter[] = ?', array('@self', array($this->prefix('@panel'), 'register')));
        }
    }

    /**
     * Register extension
     */
    public static function register(Configurator $configurator)
    {
        $class = get_class();
        $configurator->onCompile[] = function ($config, Compiler $compiler) use ($class) {
            $compiler->addExtension("fakelogin", new $class);
        };
    }

}