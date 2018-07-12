<?php

namespace App\Controller;

use App\Form\ResearchType;
use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class HomeController extends Controller
{
    /**
     * @Route("/", name="home")
     */
    public function index(Request $request, ProductRepository $productRepo)
    {
        $form = $this->createForm(ResearchType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $product = $data['search'];
            if ($data['filter'] == 'category') {
                $filter = $data['categories']->getId();
                $filterName = 'category';
            }elseif ($data['filter'] == 'department') {
                $filter = $data['departements']->getId();
                $filterName = 'department';
            }else {
                $filter = '';
                $filterName = '';
            }
            $products = $productRepo->findByLike($product, $filterName, $filter);
            return $this->redirectToRoute('search_result', ['products' => $products]);
        }
        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
            'form' => $form->createView()
        ]);
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     * @Route("/search", name="search_result")
     */
    public function searchAction()
    {
        return $this->render('home/search.html.twig');
    }

    /**
     * @Route("/legal", name="legal")
     */
    public function legal()
    {
        return $this->render('home/legal.html.twig', ['controller_name' => 'HomeController']);
    }
}
