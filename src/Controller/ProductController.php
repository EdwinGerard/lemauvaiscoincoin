<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\Review;
use App\Entity\User;
use App\Form\CommentType;
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
     * @param ProductRepository $productRepository
     * @return Response
     * @Route("/products/{page}", requirements={"page" = "\d+"}, name="product_list")
     */
    public function listVisiteur(ProductRepository $productRepository, $page): Response
    {
        $nbProductsParPage = 9;
        $products = $productRepository->findAllPagination($page, $nbProductsParPage);
        $pagination = array(
            'page' => $page,
            'nbPages' => ceil(count($products) / $nbProductsParPage),
            'nomRoute' => 'product_list',
            'paramsRoute' => array()
        );

        return $this->render('product/list.html.twig', ['products' => $products, 'pagination' => $pagination]);
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
        $commentForm = $this->createForm(CommentType::class);
        $commentForm->handleRequest($request);
        $form->handleRequest($request);
        $em = $this->getDoctrine()->getManager();
        $seller = $product->getCreator();
        $customer = $this->getUser();
        $hasVoted = true;
        $hasCommented = true;
        $reviews = $em->getRepository('App:Review')->findByProduct($product);
        if ($reviews == null) {
            $hasVoted = false;
            $hasCommented = false;
        }else {
            foreach ($reviews as $rev) {
                if ($rev->getCustomer()->getId() !== $customer->getId()) {
                    $hasVoted = false;
                    $hasCommented = false;
                }elseif ($rev->getOpinion() == null) {
                    $hasCommented = false;
                }
            }
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $noteVal = $data['rating'];
            if ($customer == $seller) {
                $this->addFlash('danger', 'Vous ne pouvez pas vous noter vous-même!');
                return $this->redirectToRoute('product_show', [
                    'id' => $product->getId(),
                    'hasCommented' => $hasCommented,
                    'hasVoted' => $hasVoted
                ]);
            }
            $reviews = $em->getRepository('App:Review')->findByProduct($product);
            if ($reviews == null) {
                $review = new Review();
                $review->setNote(intval($noteVal));
                $review->setCustomer($customer);
                $review->setSeller($seller);
                $review->setProduct($product);
                $em->persist($review);
                $hasCommented = false;
                $this->addFlash('success', 'Merci pour votre note');
            } else {
                foreach ($reviews as $review) {
                    if ($review->getCustomer()->getId() == $this->getUser()->getId()) {
                        $this->addFlash('warning', 'Vous avez déjà noté ce vendeur');
                        break;
                    }else {
                        $review = new Review();
                        $review->setNote(intval($noteVal));
                        $review->setCustomer($customer);
                        $review->setSeller($seller);
                        $review->setProduct($product);
                        $em->persist($review);
                        $hasCommented = false;
                        $this->addFlash('success', 'Merci pour votre note');
                    }
                }
            }
            $this->getDoctrine()->getManager()->flush();
            $this->averageAction($seller);

            return $this->redirectToRoute('product_show', ['id' => $product->getId(), 'hasCommented' => $hasCommented]);
        }

        if ($commentForm->isSubmitted() && $commentForm->isValid()) {
            $data = $commentForm->getData();
            $comment = $data['comment'];
            if ($customer == $seller) {
                $this->addFlash('danger', 'Vous ne pouvez pas vous commenter vous-même!');
                return $this->redirectToRoute('product_show', ['id' => $product->getId()]);
            }
            $reviews = $em->getRepository('App:Review')->findByProduct($product);
            if ($reviews == null) {
                $this->addFlash('warning', 'Vous devez noter ce vendeur avant de commenter');
                $hasCommented = false;
            } else {
                foreach ($reviews as $review) {
                    if ($review->getCustomer()->getId() == $this->getUser()->getId() && $review->getOpinion() !== null) {
                        $this->addFlash('warning', 'Vous avez déjà commenté ce produit');
                        break;
                    }else {
                        $review->setOpinion($comment);
                        $this->addFlash('success', 'Merci pour votre commentaire');
                        break;
                    }
                }

            }
            $this->getDoctrine()->getManager()->flush();
            return $this->redirectToRoute('product_show', ['id' => $product->getId(), 'hasCommented' => $hasCommented]);
        }
        return $this->render('product/show.html.twig', [
            'product' => $product,
            'hasVoted' => $hasVoted,
            'hasCommented' => $hasCommented,
            'formNote' => $form->createView(),
            'formComment' => $commentForm->createView()
        ]);
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
