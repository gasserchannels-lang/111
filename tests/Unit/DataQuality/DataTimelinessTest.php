<?php

namespace Tests\Unit\DataQuality;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;

class DataTimelinessTest extends TestCase
{
    #[Test]
    public function it_validates_data_freshness(): void
    {
        $lastUpdated = new \DateTime('-1 hour');
        $maxAge = 2; // hours

        $isFresh = $this->isDataFresh($lastUpdated, $maxAge);

        $this->assertTrue($isFresh);
    }

    #[Test]
    public function it_detects_stale_data(): void
    {
        $lastUpdated = new \DateTime('-5 hours');
        $maxAge = 2; // hours

        $isFresh = $this->isDataFresh($lastUpdated, $maxAge);

        $this->assertFalse($isFresh);
    }

    #[Test]
    public function it_calculates_data_age_in_hours(): void
    {
        $lastUpdated = new \DateTime('-3 hours');
        $expectedAge = 3;

        $actualAge = $this->calculateDataAge($lastUpdated);

        $this->assertEquals($expectedAge, $actualAge);
    }

    #[Test]
    public function it_validates_real_time_data_requirements(): void
    {
        $dataTimestamp = new \DateTime('-30 seconds');
        $maxLatency = 60; // seconds

        $isRealTime = $this->isRealTimeData($dataTimestamp, $maxLatency);

        $this->assertTrue($isRealTime);
    }

    #[Test]
    public function it_handles_different_time_zones(): void
    {
        $utcTime = new \DateTime('2024-01-15 10:00:00', new \DateTimeZone('UTC'));
        $localTime = new \DateTime('2024-01-15 12:00:00', new \DateTimeZone('Europe/London'));

        $timeDiff = $this->calculateTimeDifference($utcTime, $localTime);

        $this->assertEquals(2, $timeDiff); // 2 hours difference
    }

    #[Test]
    public function it_validates_scheduled_data_updates(): void
    {
        $lastUpdate = new \DateTime('2024-01-15 09:00:00');
        $nextScheduledUpdate = new \DateTime('2024-01-15 12:00:00');
        $currentTime = new \DateTime('2024-01-15 11:30:00');

        $isOnSchedule = $this->isUpdateOnSchedule($lastUpdate, $nextScheduledUpdate, $currentTime);

        $this->assertTrue($isOnSchedule);
    }

    #[Test]
    public function it_detects_delayed_data_updates(): void
    {
        $lastUpdate = new \DateTime('2024-01-15 09:00:00');
        $expectedNextUpdate = new \DateTime('2024-01-15 12:00:00');
        $currentTime = new \DateTime('2024-01-15 13:00:00'); // 1 hour late

        $isDelayed = $this->isUpdateDelayed($lastUpdate, $expectedNextUpdate, $currentTime);

        $this->assertTrue($isDelayed);
    }

    #[Test]
    public function it_calculates_data_velocity(): void
    {
        $dataPoints = [
            new \DateTime('2024-01-15 10:00:00'),
            new \DateTime('2024-01-15 10:01:00'),
            new \DateTime('2024-01-15 10:02:00'),
            new \DateTime('2024-01-15 10:03:00')
        ];

        $velocity = $this->calculateDataVelocity($dataPoints);

        $this->assertEquals(1, $velocity); // 1 update per minute
    }

    #[Test]
    public function it_validates_data_synchronization(): void
    {
        $source1Time = new \DateTime('2024-01-15 10:00:00');
        $source2Time = new \DateTime('2024-01-15 10:00:30');
        $maxSyncDelay = 60; // seconds

        $isSynchronized = $this->isDataSynchronized($source1Time, $source2Time, $maxSyncDelay);

        $this->assertTrue($isSynchronized);
    }

    #[Test]
    public function it_handles_data_backfill_scenarios(): void
    {
        $historicalData = [
            '2024-01-15 09:00:00' => 100,
            '2024-01-15 10:00:00' => 105,
            '2024-01-15 11:00:00' => 110
        ];

        $backfillData = [
            '2024-01-15 09:30:00' => 102,
            '2024-01-15 10:30:00' => 107
        ];

        $isValidBackfill = $this->validateBackfillData($historicalData, $backfillData);

        $this->assertTrue($isValidBackfill);
    }

    private function isDataFresh(\DateTime $lastUpdated, int $maxAgeHours): bool
    {
        $now = new \DateTime();
        $age = $now->diff($lastUpdated)->h;

        return $age <= $maxAgeHours;
    }

    private function calculateDataAge(\DateTime $lastUpdated): int
    {
        $now = new \DateTime();
        return $now->diff($lastUpdated)->h;
    }

    private function isRealTimeData(\DateTime $dataTimestamp, int $maxLatencySeconds): bool
    {
        $now = new \DateTime();
        $latency = $now->getTimestamp() - $dataTimestamp->getTimestamp();

        return $latency <= $maxLatencySeconds;
    }

    private function calculateTimeDifference(\DateTime $time1, \DateTime $time2): int
    {
        return abs($time1->diff($time2)->h);
    }

    private function isUpdateOnSchedule(\DateTime $lastUpdate, \DateTime $nextScheduled, \DateTime $currentTime): bool
    {
        return $currentTime >= $lastUpdate && $currentTime <= $nextScheduled;
    }

    private function isUpdateDelayed(\DateTime $lastUpdate, \DateTime $expectedNext, \DateTime $currentTime): bool
    {
        return $currentTime > $expectedNext;
    }

    private function calculateDataVelocity(array $timestamps): float
    {
        if (count($timestamps) < 2) {
            return 0;
        }

        $firstTime = $timestamps[0];
        $lastTime = end($timestamps);
        $timeSpan = $lastTime->getTimestamp() - $firstTime->getTimestamp();
        $updates = count($timestamps) - 1;

        return $timeSpan > 0 ? $updates / ($timeSpan / 60) : 0; // updates per minute
    }

    private function isDataSynchronized(\DateTime $time1, \DateTime $time2, int $maxDelaySeconds): bool
    {
        $delay = abs($time1->getTimestamp() - $time2->getTimestamp());
        return $delay <= $maxDelaySeconds;
    }

    private function validateBackfillData(array $historical, array $backfill): bool
    {
        foreach ($backfill as $timestamp => $value) {
            $backfillTime = new \DateTime($timestamp);

            // Check if backfill data fits chronologically
            $isValidTime = true;
            foreach ($historical as $histTime => $histValue) {
                $histDateTime = new \DateTime($histTime);
                if ($backfillTime <= $histDateTime) {
                    $isValidTime = false;
                    break;
                }
            }

            if (!$isValidTime) {
                return false;
            }
        }

        return true;
    }
}
