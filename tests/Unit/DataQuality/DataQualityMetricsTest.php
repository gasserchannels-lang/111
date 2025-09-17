<?php

namespace Tests\Unit\DataQuality;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\CoversNothing;

/**
 * اختبارات مقاييس جودة البيانات
 *
 * هذا الكلاس يختبر مقاييس جودة البيانات المختلفة
 * ويحذر من مشاكل جودة البيانات التي قد تؤثر على دقة النتائج
 *
 * ⚠️ تحذير: يجب مراقبة جودة البيانات بانتظام لضمان دقة النتائج
 */
class DataQualityMetricsTest extends TestCase
{
    #[Test]
    #[CoversNothing]
    public function it_calculates_completeness_metrics(): void
    {
        // ⚠️ تحذير: مقاييس الاكتمال يجب أن تكون دقيقة
        $data = [
            ['id' => 1, 'name' => 'Product A', 'price' => 100.00, 'description' => 'A great product'],
            ['id' => 2, 'name' => 'Product B', 'price' => 200.00, 'description' => 'Another great product'],
            ['id' => 3, 'name' => 'Product C', 'price' => 300.00, 'description' => null], // Missing description
            ['id' => 4, 'name' => 'Product D', 'price' => 400.00, 'description' => 'Yet another product']
        ];

        $requiredFields = ['id', 'name', 'price', 'description'];
        $completeness = $this->calculateCompletenessMetrics($data, $requiredFields);

        $this->assertEquals(93.75, $completeness['overall_completeness'], '⚠️ تحذير: حساب الاكتمال غير صحيح!');
        $this->assertEquals(100.0, $completeness['field_completeness']['id'], '⚠️ تحذير: اكتمال حقل ID غير صحيح!');
        $this->assertEquals(100.0, $completeness['field_completeness']['name'], '⚠️ تحذير: اكتمال حقل الاسم غير صحيح!');
        $this->assertEquals(100.0, $completeness['field_completeness']['price'], '⚠️ تحذير: اكتمال حقل السعر غير صحيح!');
        $this->assertEquals(75.0, $completeness['field_completeness']['description'], '⚠️ تحذير: اكتمال حقل الوصف غير صحيح!');
    }

    #[Test]
    #[CoversNothing]
    public function it_calculates_accuracy_metrics(): void
    {
        // ⚠️ تحذير: مقاييس الدقة يجب أن تكون عالية لضمان موثوقية البيانات
        $data = [
            ['id' => 1, 'price' => 100.00, 'expected_price' => 100.00],
            ['id' => 2, 'price' => 200.00, 'expected_price' => 200.00],
            ['id' => 3, 'price' => 300.00, 'expected_price' => 295.00], // Inaccurate
            ['id' => 4, 'price' => 400.00, 'expected_price' => 400.00]
        ];

        $accuracy = $this->calculateAccuracyMetrics($data, 'price', 'expected_price');

        $this->assertEquals(75.0, $accuracy['overall_accuracy'], '⚠️ تحذير: دقة البيانات منخفضة! يجب أن تكون أعلى من 90%');
        $this->assertEquals(3, $accuracy['accurate_records'], '⚠️ تحذير: عدد السجلات الدقيقة غير صحيح!');
        $this->assertEquals(1, $accuracy['inaccurate_records'], '⚠️ تحذير: عدد السجلات غير الدقيقة غير صحيح!');
    }

