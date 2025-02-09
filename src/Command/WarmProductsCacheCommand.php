<?php

namespace App\Command;

use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:warm-products-cache',
    description: 'Warm up the products cache for common queries',
)]
class WarmProductsCacheCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly ProductRepository $productRepository
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->section('Starting cache warm-up for products...');

        // 1. Cache all products
        $io->text('Caching all products...');
        $allProducts = $this->productRepository
            ->createQueryBuilder('p')
            ->select('p', 'c')  // Sélectionne les produits et les catégories
            ->leftJoin('p.category', 'c')
            ->getQuery()
            ->getResult();

        $io->text(sprintf('Cached %d products', count($allProducts)));

        // 2. Cache products by price ranges
        $io->text('Caching products by price ranges...');
        $priceRanges = [
            [0, 50],
            [50, 100],
            [100, 500],
            [500, null]
        ];

        foreach ($priceRanges as $range) {
            $qb = $this->productRepository->createQueryBuilder('p')
                ->select('p', 'c')
                ->leftJoin('p.category', 'c')
                ->where('p.price >= :min')
                ->setParameter('min', $range[0]);

            if ($range[1] !== null) {
                $qb->andWhere('p.price < :max')
                    ->setParameter('max', $range[1]);
            }

            $products = $qb->getQuery()->getResult();
            $io->text(sprintf('Cached %d products for price range %d-%s',
                count($products),
                $range[0],
                $range[1] ?? 'max'
            ));
        }

        // 3. Cache products by category
        $io->text('Caching products by category...');
        $categories = $this->em->getRepository('App\Entity\Category')->findAll();
        foreach ($categories as $category) {
            $products = $this->productRepository
                ->createQueryBuilder('p')
                ->select('p', 'c')
                ->leftJoin('p.category', 'c')
                ->where('p.category = :category')
                ->setParameter('category', $category)
                ->getQuery()
                ->getResult();

            $io->text(sprintf('Cached %d products for category "%s"',
                count($products),
                $category->getName() ?? 'unnamed'
            ));
        }

        $io->success('Cache warm-up completed!');

        return Command::SUCCESS;
    }
}