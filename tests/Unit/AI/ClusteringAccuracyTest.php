<?php

namespace Tests\Unit\AI;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ClusteringAccuracyTest extends TestCase
{
    #[Test]
    public function it_validates_clustering_accuracy(): void
    {
        $clusters = [
            ['id' => 1, 'cluster' => 0, 'features' => [1, 2, 3]],
            ['id' => 2, 'cluster' => 0, 'features' => [1, 3, 4]],
            ['id' => 3, 'cluster' => 1, 'features' => [10, 11, 12]],
            ['id' => 4, 'cluster' => 1, 'features' => [11, 12, 13]],
        ];

        $accuracy = $this->calculateClusteringAccuracy($clusters);
        $this->assertGreaterThan(0.8, $accuracy);
    }

    #[Test]
    public function it_validates_silhouette_score(): void
    {
        $clusters = [
            ['id' => 1, 'cluster' => 0, 'features' => [1, 2, 3]],
            ['id' => 2, 'cluster' => 0, 'features' => [1, 3, 4]],
            ['id' => 3, 'cluster' => 1, 'features' => [10, 11, 12]],
            ['id' => 4, 'cluster' => 1, 'features' => [11, 12, 13]],
        ];

        $silhouetteScore = $this->calculateSilhouetteScore($clusters);
        $this->assertGreaterThan(0.5, $silhouetteScore);
    }

    #[Test]
    public function it_validates_inertia_score(): void
    {
        $clusters = [
            ['id' => 1, 'cluster' => 0, 'features' => [1, 2, 3]],
            ['id' => 2, 'cluster' => 0, 'features' => [1, 3, 4]],
            ['id' => 3, 'cluster' => 1, 'features' => [10, 11, 12]],
            ['id' => 4, 'cluster' => 1, 'features' => [11, 12, 13]],
        ];

        $inertia = $this->calculateInertia($clusters);
        $this->assertGreaterThan(0, $inertia);
    }

    #[Test]
    public function it_validates_davies_bouldin_score(): void
    {
        $clusters = [
            ['id' => 1, 'cluster' => 0, 'features' => [1, 2, 3]],
            ['id' => 2, 'cluster' => 0, 'features' => [1, 3, 4]],
            ['id' => 3, 'cluster' => 1, 'features' => [10, 11, 12]],
            ['id' => 4, 'cluster' => 1, 'features' => [11, 12, 13]],
        ];

        $dbScore = $this->calculateDaviesBouldinScore($clusters);
        $this->assertGreaterThan(0, $dbScore);
    }

    #[Test]
    public function it_validates_calinski_harabasz_score(): void
    {
        $clusters = [
            ['id' => 1, 'cluster' => 0, 'features' => [1, 2, 3]],
            ['id' => 2, 'cluster' => 0, 'features' => [1, 3, 4]],
            ['id' => 3, 'cluster' => 1, 'features' => [10, 11, 12]],
            ['id' => 4, 'cluster' => 1, 'features' => [11, 12, 13]],
        ];

        $chScore = $this->calculateCalinskiHarabaszScore($clusters);
        $this->assertGreaterThan(0, $chScore);
    }

    #[Test]
    public function it_validates_cluster_separation(): void
    {
        $clusters = [
            ['id' => 1, 'cluster' => 0, 'features' => [1, 2, 3]],
            ['id' => 2, 'cluster' => 0, 'features' => [1, 3, 4]],
            ['id' => 3, 'cluster' => 1, 'features' => [10, 11, 12]],
            ['id' => 4, 'cluster' => 1, 'features' => [11, 12, 13]],
        ];

        $separation = $this->calculateClusterSeparation($clusters);
        $this->assertGreaterThan(0, $separation);
    }

    #[Test]
    public function it_validates_cluster_cohesion(): void
    {
        $clusters = [
            ['id' => 1, 'cluster' => 0, 'features' => [1, 2, 3]],
            ['id' => 2, 'cluster' => 0, 'features' => [1, 3, 4]],
            ['id' => 3, 'cluster' => 1, 'features' => [10, 11, 12]],
            ['id' => 4, 'cluster' => 1, 'features' => [11, 12, 13]],
        ];

        $cohesion = $this->calculateClusterCohesion($clusters);
        $this->assertGreaterThan(0, $cohesion);
    }

