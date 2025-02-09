<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\TruncateMode;
use function Symfony\Component\String\u;

final class IndexController extends AbstractController
{
    #[Route('/', name: 'app_index')]
    public function index(ProductRepository $productRepository): Response
    {
        $products = $productRepository->findAll();

        foreach ($products as $product) {
            $name = u($product->getName())->truncate(10, '...', cut: TruncateMode::WordBefore);
            $product->setName($name->toString());
            $description = u($product->getDescription())->truncate(40, '...', cut: TruncateMode::WordAfter);
            $product->setDescription($description->toString());
        }
        return $this->render('index/index.html.twig', [
            'products' => $products,
        ]);
    }

    #[Route('product/{id}', name: 'app_index_show')]
    public function show(ProductRepository $productRepository, string $id): Response
    {
        $product = $productRepository->findOneBy(['id' => $id]);

        if (!$product) {
            throw $this->createNotFoundException('Le produit n\'existe pas');
        }

        return $this->render('index/show.html.twig', [
            'product' => $product,
        ]);
    }

}
