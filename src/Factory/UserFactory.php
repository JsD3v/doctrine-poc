<?php

namespace App\Factory;

use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<User>
 */
final class UserFactory extends PersistentProxyObjectFactory
{
    public function __construct(private readonly UserPasswordHasherInterface $hasher)
    {
    }

    public static function class(): string
    {
        return User::class;
    }

    protected function defaults(): array|callable
    {
        return [
            'email' => self::faker()->unique()->safeEmail(),
            'password' => 'password123', // Sera hashé dans initialize()
            'roles' => ['ROLE_USER'],
        ];
    }

    protected function initialize(): static
    {
        return $this
            ->afterInstantiate(function(User $user): void {
                // Hash le mot de passe
                $hashedPassword = $this->hasher->hashPassword(
                    $user,
                    $user->getPassword()
                );
                $user->setPassword($hashedPassword);
            })
            ;
    }
}