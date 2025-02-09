<?php

namespace App\Twig\Components;

use App\Repository\ProductRepository;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class Search
{
    use DefaultActionTrait;

    public function __construct(private readonly ProductRepository $productRepository)
    {
    }

    public function GetProduct()
    {
        return $this->productRepository->findAll();
    }
}