    #[Test]
    public function it_validates_optimal_cluster_number(): void
    {
        $data = [
            ['features' => [1, 2, 3]],
            ['features' => [1, 3, 4]],
            ['features' => [10, 11, 12]],
            ['features' => [11, 12, 13]],
            ['features' => [20, 21, 22]],
            ['features' => [21, 22, 23]],
        ];

        $optimalK = $this->findOptimalClusterNumber($data);
        $this->assertGreaterThan(1, $optimalK);
        $this->assertLessThanOrEqual(6, $optimalK);
    }

    #[Test]
    public function it_validates_cluster_stability(): void
    {
        $clusters = [
            ['id' => 1, 'cluster' => 0, 'features' => [1, 2, 3]],
            ['id' => 2, 'cluster' => 0, 'features' => [1, 3, 4]],
            ['id' => 3, 'cluster' => 1, 'features' => [10, 11, 12]],
            ['id' => 4, 'cluster' => 1, 'features' => [11, 12, 13]],
        ];

        $stability = $this->calculateClusterStability($clusters);
        $this->assertGreaterThan(0, $stability);
        $this->assertLessThanOrEqual(1, $stability);
    }

    #[Test]
    public function it_validates_cluster_purity(): void
    {
        $clusters = [
            ['id' => 1, 'cluster' => 0, 'label' => 'A'],
            ['id' => 2, 'cluster' => 0, 'label' => 'A'],
            ['id' => 3, 'cluster' => 1, 'label' => 'B'],
            ['id' => 4, 'cluster' => 1, 'label' => 'B'],
        ];

        $purity = $this->calculateClusterPurity($clusters);
        $this->assertEquals(1.0, $purity); // Perfect purity
    }

    #[Test]
    public function it_validates_cluster_entropy(): void
    {
        $clusters = [
            ['id' => 1, 'cluster' => 0, 'label' => 'A'],
            ['id' => 2, 'cluster' => 0, 'label' => 'A'],
            ['id' => 3, 'cluster' => 1, 'label' => 'B'],
            ['id' => 4, 'cluster' => 1, 'label' => 'B'],
        ];

        $entropy = $this->calculateClusterEntropy($clusters);
        $this->assertEquals(0.0, $entropy); // Perfect clustering
    }

    #[Test]
    public function it_validates_cluster_centroids(): void
    {
        $clusters = [
            ['id' => 1, 'cluster' => 0, 'features' => [1, 2, 3]],
            ['id' => 2, 'cluster' => 0, 'features' => [1, 3, 4]],
            ['id' => 3, 'cluster' => 1, 'features' => [10, 11, 12]],
            ['id' => 4, 'cluster' => 1, 'features' => [11, 12, 13]],
        ];

        $centroids = $this->calculateClusterCentroids($clusters);
        $this->assertCount(2, $centroids);
        $this->assertArrayHasKey(0, $centroids);
        $this->assertArrayHasKey(1, $centroids);
    }

    #[Test]
    public function it_validates_cluster_sizes(): void
    {
        $clusters = [
            ['id' => 1, 'cluster' => 0, 'features' => [1, 2, 3]],
            ['id' => 2, 'cluster' => 0, 'features' => [1, 3, 4]],
            ['id' => 3, 'cluster' => 1, 'features' => [10, 11, 12]],
            ['id' => 4, 'cluster' => 1, 'features' => [11, 12, 13]],
        ];

        $sizes = $this->calculateClusterSizes($clusters);
        $this->assertCount(2, $sizes);
        $this->assertEquals(2, $sizes[0]);
        $this->assertEquals(2, $sizes[1]);
    }

    #[Test]
    public function it_validates_cluster_quality_metrics(): void
    {
        $clusters = [
            ['id' => 1, 'cluster' => 0, 'features' => [1, 2, 3]],
            ['id' => 2, 'cluster' => 0, 'features' => [1, 3, 4]],
            ['id' => 3, 'cluster' => 1, 'features' => [10, 11, 12]],
            ['id' => 4, 'cluster' => 1, 'features' => [11, 12, 13]],
        ];

        $metrics = $this->calculateClusterQualityMetrics($clusters);
        $this->assertArrayHasKey('silhouette_score', $metrics);
        $this->assertArrayHasKey('inertia', $metrics);
        $this->assertArrayHasKey('davies_bouldin', $metrics);
        $this->assertArrayHasKey('calinski_harabasz', $metrics);
    }

