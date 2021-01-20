## Code/Annotation examples

Collection of code/annotation examples and their corresponding OpenAPI specs generated using swagger-php.

* **openapi-spec**
  
  Implementation of the [OpenAPI v3 example specs](https://github.com/OAI/OpenAPI-Specification/tree/master/examples/v3.0)
  using swagger-php annotations.
  
  * openapi-spec: [source](openapi-spec) / [spec](openapi-spec/openapi-spec.yaml)
  * petstore-3.0 (includes oauth2 auth flow): [source](petstore-3.0) / [spec](openapi-spec/petstore-3.0.yaml)
  
* **petstore.swagger.io**

  The [swagger-ui](http://petstore.swagger.io/) petstore example using swagger-php annotations.

  * petstore.swagger.io: [source](petstore.swagger.io) / [spec](petstore.swagger.io/petstore.swagger.io.yaml)

* **swagger-spec**

  Some more examples based on the (now defunct) [swagger-api](https://github.com/swagger-api/) specs.

  * petstore: [source](swagger-spec/petstore) / [spec](swagger-spec/petstore/petstore.yaml)
  * petstore-simple: [source](swagger-spec/petstore-simple)
    / [spec](swagger-spec/petstore-simple/petstore-simple.yaml)
  * petstore-with-external-docs: [source](swagger-spec/petstore-with-external-docs)
    / [spec](swagger-spec/petstore-with-external-docs/petstore-with-external-docs.yaml)
 
* **Other**

  * simple response object: [source](example-object) / [spec](example-object/example-object.yaml)
  * misc: [source](misc) / [spec](misc/misc.yaml)
  * using interfaces: [source](using-interfaces) 
    / [spec (inherit)](using-interfaces/using-interfaces-inherit.yaml)
    / [spec (merge)](using-interfaces/using-interfaces-merge.yaml) 
  * using traits: [source](using-traits)
    / [spec (inherit)](using-traits/using-traits-inherit.yaml)
    / [spec (merge)](using-traits/using-interfaces-merge.yaml) 
  * using refs: [source](using-refs) / [spec](using-refs/using-refs.yaml) 
  

## Custom processors

[Processors](../src/Processors) implement the various steps involved in converting annotations into an OpenAPI spec.

Writing a custom processor is the recommended way to extend swagger-php in a clean way.

Processors are expected to implement the `__invoke()` method expecting the current `Analysis` object as single parameter:

```php
<?php
...
use OpenApi\Analysis;
...

class MyCustomProcessor
{
    public function __invoke(Analysis $analysis)
    {
        // custom processing    
    }
}
``` 

* **schema-query-parameter-processor**

  A processor that takes a vendor tag (expecting a schema `#ref`) and injects all properties of that given schema as
  query parameter to the [request definition](schema-query-parameter-processor/app/ProductController.php).

  [source](schema-query-parameter-processor)