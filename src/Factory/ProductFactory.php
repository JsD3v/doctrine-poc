<?php

namespace App\Factory;

use App\Entity\Product;
use Mmo\Faker\PicsumProvider;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;
use Faker\Factory;

/**
 * @extends PersistentProxyObjectFactory<Product>
 */
final class ProductFactory extends PersistentProxyObjectFactory
{
    private static $faker;

    public function __construct()
    {
        // S'assurer que le faker est initialisé avec le bon provider
        if (!self::$faker) {
            self::$faker = Factory::create();
            self::$faker->addProvider(new PicsumProvider(self::$faker));
            // Remplacer le faker par défaut de Foundry par notre version avec le provider
        }
    }

    public static function class(): string
    {
        return Product::class;
    }

    protected function defaults(): array|callable
    {
        return [
            'description' => self::faker()->text(),
            'image' => self::faker()->imageUrl(400, 400),
            'name' => self::faker()->text(30),
            'price' => self::faker()->randomFloat(2, 5, 1000),
        ];
    }

    protected function initialize(): static
    {
        return $this;
    }
}