    #[Test]
    #[CoversNothing]
    public function it_calculates_consistency_metrics(): void
    {
        // ⚠️ تحذير: الاتساق في البيانات ضروري لضمان التماسك
        $data = [
            ['id' => 1, 'currency' => 'USD', 'price' => 100.00],
            ['id' => 2, 'currency' => 'USD', 'price' => 200.00],
            ['id' => 3, 'currency' => 'EUR', 'price' => 300.00], // Inconsistent currency
            ['id' => 4, 'currency' => 'USD', 'price' => 400.00]
        ];

        $consistency = $this->calculateConsistencyMetrics($data, 'currency');

        $this->assertEquals(75.0, $consistency['overall_consistency'], '⚠️ تحذير: اتساق البيانات منخفض! يجب توحيد العملات');
        $this->assertEquals(3, $consistency['consistent_records'], '⚠️ تحذير: عدد السجلات المتسقة غير صحيح!');
        $this->assertEquals(1, $consistency['inconsistent_records'], '⚠️ تحذير: عدد السجلات غير المتسقة غير صحيح!');
    }

    #[Test]
    #[CoversNothing]
    public function it_calculates_timeliness_metrics(): void
    {
        // ⚠️ تحذير: مقاييس التوقيت يجب أن تكون دقيقة
        $data = [
            ['id' => 1, 'updated_at' => date('Y-m-d H:i:s')],
            ['id' => 2, 'updated_at' => date('Y-m-d H:i:s', strtotime('-1 hour'))],
            ['id' => 3, 'updated_at' => date('Y-m-d H:i:s', strtotime('-1 day'))],
            ['id' => 4, 'updated_at' => date('Y-m-d H:i:s', strtotime('-1 week'))]
        ];

        $timeliness = $this->calculateTimelinessMetrics($data, 'updated_at', 24); // 24 hours threshold

        $this->assertEquals(75.0, $timeliness['overall_timeliness'], '⚠️ تحذير: حساب التوقيت غير صحيح!');
        $this->assertEquals(3, $timeliness['timely_records'], '⚠️ تحذير: عدد السجلات الحديثة غير صحيح!');
        $this->assertEquals(1, $timeliness['stale_records'], '⚠️ تحذير: عدد السجلات القديمة غير صحيح!');
    }

    #[Test]
    #[CoversNothing]
    public function it_calculates_validity_metrics(): void
    {
        // ⚠️ تحذير: صحة البيانات ضرورية لضمان جودة المدخلات
        $data = [
            ['id' => 1, 'email' => 'user@example.com', 'phone' => '+1-555-123-4567'],
            ['id' => 2, 'email' => 'admin@test.org', 'phone' => '+44-20-7946-0958'],
            ['id' => 3, 'email' => 'invalid-email', 'phone' => '123'], // Invalid email and phone
            ['id' => 4, 'email' => 'user2@example.com', 'phone' => '+1-555-987-6543']
        ];

        $validity = $this->calculateValidityMetrics($data, [
            'email' => 'email',
            'phone' => 'phone'
        ]);

        $this->assertEquals(75.0, $validity['overall_validity'], '⚠️ تحذير: صحة البيانات منخفضة! يجب إصلاح البريد الإلكتروني والهاتف غير الصحيح');
        $this->assertEquals(3, $validity['valid_records'], '⚠️ تحذير: عدد السجلات الصحيحة غير صحيح!');
        $this->assertEquals(1, $validity['invalid_records'], '⚠️ تحذير: عدد السجلات غير الصحيحة غير صحيح!');
    }

    #[Test]
    #[CoversNothing]
    public function it_calculates_uniqueness_metrics(): void
    {
        // ⚠️ تحذير: تفرد البيانات مهم لمنع التكرارات
        $data = [
            ['id' => 1, 'email' => 'user1@example.com'],
            ['id' => 2, 'email' => 'user2@example.com'],
            ['id' => 3, 'email' => 'user1@example.com'], // Duplicate email
            ['id' => 4, 'email' => 'user3@example.com']
        ];

        $uniqueness = $this->calculateUniquenessMetrics($data, 'email');

        $this->assertEquals(75.0, $uniqueness['overall_uniqueness'], '⚠️ تحذير: تفرد البيانات منخفض! يوجد تكرارات في البريد الإلكتروني');
        $this->assertEquals(3, $uniqueness['unique_records'], '⚠️ تحذير: عدد السجلات الفريدة غير صحيح!');
        $this->assertEquals(1, $uniqueness['duplicate_records'], '⚠️ تحذير: عدد السجلات المكررة غير صحيح!');
    }

