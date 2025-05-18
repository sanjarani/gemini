# Gemini for Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/sanjarani/gemini.svg?style=flat-square)](https://packagist.org/packages/sanjarani/gemini)
[![Total Downloads](https://img.shields.io/packagist/dt/sanjarani/gemini.svg?style=flat-square)](https://packagist.org/packages/sanjarani/gemini)
[![License](https://img.shields.io/packagist/l/sanjarani/gemini.svg?style=flat-square)](https://packagist.org/packages/sanjarani/gemini)

یک پکیج حرفه‌ای، توسعه‌پذیر و مقیاس‌پذیر برای کار با Google Gemini API در فریم‌ورک Laravel.

## ویژگی‌ها

- پشتیبانی از تمامی مدل‌های Gemini (gemini-pro، gemini-pro-vision و غیره)
- قابلیت تغییر مدل در runtime
- استفاده از Laravel HTTP Client با مدیریت خطای دقیق
- طراحی بر اساس سرویس‌محور بودن و Dependency Injection
- پشتیبانی از Prompt Caching برای صرفه‌جویی در توکن‌ها و پاسخ‌دهی سریع‌تر
- شمارش توکن ورودی و خروجی با تخمین هزینه
- پشتیبانی از اجرای async با Queue و Job اختصاصی
- کنترل دقیق نرخ ارسال درخواست‌ها و خطاهای مرتبط با Rate Limit
- فاساد برای دسترسی آسان‌تر
- میدلور برای مدیریت درخواست‌ها
- سیستم لاگینگ قابل تنظیم
- پشتیبانی از ایجاد embedding و محاسبه شباهت متن‌ها

## نصب

این پکیج را می‌توانید با Composer نصب کنید:

```bash
composer require sanjarani/gemini
```php

سپس فایل پیکربندی را منتشر کنید:

```bash
php artisan vendor:publish --tag=gemini-config
```php

## پیکربندی

پس از انتشار فایل پیکربندی، می‌توانید تنظیمات را در فایل `config/gemini.php` انجام دهید. همچنین می‌توانید از متغیرهای محیطی در فایل `.env` استفاده کنید:

```env
GEMINI_API_KEY=your-api-key
GEMINI_DEFAULT_MODEL=gemini-pro
GEMINI_REQUEST_TIMEOUT=30
GEMINI_ENABLE_CACHE=false
GEMINI_CACHE_TTL=3600
GEMINI_ENABLE_LOGGING=false
```php

## نحوه استفاده

### تولید متن با مدل Gemini

```php
use Sanjarani\Gemini\Facades\Gemini;

// استفاده ساده
$response = Gemini::generate('به من در مورد هوش مصنوعی بگو');
echo $response->content();

// استفاده با تنظیمات بیشتر
$response = Gemini::generate('به من در مورد هوش مصنوعی بگو', [
    'temperature' => 0.7,
    'top_p' => 0.9,
    'top_k' => 40,
    'max_tokens' => 500,
]);
```php

### گفتگو با مدل Gemini

```php
use Sanjarani\Gemini\Facades\Gemini;

$messages = [
    ['role' => 'user', 'content' => 'سلام، حالت چطوره؟'],
    ['role' => 'model', 'content' => 'سلام! من خوبم، چطور می‌تونم کمکت کنم؟'],
    ['role' => 'user', 'content' => 'می‌خوام در مورد برنامه‌نویسی PHP بیشتر یاد بگیرم.'],
];

$response = Gemini::chat($messages);
echo $response->content();
```php

### استفاده از مدل Vision برای تحلیل تصاویر

```php
use Sanjarani\Gemini\Facades\Gemini;

// تحلیل یک تصویر
$response = Gemini::generateFromImage(
    '/path/to/image.jpg',
    'این تصویر را توصیف کن'
);

// تحلیل چند تصویر
$response = Gemini::generateFromMultipleImages(
    ['/path/to/image1.jpg', '/path/to/image2.jpg'],
    'این دو تصویر را مقایسه کن'
);

### استفاده از تصویر base64
$base64Image = 'data:image/jpeg;base64,...';
$response = Gemini::generateFromBase64Image(
    $base64Image,
    'این تصویر را توصیف کن'
);

### ایجاد embedding برای متن
$response = Gemini::embedText('این یک متن نمونه است');
$embedding = $response->raw()['embedding']['values'];

### ایجاد embedding برای چندین متن
$texts = ['متن اول', 'متن دوم', 'متن سوم'];
$response = Gemini::embedBatch($texts);

### محاسبه شباهت بین دو embedding
$embedding1 = $response1->raw()['embedding']['values'];
$embedding2 = $response2->raw()['embedding']['values'];
$similarity = Gemini::calculateSimilarity($embedding1, $embedding2);
echo "Similarity score: {$similarity}"; // عددی بین 0 تا 1``

### تغییر مدل در runtime

```php
use Sanjarani\Gemini\Facades\Gemini;

$response = Gemini::setModel('gemini-ultra')
    ->generate('یک داستان کوتاه بنویس');
```php

### استفاده از اجرای غیرهمزمان

```php
use Sanjarani\Gemini\Facades\Gemini;
use App\Handlers\GeminiResponseHandler;

// اجرای غیرهمزمان با callback
Gemini::generateAsync(
    'یک مقاله در مورد هوش مصنوعی بنویس',
    [],
    GeminiResponseHandler::class,
    'handleResponse',
    ['article_id' => 123]
);
```php

### دسترسی به اطلاعات پاسخ

```php
use Sanjarani\Gemini\Facades\Gemini;

$response = Gemini::generate('سلام، حالت چطوره؟');

// دسترسی به متن پاسخ
echo $response->content();

// دسترسی به داده‌های خام پاسخ
$rawData = $response->raw();

// دسترسی به اطلاعات توکن
$tokenUsage = $response->tokenUsage();
echo "Prompt tokens: {$tokenUsage['prompt_tokens']}\n";
echo "Completion tokens: {$tokenUsage['completion_tokens']}\n";
echo "Total tokens: {$tokenUsage['total_tokens']}\n";

// دسترسی به هزینه تخمینی
echo "Estimated cost: \${$response->estimatedCost()}\n";

// دسترسی به مدل استفاده شده
echo "Model: {$response->model()}\n";

// دسترسی به دلیل پایان پاسخ
echo "Finish reason: {$response->finishReason()}\n";

// بررسی موفقیت‌آمیز بودن درخواست
if ($response->successful()) {
    echo "Request was successful!\n";
}
```php

### استفاده از تزریق وابستگی

```php
use Sanjarani\Gemini\Contracts\TextGenerationServiceInterface;

class MyService
{
    protected $textService;
    
    public function __construct(TextGenerationServiceInterface $textService)
    {
        $this->textService = $textService;
    }
    
    public function generateContent($prompt)
    {
        return $this->textService->generate($prompt);
    }
}
```php

### استفاده از میدلور Rate Limiting

در فایل `app/Http/Kernel.php`:

```php
protected $routeMiddleware = [
    // ...
    'gemini.limit' => \Sanjarani\Gemini\Middleware\GeminiRateLimitMiddleware::class,
];
```php

در روت‌ها:

```php
Route::post('/generate', [GeminiController::class, 'generate'])
    ->middleware('gemini.limit:60,1'); // 60 درخواست در هر دقیقه
```php

## دستورات Artisan

### تست Gemini API

```bash
php artisan gemini:test "سلام، حالت چطوره؟"
```php

### پاک کردن کش Gemini

```bash
php artisan gemini:cache-clear
```php

## مدیریت خطاها

این پکیج خطاهای مختلف را با استثناهای اختصاصی مدیریت می‌کند:

```php
use Sanjarani\Gemini\Facades\Gemini;
use Sanjarani\Gemini\Exceptions\GeminiApiException;
use Sanjarani\Gemini\Exceptions\GeminiApiRateLimitException;
use Sanjarani\Gemini\Exceptions\GeminiModelNotFoundException;
use Sanjarani\Gemini\Exceptions\GeminiNetworkException;
use Sanjarani\Gemini\Exceptions\GeminiConfigurationException;

try {
    $response = Gemini::generate('سلام، حالت چطوره؟');
} catch (GeminiApiRateLimitException $e) {
    // مدیریت خطای محدودیت نرخ درخواست
    echo "Rate limit exceeded: {$e->getMessage()}";
} catch (GeminiModelNotFoundException $e) {
    // مدیریت خطای عدم وجود مدل
    echo "Model not found: {$e->getMessage()}";
} catch (GeminiNetworkException $e) {
    // مدیریت خطای شبکه
    echo "Network error: {$e->getMessage()}";
} catch (GeminiConfigurationException $e) {
    // مدیریت خطای پیکربندی
    echo "Configuration error: {$e->getMessage()}";
} catch (GeminiApiException $e) {
    // مدیریت سایر خطاهای API
    echo "API error: {$e->getMessage()}";
}
```php

## تست‌ها

برای اجرای تست‌ها:

```bash
composer test
```php

## مشارکت

مشارکت‌ها با آغوش باز پذیرفته می‌شوند! لطفاً قبل از ارسال pull request، تست‌ها را اجرا کنید.

## لایسنس

این پکیج تحت لایسنس MIT منتشر شده است. برای اطلاعات بیشتر، فایل [LICENSE](LICENSE) را مشاهده کنید.
