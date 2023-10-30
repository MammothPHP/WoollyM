### Displaying an HTML table:

```php
$html = $df->toHTML(['class' => 'myclass', 'id' => 'myid']);
```

<table>
<thead><tr><th>a</th><th>b</th><th>c</th></tr></thead>
<tfoot><tr><th>a</th><th>b</th><th>c</th></tr></tfoot>
<tbody>
<tr><th>1</th><th>2</th><th>3</th></tr>
<tr><th>4</th><th>5</th><th>6</th></tr>
<tr><th>7</th><th>8</th><th>9</th></tr>
</tbody>
</table>

With support for [DataTables.js](http://datatables.net/):

```php
$dataTable = $df->toHTML(['datatable' => '{ "optionKey": "optionValue" }']);
```