    #[Test]
    #[CoversNothing]
    public function it_calculates_integrity_metrics(): void
    {
        // ⚠️ تحذير: مقاييس التكامل يجب أن تكون دقيقة
        $parentData = [
            ['id' => 1, 'name' => 'Category A'],
            ['id' => 2, 'name' => 'Category B']
        ];

        $childData = [
            ['id' => 1, 'name' => 'Product 1', 'category_id' => 1],
            ['id' => 2, 'name' => 'Product 2', 'category_id' => 2],
            ['id' => 3, 'name' => 'Product 3', 'category_id' => 3] // Invalid category_id
        ];

        $integrity = $this->calculateIntegrityMetrics($parentData, $childData, 'id', 'category_id');

        $this->assertEqualsWithDelta(66.7, $integrity['overall_integrity'], 0.1, '⚠️ تحذير: حساب التكامل غير صحيح!');
        $this->assertEquals(2, $integrity['valid_relationships'], '⚠️ تحذير: عدد العلاقات الصحيحة غير صحيح!');
        $this->assertEquals(1, $integrity['invalid_relationships'], '⚠️ تحذير: عدد العلاقات الخاطئة غير صحيح!');
    }

    #[Test]
    #[CoversNothing]
    public function it_calculates_overall_quality_score(): void
    {
        // ⚠️ تحذير: النقاط الإجمالية لجودة البيانات يجب أن تكون عالية
        $data = [
            ['id' => 1, 'name' => 'Product A', 'price' => 100.00, 'description' => 'A great product', 'email' => 'user@example.com'],
            ['id' => 2, 'name' => 'Product B', 'price' => 200.00, 'description' => 'Another great product', 'email' => 'admin@test.org'],
            ['id' => 3, 'name' => 'Product C', 'price' => 300.00, 'description' => null, 'email' => 'invalid-email'], // Missing description and invalid email
            ['id' => 4, 'name' => 'Product D', 'price' => 400.00, 'description' => 'Yet another product', 'email' => 'user2@example.com']
        ];

        $qualityScore = $this->calculateOverallQualityScore($data);

        $this->assertGreaterThan(0, $qualityScore, '⚠️ تحذير: نقاط جودة البيانات يجب أن تكون أكبر من صفر!');
        $this->assertLessThanOrEqual(100, $qualityScore, '⚠️ تحذير: نقاط جودة البيانات يجب أن تكون أقل من أو تساوي 100!');

        // تحذير إضافي إذا كانت النقاط منخفضة
        if ($qualityScore < 80) {
            // ⚠️ تحذير: نقاط جودة البيانات منخفضة جداً! يجب تحسين جودة البيانات
        }
    }

