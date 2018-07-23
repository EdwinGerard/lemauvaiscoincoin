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
        $form = $this->createForm(ResearchType::class, null, array(
            'action' => $this->generateUrl('search_result', ['page' => 1])
        ));
        $form->handleRequest($request);

        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
            'form' => $form->createView()
        ]);
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     * @Route("/search/{page}", requirements={"page" = "\d+"}, name="search_result")
     */
    public function searchAction(Request $request, ProductRepository $productRepo, $page)
    {
        $form = $this->createForm(ResearchType::class, null, array(
            'action' => $this->generateUrl('search_result', ['page' => 1])
        ));
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $product = $data['search'];
            if ($data['filter'] == 'category') {
                $filter = $data['categories']->getId();
                $filterName = 'category';
            } elseif ($data['filter'] == 'department') {
                $filter = $data['departements']->getId();
                $filterName = 'department';
            } else {
                $filter = '';
                $filterName = '';
            }
            $nbProductsParPage = 9;
            $products = $productRepo->findAllPagination($page, $nbProductsParPage);
            $pagination = array(
                'page' => $page,
                'nbPages' => ceil(count($products) / $nbProductsParPage),
                'nomRoute' => 'search_result',
                'paramsRoute' => array()
            );
            $products = $productRepo->findByLike($product, $filterName, $filter, $page, $nbProductsParPage);
            return $this->render('home/search.html.twig', ['products' => $products, 'pagination' => $pagination]);
        }
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
