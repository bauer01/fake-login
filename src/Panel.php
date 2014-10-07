<?php

namespace Bauer01\FakeLogin;

use Nette\Application,
    Nette\Security,
    Nette\Diagnostics\Dumper;

class Panel extends Application\UI\Control implements \Nette\Diagnostics\IBarPanel
{

    /** @var \Nette\Security\User */
    private $user;

    /** @var \Nette\Security\Identity */
    private $identity;

    public function __construct(array $config, Security\User $user)
    {
        if ($config["id"] !== null) {
            $this->identity = new Security\Identity($config["id"], $config["roles"], $config["data"]);
        }
        $this->user = $user;
    }

    public function getTab()
    {
        ob_start();
        include __DIR__ . "/templates/tab.phtml";
        return ob_get_clean();
    }

    public function getPanel()
    {
	$template = parent::getTemplate();
        $template->setFile(__DIR__ . '/templates/panel.latte');
        $template->identity = $this->identity;
        $template->user = $this->user;
        $template->dumper = function ($variable, $collapsed = false) {

            if (class_exists('Nette\Diagnostics\Dumper')) {
                return Dumper::toHtml($variable, [Dumper::COLLAPSE => $collapsed]);
            }

            // Nette 2.0 back compatibility
            return \Nette\Diagnostics\Helpers::clickableDump($variable, $collapsed);
        };

        ob_start();
        if ($this->parent) {
            $template->render();
        }
        return ob_get_clean();
    }

    public function register(Application\Application $application, Application\IPresenter $presenter)
    {
        if (!$this->parent) {
            $presenter->addComponent($this, "_fakeLogin");
        }
    }

    public function createComponentLoginForm($name)
    {
        $form = new Application\UI\Form($this, $name);
        $form->addSubmit("submit", $this->user->isLoggedIn() ? "Logout" : "Login");
        $form->onSuccess[] = array($this, "onSuccessLoginForm");
        return $form;
    }

    public function onSuccessLoginForm()
    {
        if ($this->user->isLoggedIn()) {
            $this->user->logout(true);
        } else {
            $this->user->login($this->identity);
        }

        $this->redirect("this");
    }

}