    #[Test]
    #[CoversNothing]
    public function it_generates_quality_report(): void
    {
        // ⚠️ تحذير: تقرير جودة البيانات يجب أن يكون شاملاً ومفصلاً
        $data = [
            ['id' => 1, 'name' => 'Product A', 'price' => 100.00, 'description' => 'A great product'],
            ['id' => 2, 'name' => 'Product B', 'price' => 200.00, 'description' => 'Another great product'],
            ['id' => 3, 'name' => 'Product C', 'price' => 300.00, 'description' => null],
            ['id' => 4, 'name' => 'Product D', 'price' => 400.00, 'description' => 'Yet another product']
        ];

        $report = $this->generateQualityReport($data);

        $this->assertArrayHasKey('overall_score', $report, '⚠️ تحذير: تقرير جودة البيانات مفقود النقاط الإجمالية!');
        $this->assertArrayHasKey('completeness', $report, '⚠️ تحذير: تقرير جودة البيانات مفقود مقاييس الاكتمال!');
        $this->assertArrayHasKey('accuracy', $report, '⚠️ تحذير: تقرير جودة البيانات مفقود مقاييس الدقة!');
        $this->assertArrayHasKey('consistency', $report, '⚠️ تحذير: تقرير جودة البيانات مفقود مقاييس الاتساق!');
        $this->assertArrayHasKey('timeliness', $report, '⚠️ تحذير: تقرير جودة البيانات مفقود مقاييس التوقيت!');
        $this->assertArrayHasKey('validity', $report, '⚠️ تحذير: تقرير جودة البيانات مفقود مقاييس الصحة!');
        $this->assertArrayHasKey('uniqueness', $report, '⚠️ تحذير: تقرير جودة البيانات مفقود مقاييس التفرد!');
        $this->assertArrayHasKey('integrity', $report, '⚠️ تحذير: تقرير جودة البيانات مفقود مقاييس التكامل!');
        $this->assertArrayHasKey('recommendations', $report, '⚠️ تحذير: تقرير جودة البيانات مفقود التوصيات!');
    }

    #[Test]
    #[CoversNothing]
    public function it_calculates_field_level_metrics(): void
    {
        // ⚠️ تحذير: مقاييس مستوى الحقل مهمة لتحديد المشاكل الدقيقة
        $data = [
            ['id' => 1, 'name' => 'Product A', 'price' => 100.00, 'description' => 'A great product'],
            ['id' => 2, 'name' => 'Product B', 'price' => 200.00, 'description' => 'Another great product'],
            ['id' => 3, 'name' => 'Product C', 'price' => 300.00, 'description' => null],
            ['id' => 4, 'name' => 'Product D', 'price' => 400.00, 'description' => 'Yet another product']
        ];

        $fieldMetrics = $this->calculateFieldLevelMetrics($data);

        $this->assertArrayHasKey('id', $fieldMetrics, '⚠️ تحذير: مقاييس حقل ID مفقودة!');
        $this->assertArrayHasKey('name', $fieldMetrics, '⚠️ تحذير: مقاييس حقل الاسم مفقودة!');
        $this->assertArrayHasKey('price', $fieldMetrics, '⚠️ تحذير: مقاييس حقل السعر مفقودة!');
        $this->assertArrayHasKey('description', $fieldMetrics, '⚠️ تحذير: مقاييس حقل الوصف مفقودة!');

        $this->assertEquals(100.0, $fieldMetrics['id']['completeness'], '⚠️ تحذير: اكتمال حقل ID غير صحيح!');
        $this->assertEquals(100.0, $fieldMetrics['name']['completeness'], '⚠️ تحذير: اكتمال حقل الاسم غير صحيح!');
        $this->assertEquals(100.0, $fieldMetrics['price']['completeness'], '⚠️ تحذير: اكتمال حقل السعر غير صحيح!');
        $this->assertEquals(75.0, $fieldMetrics['description']['completeness'], '⚠️ تحذير: اكتمال حقل الوصف غير صحيح!');
    }

    #[Test]
    #[CoversNothing]
    public function it_calculates_trend_metrics(): void
    {
        $historicalData = [
            ['date' => '2024-01-01', 'quality_score' => 85.0],
            ['date' => '2024-01-02', 'quality_score' => 87.0],
            ['date' => '2024-01-03', 'quality_score' => 89.0],
            ['date' => '2024-01-04', 'quality_score' => 91.0],
            ['date' => '2024-01-05', 'quality_score' => 88.0]
        ];

        $trend = $this->calculateTrendMetrics($historicalData, 'quality_score');

        $this->assertArrayHasKey('trend_direction', $trend);
        $this->assertArrayHasKey('trend_strength', $trend);
        $this->assertArrayHasKey('average_change', $trend);
        $this->assertArrayHasKey('volatility', $trend);
    }

