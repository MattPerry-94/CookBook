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

    public function register($email, $pwd, $username = null){

        $UserModel = new UserModel($this->pdo,"users");
        $hash = password_hash($pwd, PASSWORD_DEFAULT);
        
        $newUserId = $UserModel->addUser($email, $hash, $username);
        
        if($newUserId > 0){
            session_regenerate_id(true);
            // On stocke le pseudo s'il existe, sinon l'email
            $_SESSION["user"]    = !empty($username) ? $username : $email;
            $_SESSION["id_user"] = $newUserId; // Ajout de l'ID utilisateur en session
            $_SESSION["role"]    = 'user';
            
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
        if($result === false){
            $this->render("signin.html.twig", [
                'error' => 'Mot de passe incorrect ou utilisateur inexistant.',
                'old_email' => $mail
            ]);
        }
        else{
            // On stocke le pseudo (name) s'il existe, sinon l'email
            $_SESSION["user"] = !empty($result["name"]) ? $result["name"] : $result["email"];
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
