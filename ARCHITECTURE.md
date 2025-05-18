# معماری پکیج sanjarani/gemini

این سند معماری کلی پکیج `sanjarani/gemini` را توضیح می‌دهد که برای کار با Google Gemini API در فریم‌ورک Laravel 12+ طراحی شده است.

## اصول طراحی

- **ماژولار**: هر بخش از پکیج به صورت ماژولار طراحی شده تا امکان جایگزینی و توسعه آسان فراهم شود.
- **سرویس‌محور**: استفاده از الگوی طراحی سرویس‌محور برای جداسازی منطق کسب و کار از زیرساخت.
- **تزریق وابستگی**: پشتیبانی کامل از تزریق وابستگی (Dependency Injection) برای تست‌پذیری بهتر.
- **قابل گسترش**: طراحی به گونه‌ای که افزودن قابلیت‌های جدید بدون تغییر در کد موجود امکان‌پذیر باشد.
- **سازگار با Laravel**: استفاده از قراردادها و الگوهای Laravel برای یکپارچگی بهتر.

## ساختار پوشه‌ها

```
src/
  ├── Clients/             # کلاینت‌های ارتباط با API
  ├── Responses/           # کلاس‌های پاسخ به صورت Value Object
  ├── Services/            # سرویس‌های اصلی پکیج
  ├── Jobs/                # کارهای غیرهمزمان برای اجرا در صف
  ├── Exceptions/          # استثناهای اختصاصی
  ├── Contracts/           # اینترفیس‌ها و قراردادها
  ├── Facades/             # فاساد برای دسترسی آسان‌تر
  ├── Middleware/          # میدلورهای اختصاصی
  ├── Utilities/           # ابزارهای کمکی مانند شمارنده توکن
  ├── Support/             # کلاس‌های پشتیبانی و کمکی
  ├── GeminiServiceProvider.php  # سرویس پروایدر اصلی
  └── Gemini.php           # کلاس اصلی پکیج
config/
  └── gemini.php           # فایل پیکربندی
tests/
  ├── Feature/             # تست‌های ویژگی
  ├── Unit/                # تست‌های واحد
  └── Integration/         # تست‌های یکپارچگی با API واقعی
```

## اجزای اصلی

### 1. کلاینت Gemini

کلاینت اصلی مسئول ارتباط با Google Gemini API است. این کلاینت از Laravel HTTP Client استفاده می‌کند و قابلیت‌های زیر را دارد:

- ارسال درخواست به API با مدیریت خطای دقیق
- پشتیبانی از تمام مدل‌های Gemini
- امکان تغییر مدل در زمان اجرا
- مدیریت محدودیت نرخ ارسال درخواست (Rate Limiting)
- پشتیبانی از استراتژی Backoff برای خطاهای شبکه

```php
interface GeminiClientInterface
{
    public function send(array $payload, ?string $model = null): GeminiResponseInterface;
    public function setModel(string $model): self;
    public function getModel(): string;
}
```

### 2. سرویس‌های تخصصی

سرویس‌های تخصصی برای کار با انواع مختلف درخواست‌ها طراحی شده‌اند:

- `TextGenerationService`: برای تولید متن با مدل‌های متنی
- `VisionService`: برای کار با مدل‌های بینایی و تصویر
- `EmbeddingService`: برای ایجاد embedding از متن‌ها

```php
interface TextGenerationServiceInterface
{
    public function generate(string $prompt, array $options = []): GeminiResponseInterface;
    public function chat(array $messages, array $options = []): GeminiResponseInterface;
}
```

### 3. کلاس‌های پاسخ

پاسخ‌های API به صورت Value Object پیاده‌سازی می‌شوند تا تست‌پذیری و استفاده از آن‌ها آسان‌تر باشد:

```php
interface GeminiResponseInterface
{
    public function content(): string;
    public function raw(): array;
    public function tokenUsage(): array;
    public function estimatedCost(): float;
    public function model(): string;
    public function finishReason(): ?string;
    public function successful(): bool;
}
```

### 4. سیستم کش

سیستم کش برای ذخیره پاسخ‌های قبلی و کاهش هزینه و زمان پاسخ‌دهی طراحی شده است:

```php
interface GeminiCacheInterface
{
    public function remember(string $key, array $payload, \Closure $callback, ?int $ttl = null): GeminiResponseInterface;
    public function forget(string $key): bool;
    public function has(string $key): bool;
}
```

### 5. سیستم صف و کارهای غیرهمزمان

