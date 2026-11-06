<?php

namespace App\ProjectTree;

use App\Entity\Education;
use App\Entity\Experience;
use App\Entity\Interest;
use App\Entity\Language;
use App\Entity\Profile;
use App\Entity\Project;
use App\Entity\Skill;
use Doctrine\ORM\EntityManagerInterface;

final readonly class ProjectTreeLoader
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ProjectTreeBuilder $builder,
    ) {
    }

    public function load(): ?Folder
    {
        $profile = $this->entityManager->getRepository(Profile::class)->findOneBy([], ['id' => 'ASC']);

        if ($profile === null) {
            return null;
        }

        $newestFirst = ['startDate' => 'DESC', 'id' => 'DESC'];
        $byPosition = ['position' => 'ASC', 'id' => 'ASC'];

        return $this->builder->build(
            $profile,
            $this->entityManager->getRepository(Experience::class)->findBy([], $newestFirst),
            $this->entityManager->getRepository(Education::class)->findBy([], $newestFirst),
            $this->entityManager->getRepository(Skill::class)->findBy([], $byPosition),
            $this->entityManager->getRepository(Project::class)->findBy([], $byPosition),
            $this->entityManager->getRepository(Language::class)->findBy([], $byPosition),
            $this->entityManager->getRepository(Interest::class)->findBy([], $byPosition),
        );
    }
}
