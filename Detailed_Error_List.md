# قائمة تفصيلية بالأخطاء والمخالفات

**تاريخ الإنشاء:** 11 سبتمبر 2025

هذا المستند يحتوي على قائمة مفصلة بجميع الأخطاء التي تم اكتشافها بواسطة أدوات التحليل.

---

## القسم الأول: أخطاء PHPStan (146 خطأ)

فيما يلي قائمة تفصيلية بالأخطاء التي كشف عنها تحليل PHPStan. إصلاح هذه الأخطاء ضروري لضمان جودة واستقرار الشيفرة.

| الملف | السطر | وصف الخطأ |
| :--- | :--- | :--- |
| `app\Http\Controllers\ErrorController.php` | 35 | Parameter #1 $view of function view expects view-string&#124;null, string given. |
| `app\Http\Controllers\ErrorController.php` | 63 | Parameter #1 $view of function view expects view-string&#124;null, string given. |
| `app\Http\Controllers\ErrorController.php` | 255 | Offset 0 on non-empty-list<string> on left side of ?? always exists and is not nullable. |
| `app\Http\Controllers\ReviewController.php` | 25 | Parameter #1 $view of function view expects view-string&#124;null, string given. |
| `app\Http\Controllers\ReviewController.php` | 44 | Parameter #1 $view of function view expects view-string&#124;null, string given. |
| `app\Http\Controllers\ReviewController.php` | 98 | Parameter #1 $view of function view expects view-string&#124;null, string given. |
| `app\Http\Middleware\AdminMiddleware.php` | 26 | Cannot call method isAdmin() on App\Models\User&#124;null. |
| `app\Http\Middleware\AuthenticateSession.php` | 20 | Cannot access property $session_id on App\Models\User&#124;null. |
| `app\Http\Middleware\Authorize.php` | 16 | Cannot call method can() on App\Models\User&#124;null. |
| `app\Http\Middleware\ConvertEmptyStringsToNull.php` | 23 | Method has no return type specified. |
| `app\Http\Middleware\ConvertEmptyStringsToNull.php` | 23 | Method has parameter $input with no type specified. |
| `app\Http\Middleware\EnsureEmailIsVerified.php` | 16 | Cannot call method hasVerifiedEmail() on App\Models\User&#124;null. |
| `app\Http\Middleware\RequirePassword.php` | 18 | Access to an undefined property App\Models\User::$password_confirmed_at. |
| `app\Http\Middleware\SecurityHeaders.php` | 137 | Instanceof between Illuminate\Http\UploadedFile and Illuminate\Http\UploadedFile will always evaluate to true. |
| `app\Http\Middleware\ThrottleSensitiveOperations.php` | 25 | Parameter #2 $maxAttempts of static method RateLimiter::tooManyAttempts() expects int, array<string, int> given. |
| `app\Http\Middleware\ThrottleSensitiveOperations.php` | 41 | Parameter #2 $decaySeconds of static method RateLimiter::hit() expects DateInterval&#124;DateTimeInterface&#124;int, array<string, int> given. |
| `app\Models\AuditLog.php` | 14 | Class uses generic trait HasFactory but does not specify its types: TFactory. |
| `app\Models\AuditLog.php` | 42 | Method user() return type with generic class App\Models\User does not specify its types. |
| `app\Models\Brand.php` | 50 | Method factory() has no return type specified. |
| `app\Models\Brand.php` | 50 | Method factory() has parameter $count with no type specified. |
| `app\Models\Brand.php` | 50 | Method factory() has parameter $state with no type specified. |
| `app\Models\Brand.php` | 116 | Method scopeActive() has parameter $query with generic class App\Models\Brand but does not specify its types. |
| `app\Models\Brand.php` | 116 | Method scopeActive() return type with generic class App\Models\Brand does not specify its types. |
| `app\Models\Brand.php` | 128 | Method scopeSearch() has parameter $query with generic class App\Models\Brand but does not specify its types. |
| `app\Models\Brand.php` | 128 | Method scopeSearch() return type with generic class App\Models\Brand does not specify its types. |
| `app\Models\Category.php` | 39 | PHPDoc tag @mixin contains generic class App\Models\Category but does not specify its types. |
| `app\Models\Category.php` | 41 | Class uses generic trait HasFactory but does not specify its types. |
| `app\Models\Category.php` | 49 | Method factory() has no return type specified. |
| `app\Models\Category.php` | 49 | Method factory() has parameter $count with no type specified. |
| `app\Models\Category.php` | 49 | Method factory() has parameter $state with no type specified. |
| `app\Models\Category.php` | 134 | Method scopeActive() has parameter $query with generic class App\Models\Category but does not specify its types. |
| `app\Models\Category.php` | 134 | Method scopeActive() return type with generic class App\Models\Category does not specify its types. |
| `app\Models\Category.php` | 146 | Method scopeSearch() has parameter $query with generic class App\Models\Category but does not specify its types. |
| `app\Models\Category.php` | 146 | Method scopeSearch() return type with generic class App\Models\Category does not specify its types. |
| `app\Models\Product.php` | 57 | Method factory() has no return type specified. |
| `app\Models\Product.php` | 57 | Method factory() has parameter $count with no type specified. |
| `app\Models\Product.php` | 57 | Method factory() has parameter $state with no type specified. |
| `app\Services\PasswordPolicyService.php` | 296 | Method getLastPasswordChange() never returns Carbon\Carbon so it can be removed from the return type. |

