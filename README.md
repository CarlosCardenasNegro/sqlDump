<h1>Simple utility to dump a MySQL database</h1>
<p>This is a simple utility that make a dump of a selected database to a text file containts the sql code and the data to recreate the database.</p>
<p>The sql text file should include the table structure the complete data and the indexes.</p>
<p>It don't dump Views, Stored procedures and so... because it's very simple, but it do his job.</p>
<p>It require and use <code>monolog</code> to export messages, error, and so...</p>
<h2>Usage</h2>
<p>It's so simple. Create a instance of the class passing a <code>\PDO</code> instance and a <code>monolog</code> instance.</p>
<p>And execute his <code>dumpSQL</code> method <code>$example->dumpSQL([$filename])</code>. The filename it's optional (it default to 'Dump.sql')</p>
<p>The other methods can be used also to extract only tables, fields anda data and so,...  if you want.</p>
<pre>
<?php

$dumpTest = new sqlDump\sqlDump($pdo, $logger);
echo $dumpTest->dumpSQL();

?>
</pre>
