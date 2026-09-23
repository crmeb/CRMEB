# jmespath.php

JMESPath (pronounced "jaymz path") allows you to declaratively specify how to
extract elements from a JSON document. *jmespath.php* allows you to use JMESPath
in PHP applications with PHP data structures. It requires PHP 7.2.5 or greater
and can be installed through [Composer](https://getcomposer.org/doc/00-intro.md)
using the `mtdowling/jmespath.php` package.

```php
require 'vendor/autoload.php';

$expression = 'foo.*.baz';

$data = [
    'foo' => [
        'bar' => ['baz' => 1],
        'bam' => ['baz' => 2],
        'boo' => ['baz' => 3]
    ]
];

JmesPath\search($expression, $data);
// Returns: [1, 2, 3]
```

- [JMESPath Tutorial](https://jmespath.org/tutorial.html)
- [JMESPath Grammar](https://jmespath.org/specification.html#grammar)
- [JMESPath Python library](https://github.com/jmespath/jmespath.py)

## PHP Usage

The `JmesPath\search` function can be used in most cases when using the library.
This function utilizes a JMESPath runtime based on your environment. The runtime
utilized can be configured using environment variables.

```php
$result = JmesPath\search($expression, $data);

// or, if you require PSR-4 compliance.
$result = JmesPath\Env::search($expression, $data);
```

### Runtimes

jmespath.php utilizes *runtimes*. There are currently two runtimes: AstRuntime
and CompilerRuntime.

AstRuntime is utilized by `JmesPath\search()` and `JmesPath\Env::search()` by
default.

#### AstRuntime

The AstRuntime will parse an expression, cache the resulting AST in memory, and
interpret the AST using an external tree visitor. AstRuntime provides a good
general approach for interpreting JMESPath expressions that have a low to
moderate level of reuse.

```php
$runtime = new JmesPath\AstRuntime();
$runtime('foo.bar', ['foo' => ['bar' => 'baz']]);
// > 'baz'
```

#### CompilerRuntime

`JmesPath\CompilerRuntime` provides the most performance for applications that
have a moderate to high level of reuse of JMESPath expressions. The
CompilerRuntime will walk a JMESPath AST and emit PHP source code, resulting in
anywhere from 7x to 60x speed improvements.

Compiling JMESPath expressions to source code is a slower process than just
walking and interpreting a JMESPath AST (via the AstRuntime). However, running
the compiled JMESPath code results in much better performance than walking an
AST. This essentially means that there is a warm-up period when using the
`CompilerRuntime`, but after the warm-up period, it will provide much better
performance.

Use the CompilerRuntime if you know that you will be executing JMESPath
expressions more than once or if you can pre-compile JMESPath expressions before
executing them (for example, server-side applications).

```php
// Note: The cache directory argument is optional.
$runtime = new JmesPath\CompilerRuntime('/path/to/compile/folder');
$runtime('foo.bar', ['foo' => ['bar' => 'baz']]);
// > 'baz'
```

##### Environment Variables

You can utilize the CompilerRuntime in `JmesPath\search()` by setting the
`JP_PHP_COMPILE` environment variable to "on" or to a directory on disk used to
store cached expressions.

## Testing

A comprehensive list of test cases can be found at
https://github.com/jmespath/jmespath.php/tree/master/tests/compliance. These
compliance tests are utilized by jmespath.php to ensure consistency with other
implementations, and can serve as examples of the language.

jmespath.php is tested using PHPUnit. In order to run the tests, you need to
first install the dependencies using Composer, then you just need to run the
tests via make:

```bash
make test
```

You can run a suite of performance tests as well:

```bash
make perf
```

## Security

If you discover a security vulnerability within this package, follow the
reporting process from our
[Security Policy](https://github.com/jmespath/jmespath.php/security/policy).

## License

jmespath.php is made available under the MIT License (MIT). Please see
[License File](LICENSE) for more information.
