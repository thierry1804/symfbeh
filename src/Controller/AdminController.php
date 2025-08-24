<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserEditFormType;
use App\Form\UserCreateFormType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin')]
#[IsGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{
    #[Route('/', name: 'app_admin_dashboard', methods: ['GET'])]
    public function dashboard(UserRepository $userRepository): Response
    {
        $totalUsers = $userRepository->count([]);
        $activeUsers = $userRepository->count(['isActive' => true]);
        $verifiedUsers = $userRepository->count(['isVerified' => true]);
        $recentUsers = $userRepository->findBy([], ['createdAt' => 'DESC'], 5);

        return $this->render('admin/dashboard.html.twig', [
            'totalUsers' => $totalUsers,
            'activeUsers' => $activeUsers,
            'verifiedUsers' => $verifiedUsers,
            'recentUsers' => $recentUsers,
        ]);
    }

    #[Route('/users', name: 'app_admin_users', methods: ['GET'])]
    public function users(UserRepository $userRepository, Request $request): Response
    {
        $page = $request->query->getInt('page', 1);
        $limit = 20;
        $offset = ($page - 1) * $limit;

        $users = $userRepository->findBy([], ['createdAt' => 'DESC'], $limit, $offset);
        $totalUsers = $userRepository->count([]);
        $totalPages = ceil($totalUsers / $limit);

        return $this->render('admin/users/index.html.twig', [
            'users' => $users,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalUsers' => $totalUsers,
        ]);
    }

    #[Route('/users/new', name: 'app_admin_user_new', methods: ['GET', 'POST'])]
    public function newUser(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = new User();
        $form = $this->createForm(UserCreateFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Hasher le mot de passe
            $plainPassword = $form->get('plainPassword')->getData();
            $user->setPassword($passwordHasher->hashPassword($user, $plainPassword));

            // Définir les valeurs par défaut
            $user->setCreatedAt(new \DateTime());
            $user->setIsActive(true);
            $user->setIsVerified(false);

            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'Utilisateur créé avec succès.');

            return $this->redirectToRoute('app_admin_user_show', ['id' => $user->getId()]);
        }

        return $this->render('admin/users/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/users/{id}', name: 'app_admin_user_show', methods: ['GET'])]
    public function showUser(User $user): Response
    {
        return $this->render('admin/users/show.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/users/{id}/edit', name: 'app_admin_user_edit', methods: ['GET', 'POST'])]
    public function editUser(Request $request, User $user, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        $form = $this->createForm(UserEditFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gérer le changement de mot de passe si fourni
            $plainPassword = $form->get('plainPassword')->getData();
            if ($plainPassword) {
                $user->setPassword($passwordHasher->hashPassword($user, $plainPassword));
            }

            $entityManager->flush();

            $this->addFlash('success', 'Utilisateur mis à jour avec succès.');

            return $this->redirectToRoute('app_admin_user_show', ['id' => $user->getId()]);
        }

        return $this->render('admin/users/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/users/{id}/toggle-status', name: 'app_admin_user_toggle_status', methods: ['POST'])]
    public function toggleUserStatus(User $user, EntityManagerInterface $entityManager): Response
    {
        $user->setIsActive(!$user->isActive());
        $entityManager->flush();

        $status = $user->isActive() ? 'activé' : 'désactivé';
        $this->addFlash('success', "Utilisateur {$status} avec succès.");

        return $this->redirectToRoute('app_admin_users');
    }

    #[Route('/users/{id}/toggle-verification', name: 'app_admin_user_toggle_verification', methods: ['POST'])]
    public function toggleUserVerification(User $user, EntityManagerInterface $entityManager): Response
    {
        $user->setIsVerified(!$user->isVerified());
        $entityManager->flush();

        $status = $user->isVerified() ? 'vérifié' : 'non vérifié';
        $this->addFlash('success', "Utilisateur marqué comme {$status}.");

        return $this->redirectToRoute('app_admin_users');
    }

    #[Route('/users/{id}/delete', name: 'app_admin_user_delete', methods: ['POST'])]
    public function deleteUser(User $user, EntityManagerInterface $entityManager): Response
    {
        // Empêcher la suppression de l'utilisateur connecté
        if ($user === $this->getUser()) {
            $this->addFlash('error', 'Vous ne pouvez pas supprimer votre propre compte.');
            return $this->redirectToRoute('app_admin_users');
        }

        $entityManager->remove($user);
        $entityManager->flush();

        $this->addFlash('success', 'Utilisateur supprimé avec succès.');

        return $this->redirectToRoute('app_admin_users');
    }
}
