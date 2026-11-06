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
        $experienceFiles = $this->experienceFiles($experiences, $experienceTimeline);
        $educationFiles = $this->educationFiles($educations, $educationTimeline);
        $projectFiles = $this->projectFiles($projects, $projectLinks);
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

        return new Folder('', [
            new Folder('config', [
                new File(
                    'config/interests.yaml',
                    FileContents::interests($interests),
                    'interests',
                    ['interests' => $interests],
                    breadcrumb: ['interests'],
                ),
                new File(
                    'config/languages.yaml',
                    FileContents::languages($languages),
                    'languages',
                    ['languages' => $languages],
                    breadcrumb: ['languages'],
                ),
            ]),
            new Folder('projects', $projectFiles),
            new Folder('src', [
                new Folder('src/Education', $educationFiles),
                new Folder('src/Experience', $experienceFiles),
            ]),
            new File('.env', FileContents::env($profile), 'contact', ['profile' => $profile]),
            new File(
                'composer.json',
                FileContents::composerJson($profile, $skills),
                'skills',
                ['skillsByCategory' => $skillsByCategory],
                breadcrumb: ['require'],
            ),
            new File(
                'README.md',
                $readme,
                'readme',
                [
                    'profile' => $profile,
                    'summary' => FileContents::paragraphs($profile->getSummary()),
                    'experienceTimeline' => $experienceTimeline,
                    'educationTimeline' => $educationTimeline,
                    'projectLinks' => $projectLinks,
                ],
                breadcrumb: [$profile->getName()],
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
    private function experienceFiles(array $experiences, array $timeline): array
    {
        return array_map(
            static function (Experience $experience, array $item) use ($timeline): File {
                $className = basename($item['path'], '.php');

                return new File(
                    $item['path'],
                    FileContents::experience($className, $experience),
                    'experience',
                    [
                        'experience' => $experience,
                        'dates' => FileContents::monthRange($experience->getStartDate(), $experience->getEndDate()),
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
    private function educationFiles(array $educations, array $timeline): array
    {
        return array_map(
            static function (Education $education, array $item) use ($timeline): File {
                $className = basename($item['path'], '.php');

                return new File(
                    $item['path'],
                    FileContents::education($className, $education),
                    'education',
                    [
                        'education' => $education,
                        'dates' => FileContents::monthRange($education->getStartDate(), $education->getEndDate()),
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
    private function projectFiles(array $projects, array $links): array
    {
        return array_map(
            static fn (Project $project, array $link): File => new File(
                $link['path'],
                FileContents::project($project),
                'project',
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
