<?php

namespace App\Form;

use App\Entity\Customer;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CustomerFormType extends AbstractType
{
    public function __construct(
        private UserRepository $userRepository
    ) {}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstName', TextType::class, [
                'label' => 'Prénom *',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Entrez le prénom'
                ]
            ])
            ->add('lastName', TextType::class, [
                'label' => 'Nom de famille *',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Entrez le nom de famille'
                ]
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email *',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'exemple@email.com'
                ]
            ])
            ->add('phone', TextType::class, [
                'label' => 'Téléphone',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => '01 23 45 67 89'
                ]
            ])
            ->add('mobile', TextType::class, [
                'label' => 'Mobile',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => '06 12 34 56 78'
                ]
            ])
            ->add('company', TextType::class, [
                'label' => 'Entreprise',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Nom de l\'entreprise'
                ]
            ])
            ->add('jobTitle', TextType::class, [
                'label' => 'Poste',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Directeur, Manager, etc.'
                ]
            ])
            ->add('address', TextType::class, [
                'label' => 'Adresse',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => '123 Rue de la Paix'
                ]
            ])
            ->add('postalCode', TextType::class, [
                'label' => 'Code postal',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => '75001'
                ]
            ])
            ->add('city', TextType::class, [
                'label' => 'Ville',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Paris'
                ]
            ])
            ->add('country', TextType::class, [
                'label' => 'Pays',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'France'
                ]
            ])
            ->add('website', UrlType::class, [
                'label' => 'Site web',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'https://www.exemple.com'
                ]
            ])
            ->add('linkedin', UrlType::class, [
                'label' => 'LinkedIn',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'https://linkedin.com/in/profil'
                ]
            ])
            ->add('twitter', UrlType::class, [
                'label' => 'Twitter',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'https://twitter.com/utilisateur'
                ]
            ])
            ->add('status', ChoiceType::class, [
                'label' => 'Statut *',
                'choices' => [
                    'Prospect' => 'prospect',
                    'Client' => 'client',
                    'VIP' => 'vip',
                    'Inactif' => 'inactif'
                ],
                'attr' => [
                    'class' => 'form-select'
                ]
            ])
            ->add('source', ChoiceType::class, [
                'label' => 'Source',
                'required' => false,
                'choices' => [
                    'Site web' => 'site_web',
                    'Recommandation' => 'recommandation',
                    'Salon/Événement' => 'salon',
                    'Réseaux sociaux' => 'reseaux_sociaux',
                    'Publicité' => 'publicite',
                    'Email marketing' => 'email_marketing',
                    'Téléphone' => 'telephone',
                    'Autre' => 'autre'
                ],
                'attr' => [
                    'class' => 'form-select'
                ]
            ])
            ->add('annualRevenue', MoneyType::class, [
                'label' => 'Revenu annuel',
                'required' => false,
                'currency' => 'EUR',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => '100000'
                ]
            ])
            ->add('employeeCount', IntegerType::class, [
                'label' => 'Nombre d\'employés',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => '50',
                    'min' => 1
                ]
            ])
            ->add('assignedTo', EntityType::class, [
                'label' => 'Assigné à',
                'class' => User::class,
                'choice_label' => 'fullName',
                'required' => false,
                'placeholder' => 'Sélectionnez un utilisateur',
                'query_builder' => function () {
                    return $this->userRepository->createQueryBuilder('u')
                        ->where('u.isActive = :active')
                        ->setParameter('active', true)
                        ->orderBy('u.firstName', 'ASC');
                },
                'attr' => [
                    'class' => 'form-select'
                ]
            ])
            ->add('lastContactAt', DateType::class, [
                'label' => 'Dernier contact',
                'required' => false,
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('nextFollowUpAt', DateType::class, [
                'label' => 'Prochain suivi',
                'required' => false,
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('notes', TextareaType::class, [
                'label' => 'Notes',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 4,
                    'placeholder' => 'Notes importantes sur ce client...'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Customer::class,
        ]);
    }
}
