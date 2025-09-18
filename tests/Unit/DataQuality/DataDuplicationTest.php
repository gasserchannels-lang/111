<?php

namespace Tests\Unit\DataQuality;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;

class DataDuplicationTest extends TestCase
{
    #[Test]
    public function it_detects_exact_duplicates(): void
    {
        $data = [
            ['id' => 1, 'name' => 'Product A', 'price' => 100.00],
            ['id' => 2, 'name' => 'Product B', 'price' => 200.00],
            ['id' => 3, 'name' => 'Product A', 'price' => 100.00], // Exact duplicate
            ['id' => 4, 'name' => 'Product C', 'price' => 300.00]
        ];

        $duplicates = $this->detectExactDuplicates($data);
        $this->assertCount(1, $duplicates);
        $this->assertEquals(3, $duplicates[0]['id']);
    }

    #[Test]
    public function it_detects_near_duplicates(): void
    {
        $data = [
            ['id' => 1, 'name' => 'Product A', 'price' => 100.00],
            ['id' => 2, 'name' => 'Product A ', 'price' => 100.00], // Near duplicate (extra space)
            ['id' => 3, 'name' => 'Product B', 'price' => 200.00],
            ['id' => 4, 'name' => 'product a', 'price' => 100.00] // Near duplicate (case difference)
        ];

        $duplicates = $this->detectNearDuplicates($data, ['name', 'price']);
        $this->assertCount(3, $duplicates);
    }

    #[Test]
    public function it_detects_duplicate_emails(): void
    {
        $data = [
            ['id' => 1, 'name' => 'John Doe', 'email' => 'john@example.com'],
            ['id' => 2, 'name' => 'Jane Smith', 'email' => 'jane@example.com'],
            ['id' => 3, 'name' => 'John D.', 'email' => 'john@example.com'], // Duplicate email
            ['id' => 4, 'name' => 'Bob Johnson', 'email' => 'bob@example.com']
        ];

        $duplicates = $this->detectDuplicateEmails($data);
        $this->assertCount(1, $duplicates);
        $this->assertEquals(3, $duplicates[0]['id']);
    }

    #[Test]
    public function it_detects_duplicate_phone_numbers(): void
    {
        $data = [
            ['id' => 1, 'name' => 'John Doe', 'phone' => '+1-555-123-4567'],
            ['id' => 2, 'name' => 'Jane Smith', 'phone' => '+1-555-987-6543'],
            ['id' => 3, 'name' => 'John D.', 'phone' => '+1-555-123-4567'], // Duplicate phone (same number)
            ['id' => 4, 'name' => 'Bob Johnson', 'phone' => '+1-555-111-2222']
        ];

        $duplicates = $this->detectDuplicatePhoneNumbers($data);
        $this->assertCount(1, $duplicates);
        $this->assertEquals(3, $duplicates[0]['id']);
    }

    #[Test]
    public function it_detects_duplicate_addresses(): void
    {
        $data = [
            ['id' => 1, 'name' => 'John Doe', 'address' => '123 Main St, City, State 12345'],
            ['id' => 2, 'name' => 'Jane Smith', 'address' => '456 Oak Ave, City, State 12345'],
            ['id' => 3, 'name' => 'John D.', 'address' => '123 Main St, City, State 12345'], // Duplicate address
            ['id' => 4, 'name' => 'Bob Johnson', 'address' => '789 Pine Rd, City, State 12345']
        ];

        $duplicates = $this->detectDuplicateAddresses($data);
        $this->assertCount(1, $duplicates);
        $this->assertEquals(3, $duplicates[0]['id']);
    }

    #[Test]
    public function it_detects_duplicate_products(): void
    {
        $data = [
            ['id' => 1, 'name' => 'Laptop Computer', 'sku' => 'LAP-001', 'price' => 999.99],
            ['id' => 2, 'name' => 'Wireless Mouse', 'sku' => 'MOU-001', 'price' => 29.99],
            ['id' => 3, 'name' => 'Laptop Computer', 'sku' => 'LAP-002', 'price' => 999.99], // Duplicate product
            ['id' => 4, 'name' => 'USB Cable', 'sku' => 'USB-001', 'price' => 9.99]
        ];

        $duplicates = $this->detectDuplicateProducts($data);
        $this->assertCount(1, $duplicates);
        $this->assertEquals(3, $duplicates[0]['id']);
    }

