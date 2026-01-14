<?php
namespace App\Controllers;
use Twig\Environment;
use App\Middlewares\JwtService;
use App\Models\UserModel;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

use PDO;


final class AuthController extends Controller {

    /**
     * Constructeur du contrôleur d'authentification.
     *
     * @param PDO $pdo Instance de la connexion PDO.
     * @param Environment $twig Instance de Twig.
     */
    public function __construct(PDO $pdo, Environment $twig)
    {
        parent::__construct($pdo, $twig);
    }

    /**
     * Traite l'inscription d'un nouvel utilisateur.
     *
     * @param string $email Email de l'utilisateur.
     * @param string $pwd Mot de passe de l'utilisateur.
     * @param string|null $username Pseudo de l'utilisateur (optionnel).
     * @return void
     */
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
        
    /**
     * Affiche le formulaire d'inscription.
     *
     * @return void
     */
    public function signup(){

       $this->render("signup.html.twig",[]);

    }

    /**
     * Méthode de connexion (placeholder).
     *
     * @return void
     */
    public function connection(){

    }

    /**
     * Traite la connexion d'un utilisateur.
     *
     * @param string $mail Email de l'utilisateur.
     * @param string $pwd Mot de passe de l'utilisateur.
     * @return void
     */
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

    /**
     * Affiche le formulaire de connexion.
     *
     * @return void
     */
    public function signin(){

        $this->render("signin.html.twig",[]);
    }

    /**
     * Déconnecte l'utilisateur et redirige vers l'accueil.
     *
     * @return void
     */
    public function logout(){
        session_destroy();
        header("Location: /CookBook/");
    }



}    
