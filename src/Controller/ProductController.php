<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\Review;
use App\Entity\User;
use App\Form\ProductType;
use App\Form\RatingType;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class ProductController extends Controller
{
    /**
     * @Route("/admin/product", name="product_index", methods="GET")
     */
    public function index(ProductRepository $productRepository): Response
    {
        return $this->render('product/index.html.twig', ['products' => $productRepository->findAll()]);
    }

    /**
     * @Route("/product/new", name="product_new", methods="GET|POST")
     */
    public function new(Request $request): Response
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $product->getUploadedPic();
            $fileName = $this->generateUniqueFileName().'.'.$file->guessExtension();
            $file->move(
                $this->getParameter('products_images_directory'),
                $fileName
            );
            $product->setPicture($fileName);
            $em = $this->getDoctrine()->getManager();
            $product->setCreator($this->getUser());
            $this->getUser()->addProduct($product);
            $em->persist($product);
            $em->flush();

            return $this->redirectToRoute('product_index');
        }

        return $this->render('product/new.html.twig', [
            'product' => $product,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/product/{id}", name="product_show", methods="GET|POST")
     */
    public function show(Product $product, Request $request): Response
    {
        $form = $this->createForm(RatingType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $noteVal = $data['rating'];
            $seller = $product->getCreator();
            $customer = $this->getUser();
            if ($customer == $seller) {
                $this->addFlash('danger', 'Vous ne pouvez pas vous noter vous-même!');
                return $this->redirectToRoute('product_show', ['id' => $product->getId()]);
            }
            $em = $this->getDoctrine()->getManager();
            $review = $em->getRepository('App:Review')->findOneByCustomer($customer);
            if ($review == null) {
                $review = new Review();
                $review->setNote(intval($noteVal));
                $review->setCustomer($customer);
                $review->setSeller($seller);
                $em->persist($review);
                $this->addFlash('success', 'Merci pour votre note');
            }else {
                $this->addFlash('warning', 'Vous avez déjà noté ce vendeur');
            }
            $this->getDoctrine()->getManager()->flush();
            $this->averageAction($seller);

            return $this->redirectToRoute('product_show', ['id' => $product->getId()]);
        }
        return $this->render('product/show.html.twig', ['product' => $product, 'formNote' => $form->createView()]);
    }

    /**
     * @Route("/product/{id}/edit", name="product_edit", methods="GET|POST")
     */
    public function edit(Request $request, Product $product): Response
    {
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
                $file = $product->getUploadedPic();
                $fileName = $this->generateUniqueFileName().'.'.$file->guessExtension();
                $file->move(
                    $this->getParameter('products_images_directory'),
                    $fileName
                );
                $product->setPicture($fileName);
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('product_edit', ['id' => $product->getId()]);
        }

        return $this->render('product/edit.html.twig', [
            'product' => $product,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/product/{id}", name="product_delete", methods="DELETE")
     */
    public function delete(Request $request, Product $product): Response
    {
        if ($this->isCsrfTokenValid('delete'.$product->getId(), $request->request->get('_token'))) {
            $em = $this->getDoctrine()->getManager();
            $user = $product->getCreator();
            $user->removeProduct($product);
            $em->remove($product);
            $em->flush();
        }

        return $this->redirectToRoute('product_index');
    }

    /**
     * @return string
     */
    private function generateUniqueFileName()
    {
        return md5(uniqid());
    }

    public function averageAction(User $user)
    {
        $reviews = $user->getReviews();
        foreach ($reviews as $review) {
            $notes[] = $review->getNote();
        }
        $average = array_sum($notes) / count($notes);
        $user->setAverageNote($average);
        $this->getDoctrine()->getManager()->flush();
    }
}