*... and 108 more errors across many files, primarily in `app/Models` and `app/Http/Middleware`.*

---

## القسم الثاني: مخالفات PHP-CS-Fixer (193 ملفًا)

فيما يلي قائمة بالملفات التي تحتوي على مخالفات في تنسيق الشيفرة. يمكن إصلاحها جميعًا تلقائيًا عن طريق تشغيل الأمر: `composer run-script fix:style`.

1. `app\Console\Commands\AgentProposeFixCommand.php`
2. `app\Console\Commands\CheckDeploymentReadiness.php`
3. `app\Console\Commands\CleanupOldDataCommand.php`
4. `app\Console\Commands\ComprehensiveAnalysis.php`
5. `app\Console\Commands\FixCode.php`
6. `app\Console\Commands\StatsCommand.php`
7. `app\Console\Commands\UpdatePricesCommand.php`
8. `app\Console\Kernel.php`
9. `app\Contracts\EmailVerificationServiceInterface.php`
10. `app\Contracts\UserBanServiceInterface.php`
11. `app\Exceptions\GlobalExceptionHandler.php`
12. `app\Helpers\PriceHelper.php`
13. `app\Http\Controllers\Admin\DashboardController.php`
14. `app\Http\Controllers\AdminController.php`
15. `app\Http\Controllers\Api\BaseApiController.php`
16. `app\Http\Controllers\Api\PriceSearchController.php`
17. `app\Http\Controllers\Api\ProductController.php`
18. `app\Http\Controllers\Api\Schemas\PaginationLinksSchema.php`
19. `app\Http\Controllers\Api\Schemas\PaginationMetaSchema.php`
20. `app\Http\Controllers\Api\Schemas\ProductSchema.php`
21. `app\Http\Controllers\Api\Schemas\ReviewSchema.php`
22. `app\Http\Controllers\Api\V2\BaseApiController.php`
23. `app\Http\Controllers\BrandController.php`
24. `app\Http\Controllers\CartController.php`
25. `app\Http\Controllers\ErrorController.php`
26. `app\Http\Controllers\LocaleController.php`
27. `app\Http\Controllers\PriceAlertController.php`
28. `app\Http\Controllers\ProductController.php`
29. `app\Http\Controllers\ReviewController.php`
30. `app\Http\Middleware\AdminMiddleware.php`
31. `app\Http\Middleware\EnsureEmailIsVerified.php`
32. `app\Http\Middleware\InputSanitizationMiddleware.php`
33. `app\Http\Middleware\LocaleMiddleware.php`
34. `app\Http\Middleware\RedirectIfAuthenticated.php`
35. `app\Http\Middleware\SecurityHeaders.php`
36. `app\Http\Middleware\SetCacheHeaders.php`
37. `app\Http\Middleware\SetLocaleAndCurrency.php`
38. `app\Http\Middleware\StartSession.php`
39. `app\Http\Middleware\SubstituteBindings.php`
40. `app\Http\Middleware\ThrottleSensitiveOperations.php`
41. `app\Http\Middleware\ValidateApiRequest.php`
42. `app\Http\Requests\ProductCreateRequest.php`
43. `app\Http\Requests\ProductRequest.php`
44. `app\Http\Requests\ProductSearchRequest.php`
45. `app\Http\Requests\ProductUpdateRequest.php`
46. `app\Jobs\ProcessHeavyOperation.php`
47. `app\Models\AuditLog.php`
48. `app\Models\Brand.php`
49. `app\Models\Category.php`
50. `app\Models\Currency.php`
51. `app\Models\Language.php`
52. `app\Models\PriceAlert.php`
53. `app\Models\PriceOffer.php`
54. `app\Models\Product.php`
55. `app\Models\Review.php`
56. `app\Models\Store.php`
57. `app\Models\User.php`
58. `app\Models\UserLocaleSetting.php`
59. `app\Models\Wishlist.php`
60. `app\Policies\ProductPolicy.php`
61. `app\Policies\UserPolicy.php`
62. `app\Providers\AppServiceProvider.php`
63. `app\Repositories\ProductRepository.php`
64. `app\Services\AuditService.php`
65. `app\Services\BackupService.php`
66. `app\Services\CacheService.php`
67. `app\Services\CDNService.php`
68. `app\Services\CentralizedLoggingService.php`
69. `app\Services\CloudStorageService.php`
70. `app\Services\EmailVerificationService.php`
71. `app\Services\FactoryConfigurationService.php`
72. `app\Services\FileCleanupService.php`
73. `app\Services\FileSecurityService.php`
74. `app\Services\FinancialTransactionService.php`
75. `app\Services\ImageOptimizationService.php`
76. `app\Services\InputSanitizerService.php`
77. `app\Services\LoginAttemptService.php`
78. `app\Services\NotificationService.php`
79. `app\Services\PasswordPolicyService.php`
80. `app\Services\PasswordResetService.php`
81. `app\Services\PerformanceAnalysisService.php`
82. `app\Services\PerformanceMonitoringService.php`
83. `app\Services\PriceSearchService.php`
84. `app\Services\ProcessResult.php`
85. `app\Services\ProcessService.php`
86. `app\Services\ProductService.php`
87. `app\Services\QualityAnalysisService.php`
88. `app\Services\ReportService.php`
89. `app\Services\SecurityAnalysisService.php`
90. `app\Services\StatisticsService.php`
91. `app\Services\StorageManagementService.php`
92. `app\Services\SuspiciousActivityService.php`
93. `app\Services\TestAnalysisService.php`
94. `app\Services\TestAnalysisServiceFactory.php`
95. `app\Services\UserBanService.php`
96. `app\Services\VulnerabilityScanService.php`
97. `app\Services\WatermarkService.php`
98. `config\app.php`
99. `config\broadcasting.php`
100. `config\cache.php`
101. `config\database.php`
102. `config\filesystems.php`
103. `config\logging.php`
104. `config\monitoring.php`
105. `config\sanctum.php`
106. `config\sentry.php`
107. `config\session.php`
108. `database\factories\BrandFactory.php`
109. `database\factories\CategoryFactory.php`
110. `database\factories\LanguageFactory.php`
111. `database\factories\PriceOfferFactory.php`
112. `database\factories\ProductFactory.php`
113. `database\factories\StoreFactory.php`
114. `database\factories\UserFactory.php`
115. `database\migrations\0001_01_01_000000_create_users_table.php`
116. `database\migrations\0001_01_01_000001_create_cache_table.php`
117. `database\migrations\0001_01_01_000002_create_jobs_table.php`
118. `database\migrations\2025_01_15_000001_add_missing_indexes.php`
119. `database\migrations\2025_01_15_000002_add_encrypted_fields.php`
120. `database\migrations\2025_01_15_000003_create_audit_logs_table.php`
121. `database\migrations\2025_01_15_000004_create_notifications_table.php`
122. `database\migrations\2025_08_18_145450_create_brands_table.php`
123. `database\migrations\2025_08_18_145451_create_categories_table.php`
124. `database\migrations\2025_08_18_145452_create_products_table.php`
125. `database\migrations\2025_08_18_145453_create_languages_and_currencies_tables.php`
126. `database\migrations\2025_08_18_145454_create_stores_table.php`
127. `database\migrations\2025_08_21_180931_create_price_offers_table.php`
128. `database\migrations\2025_08_21_184616_create_reviews_table.php`
129. `database\migrations\2025_08_21_184634_create_wishlists_table.php`
130. `database\migrations\2025_08_21_184657_create_price_alerts_table.php`
131. `database\migrations\2025_09_07_055316_add_image_to_products_table.php`
132. `database\migrations\2025_09_07_073043_add_is_admin_to_users_table.php`
133. `database\migrations\2025_09_08_025634_add_soft_deletes_to_brands_table.php`
134. `database\migrations\2025_09_08_025841_add_soft_deletes_to_categories_table.php`
135. `database\migrations\2025_09_08_030119_add_soft_deletes_to_stores_table.php`
136. `database\migrations\2025_09_08_030214_add_description_to_stores_table.php`
137. `database\migrations\2025_09_08_030254_add_logo_url_to_stores_table.php`
138. `database\migrations\2025_09_08_030441_add_affiliate_code_to_stores_table.php`
139. `database\migrations\2025_09_08_030534_add_store_id_to_products_table.php`
140. `database\migrations\2025_09_08_030823_add_soft_deletes_to_price_alerts_table.php`
141. `database\migrations\2025_09_08_031045_add_soft_deletes_to_wishlists_table.php`
142. `database\migrations\2025_09_08_031138_add_notes_to_wishlists_table.php`
143. `database\migrations\2025_09_08_041809_add_soft_deletes_to_products_table.php`
144. `database\migrations\2025_09_08_042807_add_decimal_places_to_currencies_table.php`
145. `database\migrations\2025_09_08_064338_add_is_available_and_original_price_to_price_offers_table.php`
146. `database\migrations\2025_09_10_064350_add_ban_fields_to_users_table.php`
147. `database\seeders\PriceOfferSeeder.php`
148. `database\seeders\StoreSeeder.php`
149. `routes\channels.php`
150. `tests\Benchmarks\PerformanceBenchmark.php`
151. `tests\CreatesApplication.php`
152. `tests\DuskTestCase.php`
153. `tests\Feature\AdminControllerTest.php`
154. `tests\Feature\Api\PriceSearchControllerTest.php`
155. `tests\Feature\BrandControllerTest.php`
156. `tests\Feature\HostingerTest.php`
157. `tests\Feature\Http\Controllers\CategoryControllerTest.php`
158. `tests\Feature\Http\Controllers\HomeControllerTest.php`
159. `tests\Feature\Http\Controllers\PriceAlertControllerTest.php`
160. `tests\Feature\Http\Controllers\ProductControllerTest.php`
161. `tests\Feature\Http\Controllers\ReviewControllerTest.php`
162. `tests\Feature\Http\Controllers\WishlistControllerTest.php`
163. `tests\Feature\Integration\PriceSearchIntegrationTest.php`
164. `tests\Feature\Middleware\LocaleMiddlewareTest.php`
165. `tests\Feature\Performance\PerformanceTest.php`
166. `tests\Feature\Security\SecurityTest.php`
167. `tests\Feature\UITest.php`
168. `tests\Integration\IntegrationTest.php`
169. `tests\Performance\PerformanceTest.php`
170. `tests\Security\SecurityAudit.php`
171. `tests\Unit\AgentProposeFixCommandTest.php`
172. `tests\Unit\Commands\StatsCommandTest.php`
173. `tests\Unit\Commands\UpdatePricesCommandTest.php`
174. `tests\Unit\Controllers\BrandControllerTest.php`
175. `tests\Unit\Controllers\CategoryControllerTest.php`
176. `tests\Unit\Controllers\HomeControllerTest.php`
177. `tests\Unit\Controllers\ProductControllerTest.php`
178. `tests\Unit\Controllers\WishlistControllerTest.php`
179. `tests\Unit\Helpers\PriceHelperTest.php`
180. `tests\Unit\Middleware\AdminMiddlewareTest.php`
181. `tests\Unit\ModelRelationsTest.php`
182. `tests\Unit\Models\BrandTest.php`
183. `tests\Unit\Models\CategoryTest.php`
184. `tests\Unit\Models\CurrencyTest.php`
185. `tests\Unit\Models\PriceAlertTest.php`
186. `tests\Unit\Models\PriceOfferTest.php`
187. `tests\Unit\Models\ProductTest.php`
188. `tests\Unit\Models\ReviewTest.php`
189. `tests\Unit\Models\StoreTest.php`
190. `tests\Unit\Models\UserTest.php`
191. `tests\Unit\Models\WishlistTest.php`
192. `tests\Unit\Services\ProcessServiceTest.php`
193. `tests\Unit\StoreModelTest.php`