    #[Test]
    public function it_detects_duplicate_orders(): void
    {
        $data = [
            ['id' => 1, 'customer_id' => 1, 'order_date' => '2024-01-15', 'total' => 150.00],
            ['id' => 2, 'customer_id' => 2, 'order_date' => '2024-01-16', 'total' => 200.00],
            ['id' => 3, 'customer_id' => 1, 'order_date' => '2024-01-15', 'total' => 150.00], // Duplicate order
            ['id' => 4, 'customer_id' => 3, 'order_date' => '2024-01-17', 'total' => 100.00]
        ];

        $duplicates = $this->detectDuplicateOrders($data);
        $this->assertCount(1, $duplicates);
        $this->assertEquals(3, $duplicates[0]['id']);
    }

    #[Test]
    public function it_detects_duplicate_users(): void
    {
        $data = [
            ['id' => 1, 'username' => 'johndoe', 'email' => 'john@example.com'],
            ['id' => 2, 'username' => 'janesmith', 'email' => 'jane@example.com'],
            ['id' => 3, 'username' => 'johndoe', 'email' => 'john.doe@example.com'], // Duplicate username
            ['id' => 4, 'username' => 'bobjohnson', 'email' => 'bob@example.com']
        ];

        $duplicates = $this->detectDuplicateUsers($data);
        $this->assertCount(1, $duplicates);
        $this->assertEquals(3, $duplicates[0]['id']);
    }

    #[Test]
    public function it_detects_duplicate_skus(): void
    {
        $data = [
            ['id' => 1, 'name' => 'Product A', 'sku' => 'SKU-001'],
            ['id' => 2, 'name' => 'Product B', 'sku' => 'SKU-002'],
            ['id' => 3, 'name' => 'Product C', 'sku' => 'SKU-001'], // Duplicate SKU
            ['id' => 4, 'name' => 'Product D', 'sku' => 'SKU-003']
        ];

        $duplicates = $this->detectDuplicateSkus($data);
        $this->assertCount(1, $duplicates);
        $this->assertEquals(3, $duplicates[0]['id']);
    }

    #[Test]
    public function it_detects_duplicate_credit_cards(): void
    {
        $data = [
            ['id' => 1, 'name' => 'John Doe', 'card_number' => '4111111111111111'],
            ['id' => 2, 'name' => 'Jane Smith', 'card_number' => '5555555555554444'],
            ['id' => 3, 'name' => 'John D.', 'card_number' => '4111111111111111'], // Duplicate card
            ['id' => 4, 'name' => 'Bob Johnson', 'card_number' => '6011111111111117']
        ];

        $duplicates = $this->detectDuplicateCreditCards($data);
        $this->assertCount(1, $duplicates);
        $this->assertEquals(3, $duplicates[0]['id']);
    }

    #[Test]
    public function it_detects_duplicate_social_security_numbers(): void
    {
        $data = [
            ['id' => 1, 'name' => 'John Doe', 'ssn' => '123-45-6789'],
            ['id' => 2, 'name' => 'Jane Smith', 'ssn' => '987-65-4321'],
            ['id' => 3, 'name' => 'John D.', 'ssn' => '123456789'], // Duplicate SSN (different format)
            ['id' => 4, 'name' => 'Bob Johnson', 'ssn' => '111-22-3333']
        ];

        $duplicates = $this->detectDuplicateSSNs($data);
        $this->assertCount(1, $duplicates);
        $this->assertEquals(3, $duplicates[0]['id']);
    }

    #[Test]
    public function it_detects_duplicate_ip_addresses(): void
    {
        $data = [
            ['id' => 1, 'user_id' => 1, 'ip_address' => '192.168.1.1', 'timestamp' => '2024-01-15 10:00:00'],
            ['id' => 2, 'user_id' => 2, 'ip_address' => '192.168.1.2', 'timestamp' => '2024-01-15 11:00:00'],
            ['id' => 3, 'user_id' => 1, 'ip_address' => '192.168.1.1', 'timestamp' => '2024-01-15 12:00:00'], // Duplicate IP
            ['id' => 4, 'user_id' => 3, 'ip_address' => '192.168.1.3', 'timestamp' => '2024-01-15 13:00:00']
        ];

        $duplicates = $this->detectDuplicateIPAddresses($data);
        $this->assertCount(1, $duplicates);
        $this->assertEquals(3, $duplicates[0]['id']);
    }

