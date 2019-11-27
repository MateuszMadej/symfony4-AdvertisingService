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
            $user->setCity($data['city']);
            $user->setBlocked(FALSE);

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
                    if($checkEmail->getBlocked() == FALSE)
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
     * @Route("/usersAds", name="usersAds")
     */
    public function usersAds(SessionInterface $session)
    {
        // check if user is logged
        if($session->get('currentUser'))
        {
            $currentUser = $session->get('currentUser');
        }
        else
        {
            $currentUser = FALSE;
            return $this->redirectToRoute('index');
        }

        // users ads panel

        return $this->render('main/usersAds.html.twig', [
            'currentUser' => $currentUser,
        ]); 
    }

    /**
     * @Route("/manageAds", name="manageAds")
     */
    public function manageAds(SessionInterface $session)
    {
        // check if user is logged
        if($session->get('currentUser'))
        {
            $currentUser = $session->get('currentUser');

            if($currentUser->getUserType() != "admin")
            {
                return $this->redirectToRoute('index');
            }
        }
        else
        {
            $currentUser = FALSE;
            return $this->redirectToRoute('index');
        }

        // admin panel for manage ads

        return $this->render('main/manageAds.html.twig', [
            'currentUser' => $currentUser,
        ]); 
    }

    /**
     * @Route("/manageUsers", name="manageUsers")
     */
    public function manageUsers(Request $request, SessionInterface $session)
    {
        // check if user is logged
        if($session->get('currentUser'))
        {
            $currentUser = $session->get('currentUser');

            if($currentUser->getUserType() != "admin")
            {
                return $this->redirectToRoute('index');
            }
        }
        else
        {
            $currentUser = FALSE;
            return $this->redirectToRoute('index');
        }

        $entityManager = $this->getDoctrine()->getManager();
        $data = $request->request->all();
        $defaultSearchCounter = 5;

        // find in the db all users from the newest
        $findUsers = $entityManager->getRepository(Users::class)->findBy([],['id'=>'DESC']);

        if(isset($data['search']))
        {
            $searchCounter = $data['inputState'];
            $email = $data['email'];

            return $this->render('main/manageUsers.html.twig', [
                'currentUser' => $currentUser,
                'findUsers' => $findUsers,
                'searchCounter' => $searchCounter,
                'email' => $email,
            ]); 

        }

        return $this->render('main/manageUsers.html.twig', [
            'currentUser' => $currentUser,
            'findUsers' => $findUsers,
            'defaultSearchCounter' => $defaultSearchCounter,
        ]); 
    }

    /**
     * @Route("/blockUser/{id}", name="blockUser")
     */
    public function blockUser($id, SessionInterface $session)
    {
        // check if user is logged
        if($session->get('currentUser'))
        {
            $currentUser = $session->get('currentUser');

            if($currentUser->getUserType() != "admin")
            {
                return $this->redirectToRoute('index');
            }
        }
        else
        {
            $currentUser = FALSE;
            return $this->redirectToRoute('index');
        }

        $entityManager = $this->getDoctrine()->getManager();
        $user = $entityManager->getRepository(Users::class)->find($id);    

        if($user->getBlocked() == FALSE) // block user
        {
            $user->setBlocked(TRUE);
            $entityManager->persist($user);
            $entityManager->flush(); 
            return $this->redirectToRoute('manageUsers');
        }

        if($user->getBlocked() == TRUE) // unblock user
        {
            $user->setBlocked(FALSE);
            $entityManager->persist($user);
            $entityManager->flush(); 
            return $this->redirectToRoute('manageUsers');
        } 
    }

    /**
     * @Route("/editUser/{id}", name="editUser")
     */
    public function editUser($id, Request $request,SessionInterface $session)
    {
        // check if user is logged
        if($session->get('currentUser'))
        {
            $currentUser = $session->get('currentUser');

            if($currentUser->getUserType() != "admin")
            {
                return $this->redirectToRoute('index');
            }
        }
        else
        {
            $currentUser = FALSE;
            return $this->redirectToRoute('index');
        }

        $entityManager = $this->getDoctrine()->getManager();
        $data = $request->request->all();
        $user = $entityManager->getRepository(Users::class)->find($id); 

        if(isset($data['edit']))
        {
            $checkEmail = $entityManager->getRepository(Users::class)->findOneBy(array('email' => $data['email']));

            if($checkEmail && $checkEmail != $user) // if email address exists in the database and is diffrent from currently edited email
            {
                $errorMail = TRUE;
                return $this->render('main/editUser.html.twig', [
                    'currentUser' => $currentUser,
                    'user' => $user,
                    'errorMail' => $errorMail,
                ]);
            }

            $user->setEmail($data['email']);

            if($data['password']) // if password is set in form
            {
                if(strlen($data['password']) < 8) // if password is shorter than 8
                {
                    $errorPassword = TRUE;
                    return $this->render('main/editUser.html.twig', [
                        'currentUser' => $currentUser,
                        'user' => $user,
                        'errorPassword' => $errorPassword,
                    ]);
                }

                if($data['password'] != $data['repassword']) // if passwords are mismatched
                {
                    $errorRePassword = TRUE;
                    return $this->render('main/editUser.html.twig', [
                        'currentUser' => $currentUser,
                        'user' => $user,
                        'errorRePassword' => $errorRePassword,
                    ]);
                }

                $storedPassword = password_hash($data['password'], PASSWORD_DEFAULT); // password hashing
                $user->setPassword($storedPassword);
            }
            
            $user->setName($data['name']);
            $user->setSurname($data['surname']);
            $user->setPhoneNumber($data['phone']);
            $user->setCity($data['city']);

            if($data['blocked'] == "no")
            {
                $user->setBlocked(FALSE);
            }
            if($data['blocked'] == "yes")
            {
                $user->setBlocked(TRUE);
            }

            if($data['userType']) // if userType was changed in form
            {
                $user->setUserType($data['userType']);
            }

            $entityManager->persist($user);
            $entityManager->flush();
        }

        return $this->render('main/editUser.html.twig', [
            'currentUser' => $currentUser,
            'user' => $user,

        ]);
    }
}
