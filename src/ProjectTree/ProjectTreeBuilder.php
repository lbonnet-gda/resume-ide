<?php

namespace App\ProjectTree;

use App\Entity\Education;
use App\Entity\Experience;
use App\Entity\Interest;
use App\Entity\Language;
use App\Entity\Profile;
use App\Entity\Project;
use App\Entity\Skill;

/**
 * @phpstan-type TimelineItem array{path: string, title: string, subtitle: string, dates: string}
 * @phpstan-type ProjectLink array{path: string, name: string, summary: string}
 */
final class ProjectTreeBuilder
{
    /**
     * @param list<Experience> $experiences newest first
     * @param list<Education> $educations newest first
     * @param list<Skill> $skills
     * @param list<Project> $projects
     * @param list<Language> $languages
     * @param list<Interest> $interests
     */
    public function build(
        Profile $profile,
        array $experiences,
        array $educations,
        array $skills,
        array $projects,
        array $languages,
        array $interests,
    ): Folder {
        $experienceTimeline = $this->experienceTimeline($experiences);
        $educationTimeline = $this->educationTimeline($educations);
        $projectLinks = $this->projectLinks($projects);
        $name = $profile->getName();
        $experienceFiles = $this->experienceFiles($experiences, $experienceTimeline, $name);
        $educationFiles = $this->educationFiles($educations, $educationTimeline, $name);
        $projectFiles = $this->projectFiles($projects, $projectLinks, $name);
        $readme = FileContents::readme(
            $profile,
            $experiences,
            $experienceFiles,
            $educations,
            $educationFiles,
            $projects,
            $projectFiles,
        );
        $skillsByCategory = [];

        foreach ($skills as $skill) {
            $skillsByCategory[$skill->getCategory()][] = $skill;
        }

        $languageList = array_map(
            static fn (Language $language): string => $language->getName().' : '.$language->getLevel()->frenchLabel(),
            $languages,
        );
        $interestList = array_map(
            static fn (Interest $interest): string => implode(
                ' : ',
                array_filter([$interest->getName(), $interest->getDescription()]),
            ),
            $interests,
        );
        $contactChannels = array_filter([
            'e-mail',
            $profile->getLinkedinUrl() !== null ? 'LinkedIn' : null,
            $profile->getGithubUrl() !== null ? 'GitHub' : null,
        ]);

        return new Folder('', [
            new Folder('config', [
                new File(
                    'config/interests.yaml',
                    FileContents::interests($interests),
                    'interests',
                    "Centres d'intérêt – $name",
                    FileContents::description(implode(' · ', $interestList)),
                    ['interests' => $interests],
                    breadcrumb: ['interests'],
                ),
                new File(
                    'config/languages.yaml',
                    FileContents::languages($languages),
                    'languages',
                    "Langues – $name",
                    FileContents::description(implode(' · ', $languageList)),
                    ['languages' => $languages],
                    breadcrumb: ['languages'],
                ),
            ]),
            new Folder('projects', $projectFiles),
            new Folder('src', [
                new Folder('src/Education', $educationFiles),
                new Folder('src/Experience', $experienceFiles),
            ]),
            new File(
                '.env',
                FileContents::env($profile),
                'contact',
                "Contact – $name",
                FileContents::description(
                    "Contacter $name, {$profile->getTitle()} ({$profile->getLocation()}) : "
                    .implode(', ', $contactChannels).'.',
                ),
                ['profile' => $profile],
            ),
            new File(
                'composer.json',
                FileContents::composerJson($profile, $skills),
                'skills',
                "Compétences – $name",
                FileContents::description(
                    "Compétences de $name, {$profile->getTitle()} : "
                    .implode(', ', array_map(static fn (Skill $skill): string => $skill->getName(), $skills)).'.',
                ),
                ['skillsByCategory' => $skillsByCategory],
                breadcrumb: ['require'],
            ),
            new File(
                'README.md',
                $readme,
                'readme',
                "$name – {$profile->getTitle()}",
                FileContents::description($profile->getSummary()),
                [
                    'profile' => $profile,
                    'summary' => FileContents::paragraphs($profile->getSummary()),
                    'experienceTimeline' => $experienceTimeline,
                    'educationTimeline' => $educationTimeline,
                    'projectLinks' => $projectLinks,
                ],
                breadcrumb: [$name],
            ),
        ]);
    }

    /**
     * @param list<Experience> $experiences
     *
     * @return list<TimelineItem>
     */
    private function experienceTimeline(array $experiences): array
    {
        $classNames = $this->numberedFromOldest(
            array_map(
                static fn (Experience $experience): string => FileNames::className(
                    $experience->getCompany(),
                    'Company',
                ),
                $experiences,
            ),
        );

        return array_map(
            static fn (Experience $experience, string $className): array => [
                'path' => 'src/Experience/'.$className.'.php',
                'title' => $experience->getCompany(),
                'subtitle' => $experience->getRole(),
                'dates' => FileContents::yearRange(
                    $experience->getStartDate(),
                    $experience->getEndDate(),
                    "aujourd'hui",
                ),
            ],
            $experiences,
            $classNames,
        );
    }

