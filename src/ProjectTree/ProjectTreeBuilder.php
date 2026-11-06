<?php

namespace App\ProjectTree;

use App\Entity\Education;
use App\Entity\Experience;
use App\Entity\Interest;
use App\Entity\Language;
use App\Entity\Profile;
use App\Entity\Project;
use App\Entity\Skill;

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
        $experienceFiles = $this->experienceFiles($experiences);
        $educationFiles = $this->educationFiles($educations);
        $projectFiles = $this->projectFiles($projects);
        $readme = FileContents::readme(
            $profile,
            $experiences,
            $experienceFiles,
            $educations,
            $educationFiles,
            $projects,
            $projectFiles,
        );

        return new Folder('', [
            new Folder('config', [
                new File('config/interests.yaml', FileContents::interests($interests), breadcrumb: ['interests']),
                new File('config/languages.yaml', FileContents::languages($languages), breadcrumb: ['languages']),
            ]),
            new Folder('projects', $projectFiles),
            new Folder('src', [
                new Folder('src/Education', $educationFiles),
                new Folder('src/Experience', $experienceFiles),
            ]),
            new File('.env', FileContents::env($profile)),
            new File('composer.json', FileContents::composerJson($profile, $skills), breadcrumb: ['require']),
            new File('README.md', $readme, breadcrumb: [$profile->getName()]),
        ]);
    }

    /**
     * @param list<Experience> $experiences
     *
     * @return list<File>
     */
    private function experienceFiles(array $experiences): array
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
            static fn (Experience $experience, string $className): File => new File(
                'src/Experience/'.$className.'.php',
                FileContents::experience($className, $experience),
                FileContents::yearRange($experience->getStartDate(), $experience->getEndDate(), 'now'),
                ['App\\Experience', $className],
            ),
            $experiences,
            $classNames,
        );
    }

    /**
     * @param list<Education> $educations
     *
     * @return list<File>
     */
    private function educationFiles(array $educations): array
    {
        $classNames = $this->numberedFromOldest(
            array_map(
                static fn (Education $education): string => FileNames::className($education->getSchool(), 'School'),
                $educations,
            ),
        );

        return array_map(
            static fn (Education $education, string $className): File => new File(
                'src/Education/'.$className.'.php',
                FileContents::education($className, $education),
                FileContents::yearRange($education->getStartDate(), $education->getEndDate(), 'now'),
                ['App\\Education', $className],
            ),
            $educations,
            $classNames,
        );
    }

    /**
     * @param list<Project> $projects
     *
     * @return list<File>
     */
    private function projectFiles(array $projects): array
    {
        $slugs = FileNames::unique(
            array_map(
                static fn (Project $project): string => FileNames::slug($project->getName()),
                $projects,
            ),
            '-',
        );

        return array_map(
            static fn (Project $project, string $slug): File => new File(
                'projects/'.$slug.'.md',
                FileContents::project($project),
                breadcrumb: [$project->getName()],
            ),
            $projects,
            $slugs,
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
