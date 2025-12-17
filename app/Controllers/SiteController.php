<?php
namespace App\Controllers;
use App\Controllers\Controller;



class SiteController extends Controller {


    public function index(){
        
        $this->render("home.html.twig",[]);
    }

}