    #[Test]
    #[CoversNothing]
    public function it_calculates_benchmark_metrics(): void
    {
        // ⚠️ تحذير: مقاييس المعيار مهمة لمقارنة الأداء مع الصناعة
        $data = [
            ['id' => 1, 'name' => 'Product A', 'price' => 100.00, 'description' => 'A great product'],
            ['id' => 2, 'name' => 'Product B', 'price' => 200.00, 'description' => 'Another great product'],
            ['id' => 3, 'name' => 'Product C', 'price' => 300.00, 'description' => null],
            ['id' => 4, 'name' => 'Product D', 'price' => 400.00, 'description' => 'Yet another product']
        ];

        $benchmark = $this->calculateBenchmarkMetrics($data);

        $this->assertArrayHasKey('current_score', $benchmark, '⚠️ تحذير: النقاط الحالية مفقودة في مقاييس المعيار!');
        $this->assertArrayHasKey('industry_average', $benchmark, '⚠️ تحذير: متوسط الصناعة مفقود في مقاييس المعيار!');
        $this->assertArrayHasKey('performance_gap', $benchmark, '⚠️ تحذير: فجوة الأداء مفقودة في مقاييس المعيار!');
        $this->assertArrayHasKey('performance_level', $benchmark, '⚠️ تحذير: مستوى الأداء مفقود في مقاييس المعيار!');

        // تحذير إضافي إذا كان الأداء منخفض
        if (isset($benchmark['performance_level']) && $benchmark['performance_level'] === 'below_average') {
            // ⚠️ تحذير: الأداء أقل من المتوسط! يجب تحسين جودة البيانات
        }
    }

    private function calculateCompletenessMetrics(array $data, array $requiredFields): array
    {
        $totalRecords = count($data);
        $fieldCompleteness = [];
        $overallCompleteness = 0;

        foreach ($requiredFields as $field) {
            $completeRecords = 0;
            foreach ($data as $record) {
                if (isset($record[$field]) && $record[$field] !== null && $record[$field] !== '') {
                    $completeRecords++;
                }
            }
            $fieldCompleteness[$field] = ($completeRecords / $totalRecords) * 100;
        }

        $overallCompleteness = array_sum($fieldCompleteness) / count($fieldCompleteness);

        return [
            'overall_completeness' => $overallCompleteness,
            'field_completeness' => $fieldCompleteness,
            'total_records' => $totalRecords
        ];
    }

    private function calculateAccuracyMetrics(array $data, string $actualField, string $expectedField): array
    {
        $totalRecords = count($data);
        $accurateRecords = 0;

        foreach ($data as $record) {
            if (isset($record[$actualField]) && isset($record[$expectedField])) {
                if (abs($record[$actualField] - $record[$expectedField]) <= 0.01) {
                    $accurateRecords++;
                }
            }
        }

        $overallAccuracy = ($accurateRecords / $totalRecords) * 100;

        return [
            'overall_accuracy' => $overallAccuracy,
            'accurate_records' => $accurateRecords,
            'inaccurate_records' => $totalRecords - $accurateRecords,
            'total_records' => $totalRecords
        ];
    }

    private function calculateConsistencyMetrics(array $data, string $field): array
    {
        $totalRecords = count($data);
        $values = array_column($data, $field);
        $uniqueValues = array_unique($values);
        $mostCommonValue = array_keys(array_count_values($values), max(array_count_values($values)))[0];

        $consistentRecords = 0;
        foreach ($data as $record) {
            if ($record[$field] === $mostCommonValue) {
                $consistentRecords++;
            }
        }

        $overallConsistency = ($consistentRecords / $totalRecords) * 100;

        return [
            'overall_consistency' => $overallConsistency,
            'consistent_records' => $consistentRecords,
            'inconsistent_records' => $totalRecords - $consistentRecords,
            'total_records' => $totalRecords
        ];
    }