    private function calculateClusteringAccuracy(array $clusters): float
    {
        // Simplified accuracy calculation based on intra-cluster distance
        $intraClusterDistances = [];
        $interClusterDistances = [];

        foreach ($clusters as $cluster) {
            $clusterId = $cluster['cluster'];
            $features = $cluster['features'];

            // Calculate intra-cluster distances
            foreach ($clusters as $otherCluster) {
                if ($otherCluster['cluster'] === $clusterId && $otherCluster['id'] !== $cluster['id']) {
                    $distance = $this->calculateEuclideanDistance($features, $otherCluster['features']);
                    $intraClusterDistances[] = $distance;
                }
            }

            // Calculate inter-cluster distances
            foreach ($clusters as $otherCluster) {
                if ($otherCluster['cluster'] !== $clusterId) {
                    $distance = $this->calculateEuclideanDistance($features, $otherCluster['features']);
                    $interClusterDistances[] = $distance;
                }
            }
        }

        $avgIntraDistance = array_sum($intraClusterDistances) / count($intraClusterDistances);
        $avgInterDistance = array_sum($interClusterDistances) / count($interClusterDistances);

        return $avgInterDistance / ($avgIntraDistance + $avgInterDistance);
    }

    private function calculateSilhouetteScore(array $clusters): float
    {
        $scores = [];

        foreach ($clusters as $cluster) {
            $clusterId = $cluster['cluster'];
            $features = $cluster['features'];

            // Calculate average distance to other points in same cluster
            $intraDistances = [];
            foreach ($clusters as $otherCluster) {
                if ($otherCluster['cluster'] === $clusterId && $otherCluster['id'] !== $cluster['id']) {
                    $distance = $this->calculateEuclideanDistance($features, $otherCluster['features']);
                    $intraDistances[] = $distance;
                }
            }
            $a = count($intraDistances) > 0 ? array_sum($intraDistances) / count($intraDistances) : 0;

            // Calculate average distance to points in nearest other cluster
            $clusterDistances = [];
            $uniqueClusters = array_unique(array_column($clusters, 'cluster'));
            foreach ($uniqueClusters as $otherClusterId) {
                if ($otherClusterId !== $clusterId) {
                    $distances = [];
                    foreach ($clusters as $otherCluster) {
                        if ($otherCluster['cluster'] === $otherClusterId) {
                            $distance = $this->calculateEuclideanDistance($features, $otherCluster['features']);
                            $distances[] = $distance;
                        }
                    }
                    if (count($distances) > 0) {
                        $clusterDistances[] = array_sum($distances) / count($distances);
                    }
                }
            }
            $b = count($clusterDistances) > 0 ? min($clusterDistances) : 0;

            $scores[] = $b > $a ? ($b - $a) / max($a, $b) : 0;
        }

        return array_sum($scores) / count($scores);
    }

    private function calculateInertia(array $clusters): float
    {
        $centroids = $this->calculateClusterCentroids($clusters);
        $inertia = 0;

        foreach ($clusters as $cluster) {
            $clusterId = $cluster['cluster'];
            $features = $cluster['features'];
            $centroid = $centroids[$clusterId];

            $distance = $this->calculateEuclideanDistance($features, $centroid);
            $inertia += $distance * $distance;
        }

        return $inertia;
    }

    private function calculateDaviesBouldinScore(array $clusters): float
    {
        $centroids = $this->calculateClusterCentroids($clusters);
        $uniqueClusters = array_unique(array_column($clusters, 'cluster'));
        $scores = [];

        foreach ($uniqueClusters as $clusterId) {
            $maxRatio = 0;

            foreach ($uniqueClusters as $otherClusterId) {
                if ($otherClusterId !== $clusterId) {
                    $intraDistance = $this->calculateClusterIntraDistance($clusters, $clusterId, $centroids[$clusterId]);
                    $otherIntraDistance = $this->calculateClusterIntraDistance($clusters, $otherClusterId, $centroids[$otherClusterId]);
                    $interDistance = $this->calculateEuclideanDistance($centroids[$clusterId], $centroids[$otherClusterId]);

                    $ratio = ($intraDistance + $otherIntraDistance) / $interDistance;
                    $maxRatio = max($maxRatio, $ratio);
                }
            }

            $scores[] = $maxRatio;
        }

        return array_sum($scores) / count($scores);
    }

