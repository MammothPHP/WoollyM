## Get an HTML table:

```php
$html = HTML::fromDataFrame($df)->toString(class: 'myclass', id: 'myid', offset: 0, limit: null, pretty: true);
```