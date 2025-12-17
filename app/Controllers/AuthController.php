<?php
namespace App\Controllers;
use Twig\Environment;
use App\Middlewares\JwtService;
use App\Models\UserModel;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

use PDO;


final class AuthController extends Controller {

    public function __construct(PDO $pdo, Environment $twig)
    {
        parent::__construct($pdo, $twig);
    }

    public function register($email,$pwd){

        $UserModel = new UserModel($this->pdo,"users");
        $hash = password_hash($pwd, PASSWORD_DEFAULT);
        
        $row = $UserModel->addUser($email,$hash);
        if($row !== 0){
            session_regenerate_id(true);
            $_SESSION["user"] = $email;
            $_SESSION["role"] = 'user';
            // Après inscription, l'utilisateur reste connecté et va sur la page d'accueil
            header("location: /CookBook/");
        }
        else{
            header("location: /CookBook/signup");
        }

    }
        
    public function signup(){

       $this->render("signup.html.twig",[]);

    }

    public function connection(){

    }

    public function login($mail, $pwd){
        $UserModel = new UserModel($this->pdo, "users");
        $result = $UserModel->login($mail, $pwd);
        if($result ===false){
            header("Location: /CookBook/signin");
        }
        else{
            $_SESSION["user"] = $result["email"];
            $_SESSION["id_user"] = $result["id"];
            $_SESSION["role"] = $result["role"] ?? 'user';

            // Si c'est un admin, on le redirige vers le tableau de bord admin,
            // sinon vers la page d'accueil classique.
            if (!empty($result['role']) && $result['role'] === 'admin') {
                header("Location: /CookBook/admin");
            } else {
                header("Location: /CookBook/");
            }
        }
    }

    public function signin(){

        $this->render("signin.html.twig",[]);
    }

    public function logout(){
        session_destroy();
        header("Location: /CookBook/");
    }



}    