برای اجرای غیرهمزمان درخواست‌ها، از سیستم صف Laravel استفاده می‌شود:

```php
class RunGeminiJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    protected $payload;
    protected $model;
    protected $callback;
    
    // پیاده‌سازی متدهای لازم
}
```

### 6. شمارنده توکن و تخمین هزینه

ابزاری برای تخمین تعداد توکن‌ها و هزینه درخواست‌ها:

```php
interface TokenCounterInterface
{
    public function countTokens(string $text): int;
    public function estimateCost(int $inputTokens, int $outputTokens, string $model): float;
}
```

### 7. سیستم لاگینگ

سیستم لاگینگ قابل فعال/غیرفعال‌سازی برای ثبت درخواست‌ها و پاسخ‌ها:

```php
interface GeminiLoggerInterface
{
    public function logRequest(array $payload, string $model): void;
    public function logResponse(GeminiResponseInterface $response): void;
    public function logError(\Throwable $exception): void;
}
```

### 8. فاساد

فاساد برای دسترسی آسان‌تر به قابلیت‌های پکیج:

```php
class Gemini extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'gemini';
    }
}
```

### 9. میدلور

میدلور برای مدیریت درخواست‌ها و محدودیت‌های دسترسی:

```php
class GeminiRateLimitMiddleware
{
    public function handle($request, \Closure $next)
    {
        // پیاده‌سازی منطق محدودیت نرخ درخواست
        return $next($request);
    }
}
```

## جریان کار

1. کاربر از طریق فاساد یا تزریق وابستگی، درخواستی را به سرویس مورد نظر ارسال می‌کند.
2. سرویس، درخواست را پردازش کرده و در صورت فعال بودن کش، ابتدا بررسی می‌کند که آیا پاسخ در کش موجود است یا خیر.
3. اگر پاسخ در کش نباشد، سرویس از کلاینت برای ارسال درخواست به API استفاده می‌کند.
4. کلاینت، درخواست را به API ارسال کرده و پاسخ را دریافت می‌کند.
5. پاسخ دریافتی به یک شیء `GeminiResponse` تبدیل می‌شود.
6. در صورت فعال بودن کش، پاسخ در کش ذخیره می‌شود.
7. پاسخ به کاربر برگردانده می‌شود.

## مدیریت خطا

خطاهای مختلف با استثناهای اختصاصی مدیریت می‌شوند:

- `GeminiApiException`: خطای عمومی API
- `GeminiApiRateLimitException`: خطای محدودیت نرخ درخواست
- `GeminiModelNotFoundException`: خطای عدم وجود مدل
- `GeminiInvalidResponseException`: خطای پاسخ نامعتبر
- `GeminiNetworkException`: خطای شبکه
- `GeminiConfigurationException`: خطای پیکربندی

## پیکربندی

فایل پیکربندی `config/gemini.php` شامل موارد زیر است:

```php
return [
    'api_key' => env('GEMINI_API_KEY'),
    'base_url' => env('GEMINI_BASE_URL', 'https://generativelanguage.googleapis.com/v1'),
    'default_model' => env('GEMINI_DEFAULT_MODEL', 'gemini-pro'),
    'request_timeout' => env('GEMINI_REQUEST_TIMEOUT', 30),
    'enable_cache' => env('GEMINI_ENABLE_CACHE', false),
    'cache_ttl' => env('GEMINI_CACHE_TTL', 3600),
    'enable_logging' => env('GEMINI_ENABLE_LOGGING', false),
    'models' => [
        'gemini-pro' => [
            'max_tokens' => 8192,
            'input_price_per_1k' => 0.00025,
            'output_price_per_1k' => 0.0005,
        ],
        'gemini-pro-vision' => [
            'max_tokens' => 8192,
            'input_price_per_1k' => 0.0025,
            'output_price_per_1k' => 0.0005,
        ],
        // سایر مدل‌ها
    ],
];
```

## دستورات Artisan

دستورات Artisan زیر برای کار با پکیج فراهم شده‌اند:

- `php artisan gemini:test`: برای تست دستی یک پرامپت از CLI
- `php artisan gemini:cache-clear`: برای حذف کش‌های ایجادشده

## تست‌پذیری

پکیج به گونه‌ای طراحی شده که به راحتی قابل تست باشد:

- تست‌های واحد برای کلاس‌های اصلی
- تست‌های ویژگی برای سناریوهای کاربردی
- تست‌های یکپارچگی با API واقعی
- امکان Mock کردن پاسخ‌های API برای تست بدون نیاز به اتصال به اینترنت
