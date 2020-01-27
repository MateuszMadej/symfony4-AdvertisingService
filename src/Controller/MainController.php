<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use App\Entity\Users;
use App\Entity\AdsCategories;
use App\Entity\Ads;
use App\Entity\AdsPhotos;
use \DateTime;

class MainController extends AbstractController
{
    /**
     * @Route("/", name="index")
     */
    public function index(Request $request, SessionInterface $session)
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

        $entityManager = $this->getDoctrine()->getManager();
        $data = $request->request->all();

        $findUsers = $entityManager->getRepository(Users::class)->findBy([], ['id' => 'ASC']);
        $findUsersByCity = $entityManager->getRepository(Users::class)->findBy([], ['city' => 'ASC']);
        $findCategories = $entityManager->getRepository(AdsCategories::class)->findBy([],['name'=>'ASC']);
        $cities = [];

        foreach($findUsersByCity as $userCity) // add only uniques cities
        {
            if($userCity->getUserType() != "admin")
            {
                if(!in_array($userCity->getCity(), $cities, true))
                {
                    array_push($cities, $userCity->getCity());
                }
            }
        }

        if(isset($data['search']))
        {
            $adName = $data['name'];
            $adsCity = $data['city'];
            $adsCategory = $data['category'];

            // using this data find accurate ads
        }

        return $this->render('main/index.html.twig', [
            'currentUser' => $currentUser,
            'findUsers' => $findUsers,
            'findCategories' => $findCategories,
            'cities' => $cities,
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
     * @Route("/myAccount", name="myAccount")
     */
    public function myAccount(SessionInterface $session)
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

        $entityManager = $this->getDoctrine()->getManager();
        
        // for displaying current data after editing (wont work corrently using session data)
        $userId = $currentUser->getId();
        $user = $entityManager->getRepository(Users::class)->find($userId);

        return $this->render('main/myAccount.html.twig', [
            'currentUser' => $currentUser,
            'user' => $user,
        ]); 
    }

    /**
     * @Route("/deleteMyAccount/{id}", name="deleteMyAccount")
     */
    public function deleteMyAccount($id, SessionInterface $session)
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

        $entityManager = $this->getDoctrine()->getManager();
        $user = $entityManager->getRepository(Users::class)->find($id);

        $user->setBlocked(TRUE);
        $entityManager->persist($user);
        $entityManager->flush(); 

        $session->remove('currentUser');
        return $this->redirectToRoute('index');
    }

    /**
     * @Route("/editMyAccount/{id}", name="editMyAccount")
     */
    public function editMyAccount($id, Request $request, SessionInterface $session)
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

        $entityManager = $this->getDoctrine()->getManager();
        $data = $request->request->all();
        $user = $entityManager->getRepository(Users::class)->find($id);

