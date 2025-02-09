<?php

namespace App\Controller\Admin;

use App\Entity\Product;
use Doctrine\ORM\Cache\EntityCacheKey;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ProductCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Product::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->onlyOnIndex(),
            TextField::new('name'),
            TextareaField::new('description'),
            ImageField::new('imageFile')->onlyOnIndex(),
            MoneyField::new('price')->setCurrency('EUR'),
        ];
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        parent::updateEntity($entityManager, $entityInstance);

        $cache = $entityManager->getCache();
        if ($cache) {
            $metadata = $entityManager->getClassMetadata(Product::class);
            $cacheKey = new EntityCacheKey($metadata->rootEntityName, ['id' => $entityInstance->getId()]);

            $cacheRegion = $cache->getEntityCacheRegion(Product::class);
            if ($cacheRegion) {
                $cacheRegion->evict($cacheKey);
            }
        }
    }


    // Optionnel : gérer aussi la suppression
    public function deleteEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        // Invalider le cache avant la suppression
        $cache = $entityManager->getCache();
        if ($cache && $cache->containsEntity(Product::class, $entityInstance->getId())) {
            $cacheRegion = $cache->getEntityCacheRegion(Product::class);
            if ($cacheRegion) {
                $cacheRegion->evict($entityInstance->getId());
            }
        }

        parent::deleteEntity($entityManager, $entityInstance);
    }
}
