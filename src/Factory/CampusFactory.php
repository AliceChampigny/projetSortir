<?php

namespace App\Factory;

use App\Entity\Campus;
use App\Repository\CampusRepository;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;
use Zenstruck\Foundry\RepositoryProxy;

/**
 * @extends ModelFactory<Campus>
 *
 * @method        Campus|Proxy create(array|callable $attributes = [])
 * @method static Campus|Proxy createOne(array $attributes = [])
 * @method static Campus|Proxy find(object|array|mixed $criteria)
 * @method static Campus|Proxy findOrCreate(array $attributes)
 * @method static Campus|Proxy first(string $sortedField = 'id')
 * @method static Campus|Proxy last(string $sortedField = 'id')
 * @method static Campus|Proxy random(array $attributes = [])
 * @method static Campus|Proxy randomOrCreate(array $attributes = [])
 * @method static CampusRepository|RepositoryProxy repository()
 * @method static Campus[]|Proxy[] all()
 * @method static Campus[]|Proxy[] createMany(int $number, array|callable $attributes = [])
 * @method static Campus[]|Proxy[] createSequence(iterable|callable $sequence)
 * @method static Campus[]|Proxy[] findBy(array $attributes)
 * @method static Campus[]|Proxy[] randomRange(int $min, int $max, array $attributes = [])
 * @method static Campus[]|Proxy[] randomSet(int $number, array $attributes = [])
 */
final class CampusFactory extends ModelFactory
{
    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#factories-as-services
     *
     * @todo inject services if required
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     *
     * @todo add your default values here
     */
    protected function getDefaults(): array
    {
        return [
            'nom' => self::faker()->city(30),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): self
    {
        return $this
            // ->afterInstantiate(function(Campus $campus): void {})
        ;
    }

    protected static function getClass(): string
    {
        return Campus::class;
    }
}