    private function calculateTimelinessMetrics(array $data, string $dateField, int $thresholdHours): array
    {
        $totalRecords = count($data);
        $timelyRecords = 0;
        $threshold = $thresholdHours * 3600; // Convert to seconds

        foreach ($data as $record) {
            if (isset($record[$dateField])) {
                $lastUpdate = strtotime($record[$dateField]);
                if (time() - $lastUpdate <= $threshold) {
                    $timelyRecords++;
                }
            }
        }

        $overallTimeliness = ($timelyRecords / $totalRecords) * 100;

        return [
            'overall_timeliness' => $overallTimeliness,
            'timely_records' => $timelyRecords,
            'stale_records' => $totalRecords - $timelyRecords,
            'total_records' => $totalRecords
        ];
    }

    private function calculateValidityMetrics(array $data, array $fieldValidations): array
    {
        $totalRecords = count($data);
        $validRecords = 0;

        foreach ($data as $record) {
            $isValid = true;
            foreach ($fieldValidations as $field => $validation) {
                if (isset($record[$field])) {
                    switch ($validation) {
                        case 'email':
                            if (!filter_var($record[$field], FILTER_VALIDATE_EMAIL)) {
                                $isValid = false;
                            }
                            break;
                        case 'phone':
                            if (!preg_match('/^[\+]?[1-9][\d]{0,15}$/', preg_replace('/[^\d+]/', '', $record[$field]))) {
                                $isValid = false;
                            }
                            break;
                    }
                }
            }
            if ($isValid) {
                $validRecords++;
            }
        }

        $overallValidity = ($validRecords / $totalRecords) * 100;

        return [
            'overall_validity' => $overallValidity,
            'valid_records' => $validRecords,
            'invalid_records' => $totalRecords - $validRecords,
            'total_records' => $totalRecords
        ];
    }

    private function calculateUniquenessMetrics(array $data, string $field): array
    {
        $totalRecords = count($data);
        $values = array_column($data, $field);
        $uniqueValues = array_unique($values);
        $uniqueRecords = count($uniqueValues);
        $duplicateRecords = $totalRecords - $uniqueRecords;

        $overallUniqueness = ($uniqueRecords / $totalRecords) * 100;

        return [
            'overall_uniqueness' => $overallUniqueness,
            'unique_records' => $uniqueRecords,
            'duplicate_records' => $duplicateRecords,
            'total_records' => $totalRecords
        ];
    }

    private function calculateIntegrityMetrics(array $parentData, array $childData, string $parentKey, string $foreignKey): array
    {
        $totalRelationships = count($childData);
        $parentIds = array_column($parentData, $parentKey);
        $validRelationships = 0;

        foreach ($childData as $record) {
            if (in_array($record[$foreignKey], $parentIds)) {
                $validRelationships++;
            }
        }

        $overallIntegrity = ($validRelationships / $totalRelationships) * 100;

        return [
            'overall_integrity' => $overallIntegrity,
            'valid_relationships' => $validRelationships,
            'invalid_relationships' => $totalRelationships - $validRelationships,
            'total_relationships' => $totalRelationships
        ];
    }

    private function calculateOverallQualityScore(array $data): float
    {
        $completeness = $this->calculateCompletenessMetrics($data, ['id', 'name', 'price', 'description']);
        $validity = $this->calculateValidityMetrics($data, ['email' => 'email']);
        $uniqueness = $this->calculateUniquenessMetrics($data, 'email');

        $overallScore = ($completeness['overall_completeness'] + $validity['overall_validity'] + $uniqueness['overall_uniqueness']) / 3;

        return $overallScore;
    }

