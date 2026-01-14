<?php
namespace App\Controllers;
use App\Controllers\Controller;



class SiteController extends Controller {

    /**
     * Affiche la page d'accueil du site.
     *
     * @return void
     */
    public function index(){
        
        $this->render("home.html.twig",[]);
    }

}
