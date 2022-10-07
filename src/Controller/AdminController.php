<?php

namespace App\Controller;

use App\Entity\Membre;
use App\Entity\Commande;
use App\Entity\Vehicule;
use App\Form\MembreType;
use App\Form\VehiculeType;
use App\Form\CommandePostType;
use App\Form\CommandePostAdminType;
use App\Repository\MembreRepository;
use App\Repository\CommandeRepository;
use App\Repository\VehiculeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AdminController extends AbstractController
{
    #[Route('/admin', name: 'app_admin')]
    public function index(): Response
    {
        return $this->render('admin/index.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }

    #[Route("/admin/vehicules", name:"admin_vehicules")]
    #[Route("/admin/vehicule/edit{id}", name:"admin_edit_vehicule")]
    public function adminVehicules(VehiculeRepository $repo, EntityManagerInterface $manager, Request $globals, Vehicule $vehicule = null): Response
    {
        $colonnes = $manager->getClassMetadata(Vehicule::class)->getFieldNames();

        $vehicules=$repo->findAll();
        // return $this->render("admin/admin_vehicules.html.twig",[
        //     'vehicules' => $vehicules,
        //     'collones' => $colonnes
        // ]);

        if($vehicule ==null):
            $vehicule = new Vehicule;
            $vehicule->setDateEnregistrement(new \DateTime);
        endif;

        $form= $this->createForm(VehiculeType::class, $vehicule );
        $form->handleRequest($globals);

        if($form->isSubmitted() && $form->isValid())
        {
            $manager->persist($vehicule);
            $manager->flush();

            $this->addFlash('success',"L'opération s'est bien déroulé");
            //addFlash() permet de créer un message qui sera affiché une fois à l'utilisateur
            // arg 1 : type du message (peut être tout ce qu'on veut)
            // arg 2 : contenu du message

            return $this->redirectToRoute('admin_vehicules');
        }

        return $this->renderForm("admin/admin_vehicules.html.twig",[
            'vehicules' => $vehicules,
            'colonnes' => $colonnes,
            "formVehicule" => $form,
            "editMode" => $vehicule->getId() !== null
        ]);


    }

    #[Route("/admin/vehicule/delete/{id}", name: 'admin_delete_vehicule')]
    public function deleteVehicule(Vehicule $vehicule, EntityManagerInterface $manager)
    {
        $manager->remove($vehicule);
        $manager->flush();
        $this->addFlash('success',"Le vehicule a bien été supprimé");
        return $this->redirectToRoute("admin_vehicules");
    }


    #[Route("/admin/membres", name:"admin_membres")]
    #[Route("/admin/membre/edit{id}", name:"admin_edit_membre")]
    public function adminMembres(MembreRepository $repo, EntityManagerInterface $manager, Request $globals, UserPasswordHasherInterface $membrePasswordHasher, Membre $membre = null): Response
    {
        $colonnes = $manager->getClassMetadata(Membre::class)->getFieldNames();
        
        $membres=$repo->findAll();
        // return $this->render("admin/admin_membres.html.twig",[
        //     'membres' => $membres,
        //     'collones' => $colonnes
        // ]);
        
        if($membre ==null):
            $membre = new Membre;
        endif;
        
        $form= $this->createForm(MembreType::class, $membre );
        $form->handleRequest($globals);
        
        if($form->isSubmitted() && $form->isValid())
        {
            $membre->setDateEnregistrement(new \DateTime);
            $membre->setPassword(
                $membrePasswordHasher->hashPassword(
                    $membre,
                    $form->get('plainPassword')->getData()
                )
            );

            $manager->persist($membre);
            $manager->flush();
            
            $this->addFlash('success',"L'opération s'est bien déroulé");
            //addFlash() permet de créer un message qui sera affiché une fois à l'utilisateur
            // arg 1 : type du message (peut être tout ce qu'on veut)
            // arg 2 : contenu du message
            
            return $this->redirectToRoute('admin_membres');
        }
        
        return $this->renderForm("admin/admin_membres.html.twig",[
            'membres' => $membres,
            'colonnes' => $colonnes,
            "formMembre" => $form,
            "editMode" => $membre->getId() !== null
        ]);
        
        
    }
    
    #[Route("/admin/membre/delete/{id}", name: 'admin_delete_membre')]
    public function deleteMembre(Membre $membre, EntityManagerInterface $manager)
    {
        $manager->remove($membre);
        $manager->flush();
        $this->addFlash('success',"Le membre a bien été supprimé");
        return $this->redirectToRoute("admin_membres");
    }

    #[Route("/membre/show/{id}", name:"membre_show")]
    public function showMembre($id, MembreRepository $repo,)
    {
        $membre = $repo->find($id);
        
        return $this->renderForm('agence/show_membre.html.twig', [
            'membre' => $membre,
        ]);
    }
    
    #[Route("/admin/commandes", name: "admin_commandes")]
    #[Route("/admin/commande/edit{id}", name:"admin_edit_commande")]
    public function adminCommandes(CommandeRepository $repo, EntityManagerInterface $manager, Request $globals, VehiculeRepository $repoVehicule, MembreRepository $repoMembre, Commande $commande = null)
    {
        $colonnes = $manager->getClassMetadata(Commande::class)->getFieldNames();

        if($commande == null):
            $commande = new Commande;
            
        endif;
        $commandes = $repo->findAll();
        $membres = $repoMembre->findAll();
        $vehicules = $repoVehicule->findAll();
        
        $form = $this->createForm(CommandePostAdminType::class, $commande);
        $form->handleRequest($globals);
        
        if($form->isSubmitted() && $form->isValid())
        {
            $commande->setDateEnregistrement(new \DateTime);
            $manager->persist($commande);
            $manager->flush();
            
            $this->addFlash('success',"L'opération s'est bien déroulé");            
            return $this->redirectToRoute('admin_commandes');
        }


        return $this->renderForm("admin/admin_commandes.html.twig",[
            'commandes' => $commandes,
            'colonnes' => $colonnes,
            "formCommande" => $form,
            "editMode" => $commande->getId() !== null
            
        ]);

    }
    
    #[Route("/admin/commande/delete/{id}", name: 'admin_delete_commande')]
    public function deleteComande(Commande $commande, EntityManagerInterface $manager)
    {
        $manager->remove($commande);
        $manager->flush();
        $this->addFlash('success',"La commande a bien été supprimé");
        return $this->redirectToRoute("admin_commandes");
    }

    #[Route("/commande/show/{id}", name:"commande_show")]
    public function showCommande($id, CommandeRepository $repo,)
    {
        $commande = $repo->find($id);
        
        return $this->renderForm('agence/show_commande.html.twig', [
            'commande' => $commande,
        ]);
    }

}
