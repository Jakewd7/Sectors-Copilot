**1. Helper Global PHP (`app/Helpers/helpers.php`)**

* **`dateformat(?string $date): string`**
Format tanggal ke format Indonesia lengkap.
```php
dateformat('2026-08-21'); // Output: 21 Agustus 2026

```


* **`datetimeformat(?string $datetime): string`**
Format tanggal dan waktu lengkap.
```php
datetimeformat('2026-08-21 08:18:17'); // Output: 21 Agustus 2026 08:18

```


* **`dateformat_short(?string $date): string`**
Format tanggal singkat numerik.
```php
dateformat_short('2026-08-21'); // Output: 21/08/2026

```


* **`datetimeformat_short(?string $datetime): string`**
Format tanggal dan waktu singkat numerik.
```php
datetimeformat_short('2026-08-21 08:18:17'); // Output: 21/08/2026 08:18

```


* **`timeformat(?string $time): string`**
Format jam dan menit saja.
```php
timeformat('08:18:17'); // Output: 08:18

```


* **`dateformat_mysql(?string $date): ?string`**
Konversi format tanggal ke format database MySQL (Y-m-d).
```php
dateformat_mysql('21/08/2026'); // Output: 2026-08-21

```


* **`datetimeformat_day(?string $datetime): string`**
Format lengkap dengan nama hari bahasa Indonesia.
```php
datetimeformat_day('2026-08-21 08:18:17'); // Output: Jumat, 21 Agustus 2026 08:18

```


* **`getallheaders(): array`**
Mengambil seluruh HTTP request headers (fallback aman).
```php
$headers = getallheaders();

```


* **`substrwords(?string $text, int $maxchar = 100, string $end = '...'): string`**
Memotong teks berdasarkan batas karakter tanpa merusak kata.
```php
substrwords('Aplikasi Kasir POS Laravel', 10); // Output: Aplikasi...

```


* **`clearNum(string|int|float|null $number): int`**
Membersihkan format angka/Rupiah menjadi integer murni.
```php
clearNum('Rp 150.000'); // Output: 150000

```


* **`myNum(int|float|string|null $number): string`**
Memformat angka ke format ribuan khas Indonesia.
```php
myNum(150000); // Output: 150.000

```


* **`format_code(string|int|null $text, int $num_nol = 6): string`**
Membuat nomor urut/kode dengan padding angka nol di depan (zero-fill).
```php
format_code(15, 6); // Output: 000015

```


* **`generateRandomString(int $length = 10): string`**
Generate string acak alfanumerik.
```php
generateRandomString(8);

```


* **`generateRandomNumber(int $length = 10): string`**
Generate string khusus angka acak.
```php
generateRandomNumber(6); // Output: 482910

```