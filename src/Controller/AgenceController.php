<?php

namespace App\Controller;

use DateTime;
use DateInterval;
use App\Entity\Commande;
use App\Form\CommandePostType;
use App\Repository\MembreRepository;
use App\Repository\CommandeRepository;
use App\Repository\VehiculeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AgenceController extends AbstractController
{
    #[Route('/', name: 'app_agence')]
    public function index(VehiculeRepository $repo): Response
    {
        $vehicules = $repo->findAll();
    
        return $this->render('agence/index.html.twig', [
            'controller_name' => 'AgenceController',
            'vehicules' => $vehicules
        ]);
    }
    
    #[Route("/agence/show/{id}", name:"vehicule_show")]
    public function show($id, VehiculeRepository $repo, Request $globals, EntityManagerInterface $manager)
    {
        $vehicule = $repo->find($id);
                
        $commande = new Commande;
        $form=$this->createForm(CommandePostType::class, $commande);
        $form->handleRequest($globals);
        
        if($form->isSubmitted() && $form->isValid())
        {
            $table = $globals->request->get("commande_post");
            $tableOrigin = $table["date_heure_depart"]['date'];
            $origin = $tableOrigin["year"] . "-" . $tableOrigin["month"] . "-" . $tableOrigin["day"];
            $origin = date_create($origin);
            $tableTarget = $table["date_heure_fin"]['date'];
            $target = $tableTarget["year"] . "-" . $tableTarget["month"] . "-" . $tableTarget["day"];
            $target = date_create($target);
            $commande->setDateEnregistrement(new \DateTime);
            $interval = date_diff($origin, $target);;
            $prix = $vehicule->getPrixJournalier();
            // $interval = $interval->format('d');
            $interval = ($interval->d) + ($interval->m) *30 + ($interval->y) *364 ;
            $prix = $prix * $interval;
            $commande->setPrixTotal($prix);
            $commande->setIdVehicule($vehicule);
            $commande->setIdMembre($this->getUser());
            $manager->persist($commande);
            $manager->flush();
            $this->addFlash('success', "votre commande a bien été accepté");
            return $this->redirectToRoute('app_agence');
            // return $this->redirectToRoute('vehicule_show', [
            //     'id' => $vehicule->getId()
            // ]);        
        }
        return $this->renderForm('agence/show_vehicule.html.twig', [
            'item' => $vehicule,
            'formCommande' => $form,
        ]);
        }

        #[Route("/profil", name: "app_profil")]
        public function profil(CommandeRepository $repo, EntityManagerInterface $manager, Request $globals, VehiculeRepository $repoVehicule, MembreRepository $repoMembre, Commande $commande = null)
        {
            if($commande == null):
                $commande = new Commande;              
            endif;
            $commandes = $repo->findAll();
            
            $form = $this->createForm(CommandePostType::class, $commande);
            $form->handleRequest($globals);
    
    
            return $this->renderForm("agence/profil.html.twig",[
                'commandes' => $commandes,
                "formCommande" => $form,
            ]);
                    
        }
        
        #[Route("/profil/commande/delete/{id}", name: 'user_delete_commande')]
        public function deleteComande(Commande $commande, EntityManagerInterface $manager)
        {
            $manager->remove($commande);
            $manager->flush();
            $this->addFlash('success',"La commande a bien été supprimé");
            return $this->redirectToRoute("profil");
        }
    
    
}