    #[Test]
    public function it_detects_duplicate_transactions(): void
    {
        $data = [
            ['id' => 1, 'transaction_id' => 'TXN-001', 'amount' => 100.00, 'timestamp' => '2024-01-15 10:00:00'],
            ['id' => 2, 'transaction_id' => 'TXN-002', 'amount' => 200.00, 'timestamp' => '2024-01-15 11:00:00'],
            ['id' => 3, 'transaction_id' => 'TXN-001', 'amount' => 100.00, 'timestamp' => '2024-01-15 12:00:00'], // Duplicate transaction
            ['id' => 4, 'transaction_id' => 'TXN-003', 'amount' => 300.00, 'timestamp' => '2024-01-15 13:00:00']
        ];

        $duplicates = $this->detectDuplicateTransactions($data);
        $this->assertCount(1, $duplicates);
        $this->assertEquals(3, $duplicates[0]['id']);
    }

    #[Test]
    public function it_detects_duplicate_files(): void
    {
        $data = [
            ['id' => 1, 'filename' => 'document.pdf', 'file_hash' => 'abc123def456'],
            ['id' => 2, 'filename' => 'image.jpg', 'file_hash' => 'def456ghi789'],
            ['id' => 3, 'filename' => 'document_copy.pdf', 'file_hash' => 'abc123def456'], // Duplicate file
            ['id' => 4, 'filename' => 'spreadsheet.xlsx', 'file_hash' => 'ghi789jkl012']
        ];

        $duplicates = $this->detectDuplicateFiles($data);
        $this->assertCount(1, $duplicates);
        $this->assertEquals(3, $duplicates[0]['id']);
    }

    #[Test]
    public function it_detects_duplicate_urls(): void
    {
        $data = [
            ['id' => 1, 'title' => 'Home Page', 'url' => 'https://example.com'],
            ['id' => 2, 'title' => 'About Page', 'url' => 'https://example.com/about'],
            ['id' => 3, 'title' => 'Home Page Copy', 'url' => 'https://example.com/'], // Duplicate URL
            ['id' => 4, 'title' => 'Contact Page', 'url' => 'https://example.com/contact']
        ];

        $duplicates = $this->detectDuplicateUrls($data);
        $this->assertCount(1, $duplicates);
        $this->assertEquals(3, $duplicates[0]['id']);
    }

    #[Test]
    public function it_detects_duplicate_phone_numbers_with_normalization(): void
    {
        $data = [
            ['id' => 1, 'name' => 'John Doe', 'phone' => '+1-555-123-4567'],
            ['id' => 2, 'name' => 'Jane Smith', 'phone' => '+1-555-987-6543'],
            ['id' => 3, 'name' => 'John D.', 'phone' => '15551234567'], // Duplicate phone (different format)
            ['id' => 4, 'name' => 'Bob Johnson', 'phone' => '555.111.2222']
        ];

        $duplicates = $this->detectDuplicatePhoneNumbersWithNormalization($data);
        $this->assertCount(1, $duplicates);
        $this->assertEquals(3, $duplicates[0]['id']);
    }

    #[Test]
    public function it_detects_duplicate_emails_with_normalization(): void
    {
        $data = [
            ['id' => 1, 'name' => 'John Doe', 'email' => 'john@example.com'],
            ['id' => 2, 'name' => 'Jane Smith', 'email' => 'jane@example.com'],
            ['id' => 3, 'name' => 'John D.', 'email' => 'JOHN@EXAMPLE.COM'], // Duplicate email (case difference)
            ['id' => 4, 'name' => 'Bob Johnson', 'email' => 'bob@example.com']
        ];

        $duplicates = $this->detectDuplicateEmailsWithNormalization($data);
        $this->assertCount(1, $duplicates);
        $this->assertEquals(3, $duplicates[0]['id']);
    }

    #[Test]
    public function it_calculates_duplication_percentage(): void
    {
        $data = [
            ['id' => 1, 'name' => 'Product A', 'price' => 100.00],
            ['id' => 2, 'name' => 'Product B', 'price' => 200.00],
            ['id' => 3, 'name' => 'Product A', 'price' => 100.00], // Duplicate
            ['id' => 4, 'name' => 'Product C', 'price' => 300.00],
            ['id' => 5, 'name' => 'Product B', 'price' => 200.00] // Duplicate
        ];

        $duplicationPercentage = $this->calculateDuplicationPercentage($data);
        $this->assertEquals(40.0, $duplicationPercentage); // 2 duplicates out of 5 records = 40%
    }

