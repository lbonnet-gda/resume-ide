<?php

namespace App\ProjectTree;

use App\Entity\Education;
use App\Entity\Experience;
use App\Entity\Interest;
use App\Entity\Language;
use App\Entity\Profile;
use App\Entity\Project;
use App\Entity\Skill;
use DateTimeImmutable;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

final class FileContents
{
    /**
     * @param list<Experience> $experiences newest first
     * @param list<File> $experienceFiles in the same order as $experiences
     * @param list<Education> $educations newest first
     * @param list<File> $educationFiles in the same order as $educations
     * @param list<Project> $projects
     * @param list<File> $projectFiles in the same order as $projects
     */
    public static function readme(
        Profile $profile,
        array $experiences,
        array $experienceFiles,
        array $educations,
        array $educationFiles,
        array $projects,
        array $projectFiles,
    ): string {
        $contact = [
            $profile->getLocation(),
            sprintf('[%1$s](mailto:%1$s)', $profile->getEmail()),
        ];

        if ($profile->getLinkedinUrl() !== null) {
            $contact[] = sprintf('[LinkedIn](%s)', $profile->getLinkedinUrl());
        }

        if ($profile->getGithubUrl() !== null) {
            $contact[] = sprintf('[GitHub](%s)', $profile->getGithubUrl());
        }

        $contact = implode(" ·\n", $contact);

        $experienceLines = implode(
            "\n",
            array_map(
                static fn (Experience $experience, File $file): string => sprintf(
                    '- [%s](%s), %s (%s)',
                    $experience->getCompany(),
                    $file->path,
                    $experience->getRole(),
                    self::yearRange($experience->getStartDate(), $experience->getEndDate(), "aujourd'hui"),
                ),
                $experiences,
                $experienceFiles,
            )
        );
        $educationLines = implode(
            "\n",
            array_map(
                static fn (Education $education, File $file): string => sprintf(
                    '- [%s](%s), %s (%s)',
                    $education->getSchool(),
                    $file->path,
                    $education->getDegree(),
                    self::yearRange($education->getStartDate(), $education->getEndDate(), "aujourd'hui"),
                ),
                $educations,
                $educationFiles,
            )
        );
        $projectLines = implode(
            "\n",
            array_map(
                static fn (Project $project, File $file): string => sprintf(
                    '- [%s](%s)',
                    $project->getName(),
                    $file->path
                ),
                $projects,
                $projectFiles,
            )
        );

        return <<<MARKDOWN
            # {$profile->getName()}

            **{$profile->getTitle()}**

            $contact

            {$profile->getSummary()}

            ## Expériences

            $experienceLines

            ## Formation

            $educationLines

            ## Projets

            $projectLines

            ## En savoir plus

            - [Compétences](composer.json)
            - [Langues](config/languages.yaml)
            - [Centres d'intérêt](config/interests.yaml)

            MARKDOWN;
    }

    public static function env(Profile $profile): string
    {
        $lines = ['# Contact', 'EMAIL='.$profile->getEmail()];

        if ($profile->getLinkedinUrl() !== null) {
            $lines[] = 'LINKEDIN='.$profile->getLinkedinUrl();
        }

        if ($profile->getGithubUrl() !== null) {
            $lines[] = 'GITHUB='.$profile->getGithubUrl();
        }

        return implode("\n", $lines)."\n";
    }

    /**
     * @param list<Skill> $skills
     */
    public static function composerJson(Profile $profile, array $skills): string
    {
        $packages = [];

        foreach ($skills as $skill) {
            $packages[] = FileNames::slug($skill->getCategory()).'/'.FileNames::slug($skill->getName());
        }

        $packages = FileNames::unique($packages, '-');
        $versions = array_map(static fn (Skill $skill): string => '^'.$skill->getLevel().'.0', $skills);
        $json = json_encode(
            [
                'name' => FileNames::slug($profile->getName()).'/resume',
                'description' => 'Compétences : la version indique le niveau (1 à 5)',
                'require' => array_combine($packages, $versions),
            ],
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR,
        );

        return $json."\n";
    }

    public static function experience(string $className, Experience $experience): string
    {
        return self::phpClass('Experience', $className, [
            'company' => $experience->getCompany(),
            'role' => $experience->getRole(),
            'location' => $experience->getLocation(),
            'startDate' => $experience->getStartDate(),
            'endDate' => $experience->getEndDate(),
            'missions' => $experience->getMissions(),
            'stack' => $experience->getStack(),
        ]);
    }

    public static function education(string $className, Education $education): string
    {
        return self::phpClass('Education', $className, [
            'school' => $education->getSchool(),
            'degree' => $education->getDegree(),
            'location' => $education->getLocation(),
            'startDate' => $education->getStartDate(),
            'endDate' => $education->getEndDate(),
            'description' => $education->getDescription(),
        ]);
    }

    public static function project(Project $project): string
    {
        $links = [];

        if ($project->getUrl() !== null) {
            $links[] = sprintf('- [Site](%s)', $project->getUrl());
        }

        if ($project->getRepositoryUrl() !== null) {
            $links[] = sprintf('- [Code source](%s)', $project->getRepositoryUrl());
        }

        $blocks = ['# '.$project->getName(), $project->getDescription()];

        if ($links !== []) {
            $blocks[] = implode("\n", $links);
        }

        return implode("\n\n", $blocks)."\n";
    }

    /**
     * @param list<Language> $languages
     */
    public static function languages(array $languages): string
    {
        return self::yamlList(
            'languages',
            array_map(
                static fn (Language $language): array => [
                    'name' => $language->getName(),
                    'level' => $language->getLevel(),
                ],
                $languages,
            )
        );
    }

    /**
     * @param list<Interest> $interests
     */
    public static function interests(array $interests): string
    {
        return self::yamlList(
            'interests',
            array_map(
                static fn (Interest $interest): array => [
                    'name' => $interest->getName(),
                    'description' => $interest->getDescription(),
                ],
                $interests,
            )
        );
    }

    public static function yearRange(DateTimeImmutable $start, ?DateTimeImmutable $end, string $ongoing): string
    {
        if ($end?->format('Y') === $start->format('Y')) {
            return $start->format('Y');
        }

        return $start->format('Y').' – '.($end?->format('Y') ?? $ongoing);
    }

    /**
     * @param array<string, DateTimeImmutable|list<string>|string|null> $arguments
     */
    private static function phpClass(string $namespace, string $className, array $arguments): string
    {
        $lines = [];

        foreach ($arguments as $name => $value) {
            $lines[] = sprintf('            %s: %s,', $name, self::phpValue($value));
        }

        $lines = implode("\n", $lines);

        return <<<PHP
            <?php

            namespace App\\$namespace;

            use App\\Resume\\$namespace;
            use DateTimeImmutable;

            final class $className extends $namespace
            {
                public function __construct()
                {
                    parent::__construct(
            $lines
                    );
                }
            }

            PHP;
    }

    /**
     * @param DateTimeImmutable|list<string>|string|null $value
     */
    private static function phpValue(DateTimeImmutable|array|string|null $value): string
    {
        if ($value === null) {
            return 'null';
        }

        if ($value instanceof DateTimeImmutable) {
            return sprintf("new DateTimeImmutable('%s')", $value->format('Y-m-d'));
        }

        if (is_string($value)) {
            return self::phpString($value);
        }

        $lines = ['['];

        foreach ($value as $item) {
            $lines[] = sprintf('                %s,', self::phpString($item));
        }

        $lines[] = '            ]';

        return implode("\n", $lines);
    }

    private static function phpString(string $value): string
    {
        if (str_contains($value, "'")) {
            return '"'.addcslashes($value, '"$\\').'"';
        }

        return "'".addcslashes($value, '\\')."'";
    }

    /**
     * Written by hand because Yaml::dump() cannot produce the "-   key: value" form.
     *
     * @param list<array<string, ?string>> $items
     */
    private static function yamlList(string $rootKey, array $items): string
    {
        $lines = [$rootKey.':'];

        foreach ($items as $item) {
            $prefix = '    -   ';

            foreach ($item as $key => $value) {
                $lines[] = $prefix.$key.': '.self::yamlValue($value);
                $prefix = '        ';
            }
        }

        return implode("\n", $lines)."\n";
    }

    private static function yamlValue(?string $value): string
    {
        // Yaml::dump() quotes any text with a space: plain text is kept as long as it parses back to the same value.
        try {
            if ($value !== null && Yaml::parse('key: '.$value) === ['key' => $value]) {
                return $value;
            }
        } catch (ParseException) {
        }

        return Yaml::dump($value);
    }
}