    private function calculateCalinskiHarabaszScore(array $clusters): float
    {
        $centroids = $this->calculateClusterCentroids($clusters);
        $overallCentroid = $this->calculateOverallCentroid($clusters);

        $betweenClusterSum = 0;
        $withinClusterSum = 0;

        foreach ($uniqueClusters = array_unique(array_column($clusters, 'cluster')) as $clusterId) {
            $clusterPoints = array_filter($clusters, function ($c) use ($clusterId) {
                return $c['cluster'] === $clusterId;
            });

            $clusterSize = count($clusterPoints);
            $centroid = $centroids[$clusterId];

            // Between-cluster sum of squares
            $betweenClusterSum += $clusterSize * $this->calculateEuclideanDistance($centroid, $overallCentroid) ** 2;

            // Within-cluster sum of squares
            foreach ($clusterPoints as $point) {
                $withinClusterSum += $this->calculateEuclideanDistance($point['features'], $centroid) ** 2;
            }
        }

        $n = count($clusters);
        $k = count($uniqueClusters);

        return ($betweenClusterSum / ($k - 1)) / ($withinClusterSum / ($n - $k));
    }

    private function calculateClusterSeparation(array $clusters): float
    {
        $centroids = $this->calculateClusterCentroids($clusters);
        $uniqueClusters = array_unique(array_column($clusters, 'cluster'));
        $minDistance = PHP_FLOAT_MAX;

        foreach ($uniqueClusters as $clusterId) {
            foreach ($uniqueClusters as $otherClusterId) {
                if ($otherClusterId !== $clusterId) {
                    $distance = $this->calculateEuclideanDistance($centroids[$clusterId], $centroids[$otherClusterId]);
                    $minDistance = min($minDistance, $distance);
                }
            }
        }

        return $minDistance;
    }

    private function calculateClusterCohesion(array $clusters): float
    {
        $centroids = $this->calculateClusterCentroids($clusters);
        $totalCohesion = 0;

        foreach ($clusters as $cluster) {
            $clusterId = $cluster['cluster'];
            $features = $cluster['features'];
            $centroid = $centroids[$clusterId];

            $distance = $this->calculateEuclideanDistance($features, $centroid);
            $totalCohesion += $distance;
        }

        return $totalCohesion / count($clusters);
    }

    private function findOptimalClusterNumber(array $data): int
    {
        $maxK = min(6, count($data));
        $bestK = 2;
        $bestScore = 0;

        for ($k = 2; $k <= $maxK; $k++) {
            // Simulate clustering with k clusters
            $clusters = $this->simulateClustering($data, $k);
            $score = $this->calculateSilhouetteScore($clusters);

            if ($score > $bestScore) {
                $bestScore = $score;
                $bestK = $k;
            }
        }

        return $bestK;
    }

    private function calculateClusterStability(array $clusters): float
    {
        // Simplified stability calculation
        $clusterSizes = $this->calculateClusterSizes($clusters);
        $expectedSize = count($clusters) / count($clusterSizes);

        $variance = 0;
        foreach ($clusterSizes as $size) {
            $variance += pow($size - $expectedSize, 2);
        }

        $variance /= count($clusterSizes);
        $coefficientOfVariation = sqrt($variance) / $expectedSize;

        return 1 - min(1, $coefficientOfVariation);
    }

    private function calculateClusterPurity(array $clusters): float
    {
        $clusterLabels = [];
        foreach ($clusters as $cluster) {
            $clusterId = $cluster['cluster'];
            $label = $cluster['label'];

            if (! isset($clusterLabels[$clusterId])) {
                $clusterLabels[$clusterId] = [];
            }
            $clusterLabels[$clusterId][] = $label;
        }

        $totalCorrect = 0;
        $totalPoints = count($clusters);

        foreach ($clusterLabels as $labels) {
            $labelCounts = array_count_values($labels);
            $maxCount = max($labelCounts);
            $totalCorrect += $maxCount;
        }

        return $totalCorrect / $totalPoints;
    }

