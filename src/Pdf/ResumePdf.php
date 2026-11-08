<?php

namespace App\Pdf;

use App\Entity\Education;
use App\Entity\Experience;
use App\Entity\Interest;
use App\Entity\Language;
use App\Entity\Profile;
use App\Entity\Project;
use App\Entity\Skill;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Events;
use Sensiolabs\GotenbergBundle\GotenbergPdfInterface;
use Sensiolabs\GotenbergBundle\Processor\InMemoryProcessor;
use Symfony\Contracts\Cache\CacheInterface;

#[AsDoctrineListener(event: Events::postFlush)]
final readonly class ResumePdf
{
    private const string PUBLIC_CACHE_KEY = 'resume_pdf.public';
    private const string WITH_PHONE_CACHE_KEY = 'resume_pdf.with_phone';

    public function __construct(
        private EntityManagerInterface $entityManager,
        private GotenbergPdfInterface $gotenberg,
        private CacheInterface $cache,
    ) {
    }

    public function generate(Profile $profile, bool $withPhone): string
    {
        return $this->cache->get(
            $withPhone ? self::WITH_PHONE_CACHE_KEY : self::PUBLIC_CACHE_KEY,
            fn (): string => $this->render($profile, $withPhone),
        );
    }

    public function postFlush(): void
    {
        $this->cache->delete(self::PUBLIC_CACHE_KEY);
        $this->cache->delete(self::WITH_PHONE_CACHE_KEY);
    }

    private function render(Profile $profile, bool $withPhone): string
    {
        $newestFirst = ['startDate' => 'DESC', 'id' => 'DESC'];
        $byPosition = ['position' => 'ASC', 'id' => 'ASC'];
        $skills = [];

        foreach ($this->entityManager->getRepository(Skill::class)->findBy([], $byPosition) as $skill) {
            $skills[$skill->getCategory()][] = $skill->getName();
        }

        $context = [
            'profile' => $profile,
            'phone' => $withPhone ? $profile->getPhone() : null,
            'summary' => preg_split('/\R\s*\R/', trim($profile->getSummary())),
            'experiences' => $this->entityManager->getRepository(Experience::class)->findBy([], $newestFirst),
            'educations' => $this->entityManager->getRepository(Education::class)->findBy([], $newestFirst),
            'skills' => $skills,
            'projects' => $this->entityManager->getRepository(Project::class)->findBy([], $byPosition),
            'languages' => $this->entityManager->getRepository(Language::class)->findBy([], $byPosition),
            'interests' => $this->entityManager->getRepository(Interest::class)->findBy([], $byPosition),
        ];
        $builder = $this->gotenberg->html()
            ->content('pdf/resume.html.twig', $context)
            // Without it, Gotenberg ignores the CSS @page size and prints on Letter paper.
            ->preferCssPageSize()
            ->generateTaggedPdf();
        $builder->processor(new InMemoryProcessor());

        return $builder->generate()->process();
    }
}
