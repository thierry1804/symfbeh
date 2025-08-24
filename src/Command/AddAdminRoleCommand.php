<?php

namespace App\Command;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:add-admin-role',
    description: 'Ajoute le rôle ROLE_ADMIN à un utilisateur existant',
)]
class AddAdminRoleCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserRepository $userRepository
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'Email de l\'utilisateur à promouvoir admin')
            ->setHelp('Cette commande permet d\'ajouter le rôle ROLE_ADMIN à un utilisateur existant.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $email = $input->getArgument('email');

        // Rechercher l'utilisateur par email
        $user = $this->userRepository->findOneBy(['email' => $email]);

        if (!$user) {
            $io->error(sprintf('Aucun utilisateur trouvé avec l\'email : %s', $email));
            return Command::FAILURE;
        }

        // Vérifier si l'utilisateur a déjà le rôle admin
        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            $io->warning(sprintf('L\'utilisateur %s a déjà le rôle ROLE_ADMIN', $email));
            return Command::SUCCESS;
        }

        // Ajouter le rôle ROLE_ADMIN
        $roles = $user->getRoles();
        $roles[] = 'ROLE_ADMIN';
        $user->setRoles($roles);

        // Sauvegarder les modifications
        $this->entityManager->flush();

        $io->success(sprintf(
            'Le rôle ROLE_ADMIN a été ajouté avec succès à l\'utilisateur %s (%s)',
            $user->getFullName(),
            $email
        ));

        return Command::SUCCESS;
    }
}
