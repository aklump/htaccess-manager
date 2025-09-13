<!--
id: plugins
tags: ''
-->

# Plugins

```text
plugins/source/
├── source.schema.json
├── src
│   └── SourcePlugin.php
└── tests
    └── SourcePluginTest.php
```

## Requirements

* Plugin classes should end in `Plugin.php` (otherwise the instructions for testing below will not be accurate)

## PhpUnit Testing

Tests are not yet handled automatically, so you have to do the following if your plugin has unit tests.

1. Register your test namespace in _composer.json_.

   ```json
   {
     "autoload-dev": {
       "psr-4": {
         "AKlump\\HtaccessManager\\Tests\\Unit\\Plugin\\": [
           "./plugins/source/tests"
         ]
       }
     }
   }
   ```

2. Require your plugin class you're testing in your test file(s).

    ```php
    namespace AKlump\HtaccessManager\Tests\Unit;
    
    require_once __DIR__ . '/../src/SourcePlugin.php';
    
    class SourcePluginTest extends TestCase {
    ```

3. Add path to _phpunit.xml_. Either one path per plugin, or use a glob as shown below.

   ```xml
   
   <testsuites>
       <testsuite name="default">
           <directory suffix="Test.php">../plugins/*/tests/</directory>
       </testsuite>
   </testsuites>
   ```

4. Add to the coverage in _phpunit.xml_

   ```xml
   <coverage processUncoveredFiles="true">
       <include>
            <directory suffix="Plugin.php">../plugins/*/</directory>
       </include>
   </coverage>
   ```
