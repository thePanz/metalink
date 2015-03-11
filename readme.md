# Metalink

This package allows to simplify the handling and serving of MetaLink files.
The main class is Metalink, while ApacheMetalink is an example of Apache mirror list
wrapped in a Metalink provider.

## Basic Example

``` php
require_once 'vendor/autoload.php';

$path = 'lucene/solr/4.10.3/solr-4.10.3.zip';
$meta = new Pnz\Metalink\ApacheMetalink($path);
$filename = basename($path);

header('Content-Type: application/metalink4+xml');
header('Content-Description: File Transfer');
header('Content-Disposition: attachment; filename="' . $filename . '.metalink"');

print $meta->getMetalink4XML();
die();
```
