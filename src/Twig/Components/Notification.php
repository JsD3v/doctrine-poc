<?php

namespace App\Twig\Components;

use App\Repository\NotificationRepository;
use App\Repository\ProductRepository;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class Notification
{
    use DefaultActionTrait;
    public function __construct(
        private readonly NotificationRepository $notificationRepository,
        private readonly ProductRepository $productRepository,
    ) {}

    public function GetProductId($id): int
    {
        return $this->productRepository->findBy($id);
    }

    public function getUnreadCount(): int
    {
        return $this->notificationRepository->countUnread();
    }

    public function getNotifications(): array
    {
        return $this->notificationRepository->findBy(
            [],
            ['createdAt' => 'DESC'],
            10
        );
    }

    public function addToCart(int $productId): void
    {
        $notification = new Notification();
        $notification->setMessage('Produit ajouté au panier avec succès !');

        $this->notificationRepository->save($notification, true);
    }
}
