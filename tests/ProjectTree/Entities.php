<?php

namespace App\Tests\ProjectTree;

use App\Entity\Education;
use App\Entity\Experience;
use App\Entity\Profile;

final class Entities
{
    public static function profile(): Profile
    {
        return new Profile()
            ->setName('Jane Doe')
            ->setTitle('Développeuse PHP')
            ->setSummary('Dix ans de PHP.')
            ->setEmail('jane.doe@example.com')
            ->setPhone('+33 6 39 98 00 00')
            ->setLocation('Lyon, France')
            ->setLinkedinUrl('https://linkedin.example.com/in/jane-doe')
            ->setGithubUrl('https://github.example.com/jane-doe');
    }

    /**
     * @param list<string> $missions
     */
    public static function experience(
        string $company = 'Acme Corp',
        string $start = '2021-09-01',
        ?string $end = null,
        array $missions = ['Encadrer une équipe'],
    ): Experience {
        return new Experience()
            ->setCompany($company)
            ->setRole('Lead développeuse')
            ->setLocation('Lyon')
            ->setStartDate(new \DateTimeImmutable($start))
            ->setEndDate($end === null ? null : new \DateTimeImmutable($end))
            ->setMissions($missions)
            ->setStack(['PHP', 'Symfony']);
    }

    public static function education(
        string $school = 'IUT de Lyon',
        string $start = '2011-09-01',
        string $end = '2013-06-30',
        ?string $description = null,
    ): Education {
        return new Education()
            ->setSchool($school)
            ->setDegree('DUT informatique')
            ->setLocation('Lyon')
            ->setStartDate(new \DateTimeImmutable($start))
            ->setEndDate(new \DateTimeImmutable($end))
            ->setDescription($description);
    }
}
