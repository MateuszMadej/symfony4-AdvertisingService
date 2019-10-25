<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use App\Entity\Users;
use \DateTime;

class MainController extends AbstractController
{
    /**
     * @Route("/", name="index")
     */
    public function index(SessionInterface $session)
    {
        // check if user is logged
        if($session->get('currentUser'))
        {
            $currentUser = $session->get('currentUser');
            // dump($currentUser);
        }
        else
        {
            $currentUser = FALSE;
        }

        return $this->render('main/index.html.twig', [
                'currentUser' => $currentUser,
            ]); 
    }

    /**
     * @Route("/registration", name="registration")
     */
    public function registration(Request $request, SessionInterface $session)
    {
        // if user is logged redirect to main page
        if($session->get('currentUser'))
        {
            return $this->redirectToRoute('index');
        }


        $entityManager = $this->getDoctrine()->getManager();
        $data = $request->request->all();

        if(isset($data['add']))
        {
            $checkEmail = $entityManager->getRepository(Users::class)->findOneBy(array('email' => $data['email']));

            if($checkEmail) // if email address exists in the database
            {
                $errorMail = TRUE;
                return $this->render('main/registration.html.twig', [
                    'errorMail' => $errorMail,
                ]);
            }

            if(strlen($data['password']) < 8) // if password is shorter than 8
            {
                $errorPassword = TRUE;
                return $this->render('main/registration.html.twig', [
                    'errorPassword' => $errorPassword,
                ]);
            }

            if($data['password'] != $data['repassword']) // if passwords are mismatched
            {
                $errorRePassword = TRUE;
                return $this->render('main/registration.html.twig', [
                    'errorRePassword' => $errorRePassword,
                ]);
            }

            $storedPassword = password_hash($data['password'], PASSWORD_DEFAULT); // password hashing

            $user = new Users();
            $user->setEmail($data['email']);
            $user->setPassword($storedPassword);
            $user->setName($data['name']);
            $user->setSurname($data['surname']);
            $user->setPhoneNumber($data['phone']);
            $user->setUserType("user");

            $entityManager->persist($user);
            $entityManager->flush();

            // redirect to login page
            return $this->redirectToRoute('login');
        }

        return $this->render('main/registration.html.twig');
    }

    /**
     * @Route("/login", name="login")
     */
    public function login(Request $request, SessionInterface $session)
    {
        // if user is logged redirect to main page
        if($session->get('currentUser'))
        {
            return $this->redirectToRoute('index');
        }

        $entityManager = $this->getDoctrine()->getManager();
        $data = $request->request->all();
        $errorLogin = FALSE;

        if(isset($data['email']) && isset($data['password']))
        {
            // find email in the database
            $checkEmail = $entityManager->getRepository(Users::class)->findOneBy(array('email' => $data['email']));

            if($checkEmail) // if email is in the database
            {
                if(password_verify($data['password'], $checkEmail->getPassword())) // if password for this email is correct 
                {
                    // create login session
                    $session->set('currentUser', $checkEmail);
                    return $this->redirectToRoute('index');
                }

                else
                {
                    $errorLogin = TRUE;
                }
            }

            else
            {
                $errorLogin = TRUE;
            }
        }

        return $this->render('main/login.html.twig', [
            'errorLogin' => $errorLogin,
        ]);
    }

    /**
     * @Route("/logout", name="logout")
     */
    public function logout(SessionInterface $session)
    {
        // remove session
        $session->remove('currentUser');
        return $this->redirectToRoute('index');
    }

    /**
     * @Route("/profile", name="profile")
     */
    public function profile()
    {
        return $this->render('main/profile.html.twig');
    }
}