    #[Test]
    public function it_generates_duplicate_report(): void
    {
        $data = [
            ['id' => 1, 'name' => 'Product A', 'price' => 100.00],
            ['id' => 2, 'name' => 'Product B', 'price' => 200.00],
            ['id' => 3, 'name' => 'Product A', 'price' => 100.00], // Duplicate
            ['id' => 4, 'name' => 'Product C', 'price' => 300.00]
        ];

        $report = $this->generateDuplicateReport($data);

        $this->assertArrayHasKey('total_records', $report);
        $this->assertArrayHasKey('duplicate_count', $report);
        $this->assertArrayHasKey('duplication_percentage', $report);
        $this->assertArrayHasKey('duplicates', $report);

        $this->assertEquals(4, $report['total_records']);
        $this->assertEquals(1, $report['duplicate_count']);
        $this->assertEquals(25.0, $report['duplication_percentage']);
    }

    private function detectExactDuplicates(array $data): array
    {
        $duplicates = [];
        $seen = [];

        foreach ($data as $record) {
            // Create a key without the 'id' field for comparison
            $comparisonRecord = $record;
            unset($comparisonRecord['id']);
            $key = md5(serialize($comparisonRecord));

            if (isset($seen[$key])) {
                $duplicates[] = $record;
            } else {
                $seen[$key] = true;
            }
        }

        return $duplicates;
    }

    private function detectNearDuplicates(array $data, array $fields): array
    {
        $duplicates = [];

        for ($i = 0; $i < count($data); $i++) {
            for ($j = $i + 1; $j < count($data); $j++) {
                $record1 = $data[$i];
                $record2 = $data[$j];

                $isDuplicate = true;
                foreach ($fields as $field) {
                    if (isset($record1[$field]) && isset($record2[$field])) {
                        $value1 = strtolower(trim($record1[$field]));
                        $value2 = strtolower(trim($record2[$field]));

                        if ($value1 !== $value2) {
                            $isDuplicate = false;
                            break;
                        }
                    }
                }

                if ($isDuplicate) {
                    // Add both records to avoid duplicates
                    if (!in_array($record1, $duplicates)) {
                        $duplicates[] = $record1;
                    }
                    if (!in_array($record2, $duplicates)) {
                        $duplicates[] = $record2;
                    }
                }
            }
        }

        return $duplicates;
    }

    private function detectDuplicateEmails(array $data): array
    {
        $duplicates = [];
        $seen = [];

        foreach ($data as $record) {
            if (isset($record['email'])) {
                $email = strtolower(trim($record['email']));
                if (isset($seen[$email])) {
                    $duplicates[] = $record;
                } else {
                    $seen[$email] = true;
                }
            }
        }

        return $duplicates;
    }

    private function detectDuplicatePhoneNumbers(array $data): array
    {
        $duplicates = [];
        $seen = [];

        foreach ($data as $record) {
            if (isset($record['phone'])) {
                $phone = preg_replace('/[^\d]/', '', $record['phone']);
                if (isset($seen[$phone])) {
                    $duplicates[] = $record;
                } else {
                    $seen[$phone] = true;
                }
            }
        }

        return $duplicates;
    }

    private function detectDuplicateAddresses(array $data): array
    {
        $duplicates = [];
        $seen = [];

        foreach ($data as $record) {
            if (isset($record['address'])) {
                $address = strtolower(preg_replace('/[^\w\s]/', '', $record['address']));
                if (isset($seen[$address])) {
                    $duplicates[] = $record;
                } else {
                    $seen[$address] = true;
                }
            }
        }

        return $duplicates;
    }

    private function detectDuplicateProducts(array $data): array
    {
        $duplicates = [];
        $seen = [];

        foreach ($data as $record) {
            if (isset($record['name']) && isset($record['price'])) {
                $key = strtolower($record['name']) . '|' . $record['price'];
                if (isset($seen[$key])) {
                    $duplicates[] = $record;
                } else {
                    $seen[$key] = true;
                }
            }
        }

        return $duplicates;
    }

    private function detectDuplicateOrders(array $data): array
    {
        $duplicates = [];
        $seen = [];

        foreach ($data as $record) {
            if (isset($record['customer_id']) && isset($record['order_date']) && isset($record['total'])) {
                $key = $record['customer_id'] . '|' . $record['order_date'] . '|' . $record['total'];
                if (isset($seen[$key])) {
                    $duplicates[] = $record;
                } else {
                    $seen[$key] = true;
                }
            }
        }

        return $duplicates;
    }

    private function detectDuplicateUsers(array $data): array
    {
        $duplicates = [];
        $seen = [];

        foreach ($data as $record) {
            if (isset($record['username'])) {
                $username = strtolower(trim($record['username']));
                if (isset($seen[$username])) {
                    $duplicates[] = $record;
                } else {
                    $seen[$username] = true;
                }
            }
        }

        return $duplicates;
    }

