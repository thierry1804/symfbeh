<?php

namespace App\Controller;

use App\Entity\Customer;
use App\Entity\User;
use App\Form\CustomerFormType;
use App\Repository\CustomerRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/customers')]
#[IsGranted('ROLE_ADMIN')]
class CustomerController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private CustomerRepository $customerRepository,
        private UserRepository $userRepository
    ) {}

    #[Route('/', name: 'app_customer_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $page = $request->query->getInt('page', 1);
        $limit = 20;
        $offset = ($page - 1) * $limit;

        // Filtres
        $filters = [
            'search' => $request->query->get('search'),
            'status' => $request->query->get('status'),
            'source' => $request->query->get('source'),
            'assignedTo' => $request->query->get('assignedTo'),
            'isActive' => $request->query->get('isActive', true),
            'city' => $request->query->get('city'),
            'country' => $request->query->get('country'),
        ];

        // Nettoyer les filtres vides
        $filters = array_filter($filters, fn($value) => $value !== null && $value !== '');

        $customers = $this->customerRepository->findByFilters(
            $filters,
            ['createdAt' => 'DESC'],
            $limit,
            $offset
        );

        // Statistiques
        $statistics = $this->customerRepository->getStatusStatistics();
        $sourceStats = $this->customerRepository->getSourceStatistics();

        // Utilisateurs pour le filtre
        $users = $this->userRepository->findAll();

        return $this->render('admin/customers/index.html.twig', [
            'customers' => $customers,
            'statistics' => $statistics,
            'sourceStats' => $sourceStats,
            'users' => $users,
            'filters' => $filters,
            'currentPage' => $page,
            'totalPages' => ceil(count($this->customerRepository->findByFilters($filters)) / $limit),
        ]);
    }

    #[Route('/new', name: 'app_customer_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $customer = new Customer();
        $form = $this->createForm(CustomerFormType::class, $customer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($customer);
            $this->entityManager->flush();

            $this->addFlash('success', 'Le client a été créé avec succès.');

            return $this->redirectToRoute('app_customer_show', ['id' => $customer->getId()]);
        }

        return $this->render('admin/customers/new.html.twig', [
            'customer' => $customer,
            'form' => $form,
        ]);
    }

    #[Route('/dashboard', name: 'app_customer_dashboard', methods: ['GET'])]
    public function dashboard(): Response
    {
        $statistics = $this->customerRepository->getStatusStatistics();
        $sourceStats = $this->customerRepository->getSourceStatistics();
        $recentCustomers = $this->customerRepository->findRecentCustomers(7);
        $vipCustomers = $this->customerRepository->findVipCustomers();
        $customersNeedingFollowUp = $this->customerRepository->findCustomersNeedingFollowUp();
        $unassignedCustomers = $this->customerRepository->findUnassignedCustomers();

        return $this->render('admin/customers/dashboard.html.twig', [
            'statistics' => $statistics,
            'sourceStats' => $sourceStats,
            'recentCustomers' => $recentCustomers,
            'vipCustomers' => $vipCustomers,
            'customersNeedingFollowUp' => $customersNeedingFollowUp,
            'unassignedCustomers' => $unassignedCustomers,
        ]);
    }

    #[Route('/export', name: 'app_customer_export', methods: ['GET'])]
    public function export(Request $request): Response
    {
        $filters = [
            'search' => $request->query->get('search'),
            'status' => $request->query->get('status'),
            'source' => $request->query->get('source'),
            'assignedTo' => $request->query->get('assignedTo'),
            'isActive' => $request->query->get('isActive', true),
        ];

        $filters = array_filter($filters, fn($value) => $value !== null && $value !== '');
        $customers = $this->customerRepository->findByFilters($filters);

        $response = new Response();
        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="customers_' . date('Y-m-d') . '.csv"');

        $handle = fopen('php://temp', 'r+');
        
        // En-têtes CSV
        fputcsv($handle, [
            'ID', 'Prénom', 'Nom', 'Email', 'Téléphone', 'Mobile', 'Entreprise',
            'Poste', 'Adresse', 'Code Postal', 'Ville', 'Pays', 'Statut',
            'Source', 'Revenu Annuel', 'Nombre Employés', 'Assigné à',
            'Date de création', 'Dernier contact', 'Prochain suivi', 'Tags'
        ], ';');

        // Données
        foreach ($customers as $customer) {
            fputcsv($handle, [
                $customer->getId(),
                $customer->getFirstName(),
                $customer->getLastName(),
                $customer->getEmail(),
                $customer->getPhone(),
                $customer->getMobile(),
                $customer->getCompany(),
                $customer->getJobTitle(),
                $customer->getAddress(),
                $customer->getPostalCode(),
                $customer->getCity(),
                $customer->getCountry(),
                $customer->getStatusLabel(),
                $customer->getSource(),
                $customer->getAnnualRevenue(),
                $customer->getEmployeeCount(),
                $customer->getAssignedTo() ? $customer->getAssignedTo()->getFullName() : '',
                $customer->getCreatedAt() ? $customer->getCreatedAt()->format('d/m/Y H:i') : '',
                $customer->getLastContactAt() ? $customer->getLastContactAt()->format('d/m/Y H:i') : '',
                $customer->getNextFollowUpAt() ? $customer->getNextFollowUpAt()->format('d/m/Y H:i') : '',
                implode(', ', $customer->getTagsArray()),
            ], ';');
        }

        rewind($handle);
        $content = stream_get_contents($handle);
        fclose($handle);

        $response->setContent($content);

        return $response;
    }

    #[Route('/bulk-assign', name: 'app_customer_bulk_assign', methods: ['POST'])]
    public function bulkAssign(Request $request): Response
    {
        $customerIds = $request->request->get('customerIds', []);
        $userId = $request->request->get('assignedTo');

        if (!empty($customerIds) && $userId) {
            $user = $this->userRepository->find($userId);
            $customers = $this->customerRepository->findBy(['id' => $customerIds]);

            foreach ($customers as $customer) {
                $customer->setAssignedTo($user);
            }

            $this->entityManager->flush();
            $this->addFlash('success', count($customers) . ' clients ont été assignés avec succès.');
        }

        return $this->redirectToRoute('app_customer_index');
    }

    #[Route('/bulk-status', name: 'app_customer_bulk_status', methods: ['POST'])]
    public function bulkStatus(Request $request): Response
    {
        $customerIds = $request->request->get('customerIds', []);
        $status = $request->request->get('status');

        if (!empty($customerIds) && $status) {
            $customers = $this->customerRepository->findBy(['id' => $customerIds]);

            foreach ($customers as $customer) {
                $customer->setStatus($status);
            }

            $this->entityManager->flush();
            $this->addFlash('success', 'Le statut de ' . count($customers) . ' clients a été mis à jour.');
        }

        return $this->redirectToRoute('app_customer_index');
    }

    #[Route('/{id}', name: 'app_customer_show', methods: ['GET'])]
    public function show(Customer $customer): Response
    {
        return $this->render('admin/customers/show.html.twig', [
            'customer' => $customer,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_customer_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Customer $customer): Response
    {
        $form = $this->createForm(CustomerFormType::class, $customer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            $this->addFlash('success', 'Le client a été modifié avec succès.');

            return $this->redirectToRoute('app_customer_show', ['id' => $customer->getId()]);
        }

        return $this->render('admin/customers/edit.html.twig', [
            'customer' => $customer,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_customer_delete', methods: ['POST'])]
    public function delete(Request $request, Customer $customer): Response
    {
        if ($this->isCsrfTokenValid('delete'.$customer->getId(), $request->request->get('_token'))) {
            $customer->setIsActive(false);
            $this->entityManager->flush();

            $this->addFlash('success', 'Le client a été désactivé avec succès.');
        }

        return $this->redirectToRoute('app_customer_index');
    }

    #[Route('/{id}/activate', name: 'app_customer_activate', methods: ['POST'])]
    public function activate(Request $request, Customer $customer): Response
    {
        if ($this->isCsrfTokenValid('activate'.$customer->getId(), $request->request->get('_token'))) {
            $customer->setIsActive(true);
            $this->entityManager->flush();

            $this->addFlash('success', 'Le client a été activé avec succès.');
        }

        return $this->redirectToRoute('app_customer_show', ['id' => $customer->getId()]);
    }

    #[Route('/{id}/update-status', name: 'app_customer_update_status', methods: ['POST'])]
    public function updateStatus(Request $request, Customer $customer): Response
    {
        if ($this->isCsrfTokenValid('update_status'.$customer->getId(), $request->request->get('_token'))) {
            $status = $request->request->get('status');
            $customer->setStatus($status);
            $this->entityManager->flush();

            $this->addFlash('success', 'Le statut du client a été mis à jour.');
        }

        return $this->redirectToRoute('app_customer_show', ['id' => $customer->getId()]);
    }

    #[Route('/{id}/assign', name: 'app_customer_assign', methods: ['POST'])]
    public function assign(Request $request, Customer $customer): Response
    {
        if ($this->isCsrfTokenValid('assign'.$customer->getId(), $request->request->get('_token'))) {
            $userId = $request->request->get('assignedTo');
            $user = $userId ? $this->userRepository->find($userId) : null;
            
            $customer->setAssignedTo($user);
            $this->entityManager->flush();

            $this->addFlash('success', 'Le client a été assigné avec succès.');
        }

        return $this->redirectToRoute('app_customer_show', ['id' => $customer->getId()]);
    }

    #[Route('/{id}/schedule-follow-up', name: 'app_customer_schedule_follow_up', methods: ['POST'])]
    public function scheduleFollowUp(Request $request, Customer $customer): Response
    {
        if ($this->isCsrfTokenValid('schedule_follow_up'.$customer->getId(), $request->request->get('_token'))) {
            $date = $request->request->get('nextFollowUpAt');
            if ($date) {
                $customer->setNextFollowUpAt(new \DateTime($date));
                $customer->setLastContactAt(new \DateTime());
                $this->entityManager->flush();

                $this->addFlash('success', 'Le suivi a été programmé avec succès.');
            }
        }

        return $this->redirectToRoute('app_customer_show', ['id' => $customer->getId()]);
    }
}
