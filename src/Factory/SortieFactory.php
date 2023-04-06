<?php

namespace App\Factory;

use App\Entity\Sortie;
use App\Repository\SortieRepository;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;
use Zenstruck\Foundry\RepositoryProxy;

/**
 * @extends ModelFactory<Sortie>
 *
 * @method        Sortie|Proxy create(array|callable $attributes = [])
 * @method static Sortie|Proxy createOne(array $attributes = [])
 * @method static Sortie|Proxy find(object|array|mixed $criteria)
 * @method static Sortie|Proxy findOrCreate(array $attributes)
 * @method static Sortie|Proxy first(string $sortedField = 'id')
 * @method static Sortie|Proxy last(string $sortedField = 'id')
 * @method static Sortie|Proxy random(array $attributes = [])
 * @method static Sortie|Proxy randomOrCreate(array $attributes = [])
 * @method static SortieRepository|RepositoryProxy repository()
 * @method static Sortie[]|Proxy[] all()
 * @method static Sortie[]|Proxy[] createMany(int $number, array|callable $attributes = [])
 * @method static Sortie[]|Proxy[] createSequence(iterable|callable $sequence)
 * @method static Sortie[]|Proxy[] findBy(array $attributes)
 * @method static Sortie[]|Proxy[] randomRange(int $min, int $max, array $attributes = [])
 * @method static Sortie[]|Proxy[] randomSet(int $number, array $attributes = [])
 */
final class SortieFactory extends ModelFactory
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
            'campus' => CampusFactory::new(),
            'dateHeureDebut' => self::faker()->dateTime(),
            'dateLimiteInscription' => self::faker()->dateTime(),
            'duree' => self::faker()->randomNumber(),
            'etat' => null, // TODO add App\Entity\Etat type manually
            'infosSortie' => self::faker()->text(500),
            'lieu' => null, // TODO add App\Entity\Lieu type manually
            'nbInscriptionsMax' => self::faker()->randomNumber(),
            'nom' => self::faker()->text(30),
            'organisateur' => ParticipantFactory::new(),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): self
    {
        return $this
            // ->afterInstantiate(function(Sortie $sortie): void {})
        ;
    }

    protected static function getClass(): string
    {
        return Sortie::class;
    }
}