    private function detectDuplicateSkus(array $data): array
    {
        $duplicates = [];
        $seen = [];

        foreach ($data as $record) {
            if (isset($record['sku'])) {
                $sku = strtoupper(trim($record['sku']));
                if (isset($seen[$sku])) {
                    $duplicates[] = $record;
                } else {
                    $seen[$sku] = true;
                }
            }
        }

        return $duplicates;
    }

    private function detectDuplicateCreditCards(array $data): array
    {
        $duplicates = [];
        $seen = [];

        foreach ($data as $record) {
            if (isset($record['card_number'])) {
                $cardNumber = preg_replace('/[^\d]/', '', $record['card_number']);
                if (isset($seen[$cardNumber])) {
                    $duplicates[] = $record;
                } else {
                    $seen[$cardNumber] = true;
                }
            }
        }

        return $duplicates;
    }

    private function detectDuplicateSSNs(array $data): array
    {
        $duplicates = [];
        $seen = [];

        foreach ($data as $record) {
            if (isset($record['ssn'])) {
                $ssn = preg_replace('/[^\d]/', '', $record['ssn']);
                if (isset($seen[$ssn])) {
                    $duplicates[] = $record;
                } else {
                    $seen[$ssn] = true;
                }
            }
        }

        return $duplicates;
    }

    private function detectDuplicateIPAddresses(array $data): array
    {
        $duplicates = [];
        $seen = [];

        foreach ($data as $record) {
            if (isset($record['ip_address'])) {
                $ip = $record['ip_address'];
                if (isset($seen[$ip])) {
                    $duplicates[] = $record;
                } else {
                    $seen[$ip] = true;
                }
            }
        }

        return $duplicates;
    }

    private function detectDuplicateTransactions(array $data): array
    {
        $duplicates = [];
        $seen = [];

        foreach ($data as $record) {
            if (isset($record['transaction_id'])) {
                $transactionId = $record['transaction_id'];
                if (isset($seen[$transactionId])) {
                    $duplicates[] = $record;
                } else {
                    $seen[$transactionId] = true;
                }
            }
        }

        return $duplicates;
    }

    private function detectDuplicateFiles(array $data): array
    {
        $duplicates = [];
        $seen = [];

        foreach ($data as $record) {
            if (isset($record['file_hash'])) {
                $hash = $record['file_hash'];
                if (isset($seen[$hash])) {
                    $duplicates[] = $record;
                } else {
                    $seen[$hash] = true;
                }
            }
        }

        return $duplicates;
    }

    private function detectDuplicateUrls(array $data): array
    {
        $duplicates = [];
        $seen = [];

        foreach ($data as $record) {
            if (isset($record['url'])) {
                $url = rtrim(strtolower($record['url']), '/');
                if (isset($seen[$url])) {
                    $duplicates[] = $record;
                } else {
                    $seen[$url] = true;
                }
            }
        }

        return $duplicates;
    }

    private function detectDuplicatePhoneNumbersWithNormalization(array $data): array
    {
        $duplicates = [];
        $seen = [];

        foreach ($data as $record) {
            if (isset($record['phone'])) {
                $phone = preg_replace('/[^\d]/', '', $record['phone']);
                if (isset($seen[$phone])) {
                    $duplicates[] = $record;
                } else {
                    $seen[$phone] = true;
                }
            }
        }

        return $duplicates;
    }

    private function detectDuplicateEmailsWithNormalization(array $data): array
    {
        $duplicates = [];
        $seen = [];

        foreach ($data as $record) {
            if (isset($record['email'])) {
                $email = strtolower(trim($record['email']));
                if (isset($seen[$email])) {
                    $duplicates[] = $record;
                } else {
                    $seen[$email] = true;
                }
            }
        }

        return $duplicates;
    }

    private function calculateDuplicationPercentage(array $data): float
    {
        $totalRecords = count($data);
        $duplicates = $this->detectExactDuplicates($data);
        $duplicateCount = count($duplicates);

        return ($duplicateCount / $totalRecords) * 100;
    }

    private function generateDuplicateReport(array $data): array
    {
        $totalRecords = count($data);
        $duplicates = $this->detectExactDuplicates($data);
        $duplicateCount = count($duplicates);
        $duplicationPercentage = ($duplicateCount / $totalRecords) * 100;

        return [
            'total_records' => $totalRecords,
            'duplicate_count' => $duplicateCount,
            'duplication_percentage' => $duplicationPercentage,
            'duplicates' => $duplicates,
            'generated_at' => date('Y-m-d H:i:s')
        ];
    }
}
