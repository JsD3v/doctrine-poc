<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Cache\InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Cache\Adapter\RedisAdapter;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Stopwatch\Stopwatch;
use Symfony\Component\String\TruncateMode;
use Symfony\Contracts\Cache\CacheInterface;
use function Symfony\Component\String\u;

final class IndexController extends AbstractController
{
    /**
     * @throws InvalidArgumentException
     */
    #[Route('/', name: 'app_index')]
    public function index(
        ProductRepository $productRepository,
        CacheInterface $cache
    ): Response {
        // Test Redis
        $cacheKey = 'test_key';
        $cache->get($cacheKey, function () {
            return 'Test Value - ' . date('Y-m-d H:i:s');
        });

        $stopwatch = new Stopwatch();
        $stopwatch->start('doctrine_query');
        $products = $productRepository->findAll();
        $event = $stopwatch->stop('doctrine_query');

        foreach ($products as $product) {
            $name = u($product->getName())->truncate(10, '...', cut: TruncateMode::WordBefore);
            $product->setName($name->toString());
            $description = u($product->getDescription())->truncate(40, '...', cut: TruncateMode::WordAfter);
            $product->setDescription($description->toString());
        }

        dump([
            'query_time' => $event->getDuration() . 'ms',
        ]);

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


    /**
     * Seulement pour tester
     */

    #[Route('/test-cache', name: 'test_product')]
    public function test(EntityManagerInterface $em): Response
    {
        // Crée la connexion Redis (en reprenant la DSN de ton .env par ex.)
        // Dans un projet Symfony, tu peux aussi injecter %env(REDIS_URL)% comme paramètre
        $redis = RedisAdapter::createConnection($_ENV['REDIS_URL'] ?? 'redis://localhost:6379');

        // Méthode 1 : DBSIZE -> nombre total de clés dans cette base Redis
        $dbSize = $redis->dbSize();

        // Méthode 2 : KEYS "*" -> liste toutes les clés (attention, c'est O(n) et coûteux)
        $keys = $redis->keys('*');
        $count = count($keys);

        return new Response('Redis DB size: ' . $dbSize.'Numbers of keys: ' . $count);
    }

    #[Route('/test-products', name: 'test_products')]
    public function testProducts(EntityManagerInterface $em): Response
    {
        // 1) Charge TOUTES les entrées, ce qui va peupler le 2LC
        $products = $em->getRepository(Product::class)->findAll();

        return new Response('We got ' . count($products) . ' products!');
    }


}
