<?php

namespace App\Controller;

use App\Entity\Profile;
use App\Pdf\ResumePdf;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\AsciiSlugger;

final class ResumePdfController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ResumePdf $resumePdf,
    ) {
    }

    #[Route('/resume.pdf', name: 'app_resume_pdf', methods: ['GET'])]
    public function public(): Response
    {
        return $this->download(false);
    }

    #[Route('/admin/resume.pdf', name: 'admin_resume_pdf', methods: ['GET'])]
    public function withPhone(): Response
    {
        return $this->download(true);
    }

    private function download(bool $withPhone): Response
    {
        $profile = $this->entityManager->getRepository(Profile::class)->findOneBy([], ['id' => 'ASC']);

        if ($profile === null) {
            return $this->render(
                'empty_project.html.twig',
                response: new Response(status: Response::HTTP_SERVICE_UNAVAILABLE),
            );
        }

        $fileName = new AsciiSlugger()->slug('CV '.$profile->getName().($withPhone ? ' with phone' : '')).'.pdf';

        return new Response(
            $this->resumePdf->generate($profile, $withPhone),
            headers: [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => HeaderUtils::makeDisposition(HeaderUtils::DISPOSITION_ATTACHMENT, $fileName),
            ],
        );
    }
}