    private function calculateClusterEntropy(array $clusters): float
    {
        $clusterLabels = [];
        foreach ($clusters as $cluster) {
            $clusterId = $cluster['cluster'];
            $label = $cluster['label'];

            if (! isset($clusterLabels[$clusterId])) {
                $clusterLabels[$clusterId] = [];
            }
            $clusterLabels[$clusterId][] = $label;
        }

        $totalEntropy = 0;
        $totalPoints = count($clusters);

        foreach ($clusterLabels as $labels) {
            $labelCounts = array_count_values($labels);
            $clusterSize = count($labels);

            $entropy = 0;
            foreach ($labelCounts as $count) {
                $probability = $count / $clusterSize;
                $entropy -= $probability * log($probability, 2);
            }

            $totalEntropy += $entropy * ($clusterSize / $totalPoints);
        }

        return $totalEntropy;
    }

    private function calculateClusterCentroids(array $clusters): array
    {
        $centroids = [];
        $clusterSums = [];
        $clusterCounts = [];

        foreach ($clusters as $cluster) {
            $clusterId = $cluster['cluster'];
            $features = $cluster['features'];

            if (! isset($clusterSums[$clusterId])) {
                $clusterSums[$clusterId] = array_fill(0, count($features), 0);
                $clusterCounts[$clusterId] = 0;
            }

            for ($i = 0; $i < count($features); $i++) {
                $clusterSums[$clusterId][$i] += $features[$i];
            }
            $clusterCounts[$clusterId]++;
        }

        foreach ($clusterSums as $clusterId => $sums) {
            $centroids[$clusterId] = array_map(function ($sum) use ($clusterId, $clusterCounts) {
                return $sum / $clusterCounts[$clusterId];
            }, $sums);
        }

        return $centroids;
    }

    private function calculateClusterSizes(array $clusters): array
    {
        $sizes = [];
        foreach ($clusters as $cluster) {
            $clusterId = $cluster['cluster'];
            $sizes[$clusterId] = ($sizes[$clusterId] ?? 0) + 1;
        }

        return array_values($sizes);
    }

    private function calculateClusterQualityMetrics(array $clusters): array
    {
        return [
            'silhouette_score' => $this->calculateSilhouetteScore($clusters),
            'inertia' => $this->calculateInertia($clusters),
            'davies_bouldin' => $this->calculateDaviesBouldinScore($clusters),
            'calinski_harabasz' => $this->calculateCalinskiHarabaszScore($clusters),
        ];
    }

    private function calculateEuclideanDistance(array $point1, array $point2): float
    {
        $sum = 0;
        for ($i = 0; $i < count($point1); $i++) {
            $sum += pow($point1[$i] - $point2[$i], 2);
        }

        return sqrt($sum);
    }

    private function calculateClusterIntraDistance(array $clusters, int $clusterId, array $centroid): float
    {
        $distances = [];
        foreach ($clusters as $cluster) {
            if ($cluster['cluster'] === $clusterId) {
                $distances[] = $this->calculateEuclideanDistance($cluster['features'], $centroid);
            }
        }

        return count($distances) > 0 ? array_sum($distances) / count($distances) : 0;
    }

    private function calculateOverallCentroid(array $clusters): array
    {
        $sums = [];
        $count = count($clusters);

        foreach ($clusters as $cluster) {
            $features = $cluster['features'];
            for ($i = 0; $i < count($features); $i++) {
                $sums[$i] = ($sums[$i] ?? 0) + $features[$i];
            }
        }

        return array_map(function ($sum) use ($count) {
            return $sum / $count;
        }, $sums);
    }

    private function simulateClustering(array $data, int $k): array
    {
        // Simplified clustering simulation
        $clusters = [];
        $clusterSize = count($data) / $k;

        for ($i = 0; $i < count($data); $i++) {
            $clusters[] = [
                'id' => $i + 1,
                'cluster' => floor($i / $clusterSize),
                'features' => $data[$i]['features'],
            ];
        }

        return $clusters;
    }
}