    private function generateQualityReport(array $data): array
    {
        $completeness = $this->calculateCompletenessMetrics($data, ['id', 'name', 'price', 'description']);
        $validity = $this->calculateValidityMetrics($data, ['email' => 'email']);
        $uniqueness = $this->calculateUniquenessMetrics($data, 'email');

        $overallScore = ($completeness['overall_completeness'] + $validity['overall_validity'] + $uniqueness['overall_uniqueness']) / 3;

        $recommendations = [];
        if ($completeness['overall_completeness'] < 90) {
            $recommendations[] = 'Improve data completeness by filling missing fields';
        }
        if ($validity['overall_validity'] < 90) {
            $recommendations[] = 'Improve data validity by fixing invalid formats';
        }
        if ($uniqueness['overall_uniqueness'] < 90) {
            $recommendations[] = 'Improve data uniqueness by removing duplicates';
        }

        return [
            'overall_score' => $overallScore,
            'completeness' => $completeness,
            'accuracy' => ['overall_accuracy' => 100.0], // Placeholder
            'consistency' => ['overall_consistency' => 100.0], // Placeholder
            'timeliness' => ['overall_timeliness' => 100.0], // Placeholder
            'validity' => $validity,
            'uniqueness' => $uniqueness,
            'integrity' => ['overall_integrity' => 100.0], // Placeholder
            'recommendations' => $recommendations,
            'generated_at' => date('Y-m-d H:i:s')
        ];
    }

    private function calculateFieldLevelMetrics(array $data): array
    {
        $fieldMetrics = [];
        $fields = array_keys($data[0] ?? []);

        foreach ($fields as $field) {
            $completeness = $this->calculateCompletenessMetrics($data, [$field]);
            $fieldMetrics[$field] = [
                'completeness' => $completeness['field_completeness'][$field],
                'null_count' => $this->countNullValues($data, $field),
                'unique_count' => count(array_unique(array_column($data, $field)))
            ];
        }

        return $fieldMetrics;
    }

    private function countNullValues(array $data, string $field): int
    {
        $nullCount = 0;
        foreach ($data as $record) {
            if (!isset($record[$field]) || $record[$field] === null || $record[$field] === '') {
                $nullCount++;
            }
        }
        return $nullCount;
    }

    private function calculateTrendMetrics(array $historicalData, string $valueField): array
    {
        $values = array_column($historicalData, $valueField);
        $n = count($values);

        if ($n < 2) {
            return [
                'trend_direction' => 'stable',
                'trend_strength' => 0,
                'average_change' => 0,
                'volatility' => 0
            ];
        }

        $changes = [];
        for ($i = 1; $i < $n; $i++) {
            $changes[] = $values[$i] - $values[$i - 1];
        }

        $averageChange = array_sum($changes) / count($changes);
        $volatility = $this->calculateStandardDeviation($changes);

        $trendDirection = 'stable';
        if ($averageChange > 0.1) {
            $trendDirection = 'increasing';
        } elseif ($averageChange < -0.1) {
            $trendDirection = 'decreasing';
        }

        $trendStrength = abs($averageChange) / $volatility;

        return [
            'trend_direction' => $trendDirection,
            'trend_strength' => $trendStrength,
            'average_change' => $averageChange,
            'volatility' => $volatility
        ];
    }

    private function calculateStandardDeviation(array $values): float
    {
        $mean = array_sum($values) / count($values);
        $variance = array_sum(array_map(function ($x) use ($mean) {
            return pow($x - $mean, 2);
        }, $values)) / count($values);

        return sqrt($variance);
    }

    private function calculateBenchmarkMetrics(array $data): array
    {
        $currentScore = $this->calculateOverallQualityScore($data);
        $industryAverage = 85.0; // Placeholder industry average
        $performanceGap = $currentScore - $industryAverage;

        $performanceLevel = 'below_average';
        if ($performanceGap > 10) {
            $performanceLevel = 'excellent';
        } elseif ($performanceGap > 5) {
            $performanceLevel = 'above_average';
        } elseif ($performanceGap > -5) {
            $performanceLevel = 'average';
        }

        return [
            'current_score' => $currentScore,
            'industry_average' => $industryAverage,
            'performance_gap' => $performanceGap,
            'performance_level' => $performanceLevel
        ];
    }
}
