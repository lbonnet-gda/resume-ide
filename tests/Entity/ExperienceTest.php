<?php

namespace App\Tests\Entity;

use App\Entity\Experience;
use PHPUnit\Framework\TestCase;

final class ExperienceTest extends TestCase
{
    public function testListsWithGapsAreReindexed(): void
    {
        $experience = new Experience()
            ->setMissions([0 => 'First', 2 => 'Third'])
            ->setStack([1 => 'PHP']);

        self::assertSame(['First', 'Third'], $experience->getMissions());
        self::assertSame(['PHP'], $experience->getStack());
    }
}
