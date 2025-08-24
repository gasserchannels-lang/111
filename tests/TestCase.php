<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

abstract class TestCase extends BaseTestCase
{
    // ✅ الخطوة 1: إضافة RefreshDatabase trait
    // هذا سيضمن أن قاعدة البيانات (في الذاكرة) يتم مسحها وإعادة بنائها قبل كل اختبار
    use CreatesApplication, RefreshDatabase;
}