        if(isset($data['edit']))
        {
            $checkEmail = $entityManager->getRepository(Users::class)->findOneBy(array('email' => $data['email']));

            if($checkEmail && $checkEmail != $user) // if email address exists in the database and is diffrent from currently edited email
            {
                $errorMail = TRUE;
                return $this->render('main/editMyAccount.html.twig', [
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
                    return $this->render('main/editMyAccount.html.twig', [
                        'currentUser' => $currentUser,
                        'user' => $user,
                        'errorPassword' => $errorPassword,
                    ]);
                }

                if($data['password'] != $data['repassword']) // if passwords are mismatched
                {
                    $errorRePassword = TRUE;
                    return $this->render('main/editMyAccount.html.twig', [
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

            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('myAccount');
        }

        return $this->render('main/editMyAccount.html.twig', [
            'currentUser' => $currentUser,
            'user' => $user,
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

    /**
     * @Route("/manageCategories", name="manageCategories")
     */
    public function manageCategories(Request $request, SessionInterface $session)
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

        // find in the db all users from the newest
        $findCategories = $entityManager->getRepository(AdsCategories::class)->findBy([],['id'=>'DESC']);

        return $this->render('main/manageCategories.html.twig', [
            'currentUser' => $currentUser,
            'categories' => $findCategories,
        ]); 
    }

    /**
     * @Route("/addCategory", name="addCategory")
     */
        public function addCategory(Request $request, SessionInterface $session)
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

        if(isset($data['add']))
        {
            $checkName = $entityManager->getRepository(AdsCategories::class)->findOneBy(array('name' => $data['name']));

            if($checkName) 
            {
                $errorName = TRUE;
                return $this->render('main/addCategory.html.twig', [
                    'errorName' => $errorName,
                    'currentUser' => $currentUser,
                ]);
            }
        
            $category = new AdsCategories();
            $category->setName($data['name']);
            $category->setDescription($data['description']);
            $category->setModifyDate(new \DateTime('@'.strtotime('now')));

            $entityManager->persist($category);
            $entityManager->flush();

            return $this->redirectToRoute('manageCategories');
        }

        return $this->render('main/addCategory.html.twig', [
            'currentUser' => $currentUser,
        ]); 
    }

    /**
     * @Route("/deleteCategory/{id}", name="deleteCategory")
     */
    public function deleteCategory($id, SessionInterface $session)
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
        $category = $entityManager->getRepository(AdsCategories::class)->find($id); 
        $findAllCategories = $entityManager->getRepository(AdsCategories::class)->findBy([],['id'=>'ASC']);
        $findAllAds = $entityManager->getRepository(Ads::class)->findBy([],['id'=>'DESC']);

        foreach($findAllAds as $findAd) // check if category to delete is not used somewhere
        {
            if($findAd->getCategoryId()->getName() == $category->getName())
            {
                dump("Kategoria jest używana i nie może zostać usunięta!");
                sleep(2);
                return $this->redirectToRoute('manageCategories');
            }
        }
        
        $entityManager->remove($category);
        $entityManager->flush();
        
        return $this->redirectToRoute('manageCategories');    
    }

    /**
     * @Route("/editCategory/{id}", name="editCategory")
     */
    public function editCategory($id, Request $request,SessionInterface $session)
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
        $category = $entityManager->getRepository(AdsCategories::class)->find($id); 

        if(isset($data['edit']))
        {
            $checkName = $entityManager->getRepository(AdsCategories::class)->findOneBy(array('name' => $data['name']));

            if($checkName && $checkName != $category)
            {
                $errorName = TRUE;
                return $this->render('main/editCategory.html.twig', [
                    'currentUser' => $currentUser,
                    'category' => $category,
                    'errorName' => $errorName,
                ]);
            }

            $category->setName($data['name']);
            $category->setDescription($data['description']);
            $category->setModifyDate(new \DateTime('@'.strtotime('now')));

            $entityManager->persist($category);
            $entityManager->flush();
            return $this->redirectToRoute('manageCategories');
        }

        return $this->render('main/editCategory.html.twig', [
            'currentUser' => $currentUser,
            'category' => $category,

        ]);
    }

    /**
     * @Route("/manageAds", name="manageAds")
     */
    public function manageAds(Request $request, SessionInterface $session)
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

        $findUsers = $entityManager->getRepository(Users::class)->findBy([], ['id' => 'ASC']);
        $findUsersByCity = $entityManager->getRepository(Users::class)->findBy([], ['city' => 'ASC']);
        $findCategories = $entityManager->getRepository(AdsCategories::class)->findBy([],['name'=>'ASC']);
        $cities = [];

        foreach($findUsersByCity as $userCity) // add only uniques cities
        {
            if($userCity->getUserType() != "admin")
            {
                if(!in_array($userCity->getCity(), $cities, true))
                {
                    array_push($cities, $userCity->getCity());
                }
            }
        }

        if(isset($data['search']))
        {
            $adName = $data['name'];
            $adsCity = $data['inputState'];
            $adsCategory = $data['category'];
            
            return $this->render('main/manageAds.html.twig', [
                'currentUser' => $currentUser,
                'findUsers' => $findUsers,
                'findCategories' => $findCategories,
                'cities' => $cities,
                'adName' => $adName,
            ]);
        }

        return $this->render('main/manageAds.html.twig', [
            'currentUser' => $currentUser,
            'findUsers' => $findUsers,
            'findCategories' => $findCategories,
            'cities' => $cities,
        ]); 
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

        $entityManager = $this->getDoctrine()->getManager();
        
        $userId = $currentUser->getId();
        $ads = $entityManager->getRepository(Ads::class)->findBy([],['modify_date'=>'DESC']);

        return $this->render('main/usersAds.html.twig', [
            'currentUser' => $currentUser,
            'ads' => $ads,
        ]); 
    }

    /**
     * @Route("/addAdvert", name="addAdvert")
     */
        public function addAdvert(Request $request, SessionInterface $session)
    {
        // check if user is logged
        if($session->get('currentUser'))
        {
            $currentUser = $session->get('currentUser');

            if($currentUser->getUserType() != "user")
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
        
        $userId = $currentUser->getId();
        $user = $entityManager->getRepository(Users::class)->find($userId);
        $categories = $entityManager->getRepository(AdsCategories::class)->findBy([],['id'=>'ASC']);

        $data = $request->request->all();

        if(isset($data['add']))
        {
            $category = $entityManager->getRepository(AdsCategories::class)->find($data['category']);
            $ads = new Ads();
            $ads->setTitle($data['title']);
            $ads->setCategoryId($category);
            $ads->setDescription($data['description']);
            $ads->setModifyDate(new \DateTime('@'.strtotime('now')));
            $ads->setUserId($user);

            if($request->files->get('upFile'))
            {
                $file = $request->files->get('upFile');
                $fileName = sha1(random_bytes(14)).".".$file->getClientOriginalExtension();

                $file->move(
                    $this->getParameter('files_directory'),
                    $fileName
                );

                $adPhoto = new AdsPhotos(); 
                $adPhoto->setFile($fileName);
                $adPhoto->setAd($ads);

                $entityManager->persist($adPhoto);
            }

            $entityManager->persist($ads);
            $entityManager->flush();

            return $this->redirectToRoute('usersAds');
        }
        

        return $this->render('main/addAdvert.html.twig', [
            'currentUser' => $currentUser,
            'user' => $user,
            'categories' => $categories,
        ]); 
    }

    /**
     * @Route("/editAdvert/{id}", name="editAdvert")
     */
    public function editAdvert($id, Request $request,SessionInterface $session)
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

        $entityManager = $this->getDoctrine()->getManager();
        $data = $request->request->all();
        $advert = $entityManager->getRepository(Ads::class)->find($id); 
        $categories = $entityManager->getRepository(AdsCategories::class)->findBy([],['id'=>'ASC']);
        $advertFiles = $entityManager->getRepository(AdsPhotos::class)->findBy(['ad' => $advert]);

        if(isset($data['edit']))
        {
            $advert->setTitle($data['title']);
            $advert->setDescription($data['description']);
            $advert->setModifyDate(new \DateTime('@'.strtotime('now')));

            if($request->files->get('upFile'))
            {
                $file = $request->files->get('upFile');
                $fileName = sha1(random_bytes(14)).".".$file->getClientOriginalExtension();

                $file->move(
                    $this->getParameter('files_directory'),
                    $fileName
                );

                $adPhoto = new AdsPhotos(); 
                $adPhoto->setFile($fileName);
                $adPhoto->setAd($advert);

                $entityManager->persist($adPhoto);
            }

            $entityManager->persist($advert);
            $entityManager->flush();

            return $this->redirectToRoute('usersAds');
        }

        if(isset($data['delete']))
        {
            foreach($advertFiles as $advertFile)
            {
                $entityManager->remove($advertFile);
            }

            $entityManager->flush();
        }

        return $this->render('main/editAdvert.html.twig', [
            'currentUser' => $currentUser,
            'advert' => $advert,
            'categories' => $categories,
            'advertFiles' => $advertFiles,

        ]);
    }

    /**
     * @Route("/deleteAdvert/{id}", name="deleteAdvert")
     */
    public function deleteAdvert($id, SessionInterface $session)
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

        $entityManager = $this->getDoctrine()->getManager();
        $advert = $entityManager->getRepository(Ads::class)->find($id);  
        $advertFiles = $entityManager->getRepository(AdsPhotos::class)->findBy(['ad' => $advert]);

        foreach($advertFiles as $advertFile)
        {
            $entityManager->remove($advertFile);
        }

        $entityManager->remove($advert);
        $entityManager->flush();
        
        return $this->redirectToRoute('usersAds');    
    }

}

