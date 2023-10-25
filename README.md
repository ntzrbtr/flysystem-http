# Flysystem adapter for HTTP

This adapter allows you to read files from a remote HTTP server.

Note that the adapter only supports reading files, not writing (all write operations return false or throw an exception).

## Usage

You can choose whether to use the adapter based on PSR-7 (using Guzzle by default) or using pure PHP streams.

When using the PSR-7 adapter, you can directly pass a PSR-7 client to the constructor. Alternatively, you can pass a
base URL and the adapter will create a Guzzle client for you.

```php
$adapterFromUrl = \Netzarbeiter\FlysystemHttp\HttpAdapterPsr::fromUrl('http://example.com');

$client = new \GuzzleHttp\Client(['base_uri' => 'http://example.com']);
$adapterFromClient = new Netzarbeiter\FlysystemHttp\HttpAdapterPsr($client);
```

When using the stream adapter, you can pass a base URL and a stream context (optional).

```php
$adapter = new \Netzarbeiter\FlysystemHttp\HttpAdapterStream('http://example.com');
```