    /**
     * @param list<Experience> $experiences
     * @param list<TimelineItem> $timeline in the same order as $experiences
     *
     * @return list<File>
     */
    private function experienceFiles(array $experiences, array $timeline, string $name): array
    {
        return array_map(
            static function (Experience $experience, array $item) use ($timeline, $name): File {
                $className = basename($item['path'], '.php');
                $dates = FileContents::monthRange($experience->getStartDate(), $experience->getEndDate());
                $role = "{$experience->getRole()} chez {$experience->getCompany()}";
                $missions = array_map(
                    static fn (string $mission): string => rtrim($mission, '.').'.',
                    $experience->getMissions(),
                );

                return new File(
                    $item['path'],
                    FileContents::experience($className, $experience),
                    'experience',
                    "$role – $name",
                    FileContents::description(
                        "$role, $dates, {$experience->getLocation()}. ".implode(' ', $missions),
                    ),
                    [
                        'experience' => $experience,
                        'dates' => $dates,
                        'timeline' => $timeline,
                    ],
                    FileContents::yearRange($experience->getStartDate(), $experience->getEndDate(), 'now'),
                    ['App\\Experience', $className],
                );
            },
            $experiences,
            $timeline,
        );
    }

    /**
     * @param list<Education> $educations
     *
     * @return list<TimelineItem>
     */
    private function educationTimeline(array $educations): array
    {
        $classNames = $this->numberedFromOldest(
            array_map(
                static fn (Education $education): string => FileNames::className($education->getSchool(), 'School'),
                $educations,
            ),
        );

        return array_map(
            static fn (Education $education, string $className): array => [
                'path' => 'src/Education/'.$className.'.php',
                'title' => $education->getSchool(),
                'subtitle' => $education->getDegree(),
                'dates' => FileContents::yearRange(
                    $education->getStartDate(),
                    $education->getEndDate(),
                    "aujourd'hui",
                ),
            ],
            $educations,
            $classNames,
        );
    }

    /**
     * @param list<Education> $educations
     * @param list<TimelineItem> $timeline in the same order as $educations
     *
     * @return list<File>
     */
    private function educationFiles(array $educations, array $timeline, string $name): array
    {
        return array_map(
            static function (Education $education, array $item) use ($timeline, $name): File {
                $className = basename($item['path'], '.php');
                $dates = FileContents::monthRange($education->getStartDate(), $education->getEndDate());
                $degree = "{$education->getDegree()}, {$education->getSchool()}";

                return new File(
                    $item['path'],
                    FileContents::education($className, $education),
                    'education',
                    "$degree – $name",
                    FileContents::description(
                        "$degree, $dates, {$education->getLocation()}. {$education->getDescription()}",
                    ),
                    [
                        'education' => $education,
                        'dates' => $dates,
                        'timeline' => $timeline,
                    ],
                    FileContents::yearRange($education->getStartDate(), $education->getEndDate(), 'now'),
                    ['App\\Education', $className],
                );
            },
            $educations,
            $timeline,
        );
    }

    /**
     * @param list<Project> $projects
     *
     * @return list<ProjectLink>
     */
    private function projectLinks(array $projects): array
    {
        $slugs = FileNames::unique(
            array_map(
                static fn (Project $project): string => FileNames::slug($project->getName()),
                $projects,
            ),
            '-',
        );

        return array_map(
            static fn (Project $project, string $slug): array => [
                'path' => 'projects/'.$slug.'.md',
                'name' => $project->getName(),
                'summary' => FileContents::paragraphs($project->getDescription())[0] ?? '',
            ],
            $projects,
            $slugs,
        );
    }

    /**
     * @param list<Project> $projects
     * @param list<ProjectLink> $links in the same order as $projects
     *
     * @return list<File>
     */
    private function projectFiles(array $projects, array $links, string $name): array
    {
        return array_map(
            static fn (Project $project, array $link): File => new File(
                $link['path'],
                FileContents::project($project),
                'project',
                "Projet {$project->getName()} – $name",
                FileContents::description($project->getDescription()),
                [
                    'project' => $project,
                    'description' => FileContents::paragraphs($project->getDescription()),
                    'otherProjects' => array_values(
                        array_filter($links, static fn (array $other): bool => $other['path'] !== $link['path']),
                    ),
                ],
                breadcrumb: [$project->getName()],
            ),
            $projects,
            $links,
        );
    }

    /**
     * Numbers from the oldest, so that adding a newer entry never changes the URL of an existing file.
     *
     * @param list<string> $classNames newest first
     *
     * @return list<string>
     */
    private function numberedFromOldest(array $classNames): array
    {
        return array_reverse(FileNames::unique(array_reverse($classNames)));
    }
}
