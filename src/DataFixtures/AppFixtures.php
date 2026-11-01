<?php

namespace App\DataFixtures;

use App\Entity\Education;
use App\Entity\Experience;
use App\Entity\Interest;
use App\Entity\Language;
use App\Entity\Profile;
use App\Entity\Project;
use App\Entity\Skill;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // The repository is public: fake data only. French numbers starting with 06 39 98 are reserved for fiction.
        $manager->persist((new Profile())
            ->setName('Jane Doe')
            ->setTitle('Développeuse PHP / Symfony senior')
            ->setSummary(
                "Développeuse back-end depuis dix ans, je conçois des applications Symfony robustes et lisibles.\n\n"
                ."J'aime les bases de code propres, les tests qui ont du sens et les équipes qui partagent leurs connaissances."
            )
            ->setEmail('jane.doe@example.com')
            ->setPhone('+33 6 39 98 00 00')
            ->setLocation('Lyon, France')
            ->setLinkedinUrl('https://linkedin.example.com/in/jane-doe')
            ->setGithubUrl('https://github.example.com/jane-doe'));

        $experiences = [
            [
                'Acme Corp',
                'Lead développeuse Symfony',
                'Lyon (hybride)',
                '2021-09-01',
                null,
                [
                    "Migration progressive d'un monolithe Symfony 4 vers Symfony 7",
                    "Mise en place de l'intégration continue et de l'analyse statique",
                    "Encadrement d'une équipe de quatre développeurs",
                ],
                ['PHP', 'Symfony', 'PostgreSQL', 'Docker', 'GitHub Actions'],
            ],
            [
                'Globex',
                'Développeuse back-end',
                'Paris',
                '2018-03-01',
                '2021-08-31',
                [
                    "Conception d'une API REST consommée par les applications mobiles",
                    "Optimisation des requêtes Doctrine sur un catalogue d'un million de produits",
                    'Écriture des tests fonctionnels des parcours de paiement',
                ],
                ['PHP', 'Symfony', 'API Platform', 'MySQL', 'Redis'],
            ],
            [
                'Initech',
                'Développeuse web',
                'Grenoble',
                '2016-01-01',
                '2018-02-28',
                [
                    "Développement de modules métier pour l'intranet de l'entreprise",
                    "Maintenance d'applications PHP historiques",
                ],
                ['PHP', 'Symfony', 'jQuery', 'MySQL'],
            ],
            [
                'Umbrella Studio',
                'Développeuse stagiaire',
                'Grenoble',
                '2015-03-01',
                '2015-08-31',
                [
                    "Réalisation d'un outil interne de suivi des temps",
                ],
                ['PHP', 'JavaScript'],
            ],
        ];
        foreach ($experiences as [$company, $role, $location, $start, $end, $missions, $stack]) {
            $manager->persist((new Experience())
                ->setCompany($company)
                ->setRole($role)
                ->setLocation($location)
                ->setStartDate(new \DateTimeImmutable($start))
                ->setEndDate($end === null ? null : new \DateTimeImmutable($end))
                ->setMissions($missions)
                ->setStack($stack));
        }

        $educations = [
            ['Université Grenoble Alpes', 'Master informatique', 'Grenoble', '2013-09-01', '2015-08-31', 'Spécialité génie logiciel.'],
            ['IUT de Lyon', 'DUT informatique', 'Lyon', '2011-09-01', '2013-06-30', null],
        ];
        foreach ($educations as [$school, $degree, $location, $start, $end, $description]) {
            $manager->persist((new Education())
                ->setSchool($school)
                ->setDegree($degree)
                ->setLocation($location)
                ->setStartDate(new \DateTimeImmutable($start))
                ->setEndDate(new \DateTimeImmutable($end))
                ->setDescription($description));
        }

        $skills = [
            ['PHP', 'Langages', 5],
            ['SQL', 'Langages', 4],
            ['JavaScript', 'Langages', 3],
            ['Symfony', 'Frameworks', 5],
            ['Doctrine', 'Frameworks', 5],
            ['API Platform', 'Frameworks', 4],
            ['Stimulus', 'Frameworks', 2],
            ['PHPUnit', 'Qualité', 4],
            ['PHPStan', 'Qualité', 4],
            ['Docker', 'Outils', 4],
            ['Git', 'Outils', 5],
            ['GitHub Actions', 'Outils', 3],
        ];
        foreach ($skills as $position => [$name, $category, $level]) {
            $manager->persist((new Skill())
                ->setName($name)
                ->setCategory($category)
                ->setLevel($level)
                ->setPosition($position));
        }

        $projects = [
            [
                'resume-ide',
                "Un CV présenté comme un projet ouvert dans un IDE.\n\nSymfony, Live Components et aucune étape de build.",
                'https://resume.example.com',
                'https://git.example.com/jane-doe/resume-ide',
            ],
            [
                'recipe-box',
                'Gestion de recettes de cuisine, avec une liste de courses générée automatiquement.',
                null,
                'https://git.example.com/jane-doe/recipe-box',
            ],
            [
                'trail-stats',
                'Tableau de bord des sorties de course à pied, alimenté par des fichiers GPX.',
                'https://trail-stats.example.com',
                null,
            ],
        ];
        foreach ($projects as $position => [$name, $description, $url, $repositoryUrl]) {
            $manager->persist((new Project())
                ->setName($name)
                ->setDescription($description)
                ->setUrl($url)
                ->setRepositoryUrl($repositoryUrl)
                ->setPosition($position));
        }

        $languages = [
            ['Français', 'Langue maternelle'],
            ['Anglais', 'Courant (C1)'],
            ['Espagnol', 'Intermédiaire (B1)'],
        ];
        foreach ($languages as $position => [$name, $level]) {
            $manager->persist((new Language())
                ->setName($name)
                ->setLevel($level)
                ->setPosition($position));
        }

        $interests = [
            ['Trail', 'Courses en montagne, du 20 au 50 km'],
            ['Cuisine', null],
            ['Open source', 'Contributions ponctuelles à des projets PHP'],
        ];
        foreach ($interests as $position => [$name, $description]) {
            $manager->persist((new Interest())
                ->setName($name)
                ->setDescription($description)
                ->setPosition($position));
        }

        $manager->flush();
    }
}
