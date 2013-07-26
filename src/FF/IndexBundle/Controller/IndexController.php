<?php

namespace FF\IndexBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class IndexController extends Controller
{
	/**
	 * @Route("/", name="home")
	 */
    public function indexAction()
    {
        return $this->render('FFIndexBundle:Index:index.html.twig');
    }
}
