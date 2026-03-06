<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $apiSpec->name }} - API Reference</title>
</head>
<body>
    <script
        id="api-reference"
        data-url="{{ route('api-specs.spec.json', ['uuid' => $apiSpec->uuid]) }}"
        data-configuration="{{ json_encode([
            'theme' => 'default',
            'hideDownloadButton' => false,
            'hideModels' => false,
            'hideDarkModeToggle' => true,
            'defaultHttpClient' => ['targetKey' => 'php', 'clientKey' => 'guzzle'],
        ]) }}"
    ></script>
    <script src="https://cdn.jsdelivr.net/npm/@scalar/api-reference"></script>
</body>
</html>
